<?php
session_start();
include('db_connection.php');

// Initialize variables
$students = [];
$recentPoints = [];
$totalAssignments = 0;
$totalPointsAwarded = 0;
$studentsWithPoints = 0;
$sessionsAwarded = 0;

// Fetch all students with their points info
$query = "SELECT *, CONCAT(FIRSTNAME, ' ', LASTNAME) as name FROM users WHERE user_type='student' ORDER BY LASTNAME ASC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch recent point activity
$query = "SELECT p.*, CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) as student_name 
          FROM points p 
          JOIN users u ON p.student_id = u.ID 
          WHERE u.user_type='student'
          ORDER BY p.awarded_date DESC 
          LIMIT 10";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $recentPoints[] = $row;
    }
}

// Calculate statistics
$query = "SELECT 
            COUNT(DISTINCT id) as total_assignments,
            SUM(points_earned) as total_points,
            COUNT(DISTINCT student_id) as students_with_points,
            SUM(CASE WHEN converted_to_session = 1 THEN 1 ELSE 0 END) as sessions_awarded
          FROM points";
$result = $conn->query($query);

if ($result) {
    $stats = $result->fetch_assoc();
    $totalAssignments = $stats['total_assignments'];
    $totalPointsAwarded = $stats['total_points'];
    $studentsWithPoints = $stats['students_with_points'];
    $sessionsAwarded = $stats['sessions_awarded'];
}

// Handle point assignment
if (isset($_POST['assign_points'])) {
    $student_id = $_POST['student_id'];
    $points = $_POST['points'];
    $reason = $_POST['reason'];
    $awarded_by = $_SESSION['username'];
    
    $stmt = $conn->prepare("INSERT INTO points (student_id, points_earned, points_reason, awarded_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $student_id, $points, $reason, $awarded_by);
    
    if ($stmt->execute()) {        // Update student's current points
        $stmt = $conn->prepare("UPDATE users SET current_points = current_points + ?, total_points_earned = total_points_earned + ? WHERE ID = ?");
        $stmt->bind_param("iii", $points, $points, $student_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Points awarded successfully!";
    } else {
        $_SESSION['error_message'] = "Error awarding points.";
    }
    
    header("Location: ../admin_points.php");
    exit();
}

// Handle point conversion
if (isset($_POST['convert_points'])) {
    $student_id = $_POST['student_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {        // Check if student has 3 points
        $stmt = $conn->prepare("SELECT current_points, SESSION as current_sessions FROM users WHERE ID = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        
        if ($student['current_points'] >= 3 && $student['current_sessions'] < 30) {
            // Deduct points and add session
            $stmt = $conn->prepare("UPDATE users SET current_points = current_points - 3, sessions_earned = sessions_earned + 1, SESSION = SESSION + 1 WHERE ID = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            
            // Mark points as converted
            $stmt = $conn->prepare("UPDATE points SET converted_to_session = 1 WHERE student_id = ? AND converted_to_session = 0 LIMIT 3");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success_message'] = "Points successfully converted to a bonus session!";
        } else {
            $conn->rollback();
            $_SESSION['error_message'] = "Not enough points or maximum sessions reached.";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error converting points.";
    }
    
    header("Location: ../admin_points.php");
    exit();
}
?>