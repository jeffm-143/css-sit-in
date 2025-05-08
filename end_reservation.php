<?php
session_start();
require_once 'database.php';

if (isset($_POST['end_reservation'])) {
    $reservation_id = $_POST['reservation_id'];
    $student_id = $_POST['student_id'];
    $end_time = date('Y-m-d H:i:s');
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update reservation status
        $stmt = $conn->prepare("UPDATE reservations SET status = 'completed', timeout_at = ? WHERE id = ?");
        $stmt->bind_param("si", $end_time, $reservation_id);
        
        if ($stmt->execute()) {
            // Update session count
            $count_stmt = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
            $count_stmt->bind_param("s", $student_id);
            $count_stmt->execute();
            $current_session = $count_stmt->get_result()->fetch_assoc()['SESSION'];
            
            $new_session = $current_session - 1;
            $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
            $update_session->bind_param("is", $new_session, $student_id);
            $update_session->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Reservation completed. Student has " . $new_session . " sessions remaining.";
        } else {
            throw new Exception("Failed to complete reservation");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

header("Location: sit-in.php");
exit();
?>