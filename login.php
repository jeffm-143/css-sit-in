<?php
session_start();

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST['Username']);
    $password = htmlspecialchars($_POST['Password']);

    $stmt = $conn->prepare("SELECT PASSWORD FROM users WHERE USERNAME = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($hashedPassword);
    $stmt->fetch();

    if ($stmt->num_rows > 0 && password_verify($password, $hashedPassword)) {
        $_SESSION['username'] = $username;
        echo "<script>
            alert('Login Successful! Welcome!');
            window.location.href = 'dashboard.php';
        </script>";
    } else {
        echo "<script>
            alert('Invalid username or password.');
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
    <title>Login</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #50ac6b);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.3);
            width: 400px;
            text-align: center;
        }
        .logo {
            width: 70px;
            margin-bottom: 10px;
            width: 50%; 
            left: 10px;
        }
        .csslogo {
            width: 70px;
            margin-bottom: 10px;
            width: 40%; 
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
        }
        .w3-input {
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .w3-button {
            width: 100%;
            background: #5461dd;
            color: white;
            font-size: 16px;
            padding: 10px;
            border-radius: 5px;
        }
        .w3-button:hover {
            background: #2b9ebb;
        }
        .register-link {
            margin-top: 10px;
            font-size: 14px;
        }
        .register-link a {
            color: #0066cc;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-container w3-card w3-animate-opacity">
        <img src="images/uc.png" alt="UC Logo" class="logo">
        <img src="images/css.png" alt="Css" class="csslogo">
        <h2>CCS Sit-in Monitoring System</h2>

        <form method="post" action="">
            <input class="w3-input w3-border" type="text" name="Username" placeholder="Username" required>
            <input class="w3-input w3-border" type="password" name="Password" placeholder="Password" required>
            <button class="w3-button w3-blue w3-hover-green" type="submit">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register.php">Register Here</a>
        </div>
    </div>

</body>
</html>
