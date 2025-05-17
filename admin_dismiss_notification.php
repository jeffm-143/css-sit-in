<?php
// admin_dismiss_notification.php - Handles marking admin notifications as read
session_start();
require_once 'database.php';
header('Content-Type: application/json');

// Check if user is an admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get the input data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if notification_id is provided
if (!isset($data['notification_id']) || empty($data['notification_id'])) {
    echo json_encode(['success' => false, 'message' => 'Notification ID is required']);
    exit();
}

$notification_id = $data['notification_id'];

// Update the reservation status to 'read'
$stmt = $conn->prepare("UPDATE reservations SET admin_read = 1 WHERE id = ?");
$stmt->bind_param("i", $notification_id);

try {
    if (!$stmt->execute()) {
        throw new Exception("Error updating notification: " . $stmt->error);
    }

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No notification found or already processed']);
    }
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>