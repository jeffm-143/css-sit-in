<?php
session_start();
require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reservation_id = $_POST['reservation_id'];
    $student_id = $_POST['student_id'];
    $lab_room = $_POST['lab_room'];
    $purpose = $_POST['purpose'];

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Insert into sit_in_sessions
        $stmt = $conn->prepare("INSERT INTO sit_in_sessions (student_id, lab_room, purpose, start_time, status) VALUES (?, ?, ?, NOW(), 'active')");
        $stmt->bind_param("sss", $student_id, $lab_room, $purpose);
        $stmt->execute();

        // Update reservation status
        $stmt = $conn->prepare("UPDATE reservations SET status = 'approved', timeout_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();

        // Commit transaction
        $conn->commit();
        $_SESSION['success'] = "Sit-in session started successfully.";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['error'] = "Failed to start sit-in session: " . $e->getMessage();
    }

    $stmt->close();
    header("Location: view-sit-in.php");
    exit();
}

header("Location: view-sit-in.php");
exit();
?>