<?php
session_start();

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch the logged-in user's ID
$user = $_SESSION['username']; 

// Fetch sit-in session history for the logged-in user
$sql = "SELECT s.id, u.ID_NUMBER, CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) AS Name, 
               s.purpose, s.lab_room, s.start_time, s.end_time, DATE(s.start_time) AS date, s.status 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE u.USERNAME = ?
        ORDER BY s.start_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session History - CCS Sit-in Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">

<!-- Navigation -->
<header class="bg-navy-800 shadow-lg">
    <div class="container mx-auto px-4">
        <nav class="flex items-center justify-between h-16">
            <h2 class="text-2xl font-bold text-white">History</h2>
            <div class="flex items-center space-x-8">
                <ul class="flex space-x-6">
                    <li><a href="#" class="text-white hover:text-yellow-400 transition">Notification</a></li>
                    <li><a href="dashboard.php" class="text-white hover:text-yellow-400 transition">Home</a></li>
                    <li><a href="edit_profile.php" class="text-white hover:text-yellow-400 transition">Edit Profile</a></li>
                    <li><a href="history.php" class="text-yellow-400 font-bold transition">History</a></li>
                    <li><a href="reservation.php" class="text-white hover:text-yellow-400 transition">Reservation</a></li>
                </ul>
                <a href="logout.php" class="bg-yellow-400 text-navy-800 px-4 py-2 rounded-lg font-bold hover:bg-navy-800 hover:text-yellow-400 transition duration-300">Log out</a>
            </div>
        </nav>
    </div>
</header>

<!-- Main Content -->
<div class="container mx-auto p-6">
    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Your Sit-in Session History</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto bg-white shadow-lg rounded-lg overflow-hidden">
                <thead class="bg-blue-500 text-white">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Purpose</th>
                        <th class="px-4 py-2">Lab Room</th>
                        <th class="px-4 py-2">Start Time</th>
                        <th class="px-4 py-2">End Time</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['ID_NUMBER']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['Name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['lab_room']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['start_time']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['end_time']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td class="px-4 py-2">
                                <a href="feedback.php?session_id=<?php echo $row['id']; ?>" 
                                   class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-700 transition">
                                    Feedback
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500 mt-4">No session history found.</p>
    <?php endif; ?>

</div>

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'navy': {
                        800: '#000080',
                    }
                }
            }
        }
    }
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
