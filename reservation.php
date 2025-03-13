<?php
// Database connection (update with your actual credentials)
$servername = 'localhost'; 
$username = "root";
$password = "";
$dbname = "css_sit_in";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Close the connection at the end of your script
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .bg-navy {
            background-color: #000080;
        }
        .hover\:bg-darkblue:hover {
            background-color: #00008B;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <!-- Navigation -->
    <header class="bg-navy text-white shadow-md">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            <h2 class="text-2xl font-bold">Dashboard</h2>
            <nav>
                <ul class="flex space-x-4">
                    <li><a href="#" class="hover:text-yellow-500">Notification</a></li>
                    <li><a href="dashboard.php" class="hover:text-yellow-500">Home</a></li>
                    <li><a href="edit_profile.php" class="hover:text-yellow-500">Edit Profile</a></li>
                    <li><a href="history.php" class="hover:text-yellow-500">History</a></li>
                    <li><a href="reservation.php" class="hover:text-yellow-500">Reservation</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="bg-yellow-500 text-navy px-4 py-2 rounded hover:bg-navy hover:text-yellow-500 transition">Log out</a>
        </div>
    </header>
    <div class="container mx-auto px-4 py-8 flex-grow">
        <div class="bg-white p-8 rounded shadow-md">
            <h2 class="text-3xl font-bold text-center text-navy mb-6">Reservation</h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">ID Number:</label>
                    <input type="text" name="id_number" value="" readonly class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Student Name:</label>
                    <input type="text" name="student_name" value="" readonly class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Purpose:</label>
                    <input type="text" name="purpose" value="" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Lab:</label>
                    <input type="text" name="lab" value="" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Time In:</label>
                    <input type="time" name="time_in" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Date:</label>
                    <input type="date" name="date" class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Remaining Session:</label>
                    <input type="text" name="remaining_session" value="30" readonly class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-navy">
                </div>
                <button type="submit" class="w-full bg-navy text-white py-2 rounded hover:bg-darkblue transition">Reserve</button>
            </form>
        </div>
    </div>
</body>
</html>