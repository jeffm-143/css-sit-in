<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include('db_connection.php');

// Get top 5 students based on lab sessions
$sessionsQuery = "SELECT u.ID, CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) as name, u.EMAIL, u.YEAR, u.COURSE, u.IMAGE, 
         COUNT(s.id) as session_count,
         SUM(TIMESTAMPDIFF(MINUTE, s.start_time, s.end_time)) as total_minutes
         FROM users u
         LEFT JOIN sit_in_sessions s ON u.ID_NUMBER = s.student_id AND s.status = 'completed'
         WHERE u.user_type = 'student'
         GROUP BY u.ID
         HAVING session_count > 0
         ORDER BY session_count DESC, total_minutes DESC
         LIMIT 5";

$sessionsResult = $conn->query($sessionsQuery);
$topSessionStudents = [];

if ($sessionsResult && $sessionsResult->num_rows > 0) {
    while ($row = $sessionsResult->fetch_assoc()) {
        $topSessionStudents[] = $row;
    }
}

// Get top 5 students based on points
$pointsQuery = "SELECT u.ID, CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) as name, u.EMAIL, u.YEAR, u.COURSE, u.IMAGE,
         COALESCE(COUNT(p.id), 0) as session_count,
         u.POINTS as total_points, u.total_points_earned, u.sessions_earned
         FROM users u
         LEFT JOIN points p ON u.ID = p.student_id
         WHERE u.user_type = 'student' AND u.POINTS > 0
         GROUP BY u.ID
         ORDER BY u.POINTS DESC, u.total_points_earned DESC
         LIMIT 5";

$pointsResult = $conn->query($pointsQuery);
$topPointsStudents = [];

if ($pointsResult && $pointsResult->num_rows > 0) {
    while ($row = $pointsResult->fetch_assoc()) {
        $topPointsStudents[] = $row;
    }
}

// Get total statistics
$statsQuery = "SELECT 
    COUNT(*) as total_sessions,
    COUNT(DISTINCT student_id) as unique_students,
    SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as total_minutes
    FROM sit_in_sessions
    WHERE status = 'completed'";
    
$statsResult = $conn->query($statsQuery);
$stats = $statsResult->fetch_assoc();

// Get total points awarded
$pointsStatsQuery = "SELECT SUM(POINTS) as total_points_awarded FROM users WHERE user_type = 'student'";
$pointsStatsResult = $conn->query($pointsStatsQuery);
$pointsStats = $pointsStatsResult->fetch_assoc();
$totalPointsAwarded = $pointsStats['total_points_awarded'] ?: 0;

