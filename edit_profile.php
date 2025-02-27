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
    $IDNO = $_POST['IDNO'];
    $lName = $_POST['lName'];
    $fName = $_POST['fName'];
    $MdName = $_POST['MdName'];
    $Course = $_POST['Course'];
    $Yrlevel = $_POST['Yrlevel'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $target_file;
        } else {
            echo "<script>alert('Error uploading image. Please try again.');</script>";
        }
    }

    $stmt = $conn->prepare("UPDATE users SET ID_NUMBER = ?, LASTNAME = ?, FIRSTNAME = ?, MIDDLENAME = ?, COURSE = ?, YEAR = ?, EMAIL = ?, ADDRESS = ?, IMAGE = ? WHERE USERNAME = ?");
    $stmt->bind_param("ssssssssss", $IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $email, $address, $profile_image, $user);

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Error updating profile. Please try again.');</script>";
    }

    $stmt->close();
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
            display: flex;
            justify-content: center;
            height: 100vh;
        }

        .edit-profile-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
            width: 40%; /* Keep container compact */
            text-align: center;
            height: 690px; /* Adjust height dynamically */
            margin-top: 10px; /* Push to the top */
        }

        .edit-profile-container h2 {
            margin-bottom: 10px;
            color: #333;
        }
        .w3-input, .w3-select {
            margin-bottom: 15px;
            border-radius: 5px;
            width: 100%;
        }
        .w3-button {
            width: 50%;
            background: #5461dd;
            color: white;
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
        }
        .w3-button:hover {
            background: #2b9ebb;
        }
    </style>
</head>
<body>
    <div class="edit-profile-container w3-card w3-animate-opacity">
        <h2>Edit Profile</h2>
        <form method="post" action="" enctype="multipart/form-data">
            <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" style="width: 60px; height: 60px; border-radius: 60%; margin-bottom: 5px;">
            <input class="w3-input w3-border" type="file" name="profile_image">
            <input class="w3-input w3-border" type="text" name="IDNO" value="<?php echo htmlspecialchars($IDNO); ?>" placeholder="ID Number" required>
            <input class="w3-input w3-border" type="text" name="lName" value="<?php echo htmlspecialchars($lName); ?>" placeholder="Last Name" required>
            <input class="w3-input w3-border" type="text" name="fName" value="<?php echo htmlspecialchars($fName); ?>" placeholder="First Name" required>
            <input class="w3-input w3-border" type="text" name="MdName" value="<?php echo htmlspecialchars($MdName); ?>" placeholder="Middle Name" required>
            <select class="w3-select w3-border" name="Course" required>
                <option value="" disabled>Select Course</option>
                <option value="Computer Science" <?php if ($Course == 'Computer Science') echo 'selected'; ?>>BSIT</option>
                <option value="Engineering" <?php if ($Course == 'Engineering') echo 'selected'; ?>>BSED</option>
                <option value="Business Administration" <?php if ($Course == 'Business Administration') echo 'selected'; ?>>Business Administration</option>
            </select>
            <select class="w3-select w3-border" name="Yrlevel" required>
                <option value="" disabled>Select Year Level</option>
                <option value="1" <?php if ($Yrlevel == '1') echo 'selected'; ?>>1st Year</option>
                <option value="2" <?php if ($Yrlevel == '2') echo 'selected'; ?>>2nd Year</option>
                <option value="3" <?php if ($Yrlevel == '3') echo 'selected'; ?>>3rd Year</option>
                <option value="4" <?php if ($Yrlevel == '4') echo 'selected'; ?>>4th Year</option>
            </select>
            <input class="w3-input w3-border" type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email" required>
            <input class="w3-input w3-border" type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" placeholder="Address" required>
            <button class="w3-button w3-blue w3-hover-green" type="submit">Update Profile</button>
        </form>
    </div>
</body>
</html>
