<?php
session_start();
require_once 'database.php';

// Process all form submissions first, before fetching data

// Handle the "end session" form submission
if (isset($_POST['end_session'])) {
    // Debug information
    error_log("Processing end_session request: ".print_r($_POST, true));
    
    $session_id = $_POST['session_id'];
    $student_id = $_POST['student_id'];
    $end_time = date('Y-m-d H:i:s');
    
    // Get current session count before updating
    $session_query = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
    $session_query->bind_param("s", $student_id);
    $session_query->execute();
    $current_session = $session_query->get_result()->fetch_assoc()['SESSION'];
        // Start a transaction for consistency
    $conn->begin_transaction();
    
    try {
        // First verify this is a valid active session for this specific student
        $verify_session = $conn->prepare("
            SELECT id FROM sit_in_sessions 
            WHERE id = ? 
            AND student_id = ? 
            AND status = 'active'
            LIMIT 1
        ");
        $verify_session->bind_param("is", $session_id, $student_id);
        $verify_session->execute();
        
        if ($verify_session->get_result()->num_rows === 0) {
            throw new Exception("Session not found or already completed");
        }

        // Update sit-in session status only for this specific student
        $stmt = $conn->prepare("
            UPDATE sit_in_sessions 
            SET status = 'completed', 
                end_time = ? 
            WHERE id = ? 
            AND student_id = ? 
            AND status = 'active'
            LIMIT 1
        ");
        $stmt->bind_param("sis", $end_time, $session_id, $student_id);
        
        if ($stmt->execute()) {
            // Decrease session count
            $new_session = $current_session - 1;
            $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
            $update_session->bind_param("is", $new_session, $student_id);
            $update_session->execute();
            
            // Commit the transaction
            $conn->commit();
            $_SESSION['success'] = "Session ended successfully! Student has " . $new_session . " sessions remaining.";
        } else {
            throw new Exception("Failed to update session status");
        }
        
        // Close statement after transaction is complete
        $stmt->close();
    } catch (Exception $e) {
        // Rollback on any error
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
        error_log("Session timeout error: " . $e->getMessage());
    }
    
    // Redirect to refresh the page
    header("Location: sit-in.php");
    exit();
}

// Handle reservation timeout with end time tracking
if (isset($_POST['end_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $student_id = $_POST['student_id'];
    $now = date('Y-m-d H:i:s');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First get current session count and verify student
        $session_query = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
        $session_query->bind_param("s", $student_id);
        $session_query->execute();
        $session_result = $session_query->get_result();
        
        if ($session_result->num_rows === 0) {
            throw new Exception("Student not found");
        }
        
        $current_session = $session_result->fetch_assoc()['SESSION'];
        
        // Get reservation details and verify ownership
        $get_reservation = $conn->prepare("
            SELECT lab_room, pc_number 
            FROM reservations 
            WHERE id = ? 
            AND student_id = ? 
            AND status = 'approved'
            AND timeout_at IS NULL
        ");
        $get_reservation->bind_param("is", $reservation_id, $student_id);
        $get_reservation->execute();
        $reservation = $get_reservation->get_result()->fetch_assoc();
        
        if (!$reservation) {
            throw new Exception("Reservation not found or already timed out");
        }
        
        // Record timeout for specific reservation only
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET timeout_at = ? 
            WHERE id = ? 
            AND student_id = ?
            AND status = 'approved' 
            AND timeout_at IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("sis", $now, $reservation_id, $student_id);
        
        // Update computer status to available for specific computer only
        $update_computer = $conn->prepare("
            UPDATE computers 
            SET status = 'available' 
            WHERE lab_room_id = ? 
            AND pc_number = ?
            AND status = 'in_use'
            LIMIT 1
        ");
        $update_computer->bind_param("ss", $reservation['lab_room'], $reservation['pc_number']);
        
        // Decrease session count
        $new_session = $current_session - 1;
        $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
        $update_session->bind_param("is", $new_session, $student_id);
        
        if ($stmt->execute() && $update_computer->execute() && $update_session->execute()) {
            $conn->commit();
            $_SESSION['success'] = "Timeout recorded successfully. Student has {$new_session} sessions remaining.";
        } else {
            throw new Exception("Failed to record timeout");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: sit-in.php");
    exit();
}

// Process direct sit-in
if (isset($_POST['start_sitin'])) {
    $student_id = $_POST['student_id'];
    $lab_room = $_POST['lab_room'];
    $purpose = $_POST['purpose'];
    
    // Check if student has active sit-in
    $check_stmt = $conn->prepare("SELECT id FROM sit_in_sessions WHERE student_id = ? AND status = 'active'");
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $has_active_sitin = $result->num_rows > 0;
    
    // Check if student has active reservation
    $check_res_stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE student_id = ? 
        AND status = 'approved' 
        AND reservation_date = CURRENT_DATE
        AND time_in <= CURRENT_TIME 
        AND time_out >= CURRENT_TIME
    ");
    $check_res_stmt->bind_param("i", $student_id);
    $check_res_stmt->execute();
    $has_active_reservation = $check_res_stmt->get_result()->fetch_assoc()['count'] > 0;
    
    if ($has_active_sitin) {
        $_SESSION['error'] = "You already have an active sit-in session.";
    } 
    elseif ($has_active_reservation) {
        $_SESSION['error'] = "You have an approved reservation for this time. Please use that instead.";
    }
    else {
        $stmt = $conn->prepare("INSERT INTO sit_in_sessions (student_id, reservation_id, lab_room, purpose, status) VALUES (?, NULL, ?, ?, 'active')");
        $stmt->bind_param("iss", $student_id, $lab_room, $purpose);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Direct sit-in session started successfully.";
        } else {
            $_SESSION['error'] = "Failed to start sit-in session.";
        }
    }
    
    header("Location: sit-in.php");
    exit();
}

// After processing all form submissions, fetch the latest data
// Fetch direct sit-in sessions
$direct_sessions_query = "
    SELECT 
        s.id as sit_id,
        s.student_id,
        s.lab_room,
        s.purpose,
        s.status,
        s.start_time,
        u.FIRSTNAME,
        u.LASTNAME,
        u.ID_NUMBER,
        u.SESSION
    FROM sit_in_sessions s
    JOIN users u ON s.student_id = u.ID_NUMBER
    WHERE s.status = 'active' 
    ORDER BY s.start_time DESC
";

$direct_sessions = $conn->query($direct_sessions_query);

if (!$direct_sessions) {
    $_SESSION['error'] = "Database error: " . $conn->error;
}

// Get current time and date
$current_date = date('Y-m-d');
$current_time = date('H:i:s');

// Update the reservation query to only show approved reservations without timeout
$reservation_sessions = $conn->query("
    SELECT 
        r.id,
        r.student_id,
        r.lab_room,
        r.pc_number,
        r.purpose,
        r.reservation_date,
        r.time_in,
        r.status,
        r.timeout_at,
        u.FIRSTNAME,
        u.LASTNAME,
        u.ID_NUMBER,
        u.SESSION
    FROM reservations r
    JOIN users u ON r.student_id = u.ID_NUMBER
    WHERE r.status = 'approved' 
    AND (r.timeout_at IS NULL)  -- Only show reservations that haven't been timed out
    ORDER BY r.time_in ASC
");

// The end_session handler at the top of the file takes care of this functionality

// Handle reservation timeout with end time tracking
if (isset($_POST['end_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $student_id = $_POST['student_id'];
    $now = date('Y-m-d H:i:s');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First get current session count and verify student
        $session_query = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
        $session_query->bind_param("s", $student_id);
        $session_query->execute();
        $session_result = $session_query->get_result();
        
        if ($session_result->num_rows === 0) {
            throw new Exception("Student not found");
        }
        
        $current_session = $session_result->fetch_assoc()['SESSION'];
        
        // Get reservation details and verify ownership
        $get_reservation = $conn->prepare("
            SELECT lab_room, pc_number 
            FROM reservations 
            WHERE id = ? 
            AND student_id = ? 
            AND status = 'approved'
            AND timeout_at IS NULL
        ");
        $get_reservation->bind_param("is", $reservation_id, $student_id);
        $get_reservation->execute();
        $reservation = $get_reservation->get_result()->fetch_assoc();
        
        if (!$reservation) {
            throw new Exception("Reservation not found or already timed out");
        }
        
        // Record timeout for specific reservation only
        $stmt = $conn->prepare("
            UPDATE reservations 
            SET timeout_at = ? 
            WHERE id = ? 
            AND student_id = ?
            AND status = 'approved' 
            AND timeout_at IS NULL
            LIMIT 1
        ");
        $stmt->bind_param("sis", $now, $reservation_id, $student_id);
        
        // Update computer status to available for specific computer only
        $update_computer = $conn->prepare("
            UPDATE computers 
            SET status = 'available' 
            WHERE lab_room_id = ? 
            AND pc_number = ?
            AND status = 'in_use'
            LIMIT 1
        ");
        $update_computer->bind_param("ss", $reservation['lab_room'], $reservation['pc_number']);
        
        // Decrease session count
        $new_session = $current_session - 1;
        $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
        $update_session->bind_param("is", $new_session, $student_id);
        
        if ($stmt->execute() && $update_computer->execute() && $update_session->execute()) {
            $conn->commit();
            $_SESSION['success'] = "Timeout recorded successfully. Student has {$new_session} sessions remaining.";
        } else {
            throw new Exception("Failed to record timeout");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    header("Location: sit-in.php");
    exit();
}

// Function to check if student has active sit-in
function hasActiveSitIn($conn, $student_id) {
    $stmt = $conn->prepare("SELECT id FROM sit_in_sessions WHERE student_id = ? AND status = 'active'");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Add this function to check student reservations
function hasActiveReservation($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE student_id = ? 
        AND status = 'approved' 
        AND reservation_date = CURRENT_DATE
        AND time_in <= CURRENT_TIME 
        AND time_out >= CURRENT_TIME
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] > 0;
}

// Process direct sit-in
if (isset($_POST['start_sitin'])) {
    $student_id = $_POST['student_id'];
    $lab_room = $_POST['lab_room'];
    $purpose = $_POST['purpose'];
    
    // Check if student has active sit-in
    if (hasActiveSitIn($conn, $student_id)) {
        $_SESSION['error'] = "You already have an active sit-in session.";
    } 
    // Check if student has active reservation
    elseif (hasActiveReservation($conn, $student_id)) {
        $_SESSION['error'] = "You have an approved reservation for this time. Please use that instead.";
    }
    else {
        $stmt = $conn->prepare("INSERT INTO sit_in_sessions (student_id, lab_room, purpose, status) VALUES (?, ?, ?, 'active')");
        $stmt->bind_param("iss", $student_id, $lab_room, $purpose);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Sit-in session started successfully.";
        } else {
            $_SESSION['error'] = "Failed to start sit-in session.";
        }
    }
    header("Location: sit-in.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Active Sit-in Sessions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php 
                    echo $_SESSION['success']; 
                    unset($_SESSION['success']); 
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>
        <!-- Tab Navigation -->
        <div class="mb-4 border-b border-gray-200">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 rounded-t-lg border-b-2 border-blue-600 text-blue-600 active" 
                            id="direct-tab" 
                            data-tabs-target="#direct" 
                            type="button" 
                            role="tab" 
                            aria-controls="direct" 
                            aria-selected="true">
                        Direct Sit-in Sessions
                    </button>
                </li>
                <li class="mr-2" role="presentation">
                    <button class="inline-block p-4 rounded-t-lg border-b-2 border-transparent hover:text-gray-600 hover:border-gray-300" 
                            id="reservation-tab" 
                            data-tabs-target="#reservation" 
                            type="button" 
                            role="tab" 
                            aria-controls="reservation" 
                            aria-selected="false">
                        Reservation-based Sessions
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Content -->
        <div id="tabContent">
            <!-- Direct Sit-in Tab -->
            <div class="block" id="direct" role="tabpanel" aria-labelledby="direct-tab">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6 text-center">Direct Sit-in Sessions</h2>
                    <?php if ($direct_sessions && $direct_sessions->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2">Student ID</th>
                                        <th class="px-4 py-2">Name</th>
                                        <th class="px-4 py-2">Laboratory</th>
                                        <th class="px-4 py-2">Purpose</th>
                                        <th class="px-4 py-2">Start Time</th>
                                        <th class="px-4 py-2">Sessions Left</th>
                                        <th class="px-4 py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($session = $direct_sessions->fetch_assoc()): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($session['ID_NUMBER']); ?></td>
                                            <td class="border px-4 py-2">
                                                <?php echo htmlspecialchars($session['FIRSTNAME'] . ' ' . $session['LASTNAME']); ?>
                                            </td>
                                            <td class="border px-4 py-2">Room <?php echo htmlspecialchars($session['lab_room']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($session['purpose']); ?></td>
                                            <td class="border px-4 py-2">
                                                <?php echo date('h:i A', strtotime($session['start_time'])); ?>
                                            </td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($session['SESSION']); ?></td>                                            <td class="border px-4 py-2">                                                <form method="POST" action="sit-in.php" class="inline text-center">
                                                    <input type="hidden" name="session_id" value="<?php echo $session['sit_id']; ?>">
                                                    <input type="hidden" name="student_id" value="<?php echo $session['ID_NUMBER']; ?>">
                                                    <input type="hidden" name="current_tab" value="direct">
                                                    <button type="submit" name="end_session" 
                                                            class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700"
                                                            onclick="localStorage.setItem('activeTab', 'direct');">
                                                        Time Out
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            No active direct sit-in sessions at the moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Reservation-based Tab -->
            <div class="hidden" id="reservation" role="tabpanel" aria-labelledby="reservation-tab">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-bold mb-6 text-center">Reservation-based Sessions</h2>
                    <?php if ($reservation_sessions && $reservation_sessions->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full table-auto">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-4 py-2">Student ID</th>
                                        <th class="px-4 py-2">Name</th>
                                        <th class="px-4 py-2">Laboratory</th>
                                        <th class="px-4 py-2">PC Number</th>
                                        <th class="px-4 py-2">Purpose</th>
                                        <th class="px-4 py-2">Time</th>
                                        <th class="px-4 py-2">Sessions Left</th>
                                        <th class="px-4 py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($reservation = $reservation_sessions->fetch_assoc()): ?>
                                        <tr>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['ID_NUMBER']); ?></td>
                                            <td class="border px-4 py-2">
                                                <?php echo htmlspecialchars($reservation['FIRSTNAME'] . ' ' . $reservation['LASTNAME']); ?>
                                            </td>
                                            <td class="border px-4 py-2">Room <?php echo htmlspecialchars($reservation['lab_room']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['pc_number']); ?></td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                                            <td class="border px-4 py-2">
                                                <?php 
                                                echo date('h:i A', strtotime($reservation['time_in'])) . ' - ' . 
                                                     ($reservation['timeout_at'] ? date('h:i A', strtotime($reservation['timeout_at'])) : 'Ongoing'); 
                                                ?>
                                            </td>
                                            <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['SESSION']); ?></td>
                                            <td class="border px-4 py-2">
                                                <?php 
                                                if (empty($reservation['timeout_at'])): 
                                                ?>
                                                    <form method="POST" class="inline text-center">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                        <input type="hidden" name="student_id" value="<?php echo $reservation['ID_NUMBER']; ?>">
                                                        <button type="submit" name="end_reservation" 
                                                                class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700">
                                                            Timeout
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <span class="text-gray-500">
                                                        Timed out at <?php echo date('h:i A', strtotime($reservation['timeout_at'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            No active reservation-based sessions at the moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('[role="tab"]');
            const tabPanels = document.querySelectorAll('[role="tabpanel"]');
            
            // Check if there's a saved tab in localStorage
            const savedTab = localStorage.getItem('activeTab') || 'direct';
            
            // Activate the saved tab or default to first tab
            activateTab(savedTab);
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-tabs-target').substring(1);
                    activateTab(targetId);
                    
                    // Save the current tab to localStorage
                    localStorage.setItem('activeTab', targetId);
                });
            });
            
            function activateTab(tabId) {
                // Reset all tabs and panels
                tabs.forEach(t => {
                    t.classList.remove('border-blue-600', 'text-blue-600');
                    t.classList.add('border-transparent');
                    t.setAttribute('aria-selected', 'false');
                });
                tabPanels.forEach(p => p.classList.add('hidden'));
                
                // Activate target tab
                const targetTab = document.querySelector(`[data-tabs-target="#${tabId}"]`);
                if (targetTab) {
                    targetTab.classList.remove('border-transparent');
                    targetTab.classList.add('border-blue-600', 'text-blue-600');
                    targetTab.setAttribute('aria-selected', 'true');
                }
                
                // Show target panel
                const panel = document.getElementById(tabId);
                if (panel) {
                    panel.classList.remove('hidden');
                }
            }
        });
    </script>
</body>
</html>
