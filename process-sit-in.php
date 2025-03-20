<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $lab_room = $_POST['lab_room'];
    $purpose = $_POST['purpose'];
    $start_time = date('Y-m-d H:i:s');
    
    // Check for existing active session
    $check = $conn->prepare("SELECT id FROM sit_in_sessions WHERE student_id = ? AND status = 'active'");
    $check->bind_param("s", $student_id);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        $_SESSION['error'] = "Student already has an active sit-in session.";
    } else {
        // Create new sit-in session with simplified fields
        $stmt = $conn->prepare("
            INSERT INTO sit_in_sessions 
            (student_id, lab_room, purpose, start_time, status) 
            VALUES (?, ?, ?, ?, 'active')
        ");
        $stmt->bind_param("ssss", $student_id, $lab_room, $purpose, $start_time);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Sit-in session started successfully.";
        } else {
            $_SESSION['error'] = "Error starting sit-in session.";
        }
    }
}

header("Location: sit-in.php");
exit();
