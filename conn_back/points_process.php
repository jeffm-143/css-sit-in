<?php
session_start();
include(__DIR__ . '/../db_connection.php');

// Initialize variables with safe default values
$students = [];
$recentPoints = [];
$totalAssignments = 0;
$totalPointsAwarded = 0;
$studentsWithPoints = 0;
$sessionsAwarded = 0;

// Fetch all students with their points info and ensure complete data
$query = "SELECT u.*, 
          CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) as name,
          COALESCE(u.current_points, 0) as current_points,
          COALESCE(u.total_points_earned, 0) as total_points_earned,
          COALESCE(u.SESSION, 0) as SESSION,
          COALESCE(u.sessions_earned, 0) as sessions_earned
          FROM users u 
          WHERE u.user_type='student' 
          ORDER BY u.LASTNAME ASC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Ensure all required fields have at least a zero value
        $row['current_points'] = isset($row['current_points']) ? (int)$row['current_points'] : 0;
        $row['total_points_earned'] = isset($row['total_points_earned']) ? (int)$row['total_points_earned'] : 0;
        $row['SESSION'] = isset($row['SESSION']) ? (int)$row['SESSION'] : 0;
        $row['sessions_earned'] = isset($row['sessions_earned']) ? (int)$row['sessions_earned'] : 0;
        
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

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $recentPoints[] = $row;
    }
}

// Calculate statistics with safety checks
$query = "SELECT 
            COUNT(DISTINCT id) as total_assignments,
            COALESCE(SUM(points_earned), 0) as total_points,
            COUNT(DISTINCT student_id) as students_with_points,
            SUM(CASE WHEN converted_to_session = 1 THEN 1 ELSE 0 END) as sessions_awarded
          FROM points";
$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    // Ensure all stats have at least a zero value
    $totalAssignments = isset($row['total_assignments']) ? (int)$row['total_assignments'] : 0;
    $totalPointsAwarded = isset($row['total_points']) ? (int)$row['total_points'] : 0;
    $studentsWithPoints = isset($row['students_with_points']) ? (int)$row['students_with_points'] : 0;
    $sessionsAwarded = isset($row['sessions_awarded']) ? (int)$row['sessions_awarded'] : 0;
}

// Handle point assignment
if (isset($_POST['assign_points'])) {
    $student_id = $_POST['student_id'];
    $points = (int)$_POST['points'];
    $reason = $_POST['reason'];
    $awarded_by = $_SESSION['username'];
    
    // Check if student has reached max sessions (to prevent unnecessary points)
    $stmt = $conn->prepare("SELECT SESSION FROM users WHERE ID = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $student = $result->fetch_assoc()) {
        if ($student['SESSION'] >= 30) {
            $_SESSION['error_message'] = "This student has reached maximum sessions. Cannot award more points.";
            header("Location: ../admin-points.php");
            exit();
        }
    }
    
    $stmt = $conn->prepare("INSERT INTO points (student_id, points_earned, points_reason, awarded_by) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $student_id, $points, $reason, $awarded_by);
    
    if ($stmt->execute()) {
        // Update student's current points
        $stmt = $conn->prepare("UPDATE users SET current_points = current_points + ?, total_points_earned = total_points_earned + ? WHERE ID = ?");
        $stmt->bind_param("iii", $points, $points, $student_id);
        $stmt->execute();
        
        $_SESSION['success_message'] = "Points awarded successfully!";
    } else {
        $_SESSION['error_message'] = "Error awarding points.";
    }
    
    header("Location: ../admin-points.php");
    exit();
}

// Handle point conversion
if (isset($_POST['convert_points'])) {
    $student_id = $_POST['student_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if student has 3 points and hasn't reached max sessions
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
            
            // Mark points as converted - find the 3 oldest unconverted points
            $stmt = $conn->prepare("UPDATE points SET converted_to_session = 1 
                                   WHERE student_id = ? AND converted_to_session = 0 
                                   ORDER BY awarded_date ASC LIMIT 3");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success_message'] = "Points successfully converted to a bonus session!";
        } else {
            $conn->rollback();
            if ($student['current_sessions'] >= 30) {
                $_SESSION['error_message'] = "Maximum sessions (30) already reached.";
            } else {
                $_SESSION['error_message'] = "Not enough points for conversion. Need 3 points.";
            }
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error converting points: " . $e->getMessage();
    }
    
    header("Location: ../admin-points.php");
    exit();
}
?>