<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Include the database connection
include('db_connection.php');

// Get top students based on points earned
$pointsQuery = "SELECT u.ID, CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) as name, u.EMAIL, u.YEAR, u.COURSE, u.IMAGE, 
                COALESCE(u.total_points_earned, 0) as total_points,
                COALESCE(u.current_points, 0) as current_points,
                COUNT(p.id) as activities_count
                FROM users u
                LEFT JOIN points p ON u.ID = p.student_id 
                WHERE u.user_type = 'student'
                GROUP BY u.ID
                ORDER BY total_points DESC, activities_count DESC
                LIMIT 10";

$pointsResult = $conn->query($pointsQuery);
$topPointStudents = [];

if ($pointsResult && $pointsResult->num_rows > 0) {
    while ($row = $pointsResult->fetch_assoc()) {
        $topPointStudents[] = $row;
    }
}

// Get total points awarded
$totalPointsQuery = "SELECT COALESCE(SUM(points_earned), 0) as total_points FROM points";
$totalPointsResult = $conn->query($totalPointsQuery);
$totalPointsAwarded = 0;
if ($totalPointsResult && $row = $totalPointsResult->fetch_assoc()) {
    $totalPointsAwarded = $row['total_points'];
}

// Get total students with points
$studentsCountQuery = "SELECT COUNT(DISTINCT student_id) as student_count FROM points";
$studentsCountResult = $conn->query($studentsCountQuery);
$totalStudentsWithPoints = 0;
if ($studentsCountResult && $row = $studentsCountResult->fetch_assoc()) {
    $totalStudentsWithPoints = $row['student_count'];
}

// Get recent point awards
$recentPointsQuery = "SELECT p.*, CONCAT(u.FIRSTNAME, ' ', u.LASTNAME) as student_name 
                     FROM points p
                     JOIN users u ON p.student_id = u.ID
                     ORDER BY p.awarded_date DESC
                     LIMIT 8";
$recentPointsResult = $conn->query($recentPointsQuery);
$recentPoints = [];

if ($recentPointsResult && $recentPointsResult->num_rows > 0) {
    while ($row = $recentPointsResult->fetch_assoc()) {
        $recentPoints[] = $row;
    }
}

// Get point stats by year level
$pointsByYearQuery = "SELECT u.YEAR, 
                    SUM(COALESCE(u.total_points_earned, 0)) as total_points,
                    COUNT(DISTINCT u.ID) as student_count
                    FROM users u
                    WHERE u.user_type = 'student' AND u.total_points_earned > 0
                    GROUP BY u.YEAR
                    ORDER BY u.YEAR";
$pointsByYearResult = $conn->query($pointsByYearQuery);
$pointsByYear = [];

