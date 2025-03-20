<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, EMAIL, ADDRESS, IMAGE FROM users WHERE USERNAME = ?");
$stmt->bind_param("s", $user);
$stmt->execute();
$stmt->bind_result($IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $email, $address, $profile_image);
$stmt->fetch();
$stmt->close();

$default_image = "images/css.png";
$profile_image = $profile_image ? $profile_image : $default_image;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newEmail = $_POST['email'];
    $newAddress = $_POST['address'];
    
    // Handle image upload
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE users SET EMAIL = ?, ADDRESS = ?, IMAGE = ? WHERE USERNAME = ?");
            $stmt->bind_param("ssss", $newEmail, $newAddress, $target_file, $user);
        }
    } else {
        $stmt = $conn->prepare("UPDATE users SET EMAIL = ?, ADDRESS = ? WHERE USERNAME = ?");
        $stmt->bind_param("sss", $newEmail, $newAddress, $user);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Profile updated successfully!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
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
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-navy-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Edit Profile</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <li><a href="#" class="text-white hover:text-yellow-400 transition">Notification</a></li>
                        <li><a href="dashboard.php" class="text-white hover:text-yellow-400 transition">Home</a></li>
                        <li><a href="edit_profile.php" class="text-white hover:text-yellow-400 transition">Edit Profile</a></li>
                        <li><a href="history.php" class="text-white hover:text-yellow-400 transition">History</a></li>
                        <li><a href="reservation.php" class="text-white hover:text-yellow-400 transition">Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-navy-800 px-4 py-2 rounded-lg font-bold hover:bg-navy-800 hover:text-yellow-400 transition duration-300">Log out</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">
                    <!-- Profile Image -->
                    <div class="flex flex-col items-center space-y-4">
                        <label for="profile_image_upload" class="cursor-pointer group relative">
                            <img id="preview_image" src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" 
                                class="w-32 h-32 rounded-full object-cover border-4 border-navy-800 group-hover:opacity-75 transition-opacity">
                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-camera text-white text-2xl"></i>
                            </div>
                            <input type="file" id="profile_image_upload" name="profile_image" class="hidden" accept="image/*" onchange="previewImage(this);">
                        </label>
                    </div>

                    <!-- Personal Information -->
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Read-only fields -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ID Number</label>
                                <input type="text" value="<?php echo htmlspecialchars($IDNO); ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">First Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($fName); ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Middle Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($MdName); ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($lName); ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md" readonly>
                            </div>
                        </div>

                        <!-- Editable fields -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Course</label>
                                <input type="text" value="<?php echo htmlspecialchars($Course); ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Year Level</label>
                                <input type="text" value="<?php echo htmlspecialchars($Yrlevel); ?>" class="mt-1 block w-full px-3 py-2 bg-gray-100 rounded-md" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-navy-800 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Address</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($address); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-navy-800 focus:border-transparent">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-center space-x-4">
                        <button type="submit" class="bg-navy-800 text-white px-6 py-2 rounded-lg font-semibold hover:bg-navy-700 transition duration-300">
                            Save Changes
                        </button>
                        <a href="dashboard.php" class="bg-red-500 text-white px-6 py-2 rounded-lg font-semibold hover:bg-gray-400 transition duration-300">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

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

        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': {
                            700: '#000066',
                            800: '#000080',
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>
