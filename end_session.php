<?php
// THIS FILE IS COMPLETELY DISABLED
// All handling now happens in sit-in.php
session_start();
require_once 'database.php';

// ALWAYS redirect to sit-in.php
header("Location: sit-in.php");
exit();

// This code is kept for reference but is completely disabled
if (false) { // This condition will never be true
    // Handle direct sit-in timeout only
    $session_id = $_POST['session_id'];
    $student_id = $_POST['student_id'];
    $end_time = date('Y-m-d H:i:s');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update sit-in session status
        $stmt = $conn->prepare("UPDATE sit_in_sessions SET status = 'completed', end_time = ? WHERE id = ?");
        $stmt->bind_param("si", $end_time, $session_id);
        
        if ($stmt->execute()) {
            // Update session count for direct sit-ins
            $count_stmt = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
            $count_stmt->bind_param("s", $student_id);
            $count_stmt->execute();
            $current_session = $count_stmt->get_result()->fetch_assoc()['SESSION'];
            
            $new_session = $current_session - 1;
            $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
            $update_session->bind_param("is", $new_session, $student_id);
            $update_session->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Direct sit-in ended. Student has " . $new_session . " sessions remaining.";
        } else {
            throw new Exception("Failed to end session");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: sit-in.php");
exit();
?>
