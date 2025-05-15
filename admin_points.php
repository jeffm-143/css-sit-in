<?php 
    include('./conn_back/points_process.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Lab Usage Points | Admin</title>
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
        .points-progress .bar {
            transition: width 0.6s ease;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .pulse-animation {
            animation: pulse 2s infinite ease-in-out;
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
            background: linear-gradient(to bottom, #10B981, #059669);
            border-radius: 10px;
            transition: background 0.3s ease;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #059669, #047857);
            box-shadow: 0 0 5px rgba(16, 185, 129, 0.5);
        }

        /* Scrollbar Animation */
        @keyframes scrollGlow {
            0% { box-shadow: 0 0 0px rgba(16, 185, 129, 0); }
            50% { box-shadow: 0 0 8px rgba(16, 185, 129, 0.5); }
            100% { box-shadow: 0 0 0px rgba(16, 185, 129, 0); }
        }

        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            animation: scrollGlow 2s infinite;
        }

        /* Fix for scrollable containers */
        .scrollable-container {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .scrollable-container table {
            width: 100%;
            flex: 1;
        }

        /* Ensure the table cells don't exceed their container */
        .scrollable-container td {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Fix sticky header for better scrolling */
        .scrollable-container thead {
            position: sticky;
            top: 0;
            z-index: 2;
            background-color: #f9fafb; /* bg-gray-50 */
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        /* Add a subtle fade effect for better visibility */
        .scrollable-container::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(to top, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0));
            pointer-events: none;
            z-index: 1;
            border-bottom-left-radius: 8px;
            border-bottom-right-radius: 8px;
            width: calc(100% - 8px); /* Account for scrollbar width */
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation header with soft shadow and subtle gradient -->
    <div class="bg-white shadow-sm border-b sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">                <a href="admin-dashboard.php" class="group flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200">
                    <div class="p-1.5 rounded-full bg-blue-50 group-hover:bg-blue-100 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </div>
                    <span class="font-medium">Back to Dashboard</span>
                </a>
                <div class="hidden md:flex items-center space-x-2">
                    <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center">
                        <i class="fas fa-star text-green-600 text-sm"></i>
                    </div>
                    <span class="font-medium text-gray-700">Lab Usage Points</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Header with modern spacing and gradient accent -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 animate-fade-in">
            <div>
                <div class="inline-flex items-center bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-1 rounded-full text-sm mb-3">
                    <i class="fas fa-award mr-2"></i>
                    Reward System
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                    Student Lab Usage Points
                </h1>
                <p class="text-gray-600 mt-2 max-w-2xl">
                    Reward students for proper lab etiquette. Each 3 points earned can be converted into an additional lab session.
                </p>
            </div>
            
            <div class="mt-6 md:mt-0 flex items-center space-x-3">
                <button id="assignPointsBtn" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Assign Points
                </button>
            </div>
        </div>
        
        <!-- Success/Error Messages -->
        <div id="alertMessages">
            <?php if (isset($_SESSION['success_message'])): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: '<?php echo $_SESSION['success_message']; ?>',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'rounded-xl'
                        }
                    });
                </script>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error_message'])): ?>
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: '<?php echo $_SESSION['error_message']; ?>',
                        showConfirmButton: true,
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'bg-blue-600 hover:bg-blue-700'
                        }
                    });
                </script>
                <?php unset($_SESSION['error_message']); ?>
            <?php endif; ?>
        </div>
        
        <!-- Stats Cards with elegant hover effects -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Point Assignments Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-clipboard-check text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Point Assignments</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($totalAssignments); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-chart-line text-blue-500 mr-1"></i>
                                Total point assignments made
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
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Points</dt>
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
                                Points awarded to students
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Students with Points Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-users text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Rewarded Students</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($studentsWithPoints); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-user-graduate text-green-500 mr-1"></i>
                                Students with earned points
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bonus Sessions Card -->
            <div class="stats-card bg-white overflow-hidden shadow-lg rounded-xl transition-all duration-300 border border-gray-100">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                            <i class="fas fa-gift text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Bonus Sessions</dt>
                                <dd>
                                    <div class="text-2xl font-bold text-gray-900">
                                        <?php echo number_format($sessionsAwarded); ?>
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="text-xs text-gray-500">
                            <span class="flex items-center">
                                <i class="fas fa-clock text-yellow-500 mr-1"></i>
                                Extra lab sessions awarded
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Points Assignment Form -->
            <div id="pointsForm" class="lg:col-span-1 bg-white shadow-xl rounded-xl border border-gray-200 hidden animate-fade-in">
                <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
                            <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center mr-3">
                                <i class="fas fa-star text-green-600"></i>
                            </div>
                            Assign Points
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Give points to students for good lab behavior
                        </p>
                    </div>
                    <button id="closeFormBtn" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="px-6 py-6">
                    <form action="admin_points.php" method="POST" class="space-y-6" id="pointsAssignmentForm">
                        <div>
                            <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Select Student</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-graduate text-gray-400"></i>
                                </div>
                                <select id="student_id" name="student_id" class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg appearance-none" required>
                                    <option value="">-- Select Student --</option>
                                    <?php foreach ($students as $student): ?>
                                        <?php if ($student['SESSION'] < 30): ?>                                            <option value="<?php echo $student['ID']; ?>" data-sessions="<?php echo $student['SESSION']; ?>">
                                                <?php echo htmlspecialchars($student['FIRSTNAME'] . ' ' . $student['LASTNAME']); ?> 
                                                (<?php echo htmlspecialchars($student['ID_NUMBER']); ?>) - 
                                                <?php echo htmlspecialchars($student['COURSE']); ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?php echo $student['ID']; ?>" data-sessions="<?php echo $student['SESSION']; ?>" disabled class="text-gray-400">
                                                <?php echo htmlspecialchars($student['FIRSTNAME'] . ' ' . $student['LASTNAME']); ?> (Max Sessions: 30/30)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-red-500 hidden" id="maxSessionsWarning">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                This student already has maximum sessions (30/30). Points can only be awarded after they use some sessions.
                            </p>
                        </div>
                        
                        <div>
                            <label for="points" class="block text-sm font-medium text-gray-700 mb-1">Points to Award</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-star text-gray-400"></i>
                                </div>
                                <select id="points" name="points" class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg appearance-none" required>
                                    <option value="">-- Select Points --</option>
                                    <option value="1">1 Point - Basic Effort</option>
                                    <option value="2">2 Points - Good Effort</option>
                                    <option value="3">3 Points - Excellent Effort</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Points</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                    <i class="fas fa-comment-alt text-gray-400"></i>
                                </div>
                                <textarea id="reason" name="reason" rows="3" class="focus:ring-green-500 focus:border-green-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg" placeholder="Explain why these points are being awarded" required></textarea>
                            </div>
                            <p class="mt-1 text-xs text-gray-500 flex items-center">
                                <i class="fas fa-info-circle text-blue-500 mr-1"></i>
                                Be specific about the positive behaviors observed
                            </p>
                        </div>
                        
                        <div class="pt-5 border-t border-gray-200">
                            <div class="flex justify-end space-x-3">
                                <button type="button" id="cancelBtn" class="px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                    Cancel
                                </button>
                                <button type="submit" name="assign_points" id="submitPointsBtn" class="inline-flex justify-center items-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                    <i class="fas fa-star mr-2"></i> Award Points
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Recent Points Activity Feed -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-lg overflow-hidden sm:rounded-xl border border-gray-200 animate-fade-in h-full">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-600 to-emerald-600 sm:px-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                                <i class="fas fa-history mr-2"></i>
                                Recent Point Activity
                            </h3>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-green-600">
                                Last 10 Awards
                            </span>
                        </div>
                    </div>
                    
                    <?php if (empty($recentPoints)): ?>
                        <div class="px-4 py-12 sm:px-6 text-center">
                            <div class="flex flex-col items-center">
                                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
                                    <i class="fas fa-award text-green-400 text-2xl"></i>
                                </div>
                                <h3 class="mt-3 text-lg font-medium text-gray-900">No points awarded yet</h3>
                                <p class="mt-2 text-sm text-gray-500 max-w-md mx-auto">
                                    Start rewarding students for proper lab etiquette using the Assign Points button.
                                </p>
                                <div class="mt-6">
                                    <button id="emptyStateBtn" type="button" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                        <i class="fas fa-plus-circle mr-2"></i>
                                        Award Your First Points
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="overflow-y-auto custom-scrollbar scrollable-container" style="max-height: 340px;">
                            <ul role="list" class="divide-y divide-gray-200">
                                <?php foreach ($recentPoints as $activity): ?>
                                    <?php
                                        // Determine badge colors based on points
                                        $badgeColor = 'bg-gray-100 text-gray-800';
                                        if ($activity['points_earned'] == 1) {
                                            $badgeColor = 'bg-blue-100 text-blue-800';
                                        } elseif ($activity['points_earned'] == 2) {
                                            $badgeColor = 'bg-purple-100 text-purple-800';
                                        } elseif ($activity['points_earned'] == 3) {
                                            $badgeColor = 'bg-green-100 text-green-800';
                                        }
                                        
                                        // Format time ago
                                        $awardedTime = strtotime($activity['awarded_date']);
                                        $timeAgo = time() - $awardedTime;
                                        
                                        if ($timeAgo < 60) {
                                            $timeAgoStr = 'Just now';
                                        } elseif ($timeAgo < 3600) {
                                            $mins = floor($timeAgo / 60);
                                            $timeAgoStr = $mins . ' minute' . ($mins > 1 ? 's' : '') . ' ago';
                                        } elseif ($timeAgo < 86400) {
                                            $hours = floor($timeAgo / 3600);
                                            $timeAgoStr = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                                        } else {
                                            $days = floor($timeAgo / 86400);
                                            $timeAgoStr = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                                        }
                                    ?>
                                    <li class="px-6 py-5 hover:bg-gray-50 transition-colors">
                                        <div class="flex items-start space-x-4">
                                            <div class="flex-shrink-0 pt-1">
                                                <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                    <i class="fas fa-star text-green-500"></i>
                                                </div>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex justify-between items-start">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        Points awarded to <span class="font-semibold"><?php echo htmlspecialchars($activity['student_name']); ?></span>
                                                    </p>
                                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium <?php echo $badgeColor; ?>">
                                                        <?php echo $activity['points_earned']; ?> point<?php echo $activity['points_earned'] > 1 ? 's' : ''; ?>
                                                    </span>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($activity['points_reason']); ?>
                                                </p>
                                                <div class="mt-2 flex items-center space-x-4">
                                                    <div class="flex items-center text-xs text-gray-500">
                                                        <i class="fas fa-user-tie mr-1 text-gray-400"></i>
                                                        by <?php echo htmlspecialchars($activity['awarded_by']); ?>
                                                    </div>
                                                    <div class="flex items-center text-xs text-gray-500">
                                                        <i class="far fa-clock mr-1 text-gray-400"></i>
                                                        <?php echo $timeAgoStr; ?>
                                                    </div>
                                                    <?php if ($activity['converted_to_session']): ?>
                                                        <div class="flex items-center text-xs text-green-600">
                                                            <i class="fas fa-exchange-alt mr-1"></i>
                                                            Converted to session
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Students Table with modern styling -->
        <div class="bg-white shadow-lg overflow-hidden sm:rounded-xl border border-gray-200 animate-fade-in">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-green-600 to-emerald-600 sm:px-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <i class="fas fa-users mr-2"></i>
                        Students Point Status
                    </h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-green-600">
                        <i class="fas fa-search mr-1"></i>
                        <span id="studentCount"><?php echo count($students); ?> students</span>
                    </span>
                </div>
            </div>
            
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 sm:px-6">
                <div class="flex flex-col sm:flex-row sm:items-center">
                    <div class="relative rounded-md shadow-sm w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="studentSearch" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 sm:text-sm" placeholder="Search by name or ID">
                    </div>
                    <div class="mt-3 sm:mt-0 sm:ml-4 flex items-center">
                        <span class="mr-2 text-sm text-gray-500">Filter:</span>
                        <div class="flex space-x-2">
                            <button data-filter="all" class="filter-btn px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500">
                                All
                            </button>
                            <button data-filter="has-points" class="filter-btn px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500">
                                With Points
                            </button>
                            <button data-filter="no-points" class="filter-btn px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500">
                                No Points
                            </button>
                            <button data-filter="max-sessions" class="filter-btn px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-gray-500">
                                Max Sessions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="px-4 py-16 sm:px-6 text-center">
                    <div class="flex flex-col items-center">
                        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100">
                            <i class="fas fa-users text-green-400 text-3xl"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-medium text-gray-900">No students found</h3>
                        <p class="mt-3 text-sm text-gray-500 max-w-md mx-auto">
                            There are no students registered in the system yet. Students will appear here once they create accounts.
                        </p>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto overflow-y-auto custom-scrollbar scrollable-container" style="max-height: 510px;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Student
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Course & Year
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Points Progress
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Statistics
                                </th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sessions
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($students as $student): ?>                                <tr class="hover:bg-gray-50 transition-colors student-row" 
                                    data-name="<?php echo strtolower(htmlspecialchars($student['FIRSTNAME'] . ' ' . $student['LASTNAME'])); ?>" 
                                    data-id="<?php echo strtolower(htmlspecialchars($student['ID_NUMBER'])); ?>"
                                    data-points="<?php echo $student['POINTS']; ?>"
                                    data-sessions="<?php echo $student['SESSION']; ?>">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="<?php echo !empty($student['IMAGE']) ? $student['IMAGE'] : 'images/person.jpg'; ?>" 
                                                     alt="<?php echo htmlspecialchars($student['name']); ?>">
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">                                                    <?php echo htmlspecialchars($student['FIRSTNAME'] . ' ' . $student['LASTNAME']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500 flex items-center">
                                                    <span class="inline-flex items-center justify-center h-5 w-5 rounded-full bg-gray-100 text-gray-500 mr-1">
                                                        <i class="fas fa-id-card text-xs"></i>
                                                    </span>
                                                    <?php echo htmlspecialchars($student['ID_NUMBER']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">                                            <?php echo htmlspecialchars($student['COURSE']); ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Year <?php echo htmlspecialchars($student['YEAR']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="mb-1 flex justify-between items-center">                                                <span class="text-xs font-medium text-gray-700">
                                                <?php echo $student['POINTS']; ?>/3 Points
                                            </span>
                                            <?php if ($student['POINTS'] == 3): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Ready to convert!
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="points-progress h-2 relative max-w-xl rounded-full overflow-hidden bg-gray-200">
                                            <?php 
                                                $percentage = ($student['POINTS'] / 3) * 100;
                                                $barColor = 'bg-blue-500';
                                                
                                                if ($percentage >= 99) {
                                                    $barColor = 'bg-green-500 pulse-animation';
                                                } elseif ($percentage >= 66) {
                                                    $barColor = 'bg-green-500';
                                                } elseif ($percentage >= 33) {
                                                    $barColor = 'bg-yellow-500';
                                                }
                                            ?>
                                            <div class="bar h-2 <?php echo $barColor; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex justify-center space-x-3">
                                            <div class="text-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $student['total_points_earned']; ?>
                                                </div>
                                                <div class="text-xs text-gray-500">Total Points Earned</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo $student['sessions_earned']; ?>
                                                </div>
                                                <div class="text-xs text-gray-500">Sessions Earned</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <?php
                                        // Display with appropriate styling                                        $sessionClass = ($student['SESSION'] >= 30) ? 'text-red-600 font-medium' : 'text-gray-900';
                                        ?>
                                        <div class="text-sm <?php echo $sessionClass; ?> flex items-center justify-center">
                                            <i class="far fa-clock mr-1 <?php echo ($student['SESSION'] >= 30) ? 'text-red-500' : 'text-gray-400'; ?>"></i>
                                            <?php echo $student['SESSION']; ?>/30
                                            <?php if ($student['SESSION'] >= 30): ?>
                                                <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Maximum Reached
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">                                    <?php if ($student['SESSION'] < 30): ?>
                                            <button data-student-id="<?php echo $student['ID']; ?>" 
                                                    data-student-name="<?php echo htmlspecialchars($student['FIRSTNAME'] . ' ' . $student['LASTNAME']); ?>" 
                                                    class="award-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                                <i class="fas fa-star mr-1"></i> Award
                                            </button>
                                        <?php else: ?>
                                            <button type="button"
                                                    class="max-sessions-btn inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-gray-400 hover:bg-gray-500 transition-colors">
                                                <i class="fas fa-ban mr-1"></i> Max Sessions
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Hidden form for point conversion -->
    <form id="convertPointsForm" action="admin_points.php" method="POST" class="hidden">
        <input type="hidden" name="student_id" id="convert_student_id">
        <input type="hidden" name="convert_points" value="1">
    </form>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle form visibility
            const assignPointsBtn = document.getElementById('assignPointsBtn');
            const emptyStateBtn = document.getElementById('emptyStateBtn');
            const pointsForm = document.getElementById('pointsForm');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeFormBtn = document.getElementById('closeFormBtn');
            
            function showPointsForm() {
                pointsForm.classList.remove('hidden');
                // Smooth scroll to form
                pointsForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            function hidePointsForm() {
                pointsForm.classList.add('hidden');
            }
            
            if (assignPointsBtn) {
                assignPointsBtn.addEventListener('click', showPointsForm);
            }
            
            if (emptyStateBtn) {
                emptyStateBtn.addEventListener('click', showPointsForm);
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', hidePointsForm);
            }
            
            if (closeFormBtn) {
                closeFormBtn.addEventListener('click', hidePointsForm);
            }
            
            // Check for maximum sessions when student is selected
            const studentSelect = document.getElementById('student_id');
            const maxSessionsWarning = document.getElementById('maxSessionsWarning');
            const pointsAssignmentForm = document.getElementById('pointsAssignmentForm');
            
            if (studentSelect && maxSessionsWarning) {
                studentSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption && selectedOption.hasAttribute('data-sessions')) {
                        const sessions = parseInt(selectedOption.getAttribute('data-sessions'));
                        
                        if (sessions >= 30) {
                            maxSessionsWarning.classList.remove('hidden');
                            // Disable form submission
                            document.getElementById('submitPointsBtn').setAttribute('disabled', 'disabled');
                            document.getElementById('submitPointsBtn').classList.add('opacity-50', 'cursor-not-allowed');
                        } else {
                            maxSessionsWarning.classList.add('hidden');
                            // Enable form submission
                            document.getElementById('submitPointsBtn').removeAttribute('disabled');
                            document.getElementById('submitPointsBtn').classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    }
                });
            }
            
            // Award buttons functionality
            const awardButtons = document.querySelectorAll('.award-btn');
            
            awardButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Show the form
                    showPointsForm();
                    
                    // Pre-select the student
                    const studentId = this.getAttribute('data-student-id');
                    const studentSelect = document.getElementById('student_id');
                    
                    if (studentSelect) {
                        studentSelect.value = studentId;
                        // Trigger the change event to run our session check
                        const changeEvent = new Event('change');
                        studentSelect.dispatchEvent(changeEvent);
                    }
                    
                    // Focus on the points dropdown
                    const pointsSelect = document.getElementById('points');
                    if (pointsSelect) {
                        pointsSelect.focus();
                    }
                });
            });
            
            // Convert buttons functionality
            const convertButtons = document.querySelectorAll('.convert-btn');
            
            convertButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const studentId = this.getAttribute('data-student-id');
                    const studentName = this.getAttribute('data-student-name');
                    
                    // Normal confirmation dialog - we already filtered out max session students
                    Swal.fire({
                        title: 'Convert Points to Session?',
                        html: `Are you sure you want to convert <strong>${studentName}'s</strong> points to a bonus lab session?`,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#10B981',
                        cancelButtonColor: '#6B7280',
                        confirmButtonText: 'Yes, Convert Points',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'px-4 py-2 rounded-lg',
                            cancelButton: 'px-4 py-2 rounded-lg'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitConversion(studentId);
                        }
                    });
                });
            });
            
            // Function to submit the conversion form
            function submitConversion(studentId) {
                // Set the student ID in the hidden form
                document.getElementById('convert_student_id').value = studentId;
                
                // Submit the form
                document.getElementById('convertPointsForm').submit();
            }
            
            // Student search functionality
            const studentSearch = document.getElementById('studentSearch');
            const studentRows = document.querySelectorAll('.student-row');
            const studentCount = document.getElementById('studentCount');
            
            if (studentSearch) {
                studentSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase().trim();
                    let visibleCount = 0;
                    
                    studentRows.forEach(row => {
                        const name = row.getAttribute('data-name');
                        const id = row.getAttribute('data-id');
                        
                        // Check if name or ID contains the search term
                        if (name.includes(searchTerm) || id.includes(searchTerm)) {
                            row.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                    
                    // Update the count
                    if (studentCount) {
                        studentCount.textContent = `${visibleCount} students`;
                    }
                });
            }
            
            // Filter buttons
            const filterButtons = document.querySelectorAll('.filter-btn');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active state
                    filterButtons.forEach(btn => {
                        btn.classList.remove('bg-green-100', 'text-green-800');
                        btn.classList.add('bg-gray-100', 'text-gray-800');
                    });
                    
                    this.classList.remove('bg-gray-100', 'text-gray-800');
                    this.classList.add('bg-green-100', 'text-green-800');
                    
                    const filter = this.getAttribute('data-filter');
                    let visibleCount = 0;
                    
                    studentRows.forEach(row => {
                        const points = parseInt(row.getAttribute('data-points'));
                        const sessions = parseInt(row.getAttribute('data-sessions'));
                        
                        if (filter === 'all' || 
                            (filter === 'has-points' && points > 0) || 
                            (filter === 'no-points' && points === 0) ||
                            (filter === 'max-sessions' && sessions >= 30)) {
                            row.classList.remove('hidden');
                            visibleCount++;
                        } else {
                            row.classList.add('hidden');
                        }
                    });
                    
                    // Update the count
                    if (studentCount) {
                        studentCount.textContent = `${visibleCount} students`;
                    }
                });
            });
        });

        // Max Sessions button functionality
        const maxSessionsButtons = document.querySelectorAll('.max-sessions-btn');

        maxSessionsButtons.forEach(button => {
            button.addEventListener('click', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Maximum Sessions Reached',
                    text: 'Cannot award points! This student already has the maximum 30 sessions. Points can only be awarded after they use some of their current sessions.',
                    confirmButtonColor: '#3B82F6',
                    confirmButtonText: 'Understood'
                });
            });
        });
    </script>
</body>
</html>