// Calculate total hours from minutes
$totalHours = round(($stats['total_minutes'] ?? 0) / 60, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Lab Resources | Admin</title>
    <link rel="icon" type="image/png" href="images/wbccs.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1);
        }
        .podium-1 { border-color: #FFD700; }
        .podium-2 { border-color: #C0C0C0; }
        .podium-3 { border-color: #CD7F32; }
        .medal-1 { color: #FFD700; }
        .medal-2 { color: #C0C0C0; }
        .medal-3 { color: #CD7F32; }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .pulse-animation {
            animation: pulse 2s infinite ease-in-out;
        }
        
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation header with soft shadow and subtle gradient -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <a href="admin-dashboard.php" class="group flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200">
                    <div class="p-1.5 rounded-full bg-blue-50 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </div>
                    <span class="font-medium">Back to Dashboard</span>
                </a>
                <div class="hidden md:flex items-center space-x-2">
                    <div class="h-8 w-8 rounded-full bg-yellow-100 flex items-center justify-center">
                        <i class="fas fa-trophy text-yellow-600 text-sm"></i>
                    </div>
                    <span class="font-medium text-gray-700">Student Leaderboards</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Header with modern styling -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 animate-fade-in">
            <div>
                <div class="inline-flex items-center bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-4 py-1 rounded-full text-sm mb-3">
                    <i class="fas fa-star mr-2"></i>
                    Performance Metrics
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                    Student Leaderboards
                </h1>
                <p class="text-gray-600 mt-2 max-w-2xl">
                    Recognizing our most dedicated students based on lab attendance and points earned.
                </p>
            </div>
            
            <div class="mt-6 md:mt-0 flex items-center space-x-3">
                <div class="relative inline-block text-left">
                    <button id="exportBtn" type="button" class="inline-flex items-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-download mr-2 text-blue-500"></i>
                        Export Data
                    </button>
                    <div id="exportMenu" class="origin-top-right absolute right-0 mt-2 w-56 rounded-xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 hidden z-10 divide-y divide-gray-100">
                        <div class="py-3 px-4">
                            <p class="text-sm font-semibold text-gray-900">Export Options</p>
                            <p class="text-xs text-gray-500 mt-1">Download leaderboard data</p>
                        </div>
                        <div class="py-1">
                            <a href="#" class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-blue-50">
                                <i class="fas fa-file-csv text-green-500 mr-3 text-lg"></i>
                                Export as CSV
                            </a>
                            <a href="#" class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-blue-50">
                                <i class="fas fa-file-pdf text-red-500 mr-3 text-lg"></i>
                                Export as PDF
                            </a>
                            <a href="#" class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-blue-50">
                                <i class="fas fa-file-excel text-emerald-500 mr-3 text-lg"></i>
                                Export as Excel
                            </a>
                        </div>
                    </div>
                </div>
                
                <button id="refreshBtn" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Refresh Data
                </button>
            </div>
        </div>
        
        <!-- Stats Cards with elegant hover effects -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Sessions Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-laptop-code text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Sit-in Sessions</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($stats['total_sessions']); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-chart-line text-blue-500 mr-1"></i>
                                Total completed lab sessions
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Points Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <i class="fas fa-star text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Points Awarded</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($totalPointsAwarded); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-star-half-alt text-purple-500 mr-1"></i>
                                Cumulative points across all students
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Unique Students Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Students</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($stats['unique_students']); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-user-check text-green-500 mr-1"></i>
                                Unique students using the lab
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Hours Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                            <i class="fas fa-clock text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Lab Hours</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($totalHours); ?> hours
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-calendar-check text-indigo-500 mr-1"></i>
                                Cumulative time spent in lab
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Lab Sessions Leaderboard -->
            <div>
                <div class="bg-white shadow-lg overflow-hidden rounded-xl border border-gray-200 animate-fade-in">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-blue-700 sm:px-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <i class="fas fa-laptop-code mr-2"></i>
                                Most Lab Sessions
                            </h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-blue-600">
                                <i class="fas fa-medal mr-1"></i> Attendance
                            </span>
                        </div>
                    </div>
                    
                    <?php if (empty($topSessionStudents)): ?>
                        <div class="px-4 py-16 sm:px-6 text-center">
                            <div class="flex flex-col items-center">
                                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-blue-100">
                                    <i class="fas fa-user-graduate text-blue-400 text-3xl"></i>
                                </div>
                                <h3 class="mt-5 text-xl font-medium text-gray-900">No data available yet</h3>
                                <p class="mt-3 text-sm text-gray-500 max-w-md mx-auto">
                                    No lab session data has been recorded yet.
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($topSessionStudents as $index => $student): ?>
                                <?php 
                                    // Define medal colors for top 3
                                    $medalClass = '';
                                    $medalIcon = '';
                                    $podiumClass = '';
                                    $bgColor = 'bg-white';
                                    $badgeColor = 'bg-blue-100 text-blue-800';
                                    
                                    if ($index === 0) {
                                        $medalClass = 'medal-1';
                                        $medalIcon = '<i class="fas fa-medal text-yellow-500 mr-2 text-lg"></i>';
                                        $podiumClass = 'podium-1';
                                        $bgColor = 'bg-gradient-to-r from-blue-50 to-white';
                                        $badgeColor = 'bg-blue-100 text-blue-800';
                                    } elseif ($index === 1) {
                                        $medalClass = 'medal-2';
                                        $medalIcon = '<i class="fas fa-medal text-gray-400 mr-2 text-lg"></i>';
                                        $podiumClass = 'podium-2';
                                        $bgColor = 'bg-white';
                                    } elseif ($index === 2) {
                                        $medalClass = 'medal-3';
                                        $medalIcon = '<i class="fas fa-medal text-yellow-700 mr-2 text-lg"></i>';
                                        $podiumClass = 'podium-3';
                                        $bgColor = 'bg-white';
                                    }
                                    
                                    // Calculate hours from minutes
                                    $hours = round($student['total_minutes'] / 60, 1);
                                ?>
                                <li class="px-6 py-4 <?php echo $bgColor; ?> hover:bg-gray-50 transition-colors duration-150">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 mr-4">
                                                <div class="relative">
                                                    <img class="h-14 w-14 rounded-full object-cover border-2 shadow-sm <?php echo $podiumClass; ?>" 
                                                         src="../<?php echo !empty($student['profileImg']) ? $student['profileImg'] : 'images/person.jpg'; ?>" 
                                                         alt="<?php echo htmlspecialchars($student['name']); ?>">
                                                    <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-white border-2 <?php echo $podiumClass; ?> flex items-center justify-center text-xs font-bold <?php echo $medalClass; ?> shadow-sm">
                                                        <?php echo $index + 1; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-md font-semibold text-gray-900 flex items-center">
                                                    <?php echo $medalIcon; ?>
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                    <?php if ($index === 0): ?>
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                            <i class="fas fa-crown mr-1"></i> Top Attendee
                                                        </span>
                                                    <?php endif; ?>
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo htmlspecialchars($student['course'] . ' - ' . $student['level']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end space-y-1">
                                            <!-- Lab sessions -->
                                            <div class="px-3 py-1 rounded-full <?php echo $badgeColor; ?> text-sm font-medium flex items-center">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                <?php echo number_format($student['session_count']); ?> sessions
                                            </div>
                                            
                                            <!-- Hours in lab -->
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <i class="far fa-clock mr-1"></i>
                                                <?php echo number_format($hours); ?> hours total
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Sessions Chart -->
                <div class="mt-6 bg-white shadow-lg overflow-hidden rounded-xl border border-gray-200 p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Lab Sessions by Top Students</h4>
                    <div class="chart-container">
                        <canvas id="sessionsChart"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Points Leaderboard -->
            <div>
                <div class="bg-white shadow-lg overflow-hidden rounded-xl border border-gray-200 animate-fade-in">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-purple-600 to-purple-700 sm:px-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <i class="fas fa-star mr-2"></i>
                                Most Points Earned
                            </h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-purple-600">
                                <i class="fas fa-trophy mr-1"></i> Achievement
                            </span>
                        </div>
                    </div>
                    
                    <?php if (empty($topPointsStudents)): ?>
                        <div class="px-4 py-16 sm:px-6 text-center">
                            <div class="flex flex-col items-center">
                                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-purple-100">
                                    <i class="fas fa-star text-purple-400 text-3xl"></i>
                                </div>
                                <h3 class="mt-5 text-xl font-medium text-gray-900">No points awarded yet</h3>
                                <p class="mt-3 text-sm text-gray-500 max-w-md mx-auto">
                                    No students have earned points yet.
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <ul class="divide-y divide-gray-200">
                            <?php foreach ($topPointsStudents as $index => $student): ?>
                                <?php 
                                    // Define medal colors for top 3
                                    $medalClass = '';
                                    $medalIcon = '';
                                    $podiumClass = '';
                                    $bgColor = 'bg-white';
                                    $badgeColor = 'bg-purple-100 text-purple-800';
                                    
                                    if ($index === 0) {
                                        $medalClass = 'medal-1';
                                        $medalIcon = '<i class="fas fa-medal text-yellow-500 mr-2 text-lg"></i>';
                                        $podiumClass = 'podium-1';
                                        $bgColor = 'bg-gradient-to-r from-purple-50 to-white';
                                        $badgeColor = 'bg-purple-100 text-purple-800';
                                    } elseif ($index === 1) {
                                        $medalClass = 'medal-2';
                                        $medalIcon = '<i class="fas fa-medal text-gray-400 mr-2 text-lg"></i>';
                                        $podiumClass = 'podium-2';
                                        $bgColor = 'bg-white';
                                    } elseif ($index === 2) {
                                        $medalClass = 'medal-3';
                                        $medalIcon = '<i class="fas fa-medal text-yellow-700 mr-2 text-lg"></i>';
                                        $podiumClass = 'podium-3';
                                        $bgColor = 'bg-white';
                                    }
                                ?>
                                <li class="px-6 py-4 <?php echo $bgColor; ?> hover:bg-gray-50 transition-colors duration-150">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 mr-4">
                                                <div class="relative">
                                                    <img class="h-14 w-14 rounded-full object-cover border-2 shadow-sm <?php echo $podiumClass; ?>" 
                                                         src="../<?php echo !empty($student['profileImg']) ? $student['profileImg'] : 'images/person.jpg'; ?>" 
                                                         alt="<?php echo htmlspecialchars($student['name']); ?>">
                                                    <span class="absolute -bottom-1 -right-1 h-5 w-5 rounded-full bg-white border-2 <?php echo $podiumClass; ?> flex items-center justify-center text-xs font-bold <?php echo $medalClass; ?> shadow-sm">
                                                        <?php echo $index + 1; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-md font-semibold text-gray-900 flex items-center">
                                                    <?php echo $medalIcon; ?>
                                                    <?php echo htmlspecialchars($student['name']); ?>
                                                    <?php if ($index === 0): ?>
                                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                            <i class="fas fa-crown mr-1"></i> Top Achiever
                                                        </span>
                                                    <?php endif; ?>
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    <?php echo htmlspecialchars($student['course'] . ' - ' . $student['level']); ?>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end space-y-1">
                                            <!-- Earned points -->
                                            <div class="px-3 py-1 rounded-full <?php echo $badgeColor; ?> text-sm font-medium flex items-center">
                                                <i class="fas fa-award mr-1"></i>
                                                <?php echo number_format($student['total_points']); ?> points
                                            </div>
                                            
                                            <!-- Equivalent sessions -->
                                            <div class="text-xs text-gray-500 flex items-center">
                                                <i class="fas fa-exchange-alt mr-1"></i>
                                                <?php echo floor($student['total_points'] / 3); ?> extra sessions
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <!-- Points Chart -->
                <div class="mt-6 bg-white shadow-lg overflow-hidden rounded-xl border border-gray-200 p-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Points Earned by Top Students</h4>
                    <div class="chart-container">
                        <canvas id="pointsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- How Points and Rankings Work -->
        <div class="bg-white shadow-lg overflow-hidden rounded-xl border border-gray-200 animate-fade-in mb-8">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-indigo-600 to-purple-600 sm:px-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        How Points & Lab Sessions Work
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-indigo-600">
                        <i class="fas fa-lightbulb mr-1"></i> Info
                    </span>
                </div>
            </div>
            
            <div class="px-6 py-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex flex-col">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-laptop-code text-blue-600"></i>
                            </div>
                            <h4 class="ml-3 text-lg font-medium text-gray-900">Lab Sessions</h4>
                        </div>
                        <p class="text-sm text-gray-600">
                            The Lab Sessions leaderboard recognizes students who consistently attend and use the computer laboratory. Regular attendance helps build discipline and provides continuous practice opportunities.
                        </p>
                    </div>
                    
                    <div class="flex flex-col">
                        <div class="flex items-center mb-3">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-purple-100 flex items-center justify-center">
                                <i class="fas fa-star text-purple-600"></i>
                            </div>
                            <h4 class="ml-3 text-lg font-medium text-gray-900">Points System</h4>
                        </div>
                        <p class="text-sm text-gray-600">
                            The Points leaderboard showcases students who earn recognition for exceptional work, helping others, and demonstrating outstanding behavior in the lab. Points can be converted to extra lab sessions (3 points = 1 extra session).
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle export menu
            const exportBtn = document.getElementById('exportBtn');
            const exportMenu = document.getElementById('exportMenu');
            
            if (exportBtn && exportMenu) {
                exportBtn.addEventListener('click', function() {
                    exportMenu.classList.toggle('hidden');
                });
                
                // Close export menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!exportBtn.contains(event.target) && !exportMenu.contains(event.target)) {
                        exportMenu.classList.add('hidden');
                    }
                });
            }
            
            // Refresh button animation
            const refreshBtn = document.getElementById('refreshBtn');
            
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    icon.classList.add('animate-spin');
                    
                    // Simulate refresh
                    setTimeout(function() {
                        icon.classList.remove('animate-spin');
                        location.reload();
                    }, 1000);
                });
            }
            
            <?php if (!empty($topSessionStudents)): ?>
            // Sessions chart
            const sessionsCtx = document.getElementById('sessionsChart').getContext('2d');
            const sessionsChart = new Chart(sessionsCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($topSessionStudents as $student): ?>
                            '<?php echo htmlspecialchars($student['name']); ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Lab Sessions',
                        data: [
                            <?php foreach ($topSessionStudents as $student): ?>
                                <?php echo $student['session_count']; ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: 'rgba(54, 162, 235, 0.8)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12
                                },
                                color: '#4B5563',
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#111827',
                            bodyColor: '#4B5563',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            boxWidth: 10,
                            usePointStyle: true,
                            borderColor: 'rgba(220, 220, 220, 1)',
                            borderWidth: 1,
                            displayColors: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: true,
                                color: 'rgba(243, 244, 246, 1)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
            
            <?php if (!empty($topPointsStudents)): ?>
            // Points chart
            const pointsCtx = document.getElementById('pointsChart').getContext('2d');
            const pointsChart = new Chart(pointsCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($topPointsStudents as $student): ?>
                            '<?php echo htmlspecialchars($student['name']); ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Points Earned',
                        data: [
                            <?php foreach ($topPointsStudents as $student): ?>
                                <?php echo $student['total_points']; ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: 'rgba(153, 102, 255, 0.8)',
                        borderColor: 'rgb(153, 102, 255)',
                        borderWidth: 2,
                        borderRadius: 6,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 12
                                },
                                color: '#4B5563',
                                usePointStyle: true,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.9)',
                            titleColor: '#111827',
                            bodyColor: '#4B5563',
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12,
                            boxWidth: 10,
                            usePointStyle: true,
                            borderColor: 'rgba(220, 220, 220, 1)',
                            borderWidth: 1,
                            displayColors: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: true,
                                color: 'rgba(243, 244, 246, 1)'
                            }
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 12
                                },
                                color: '#6B7280'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            <?php endif; ?>
        });
    </script>
</body>
</html>