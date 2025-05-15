<?php 
    include('conn_back/points_process.php');
    
    // Verify admin session
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usage Analytics | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="images/css.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        'pulse-soft': 'pulseSoft 2s infinite'
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
        
        .stats-card {
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .btn-primary {
            @apply bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-4 py-2.5 rounded-md transition duration-200 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
        }
        
        .btn-secondary {
            @apply bg-white text-gray-700 border border-gray-300 px-4 py-2.5 rounded-md hover:bg-gray-50 transition duration-200 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2;
        }
        
        .input-field {
            @apply block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
        }

        /* Custom Scrollbar Styling */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(to bottom, #3b82f6, #1e40af);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #2563eb, #1e3a8a);
            box-shadow: 0 0 5px rgba(37, 99, 235, 0.5);
        }
        
        /* Animation for point number */
        @keyframes countUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .count-animation {
            animation: countUp 0.5s ease-out;
        }
        
        /* Progress bar animation */
        @keyframes fillProgress {
            from { width: 0; }
            to { width: var(--progress-width); }
        }
        
        .progress-animation {
            animation: fillProgress 1s ease-out forwards;
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
                        Usage Analytics
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm text-gray-500">
                        Monitor student engagement and manage the point system.
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <button type="button" id="assignPointsBtn" class="btn-primary flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Award Points</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div id="successAlert" class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 animate-fade-in">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo $_SESSION['success_message']; ?></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="document.getElementById('successAlert').remove()" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none">
                                <span class="sr-only">Dismiss</span>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div id="errorAlert" class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 animate-fade-in">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo $_SESSION['error_message']; ?></p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button type="button" onclick="document.getElementById('errorAlert').remove()" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none">
                                <span class="sr-only">Dismiss</span>
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Stat Card 1 -->
            <div class="bg-white rounded-lg shadow-sm p-6 stats-card relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-blue-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-blue-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-blue-50 mr-4">
                        <i class="fas fa-award text-blue-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Points Awarded</p>
                        <h3 class="text-2xl font-bold text-gray-900 count-animation">
                            <?php echo number_format($totalPointsAwarded); ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Across <?php echo $totalAssignments; ?> assignments</span>
                        <span class="text-blue-600 font-medium">
                            <?php echo $totalAssignments > 0 ? number_format($totalPointsAwarded/$totalAssignments, 1) : '0.0'; ?> avg
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Stat Card 2 -->
            <div class="bg-white rounded-lg shadow-sm p-6 stats-card relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-indigo-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-indigo-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-indigo-50 mr-4">
                        <i class="fas fa-users text-indigo-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Students With Points</p>
                        <h3 class="text-2xl font-bold text-gray-900 count-animation">
                            <?php echo number_format($studentsWithPoints); ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Out of <?php echo count($students); ?> total</span>
                        <span class="text-indigo-600 font-medium">
                            <?php echo count($students) > 0 ? number_format(($studentsWithPoints/count($students))*100, 0) : '0'; ?>% participation
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Stat Card 3 -->
            <div class="bg-white rounded-lg shadow-sm p-6 stats-card relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-blue-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-blue-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-blue-50 mr-4">
                        <i class="fas fa-clock text-blue-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Sessions Awarded</p>
                        <h3 class="text-2xl font-bold text-gray-900 count-animation">
                            <?php echo number_format($sessionsAwarded); ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">From point conversions</span>
                        <span class="text-blue-600 font-medium"><?php echo number_format($sessionsAwarded*3); ?> points used</span>
                    </div>
                </div>
            </div>
            
            <!-- Stat Card 4 -->
            <div class="bg-white rounded-lg shadow-sm p-6 stats-card relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-12 h-24 w-24 rounded-full bg-indigo-100 opacity-20"></div>
                <div class="absolute bottom-0 right-0 -mb-8 -mr-8 h-40 w-40 rounded-full bg-indigo-50 opacity-30"></div>
                
                <div class="flex items-center z-10 relative">
                    <div class="p-3 rounded-full bg-indigo-50 mr-4">
                        <i class="fas fa-chart-line text-indigo-600 fa-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-600">Points Per Student</p>
                        <h3 class="text-2xl font-bold text-gray-900 count-animation">
                            <?php echo $studentsWithPoints > 0 ? number_format($totalPointsAwarded/$studentsWithPoints, 1) : '0.0'; ?>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 z-10 relative">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">Average distribution</span>
                        <span class="text-indigo-600 font-medium">
                            <?php echo count($students) > 0 ? number_format($totalPointsAwarded/count($students), 1) : '0.0'; ?> overall avg
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Points Distribution Chart -->
            <div class="bg-white rounded-lg shadow-sm p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Points Distribution</h2>
                    <div class="flex items-center">
                        <select id="chart-period" class="text-sm border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="week">This Week</option>
                            <option value="month" selected>This Month</option>
                            <option value="quarter">This Quarter</option>
                            <option value="year">This Year</option>
                        </select>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="pointsChart"></canvas>
                </div>
            </div>
            
            <!-- Top Performing Students -->
            <div class="bg-white rounded-lg shadow-sm p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-medium text-gray-900">Top Performing Students</h2>
                    <div class="flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium cursor-pointer">
                        <span>View All</span>
                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </div>
                </div>
                <div class="space-y-4">
                    <?php 
                    // Sort students by points and get top 5
                    usort($students, function($a, $b) {
                        return $b['total_points_earned'] - $a['total_points_earned'];
                    });
                    
                    $topStudents = array_slice($students, 0, 5);
                    $maxPoints = count($topStudents) > 0 ? $topStudents[0]['total_points_earned'] : 0;
                    
                    foreach ($topStudents as $index => $student): 
                        $percent = $maxPoints > 0 ? ($student['total_points_earned'] / $maxPoints) * 100 : 0;
                    ?>
                        <div>
                            <div class="flex items-center">
                                <div class="w-8 flex-shrink-0 text-center">
                                    <?php if ($index === 0): ?>
                                        <span class="text-lg text-yellow-500"><i class="fas fa-medal"></i></span>
                                    <?php elseif ($index === 1): ?>
                                        <span class="text-lg text-gray-400"><i class="fas fa-medal"></i></span>
                                    <?php elseif ($index === 2): ?>
                                        <span class="text-lg text-amber-700"><i class="fas fa-medal"></i></span>
                                    <?php else: ?>
                                        <span class="text-lg text-gray-400"><?php echo $index + 1; ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-2 flex-1">
                                    <div class="flex items-center justify-between">
                                        <div class="text-sm font-medium text-gray-900 truncate">
                                            <?php echo htmlspecialchars($student['name']); ?>
                                        </div>
                                        <div class="text-sm font-semibold text-blue-600">
                                            <?php echo number_format($student['total_points_earned']); ?> pts
                                        </div>
                                    </div>
                                    <div class="mt-1.5 w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="bg-blue-600 h-1.5 rounded-full progress-animation" style="--progress-width: <?php echo $percent; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($topStudents) === 0): ?>
                        <div class="text-center py-6">
                            <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                <i class="fas fa-chart-bar text-blue-500 text-lg"></i>
                            </div>
                            <p class="text-gray-500 text-sm">No student data available yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Activity and Students Table Section -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity Feed -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-shadow">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h2 class="text-lg font-medium text-gray-900">Recent Activity</h2>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-[480px] overflow-y-auto custom-scrollbar">
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
                                <p class="text-sm text-gray-400 mt-1">Award points to see activity here</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Students Table -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm overflow-hidden card-shadow">
                    <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                        <h2 class="text-lg font-medium text-gray-900">Student Points Management</h2>
                        <div class="relative">
                            <input type="text" id="search-students" placeholder="Search students..." class="px-4 py-2 pr-9 border border-gray-300 rounded-md shadow-sm text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div class="max-h-[480px] overflow-y-auto custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Current Points</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="student-list">
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                                        <?php echo strtoupper(substr($student['FIRSTNAME'], 0, 1) . substr($student['LASTNAME'], 0, 1)); ?>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['name']); ?></div>
                                                        <div class="text-xs text-gray-500"><?php echo htmlspecialchars($student['ID']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-center">
                                                    <span class="px-2 py-1 text-xs font-medium bg-blue-50 text-blue-700 rounded-full">
                                                        <?php echo $student['current_points']; ?> / <?php echo $student['total_points_earned']; ?> total
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-center">
                                                    <span class="px-2 py-1 text-xs font-medium bg-indigo-50 text-indigo-700 rounded-full">
                                                        <?php echo $student['SESSION']; ?> / 30 total
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                                <div class="flex items-center justify-center space-x-2">
                                                    <button class="assign-btn text-white bg-blue-600 hover:bg-blue-700 px-2.5 py-1.5 rounded text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors" 
                                                            data-id="<?php echo $student['ID']; ?>" 
                                                            data-name="<?php echo htmlspecialchars($student['name']); ?>">
                                                        <i class="fas fa-plus-circle mr-1"></i> Points
                                                    </button>
                                                    <?php if ($student['current_points'] >= 3 && $student['SESSION'] < 30): ?>
                                                        <button class="convert-btn text-white bg-indigo-600 hover:bg-indigo-700 px-2.5 py-1.5 rounded text-xs font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition-colors" 
                                                                data-id="<?php echo $student['ID']; ?>" 
                                                                data-name="<?php echo htmlspecialchars($student['name']); ?>">
                                                            <i class="fas fa-exchange-alt mr-1"></i> Convert
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="text-white bg-gray-300 px-2.5 py-1.5 rounded text-xs font-medium cursor-not-allowed" disabled>
                                                            <i class="fas fa-exchange-alt mr-1"></i> Convert
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center">
                                            <div class="mx-auto w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center mb-3">
                                                <i class="fas fa-users text-blue-500 text-lg"></i>
                                            </div>
                                            <p class="text-gray-500">No students found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Assign Points Modal -->
    <div id="assignPointsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" id="modalOverlay"></div>
            <div class="bg-white rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6 animate-slide-up">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-medium text-gray-900">Assign Points</h3>
                    <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="conn_back/points_process.php" method="post" class="space-y-4">
                    <input type="hidden" id="student_id" name="student_id" value="">
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-2">Awarding points to: <span id="studentName" class="font-medium text-gray-900"></span></p>
                        <div class="h-1 w-full bg-blue-100 rounded-full"></div>
                    </div>
                    
                    <div>
                        <label for="points" class="block text-sm font-medium text-gray-700">Points to Award</label>
                        <div class="mt-1 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-star text-gray-400"></i>
                            </div>
                            <input type="number" min="1" max="3" id="points" name="points" required class="input-field pl-10" placeholder="1-3 points">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Note: 3 points can be converted into 1 session</p>
                    </div>
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                        <textarea id="reason" name="reason" rows="3" required class="input-field mt-1" placeholder="Enter reason for awarding points"></textarea>
                    </div>
                    <div class="flex justify-end pt-4 space-x-3">
                        <button type="button" id="cancelBtn" class="btn-secondary">Cancel</button>
                        <button type="submit" name="assign_points" class="btn-primary">
                            <i class="fas fa-check-circle mr-2"></i> Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Convert Points Modal -->
    <div id="convertPointsModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" id="convertModalOverlay"></div>
            <div class="bg-white rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6 animate-slide-up">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-medium text-gray-900">Convert Points to Session</h3>
                    <button type="button" id="closeConvertModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="conn_back/points_process.php" method="post" class="space-y-4">
                    <input type="hidden" id="convert_student_id" name="student_id" value="">
                    
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Converting Points for <span id="convertStudentName"></span></h3>
                                <div class="mt-2 text-sm text-blue-700">
                                    <p>3 points will be converted into 1 bonus session.</p>
                                    <p class="mt-1">This action cannot be undone.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-col items-center justify-center py-4">
                        <div class="flex items-center space-x-6">
                            <div class="text-center">
                                <div class="p-3 bg-blue-100 rounded-full inline-flex items-center justify-center mb-2">
                                    <i class="fas fa-star text-blue-600 text-lg"></i>
                                </div>
                                <p class="text-gray-700 font-medium">3 Points</p>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-arrow-right text-gray-400"></i>
                            </div>
                            <div class="text-center">
                                <div class="p-3 bg-indigo-100 rounded-full inline-flex items-center justify-center mb-2">
                                    <i class="fas fa-clock text-indigo-600 text-lg"></i>
                                </div>
                                <p class="text-gray-700 font-medium">1 Session</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end pt-4 space-x-3">
                        <button type="button" id="cancelConvertBtn" class="btn-secondary">Cancel</button>
                        <button type="submit" name="convert_points" class="btn-primary">
                            <i class="fas fa-exchange-alt mr-2"></i> Convert Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Charts
            const ctx = document.getElementById('pointsChart').getContext('2d');
            const pointsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                    datasets: [{
                        label: 'Points Awarded',
                        data: [18, 25, 32, 41],
                        borderColor: '#2563eb',
                        backgroundColor: 'rgba(37, 99, 235, 0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 4,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
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
                            borderWidth: 0
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

            // Modal Controls for Assign Points
            const assignPointsModal = document.getElementById('assignPointsModal');
            const assignPointsBtns = document.querySelectorAll('.assign-btn');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const modalOverlay = document.getElementById('modalOverlay');
            const studentIdField = document.getElementById('student_id');
            const studentNameField = document.getElementById('studentName');
            
            // Show modal with student data
            function showAssignPointsModal(studentId, studentName) {
                studentIdField.value = studentId;
                studentNameField.textContent = studentName;
                assignPointsModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Hide modal
            function hideAssignPointsModal() {
                assignPointsModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            // Event listeners for assign modal
            assignPointsBtns.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.dataset.id;
                    const studentName = this.dataset.name;
                    showAssignPointsModal(studentId, studentName);
                });
            });
            
            document.getElementById('assignPointsBtn').addEventListener('click', () => {
                if (document.querySelector('.assign-btn')) {
                    const firstBtn = document.querySelector('.assign-btn');
                    const studentId = firstBtn.dataset.id;
                    const studentName = firstBtn.dataset.name;
                    showAssignPointsModal(studentId, studentName);
                }
            });
            
            closeModal.addEventListener('click', hideAssignPointsModal);
            cancelBtn.addEventListener('click', hideAssignPointsModal);
            modalOverlay.addEventListener('click', hideAssignPointsModal);
            
            // Modal Controls for Convert Points
            const convertPointsModal = document.getElementById('convertPointsModal');
            const convertBtns = document.querySelectorAll('.convert-btn');
            const closeConvertModal = document.getElementById('closeConvertModal');
            const cancelConvertBtn = document.getElementById('cancelConvertBtn');
            const convertModalOverlay = document.getElementById('convertModalOverlay');
            const convertStudentIdField = document.getElementById('convert_student_id');
            const convertStudentNameField = document.getElementById('convertStudentName');
            
            // Show convert modal with student data
            function showConvertPointsModal(studentId, studentName) {
                convertStudentIdField.value = studentId;
                convertStudentNameField.textContent = studentName;
                convertPointsModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Hide convert modal
            function hideConvertPointsModal() {
                convertPointsModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            // Event listeners for convert modal
            convertBtns.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.dataset.id;
                    const studentName = this.dataset.name;
                    showConvertPointsModal(studentId, studentName);
                });
            });
            
            closeConvertModal.addEventListener('click', hideConvertPointsModal);
            cancelConvertBtn.addEventListener('click', hideConvertPointsModal);
            convertModalOverlay.addEventListener('click', hideConvertPointsModal);
            
            // Student Search Functionality
            const searchStudentsInput = document.getElementById('search-students');
            
            searchStudentsInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const studentRows = document.querySelectorAll('#student-list tr');
                
                studentRows.forEach(row => {
                    const studentName = row.querySelector('.text-sm.font-medium.text-gray-900').textContent.toLowerCase();
                    const studentId = row.querySelector('.text-xs.text-gray-500').textContent.toLowerCase();
                    
                    if (studentName.includes(searchTerm) || studentId.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
            
            // Chart period change
            document.getElementById('chart-period').addEventListener('change', function() {
                // Here you would normally fetch data for the selected period
                // For this example, we'll just update with random data
                
                const period = this.value;
                let labels, data;
                
                switch(period) {
                    case 'week':
                        labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
                        data = [5, 8, 12, 7, 15, 10, 6];
                        break;
                    case 'month':
                        labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                        data = [18, 25, 32, 41];
                        break;
                    case 'quarter':
                        labels = ['Jan', 'Feb', 'Mar'];
                        data = [45, 68, 82];
                        break;
                    case 'year':
                        labels = ['Q1', 'Q2', 'Q3', 'Q4'];
                        data = [120, 150, 180, 210];
                        break;
                    default:
                        labels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
                        data = [18, 25, 32, 41];
                }
                
                pointsChart.data.labels = labels;
                pointsChart.data.datasets[0].data = data;
                pointsChart.update();
            });
            
            // Auto-dismiss alerts after 5 seconds
            setTimeout(() => {
                const alerts = document.querySelectorAll('#successAlert, #errorAlert');
                alerts.forEach(alert => {
                    if (alert) {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            if (alert.parentNode) {
                                alert.parentNode.removeChild(alert);
                            }
                        }, 500);
                    }
                });
            }, 5000);
        });
    </script>
</body>
</html>