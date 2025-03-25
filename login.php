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

    // Check if user exists and get their password and type
    $stmt = $conn->prepare("SELECT PASSWORD, user_type FROM users WHERE USERNAME = ?");
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
            
            // Instead of immediate redirect, return success status
            echo json_encode(['success' => true, 'userType' => $userType]);
            exit();
        }
    }
    $error = "Invalid username or password.";
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

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-sm mx-auto">
            <div class="text-center">
                <svg class="mx-auto mb-4 w-14 h-14 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Login Successful!</h3>
                <p class="text-gray-500 mb-4">You will be redirected to the dashboard.</p>
            </div>
        </div>
    </div>

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

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch('', {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const modal = document.getElementById('successModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    
                    const modalContent = modal.querySelector('.text-center');
                    let okButton = modalContent.querySelector('button');

                    if (!okButton) {
                        okButton = document.createElement('button');
                        okButton.textContent = 'OK';
                        okButton.className = 'mt-4 bg-blue-500 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-500 transition duration-300';
                        okButton.addEventListener('click', () => {
                            window.location.href = data.userType === 'admin' ? 'admin-dashboard.php' : 'dashboard.php';
                        });
                        modalContent.appendChild(okButton);
                    }
                    const enterKeyListener = function(event) {
                        if (event.key === 'Enter') {
                            window.location.href = data.userType === 'admin' ? 'admin-dashboard.php' : 'dashboard.php';
                        }
                    };
                    document.removeEventListener('keydown', enterKeyListener);
                    document.addEventListener('keydown', enterKeyListener);
                }
            });
        });
    </script>
    </script>
    </script>
    </script>
</body>
</html>
