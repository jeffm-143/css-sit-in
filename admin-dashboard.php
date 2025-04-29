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
        (SELECT COUNT(*) FROM sit_in_sessions) as total_sitin
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
</head>
<body class="bg-gray-100">
    <!-- Alert Container - Add this at the top of body -->
    <div id="alert-container" class="fixed top-4 right-4 z-50"></div>

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
            <a href="admin-dashboard.php" class="text-white text-2xl font-bold">CCS Admin</a>

            <!-- Desktop Navigation -->
            <ul class="hidden md:flex space-x-6 text-white font-medium">
                <li><a href="admin-dashboard.php" class="hover:text-yellow-400 transition">Home</a></li>
                <li><a href="search.php" class="hover:text-yellow-400 transition">Search</a></li>
                <li><a href="students.php" class="hover:text-yellow-400 transition">Students</a></li>
                <li><a href="sit-in.php" class="hover:text-yellow-400 transition">Sit-in</a></li>
                <li><a href="view-sit-in.php" class="hover:text-yellow-400 transition">View Sit-in</a></li>
                <li><a href="sit-in-reports.php" class="hover:text-yellow-400 transition">Sit-in Reports</a></li>
                <li><a href="feedback-reports.php" class="hover:text-yellow-400 transition">Feedback</a></li>
                <li><a href="reservation-admin.php" class="hover:text-yellow-400 transition">Reservation</a></li>
            </ul>

            <!-- Log Out Button -->
            <a href="logout.php" class="bg-yellow-400 text-blue-900 px-4 py-2 rounded-lg font-bold hover:bg-blue-700 hover:text-white transition">
                Log out
            </a>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        <!-- Statistics Section -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-lg font-semibold text-blue-900">📊 Statistics</h2>
            <p>Students Registered: <strong><?php echo $statistics['total_students']; ?></strong></p>
            <p>Currently Sit-in: <strong><?php echo $statistics['current_sitin']; ?></strong></p>
            <p>Total Sit-in: <strong><?php echo $statistics['total_sitin']; ?></strong></p>

            <!-- Pie Chart Canvas -->
            <canvas id="pieChart"></canvas>
        </div>

        <!-- Student Year Level Bar Chart -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-lg font-semibold text-blue-900">📈 Students Year Level</h2>
            <canvas id="barChart"></canvas>
        </div>

        <!-- Announcements Section -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <h2 class="text-lg font-semibold text-blue-900">📢 Announcements</h2>
            <form method="POST" class="mb-4">
            <textarea name="announcement" class="w-full p-2 border rounded-lg" placeholder="New Announcement"></textarea>
            <div class="flex justify-center">
                <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md mt-2">Submit</button>
            </div>
            </form>
            
            <h3 class="text-md font-semibold">Posted Announcements</h3>
            <div class="border-t mt-2 pt-2 max-h-[300px] overflow-y-auto">
            <?php if ($announcements && $announcements->num_rows > 0): ?>
                <?php while($row = $announcements->fetch_assoc()): ?>
                <div class="mb-4 pb-2 border-b last:border-0">
                    <div class="flex justify-between items-start">
                        <p class="text-sm text-gray-600">
                            <strong><?php echo htmlspecialchars($row['admin_username']); ?> | 
                            <?php echo date('F d, Y h:i A', strtotime($row['date_posted'])); ?></strong>
                        </p>
                        <?php if ($row['admin_username'] === $_SESSION['username']): ?>
                            <div class="flex space-x-2">
                                <button onclick="editAnnouncement(<?php echo $row['id']; ?>, '<?php echo addslashes($row['content']); ?>')"
                                        class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                <button onclick="deleteAnnouncement(<?php echo $row['id']; ?>)"
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <p class="mt-1"><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500">No announcements yet.</p>
            <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Add this modal for editing -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <form method="POST" id="editForm">
                <input type="hidden" name="announcement_id" id="edit_announcement_id">
                <input type="hidden" name="edit_announcement" value="1">
                <textarea name="edited_content" id="edit_content" class="w-full p-2 border rounded-lg" rows="4"></textarea>
                <div class="mt-4 flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">Cancel</button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add these JavaScript functions -->
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
