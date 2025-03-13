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
    $inputUsername = htmlspecialchars($_POST['Username']);
    $inputPassword = htmlspecialchars($_POST['Password']);

    // Static admin credentials
    $adminUsername = 'admin';
    $adminPassword = 'admin123';

    if ($inputUsername === $adminUsername && $inputPassword === $adminPassword) {
        $_SESSION['username'] = $inputUsername;
        echo "<script>window.location.href='admin-dashboard.php';</script>";
    } else {
        $stmt = $conn->prepare("SELECT PASSWORD FROM users WHERE USERNAME = ?");
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($hashedPassword);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($inputPassword, $hashedPassword)) {
            $_SESSION['username'] = $inputUsername;
            echo "<script>window.location.href='dashboard.php';</script>";
        } else {
            $error = "Invalid username or password.";
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
    <title>Login - CCS Sit-in Monitoring</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-green-500">

    <div class="relative bg-white/20 backdrop-blur-md border border-white/100 shadow-2x2 rounded-xl p-8 w-full max-w-md text-center">
        
        <!-- Logos -->
        <div class="flex justify-center items-center space-x-4 mb-6">
            <img src="images/uc.png" alt="UC Logo" class="h-16">
            <img src="images/css-new.png" alt="CSS Logo" class="h-14">
        </div>

        <h2 class="text-2xl font-bold text-white tracking-wide mb-6">CCS Sit-in Monitoring System</h2>

        <!-- Login Form -->
        <form method="post" action="">
            <div class="mb-4">
                <input type="text" name="Username" placeholder="Username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-black/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div class="mb-4">
                <input type="password" name="Password" placeholder="Password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-black/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <button type="submit"
                class="w-full bg-blue-500 text-white font-semibold py-3 rounded-lg hover:bg-green-500 transition duration-300 shadow-md">
                Login
            </button>
        </form>

        <!-- Register Link -->
        <div class="mt-6 text-sm text-gray-200">
            Don't have an account? 
            <a href="register.php" class="text-yellow-400 font-medium hover:underline">Register Here</a>
        </div>
    </div>

</body>
</html>
