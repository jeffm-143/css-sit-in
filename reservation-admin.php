<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Add at the beginning of the file, after database connection
if (isset($_POST['ajax_update_computer'])) {
    header('Content-Type: application/json');
    try {
        $pc_id = $_POST['pc_id'];
        $new_status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE computers SET status = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("si", $new_status, $pc_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Computer status updated successfully']);
        } else {
            throw new Exception("No computer found with ID: " . $pc_id);
        }
    } catch (Exception $e) {
        error_log("Error updating computer status: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// Handle lab room status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_lab'])) {
        $room_id = $_POST['room_id'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE lab_rooms SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $room_id);
        
        if ($stmt->execute()) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Lab room status updated successfully.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error updating lab room status.'];
        }
    }

    // Handle computer status updates
    if (isset($_POST['update_computer'])) {
        $pc_id = $_POST['pc_id'];
        $status = $_POST['status'];
        
        $stmt = $conn->prepare("UPDATE computers SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $pc_id);
        
        if ($stmt->execute()) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Computer status updated successfully.'];
        } else {
            $_SESSION['alert'] = ['type' => 'error', 'message' => 'Error updating computer status.'];
        }
    }

    // Handle reservation approval/rejection
    if (isset($_POST['approve_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // First, get the reservation details
            $get_reservation = $conn->prepare("
                SELECT lab_room, pc_number 
                FROM reservations 
                WHERE id = ?
            ");
            $get_reservation->bind_param("i", $reservation_id);
            $get_reservation->execute();
            $reservation = $get_reservation->get_result()->fetch_assoc();

            if (!$reservation) {
                throw new Exception("Reservation not found");
            }

            // Update reservation status to approved
            $update_reservation = $conn->prepare("
                UPDATE reservations 
                SET status = 'approved' 
                WHERE id = ?
            ");
            $update_reservation->bind_param("i", $reservation_id);
            $update_reservation->execute();

            // Update computer status to in_use
            $update_computer = $conn->prepare("
                UPDATE computers 
                SET status = 'in_use' 
                WHERE lab_room_id = ? AND pc_number = ?
            ");
            $update_computer->bind_param("ss", $reservation['lab_room'], $reservation['pc_number']);
            $update_computer->execute();

            // Commit transaction
            $conn->commit();
            $_SESSION['alert'] = [
                'type' => 'success', 
                'message' => 'Reservation approved and computer status updated!'
            ];
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $_SESSION['alert'] = [
                'type' => 'error', 
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    } elseif (isset($_POST['reject_reservation'])) {
        $reservation_id = $_POST['reservation_id'];
        $stmt = $conn->prepare("UPDATE reservations SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        if ($stmt->execute()) {
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Reservation rejected successfully!'];
        }
    }
    header("Location: reservation-admin.php");
    exit();
}

// Get all lab rooms
$lab_rooms = $conn->query("SELECT * FROM lab_rooms ORDER BY room_number");

// Fetch pending reservations
$pending_query = "SELECT r.*, u.FIRSTNAME, u.LASTNAME 
                 FROM reservations r
                 JOIN users u ON r.student_id = u.ID_NUMBER
                 WHERE r.status = 'pending'
                 ORDER BY r.reservation_date ASC, r.time_in ASC";
$pending_reservations = $conn->query($pending_query);

// Update the activity logs query to match the database enum values
$activity_logs_query = "
    SELECT r.*, u.FIRSTNAME, u.LASTNAME 
    FROM reservations r
    JOIN users u ON r.student_id = u.ID_NUMBER
    ORDER BY r.updated_at DESC
";
$activity_logs = $conn->query($activity_logs_query);

// Get filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$lab_filter = isset($_GET['lab']) ? $_GET['lab'] : '';

// Initialize variables before building query
$types = '';
$params = [];

// Build query without auto-timeout
$query = "SELECT r.*, u.FIRSTNAME, u.LASTNAME, u.ID_NUMBER 
          FROM reservations r 
          JOIN users u ON r.student_id = u.ID_NUMBER 
          WHERE 1=1";

if ($status_filter && $status_filter !== 'all') {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}
if ($date_filter) {
    $query .= " AND DATE(r.reservation_date) = ?";
    $params[] = $date_filter;
    $types .= "s";
}
if ($lab_filter && $lab_filter !== 'all') {
    $query .= " AND r.lab_room = ?";
    $params[] = $lab_filter;
    $types .= "s";
}

$query .= " ORDER BY r.reservation_date ASC, r.time_in ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$reservations = $stmt->get_result();

// Replace the auto-timeout function with just status checks
function checkReservation($time_in, $time_out, $reservation_date) {
    $now = new DateTime();
    $date = new DateTime($reservation_date);
    $start = new DateTime($reservation_date . ' ' . $time_in);
    $end = new DateTime($reservation_date . ' ' . $time_out);
    
    if ($now->format('Y-m-d') === $date->format('Y-m-d')) {
        return 'current';
    } elseif ($date > $now) {
        return 'upcoming';
    }
    return 'past';
}

// Update get_reservations query to not auto-timeout
$reservations = $conn->query("
    SELECT r.*, u.FIRSTNAME, u.LASTNAME, u.ID_NUMBER
    FROM reservations r
    JOIN users u ON r.student_id = u.ID_NUMBER
    WHERE r.status IN ('pending', 'approved')
    ORDER BY r.reservation_date DESC, r.time_in ASC
");

// Get unique lab rooms for filter
$lab_rooms_filter = $conn->query("SELECT DISTINCT lab_room FROM reservations ORDER BY lab_room");

// Get all computers for a specific lab room
if (isset($_GET['get_computers'])) {
    header('Content-Type: application/json');
    try {
        $lab_room = $_GET['lab_room'] ?? '517';
        $stmt = $conn->prepare("
            SELECT id, pc_number, status 
            FROM computers 
            WHERE lab_room_id = ? 
            ORDER BY CAST(REPLACE(pc_number, 'PC', '') AS UNSIGNED)
        ");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $conn->error);
        }
        
        $stmt->bind_param("s", $lab_room);
        if (!$stmt->execute()) {
            throw new Exception("Failed to execute: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $computers = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($computers);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .computer-item {
            transition: all 0.3s ease;
        }
        .computer-item:hover {
            transform: translateY(-2px);
        }
        .grid-transition {
            animation: fadeInUp 0.5s ease-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .room-selected {
            background-color: #EBF5FF;
            border-color: #3B82F6;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        #computerGrid {
            transition: all 0.3s ease-in-out;
        }
        #roomHeading {
            transition: all 0.3s ease;
        }
        .status-badge {
            @apply px-2 py-1 rounded-full text-xs font-medium;
        }
        .status-badge.available {
            @apply bg-green-100 text-green-800;
        }
        .status-badge.in-use {
            @apply bg-yellow-100 text-yellow-800;
        }
        .status-badge.maintenance {
            @apply bg-red-100 text-red-800;
        }
        .filter-active {
            @apply ring-2 ring-blue-500;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="container mx-auto px-4 py-8">
        

        <!-- Computer Control -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 id="roomHeading" class="text-2xl font-bold mb-6">Computer Control</h2>
            <div class="flex gap-4 mb-6">
                <select id="labRoomSelect" class="border rounded-lg px-4 py-2 hover:border-blue-500 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    <option value="517" selected>Room 517</option>
                    <?php 
                    $lab_rooms->data_seek(0);
                    while($room = $lab_rooms->fetch_assoc()): 
                        if($room['room_number'] != '517'):
                    ?>
                        <option value="<?php echo $room['room_number']; ?>">Room <?php echo $room['room_number']; ?></option>
                    <?php 
                        endif;
                    endwhile; 
                    ?>
                </select>
                <select id="statusFilter" class="border rounded-lg px-4 py-2">
                    <option value="all">All Status</option>
                    <option value="available">Available</option>
                    <option value="in_use">In Used</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <div id="computerGrid" class="grid grid-cols-5 md:grid-cols-10 gap-4">
                <!-- Computers will be loaded here -->
            </div>
        </div>

        <!-- Reservation Management -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-6">Reservation Management</h2>
            
            <!-- Filters -->
            <div class="mb-6">
                <select id="labFilter" class="border rounded-lg px-4 py-2" onchange="applyFilters('reservation')">
                    <option value="all">All Labs</option>
                    <?php 
                    $lab_rooms->data_seek(0);
                    while($room = $lab_rooms->fetch_assoc()): 
                    ?>
                        <option value="<?php echo $room['room_number']; ?>">Room <?php echo $room['room_number']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <!-- Reservations Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">ID Number</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Lab Room</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">PC</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php while($row = $reservations->fetch_assoc()): ?>
                            <tr class="reservation-row hover:bg-gray-50" 
                                data-status="<?php echo $row['status']; ?>"
                                data-lab="<?php echo $row['lab_room']; ?>"
                                data-date="<?php echo $row['reservation_date']; ?>">
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['ID_NUMBER']); ?></td>
                                <td class="px-6 py-4">Room <?php echo htmlspecialchars($row['lab_room']); ?></td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['pc_number']); ?></td>
                                <td class="px-6 py-4">
                                    <?php 
                                    echo date('M d, Y h:i A', strtotime($row['reservation_date'] . ' ' . $row['time_in']));
                                    ?>
                                </td>
                                <td class="px-6 py-4"><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full <?php 
                                        switch($row['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'approved':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'disapproved':
                                            case 'rejected':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                    ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($row['status'] === 'pending'): ?>
                                        <form method="POST" class="inline-flex gap-2">
                                            <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="approve_reservation" 
                                                    class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600">
                                                Approve
                                            </button>
                                            <button type="submit" name="reject_reservation" 
                                                    class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                                Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Activity Logs -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6">Reservation Activity Logs</h2>
            <div class="mb-6">
                <select id="logsStatusFilter" class="border rounded-lg px-4 py-2" onchange="filterLogs(this.value)">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="disapproved">Disapproved</option>
                </select>
            </div>
            <?php if ($activity_logs->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Lab Room</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Date & Time</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Purpose</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase">Processed At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while($row = $activity_logs->fetch_assoc()): ?>
                                <tr class="log-row" data-status="<?php echo $row['status']; ?>">
                                    <td class="px-6 py-4">
                                        <?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?>
                                    </td>
                                    <td class="px-6 py-4">Room <?php echo htmlspecialchars($row['lab_room']); ?></td>
                                    <td class="px-6 py-4">
                                        <?php echo date('M d, Y h:i A', strtotime($row['reservation_date'] . ' ' . $row['time_in'])); ?>
                                    </td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['purpose']); ?></td>
                                    <td class="px-6 py-4">
                                        <span class="status-badge px-2 py-1 text-xs rounded-full <?php 
                                            switch($row['status']) {
                                                case 'pending':
                                                    echo 'bg-yellow-100 text-yellow-800';
                                                    break;
                                                case 'approved':
                                                    echo 'bg-green-100 text-green-800';
                                                    break;
                                                case 'disapproved':
                                                case 'rejected':
                                                    echo 'bg-red-100 text-red-800';
                                                    break;
                                                default:
                                                    echo 'bg-gray-100 text-gray-800';
                                            }
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?php echo date('M d, Y h:i A', strtotime($row['updated_at'])); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-500 text-center py-4">No activity logs found</p>
            <?php endif; ?>
        </div>

        <script>
            function filterLogs(status) {
                const rows = document.querySelectorAll('.log-row');
                let visibleCount = 0;
                
                rows.forEach(row => {
                    const rowStatus = row.dataset.status;
                    let shouldShow = false;
                    
                    switch(status) {
                        case 'all':
                            shouldShow = true;
                            break;
                        case 'pending':
                            shouldShow = rowStatus === 'pending';
                            break;
                        case 'approved':
                            shouldShow = rowStatus === 'approved';
                            break;
                        case 'disapproved':
                            shouldShow = rowStatus === 'disapproved' || rowStatus === 'rejected';
                            break;
                    }
                    
                    row.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });

                // Update no results message
                const noResults = document.getElementById('noLogsResults');
                if (visibleCount === 0) {
                    if (!noResults) {
                        const message = document.createElement('p');
                        message.id = 'noLogsResults';
                        message.className = 'text-center py-4 text-gray-500';
                        message.textContent = `No ${status === 'all' ? '' : status} reservations found`;
                        document.querySelector('.overflow-x-auto').appendChild(message);
                    }
                } else if (noResults) {
                    noResults.remove();
                }
            }

            // Computer management functions
            function loadComputers(labRoom = '517') {
                const grid = document.getElementById('computerGrid');
                const roomHeading = document.getElementById('roomHeading');
                
                // Add transition class
                grid.classList.add('opacity-0');
                
                fetch(`reservation-admin.php?get_computers=1${labRoom ? `&lab_room=${labRoom}` : ''}`)
                    .then(response => response.json())
                    .then(computers => {
                        grid.innerHTML = '';
                        
                        computers.forEach(pc => {
                            const div = document.createElement('div');
                            div.className = `computer-item ${getStatusClass(pc.status)}`;
                            div.innerHTML = `
                                <div class="p-4 rounded-lg border hover:shadow-md transition-shadow cursor-pointer"
                                     onclick="toggleComputerStatus(this, ${pc.id}, '${pc.status}')"
                                     data-pc-id="${pc.id}">
                                    <i class="fas fa-desktop text-3xl ${getStatusIconClass(pc.status)}"></i>
                                    <p class="mt-2 font-medium">${pc.pc_number}</p>
                                    <p class="text-sm ${getStatusTextClass(pc.status)}">${formatStatus(pc.status)}</p>
                                </div>
                            `;
                            grid.appendChild(div);
                        });
                        
                        // Update heading and animate grid
                        roomHeading.textContent = `Computer Status Management - Room ${labRoom}`;
                        grid.classList.remove('opacity-0');
                        grid.classList.add('grid-transition');
                        
                        // Remove animation class after transition
                        setTimeout(() => {
                            grid.classList.remove('grid-transition');
                        }, 500);
                    });
            }

            function toggleComputerStatus(element, pcId, currentStatus) {
                const pcNumber = element.querySelector('p.font-medium').textContent;
                const newStatus = currentStatus === 'available' ? 'in_use' : 'available';
                
                const formData = new FormData();
                formData.append('ajax_update_computer', '1');
                formData.append('pc_id', pcId);
                formData.append('status', newStatus);

                fetch('reservation-admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update UI
                        const computerDiv = element.closest('.computer-item');
                        computerDiv.className = `computer-item ${getStatusClass(newStatus)}`;
                        element.querySelector('.fas').className = `fas fa-desktop text-3xl ${getStatusIconClass(newStatus)}`;
                        element.querySelector('p.text-sm').className = `text-sm ${getStatusTextClass(newStatus)}`;
                        element.querySelector('p.text-sm').textContent = formatStatus(newStatus);
                        element.setAttribute('onclick', `toggleComputerStatus(this, ${pcId}, '${newStatus}')`);
                        
                        // Show custom success message
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: `${pcNumber} updated to ${newStatus === 'in_use' ? 'in used' : 'available'}`,
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'error',
                        title: `Failed to update ${pcNumber} status`,
                        showConfirmButton: false,
                        timer: 1500
                    });
                });
            }

            function getStatusClass(status) {
                switch(status) {
                    case 'available': return 'bg-green-50';
                    case 'in_use': return 'bg-red-50';
                    case 'maintenance': return 'bg-gray-50';
                    default: return 'bg-gray-50';
                }
            }

            function getStatusIconClass(status) {
                switch(status) {
                    case 'available': return 'text-green-500';
                    case 'in_use': return 'text-red-500';
                    case 'maintenance': return 'text-gray-500';
                    default: return 'text-gray-500';
                }
            }

            function getStatusTextClass(status) {
                switch(status) {
                    case 'available': return 'text-green-600';
                    case 'in_use': return 'text-red-600';
                    case 'maintenance': return 'text-gray-600';
                    default: return 'text-gray-600';
                }
            }

            function formatStatus(status) {
                return status.split('_').map(word => 
                    word.charAt(0).toUpperCase() + word.slice(1)
                ).join(' ');
            }

            // Load computers for room 517 initially and set up event listeners
            document.addEventListener('DOMContentLoaded', () => {
                loadComputers('517');
                
                document.getElementById('labRoomSelect').addEventListener('change', (e) => {
                    const selected = e.target.value || '517';
                    const select = e.target;
                    
                    // Add visual feedback for room selection
                    select.classList.add('room-selected');
                    
                    // Load computers with animation
                    loadComputers(selected);
                    
                    // Remove selection highlight after animation
                    setTimeout(() => {
                        select.classList.remove('room-selected');
                    }, 1000);
                });
                
                document.getElementById('statusFilter').addEventListener('change', (e) => {
                    const status = e.target.value;
                    document.querySelectorAll('.computer-item').forEach(item => {
                        if (status === 'all' || item.querySelector('p.text-sm').textContent.toLowerCase().replace(' ', '_') === status) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            });

            function applyFilters(section) {
                if (section === 'reservation') {
                    const lab = document.getElementById('labFilter').value;
                    document.querySelectorAll('.reservation-row').forEach(row => {
                        const rowLab = row.dataset.lab;
                        row.style.display = (lab === 'all' || lab === rowLab) ? '' : 'none';
                    });
                } else if (section === 'logs') {
                    const status = document.getElementById('logsStatusFilter').value;
                    document.querySelectorAll('.log-row').forEach(row => {
                        const rowStatus = row.dataset.status;
                        if (status === 'all') {
                            row.style.display = '';
                        } else if (status === 'approved' && rowStatus === 'approved') {
                            row.style.display = '';
                        } else if (status === 'disapproved' && (rowStatus === 'rejected' || rowStatus === 'disapproved')) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                // Add data-status attribute to log rows
                document.querySelectorAll('.log-row').forEach(row => {
                    const statusSpan = row.querySelector('.status-badge');
                    const status = statusSpan.textContent.toLowerCase().trim();
                    row.dataset.status = status === 'rejected' ? 'disapproved' : status;
                });
            });

            // Show alert if exists
            <?php if (isset($_SESSION['alert'])): ?>
                Swal.fire({
                    icon: '<?php echo $_SESSION['alert']['type']; ?>',
                    title: '<?php echo $_SESSION['alert']['message']; ?>',
                    showConfirmButton: false,
                    timer: 2000
                });
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>

            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            'navy': {
                                700: '#000066',
                                800: '#000080',
                            }
                        }
                    }
                }
            }
        </script>
    </div>
</body>
</html>
