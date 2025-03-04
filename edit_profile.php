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
$stmt = $conn->prepare("SELECT ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, EMAIL, ADDRESS, IMAGE FROM users WHERE USERNAME = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $email, $address, $profile_image);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newIDNO = $_POST['IDNO'];
    $newLName = $_POST['lName'];
    $newFName = $_POST['fName'];
    $newMdName = $_POST['MdName'];
    $newCourse = $_POST['Course'];
    $newYrlevel = $_POST['Yrlevel'];
    $newEmail = $_POST['email'];
    $newAddress = $_POST['address'];
    $newProfileImage = $profile_image;

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $newProfileImage = $target_file;
        } else {
            echo "<script>alert('Error uploading image. Please try again.');</script>";
        }
    }

    if ($newIDNO == $IDNO && $newLName == $lName && $newFName == $fName && $newMdName == $MdName && $newCourse == $Course && $newYrlevel == $Yrlevel && $newEmail == $email && $newAddress == $address && $newProfileImage == $profile_image) {
        echo "<script>alert('Nothing has been edited.'); window.location.href='dashboard.php';</script>";
    } else {
        $stmt = $conn->prepare("UPDATE users SET ID_NUMBER = ?, LASTNAME = ?, FIRSTNAME = ?, MIDDLENAME = ?, COURSE = ?, YEAR = ?, EMAIL = ?, ADDRESS = ?, IMAGE = ? WHERE USERNAME = ?");
        $stmt->bind_param("ssssssssss", $newIDNO, $newLName, $newFName, $newMdName, $newCourse, $newYrlevel, $newEmail, $newAddress, $newProfileImage, $user);

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Error updating profile. Please try again.');</script>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #50ac6b);
            margin: 0;
            padding: 0;
            height: 100%;
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
        .edit-profile-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
            width: 500px;
            text-align: center;
            margin: 20px auto;
        }
        .edit-profile-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .profile-image-container {
            margin-bottom: 15px;
        }
        .profile-image-container img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: block;
            margin: auto;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }
        .w3-input, .w3-select {
            border-radius: 5px;
            width: 100%;
        }
        .button-container {
            display: flex;
            justify-content: space-between;
        }
        .w3-button {
            width: 48%;
            background: #5461dd;
            color: white;
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
            margin: 5px 1%;
        }
        .w3-button:hover {
            background: #2b9ebb;
        }
        .w3-button.cancel {
            background: #f44336;
        }
        .w3-button.cancel:hover {
            background: #d32f2f;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
        <div class="nav-bar w3-container">
            <h2 style="margin-right: auto;">Edit Profile</h2>
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

    <div class="edit-profile-container w3-card w3-animate-opacity">
        <h2>Edit Profile</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <div class="profile-image-container">
                <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image">
                <input class="w3-input w3-border" type="file" name="profile_image">
            </div>

            <div class="form-group">
                <label>ID Number:</label>
                <input class="w3-input w3-border" type="number" name="IDNO" value="<?php echo htmlspecialchars($IDNO); ?>" required>
            </div>

            <div class="form-group">
                <label>Last Name:</label>
                <input class="w3-input w3-border" type="text" name="lName" value="<?php echo htmlspecialchars($lName); ?>" required>
            </div>

            <div class="form-group">
                <label>First Name:</label>
                <input class="w3-input w3-border" type="text" name="fName" value="<?php echo htmlspecialchars($fName); ?>" required>
            </div>

            <div class="form-group">
                <label>Middle Name:</label>
                <input class="w3-input w3-border" type="text" name="MdName" value="<?php echo htmlspecialchars($MdName); ?>" required>
            </div>

            <div class="form-group">
                <label>Course:</label>
                <select class="w3-select w3-border" name="Course" required>
                <option value="" disabled>Select Course</option>
                <option value="BSIT" <?php if ($Course == 'BSIT') echo 'selected'; ?>>Bachelor of Science in Information Technology (BSIT)</option>
                <option value="BSED" <?php if ($Course == 'BSED') echo 'selected'; ?>>Bachelor of Secondary Education (BSED)</option>
                <option value="BSBA" <?php if ($Course == 'BSBA') echo 'selected'; ?>>Bachelor of Science in Business Administration (BSBA)</option>
                <option value="BSNursing" <?php if ($Course == 'BSNursing') echo 'selected'; ?>>Bachelor of Science in Nursing (BSNursing)</option>
                <option value="BSEducation" <?php if ($Course == 'BSEducation') echo 'selected'; ?>>Bachelor of Science in Education (BSEducation)</option>
                <option value="BSPsychology" <?php if ($Course == 'BSPsychology') echo 'selected'; ?>>Bachelor of Science in Psychology (BSPsychology)</option>
                <option value="BSArchitecture" <?php if ($Course == 'BSArchitecture') echo 'selected'; ?>>Bachelor of Science in Architecture (BSArchitecture)</option>
                <option value="LLB" <?php if ($Course == 'LLB') echo 'selected'; ?>>Bachelor of Laws (LLB)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Year Level:</label>
                <select class="w3-select w3-border" name="Yrlevel" required>
                    <option value="" disabled>Select Year Level</option>
                    <option value="1" <?php if ($Yrlevel == '1') echo 'selected'; ?>>1</option>
                    <option value="2" <?php if ($Yrlevel == '2') echo 'selected'; ?>>2</option>
                    <option value="3" <?php if ($Yrlevel == '3') echo 'selected'; ?>>3</option>
                    <option value="4" <?php if ($Yrlevel == '4') echo 'selected'; ?>>4</option>
                </select>
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input class="w3-input w3-border" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label>Address:</label>
                <input class="w3-input w3-border" type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" required>
            </div>

            <div class="button-container">
                <button class="w3-button w3-blue w3-hover-green" type="submit">Update Profile</button>
                <button class="w3-button cancel" type="button" onclick="cancelEdit()">Cancel</button>
            </div>
        </form>
    </div>

    <script>
        function cancelEdit() {
            alert('Do you want to cancel editing your profile?');
            window.location.href = 'dashboard.php';
        }
    </script>

</body>
</html>
