<?php
session_start();

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

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
    
    // Validate session exists
    $check_session = $conn->prepare("SELECT id FROM sit_in_sessions WHERE id = ?");
    $check_session->bind_param("i", $session_id);
    $check_session->execute();
    $check_session->store_result();
    
    if ($check_session->num_rows === 0) {
        $_SESSION['message'] = 'Invalid session ID.';
        header('Location: history.php');
        exit();
    }
    $check_session->close();

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
    <style>
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hover-card { transition: all 0.2s ease; }
        .hover-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">

    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">History</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <!-- Notification Bell -->
                        <li class="relative">
                            <button id="notificationButton" class="text-white hover:text-yellow-400 transition-colors">
                                <i class="fas fa-bell text-xl"></i>
                                <?php
                                $notif_count = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE ID_NUMBER = ? AND is_read = 0");
                                $notif_count->bind_param("i", $IDNO);
                                $notif_count->execute();
                                $count = $notif_count->get_result()->fetch_assoc()['count'];
                                if ($count > 0):
                                ?>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="notificationCount">
                                    <?php echo $count; ?>
                                </span>
                                <?php endif; ?>
                            </button>
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto" id="notificationList">
                                    <?php
                                    $notifications_query = $conn->prepare("
                                        SELECT * FROM notifications 
                                        WHERE ID_NUMBER = ? AND is_read = 0 
                                        ORDER BY created_at DESC LIMIT 5
                                    ");
                                    $notifications_query->bind_param("i", $IDNO);
                                    $notifications_query->execute();
                                    $notifications = $notifications_query->get_result();
                                    
                                    if ($notifications->num_rows > 0):
                                        while($notif = $notifications->fetch_assoc()):
                                    ?>
                                        <div class="notification-item p-4 border-b border-gray-100 hover:bg-gray-50" 
                                             data-notification-id="<?php echo $notif['id']; ?>" 
                                             style="transition: opacity 0.3s ease-out;">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm text-gray-800"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></p>
                                                </div>
                                                <?php if (!$notif['is_read']): ?>
                                                <button onclick="markAsRead(<?php echo $notif['id']; ?>, this)" 
                                                        class="text-xs text-blue-600 hover:text-blue-800 ml-2">
                                                    Mark as read
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="p-4 text-center text-gray-500">
                                            No notifications
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <li><a href="dashboard.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="history.php" class="text-yellow-400 font-bold transition-colors"><i class="fas fa-history mr-1"></i>History</a></li>
                        <li><a href="user_labsched.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-clock mr-1"></i>Lab Schedule</a></li>
                        <li><a href="user_resources.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-book mr-1"></i>Lab Resources</a></li>
                        <li><a href="reservation.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-alt mr-1"></i>Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-indigo-900 px-6 py-2 rounded-lg font-bold hover:bg-yellow-500 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-out-alt mr-1"></i>Log out
                    </a>
                </div>
            </nav>
        </div>
    </header>

<!-- Main Content -->
<div class="container mx-auto p-6 max-w-7xl fade-in">
    <div class="bg-white rounded-2xl shadow-lg p-8 hover-card">
        <h2 class="text-3xl font-bold text-center text-gray-800 mb-8">Your Sit-in Session History</h2>

        <?php if ($result->num_rows > 0): ?>
            <div class="overflow-hidden rounded-xl border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-50 to-indigo-50">
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Purpose</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Lab Room</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Start Time</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">End Time</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-blue-50/50 transition-colors duration-150">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($row['ID_NUMBER']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['Name']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($row['purpose'])); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo nl2br(htmlspecialchars($row['lab_room'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo date('M d, Y h:i A', strtotime($row['start_time'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo $row['end_time'] ? date('M d, Y h:i A', strtotime($row['end_time'])) : '-'; ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php echo $row['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <button data-session-id="<?php echo $row['id']; ?>" 
                                            class="feedback-btn inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow">
                                        <i class="fas fa-comment-alt mr-2"></i>Feedback
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-12 bg-gray-50 rounded-xl">
                <i class="fas fa-history text-gray-400 text-5xl mb-4"></i>
                <div class="text-gray-500 text-lg">No session history found.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced Feedback Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-96 transform transition-all duration-300 scale-95 opacity-0">
        <div class="p-6 border-b border-gray-100">
            <h3 class="text-xl font-bold text-gray-900">Submit Feedback</h3>
            <p class="mt-2 text-sm text-gray-500">Share your thoughts about this session</p>
        </div>
        <div class="p-6">
            <textarea id="feedbackText" 
                      class="w-full border rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow duration-200" 
                      rows="5" 
                      placeholder="Type your feedback here..."></textarea>
        </div>
        <div class="flex justify-end gap-4 px-6 py-4 bg-gray-50 rounded-b-2xl">
            <button id="cancelButton" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
                Cancel
            </button>
            <button id="submitFeedbackButton" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-sm hover:shadow">
                Submit Feedback
            </button>
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

    // Enhanced modal animations
    const feedbackModal = document.getElementById('feedbackModal');
    const modalContent = feedbackModal.querySelector('div');
    const feedbackButtons = document.querySelectorAll('.feedback-btn');
    const cancelButton = document.getElementById('cancelButton');
    const submitFeedbackButton = document.getElementById('submitFeedbackButton');
    const feedbackText = document.getElementById('feedbackText');
    let sessionId = null;

    const showModal = () => {
        feedbackModal.classList.remove('hidden');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    };

    const hideModal = () => {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            feedbackModal.classList.add('hidden');
            feedbackText.value = '';
        }, 200);
    };

    feedbackButtons.forEach(button => {
        button.addEventListener('click', () => {
            sessionId = button.getAttribute('data-session-id');
            showModal();
            feedbackText.focus();
        });
    });

    cancelButton.addEventListener('click', hideModal);
    feedbackModal.addEventListener('click', (e) => {
        if (e.target === feedbackModal) hideModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !feedbackModal.classList.contains('hidden')) hideModal();
    });

    const submitFeedback = () => {
        if (!feedbackText.value.trim()) {
            feedbackText.classList.add('ring-2', 'ring-red-500');
            setTimeout(() => feedbackText.classList.remove('ring-2', 'ring-red-500'), 2000);
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const sessionInput = document.createElement('input');
        sessionInput.name = 'session_id';
        sessionInput.value = sessionId;

        const feedbackInput = document.createElement('input');
        feedbackInput.name = 'feedback';
        feedbackInput.value = feedbackText.value;

        form.appendChild(sessionInput);
        form.appendChild(feedbackInput);
        document.body.appendChild(form);
        form.submit();
    };

    submitFeedbackButton.addEventListener('click', submitFeedback);
    feedbackText.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && (e.metaKey || e.ctrlKey)) {
            e.preventDefault();
            submitFeedback();
        }
    });
</script>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
