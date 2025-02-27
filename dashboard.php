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

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #50ac6b) ;
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        .nav-bar {
            background-color: navy;
            padding: 15px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .nav-bar h2 {
            margin: 0;
        }
        .nav-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .nav-bar ul li {
            margin: 0 15px;
        }
        .nav-bar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease-in-out;
        }
        .nav-bar ul li a:hover {
            color: yellow;
        }
        .logout {
            background: yellow;
            color: navy;
            padding: 8px 12px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background 0.3s ease-in-out, color 0.3s ease-in-out;
        }
        .logout:hover {
            background: navy;
            color: yellow;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: block;
            margin: auto;
            object-fit: cover;
            background-color: #ddd;
        }
        .w3-card {
            margin-bottom: 20px;
            padding: 20px;
            transition: transform 0.3s ease-in-out;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .w3-card:hover {
            transform: scale(1.05);
        }
        .student-info-card {
            width: 80%;
            margin: auto;
        }
        .announcement-card, .rules-card {
            margin-top: 20px;
        }
        .w3-third {
            padding: 0 15px;
        }
        .w3-center {
            text-align: center;
        }
        .w3-center h3 {
            margin-bottom: 20px;
            color: #333;
        }
        .w3-container p {
            margin: 10px 0;
            color: #555;
        }
        .w3-container ul {
            padding-left: 20px;
            color: #555;
        }
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="nav-bar w3-container">
            <h2 style="margin-right: auto;">Dashboard</h2>
            <nav>
                <ul style="margin: 0 auto;">
                    <li><a href="notification.php">Notification</a></li>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="edit_profile.php">Edit Profile</a></li>
                    <li><a href="history.php">History</a></li>
                    <li><a href="reservation.php">Reservation</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout" style="margin-left: auto;">Log out</a>
        </div>
    </header>

    <!-- Main Content -->
    <main class="w3-container w3-margin-top">
        <div class="w3-row-padding">
            <!-- Student Info -->
            <div class="w3-third">
                <div class="w3-card w3-white w3-round ">
                    <h3 class="w3-center">Student Information</h3>
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="" class="profile-img">
                    <p><strong>ID Number:</strong> <?php echo htmlspecialchars($IDNO); ?></p>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars("$fName $MdName $lName"); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($Course); ?></p>
                    <p><strong>Year:</strong> <?php echo htmlspecialchars($Yrlevel); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <p><strong>Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                    <p><strong>Session:</strong> <?php echo htmlspecialchars($session); ?></p>
                </div>
            </div>

            <!-- Announcements -->
            <div class="w3-third">
                <div class="w3-card w3-white w3-round announcement-card">
                    <h3 class="w3-center">Announcements</h3>
                    <div class="w3-container">
                        <p><strong>CCS Admin | 2025-Feb-03</strong></p>
                        <p>The College of Computer Studies will open the registration of students for the Sit-in privilege starting tomorrow.</p>
                        <hr>
                        <p><strong>CCS Admin | 2024-May-08</strong></p>
                        <p>Important Announcement! We are excited to announce the launch of our new website! </p>
                    </div>
                </div>
            </div>

            <!-- Rules -->
            <div class="w3-third">
                <div class="w3-card w3-white w3-round rules-card">
                    <h3 class="w3-center">Rules and Regulations</h3>
                    <h4>University of Cebu</h4>
                    <h5>College of Information & Computer Studies</h5>
                    <p><strong>Laboratory Rules:</strong></p>
                    <ul>
                        <li>Maintain silence, proper decorum, and discipline inside the laboratory.</li>
                        <li>Games are not allowed inside the lab.</li>
                        <li>Surfing the Internet is allowed only with instructor permission.</li>
                    </ul>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
