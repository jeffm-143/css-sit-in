<?php 

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

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
    $email = $_POST['email'];
    $address = $_POST['address'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, USERNAME, PASSWORD, EMAIL, ADDRESS) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssissss", $IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $username, $hashedPassword, $email, $address);

    if ($stmt->execute()) {
        echo "<script>
            alert('Registration successful!');
            window.location.href = 'login.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Error: " . $stmt->error . "');
        </script>";
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
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> 
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
            max-width: 400px; 
            padding: 15px; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
            text-align: center;
        }

        .w3-input, .w3-select {
            background: #f1f1f1;
            border: none;
            padding: 5px;

            font-size: 15px;
            width: 100%;
        }

        .w3-input:focus, .w3-select:focus {
            background: #e0e0e0;
        }

        .w3-button {
            background: #5461dd;
            color: white;
            padding: 8px;
            border-radius: 8px;
            font-size: 15px;
            cursor: pointer;
            transition: 0.3s;
        }

        .w3-button:hover {
            background: #2b9ebb;
        }

        .input-group {
            display: flex;
            align-items: center;
            background: #f1f1f1;
            padding: 3px;
            border-radius: 5px;
            margin-bottom: 6px; /* Reduced margin */
        }

        .input-group i {
            margin-right: 10px;
            color: #667eea;
        }

        .w3-center a {
            color: #0066cc;
        }
        
    </style>
</head>
<body>
    <div class="w3-container w3-animate-opacity">
        <h1 class="w3-center">Create an Account</h1>
        <form method="post" action="">  
        <div class="input-group" style="position: relative;">
            <i class="fas fa-id-badge" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="number" name="IDNO" id="IDNO" class="w3-input" placeholder="ID Number" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-user" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="text" name="lName" id="lName" class="w3-input" placeholder="Last Name" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-user" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="text" name="fName" id="fName" class="w3-input" placeholder="First Name" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-user" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="text" name="MdName" id="MdName" class="w3-input" placeholder="Middle Name" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative; justify-content: center;">
            <i class="fas fa-graduation-cap" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <select name="Course" id="Course" class="w3-select" required style="padding-left: 30px;">
                <option value="" disabled selected>Select Course</option>
                <option value="BSIT">Bachelor of Science in Information Technology (BSIT)</option>
                <option value="BSED">Bachelor of Secondary Education (BSED)</option>
                <option value="BSBA">Bachelor of Science in Business Administration (BSBA)</option>
                <option value="BSNursing">Bachelor of Science in Nursing (BSNursing)</option>
                <option value="BSEducation">Bachelor of Science in Education (BSEducation)</option>
                <option value="BSPsychology">Bachelor of Science in Psychology (BSPsychology)</option>
                <option value="BSArchitecture">Bachelor of Science in Architecture (BSArchitecture)</option>
                <option value="LLB">Bachelor of Laws (LLB)</option>
            </select>
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-layer-group" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <select name="Yrlevel" id="Yrlevel" class="w3-select" required style="padding-left: 30px;">
                <option value="" disabled selected>Select Year Level</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
            </select>
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-user-circle" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="text" name="username" id="username" class="w3-input" placeholder="Username" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-lock" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="password" name="password" id="password" class="w3-input" placeholder="Password" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-envelope" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="email" name="email" id="email" class="w3-input" placeholder="Email" required style="padding-left: 30px;">
        </div>

        <div class="input-group" style="position: relative;">
            <i class="fas fa-home" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: black;"></i>
            <input type="text" name="address" id="address" class="w3-input" placeholder="Address" required style="padding-left: 30px;">
        </div>


            <button type="submit" class="w3-button w3-block w3-blue w3-hover-green">Register</button>
        </form>
        <p class="w3-center w3-padding-16 ">Already have an account? <a href="login.php">Login Here</a></p>
    </div>
</body>
</html>
