<?php
session_start();
// Enable error reporting at the top of the file
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add these functions after database connection
function hasActiveSitIn($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM sit_in_sessions 
        WHERE student_id = ? AND status = 'active'
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] > 0;
}

// Update the hasPendingReservation function to include active reservations without timeout
function hasPendingReservation($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE student_id = ? 
        AND (status = 'pending' 
             OR (status = 'approved' AND (timeout_at IS NULL OR timeout_at = '')))
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'] > 0;
}

function hasPendingOrActiveReservation($conn, $student_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reservations 
        WHERE student_id = ? 
        AND status = 'pending'
    ");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['count'] > 0;
}

$message = '';
$reservations = [];

// Get student ID from session
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Consolidate all checks before any output or redirects
$canReserve = true;
$reservationMessage = '';

$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT ID_NUMBER FROM users WHERE USERNAME = ?");
if (!$stmt) {
    error_log("Failed to prepare statement: " . $conn->error);
    die("Database error");
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$student_id = $user['ID_NUMBER'];

// Do all reservation checks at once
if (hasActiveSitIn($conn, $student_id)) {
    $canReserve = false;
    $reservationMessage = 'You cannot make a new reservation while you have an active sit-in session.';
} elseif (hasPendingReservation($conn, $student_id)) {
    $canReserve = false;
    $reservationMessage = 'You cannot make a new reservation until your current reservation is completed.';
}

// Handle new reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lab_room'])) {
    if (!$canReserve) {
        $_SESSION['error_message'] = $reservationMessage;
    } else {
        try {
            // Your existing reservation submission code
            $lab_room = $_POST['lab_room'];
            $pc_number = $_POST['selected_pc'];
            $purpose = $_POST['purpose'];
            $reservation_date = $_POST['date'];
            $time_in = $_POST['time_in'];

            $insert_stmt = $conn->prepare("
                INSERT INTO reservations 
                (student_id, lab_room, pc_number, purpose, reservation_date, time_in, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'pending')
            ");

            $insert_stmt->bind_param("isssss", 
                $student_id, 
                $lab_room, 
                $pc_number, 
                $purpose, 
                $reservation_date,
                $time_in
            );

            if ($insert_stmt->execute()) {
                $_SESSION['success_message'] = "Reservation submitted successfully!";
            } else {
                error_log("Reservation insert error: " . $insert_stmt->error);
                $_SESSION['error_message'] = "Failed to create reservation. Please try again later.";
            }
            $insert_stmt->close();
        } catch (Exception $e) {
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
        }
    }
    header("Location: reservation.php");
    exit();
}

// Get student details including full name
$stmt = $conn->prepare("SELECT CONCAT(FIRSTNAME, ' ', LASTNAME) as fullname FROM users WHERE ID_NUMBER = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$_SESSION['fullname'] = $student['fullname'];

// Fetch reservations for the current student
try {
    error_log("Fetching reservations for student_id: " . $student_id);
    
    $stmt = $conn->prepare("
        SELECT r.* 
        FROM reservations r 
        WHERE r.student_id = ?
        ORDER BY r.reservation_date DESC, r.time_in DESC
    ");

    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("i", $student_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if (!$result) {
        throw new Exception("Result failed: " . $stmt->error);
    }
    
    $reservations = $result->fetch_all(MYSQLI_ASSOC);
    error_log("Found " . count($reservations) . " reservations");
    
} catch (Exception $e) {
    error_log("Error fetching reservations: " . $e->getMessage());
    $message = "Error fetching reservations: " . $e->getMessage();
}

// Store success message for modal
$successMessage = '';
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">' . htmlspecialchars($_SESSION['error_message']) . '</span>
          </div>';
    unset($_SESSION['error_message']);
}

// Get available lab rooms
$lab_rooms = [];
$rooms_query = "SELECT * FROM lab_rooms WHERE status = 'active'";
$rooms_result = $conn->query($rooms_query);
if ($rooms_result) {
    $lab_rooms = $rooms_result->fetch_all(MYSQLI_ASSOC);
}

// Define purposes
$purposes = [
    'C', 
    'C#', 
    'Python', 
    'PHP', 
    'Java', 
    'ASP.Net',
    'Database',
    'Digital Logic & Design',
    'Embedded Systems & Iot',
    'System Integration & Architecture',
    'Computer Application',
    'Web Design & Development'
];

/**
 * Check if the lab is open for a given date and time.
 * @param string $date Format: Y-m-d
 * @param string $time Format: H:i (24-hour)
 * @return bool
 */
function isLabOpen($date, $time) {
    // Prevent reservations on Sundays
    if (empty($date) || empty($time)) {
        return false;
    }
    $dayOfWeek = (int)date('w', strtotime($date)); // 0=Sunday, 1=Monday, ..., 6=Saturday
    $formattedTime = date('H:i', strtotime($time));

    if ($dayOfWeek === 0) {
        // Sunday: lab is closed
        return false;
    }
    if ($dayOfWeek === 6) { // Saturday
        return ($formattedTime >= '08:00' && $formattedTime <= '17:00');
    }
    // Monday to Friday
    return ($formattedTime >= '07:00' && $formattedTime <= '20:00');
}

// Get available computers for a specific lab
if (isset($_GET['get_computers'])) {
    $lab_room = $_GET['lab_room'];
    $date = $_GET['date'];
    $time = $_GET['time'];
    
    // First check if the computers have been properly set up for this lab room
    $check_computers = $conn->prepare("SELECT COUNT(*) as count FROM computers WHERE lab_room_id = ?");
    $check_computers->bind_param("s", $lab_room);
    $check_computers->execute();
    $count = $check_computers->get_result()->fetch_assoc()['count'];
    
    if ($count === 0) {
        // No computers found for this lab room
        header('Content-Type: application/json');
        echo json_encode(['error' => "No computers found for Lab Room {$lab_room}. Please contact administrator."]);
        exit;
    }
    
    $stmt = $conn->prepare("
        SELECT c.*, 
               CASE 
                   WHEN r.id IS NOT NULL AND r.timeout_at IS NULL THEN 'in_use'
                   WHEN c.status = 'maintenance' THEN 'maintenance'
                   ELSE c.status 
               END as current_status
        FROM computers c
        LEFT JOIN reservations r ON c.pc_number = r.pc_number 
            AND r.lab_room = c.lab_room_id
            AND r.reservation_date = ?
            AND r.time_in = ?
            AND r.status = 'approved'
            AND r.timeout_at IS NULL
        WHERE c.lab_room_id = ?
        ORDER BY CAST(SUBSTRING(c.pc_number, 3) AS UNSIGNED)
    ");
    $stmt->bind_param("sss", $date, $time, $lab_room);
    $stmt->execute();
    $computers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($computers);
    exit;
}

// Get filter values
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'pending'; // Default to pending
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$lab_filter = isset($_GET['lab']) ? $_GET['lab'] : '';

// Initialize variables before building query
$types = '';
$params = [];

// Build query with filters and remove auto-timeout functionality
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
function checkReservation($time_in, $reservation_date) {
    $now = new DateTime();
    $date = new DateTime($reservation_date);
    $start = new DateTime($reservation_date . ' ' . $time_in);

    
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Reservations</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gradient-to-r from-blue-50 to-blue-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Dashboard</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex items-center space-x-6">
                        <!-- Notification Bell -->
                        <li class="relative">
                            <button id="notificationButton" class="text-white hover:text-yellow-400 transition-colors">
                                <i class="fas fa-bell text-xl"></i>
                                <?php
                                $notif_count = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE ID_NUMBER = ? AND is_read = 0");
                                $notif_count->bind_param("i", $student_id);
                                $notif_count->execute();
                                $count = $notif_count->get_result()->fetch_assoc()['count'];
                                if ($count > 0):
                                ?>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="notificationCount">
                                    <?php echo $count; ?>
                                </span>
                                <?php endif; ?>
                            </button>
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto" id="notificationList">
                                    <?php                                    $notifications_query = $conn->prepare("
                                        SELECT * FROM notifications 
                                        WHERE ID_NUMBER = ? AND is_read = 0 
                                        ORDER BY created_at DESC LIMIT 5
                                    ");
                                    $notifications_query->bind_param("i", $student_id);
                                    $notifications_query->execute();
                                    $notifications = $notifications_query->get_result();
                                    
                                    if ($notifications->num_rows > 0):
                                        while($notif = $notifications->fetch_assoc()):
                                    ?>                                        <div class="notification-item p-4 border-b border-gray-100 hover:bg-gray-50 <?php echo $notif['is_read'] ? 'opacity-50' : ''; ?>"
                                             data-notification-id="<?php echo $notif['id']; ?>" 
                                             style="transition: opacity 0.3s ease-out;">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm text-gray-800"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></p>
                                                </div>
                                                <?php if (!$notif['is_read']): ?>
                                                <button onclick="markAsRead(<?php echo $notif['id']; ?>, this)" 
                                                        class="text-xs text-blue-600 hover:text-blue-800 ml-2">
                                                    Mark as read
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="p-4 text-center text-gray-500">
                                            No notifications
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <li><a href="dashboard.php" class="text-yellow-400 font-bold transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="history.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-history mr-1"></i>History</a></li>
                        <li><a href="user_labsched.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-clock mr-1"></i>Lab Schedule</a></li>
                        <li><a href="user_resources.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-book mr-1"></i>Lab Resources</a></li>
                        <li><a href="reservation.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-alt mr-1"></i>Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-indigo-900 px-6 py-2 rounded-lg font-bold hover:bg-yellow-500 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-out-alt mr-1"></i>Log out
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Operating Hours and Guidelines -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">Lab Operating Hours</h3>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Monday - Friday</span>
                            <span class="text-gray-900 font-medium">7:00 AM - 8:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Saturday</span>
                            <span class="text-gray-900 font-medium">8:00 AM - 5:00 PM</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Sunday & Holidays</span>
                            <span class="text-gray-900 font-medium">Closed</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white">Guidelines</h3>
                    </div>
                    <div class="px-6 py-5">
                        <ul class="space-y-3">
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-600">Reservations can be made up to 7 days in advance</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-600">Standard sessions are 1 hour in duration</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-600">Arrive 5 minutes before your scheduled time slot</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-gray-600">No-shows will result in session point deduction</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- New Reservation Form -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">New Reservation</h3>
                </div>
                <div class="px-6 py-5">
                    <?php if (!$canReserve): ?>
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700"><?php echo htmlspecialchars($reservationMessage); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <form id="reservationForm" method="POST" class="space-y-6">
                            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Student ID</label>
                                    <input type="text" value="<?php echo htmlspecialchars($student_id); ?>" readonly 
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Student Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['fullname'] ?? ''); ?>" readonly 
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Lab Room</label>
                                    <select name="lab_room" id="labRoom" required 
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Lab Room</option>
                                        <?php foreach ($lab_rooms as $room): ?>
                                            <option value="<?php echo htmlspecialchars($room['room_number']); ?>">
                                                Room <?php echo htmlspecialchars($room['room_number']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Purpose</label>
                                    <select name="purpose" required 
                                            class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Purpose</option>
                                        <?php foreach ($purposes as $purpose): ?>
                                            <option value="<?php echo htmlspecialchars($purpose); ?>">
                                                <?php echo htmlspecialchars($purpose); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Date</label>
                                    <input type="date" name="date" id="reservationDate" required 
                                           min="<?php echo date('Y-m-d'); ?>"
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium text-gray-700">Time</label>
                                    <input type="time" name="time_in" id="timeIn" required 
                                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <!-- Computer Selection Grid -->
                            <div id="computerGrid" class="hidden mt-6">
                                <h4 class="text-lg font-medium text-gray-900 mb-4">Select a Computer</h4>
                                <div class="grid grid-cols-5 sm:grid-cols-8 md:grid-cols-10 gap-4" id="computersContainer">
                                    <!-- Computers will be loaded here -->
                                </div>
                            </div>

                            <input type="hidden" name="selected_pc" id="selectedPC">
                            
                            <div class="flex justify-end">
                                <button type="submit" name="submit_reservation" id="submitBtn" 
                                        class="px-6 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg font-semibold hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 disabled:opacity-50"
                                        disabled>
                                    Submit Reservation
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Existing Reservations -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <h3 class="text-lg font-semibold text-white">Your Reservations</h3>
                </div>
                <div class="px-6 py-5">
                    <?php if (empty($reservations)): ?>
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No reservations</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by creating a new reservation.</p>
                        </div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Room</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($reservations as $reservation): ?>
                                        <?php if ($reservation['student_id'] == $student_id): ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($reservation['lab_room']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($reservation['pc_number']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php 
                                                if (!empty($reservation['time_in'])) {
                                                    echo date('h:i A', strtotime($reservation['time_in']));
                                                } else {
                                                    echo 'Time not set';
                                                }
                                                ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($reservation['purpose']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                                    <?php
                                                    switch($reservation['status']) {
                                                        case 'approved':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'rejected':
                                                            echo 'bg-red-100 text-red-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>">
                                                    <?php echo ucfirst(htmlspecialchars($reservation['status'])); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Reservation</h3>
                <div class="mt-2 space-y-4">
                    <div id="modalContent" class="text-sm text-gray-500"></div>
                </div>
                <div class="mt-5 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('confirmationModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button id="modalDoneBtn"
                            class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-blue-700 border border-transparent rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications -->
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const notificationButton = document.getElementById('notificationButton');
            const notificationDropdown = document.getElementById('notificationDropdown');
            
            notificationButton.addEventListener('click', function(e) {
                e.stopPropagation();
                notificationDropdown.classList.toggle('hidden');
            });
            
            document.addEventListener('click', function(e) {
                if (!notificationDropdown.contains(e.target) && e.target !== notificationButton) {
                    notificationDropdown.classList.add('hidden');
                }
            });
        });        function markAsRead(notificationId, button) {
            fetch('dismiss_notification.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the UI - fade out and remove notification item completely
                    const notifItem = button.closest('.notification-item');
                    notifItem.style.opacity = '0';
                    
                    // Remove the notification after animation completes
                    setTimeout(() => {
                        // Actually remove the element from DOM
                        notifItem.remove();
                        
                        // Check if no notifications left
                        const notificationList = document.getElementById('notificationList');
                        const remainingItems = notificationList.querySelectorAll('.notification-item');
                        if (remainingItems.length === 0) {
                            notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">No notifications</div>';
                        }
                    }, 300);

                    // Update the notification counter
                    const counter = document.getElementById('notificationCount');
                    if (counter) {
                        const currentCount = parseInt(counter.textContent);
                        if (currentCount > 1) {
                            counter.textContent = currentCount - 1;
                        } else {
                            counter.remove();
                        }
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

    <script>
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
        let selectedComputer = null;

        function loadComputers() {
            const labRoom = document.getElementById('labRoom').value;
            const date = document.getElementById('reservationDate').value;
            const time = document.getElementById('timeIn').value;

            if (!labRoom || !date || !time) {
                document.getElementById('computerGrid').classList.add('hidden');
                return;
            }

            document.getElementById('computerGrid').classList.remove('hidden');
            fetch(`reservation.php?get_computers=1&lab_room=${encodeURIComponent(labRoom)}&date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}`)
                .then(response => response.json())
                .then(computers => {
                    const container = document.getElementById('computersContainer');
                    container.innerHTML = '';
                    computers.forEach(pc => {
                        const isAvailable = pc.current_status === 'available';
                        const statusClass = getStatusClass(pc.current_status);
                        const iconClass = getStatusIconClass(pc.current_status);
                        const textClass = getStatusTextClass(pc.current_status);
                        
                        const div = document.createElement('div');
                        div.className = `p-4 text-center rounded-lg border ${statusClass} transition-all duration-300 ${isAvailable ? 'cursor-pointer hover:shadow-md' : ''}`;
                        if (isAvailable) {
                            div.onclick = () => selectComputer(pc.pc_number, div);
                        }
                        div.innerHTML = `
                            <i class="fas fa-desktop text-3xl ${iconClass}"></i>
                            <p class="mt-2 font-medium">${pc.pc_number}</p>
                            <p class="text-xs ${textClass}">${formatStatus(pc.current_status)}</p>
                        `;
                        container.appendChild(div);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('computerGrid').classList.add('hidden');
                });
        }

        function getStatusClass(status) {
            switch(status) {
                case 'available':
                    return 'bg-green-50';
                case 'in_use':
                    return 'bg-yellow-50';
                case 'maintenance':
                    return 'bg-red-50';
                default:
                    return 'bg-gray-50';
            }
        }

        function getStatusIconClass(status) {
            switch(status) {
                case 'available':
                    return 'text-green-500';
                case 'in_use':
                    return 'text-yellow-500';
                case 'maintenance':
                    return 'text-red-500';
                default:
                    return 'text-gray-500';
            }
        }

        function getStatusTextClass(status) {
            switch(status) {
                case 'available':
                    return 'text-green-600';
                case 'in_use':
                    return 'text-yellow-600';
                case 'maintenance':
                    return 'text-red-600';
                default:
                    return 'text-gray-600';
            }
        }

        function formatStatus(status) {
            return status.split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        }

        // Event listeners for form inputs
        document.getElementById('labRoom').addEventListener('change', loadComputers);
        document.getElementById('reservationDate').addEventListener('change', loadComputers);
        document.getElementById('timeIn').addEventListener('change', loadComputers);

        function selectComputer(pcNumber, element) {
            if (selectedComputer) {
                selectedComputer.classList.remove('ring-2', 'ring-blue-500');
            }
            selectedComputer = element;
            element.classList.add('ring-2', 'ring-blue-500');
            document.getElementById('selectedPC').value = pcNumber;
            document.getElementById('submitBtn').disabled = false;
        }        // Time validation function
        function validateTime(time) {
            const selectedTime = new Date(`2000-01-01 ${time}`);
            const minTime = new Date(`2000-01-01 07:00`);
            const maxTime = new Date(`2000-01-01 20:00`);
            const selectedDate = document.getElementById('reservationDate').value;
            
            // Only check day of week if a date is actually selected
            if (selectedDate) {
                // Parse the date correctly: YYYY-MM-DD format
                const [year, month, day] = selectedDate.split('-').map(Number);
                const date = new Date(year, month - 1, day); // Month is 0-indexed in JavaScript
                const dayOfWeek = date.getDay();
                console.log("In validateTime - Selected date:", selectedDate, "Day of week:", dayOfWeek);
                
                // Sunday is closed
                if (dayOfWeek === 0) {
                    alert('Lab is closed on Sundays');
                    return false;
                }

                // Saturday has different hours
                if (dayOfWeek === 6) {
                    if (selectedTime < new Date(`2000-01-01 08:00`) || 
                        selectedTime > new Date(`2000-01-01 17:00`)) {
                        alert('Saturday hours are 8:00 AM to 5:00 PM');
                        return false;
                    }
                } else {
                    // Weekday validation
                    if (selectedTime < minTime || selectedTime > maxTime) {
                        alert('Weekday hours are 7:00 AM to 8:00 PM');
                        return false;
                    }
                }            }
            return true;
        }

        // Add validation to time input
        document.getElementById('timeIn').addEventListener('change', function() {
            if (this.value && !validateTime(this.value)) {
                this.value = '';
            } else if (this.value) {
                // If time is valid and we have all three required fields, load computers
                loadComputers();
            }
        });        // Date validation function
        function validateDate(selectedDate) {
            // Force proper date parsing with YYYY-MM-DD format
            const [year, month, day] = selectedDate.split('-').map(Number);
            const date = new Date(year, month - 1, day); // Month is 0-indexed in JavaScript
            
            // Get day of week (0 = Sunday, 1 = Monday, etc.)
            const dayOfWeek = date.getDay();
            console.log("Selected date:", selectedDate, "Day of week:", dayOfWeek);
            
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            date.setHours(0, 0, 0, 0);

            if (dayOfWeek === 0) {
                alert('Reservations are not available on Sundays. Please select another date.');
                document.getElementById('reservationDate').value = '';
                return false;
            }

            if (date < today) {
                alert('Please select a date from today onwards.');
                document.getElementById('reservationDate').value = '';
                return false;
            }

            return true;
        }
        
        // Add validation to date input
        document.getElementById('reservationDate').addEventListener('change', function() {
            if (this.value) {
                if (!validateDate(this.value)) {
                    this.value = '';
                } else {
                    // If we have lab room and time filled, load computers
                    const labRoom = document.getElementById('labRoom').value;
                    const time = document.getElementById('timeIn').value;
                    if (labRoom && time) {
                        loadComputers();
                    }
                }
            }
        });

        // Update form submission validation
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const required = ['student_id', 'lab_room', 'selected_pc', 'purpose', 'date', 'time_in'];
            const missing = required.filter(field => !formData.get(field));
            
            if (missing.length > 0) {
                alert('Please fill in all required fields');
                return;
            }

            if (!validateTime(formData.get('time_in'))) {
                return;
            }

            // Show confirmation modal
            document.getElementById('modalContent').innerHTML = `
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="font-medium">Lab Room:</span>
                        <span>${formData.get('lab_room')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Computer:</span>
                        <span>PC ${formData.get('selected_pc')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Date:</span>
                        <span>${new Date(formData.get('date')).toLocaleDateString()}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Time:</span>
                        <span>${formData.get('time_in')}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-medium">Purpose:</span>
                        <span>${formData.get('purpose')}</span>
                    </div>
                    <p class="mt-4 text-yellow-600 text-center">Please review your reservation details before confirming.</p>
                </div>
            `;

            document.getElementById('confirmationModal').classList.remove('hidden');
        });

        document.getElementById('modalDoneBtn').addEventListener('click', function() {
            document.getElementById('confirmationModal').classList.add('hidden');
            document.getElementById('reservationForm').submit();
        });

          </script>    <!-- Success Modal -->
    <div id="successModal" class="<?php echo empty($successMessage) ? 'hidden' : ''; ?> fixed inset-0 bg-gray-800 bg-opacity-75 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl p-6 max-w-md w-full transform transition-all">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-6">
                    <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-xl leading-6 font-bold text-gray-900 mb-2">Success!</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-lg text-gray-600"><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
                <div class="mt-5">
                    <button id="closeSuccessModal" class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white py-3 px-6 rounded-xl hover:from-blue-700 hover:to-blue-800 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($successMessage)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const successModal = document.getElementById('successModal');
            const closeBtn = document.getElementById('closeSuccessModal');
            
            closeBtn.addEventListener('click', function() {
                successModal.classList.add('hidden');
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>