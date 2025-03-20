<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<nav class="bg-blue-900 py-4 px-6 shadow-md">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <a href="admin-dashboard.php" class="text-white text-2xl font-bold">CCS Admin</a>

        <ul class="hidden md:flex space-x-6 text-white font-medium">
            <li><a href="admin-dashboard.php" class="hover:text-yellow-400 transition">Home</a></li>
            <li><a href="search.php" class="hover:text-yellow-400 transition">Search</a></li>
            <li><a href="students.php" class="hover:text-yellow-400 transition">Students</a></li>
            <li><a href="sit-in.php" class="hover:text-yellow-400 transition">Sit-in</a></li>
            <li><a href="view-sit-in.php" class="hover:text-yellow-400 transition">View Sit-in</a></li>
            <li><a href="sit-in-reports.php" class="hover:text-yellow-400 transition">Sit-in Reports</a></li>
            <li><a href="feedback-reports.php" class="hover:text-yellow-400 transition">Feedback</a></li>
            <li><a href="reservation-admin.php" class="hover:text-yellow-400 transition">Reservation</a></li>
        </ul>

        <a href="logout.php" class="bg-yellow-400 text-blue-900 px-4 py-2 rounded-lg font-bold hover:bg-blue-700 hover:text-white transition">Log out</a>
    </div>
</nav>
