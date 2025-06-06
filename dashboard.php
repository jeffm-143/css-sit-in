<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']); // Remove the flag after showing welcome
}

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT u.ID_NUMBER, u.LASTNAME, u.FIRSTNAME, u.MIDDLENAME, u.COURSE, u.YEAR, u.EMAIL, u.ADDRESS, u.IMAGE, u.SESSION,
    COALESCE((SELECT SUM(points_earned) FROM points WHERE student_id = u.ID), 0) as total_points 
    FROM users u WHERE u.USERNAME = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $email, $address, $profile_image, $session, $points);
$stmt->fetch();
$stmt->close();

$default_image = "images/css.png";
$profile_image = $profile_image ? $profile_image : $default_image;

// Update the announcements query to include all fields and properly format the date
$announcements_query = $conn->query("
    SELECT a.*, u.FIRSTNAME, u.LASTNAME 
    FROM announcements a 
    LEFT JOIN users u ON a.admin_username = u.USERNAME 
    ORDER BY a.date_posted DESC
");

// Don't close the connection yet - we need it for notifications
// $conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Dashboard</h2>                <div class="flex items-center space-x-8">
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
                        <li><a href="dashboard.php" class="text-yellow-400 font-bold transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="history.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-history mr-1"></i>History</a></li>
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

    <?php if ($showWelcome): ?>
    <div id="welcomeMessage" class="fixed top-20 right-4 z-50">
        <div class="bg-gradient-to-r from-blue-800 to-indigo-800 text-white rounded-lg shadow-lg p-4 max-w-sm transform transition-all duration-500 opacity-100 translate-x-0">
            <div class="text-center">
                <h2 class="text-xl font-bold">Welcome, <?php echo htmlspecialchars($fName . ' ' . $lName); ?>!</h2>
                <p class="text-base">College of Computer Studies Student Dashboard</p>
                <div class="mt-4">
                    <div class="relative w-full h-2 bg-gray-300 rounded-full overflow-hidden">
                        <div id="progressBar" class="absolute top-0 left-0 h-full bg-yellow-400 transition-all"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const progressBar = document.getElementById('progressBar');
        let progress = 0;
        const interval = setInterval(() => {
            progress += 4; // Increment progress faster
            progressBar.style.width = progress + '%';
            if (progress >= 100) {
                clearInterval(interval);
                const welcome = document.getElementById('welcomeMessage');
                if (welcome) {
                    welcome.remove();
                }
            }
        }, 100); // Reduce interval time for faster updates
    </script>


    </script>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8 max-w-7xl">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile -->
            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-2xl flex items-center">
                    <i class="fas fa-user text-white mr-2"></i>
                    <h3 class="text-lg font-semibold text-white">Profile</h3>
                </div>
                <div class="p-6 rounded-b-2xl">
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="" class="w-36 h-36 rounded-full object-cover ring-4 ring-navy-800/10">
                            <div class="absolute bottom-0 right-0 bg-green-500 w-4 h-4 rounded-full border-2 border-white"></div>
                        </div>
                        <h3 class="text-xl font-bold mt-4 mb-6 text-gray-800"><?php echo htmlspecialchars("$fName $lName"); ?></h3>
                        <div class="space-y-4 w-full text-sm divide-y divide-gray-100">
                            <div class="flex items-center py-2">
                                <i class="fas fa-id-badge w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3"><?php echo htmlspecialchars($IDNO); ?></span>
                            </div>
                            <div class="flex items-center py-2">
                                <i class="fas fa-graduation-cap w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3"><?php echo htmlspecialchars($Course); ?></span>
                            </div>
                            <div class="flex items-center py-2">
                                <i class="fas fa-clock w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3"><?php echo htmlspecialchars($Yrlevel); ?> Year</span>
                            </div>
                            <div class="flex items-center py-2">
                                <i class="fas fa-ticket-alt w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3">Remaining Sessions: <?php echo htmlspecialchars($session); ?></span>
                            </div>
                            <div class="flex items-center py-2">
                                <i class="fas fa-star w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3">Points: <?php echo htmlspecialchars($points); ?> total</span>
                            </div>
                            <div class="flex items-center py-2">
                                <i class="fas fa-envelope w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3"><?php echo htmlspecialchars($email); ?></span>
                            </div>
                            <div class="flex items-center py-2">
                                <i class="fas fa-home w-5 text-navy-800"></i>
                                <span class="text-gray-600 ml-3"><?php echo htmlspecialchars($address); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-2xl flex items-center">
                    <i class="fas fa-bullhorn text-white mr-2"></i>
                    <h3 class="text-lg font-semibold text-white">Announcements</h3>
                </div>
                <div class="p-6 max-h-[500px] overflow-y-auto pr-2 rounded-b-2xl">
                    <?php if ($announcements_query->num_rows > 0): ?>
                        <?php while($announcement = $announcements_query->fetch_assoc()): ?>
                            <div class="mb-6 bg-gray-50 rounded-xl p-4 last:mb-0">
                                <div class="flex items-center mb-3">
                                    <div class="w-10 h-10 bg-navy-800 rounded-full flex items-center justify-center text-white">
                                        <?php echo strtoupper(substr($announcement['FIRSTNAME'], 0, 1)); ?>
                                    </div>
                                    <div class="ml-3">
                                        <p class="font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($announcement['FIRSTNAME'] . ' ' . $announcement['LASTNAME']); ?>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('F d, Y h:i A', strtotime($announcement['date_posted'])); ?>
                                        </p>
                                    </div>
                                </div>
                                <p class="text-gray-600 text-sm leading-relaxed">
                                    <?php echo nl2br(htmlspecialchars($announcement['content'])); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3"></i>
                            <p>No announcements available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rules -->
            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition-shadow duration-300">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 rounded-t-2xl flex items-center">
                    <i class="fas fa-gavel text-white mr-2"></i>
                    <h3 class="text-lg font-semibold text-white">Laboratory Rules</h3>
                </div>
                <div class="p-6 max-h-[500px] overflow-y-auto pr-2 text-sm rounded-b-2xl">
                    <div class="space-y-4 text-gray-600">
                        <div class="bg-navy-800/5 p-4 rounded-lg">
                            <h4 class="font-bold text-navy-800 mb-2">University of Cebu</h4>
                            <p class="text-sm">Laboratory Rules & Regulations</p>
                            <p class="text-sm mt-2">To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                                <p class="ml-3">Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                                <p class="ml-3">Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                                <p class="ml-3">Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">4</span>
                                <p class="ml-3">Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">5</span>
                                <p class="ml-3">Deleting computer files and changing the set-up of the computer is a major offense.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">6</span>
                                <p class="ml-3">Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">7</span>
                                <p class="ml-3">Observe proper decorum while inside the laboratory.<br>
                                • Do not get inside the lab unless the instructor is present.<br>
                                • All bags, knapsacks, and the likes must be deposited at the counter.<br>
                                • Follow the seating arrangement of your instructor.<br>
                                • At the end of class, all software programs must be closed.<br>
                                • Return all chairs to their proper places after using.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">8</span>
                                <p class="ml-3">Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">9</span>
                                <p class="ml-3">Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">10</span>
                                <p class="ml-3">Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">11</span>
                                <p class="ml-3">For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.</p>
                            </div>
                            <div class="flex items-start">
                                <span class="bg-navy-800 text-white w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">12</span>
                                <p class="ml-3">Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately.</p>
                            </div>
                        </div>

                        <div class="mt-6 bg-red-50 p-4 rounded-lg">
                            <h5 class="font-bold text-red-700 mb-2">Disciplinary Actions</h5>
                            <ul class="list-disc pl-5 space-y-2 text-red-600">
                                <li>First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</li>
                                <li>Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