if ($pointsByYearResult && $pointsByYearResult->num_rows > 0) {
    while ($row = $pointsByYearResult->fetch_assoc()) {
        $pointsByYear[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboards | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="images/css.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.4s ease-out',
                        'pulse-soft': 'pulseSoft 2s infinite',
                        'bounce-soft': 'bounceSoft 2s infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        pulseSoft: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.8' }
                        },
                        bounceSoft: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        }
                    },
                },
            },
        }
    </script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        .trophy-gold {
            color: #FFD700;
            filter: drop-shadow(0 0 2px rgba(255, 215, 0, 0.5));
        }
        
        .trophy-silver {
            color: #C0C0C0;
            filter: drop-shadow(0 0 2px rgba(192, 192, 192, 0.5));
        }
        
        .trophy-bronze {
            color: #CD7F32;
            filter: drop-shadow(0 0 2px rgba(205, 127, 50, 0.5));
        }
        
        .leaderboard-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .leaderboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .leaderboard-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.05), rgba(37, 99, 235, 0));
            pointer-events: none;
        }
        
        .btn-primary {
            @apply bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-4 py-2.5 rounded-md transition duration-200 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #1e40af);
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #2563eb, #1e3a8a);
        }
        
        /* Badge animations */
        .badge-pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(37, 99, 235, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(37, 99, 235, 0);
            }
        }
        
        /* Confetti effect for top position */
        .confetti-effect {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            top: 0;
            left: 0;
            pointer-events: none;
        }
        
        .confetti {
            position: absolute;
            width: 10px;
            height: 10px;
            opacity: 0.7;
            animation: confetti-fall 3s linear infinite;
        }
        
        @keyframes confetti-fall {
            0% {
                transform: translateY(-100px) rotate(0deg);
                opacity: 1;
            }
            100% {
                transform: translateY(100px) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Top Navigation Bar -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="admin-dashboard.php" class="flex items-center">
                        <img class="h-10 w-auto mr-2" src="images/css.png" alt="Logo">
                        <span class="font-semibold text-lg text-gray-900">Admin Portal</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="admin-dashboard.php" class="text-gray-600 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition duration-150">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                    </a>
                    <div class="relative">
                        <button type="button" class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span class="sr-only">Open user menu</span>
                            <div class="h-8 w-8 rounded-full bg-gradient-to-r from-blue-600 to-blue-800 flex items-center justify-center text-white">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span class="ml-2 text-gray-700 font-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8 border-b border-gray-200 pb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl">
                        Student Points Leaderboard
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm text-gray-500">
                        Monitor student engagement and recognize top performers based on points earned.
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="admin-points.php" class="btn-primary flex items-center">
                        <i class="fas fa-award mr-2"></i>
                        <span>Manage Points</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Points Awarded -->
            <div class="bg-white rounded-lg shadow-sm p-6 relative overflow-hidden card-shadow">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-blue-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-blue-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-blue-50 mr-4">
                        <i class="fas fa-star text-blue-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Points Awarded</p>
                        <h3 class="text-2xl font-bold text-gray-900 animate-fade-in">
                            <?php echo number_format($totalPointsAwarded); ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="text-sm text-gray-500 flex items-center">
                        <i class="fas fa-chart-line mr-1 text-blue-600"></i>
                        <span>Across the entire program</span>
                    </div>
                </div>
            </div>

            <!-- Students With Points -->
            <div class="bg-white rounded-lg shadow-sm p-6 relative overflow-hidden card-shadow">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-indigo-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-indigo-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-indigo-50 mr-4">
                        <i class="fas fa-users text-indigo-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Students Earning Points</p>
                        <h3 class="text-2xl font-bold text-gray-900 animate-fade-in">
                            <?php echo number_format($totalStudentsWithPoints); ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="text-sm text-gray-500 flex items-center">
                        <i class="fas fa-user-check mr-1 text-indigo-600"></i>
                        <span>Active participants</span>
                    </div>
                </div>
            </div>

            <!-- Average Points Per Student -->
            <div class="bg-white rounded-lg shadow-sm p-6 relative overflow-hidden card-shadow">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-blue-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-blue-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-blue-50 mr-4">
                        <i class="fas fa-chart-pie text-blue-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Average Points Per Student</p>
                        <h3 class="text-2xl font-bold text-gray-900 animate-fade-in">
                            <?php echo $totalStudentsWithPoints > 0 ? number_format($totalPointsAwarded / $totalStudentsWithPoints, 1) : '0.0'; ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="text-sm text-gray-500 flex items-center">
                        <i class="fas fa-calculator mr-1 text-blue-600"></i>
                        <span>Distribution average</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Students List -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-shadow">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900 flex items-center">
                            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                            Top Students by Points
                        </h2>
                    </div>
                    <div class="overflow-hidden">
                        <?php if (count($topPointStudents) > 0): ?>
                            <!-- Animated leaderboard podium for top 3 -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6">
                                <div class="flex justify-center items-end space-x-6 pb-4 relative">
                                    <?php 
                                    // Sort top 3 students
                                    $podiumStudents = array_slice($topPointStudents, 0, 3);
                                    usort($podiumStudents, function($a, $b) {
                                        return $b['total_points'] - $a['total_points'];
                                    });
                                    
                                    // Helper function for ordinals
                                    function getOrdinalSuffix($number) {
                                        $suffixes = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
                                        return ($number % 100 >= 11 && $number % 100 <= 13) ? 'th' : $suffixes[$number % 10];
                                    }
                                    
                                    // Display podium in order: 2nd, 1st, 3rd
                                    $positions = [1 => 0, 0 => 1, 2 => 2]; // Mapping position to display order
                                    $heights = [120, 150, 100]; // Heights for each podium position
                                    
                                    foreach ($positions as $displayOrder => $podiumPosition):
                                        if (isset($podiumStudents[$podiumPosition])):
                                            $student = $podiumStudents[$podiumPosition];
                                            $rank = $podiumPosition + 1;
                                            $rankClass = "";
                                            $trophy = "";
                                            
                                            switch ($rank) {
                                                case 1:
                                                    $rankClass = "bg-gradient-to-r from-yellow-400 to-yellow-300";
                                                    $trophy = "<i class='fas fa-trophy text-3xl trophy-gold animate-bounce-soft'></i>";
                                                    break;
                                                case 2:
                                                    $rankClass = "bg-gradient-to-r from-gray-300 to-gray-200";
                                                    $trophy = "<i class='fas fa-trophy text-2xl trophy-silver'></i>";
                                                    break;
                                                case 3:
                                                    $rankClass = "bg-gradient-to-r from-yellow-700 to-yellow-600";
                                                    $trophy = "<i class='fas fa-trophy text-xl trophy-bronze'></i>";
                                                    break;
                                            }
                                    ?>
                                        <div class="flex flex-col items-center">
                                            <?php if ($rank === 1): ?>
                                            <div class="relative mb-2">
                                                <div class="confetti-effect">
                                                    <!-- Confetti elements added by JS -->
                                                </div>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <div class="flex flex-col items-center mb-2">
                                                <div class="w-16 h-16 rounded-full bg-white p-1 shadow-md">
                                                    <?php if (!empty($student['IMAGE']) && file_exists($student['IMAGE'])): ?>
                                                        <img src="<?php echo htmlspecialchars($student['IMAGE']); ?>" alt="<?php echo htmlspecialchars($student['name']); ?>" class="w-full h-full object-cover rounded-full">
                                                    <?php else: ?>
                                                        <div class="w-full h-full rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium text-xl">
                                                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-center mt-2">
                                                    <p class="font-medium text-gray-900 text-sm"><?php echo htmlspecialchars($student['name']); ?></p>
                                                    <div class="flex items-center justify-center mt-1">
                                                        <?php echo $trophy; ?>
                                                        <span class="font-bold text-blue-600 ml-1"><?php echo number_format($student['total_points']); ?> pts</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="relative flex items-center justify-center <?php echo $rankClass; ?> text-white font-bold rounded-t-md px-8" style="height: <?php echo $heights[$displayOrder]; ?>px; width: 80px;">
                                                <span class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-white text-gray-800 text-xs font-medium px-2 py-1 rounded-full shadow-sm">
                                                    <?php echo $rank . getOrdinalSuffix($rank); ?>
                                                </span>
                                                <span class="text-lg sm:text-2xl"><?php echo $rank === 1 ? '👑' : ($rank === 2 ? '🥈' : '🥉'); ?></span>
                                            </div>
                                        </div>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                                <div class="h-4 bg-gray-800 rounded-t-md shadow-inner"></div>
                            </div>
                            
                            <!-- Complete leaderboard table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Year & Course</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Activities</th>
                                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php foreach($topPointStudents as $index => $student): ?>
                                            <tr class="hover:bg-gray-50 transition-colors <?php echo ($index < 3) ? 'bg-blue-50 bg-opacity-30' : ''; ?>">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center justify-center w-8 h-8 rounded-full 
                                                        <?php if($index === 0): ?>
                                                            bg-yellow-100 text-yellow-600
                                                        <?php elseif($index === 1): ?>
                                                            bg-gray-100 text-gray-600
                                                        <?php elseif($index === 2): ?>
                                                            bg-yellow-700 bg-opacity-20 text-yellow-700
                                                        <?php else: ?>
                                                            bg-blue-50 text-blue-600
                                                        <?php endif; ?>
                                                    ">
                                                        <?php if($index === 0): ?>
                                                            <i class="fas fa-trophy trophy-gold"></i>
                                                        <?php elseif($index === 1): ?>
                                                            <i class="fas fa-trophy trophy-silver"></i>
                                                        <?php elseif($index === 2): ?>
                                                            <i class="fas fa-trophy trophy-bronze"></i>
                                                        <?php else: ?>
                                                            <span class="font-medium"><?php echo $index + 1; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <?php if (!empty($student['IMAGE']) && file_exists($student['IMAGE'])): ?>
                                                                <img class="h-10 w-10 rounded-full object-cover" src="<?php echo htmlspecialchars($student['IMAGE']); ?>" alt="">
                                                            <?php else: ?>
                                                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                                                    <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['name']); ?></div>
                                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['EMAIL']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full">
                                                        <?php echo htmlspecialchars($student['YEAR'] . ' - ' . $student['COURSE']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <span class="text-gray-900"><?php echo number_format($student['activities_count']); ?></span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                                    <div class="flex flex-col items-center">
                                                        <span class="text-lg font-bold text-blue-600"><?php echo number_format($student['total_points']); ?></span>
                                                        <span class="text-xs text-gray-500 mt-1">(<?php echo number_format($student['current_points']); ?> current)</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($topPointStudents) === 0): ?>
                                            <tr>
                                                <td colspan="5" class="px-6 py-10 text-center">
                                                    <div class="mx-auto w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                                        <i class="fas fa-award text-blue-500 text-2xl"></i>
                                                    </div>
                                                    <p class="text-gray-500 font-medium">No students have earned points yet</p>
                                                    <p class="text-sm text-gray-400 mt-1">Points activity will appear here once awarded</p>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="py-8 px-6 text-center">
                                <div class="mx-auto w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-trophy text-blue-500 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-1">No Leaderboard Data Available</h3>
                                <p class="text-gray-500">Students will appear here once they've earned points.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar: Stats and Recent Activity -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Points by Year Level Chart -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-shadow">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Points by Year Level</h2>
                    </div>
                    <div class="p-6">
                        <?php if (count($pointsByYear) > 0): ?>
                            <canvas id="yearLevelChart" height="220"></canvas>
                        <?php else: ?>
                            <div class="py-6 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-chart-bar text-blue-500 text-lg"></i>
                                </div>
                                <p class="text-gray-500 text-sm">No data available yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Points Activity -->
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-shadow">
                    <div class="px-6 py-5 border-b border-gray-200 flex justify-between items-center">
                        <h2 class="text-lg font-medium text-gray-900">Recent Activity</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-clock mr-1"></i> Last Awards
                        </span>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto custom-scrollbar">
                        <?php if (count($recentPoints) > 0): ?>
                            <?php foreach ($recentPoints as $activity): ?>
                                <div class="px-6 py-4 flex items-start animate-fade-in">
                                    <div class="mr-4 bg-blue-50 rounded-full p-2 mt-1">
                                        <i class="fas fa-star text-blue-500"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($activity['student_name']); ?> 
                                            <span class="font-normal text-gray-500">earned</span>
                                            <span class="font-semibold text-blue-600"><?php echo $activity['points_earned']; ?> points</span>
                                        </p>
                                        <p class="text-sm text-gray-500 mt-1"><?php echo htmlspecialchars($activity['points_reason']); ?></p>
                                        <p class="text-xs text-gray-400 mt-1.5">
                                            <span><?php echo date('M j, g:i A', strtotime($activity['awarded_date'])); ?></span>
                                            <span class="mx-1">•</span>
                                            <span>by <?php echo htmlspecialchars($activity['awarded_by']); ?></span>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="px-6 py-10 text-center">
                                <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                    <i class="fas fa-history text-blue-500 text-lg"></i>
                                </div>
                                <p class="text-gray-500">No recent activity</p>
                                <p class="text-sm text-gray-400 mt-1">Point awards will appear here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if (count($recentPoints) > 0): ?>
                    <div class="px-6 py-3 bg-gray-50 text-center">
                        <a href="admin-points.php" class="text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors">
                            View All Activity <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Generate confetti elements for top position
            const confettiEffect = document.querySelector('.confetti-effect');
            if (confettiEffect) {
                const colors = ['#3b82f6', '#60a5fa', '#93c5fd', '#dbeafe', '#2563eb', '#1d4ed8'];
                
                for (let i = 0; i < 30; i++) {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    
                    const size = Math.random() * 8 + 4;
                    const color = colors[Math.floor(Math.random() * colors.length)];
                    
                    confetti.style.width = `${size}px`;
                    confetti.style.height = `${size}px`;
                    confetti.style.backgroundColor = color;
                    confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                    confetti.style.left = `${Math.random() * 100}%`;
                    confetti.style.animationDuration = `${Math.random() * 3 + 2}s`;
                    confetti.style.animationDelay = `${Math.random() * 5}s`;
                    
                    confettiEffect.appendChild(confetti);
                }
            }
            
            // Initialize chart if we have data
            <?php if (count($pointsByYear) > 0): ?>
            const ctx = document.getElementById('yearLevelChart').getContext('2d');
            const yearLevelChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php foreach ($pointsByYear as $yearData): ?>
                            '<?php echo htmlspecialchars("Year " . $yearData['YEAR']); ?>',
                        <?php endforeach; ?>
                    ],
                    datasets: [{
                        label: 'Total Points',
                        data: [
                            <?php foreach ($pointsByYear as $yearData): ?>
                                <?php echo $yearData['total_points']; ?>,
                            <?php endforeach; ?>
                        ],
                        backgroundColor: [
                            'rgba(37, 99, 235, 0.7)',
                            'rgba(59, 130, 246, 0.7)',
                            'rgba(96, 165, 250, 0.7)',
                            'rgba(147, 197, 253, 0.7)'
                        ],
                        borderColor: [
                            'rgba(37, 99, 235, 1)',
                            'rgba(59, 130, 246, 1)',
                            'rgba(96, 165, 250, 1)',
                            'rgba(147, 197, 253, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#1e40af',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            bodyFont: {
                                family: 'Inter'
                            },
                            titleFont: {
                                family: 'Inter'
                            },
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `${context.parsed.y} points earned`;
                                },
                                footer: function(tooltipItems) {
                                    <?php $yearStats = []; ?>
                                    <?php foreach ($pointsByYear as $index => $yearData): ?>
                                        <?php $yearStats[] = "{ year: {$index}, students: {$yearData['student_count']} }"; ?>
                                    <?php endforeach; ?>
                                    
                                    const yearData = [<?php echo implode(',', $yearStats); ?>];
                                    const yearStats = yearData[tooltipItems[0].dataIndex];
                                    return `${yearStats.students} student${yearStats.students !== 1 ? 's' : ''}`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false,
                                color: 'rgba(200, 200, 200, 0.15)',
                            },
                            ticks: {
                                font: {
                                    family: 'Inter'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter'
                                }
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