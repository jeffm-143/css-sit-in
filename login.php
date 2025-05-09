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
        $_SESSION['login_time'] = time();
        $_SESSION['show_welcome'] = true; // Add this flag
        
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
    <title>Login - CCS Laboratory System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .glass-morphism {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-delayed {
            animation: float 6s ease-in-out infinite;
            animation-delay: 2s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }
        
        .bg-pattern {
            background-image: url('images/pattern.png');
            background-repeat: repeat;
            background-size: 100px;
            opacity: 0.1;
        }

        .input-group {
            position: relative;
            transition: all 0.3s ease;
        }

        .input-group:focus-within i {
            color: #3B82F6;
        }

        .input-field {
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .input-field:focus {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center relative overflow-hidden">
    <!-- Background Elements -->
    <div class="fixed inset-0 bg-gradient-to-br from-blue-900 via-indigo-900 to-purple-900"></div>
    <div class="fixed inset-0 bg-pattern"></div>
    
    <!-- Decorative Circles -->
    <div class="fixed top-0 left-0 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2"></div>
    <div class="fixed bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full filter blur-3xl opacity-20 translate-x-1/2 translate-y-1/2"></div>

    <!-- Main Content -->
    <div class="glass-morphism rounded-2xl p-8 w-full max-w-md mx-4 relative z-10">
        <!-- Logo Section -->
        <div class="text-center mb-8 relative">
            <div class="flex justify-center items-center mb-6 space-x-8">
                <img src="images/uc.png" alt="UC Logo" class="h-20 floating">
                <img src="images/css-new.png" alt="CCS Logo" class="h-16 floating-delayed">
            </div>
            <h2 class="text-3xl font-bold text-white mb-2">Welcome Back!</h2>
            <p class="text-blue-200 text-sm">Sign in to CCS Laboratory System</p>
        </div>

        <!-- Login Form -->
        <form method="post" action="" class="space-y-6">
            <div class="space-y-4">
                <div class="input-group">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="Username" required placeholder="Username" 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>

                <div class="input-group">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="password" name="Password" required placeholder="Password" 
                           class="input-field w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
            </div>

            <button type="submit" id="loginBtn" 
                    class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 rounded-lg font-semibold transition-all duration-300 hover:from-blue-600 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900 transform hover:scale-[1.02]">
                Sign In
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-gray-400">
            Don't have an account? 
            <a href="register.php" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">
                Register here →
            </a>
        </p>
    </div>

    <?php if (isset($alertType) && isset($alertMessage)): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo $alertType === "success" ? "Success!" : "Error!"; ?>',
            text: '<?php echo $alertMessage; ?>',
            timer: <?php echo $alertType === 'success' ? '1500' : 'null'; ?>,
            timerProgressBar: true,
            showConfirmButton: true,
            confirmButtonText: 'Continue',
            allowEnterKey: true
        })<?php if (isset($redirectUrl)): ?>.then((result) => {
            if ('<?php echo $alertType; ?>' === 'success' && (result.isConfirmed || result.dismiss === Swal.DismissReason.timer)) {
                window.location.href = '<?php echo $redirectUrl; ?>';
            }
        })<?php endif; ?>;
    </script>
    <?php endif; ?>

    <script>
        document.getElementById('loginBtn').addEventListener('click', function() {
            this.innerHTML = `
                <div class="flex items-center justify-center">
                    <div class="animate-spin rounded-full h-5 w-5 border-2 border-t-transparent border-white mr-2"></div>
                    <span>Signing in...</span>
                </div>
            `;
        });
    </script>
</body>
</html>
