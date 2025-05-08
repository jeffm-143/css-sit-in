<?php
session_start();
require_once 'database.php';

// Fetch direct sit-in sessions (from sit_in_sessions table)
$direct_sessions = $conn->query("
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
");

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

if (isset($_POST['end_session'])) {
    $session_id = $_POST['session_id'];
    $student_id = $_POST['student_id'];
    $end_time = date('Y-m-d H:i:s');
    
    // Get current session count before updating
    $session_query = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
    $session_query->bind_param("s", $student_id);
    $session_query->execute();
    $current_session = $session_query->get_result()->fetch_assoc()['SESSION'];
    
    // Update sit-in session status
    $stmt = $conn->prepare("UPDATE sit_in_sessions SET status = 'completed', end_time = ? WHERE id = ?");
    $stmt->bind_param("si", $end_time, $session_id);
    
    if ($stmt->execute()) {
        // Decrease session count
        $new_session = $current_session - 1;
        $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
        $update_session->bind_param("is", $new_session, $student_id);
        $update_session->execute();
        
        echo "<script>
            alert('Session ended successfully! Student has " . $new_session . " sessions remaining.');
            window.location.href = 'sit-in.php';
        </script>";
    }
    $stmt->close();
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
        // First get current session count
        $session_query = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
        $session_query->bind_param("s", $student_id);
        $session_query->execute();
        $current_session = $session_query->get_result()->fetch_assoc()['SESSION'];
        
        // Get reservation details to update computer status
        $get_reservation = $conn->prepare("SELECT lab_room, pc_number FROM reservations WHERE id = ?");
        $get_reservation->bind_param("i", $reservation_id);
        $get_reservation->execute();
        $reservation = $get_reservation->get_result()->fetch_assoc();
        
        // Record timeout
        $stmt = $conn->prepare("UPDATE reservations SET timeout_at = ? WHERE id = ? AND status = 'approved'");
        $stmt->bind_param("si", $now, $reservation_id);
        
        // Update computer status to available
        $update_computer = $conn->prepare("
            UPDATE computers 
            SET status = 'available' 
            WHERE lab_room_id = ? AND pc_number = ?
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Sit-in Sessions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6 space-y-6">
        <!-- Direct Sit-in Table -->
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
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($session['SESSION']); ?></td>
                                    <td class="border px-4 py-2">
                                        <form method="POST" action="end_session.php" class="inline text-center">
                                            <input type="hidden" name="session_id" value="<?php echo $session['sit_id']; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo $session['ID_NUMBER']; ?>">
                                            <button type="submit" name="end_session" 
                                                    class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700">
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

        <!-- Reservation-based Sessions Table -->
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
                                                    Record Timeout
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
</body>
</html>
