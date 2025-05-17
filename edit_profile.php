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
$showSuccessModal = false;

$user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, EMAIL, ADDRESS, IMAGE, SESSION FROM users WHERE USERNAME = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $email, $address, $profile_image, $sessions);
$stmt->fetch();
$stmt->close();

$default_image = "images/css.png";
$profile_image = $profile_image ? $profile_image : $default_image;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newFirstName = $_POST['firstname'];
    $newLastName = $_POST['lastname'];
    $newMiddleName = $_POST['middlename'];
    $newAddress = $_POST['address'];
    $hasChanges = false;

    if ($newFirstName !== $fName || $newLastName !== $lName || 
        $newMiddleName !== $MdName || $newAddress !== $address ||
        isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $hasChanges = true;
    }

    if ($hasChanges) {
        if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $stmt = $conn->prepare("UPDATE users SET FIRSTNAME = ?, LASTNAME = ?, MIDDLENAME = ?, ADDRESS = ?, IMAGE = ? WHERE USERNAME = ?");
                $stmt->bind_param("ssssss", $newFirstName, $newLastName, $newMiddleName, $newAddress, $target_file, $user);
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET FIRSTNAME = ?, LASTNAME = ?, MIDDLENAME = ?, ADDRESS = ? WHERE USERNAME = ?");
            $stmt->bind_param("sssss", $newFirstName, $newLastName, $newMiddleName, $newAddress, $user);
        }

        if ($stmt->execute()) {
            $showSuccessModal = true;
            $fName = $newFirstName;
            $lName = $newLastName;
            $MdName = $newMiddleName;
            $address = $newAddress;
        }
        $stmt->close();
    }
}

