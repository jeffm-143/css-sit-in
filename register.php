<?php 

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $IDNO = $_POST['IDNO'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    
    // Check for existing ID number
    $checkId = $conn->prepare("SELECT ID FROM users WHERE ID_NUMBER = ?");
    $checkId->bind_param("i", $IDNO);
    $checkId->execute();
    if($checkId->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'ID Number already exists!']);
        exit();
    }
    
    // Check for existing username
    $checkUsername = $conn->prepare("SELECT ID FROM users WHERE USERNAME = ?");
    $checkUsername->bind_param("s", $username);
    $checkUsername->execute();
    if($checkUsername->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username already taken!']);
        exit();
    }
    
    // Check for existing email
    $checkEmail = $conn->prepare("SELECT ID FROM users WHERE EMAIL = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    if($checkEmail->get_result()->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already registered!']);
        exit();
    }

    // If all checks pass, proceed with registration
    $lName = $_POST['lName'];
    $fName = $_POST['fName'];
    $MdName = $_POST['MdName'];
    $Course = $_POST['Course'];
    $Yrlevel = $_POST['Yrlevel'];
    $password = $_POST['password'];
    $address = $_POST['address'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, USERNAME, PASSWORD, EMAIL, ADDRESS, user_type) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'student')");
    $stmt->bind_param("issssissss", $IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $username, $hashedPassword, $email, $address);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $stmt->error]);
    }
    
    $stmt->close();
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | CCS Sit-in Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .input-group {
            position: relative;
            transition: all 0.3s ease;
        }
        .input-group:focus-within i {
            color: #60A5FA;
        }
        .form-input {
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }
        .form-input:focus {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-900 via-indigo-800 to-purple-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="glass-effect rounded-2xl p-8 shadow-2xl">
            <div class="text-center mb-8">
                <img src="images/css-new.png" alt="CCS Logo" class="mx-auto h-20 w-20 mb-4">
                <h2 class="text-3xl font-extrabold text-white tracking-tight">Create Your Account</h2>
                <p class="mt-2 text-sm text-gray-300">Join the CCS Laboratory Community</p>
            </div>

            <form method="post" action="" class="space-y-6">
                <!-- Student Information Section -->
                <div class="bg-white/5 rounded-xl p-6 space-y-4">
                    <h3 class="text-lg font-medium text-white mb-4">Student Information</h3>
                    
                    <div class="input-group">
                        <i class="fas fa-id-card absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="number" name="IDNO" placeholder="Student ID Number" required 
                               class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="input-group">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="fName" placeholder="First Name" required 
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="input-group">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="MdName" placeholder="Middle Name" 
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                        <div class="input-group">
                            <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input type="text" name="lName" placeholder="Last Name" required 
                                   class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="input-group">
                            <i class="fas fa-graduation-cap absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="Course" required 
                                    class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none appearance-none">
                                <option value="" disabled selected class="text-gray-400 bg-gray-800">Select Course</option>
                                <option value="BSIT" class="bg-gray-800">Bachelor of Science in Information Technology</option>
                                <option value="BSCS" class="bg-gray-800">Bachelor of Science in Computer Science</option>
                                <option value="BSIS" class="bg-gray-800">Bachelor of Science in Information Systems</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <i class="fas fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <select name="Yrlevel" required 
                                    class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none appearance-none">
                                <option value="" disabled selected class="text-gray-400 bg-gray-800">Select Year Level</option>
                                <option value="1" class="bg-gray-800">1st Year</option>
                                <option value="2" class="bg-gray-800">2nd Year</option>
                                <option value="3" class="bg-gray-800">3rd Year</option>
                                <option value="4" class="bg-gray-800">4th Year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Account Information Section -->
                <div class="bg-white/5 rounded-xl p-6 space-y-4">
                    <h3 class="text-lg font-medium text-white mb-4">Account Information</h3>
                    
                    <div class="input-group">
                        <i class="fas fa-user-circle absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" placeholder="Username" required 
                               class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" placeholder="Password" required 
                               class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" placeholder="Email Address" required 
                               class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-map-marker-alt absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="address" placeholder="Complete Address" required 
                               class="form-input w-full pl-10 pr-4 py-3 rounded-lg text-white placeholder-gray-400 border border-transparent focus:border-blue-500 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" 
                            class="w-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 px-6 rounded-lg font-semibold transform transition duration-300 hover:scale-[1.02] hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 focus:ring-offset-gray-900">
                        Create Account
                    </button>
                </div>
            </form>

            <p class="mt-6 text-center text-sm text-gray-300">
                Already have an account? 
                <a href="login.php" class="font-medium text-blue-400 hover:text-blue-300 transition-colors">
                    Sign In
                </a>
            </p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('form').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    type: 'POST',
                    url: 'register.php',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            alert(response.message);
                            window.location.href = 'login.php';
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('An error occurred. Please try again.');
                    }
                });
            });
        });
    </script>
</body>
</html>