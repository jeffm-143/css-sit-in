<?php
header('Content-Type: application/json');
require_once 'database.php';

if (!isset($_GET['lab_room'])) {
    echo json_encode(['error' => 'Lab room is required']);
    exit;
}

$lab_room = $_GET['lab_room'];
$date = $_GET['date'] ?? date('Y-m-d');
$time = $_GET['time'] ?? date('H:i:s');

try {
    // Get computers with their current status
    $stmt = $conn->prepare("
        SELECT c.*, 
            CASE 
                WHEN r.status IN ('approved', 'pending') THEN 'in_use'
                ELSE c.status 
            END as current_status
        FROM computers c
        LEFT JOIN reservations r ON c.pc_number = r.pc_number 
            AND c.lab_room_id = r.lab_room
            AND r.reservation_date = ?
            AND r.time_in = ?
        WHERE c.lab_room_id = ?
        ORDER BY CAST(SUBSTRING(c.pc_number, 3) AS UNSIGNED)
    ");
    
    $stmt->bind_param("sss", $date, $time, $lab_room);
    $stmt->execute();
    
    $result = $stmt->get_result();
    $computers = $result->fetch_all(MYSQLI_ASSOC);
    
    echo json_encode($computers);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>