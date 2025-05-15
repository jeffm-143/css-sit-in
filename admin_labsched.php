<?php 
    include('./conn_back/labsched_process.php');
?>

<?php if ($sweetAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo $alertTitle; ?>',
            text: '<?php echo $alertText; ?>',
            confirmButtonColor: '#7C3AED'
        });
    });
</script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Schedule Management</title>
    <link rel="icon" type="image/png" href="images/wbccs.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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
            box-shadow: 0 10px 25px -5px rgba(124, 58, 237, 0.1), 0 8px 10px -6px rgba(124, 58, 237, 0.1);
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
            background: linear-gradient(to bottom, #8B5CF6, #7C3AED);
            border-radius: 10px;
            transition: background 0.3s ease;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(to bottom, #7C3AED, #6D28D9);
            box-shadow: 0 0 5px rgba(124, 58, 237, 0.5);
        }
        @keyframes scrollGlow {
            0% { box-shadow: 0 0 0px rgba(124, 58, 237, 0); }
            50% { box-shadow: 0 0 8px rgba(124, 58, 237, 0.5); }
            100% { box-shadow: 0 0 0px rgba(124, 58, 237, 0); }
        }
        .custom-scrollbar:hover::-webkit-scrollbar-thumb {
            animation: scrollGlow 2s infinite;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Top Navigation Bar -->
    <div class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="admin-dashboard.php" class="group flex items-center space-x-2 text-gray-700 hover:text-purple-600 transition-colors duration-200">
                        <div class="p-1.5 rounded-full bg-purple-50 group-hover:bg-purple-100 transition-colors duration-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                        </div>
                        <span class="font-medium">Back to Dashboard</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="hidden md:flex items-center space-x-2 bg-gradient-to-r from-purple-50 to-indigo-50 px-3 py-1.5 rounded-lg shadow-sm">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-r from-purple-100 to-indigo-100 flex items-center justify-center shadow-sm">
                            <i class="fas fa-calendar-alt text-purple-600 text-sm"></i>
                        </div>
                        <span class="font-medium text-gray-700">Lab Schedules</span>
                    </div>
                    
                    <button class="md:hidden text-gray-500 hover:text-gray-700">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-8 animate__animated animate__fadeIn">
            <div class="mb-4 sm:mb-0">
                <div class="inline-flex items-center bg-gradient-to-r from-purple-600 to-violet-600 text-white px-4 py-1 rounded-full text-sm mb-3">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Schedule Management
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                    Lab Schedules Management
                </h1>
                <p class="text-gray-600 mt-1 max-w-2xl">
                    Manage laboratory room schedules for students and faculty. Upload and update schedules for all computer lab rooms.
                </p>
            </div>
            <button id="addScheduleBtn" class="bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white px-4 py-2.5 rounded-lg shadow-sm transition duration-200 flex items-center transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i> Add New Schedule
            </button>
        </div>
        
        <!-- Alert Messages -->
        <?php if(!empty($message)): ?>
            <div class="mb-6 animate__animated animate__fadeIn">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php
                // Get total number of schedules
                $total_count = isset($result) ? $result->num_rows : 0;
                
                // Get count of unique lab rooms with schedules
                $lab_counts = [];
                if(isset($result) && $result->num_rows > 0) {
                    $result->data_seek(0);
                    while($row = $result->fetch_assoc()) {
                        $lab_counts[$row['lab_room']] = true;
                    }
                }
                $labs_with_schedules = count($lab_counts);
                
                // Calculate labs without schedules
                $total_labs = 7; // Total number of lab rooms
                $labs_without_schedules = $total_labs - $labs_with_schedules;
            ?>
            
            <!-- Total Schedules -->
            <div class="stats-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 transform transition-all duration-200 hover:shadow-md hover:border-purple-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 bg-opacity-10 rounded-lg p-3">
                            <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-5">
                            <h3 class="text-gray-500 text-sm uppercase tracking-wider font-semibold">Total Schedules</h3>
                            <div class="mt-1 text-3xl font-bold text-gray-900"><?php echo $total_count; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Labs with Schedules -->
            <div class="stats-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 transform transition-all duration-200 hover:shadow-md hover:border-indigo-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 bg-opacity-10 rounded-lg p-3">
                            <i class="fas fa-door-open text-indigo-600 text-xl"></i>
                        </div>
                        <div class="ml-5">
                            <h3 class="text-gray-500 text-sm uppercase tracking-wider font-semibold">Labs with Schedules</h3>
                            <div class="mt-1 text-3xl font-bold text-gray-900"><?php echo $labs_with_schedules; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Labs without Schedules -->
            <div class="stats-card bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 transform transition-all duration-200 hover:shadow-md hover:border-yellow-200">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 bg-opacity-10 rounded-lg p-3">
                            <i class="fas fa-exclamation-triangle text-yellow-600 text-xl"></i>
                        </div>
                        <div class="ml-5">
                            <h3 class="text-gray-500 text-sm uppercase tracking-wider font-semibold">Labs Needing Schedules</h3>
                            <div class="mt-1 text-3xl font-bold text-gray-900"><?php echo $labs_without_schedules; ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lab Room Quick Filter -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-2">
                <a href="?filter=all" class="px-3 py-2 rounded-full <?php echo (!isset($_GET['filter']) || $_GET['filter'] == 'all') ? 'bg-purple-100 text-purple-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?> transition-colors duration-200 text-sm">
                    <i class="fas fa-border-all mr-1"></i> All Rooms
                </a>
                
                <?php
                $lab_rooms = ['524', '526', '528', '530', '542', '544', '517'];
                foreach($lab_rooms as $room):
                ?>
                    <a href="?filter=<?php echo $room; ?>" class="px-3 py-2 rounded-full <?php echo (isset($_GET['filter']) && $_GET['filter'] == $room) ? 'bg-purple-100 text-purple-800 font-medium' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'; ?> transition-colors duration-200 text-sm">
                        <i class="fas fa-door-closed mr-1"></i> Room <?php echo $room; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Schedules Display -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
            <!-- Table Header -->
            <div class="bg-gradient-to-r from-purple-600 to-violet-600 py-4 px-6 border-b border-purple-700">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-white flex items-center">
                        <i class="fas fa-list-alt mr-2"></i>
                        All Lab Schedules
                    </h2>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-purple-600">
                        <?php echo $total_count; ?> schedules
                    </span>
                </div>
            </div>
            
            <div class="overflow-x-auto custom-scrollbar">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Room</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Preview</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded By</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload Date</th>
                            <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php
                        if (isset($result) && $result->num_rows > 0) {
                            $result->data_seek(0); // Reset the result pointer
                            while ($row = $result->fetch_assoc()) {
                                // Skip if filter is applied and doesn't match
                                if (isset($_GET['filter']) && $_GET['filter'] != 'all' && $row['lab_room'] != $_GET['filter']) {
                                    continue;
                                }
                                ?>
                                <tr class="hover:bg-purple-50 transition-colors duration-200">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-9 w-9 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mr-3">
                                                <i class="fas fa-door-open"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">Room <?php echo $row['lab_room']; ?></div>
                                                <div class="text-xs text-gray-500">Computer Laboratory</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 font-medium"><?php echo htmlspecialchars($row['title']); ?></div>
                                        <?php if (!empty($row['description'])) { ?>
                                            <div class="text-xs text-gray-500 truncate max-w-xs"><?php echo htmlspecialchars($row['description']); ?></div>
                                        <?php } ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <a href="../uploads/schedules/<?php echo $row['schedule_image']; ?>" target="_blank" class="group relative block">
                                            <div class="h-16 w-28 rounded-lg overflow-hidden shadow-sm border border-gray-200 bg-gray-100">
                                                <img src="../uploads/schedules/<?php echo $row['schedule_image']; ?>" alt="Schedule" class="h-full w-full object-cover">
                                                <div class="absolute inset-0 bg-gradient-to-tr from-purple-700/70 to-purple-500/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-200 rounded-lg">
                                                    <span class="text-white text-sm"><i class="fas fa-eye mr-1"></i> Preview</span>
                                                </div>
                                            </div>
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 mr-2">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="text-sm text-gray-900"><?php echo htmlspecialchars($row['uploaded_by']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-700">
                                            <i class="far fa-calendar-alt text-gray-400 mr-1"></i>
                                            <?php echo date('M d, Y', strtotime($row['upload_date'])); ?>
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            <i class="far fa-clock text-gray-400 mr-1"></i>
                                            <?php echo date('h:i A', strtotime($row['upload_date'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="../uploads/schedules/<?php echo $row['schedule_image']; ?>" target="_blank" class="text-purple-600 hover:text-purple-900 p-1 rounded-full hover:bg-purple-100 transition-colors duration-200" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="../uploads/schedules/<?php echo $row['schedule_image']; ?>" download class="text-indigo-600 hover:text-indigo-900 p-1 rounded-full hover:bg-indigo-100 transition-colors duration-200" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this schedule?');" class="inline">
                                                <input type="hidden" name="schedule_id" value="<?php echo $row['schedule_id']; ?>">
                                                <button type="submit" name="delete_schedule" class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100 transition-colors duration-200" title="Delete">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="rounded-full bg-purple-100 p-4 mb-3">
                                            <i class="fas fa-calendar-times text-purple-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-600 font-medium mb-1">No schedules found</p>
                                        <p class="text-gray-500 text-sm max-w-md">Add your first lab schedule to help students and faculty view room availability.</p>
                                        <button id="emptyStateAddBtn" class="mt-4 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white px-4 py-2 rounded-lg shadow-sm transition duration-200 flex items-center">
                                            <i class="fas fa-plus mr-2"></i> Add First Schedule
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
        
    <!-- Add Schedule Form Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl m-4 animate__animated animate__fadeInUp animate__faster">
            <div class="bg-gradient-to-r from-purple-600 to-violet-600 px-6 py-4 rounded-t-2xl">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white flex items-center">
                        <span class="bg-white/20 text-white p-1.5 rounded-lg mr-3">
                            <i class="fas fa-calendar-plus"></i>
                        </span>
                        Add New Lab Schedule
                    </h2>
                    <button id="closeModal" class="text-white/80 hover:text-white p-1 rounded-full hover:bg-white/10 transition-colors duration-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
                
            <form action="" method="POST" enctype="multipart/form-data" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="lab_room" class="block text-sm font-medium text-gray-700 mb-1">Lab Room</label>
                        <div class="relative">
                            <select name="lab_room" id="lab_room" class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 appearance-none" required>
                                <option value="">Select Lab Room</option>
                                <option value="524">Lab Room 524</option>
                                <option value="526">Lab Room 526</option>
                                <option value="528">Lab Room 528</option>
                                <option value="530">Lab Room 530</option>
                                <option value="542">Lab Room 542</option>
                                <option value="544">Lab Room 544</option>
                                <option value="517">Lab Room 517</option>
                            </select>
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <i class="fas fa-door-open"></i>
                            </div>
                            <div class="absolute right-3 top-3 text-gray-400 pointer-events-none">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Schedule Title</label>
                        <div class="relative">
                            <input type="text" name="title" id="title" class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="e.g., Second Semester Schedule" required>
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <i class="fas fa-heading"></i>
                            </div>
                        </div>
                    </div>
                </div>
                    
                <div class="mt-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                    <div class="relative">
                        <textarea name="description" id="description" rows="3" class="w-full border border-gray-300 rounded-lg pl-10 pr-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500" placeholder="Enter any additional details about this schedule..."></textarea>
                        <div class="absolute left-3 top-2.5 text-gray-400">
                            <i class="fas fa-align-left"></i>
                        </div>
                    </div>
                </div>
                    
                <div class="mt-6">
                    <label for="schedule_image" class="block text-sm font-medium text-gray-700 mb-1">Schedule Image</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-purple-400 transition-colors duration-200 bg-gray-50">
                        <div class="space-y-1 text-center">
                            <i class="fas fa-image text-gray-400 text-4xl"></i>
                            <div class="flex text-sm text-gray-600 mt-2">
                                <label for="schedule_image" class="relative cursor-pointer bg-white rounded-md font-medium text-purple-600 hover:text-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500 px-2 py-1.5 shadow-sm">
                                    <span>Upload a file</span>
                                    <input id="schedule_image" name="schedule_image" type="file" class="sr-only" accept="image/*" required>
                                </label>
                                <p class="pl-1 pt-1.5">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">PNG, JPG, JPEG up to 5MB</p>
                            <div id="preview" class="mt-4 hidden">
                                <div class="relative mx-auto w-40 h-40 bg-gray-100 rounded-lg shadow-sm overflow-hidden">
                                    <img id="imagePreview" class="w-full h-full object-contain" alt="Preview">
                                    <button type="button" id="removePreview" class="absolute top-1 right-1 h-6 w-6 rounded-full bg-gray-800 bg-opacity-50 text-white flex items-center justify-center hover:bg-opacity-70 transition-colors duration-200">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" id="cancelBtn" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200 flex items-center">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </button>
                    <button type="submit" name="upload_schedule" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-violet-600 hover:from-purple-700 hover:to-violet-700 text-white rounded-lg transition duration-200 flex items-center">
                        <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal handling
        const modal = document.getElementById('scheduleModal');
        const addBtn = document.getElementById('addScheduleBtn');
        const emptyStateAddBtn = document.getElementById('emptyStateAddBtn');
        const closeBtn = document.getElementById('closeModal');
        const cancelBtn = document.getElementById('cancelBtn');
        
        function showModal() {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function hideModal() {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        if (addBtn) {
            addBtn.addEventListener('click', showModal);
        }
        
        if (emptyStateAddBtn) {
            emptyStateAddBtn.addEventListener('click', showModal);
        }
        
        closeBtn.addEventListener('click', hideModal);
        cancelBtn.addEventListener('click', hideModal);
        
        // Close modal if clicked outside
        window.addEventListener('click', (e) => {
            if (e.target === modal) {
                hideModal();
            }
        });
        
        // Image preview
        const fileInput = document.getElementById('schedule_image');
        const preview = document.getElementById('preview');
        const imagePreview = document.getElementById('imagePreview');
        const removePreviewBtn = document.getElementById('removePreview');
        
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(e.target.files[0]);
            }
        });
        
        // File drag and drop
        const dropArea = fileInput.closest('div.flex.justify-center');
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });
        
        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, unhighlight, false);
        });
        
        function highlight() {
            dropArea.classList.add('border-purple-400', 'bg-purple-50');
        }
        
        function unhighlight() {
            dropArea.classList.remove('border-purple-400', 'bg-purple-50');
        }
        
        dropArea.addEventListener('drop', handleDrop, false);
        
        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
            
            if (files && files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                
                reader.readAsDataURL(files[0]);
            }
        }
        
        // Remove preview
        if (removePreviewBtn) {
            removePreviewBtn.addEventListener('click', function() {
                fileInput.value = '';
                preview.classList.add('hidden');
                imagePreview.src = '';
            });
        }
    </script>
</body>
</html>