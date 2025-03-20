<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id'])) {
    $session_id = $_POST['session_id'];
    $student_id = $_POST['student_id']; // Add this hidden field from the form
    $end_time = date('Y-m-d H:i:s');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // End the sit-in session
        $stmt = $conn->prepare("
            UPDATE sit_in_sessions 
            SET status = 'completed', end_time = ? 
            WHERE id = ? AND status = 'active'
        ");
        $stmt->bind_param("si", $end_time, $session_id);
        $stmt->execute();
        
        // Decrease session count for the student
        $update_session = $conn->prepare("
            UPDATE users 
            SET SESSION = SESSION - 1 
            WHERE ID_NUMBER = ? AND SESSION > 0
        ");
        $update_session->bind_param("s", $student_id);
        $update_session->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Session ended and count updated successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error updating session: " . $e->getMessage();
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
