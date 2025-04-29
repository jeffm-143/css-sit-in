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

    // Check if user exists and get their password and type
    $stmt = $conn->prepare("SELECT PASSWORD, user_type, ID FROM users WHERE USERNAME = ?");
    $stmt->bind_param("s", $inputUsername);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $hashedPassword = $user['PASSWORD'];
        $userType = $user['user_type'];

        // Verify password
        if (password_verify($inputPassword, $hashedPassword)) {
            $_SESSION['username'] = $inputUsername;
            $_SESSION['user_type'] = $userType;
            $_SESSION['user_id'] = $user['ID'];

            $_SESSION['success_message'] = 'Login successful!';
            header('Location: ' . ($userType === 'admin' ? 'admin-dashboard.php' : 'dashboard.php'));
            exit();
        } else {
            $_SESSION['error_message'] = 'Invalid username or password.';
        }
    } else {
        $_SESSION['error_message'] = 'Invalid username or password.';
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
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-blue-600 via-blue-500 to-green-500">

    <!-- Display success or error messages using modals -->
    <?php if (isset($_SESSION['success_message']) || isset($_SESSION['error_message'])): ?>
        <div id="messageModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-96">
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-bold <?php echo isset($_SESSION['success_message']) ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo isset($_SESSION['success_message']) ? 'Success' : 'Error'; ?>
                    </h3>
                    <button id="closeMessageModal" class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-4">
                    <p class="text-gray-700">
                        <?php 
                        if (isset($_SESSION['success_message'])) {
                            echo $_SESSION['success_message']; 
                            unset($_SESSION['success_message']);
                        } elseif (isset($_SESSION['error_message'])) {
                            echo $_SESSION['error_message']; 
                            unset($_SESSION['error_message']);
                        }
                        ?>
                    </p>
                </div>
                <div class="flex justify-end p-4 border-t">
                    <button id="dismissMessageModal" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Dismiss</button>
                </div>
            </div>
        </div>
        <script>
            const messageModal = document.getElementById('messageModal');
            const closeMessageModal = document.getElementById('closeMessageModal');
            const dismissMessageModal = document.getElementById('dismissMessageModal');

            closeMessageModal.addEventListener('click', () => {
                messageModal.classList.add('hidden');
            });

            dismissMessageModal.addEventListener('click', () => {
                messageModal.classList.add('hidden');
            });

            setTimeout(() => {
                messageModal.classList.add('hidden');
            }, 5000); // Automatically hide after 5 seconds

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    messageModal.classList.add('hidden');
                }
            });
        </script>
    <?php endif; ?>

    <div class="relative bg-white/20 backdrop-blur-md border border-white/100 shadow-2x2 rounded-xl p-8 w-full max-w-md text-center">

    <!-- Title -->
    <h2 class="text-2xl font-bold text-white tracking-wide mb-6">CCS Sit-in Monitoring System</h2>

    <!-- Logos -->
    <div class="flex justify-center items-center space-x-4 mb-6">
        <img src="images/uc.png" alt="UC Logo" class="h-16">
        <img src="images/css-new.png" alt="CSS Logo" class="h-14">
    </div>

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


    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            // Remove the preventDefault to allow normal form submission
            // e.preventDefault();

            // Ensure the form submits properly
            this.submit();
        });
    </script>

</body>
</html>
