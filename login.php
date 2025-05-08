<?php
session_start();

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = htmlspecialchars($_POST['Username']);
    $inputPassword = htmlspecialchars($_POST['Password']);

    $stmt = $conn->prepare("SELECT ID, PASSWORD, user_type FROM users WHERE USERNAME = ?");
    $stmt->bind_param("s", $inputUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($inputPassword, $user['PASSWORD'])) {
        $_SESSION['username'] = $inputUsername;
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['user_id'] = $user['ID'];
        
        // Set success message and redirect
        $alertType = 'success';
        $alertMessage = 'Login successful! Redirecting...';
        
        $redirectUrl = $user['user_type'] === 'admin' ? 'admin-dashboard.php' : 'dashboard.php';
    } else {
        $alertType = 'error';
        $alertMessage = 'Invalid username or password.';
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
    <title>Login - CCS Sit-in Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-green-500">
    <?php if (isset($alertType) && isset($alertMessage)): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $alertType; ?>',
                title: '<?php echo $alertType === "success" ? "Success!" : "Error!"; ?>',
                text: '<?php echo $alertMessage; ?>',
                timer: <?php echo $alertType === 'success' ? '1500' : 'null'; ?>,
                timerProgressBar: <?php echo $alertType === 'success' ? 'true' : 'false'; ?>,
                showConfirmButton: <?php echo $alertType === 'success' ? 'false' : 'true'; ?>
            }).then(() => {
                <?php if (isset($redirectUrl)): ?>
                    window.location.href = '<?php echo $redirectUrl; ?>';
                <?php endif; ?>
            });
        </script>
    <?php endif; ?>

    <div class="relative bg-white/20 backdrop-blur-md border border-white/20 shadow-2xl rounded-xl p-8 w-full max-w-md text-center">
        <!-- Title -->
        <h2 class="text-2xl font-bold text-white tracking-wide mb-6">CCS Sit-in Monitoring System</h2>

        <!-- Logos -->
        <div class="flex justify-center items-center space-x-4 mb-6">
            <img src="images/uc.png" alt="UC Logo" class="h-16">
            <img src="images/css-new.png" alt="CSS Logo" class="h-14">
        </div>

        <!-- Login Form -->
        <form method="post" action="" class="space-y-6" id="loginForm">
            <div class="space-y-4">
                <div>
                    <input type="text" name="Username" placeholder="Username" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-black/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <input type="password" name="Password" placeholder="Password" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-black/20 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <button type="submit" id="loginButton"
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

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const loginButton = document.getElementById('loginButton');
            loginButton.disabled = true;
            loginButton.innerHTML = `
                <div class="inline-block align-middle w-4 h-4 border-2 border-t-transparent border-white rounded-full animate-spin mr-2"></div>
                Logging in...
            `;
        });
    </script>
</body>
</html>
