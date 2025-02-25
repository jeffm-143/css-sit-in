<?php

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'user'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $IDNO = $_POST['IDNO'];
    $lName = $_POST['lName'];
    $fName = $_POST['fName'];
    $MdName = $_POST['MdName'];
    $Course = $_POST['Course'];
    $Yrlevel = $_POST['Yrlevel'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (IDNO, lName, fName, MdName, Course, Yrlevel, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $username, $hashedPassword);

    if ($stmt->execute()) {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";
        echo "<script>
            Swal.fire({
                title: 'Good job!',
                text: 'Registration successful!',
                icon: 'success'
            }).then(() => {
                window.location.href = 'login.php';
            });
        </script>";
        exit();
    }
     else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> <!-- Icons -->
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #50ac6b);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .w3-container {
            width: 100%;
            max-width: 500px; 
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .w3-input, .w3-select {
            background: #f1f1f1;
            border: none;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
            width: 100%;
        }

        .w3-input:focus, .w3-select:focus {
            background: #e0e0e0;
        }

        .w3-button {
            background:rgb(10, 47, 214);
            color: white;
            padding: 10px;
            border-radius: 8px;
            font-size: 16=px;
            cursor: pointer;
            transition: 0.3s;
        }

        .w3-button:hover {
            background: #667eea;
        }

        .input-group {
            display: flex;
            align-items: center;
            background: #f1f1f1;
            padding: 3px;
            border-radius: 5px;
            margin-bottom: 8px;
        }

        .input-group i {
            margin-right: 10px;
            color: #667eea;
        }

        .w3-center a {
            color: #667eea;
        }
    </style>
</head>
<body>
    <div class="w3-container w3-animate-opacity">
        <h1 class="w3-center">Create an Account</h1>
        <form method="post" action="">  
            <div class="input-group">
                <i class="fas fa-id-badge"></i>
                <input type="text" name="IDNO" id="IDNO" class="w3-input" placeholder="ID Number" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="lName" id="lName" class="w3-input" placeholder="Last Name" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="fName" id="fName" class="w3-input" placeholder="First Name" required>
            </div>
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="MdName" id="MdName" class="w3-input" placeholder="Middle Name" required>
            </div>
            <div class="input-group">
                <i class="fas fa-graduation-cap"></i>
                <select name="Course" id="Course" class="w3-select" required>
                    <option value="" disabled selected>Select Course</option>
                    <option value="Computer Science">BSIT</option>
                    <option value="Engineering">BSED</option>
                    <option value="Business Administration">Business Administration</option>
                </select>
            </div>
            <div class="input-group">
                <i class="fas fa-layer-group"></i>
                <select name="Yrlevel" id="Yrlevel" class="w3-select" required>
                    <option value="" disabled selected>Select Year Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
            </div>
            <div class="input-group">
                <i class="fas fa-user-circle"></i>
                <input type="text" name="username" id="username" class="w3-input" placeholder="Username" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="w3-input" placeholder="Password" required>
            </div>
            <button type="submit" class="w3-button w3-block w3-hover-green">Register</button>
        </form>
        <p class="w3-center w3-padding-16 ">Already have an account? <a href="login.php">Login Here</a></p>
    </div>
</body>
</html>
