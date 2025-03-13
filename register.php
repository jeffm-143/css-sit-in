<?php 

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $IDNO = $_POST['IDNO'];
    $lName = $_POST['lName'];
    $fName = $_POST['fName'];
    $MdName = $_POST['MdName'];
    $Course = $_POST['Course'];
    $Yrlevel = $_POST['Yrlevel'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (ID_NUMBER, LASTNAME, FIRSTNAME, MIDDLENAME, COURSE, YEAR, USERNAME, PASSWORD, EMAIL, ADDRESS) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssissss", $IDNO, $lName, $fName, $MdName, $Course, $Yrlevel, $username, $hashedPassword, $email, $address);

    if ($stmt->execute()) {
        echo "<script>
            alert('Registration successful!');
            window.location.href = 'login.php';
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Error: " . $stmt->error . "');
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
    <title>Register | CCS Sit-in Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>
<body class="flex items-center justify-center min-h-screen bg-gradient-to-br from-indigo-800 via-blue-700 to-green-600">
    <div class="bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl rounded-xl p-8 w-full max-w-lg text-center">
        <h2 class="text-3xl font-extrabold text-white tracking-wide mb-6">Create Your Account</h2>

        <form method="post" action="" class="space-y-5">
            <!-- ID Number -->
            <div class="relative">
                <i class="fas fa-id-badge absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                <input type="number" name="IDNO" placeholder="ID Number" required 
                    class="w-full pl-12 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
            </div>

            <!-- Name Fields (Same Level) -->
            <div class="grid grid-cols-3 gap-4">
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                    <input type="text" name="fName" placeholder="First Name" required 
                        class="w-full pl-7 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
                </div>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                    <input type="text" name="MdName" placeholder="Middle Name" 
                        class="w-full pl-5 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
                </div>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                    <input type="text" name="lName" placeholder="Last Name" required 
                        class="w-full pl-7 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
                </div>
            </div>  

            <!-- Course -->
            <div class="relative">
                <i class="fas fa-graduation-cap absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                <select name="Course" required class="w-full pl-12 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition bg-transparent">
                    <option value="" disabled selected class="bg-gray-700 text-white">Select Course</option>
                    <option value="BSIT" class="bg-gray-700 text-white">BSIT</option>
                    <option value="BSED" class="bg-gray-700 text-white">BSED</option>
                    <option value="BSBA" class="bg-gray-700 text-white">BSBA</option>
                </select>
            </div>

            <!-- Username -->
            <div class="relative">
                <i class="fas fa-user-circle absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                <input type="text" name="username" placeholder="Username" required 
                    class="w-full pl-12 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
            </div>

            <!-- Password -->
            <div class="relative">
                <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                <input type="password" name="password" placeholder="Password" required 
                    class="w-full pl-12 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
            </div>

            <!-- Email -->
            <div class="relative">
                <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                <input type="email" name="email" placeholder="Email" required 
                    class="w-full pl-12 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
            </div>

            <!-- Address -->
            <div class="relative">
                <i class="fas fa-home absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-300"></i>
                <input type="text" name="address" placeholder="Address" required 
                    class="w-full pl-12 pr-4 py-3 border border-transparent rounded-lg bg-white/20 text-white placeholder-gray-300 focus:ring-4 focus:ring-blue-400 transition">
            </div>

            <!-- Register Button -->
            <button type="submit" class="w-full bg-gradient-to-r from-green-400 to-blue-500 text-white py-3 rounded-lg font-semibold shadow-md hover:opacity-90 transition duration-300">
                Register
            </button>
        </form>

        <!-- Login Link -->
        <p class="mt-4 text-sm text-gray-200">
            Already have an account? 
            <a href="login.php" class="text-yellow-400 font-medium hover:underline">Login Here</a>
        </p>
    </div>
</body>
</html>
