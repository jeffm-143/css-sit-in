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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
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

        .nav-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center; 
            gap: 10px;
        }
        .nav-bar ul li {
            display: inline-block;
        }

        .nav-bar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 15px; 
            display: block; 
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
        .student-info-card, .announcement-card, .rules-card {
            margin-top: 20px;
            box-shadow: 5px 5px 5px 5px rgba(0, 0, 0, 0.5);
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
        .rules-card ul {
            padding-left: 20px;
            color: #555;
        }
        .rules-card ul li {
            margin-bottom: 10px;
        }
        .rules-card, .announcement-card {
            max-height: 400px;
            overflow-y: auto;
        }
        
    </style>
</head>
<body>

    <!-- Navigation -->
    <header>
        <div class="nav-bar w3-container">
            <h2 style="margin-right: auto;">Dashboard</h2>
            <nav>
                <ul>
                    <li><a href="#">Notification</a></li>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="edit_profile.php">Edit Profile</a></li>
                    <li><a href="history.php">History</a></li>
                    <li><a href="reservation.php">Reservation</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout">Log out</a>
        </div>
    </header>


    <!-- Main Content -->
    <main class="w3-container w3-margin-top">
        <div class="w3-row-padding">
            <!-- Student Info -->
            <div class="w3-third">
                <div class="w3-card w3-white w3-round student-info-card">
                    <h3 class="w3-center"><strong>Student Information</strong></h3>
                    <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="" class="profile-img">
                    <p><strong><i class="fas fa-id-badge"></i> ID Number:</strong> <?php echo htmlspecialchars($IDNO); ?></p>
                    <p><strong><i class="fas fa-user"></i> Name:</strong> <?php echo htmlspecialchars("$fName $MdName $lName"); ?></p>
                    <p><strong><i class="fas fa-graduation-cap"></i> Course:</strong> <?php echo htmlspecialchars($Course); ?></p>
                    <p><strong><i class="fas fa-clock"></i> Year:</strong> <?php echo htmlspecialchars($Yrlevel); ?></p>
                    <p><strong><i class="fas fa-envelope"></i> Email:</strong> <?php echo htmlspecialchars($email); ?></p>
                    <p><strong><i class="fas fa-home"></i> Address:</strong> <?php echo htmlspecialchars($address); ?></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Session:</strong> <?php echo htmlspecialchars($session); ?></p>

                </div>
            </div>

            <!-- Announcements -->
            <div class="w3-third">
                <div class="w3-card w3-white w3-round announcement-card">
                    <h3 class="w3-center"><strong>Announcements</strong></h3>
                    <div class="w3-container" style="max-height: 400px; overflow-y: auto;">
                        <p>There is no announcement yet!</p>

                    </div>
                </div>
            </div>

            <!-- Rules -->
            <div class="w3-third">
                <div class="w3-card w3-white w3-round rules-card">
                    <h3 class="w3-center"><strong>Rules and Regulations</strong></h3>
                    <div class="w3-container">
                        <h5 class="w3-center"><strong>University of Cebu</strong></h5>
                        <h5><strong>LABORATORY RULES & REGULATIONS</strong></h5>
                        <P>To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:</P>
                        <P>1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.</P>
                        <P>2. Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.</P>
                        <P>3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.</P>
                        <P>4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.</P>
                        <P>5. Deleting computer files and changing the set-up of the computer is a major offense.</P>
                        <P>6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".</P>
                        <P>7. Observe proper decorum while inside the laboratory.</P>
                        <ul>
                            <li>Do not get inside the lab unless the instructor is present.</li>
                            <li>All bags, knapsacks, and the likes must be deposited at the counter.</li>
                            <li>Follow the seating arrangement of your instructor.</li>
                            <li>At the end of class, all software programs must be closed.</li>
                            <li>Return all chairs to their proper places after using.</li>
                        </ul>
                        <P>8. Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.</P>
                        <P>9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.</P>
                        <P>10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.</P>
                        <P>11. For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.</P>
                        <P>12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately.</P>
                        <hr><strong>DISCIPLINARY ACTION</strong></hr>
                        <ul>
                            <li>First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.</li>
                            <li>Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
