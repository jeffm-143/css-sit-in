<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database connection (assuming $conn is your connection variable)
// Include your database connection file here
include('db_connection.php');

// Get pending reservations count
$pending_count_query = "SELECT COUNT(*) as count FROM reservations WHERE status = 'pending'";
$pending_count = $conn->query($pending_count_query)->fetch_assoc()['count'];

// Get pending reservations details
$pending_notif_query = "
    SELECT r.*, u.FIRSTNAME, u.LASTNAME 
    FROM reservations r
    JOIN users u ON r.student_id = u.ID_NUMBER
    WHERE r.status = 'pending'
    ORDER BY r.created_at DESC
    LIMIT 5";
$pending_notifications = $conn->query($pending_notif_query);
?>

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

                <!-- Notification Dropdown -->
                <div class="relative">
                    <button id="notificationButton" class="text-white hover:text-yellow-400 transition-colors relative">
                        <i class="fas fa-bell text-xl"></i>
                        <?php if ($pending_count > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                            <?php echo $pending_count; ?>
                        </span>
                        <?php endif; ?>
                    </button>
                    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-800">Pending Reservations</h3>
                        </div>
                        <div class="max-h-96 overflow-y-auto">
                            <?php if ($pending_notifications->num_rows > 0): ?>
                                <?php while($notif = $pending_notifications->fetch_assoc()): ?>
                                <div class="p-4 border-b border-gray-100 hover:bg-gray-50">
                                    <p class="font-medium text-gray-800">
                                        <?php echo htmlspecialchars($notif['FIRSTNAME'] . ' ' . $notif['LASTNAME']); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        Room <?php echo htmlspecialchars($notif['lab_room']); ?> - PC <?php echo htmlspecialchars($notif['pc_number']); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?>
                                    </p>                                    <div class="mt-2">
                                        <button onclick="markNotificationAsRead(<?php echo $notif['id']; ?>, this)" 
                                                class="text-xs text-blue-600 hover:text-blue-800">
                                            Mark as read
                                        </button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="p-4 text-center text-gray-500">
                                    No pending reservations
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-4 border-t border-gray-200">
                            <a href="reservation-admin.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View All Reservations
                            </a>
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

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notificationButton = document.getElementById('notificationButton');
        const notificationDropdown = document.getElementById('notificationDropdown');
        
        notificationButton.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden');
        });
        
        document.addEventListener('click', function(e) {
            if (!notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });
    });
    
    function markNotificationAsRead(notificationId, button) {
        fetch('admin_dismiss_notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                notification_id: notificationId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the UI - fade out and remove notification item completely
                const notifItem = button.closest('div.p-4');
                notifItem.style.opacity = '0';
                notifItem.style.transition = 'opacity 0.3s ease-out';
                
                // Remove the notification after animation completes
                setTimeout(() => {
                    // Actually remove the element from DOM
                    notifItem.remove();
                    
                    // Check if no notifications left
                    const notificationList = notifItem.parentElement;
                    const remainingItems = notificationList.querySelectorAll('div.p-4.border-b');
                    if (remainingItems.length === 0) {
                        notificationList.innerHTML = '<div class="p-4 text-center text-gray-500">No pending reservations</div>';
                    }
                }, 300);

                // Update the notification counter
                const counter = document.querySelector('#notificationButton .bg-red-500');
                if (counter) {
                    const currentCount = parseInt(counter.textContent);
                    if (currentCount > 1) {
                        counter.textContent = currentCount - 1;
                    } else {
                        counter.remove();
                    }
                }
            }
        })
        .catch(error => console.error('Error:', error));
    }
    </script>