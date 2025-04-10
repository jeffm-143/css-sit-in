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

// Ensure user_id is fetched from the session
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}
$user_id = $_SESSION['user_id']; // Fetch user ID from session

// Update the feedback insertion query
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['session_id'], $_POST['feedback'])) {
    $session_id = intval($_POST['session_id']);
    $feedback = preg_replace("/\r\n|\r|\n/", " ", $conn->real_escape_string($_POST['feedback']));

    $sql = "INSERT INTO feedback (session_id, user_id, comments) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $session_id, $user_id, $feedback);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Feedback submitted successfully!';
    } else {
        $_SESSION['message'] = 'Failed to submit feedback.';
    }

    $stmt->close();
    header('Location: history.php');
    exit();
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
                    <li><a href="history.php" class="text-white hover:text-yellow-400 transition">History</a></li>
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
                <thead class="bg-gray-300">
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
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo htmlspecialchars($row['ID_NUMBER']); ?></td>
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo htmlspecialchars($row['Name']); ?></td>
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo nl2br(htmlspecialchars(str_replace("\r\n", "\n", $row['purpose']))); ?></td>
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo nl2br(htmlspecialchars(str_replace("\r\n", "\n", $row['lab_room']))); ?></td>
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo nl2br(htmlspecialchars(str_replace("\r\n", "\n", $row['start_time']))); ?></td>
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo nl2br(htmlspecialchars(str_replace("\r\n", "\n", $row['end_time']))); ?></td>
                            <td class="px-4 py-2 text-gray-800 text-center"><?php echo nl2br(htmlspecialchars(str_replace("\r\n", "\n", $row['status']))); ?></td>
                            <td class="px-4 py-2 text-center">
                                <a href="feedback.php?session_id=<?php echo $row['id']; ?>" 
                                   data-session-id="<?php echo $row['id']; ?>"
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

<!-- Feedback Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg shadow-lg w-1/3">
        <div class="p-4 border-b">
            <h3 class="text-lg font-bold">Submit Feedback</h3>
        </div>
        <div class="p-4">
            <textarea id="feedbackText" class="w-full border rounded-lg p-2" rows="5" placeholder="Type your feedback here..."></textarea>
        </div>
        <div class="flex justify-end p-4 border-t">
            <button id="cancelButton" class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2 hover:bg-gray-700">Cancel</button>
            <button id="submitFeedbackButton" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Submit Feedback</button>
        </div>
    </div>
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

    document.addEventListener('DOMContentLoaded', () => {
        const feedbackModal = document.getElementById('feedbackModal');
        const feedbackButtons = document.querySelectorAll('a[href^="feedback.php"]');
        const cancelButton = document.getElementById('cancelButton');
        const submitFeedbackButton = document.getElementById('submitFeedbackButton');
        const feedbackText = document.getElementById('feedbackText');
        let sessionId = null;

        feedbackButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                sessionId = button.getAttribute('data-session-id');
                feedbackModal.classList.remove('hidden');
            });
        });

        cancelButton.addEventListener('click', () => {
            feedbackModal.classList.add('hidden');
        });

        const submitFeedback = () => {
            if (feedbackText.value.trim() === '') {
                alert('Please enter your feedback.');
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'history.php';

            const sessionInput = document.createElement('input');
            sessionInput.type = 'hidden';
            sessionInput.name = 'session_id';
            sessionInput.value = sessionId;

            const feedbackInput = document.createElement('input');
            feedbackInput.type = 'hidden';
            feedbackInput.name = 'feedback';
            feedbackInput.value = feedbackText.value;

            form.appendChild(sessionInput);
            form.appendChild(feedbackInput);

            document.body.appendChild(form);
            form.submit();
        };

        submitFeedbackButton.addEventListener('click', submitFeedback);

        // Submit feedback on Enter key press
        feedbackText.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitFeedback();
            }
        });
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
