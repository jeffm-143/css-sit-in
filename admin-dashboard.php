<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch statistics and purpose data for pie chart
$statistics = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE user_type = 'student') as total_students,
        (SELECT COUNT(*) FROM sit_in_sessions WHERE status = 'active') as current_sitin,
        (SELECT COUNT(*) FROM sit_in_sessions) as total_sitin,
        (SELECT COUNT(*) FROM resources) as total_resources
")->fetch_assoc();

// Add this query near the top with other statistics queries
$year_stats = $conn->query("
    SELECT YEAR, COUNT(*) as count 
    FROM users 
    WHERE user_type = 'student' 
    GROUP BY YEAR 
    ORDER BY YEAR ASC
")->fetch_all(MYSQLI_ASSOC);

$year_data = array_fill(1, 4, 0); // Initialize with 0 for years 1-4
foreach ($year_stats as $stat) {
    $year_data[$stat['YEAR']] = $stat['count'];
}

// Define default programming languages with their colors
$default_languages = [
    'C' => '#FF6384',
    'C#' => '#36A2EB', 
    'Python' => '#FFCE56',
    'PHP' => '#4BC0C0',
    'Java' => '#9966FF',
    'ASP.Net' => '#FF9F40'
];

// Get purpose distribution for pie chart
$purpose_stats = $conn->query("
    SELECT purpose, COUNT(*) as count 
    FROM sit_in_sessions 
    GROUP BY purpose 
    ORDER BY count DESC
")->fetch_all(MYSQLI_ASSOC);

// Always use default languages, merge with actual data if exists
$chart_data = array_fill_keys(array_keys($default_languages), 0);
if (!empty($purpose_stats)) {
    foreach ($purpose_stats as $stat) {
        if (array_key_exists($stat['purpose'], $chart_data)) {
            $chart_data[$stat['purpose']] = (int)$stat['count'];
        }
    }
}

// Handle announcement submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['announcement'])) {
    $content = $_POST['announcement'];
    $admin_username = $_SESSION['username']; // Use the logged-in admin's username
    
    // Verify user is admin
    $verify_admin = $conn->prepare("SELECT user_type FROM users WHERE USERNAME = ? AND user_type = 'admin'");
    $verify_admin->bind_param("s", $admin_username);
    $verify_admin->execute();
    $result = $verify_admin->get_result();
    
    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO announcements (admin_username, content) VALUES (?, ?)");
        $stmt->bind_param("ss", $admin_username, $content);
        
        if ($stmt->execute()) {
            echo "<script>showAlert('Announcement posted successfully!', 'success');</script>";
        } else {
            echo "<script>showAlert('Error posting announcement.', 'error');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>showAlert('Only admin users can post announcements.', 'warning');</script>";
    }
    $verify_admin->close();
    // Redirect to refresh the page and show new announcement
    echo "<script>window.location.href = 'admin-dashboard.php';</script>";
}

// Add these handlers after the existing POST handler
if (isset($_POST['edit_announcement'])) {
    $id = $_POST['announcement_id'];
    $content = $_POST['edited_content'];
    
    $stmt = $conn->prepare("UPDATE announcements SET content = ? WHERE id = ? AND admin_username = ?");
    $stmt->bind_param("sis", $content, $id, $_SESSION['username']);
    
    if ($stmt->execute()) {
        echo "<script>showAlert('Announcement updated successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error updating announcement.', 'error');</script>";
    }
    $stmt->close();
}

if (isset($_POST['delete_announcement'])) {
    $id = $_POST['announcement_id'];
    
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ? AND admin_username = ?");
    $stmt->bind_param("is", $id, $_SESSION['username']);
    
    if ($stmt->execute()) {
        echo "<script>showAlert('Announcement deleted successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error deleting announcement.', 'error');</script>";
    }
    $stmt->close();
}

// Fetch existing announcements
$announcements = $conn->query("SELECT id, admin_username, content, date_posted FROM announcements ORDER BY date_posted DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="bg-gray-100">


    <!-- Move the alert JavaScript function to the top -->
    <script>
        function showAlert(message, type) {
            const alertContainer = document.getElementById('alert-container');
            const alertDiv = document.createElement('div');
            
            // Configure alert styles
            alertDiv.className = `transform translate-x-0 transition-transform duration-300 p-4 mb-4 rounded-lg shadow-lg flex items-center justify-between ${
                type === 'success' ? 'bg-green-100 text-green-800 border border-green-200' :
                type === 'error' ? 'bg-red-100 text-red-800 border border-red-200' :
                'bg-yellow-100 text-yellow-800 border border-yellow-200'
            }`;
            
            // Alert content with icon
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    ${type === 'success' 
                        ? '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                        : '<svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>'
                    }
                    <span class="font-medium">${message}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="ml-4 text-gray-500 hover:text-gray-700">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                </button>
            `;
            
            // Add alert to container
            alertContainer.appendChild(alertDiv);
            
            // Slide in animation
            setTimeout(() => alertDiv.classList.add('translate-x-0'), 100);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                alertDiv.classList.add('-translate-x-full');
                setTimeout(() => alertDiv.remove(), 300);
            }, 5000);
        }
    </script>

    <!-- Navigation Bar -->
    <nav class="bg-blue-900 py-4 px-6 shadow-md">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <a href="admin-dashboard.php" class="text-white text-2xl font-bold">CCS Admin</a>            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center space-x-6">
                <ul class="flex space-x-6 text-white font-medium">
                    <li><a href="admin-dashboard.php" class="hover:text-yellow-400 transition">Home</a></li>
                    <li><a href="search.php" class="hover:text-yellow-400 transition">Search</a></li>
                    <li><a href="students.php" class="hover:text-yellow-400 transition">Students</a></li>
                    <li><a href="sit-in.php" class="hover:text-yellow-400 transition">Sit-in</a></li>
                    <li><a href="view-sit-in.php" class="hover:text-yellow-400 transition">View Sit-in</a></li>
                    <li><a href="sit-in-reports.php" class="hover:text-yellow-400 transition">Sit-in Reports</a></li>
                    <li><a href="feedback-reports.php" class="hover:text-yellow-400 transition">Feedback</a></li>
                    <li><a href="reservation-admin.php" class="hover:text-yellow-400 transition">Reservation</a></li>
                </ul>
>
                <!-- Admin Tools Dropdown Menu -->
                <div class="relative">
                    <!-- Primary Button with Gradient -->
                    <button id="dashboardOptionsBtn" type="button" class="flex items-center justify-center rounded-md bg-gradient-to-r from-blue-600 to-blue-800 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:from-blue-700 hover:to-blue-900 transition duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-tools mr-2 text-blue-200"></i>
                        <span>Admin Tools</span>
                        <svg class="w-4 h-4 ml-2 transition-transform duration-200" id="dropdownArrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <!-- Enhanced Dropdown Panel -->
                    <div id="dashboardOptionsMenu" class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 hidden z-10 transform origin-top-right transition-all duration-200 ease-out opacity-0 scale-95">
                        <div class="py-1.5 divide-y divide-gray-100 dark:divide-gray-700">
                            <!-- Menu Item 1 -->
                            <div>
                                <a href="admin-resources.php" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                    <i class="fas fa-book-reader mr-3 text-blue-600 dark:text-blue-400 w-5 text-center"></i>
                                    <span>Manage Resources</span>
                                </a>
                            </div>
                            
                            <!-- Menu Item 2 -->
                            <div>
                                <a href="admin-points.php" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                    <i class="fas fa-chart-bar mr-3 text-blue-600 dark:text-blue-400 w-5 text-center"></i>
                                    <span>Usage Analytics</span>
                                </a>
                            </div>
                            
                            <!-- Menu Item 3 -->
                            <div>
                                <a href="admin-labsched.php" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                    <i class="fas fa-calendar-alt mr-3 text-blue-600 dark:text-blue-400 w-5 text-center"></i>
                                    <span>Lab Scheduling</span>
                                </a>
                            </div>
                            
                            <!-- Menu Item 4 -->
                            <div>
                                <a href="admin-leaderboards.php" class="group flex items-center px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-blue-900/30">
                                    <i class="fas fa-trophy mr-3 text-blue-600 dark:text-blue-400 w-5 text-center"></i>
                                    <span>Performance Rankings</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Add this script at the end of your file before </body> -->
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const dropdownBtn = document.getElementById('dashboardOptionsBtn');
                        const dropdownMenu = document.getElementById('dashboardOptionsMenu');
                        const dropdownArrow = document.getElementById('dropdownArrow');
                        
                        dropdownBtn.addEventListener('click', function() {
                            const expanded = dropdownMenu.classList.contains('hidden');
                            
                            if (expanded) {
                                // Show menu
                                dropdownMenu.classList.remove('hidden', 'opacity-0', 'scale-95');
                                dropdownMenu.classList.add('opacity-100', 'scale-100');
                                dropdownArrow.classList.add('rotate-180');
                            } else {
                                // Hide menu
                                dropdownMenu.classList.add('opacity-0', 'scale-95');
                                dropdownArrow.classList.remove('rotate-180');
                                
                                // Wait for animation to complete before hiding
                                setTimeout(() => {
                                    dropdownMenu.classList.add('hidden');
                                }, 200);
                            }
                        });
                        
                        // Close dropdown when clicking outside
                        document.addEventListener('click', function(event) {
                            if (!dropdownBtn.contains(event.target) && !dropdownMenu.contains(event.target)) {
                                dropdownMenu.classList.add('hidden', 'opacity-0', 'scale-95');
                                dropdownArrow.classList.remove('rotate-180');
                            }
                        });
                    });
                </script>
            </div>

            <!-- Log Out Button -->
            <a href="logout.php" class="bg-yellow-400 text-blue-900 px-4 py-2 rounded-lg font-bold hover:bg-blue-700 hover:text-white transition">
                Log out
            </a>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto p-8">
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 rounded-xl p-6 mb-8 text-white shadow-lg">
            <h1 class="text-3xl font-bold">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p class="mt-2 opacity-90">College of Computer Studies Admin Dashboard</p>
        </div>
        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Students Registered Card -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Students Registered</h2>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Live Data</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Total Students</span>
                <span class="text-3xl font-bold text-blue-600"><?php echo $statistics['total_students']; ?></span>
            </div>
            </div>

            <!-- Current Sit-ins Card -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Current Sit-ins</h2>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Active</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Active Sessions</span>
                <span class="text-3xl font-bold text-green-600"><?php echo $statistics['current_sitin']; ?></span>
            </div>
            </div>

            <!-- Total Sit-ins Card -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Total Sit-ins</h2>
                <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">All Time</span>
            </div>
            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                <span class="text-gray-600">Total Sessions</span>
                <span class="text-3xl font-bold text-purple-600"><?php echo $statistics['total_sitin']; ?></span>
            </div>
            </div>

            <!-- Analytics Card -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Purpose Analytics</h2>
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">Chart</span>
            </div>
            <canvas id="pieChart" class="w-full"></canvas>
            </div>

            <!-- Year Level Analytics Card -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-800">Year Level Distribution</h2>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">Analytics</span>
            </div>
            <canvas id="barChart" class="w-full"></canvas>
            </div>

            <!-- Announcements Card -->
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300 p-6 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Announcements</h2>
                    <span class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm">Updates</span>
                </div>
                <form method="POST" class="mb-6">
                    <textarea name="announcement" 
                            class="w-full p-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                            placeholder="Write your announcement here..."
                            rows="4"></textarea>
                    <button type="submit" 
                            class="mt-3 w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition duration-300 flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                        </svg>
                        Post Announcement
                    </button>
                </form>

                <div class="space-y-4 max-h-[400px] overflow-y-auto px-2">
                    <?php if ($announcements && $announcements->num_rows > 0): ?>
                        <?php while($row = $announcements->fetch_assoc()): ?>
                        <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-200">
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex items-center space-x-2">
                                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                        <?php echo strtoupper(substr($row['admin_username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($row['admin_username']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo date('F d, Y h:i A', strtotime($row['date_posted'])); ?></p>
                                    </div>
                                </div>
                                <?php if ($row['admin_username'] === $_SESSION['username']): ?>
                                <div class="flex space-x-2">
                                    <button onclick="editAnnouncement(<?php echo $row['id']; ?>, '<?php echo addslashes($row['content']); ?>')"
                                            class="text-blue-600 hover:text-blue-800 p-1 rounded-full hover:bg-blue-100 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="deleteAnnouncement(<?php echo $row['id']; ?>)"
                                            class="text-red-600 hover:text-red-800 p-1 rounded-full hover:bg-red-100 transition-colors duration-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-700 whitespace-pre-line"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                            </svg>
                            <p>No announcements yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal with improved styling -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-8 border w-[32rem] shadow-xl rounded-xl bg-white">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Edit Announcement</h3>
            <form method="POST" id="editForm">
                <input type="hidden" name="announcement_id" id="edit_announcement_id">
                <input type="hidden" name="edit_announcement" value="1">
                <textarea name="edited_content" id="edit_content" 
                        class="w-full p-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300"
                        rows="6"></textarea>
                <div class="mt-6 flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-6 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-300">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-300">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add these JavaScript functions -->
    <script>
        // Dropdown toggle
        document.getElementById('dashboardOptionsBtn').addEventListener('click', function(event) {
            event.stopPropagation();
            const dropdownMenu = document.getElementById('dashboardOptionsMenu');
            dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdownMenu = document.getElementById('dashboardOptionsMenu');
            const dropdownButton = document.getElementById('dashboardOptionsBtn');
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.style.display = 'none';
            }
        });

        // Running time function
        function updateTime() {
            const now = new Date();
            let hours = now.getHours();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // hour '0' should be '12'
            const minutes = now.getMinutes().toString().padStart(2, '0');
            const seconds = now.getSeconds().toString().padStart(2, '0');
            
            document.getElementById('running-time').textContent = 
                `${hours}:${minutes}:${seconds} ${ampm}`;
        }

        // Update immediately, then every second
        updateTime();
        setInterval(updateTime, 1000);
    </script>

    <script>
        function editAnnouncement(id, content) {
            document.getElementById('edit_announcement_id').value = id;
            document.getElementById('edit_content').value = content;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteAnnouncement(id) {
            if (confirm('Are you sure you want to delete this announcement?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="announcement_id" value="${id}">
                    <input type="hidden" name="delete_announcement" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>

    <!-- Chart.js for Graphs -->
    <script>
        // Real statistics data from database
        const purposeStats = {
            labels: <?php echo json_encode(array_keys($chart_data)); ?>,
            data: <?php echo json_encode(array_values($chart_data)); ?>
        };

        const yearLevelStats = {
            labels: ['1st Year', '2nd Year', '3rd Year', '4th Year'],
            data: <?php echo json_encode(array_values($year_data)); ?> // Fixed array_values syntax
        };

        // Pie Chart with real data
        var ctxPie = document.getElementById('pieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: purposeStats.labels,
                datasets: [{
                    data: purposeStats.data,
                    backgroundColor: <?php echo json_encode(array_values($default_languages)); ?>, // Fixed array_values syntax
                    borderWidth: 1,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            padding: 15,
                            font: { size: 12 },
                            usePointStyle: true
                        }
                    },
                }
            }
        });

        // Bar Chart
        var ctxBar = document.getElementById('barChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: yearLevelStats.labels,
                datasets: [{
                    label: 'Students per Year Level',
                    data: yearLevelStats.data,
                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20,
                        ticks: {
                            stepSize: 2
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Collage of Computer Studies Students Year Level',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
