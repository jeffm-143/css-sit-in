<?php
session_start();
require_once 'database.php';

// Update query to order by sit-in ID
$active_sessions = $conn->query("
    SELECT 
        s.id as sit_id,
        u.ID_NUMBER as IDNO,
        u.FIRSTNAME,
        u.LASTNAME,
        s.purpose,
        s.lab_room,
        u.SESSION,
        s.status,
        TIMESTAMPDIFF(MINUTE, s.start_time, CURRENT_TIMESTAMP) as elapsed_time
    FROM sit_in_sessions s
    JOIN users u ON s.student_id = u.ID_NUMBER
    WHERE s.status = 'active'
    ORDER BY s.id ASC
");

if (isset($_POST['end_session'])) {
    $session_id = $_POST['session_id'];
    $student_id = $_POST['student_id'];
    $end_time = date('Y-m-d H:i:s');
    
    // Get current session count before updating
    $session_query = $conn->prepare("SELECT SESSION FROM users WHERE ID_NUMBER = ?");
    $session_query->bind_param("s", $student_id);
    $session_query->execute();
    $current_session = $session_query->get_result()->fetch_assoc()['SESSION'];
    
    // Update sit-in session status
    $stmt = $conn->prepare("UPDATE sit_in_sessions SET status = 'completed', end_time = ? WHERE id = ?");
    $stmt->bind_param("si", $end_time, $session_id);
    
    if ($stmt->execute()) {
        // Decrease session count
        $new_session = $current_session - 1;
        $update_session = $conn->prepare("UPDATE users SET SESSION = ? WHERE ID_NUMBER = ?");
        $update_session->bind_param("is", $new_session, $student_id);
        $update_session->execute();
        
        echo "<script>
            alert('Session ended successfully! Student has " . $new_session . " sessions remaining.');
            window.location.href = 'sit-in.php';
        </script>";
    }
    $stmt->close();
    exit();
}

// Function to check if student has active sit-in
function hasActiveSitIn($conn, $student_id) {
    $stmt = $conn->prepare("SELECT id FROM sit_in_sessions WHERE student_id = ? AND status = 'active'");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $purpose = $_POST['purpose'];
    $lab_room = $_POST['lab_room'];


    // Existing sit-in creation code
    $stmt = $conn->prepare("INSERT INTO sit_in_sessions (student_id, purpose, lab_room, status) VALUES (?, ?, ?, 'active')");
    // ...existing code...
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Sit-in Sessions</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6">Current Sit in</h2>
            
            <?php if ($active_sessions->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full table-auto">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2">IDNO</th>
                                <th class="px-4 py-2">First Name</th>
                                <th class="px-4 py-2">Last Name</th>
                                <th class="px-4 py-2">Purpose</th>
                                <th class="px-4 py-2">Laboratory</th>
                                <th class="px-4 py-2">Session</th>
                                <th class="px-4 py-2">Status</th>
                                <th class="px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($session = $active_sessions->fetch_assoc()): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($session['IDNO']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($session['FIRSTNAME']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($session['LASTNAME']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($session['purpose']); ?></td>
                                    <td class="border px-4 py-2">Room <?php echo htmlspecialchars($session['lab_room']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($session['SESSION']); ?></td>
                                    <td class="border px-4 py-2">
                                        <span class="px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="border px-4 py-2">
                                        <form method="POST" action="end_session.php" class="inline text-center">
                                            <input type="hidden" name="session_id" value="<?php echo $session['sit_id']; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo $session['IDNO']; ?>">
                                            <button type="submit" name="end_session" 
                                                    class="bg-red-600 text-white px-4 py-1 rounded hover:bg-red-700 block mx-auto">
                                                Time Out
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-8 text-gray-500">
                    No active sit-in sessions at the moment.
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
