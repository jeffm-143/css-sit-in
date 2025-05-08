<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'ccs-sit-in';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

// Handle GET requests for PC availability
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['get_available_pcs'])) {
    $lab_room = $_GET['lab_room'];
    $date = $_GET['date'];
    $time = $_GET['time'];

    // Get all PCs in the lab room
    $query = "SELECT pc_number FROM lab_computers 
              WHERE lab_room = ? AND pc_number NOT IN (
                  SELECT pc_number FROM reservations 
                  WHERE lab_room = ? 
                  AND reservation_date = ? 
                  AND time_in = ?
                  AND status != 'rejected'
              )";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssss", $lab_room, $lab_room, $date, $time);
    $stmt->execute();
    $result = $stmt->get_result();

    $available_pcs = [];
    while ($row = $result->fetch_assoc()) {
        $available_pcs[] = ['pc_number' => $row['pc_number']];
    }

    echo json_encode(['success' => true, 'data' => $available_pcs]);
    exit;
}

// Handle POST requests for new reservations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'User not logged in']);
        exit;
    }

    $student_id = $_SESSION['user_id'];
    $purpose = $_POST['purpose'];
    $lab_room = $_POST['labRoom'];
    $pc_number = $_POST['pcNumber'];
    $date = $_POST['date'];
    $time_in = $_POST['timeIn'];

    // Check session credits
    $credits_query = "SELECT credits_remaining FROM session_credits WHERE student_id = ?";
    $stmt = $conn->prepare($credits_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $credits_result = $stmt->get_result();
    
    if ($credits_result->fetch_assoc()['credits_remaining'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'No available session credits']);
        exit;
    }

    // Check if PC is still available
    $check_query = "SELECT id FROM reservations 
                   WHERE lab_room = ? AND pc_number = ? 
                   AND reservation_date = ? AND time_in = ?
                   AND status != 'rejected'";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("siss", $lab_room, $pc_number, $date, $time_in);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This PC is no longer available']);
        exit;
    }

    // Insert reservation
    $insert_query = "INSERT INTO reservations (student_id, purpose, lab_room, pc_number, reservation_date, time_in) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ississ", $student_id, $purpose, $lab_room, $pc_number, $date, $time_in);
    
    if ($stmt->execute()) {
        // Deduct session credit
        $update_credits = "UPDATE session_credits 
                         SET credits_remaining = credits_remaining - 1 
                         WHERE student_id = ?";
        $stmt = $conn->prepare($update_credits);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Reservation submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit reservation']);
    }
    exit;
}

$conn->close();
?>