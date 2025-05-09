<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);
$message = '';
$showSuccessModal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    $stmt = $conn->prepare("SELECT PASSWORD FROM users WHERE USERNAME = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($currentPassword, $user['PASSWORD'])) {
        $message = 'Current password is incorrect';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'New passwords do not match';
    } elseif (strlen($newPassword) < 8) {
        $message = 'Password must be at least 8 characters long';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET PASSWORD = ? WHERE USERNAME = ?");
        $stmt->bind_param("ss", $hashedPassword, $_SESSION['username']);
        
        if ($stmt->execute()) {
            $showSuccessModal = true;
        } else {
            $message = 'Error updating password';
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Change Password</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <li><a href="#" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-bell mr-1"></i>Notification</a></li>
                        <li><a href="dashboard.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="history.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-history mr-1"></i>History</a></li>
                        <li><a href="reservation.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-alt mr-1"></i>Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-indigo-900 px-6 py-2 rounded-lg font-bold hover:bg-yellow-500 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-out-alt mr-1"></i>Log out
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container mx-auto px-4 py-10 max-w-2xl">
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="p-8">
                <div class="flex items-center mb-8">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-lock text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold text-gray-800">Change Your Password</h2>
                        <p class="text-gray-500">Ensure your account is secure with a strong password</p>
                    </div>
                </div>

                <?php if ($message): ?>
                    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Current Password</label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-key text-gray-400"></i>
                            </div>
                            <input type="password" name="current_password" required 
                                class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div class="border-t border-gray-200 my-6 pt-6">
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">New Password</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" name="new_password" required minlength="8"
                                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                <div class="mt-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" name="confirm_password" required minlength="8"
                                        class="block w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="edit_profile.php" 
                            class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="font-medium text-blue-800 mb-2">Password Requirements:</h3>
            <ul class="space-y-1 text-sm text-blue-700">
                <li class="flex items-center">
                    <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                    Minimum 8 characters long
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                    Include both uppercase and lowercase letters
                </li>
                <li class="flex items-center">
                    <i class="fas fa-check-circle text-blue-500 mr-2"></i>
                    Include at least one number
                </li>
            </ul>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-8 max-w-sm mx-auto transform transition-all duration-300 scale-95">
            <div class="text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle text-5xl text-green-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Password Updated Successfully</h3>
                <p class="text-gray-500 mb-6">Your password has been changed.</p>
                <button onclick="window.location.href='edit_profile.php'" 
                    class="w-full px-6 py-3 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors duration-200">
                    Continue
                </button>
                <p class="text-sm text-gray-400 mt-3">(Press Enter)</p>
            </div>
        </div>
    </div>

    <script>
        <?php if ($showSuccessModal): ?>
        window.onload = function() {
            document.getElementById('successModal').classList.remove('hidden');
            document.getElementById('successModal').querySelector('.transform').classList.remove('scale-95');
            document.getElementById('successModal').querySelector('.transform').classList.add('scale-100');
        }
        <?php endif; ?>

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                const modal = document.getElementById('successModal');
                if (!modal.classList.contains('hidden')) {
                    window.location.href = 'edit_profile.php';
                }
            }
        });
    </script>
</body>
</html>