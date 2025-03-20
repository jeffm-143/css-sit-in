<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, EMAIL, ADDRESS, IMAGE, SESSION FROM users WHERE USERNAME = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $email, $address, $profile_image, $session);
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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-navy-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Dashboard</h2>
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
    <main class="container mx-auto px-4 py-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Student Info -->
            <div class="bg-white rounded-xl shadow-lg p-4 transform hover:scale-105 transition duration-300">
                <h3 class="text-lg font-bold text-center mb-4">Student Information</h3>
                <div class="flex flex-col items-center">
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="" class="w-32 h-32 rounded-full object-cover mb-3">
                    <div class="space-y-2 w-full text-sm">
                        <p class="flex items-center"><i class="fas fa-id-badge w-5 text-gray-500"></i><span class="font-semibold ml-2">ID Number:</span> <span class="ml-1"><?php echo htmlspecialchars($IDNO); ?></span></p>
                        <p class="flex items-center"><i class="fas fa-user w-5 text-gray-500"></i><span class="font-semibold ml-2">Name:</span> <span class="ml-1"><?php echo htmlspecialchars("$fName $MdName $lName"); ?></span></p>
                        <p class="flex items-center"><i class="fas fa-graduation-cap w-5 text-gray-500"></i><span class="font-semibold ml-2">Course:</span> <span class="ml-1"><?php echo htmlspecialchars($Course); ?></span></p>
                        <p class="flex items-center"><i class="fas fa-clock w-5 text-gray-500"></i><span class="font-semibold ml-2">Year Level:</span> <span class="ml-1"><?php echo htmlspecialchars($Yrlevel); ?></span></p>
                        <p class="flex items-center"><i class="fas fa-envelope w-5 text-gray-500"></i><span class="font-semibold ml-2">Email:</span> <span class="ml-1"><?php echo htmlspecialchars($email); ?></span></p>
                        <p class="flex items-center"><i class="fas fa-home w-5 text-gray-500"></i><span class="font-semibold ml-2">Address:</span> <span class="ml-1"><?php echo htmlspecialchars($address); ?></span></p>
                        <p class="flex items-center"><i class="fas fa-calendar-alt w-5 text-gray-500"></i><span class="font-semibold ml-2">Session:</span> <span class="ml-1"><?php echo htmlspecialchars($session); ?></span></p>
                    </div>
                </div>
            </div>

            <!-- Announcements -->
            <div class="bg-white rounded-xl shadow-lg p-4 transform hover:scale-105 transition duration-300">
                <h3 class="text-lg font-bold text-center mb-4">Announcements</h3>
                <div class="max-h-[400px] overflow-y-auto scrollbar-thin">
                    <?php if ($announcements_query->num_rows > 0): ?>
                        <div class="max-h-[400px] overflow-y-auto">
                            <?php while($announcement = $announcements_query->fetch_assoc()): ?>
                                <div class="mb-4 p-3 border-b border-gray-200 last:border-0">
                                    <div class="flex justify-between items-center text-sm text-gray-500 mb-1">
                                        <span class="font-semibold">
                                            <?php 
                                            echo '<strong>' . htmlspecialchars($announcement['FIRSTNAME'] . ' ' . $announcement['LASTNAME']) . '</strong>'; 
                                            ?>
                                        </span>
                                        <span class="mx-2">|</span>
                                        <span><strong><?php echo date('F d, Y h:i A', strtotime($announcement['date_posted'])); ?></strong></span>
                                    </div>
                                    <p class="text-gray-600 text-sm"><?php echo nl2br(htmlspecialchars($announcement['content'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 text-center py-4">No announcements available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Rules -->
            <div class="bg-white rounded-xl shadow-lg p-4 transform hover:scale-105 transition duration-300">
                <h3 class="text-lg font-bold text-center mb-4">Rules and Regulations</h3>
                <div class="max-h-[400px] overflow-y-auto scrollbar-thin text-sm">
                    <h5 class="text-center font-bold mb-3">University of Cebu</h5>
                    <h5 class="font-bold mb-3">LABORATORY RULES & REGULATIONS</h5>
                    <div class="space-y-3 text-gray-600">
                        <p>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</p>
                        <p>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</p>
                        <p>2. Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.</p>
                        <p>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</p>
                        <p>4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</p>
                        <p>5. Deleting computer files and changing the set-up of the computer is a major offense.</p>
                        <p>6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</p>
                        <p>7. Observe proper decorum while inside the laboratory.</p>
                        <ul>
                            <li>Do not get inside the lab unless the instructor is present.</li>
                            <li>All bags, knapsacks, and the likes must be deposited at the counter.</li>
                            <li>Follow the seating arrangement of your instructor.</li>
                            <li>At the end of class, all software programs must be closed.</li>
                            <li>Return all chairs to their proper places after using.</li>
                        </ul>
                        <p>8. Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</p>
                        <p>9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</p>
                        <p>10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</p>
                        <p>11. For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.</p>
                        <p>12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately.</p>
                        <hr class="my-4">
                        <div class="font-bold">DISCIPLINARY ACTION</div>
                        <ul class="list-disc pl-6 space-y-2">
                            <li>First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</li>
                            <li>Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</li>
                        </ul>
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
