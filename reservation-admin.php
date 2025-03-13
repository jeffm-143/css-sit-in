<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navbar</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>

    <!-- Navigation Bar -->
    <nav class="bg-blue-900 py-4 px-6">
        <div class="max-w-7x2 mx-auto flex justify-between items-center">

            <!-- Logo & Title -->
            <a href="#" class="text-white text-xl font-semibold tracking-wide">Reservation</a>

            <!-- Desktop Navigation Links -->
            <ul class="flex space-x-6 text-white text-sm">
                <li><a href="admin-dashboard.php" class="hover:text-gray-300">Home</a></li>
                <li><a href="search.php" class="hover:text-gray-300">Search</a></li>
                <li><a href="students.php" class="hover:text-gray-300">Students</a></li>
                <li><a href="sit-in.php" class="hover:text-gray-300">Sit-in</a></li>
                <li><a href="view-sit-in.php" class="hover:text-gray-300">View Sit-in Records</a></li>
                <li><a href="sit-in-reports.php" class="hover:text-gray-300">Sit-in Reports</a></li>
                <li><a href="feedback-reports.php" class="hover:text-gray-300">Feedback Reports</a></li>
                <li><a href="reservation-admin.php" class="hover:text-gray-300">Reservation</a></li>
                <li>
                    <a href="logout.php" class="bg-yellow-500 text-black px-4 py-2 rounded-md hover:bg-yellow-600">
                        Log out
                    </a>
                </li>
            </ul>
        </div>
    </nav>

</body>
</html>
