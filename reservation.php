<?php
// Database connection (update with your actual credentials)
$servername = 'localhost'; 
$username = "root";
$password = "";
$dbname = "ccs-sit-in";

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
    <title>Make Reservation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-navy-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Make Reservation</h2>
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
                <form method="POST" action="" class="space-y-6">
                    <!-- Laboratory Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Laboratory</label>
                        <select name="laboratory" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-800 focus:border-transparent">
                            <option value="" disabled selected>Choose a laboratory</option>
                            <option value="lab1">Computer Laboratory 1</option>
                            <option value="lab2">Computer Laboratory 2</option>
                            <option value="lab3">Computer Laboratory 3</option>
                        </select>
                    </div>

                    <!-- Date Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                        <input type="date" name="date" required 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-800 focus:border-transparent">
                    </div>

                    <!-- Time Slot Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Time Slot</label>
                        <select name="time_slot" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-800 focus:border-transparent">
                            <option value="" disabled selected>Choose a time slot</option>
                            <option value="9-10">9:00 AM - 10:00 AM</option>
                            <option value="10-11">10:00 AM - 11:00 AM</option>
                            <option value="11-12">11:00 AM - 12:00 PM</option>
                            <option value="1-2">1:00 PM - 2:00 PM</option>
                            <option value="2-3">2:00 PM - 3:00 PM</option>
                            <option value="3-4">3:00 PM - 4:00 PM</option>
                            <option value="4-5">4:00 PM - 5:00 PM</option>
                        </select>
                    </div>

                    <!-- Purpose -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purpose of Reservation</label>
                        <textarea name="purpose" required rows="4" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-navy-800 focus:border-transparent resize-none"
                            placeholder="Please state your purpose for the reservation..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end">
                        <button type="submit" 
                            class="bg-navy-800 text-white px-8 py-3 rounded-lg font-semibold hover:bg-navy-700 transition duration-300 shadow-lg">
                            Submit Reservation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script>
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