//$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        
        .profile-gradient {
            background: linear-gradient(120deg, #e0f2fe, #dbeafe, #e0e7ff);
        }
        
        .custom-shadow {
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -5px rgba(59, 130, 246, 0.04);
        }
        
        .profile-pic-upload-overlay {
            opacity: 0;
            transition: all 0.3s ease;
            background: rgba(37, 99, 235, 0.8);
        }
        
        .profile-pic-container:hover .profile-pic-upload-overlay {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">History</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <!-- Notification Bell -->
                        <li class="relative">
                            <button id="notificationButton" class="text-white hover:text-yellow-400 transition-colors">
                                <i class="fas fa-bell text-xl"></i>
                                <?php
                                $notif_count = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE ID_NUMBER = ? AND is_read = 0");
                                $notif_count->bind_param("i", $IDNO);
                                $notif_count->execute();
                                $count = $notif_count->get_result()->fetch_assoc()['count'];
                                if ($count > 0):
                                ?>
                                <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="notificationCount">
                                    <?php echo $count; ?>
                                </span>
                                <?php endif; ?>
                            </button>
                            <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
                                <div class="p-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-800">Notifications</h3>
                                </div>
                                <div class="max-h-96 overflow-y-auto" id="notificationList">
                                    <?php
                                    $notifications_query = $conn->prepare("
                                        SELECT * FROM notifications 
                                        WHERE ID_NUMBER = ? AND is_read = 0 
                                        ORDER BY created_at DESC LIMIT 5
                                    ");
                                    $notifications_query->bind_param("i", $IDNO);
                                    $notifications_query->execute();
                                    $notifications = $notifications_query->get_result();
                                    
                                    if ($notifications->num_rows > 0):
                                        while($notif = $notifications->fetch_assoc()):
                                    ?>
                                        <div class="notification-item p-4 border-b border-gray-100 hover:bg-gray-50" 
                                             data-notification-id="<?php echo $notif['id']; ?>" 
                                             style="transition: opacity 0.3s ease-out;">
                                            <div class="flex justify-between">
                                                <div>
                                                    <p class="text-sm text-gray-800"><?php echo htmlspecialchars($notif['message']); ?></p>
                                                    <p class="text-xs text-gray-500 mt-1"><?php echo date('M d, Y h:i A', strtotime($notif['created_at'])); ?></p>
                                                </div>
                                                <?php if (!$notif['is_read']): ?>
                                                <button onclick="markAsRead(<?php echo $notif['id']; ?>, this)" 
                                                        class="text-xs text-blue-600 hover:text-blue-800 ml-2">
                                                    Mark as read
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <div class="p-4 text-center text-gray-500">
                                            No notifications
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </li>
                        <li><a href="dashboard.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="history.php" class="text-yellow-400 font-bold transition-colors"><i class="fas fa-history mr-1"></i>History</a></li>
                        <li><a href="user_labsched.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-clock mr-1"></i>Lab Schedule</a></li>
                        <li><a href="user_resources.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-book mr-1"></i>Lab Resources</a></li>
                        <li><a href="reservation.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-alt mr-1"></i>Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-indigo-900 px-6 py-2 rounded-lg font-bold hover:bg-yellow-500 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-out-alt mr-1"></i>Log out
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container mx-auto px-4 py-10 max-w-5xl">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
            <p class="text-gray-500 mt-2">Update your personal information and profile picture</p>
        </div>
        
        <!-- Main Content -->
        <div class="bg-white rounded-2xl shadow-lg custom-shadow overflow-hidden">
            <!-- Profile Header -->
            <div class="profile-gradient p-6 border-b border-blue-100 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="bg-blue-100 rounded-full p-2">
                        <i class="fas fa-user-edit text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-xl font-semibold text-gray-800">Edit Profile Information</h2>
                        <p class="text-sm text-gray-500">Make changes to your profile and save when done</p>
                    </div>
                </div>
                <div class="hidden sm:block">
                    <div class="flex items-center px-4 py-2 bg-blue-50 rounded-full text-blue-700 text-sm">
                        <i class="fas fa-ticket-alt mr-2"></i>
                        <span class="font-medium">Remaining Sessions: <?php echo $sessions; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Form Content -->
            <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row gap-10">
                        <!-- Left: Profile Picture -->
                        <div class="md:w-1/3 flex flex-col items-center">
                            <div class="profile-pic-container relative group mb-6 mt-4">
                                <div class="w-48 h-48 rounded-full overflow-hidden border-4 border-white shadow-md mx-auto">
                                    <img id="preview_image" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" 
                                        class="w-full h-full object-cover">
                                </div>
                                <div class="absolute inset-0 profile-pic-upload-overlay rounded-full flex flex-col items-center justify-center text-white">
                                    <i class="fas fa-camera text-2xl mb-2"></i>
                                    <span class="font-medium text-sm">Change Photo</span>
                                </div>
                                <label for="profile_image" class="absolute inset-0 cursor-pointer">
                                    <span class="sr-only">Choose New Photo</span>
                                </label>
                                <input type="file" id="profile_image" name="profile_image" class="hidden" accept="image/*" onchange="previewImage(this);">
                            </div>
                            <div class="text-center space-y-2">
                                <h3 class="font-medium text-lg text-gray-800">
                                    <?php echo htmlspecialchars($fName . ' ' . $lName); ?>
                                </h3>
                                <p class="text-blue-600 font-medium"><?php echo htmlspecialchars($IDNO); ?></p>
                                <div class="flex justify-center mt-4">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="fas fa-graduation-cap mr-1"></i> <?php echo htmlspecialchars($Course); ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Sessions Progress -->
                            <div class="mt-4 w-full bg-gradient-to-r from-gray-50 to-white rounded-lg p-3.5 shadow-sm border border-gray-100">
                                <?php
                                    $max_sessions = 30;
                                    $current = intval($sessions);
                                    $percentage = min(100, ($current / $max_sessions) * 100);
                                    
                                    if ($percentage > 60) {
                                        $gradient = 'from-blue-400 to-blue-500';
                                        $status = 'Good';
                                        $status_color = 'text-blue-500';
                                    } else if ($percentage > 30) {
                                        $gradient = 'from-yellow-400 to-yellow-500';
                                        $status = 'Medium';
                                        $status_color = 'text-yellow-500';
                                    } else {
                                        $gradient = 'from-red-400 to-red-500';
                                        $status = 'Low';
                                        $status_color = 'text-red-500';
                                    }
                                ?>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs font-medium text-gray-600">Remaining Sessions</span>
                                    <div class="flex items-center">
                                        <span class="font-semibold text-blue-600"><?php echo $sessions; ?></span>
                                        <span class="text-gray-400 text-xs ml-0.5">/30</span>
                                    </div>
                                </div>
                                
                                <div class="relative w-full h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="absolute top-0 left-0 h-full bg-gradient-to-r <?php echo $gradient; ?> rounded-full transition-all duration-500" 
                                        style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                
                                <div class="flex justify-between mt-1.5">
                                    <div class="text-xs text-gray-400">
                                        <?php if ($percentage < 30): ?>
                                            <i class="fas fa-exclamation-circle text-red-500 mr-1"></i> Running low
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-xs font-medium <?php echo $status_color; ?>">
                                        <?php echo $status; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Change Password Button -->
                            <div class="mt-6 w-full">
                                <a href="password_edit.php" class="group relative flex items-center justify-center px-5 py-3 bg-gradient-to-r from-blue-50 to-indigo-50 text-blue-700 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 border border-blue-100 w-full">
                                    <div class="absolute inset-0 w-3 bg-gradient-to-r from-blue-500 to-indigo-500 transform -skew-x-12 -translate-x-full group-hover:translate-x-0 transition-transform duration-500"></div>
                                    <div class="relative flex items-center">
                                        <span class="flex items-center justify-center w-9 h-9 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 text-white mr-3">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                        <span>
                                            <span class="block font-medium">Change Password</span>
                                            <span class="text-xs text-blue-600 opacity-80">Secure your account</span>
                                        </span>
                                    </div>
                                    <i class="fas fa-chevron-right ml-auto text-blue-400 group-hover:text-blue-600 group-hover:translate-x-1 transition-all duration-300"></i>
                                </a>
                            </div>
                        </div>
                        
                        <!-- Right: Form Fields -->
                        <div class="md:w-2/3">
                            <!-- Account Information -->
                            <div class="bg-gray-50 p-5 rounded-xl mb-6">
                                <h3 class="text-sm font-medium text-gray-600 mb-3">ACCOUNT INFORMATION</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <!-- ID Number -->
                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">ID Number</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-id-card text-gray-400"></i>
                                            </div>
                                            <input type="text" value="<?php echo htmlspecialchars($IDNO); ?>" 
                                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500" readonly>
                                        </div>
                                    </div>
                                    
                                    <!-- Course -->
                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Course</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-graduation-cap text-gray-400"></i>
                                            </div>
                                            <input type="text" value="<?php echo htmlspecialchars($Course); ?>" 
                                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500" readonly>
                                        </div>
                                    </div>
                                    
                                    <!-- Year Level -->
                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Year Level</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-layer-group text-gray-400"></i>
                                            </div>
                                            <input type="text" value="<?php echo htmlspecialchars($Yrlevel); ?>" 
                                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500" readonly>
                                        </div>
                                    </div>
                                    
                                    <!-- Email -->
                                    <div>
                                        <label class="block text-gray-700 text-sm font-medium mb-2">Email</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i class="fas fa-envelope text-gray-400"></i>
                                            </div>
                                            <input type="email" value="<?php echo htmlspecialchars($email); ?>" 
                                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-500" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="bg-gray-50 p-5 rounded-xl">
                                <h3 class="text-sm font-medium text-gray-600 mb-3">PERSONAL INFORMATION</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i class="fas fa-user text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input type="text" name="firstname" value="<?php echo htmlspecialchars($fName); ?>" 
                                                class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i class="fas fa-user text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input type="text" name="lastname" value="<?php echo htmlspecialchars($lName); ?>" 
                                                class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Middle Name</label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i class="fas fa-user text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input type="text" name="middlename" value="<?php echo htmlspecialchars($MdName); ?>" 
                                                class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" required>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                                <i class="fas fa-home text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                                            </div>
                                            <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" 
                                                class="block w-full pl-11 pr-4 py-3 rounded-xl border border-gray-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4">
                    <a href="dashboard.php" 
                       class="px-6 py-3 rounded-xl text-gray-700 bg-white border border-gray-200 hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                        Cancel
                    </a>
                    <button type="submit" name="update" 
                            class="px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 flex items-center">
                        <i class="fas fa-save mr-2"></i>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-sm mx-auto transform transition-all duration-300">
            <div class="text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-3xl text-green-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Changes Saved!</h3>
                <p class="text-gray-600 mb-6">Your profile has been updated successfully.</p>
                <button onclick="closeModal()" 
                    class="w-full px-6 py-3 rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 text-white hover:from-green-600 hover:to-emerald-600 transition-all duration-200">
                    Continue
                </button>
                <p class="text-sm text-gray-400 mt-3">(Press Enter)</p>
            </div>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('preview_image').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function showModal() {
            const modal = document.getElementById('successModal');
            modal.classList.remove('hidden');
            modal.querySelector('.transform').classList.add('scale-100');
            modal.querySelector('.transform').classList.remove('scale-95');
        }

        function closeModal() {
            const modal = document.getElementById('successModal');
            modal.querySelector('.transform').classList.remove('scale-100');
            modal.querySelector('.transform').classList.add('scale-95');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                const modal = document.getElementById('successModal');
                if (!modal.classList.contains('hidden')) {
                    closeModal();
                }
            }
        });

        // Initialize UI enhancements
        document.querySelectorAll('.group').forEach(group => {
            const input = group.querySelector('input');
            const icon = group.querySelector('i');
            
            if (input && icon) {
                input.addEventListener('focus', () => {
                    icon.classList.add('text-indigo-500');
                    input.closest('.relative').classList.add('ring-2', 'ring-indigo-200');
                });
                
                input.addEventListener('blur', () => {
                    icon.classList.remove('text-indigo-500');
                    input.closest('.relative').classList.remove('ring-2', 'ring-indigo-200');
                });
            }
        });

        <?php if ($showSuccessModal): ?>
            window.onload = function() {
                showModal();
            }
        <?php endif; ?>
    </script>
</body>
</html>
