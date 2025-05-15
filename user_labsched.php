<?php
session_start();
include('db_connection.php');

// Verify student session
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Fetch all schedules
$result = null;
$query = "SELECT * FROM lab_schedules ORDER BY upload_date DESC";
$result = $conn->query($query);

// Get unique lab rooms for filtering
$labRooms = [];
if ($result && $result->num_rows > 0) {
    // Store the results in an array so we can reuse them
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
        if (!in_array($row['lab_room'], $labRooms)) {
            $labRooms[] = $row['lab_room'];
        }
    }
    // Reset the result pointer
    $result->data_seek(0);
} else {
    $schedules = [];
}

// Sort lab rooms for consistent display
sort($labRooms);

// Get username for display
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Schedules | Student Portal</title>
    <link rel="icon" type="image/png" href="images/css.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        
        .card-shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        
        .schedule-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .lab-room-button {
            transition: all 0.3s ease;
        }
        
        .lab-room-button.active {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5);
        }
        
        .lab-room-button:hover:not(.active) {
            background-color: #eff6ff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        
        /* Custom scrollbar styling */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
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
        
        .profile-gradient {
            background: linear-gradient(120deg, #e0f2fe, #dbeafe, #e0e7ff);
        }
        
        .custom-shadow {
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -5px rgba(59, 130, 246, 0.04);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Laboratory Schedules</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <li><a href="#" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-bell mr-1"></i>Notification</a></li>
                        <li><a href="dashboard.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="history.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-history mr-1"></i>History</a></li>
                        <li><a href="reservation.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-alt mr-1"></i>Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-indigo-900 px-6 py-2 rounded-lg font-bold hover:bg-yellow-500 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                        <i class="fas fa-sign-out-alt mr-1"></i>Log out
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <div class="container mx-auto px-4 py-10 max-w-7xl">
        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Laboratory Schedules</h1>
            <p class="text-gray-500 mt-2">Browse and find available schedules for computer laboratories</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Lab Room Selection Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden custom-shadow">
                    <div class="profile-gradient p-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-door-open text-blue-600 mr-2"></i>
                            Lab Rooms
                        </h2>
                    </div>
                    <div class="p-4">
                        <button id="all-rooms-btn" class="lab-room-button active w-full text-left px-4 py-3 rounded-md mb-2 flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-th-large mr-3 text-blue-600"></i>
                                <span>All Laboratories</span>
                            </span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo count($schedules); ?></span>
                        </button>
                        
                        <?php if (!empty($labRooms)): ?>
                            <?php foreach ($labRooms as $labRoom): 
                                // Count schedules for this lab room
                                $count = 0;
                                foreach ($schedules as $schedule) {
                                    if ($schedule['lab_room'] === $labRoom) {
                                        $count++;
                                    }
                                }
                            ?>
                                <button data-lab-room="<?php echo htmlspecialchars($labRoom); ?>" class="lab-room-button w-full text-left px-4 py-3 rounded-md mb-2 flex items-center justify-between">
                                    <span class="flex items-center">
                                        <i class="fas fa-laptop-code mr-3 text-blue-600"></i>
                                        <span><?php echo htmlspecialchars($labRoom); ?></span>
                                    </span>
                                    <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo $count; ?></span>
                                </button>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="py-4 text-center text-gray-500">
                                <p>No lab rooms available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Help Section -->
                    <div class="bg-blue-50 p-4 mt-2">
                        <h3 class="text-sm font-medium text-blue-800 mb-2">Need Help?</h3>
                        <p class="text-xs text-blue-700 mb-2">If you need assistance with lab access or have questions about the schedules, please contact the laboratory staff.</p>
                        <div class="flex items-center text-xs text-blue-800">
                            <i class="fas fa-info-circle mr-1"></i>
                            <span>Contact: css.laboratory@pup.edu.ph</span>
                        </div>
                    </div>
                </div>

                <!-- Search Box -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mt-6 custom-shadow">
                    <div class="p-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="search-schedule" placeholder="Search schedules..." class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Content Area -->
            <div class="lg:col-span-3">
                <!-- Lab information -->
                <div id="all-labs-info" class="mb-6 bg-white rounded-xl shadow-lg p-6 custom-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-blue-100 rounded-full">
                            <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="ml-3 text-lg font-semibold text-gray-800">Laboratory Information</h2>
                    </div>
                    <p class="text-gray-600 mb-4">
                        The Computer Science Department provides various specialized computer laboratories for different coursework and activities. 
                        Please check the schedule for your assigned laboratory before coming to the campus.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <h3 class="font-medium text-gray-900 mb-2">Laboratory Hours</h3>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li><span class="font-medium">Monday - Friday:</span> 8:00 AM - 7:00 PM</li>
                                <li><span class="font-medium">Saturday:</span> 8:00 AM - 4:00 PM</li>
                                <li><span class="font-medium">Sunday:</span> Closed</li>
                            </ul>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                            <h3 class="font-medium text-gray-900 mb-2">Laboratory Rules</h3>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li>• Always bring your student ID</li>
                                <li>• No food and drinks allowed</li>
                                <li>• No installation of unauthorized software</li>
                                <li>• Save your work before leaving</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Lab Room Specific Info (initially hidden) -->
                <?php foreach ($labRooms as $labRoom): ?>
                <div id="<?php echo str_replace(' ', '-', strtolower($labRoom)); ?>-info" class="mb-6 bg-white rounded-xl shadow-lg p-6 hidden custom-shadow">
                    <div class="flex items-center mb-4">
                        <div class="p-2 bg-blue-100 rounded-full">
                            <i class="fas fa-laptop-code text-blue-600 text-xl"></i>
                        </div>
                        <h2 class="ml-3 text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($labRoom); ?> Information</h2>
                    </div>
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4 bg-blue-50 p-4 rounded-lg">
                        <div>
                            <h3 class="font-medium text-gray-900">Laboratory Specifications</h3>
                            <p class="text-sm text-gray-600 mt-1">
                                <?php 
                                    // Different specs based on lab room
                                    switch ($labRoom) {
                                        case 'LAB 524':
                                            echo 'Networking and Security Lab - 30 Workstations with Cisco Equipment';
                                            break;
                                        case 'LAB 526':
                                            echo 'Programming Lab - 40 PCs with Latest Development Tools';
                                            break;
                                        case 'LAB 528':
                                            echo 'Multimedia Lab - 25 High-Performance Workstations';
                                            break;
                                        case 'LAB 530':
                                            echo 'General Computing Lab - 35 Standard Computer Systems';
                                            break;
                                        case 'LAB 542':
                                            echo 'Software Engineering Lab - 30 Systems with Project Management Tools';
                                            break;
                                        case 'LAB 544':
                                            echo 'Database Systems Lab - 28 Workstations with Database Software';
                                            break;
                                        case 'LAB 517':
                                            echo 'AI and Machine Learning Lab - 20 High-Performance Computing Systems';
                                            break;
                                        default:
                                            echo 'Standard Computer Laboratory with Modern Equipment';
                                    }
                                ?>
                            </p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <button class="text-xs bg-white border border-blue-200 text-blue-600 px-3 py-1.5 rounded-md hover:bg-blue-50 transition">
                                <i class="fas fa-info-circle mr-1"></i> Request Access
                            </button>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm">
                        Please check the schedule below for availability and class times. For special access requests, contact the laboratory supervisor.
                    </p>
                </div>
                <?php endforeach; ?>

                <!-- Schedule Grid -->
                <div id="schedule-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (count($schedules) > 0): ?>
                        <?php foreach ($schedules as $schedule): ?>
                            <div class="schedule-card bg-white rounded-xl overflow-hidden shadow-lg custom-shadow" 
                                data-lab-room="<?php echo htmlspecialchars($schedule['lab_room']); ?>" 
                                data-title="<?php echo htmlspecialchars($schedule['title']); ?>" 
                                data-description="<?php echo htmlspecialchars($schedule['description']); ?>">
                                <div class="relative overflow-hidden">
                                    <img src="uploads/schedules/<?php echo htmlspecialchars($schedule['schedule_image']); ?>" 
                                        alt="<?php echo htmlspecialchars($schedule['title']); ?>" 
                                        class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
                                    <div class="absolute top-0 left-0 m-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($schedule['lab_room']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="p-5">
                                    <h3 class="text-lg font-medium text-gray-900 line-clamp-1">
                                        <?php echo htmlspecialchars($schedule['title']); ?>
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 line-clamp-2">
                                        <?php echo htmlspecialchars($schedule['description']); ?>
                                    </p>
                                    <div class="mt-3 flex items-center text-xs text-gray-500">
                                        <i class="fas fa-clock mr-1"></i>
                                        <span>Updated on <?php echo date('M j, Y', strtotime($schedule['upload_date'])); ?></span>
                                    </div>
                                    <div class="mt-4 flex justify-between items-center">
                                        <button class="view-schedule text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none flex items-center" 
                                                data-image="uploads/schedules/<?php echo htmlspecialchars($schedule['schedule_image']); ?>"
                                                data-title="<?php echo htmlspecialchars($schedule['title']); ?>"
                                                data-description="<?php echo htmlspecialchars($schedule['description']); ?>">
                                            <i class="fas fa-eye mr-1"></i> View Full Schedule
                                        </button>
                                        <button class="download-schedule text-gray-500 hover:text-blue-700 transition-colors"
                                                data-image="uploads/schedules/<?php echo htmlspecialchars($schedule['schedule_image']); ?>">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-2 flex flex-col items-center justify-center bg-white rounded-xl p-8 text-center shadow-lg">
                            <div class="mb-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-blue-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Lab Schedules Available</h3>
                            <p class="text-gray-500 mb-4">There are currently no laboratory schedules posted.</p>
                            <p class="text-sm text-gray-400">Please check back later or contact the department.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- No Schedules Found (For filtering) -->
                <div id="no-results" class="hidden col-span-2 flex flex-col items-center justify-center bg-white rounded-xl p-8 text-center shadow-lg">
                    <div class="mb-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No Matching Schedules</h3>
                    <p class="text-gray-500">Try adjusting your search criteria.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- View Schedule Modal -->
    <div id="viewScheduleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" id="viewModalOverlay"></div>
            <div class="bg-white rounded-xl shadow-xl transform transition-all max-w-4xl w-full relative">
                <button type="button" id="closeViewModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 z-10">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-8 flex items-center justify-center rounded-l-xl">
                        <img id="view-schedule-image" src="" alt="Schedule" class="max-h-[500px] max-w-full shadow-lg rounded border-4 border-white">
                    </div>
                    <div class="p-8 custom-scrollbar" style="max-height: 80vh; overflow-y: auto;">
                        <h3 id="view-schedule-title" class="text-xl font-semibold text-gray-900 mb-4"></h3>
                        <div class="mb-6">
                            <h4 class="text-sm uppercase text-gray-500 font-medium mb-2">Description</h4>
                            <p id="view-schedule-description" class="text-gray-700 whitespace-pre-line"></p>
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <button id="downloadScheduleBtn" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white py-3 px-6 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <i class="fas fa-download mr-2"></i> Download Schedule
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lab room filter buttons
            const labRoomButtons = document.querySelectorAll('.lab-room-button');
            const allRoomsBtn = document.getElementById('all-rooms-btn');
            const scheduleCards = document.querySelectorAll('.schedule-card');
            const searchInput = document.getElementById('search-schedule');
            const scheduleGrid = document.getElementById('schedule-grid');
            const noResults = document.getElementById('no-results');
            
            // Lab room info panels
            const allLabsInfo = document.getElementById('all-labs-info');
            const labInfoPanels = document.querySelectorAll('[id$="-info"]');
            
            // Set active lab room and filter schedules
            function setActiveLab(labRoom) {
                // Update button styles
                labRoomButtons.forEach(button => {
                    button.classList.remove('active');
                });
                
                // Set clicked button as active
                this.classList.add('active');
                
                // Hide all lab info panels first
                labInfoPanels.forEach(panel => {
                    panel.classList.add('hidden');
                });
                
                // Show appropriate info panel
                const selectedLabRoom = this.getAttribute('data-lab-room');
                if (selectedLabRoom) {
                    const infoPanel = document.getElementById(`${selectedLabRoom.toLowerCase().replace(/\s+/g, '-')}-info`);
                    if (infoPanel) {
                        infoPanel.classList.remove('hidden');
                    } else {
                        allLabsInfo.classList.remove('hidden');
                    }
                } else {
                    // Show all labs info when "All Laboratories" is selected
                    allLabsInfo.classList.remove('hidden');
                }
                
                // Filter the schedule cards
                filterSchedules();
            }
            
            // Add event listeners to lab room buttons
            labRoomButtons.forEach(button => {
                button.addEventListener('click', setActiveLab);
            });
            
            // Search and filter functionality
            function filterSchedules() {
                const activeButton = document.querySelector('.lab-room-button.active');
                const selectedLabRoom = activeButton.getAttribute('data-lab-room');
                const searchTerm = searchInput.value.toLowerCase();
                
                let visibleCount = 0;
                
                scheduleCards.forEach(card => {
                    const labRoom = card.dataset.labRoom;
                    const title = card.dataset.title.toLowerCase();
                    const description = card.dataset.description.toLowerCase();
                    
                    const matchesLabRoom = !selectedLabRoom || labRoom === selectedLabRoom;
                    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                    
                    if (matchesLabRoom && matchesSearch) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });
                
                // Show or hide the "No results" message
                if (visibleCount === 0) {
                    scheduleGrid.classList.add('hidden');
                    noResults.classList.remove('hidden');
                } else {
                    scheduleGrid.classList.remove('hidden');
                    noResults.classList.add('hidden');
                }
            }
            
            // Connect search input to filter function
            searchInput.addEventListener('input', filterSchedules);
            
            // Modal controls for View Schedule
            const viewScheduleModal = document.getElementById('viewScheduleModal');
            const viewScheduleBtns = document.querySelectorAll('.view-schedule');
            const closeViewModal = document.getElementById('closeViewModal');
            const viewModalOverlay = document.getElementById('viewModalOverlay');
            const viewScheduleImage = document.getElementById('view-schedule-image');
            const viewScheduleTitle = document.getElementById('view-schedule-title');
            const viewScheduleDescription = document.getElementById('view-schedule-description');
            const downloadScheduleBtn = document.getElementById('downloadScheduleBtn');
            
            // Show view schedule modal
            function showViewScheduleModal(image, title, description) {
                viewScheduleImage.src = image;
                viewScheduleTitle.textContent = title;
                viewScheduleDescription.textContent = description;
                downloadScheduleBtn.setAttribute('data-image', image);
                viewScheduleModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Hide view schedule modal
            function hideViewScheduleModal() {
                viewScheduleModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            // Event listeners for view modal
            viewScheduleBtns.forEach(button => {
                button.addEventListener('click', function() {
                    const image = this.dataset.image;
                    const title = this.dataset.title;
                    const description = this.dataset.description;
                    showViewScheduleModal(image, title, description);
                });
            });
            
            closeViewModal.addEventListener('click', hideViewScheduleModal);
            viewModalOverlay.addEventListener('click', hideViewScheduleModal);
            
            // Download schedule functionality
            downloadScheduleBtn.addEventListener('click', function() {
                const imagePath = this.getAttribute('data-image');
                const link = document.createElement('a');
                link.href = imagePath;
                link.download = 'schedule_' + Date.now() + '.jpg';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            
            // Add download functionality to download buttons in cards
            document.querySelectorAll('.download-schedule').forEach(button => {
                button.addEventListener('click', function() {
                    const imagePath = this.getAttribute('data-image');
                    const link = document.createElement('a');
                    link.href = imagePath;
                    link.download = 'schedule_' + Date.now() + '.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                });
            });
        });
    </script>
</body>
</html>