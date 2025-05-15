<?php 
    include('./conn_back/labsched_process.php');
    
    // Verify admin session
    if (!isset($_SESSION['username'])) {
        header("Location: login.php");
        exit();
    }
?>

<?php if ($sweetAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?php echo $alertType; ?>',
            title: '<?php echo $alertTitle; ?>',
            text: '<?php echo $alertText; ?>',
            confirmButtonColor: '#2563eb'
        });
    });
</script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Schedule Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="images/css.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        
        .schedule-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .schedule-card:hover {
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
        
        .image-preview {
            max-height: 200px;
            margin: 10px 0;
            border: 2px dashed #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        
        .image-preview img {
            max-height: 100%;
            max-width: 100%;
            object-fit: contain;
        }
        
        .image-preview-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            width: 100%;
            height: 100%;
        }
        
        /* Custom scrollbar styling */
        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
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
                        Lab Schedule Management
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm text-gray-500">
                        Upload and manage laboratory schedules for students and faculty members.
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <button type="button" id="uploadScheduleBtn" class="btn-primary flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Upload New Schedule</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Lab Room Filter -->
        <div class="mb-6">
            <div class="bg-white rounded-lg shadow-sm p-4 flex flex-col sm:flex-row justify-between items-center card-shadow">
                <div class="flex items-center mb-4 sm:mb-0">
                    <span class="text-gray-700 font-medium mr-2">Filter by Lab Room:</span>
                    <select id="lab-room-filter" class="border border-gray-300 rounded-md shadow-sm pl-3 pr-8 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
                        <option value="all">All Laboratories</option>
                        <option value="LAB 524">LAB 524</option>
                        <option value="LAB 526">LAB 526</option>
                        <option value="LAB 528">LAB 528</option>
                        <option value="LAB 530">LAB 530</option>
                        <option value="LAB 542">LAB 542</option>
                        <option value="LAB 544">LAB 544</option>
                        <option value="LAB 517">LAB 517</option>
                    </select>
                </div>
                <div class="flex items-center w-full sm:w-auto">
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="search-schedule" placeholder="Search schedules..." class="input-field pl-10 py-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Schedule Grid -->
        <div id="schedule-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($schedule = $result->fetch_assoc()): ?>
                    <div class="schedule-card bg-white rounded-lg overflow-hidden card-shadow animate-fade-in" data-lab-room="<?php echo htmlspecialchars($schedule['lab_room']); ?>" data-title="<?php echo htmlspecialchars($schedule['title']); ?>" data-description="<?php echo htmlspecialchars($schedule['description']); ?>">
                        <div class="aspect-w-16 aspect-h-9 bg-gray-100 relative overflow-hidden">
                            <img src="uploads/schedules/<?php echo htmlspecialchars($schedule['schedule_image']); ?>" alt="<?php echo htmlspecialchars($schedule['title']); ?>" class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300">
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
                                <span>Uploaded on <?php echo date('M j, Y', strtotime($schedule['upload_date'])); ?></span>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <button class="view-schedule text-blue-600 hover:text-blue-800 text-sm font-medium focus:outline-none" 
                                        data-image="uploads/schedules/<?php echo htmlspecialchars($schedule['schedule_image']); ?>"
                                        data-title="<?php echo htmlspecialchars($schedule['title']); ?>"
                                        data-description="<?php echo htmlspecialchars($schedule['description']); ?>">
                                    <i class="fas fa-eye mr-1"></i> View Full Schedule
                                </button>
                                <div class="flex space-x-2">
                                    <button class="edit-schedule text-gray-500 hover:text-blue-600 transition-colors duration-200"
                                            data-id="<?php echo $schedule['schedule_id']; ?>"
                                            data-lab-room="<?php echo htmlspecialchars($schedule['lab_room']); ?>"
                                            data-title="<?php echo htmlspecialchars($schedule['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($schedule['description']); ?>"
                                            data-image="uploads/schedules/<?php echo htmlspecialchars($schedule['schedule_image']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-schedule text-gray-500 hover:text-red-600 transition-colors duration-200"
                                            data-id="<?php echo $schedule['schedule_id']; ?>"
                                            data-title="<?php echo htmlspecialchars($schedule['title']); ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-3 flex flex-col items-center justify-center bg-white rounded-lg p-8 text-center shadow-sm animate-fade-in">
                    <div class="mb-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-calendar-alt text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No Schedules Found</h3>
                    <p class="text-gray-500 mb-4">Upload your first lab schedule to get started.</p>
                    <button id="emptyStateUploadBtn" class="btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i> Upload Schedule
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Upload Schedule Modal -->
    <div id="uploadModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" id="modalOverlay"></div>
            <div class="bg-white rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6 animate-slide-up">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-medium text-gray-900">Upload New Schedule</h3>
                    <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="./conn_back/labsched_process.php" method="post" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="lab_room" class="block text-sm font-medium text-gray-700">Laboratory Room</label>
                        <select id="lab_room" name="lab_room" required class="input-field mt-1">
                            <option value="">Select Lab Room</option>
                            <option value="LAB 524">LAB 524</option>
                            <option value="LAB 526">LAB 526</option>
                            <option value="LAB 528">LAB 528</option>
                            <option value="LAB 530">LAB 530</option>
                            <option value="LAB 542">LAB 542</option>
                            <option value="LAB 544">LAB 544</option>
                            <option value="LAB 517">LAB 517</option>
                        </select>
                    </div>
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Schedule Title</label>
                        <input type="text" id="title" name="title" required class="input-field mt-1" placeholder="Enter schedule title">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" class="input-field mt-1" placeholder="Enter schedule description"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Schedule Image</label>
                        <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                            <div class="space-y-1 text-center">
                                <div class="image-preview-container">
                                    <div class="image-preview hidden">
                                        <img id="preview-img" src="#" alt="Preview">
                                    </div>
                                    <div class="image-preview-placeholder">
                                        <i class="fas fa-cloud-upload-alt text-gray-400 text-3xl mb-3"></i>
                                        <p class="text-sm text-gray-500">
                                            <span class="text-blue-600 font-medium hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                Upload a file
                                            </span>
                                            or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            PNG, JPG, GIF up to 10MB
                                        </p>
                                    </div>
                                </div>
                                <input id="schedule_image" name="schedule_image" type="file" accept="image/*" class="sr-only" required>
                            </div>
                        </div>
                        <div class="mt-1 text-center">
                            <label for="schedule_image" class="btn-secondary inline-block cursor-pointer">
                                <i class="fas fa-image mr-1"></i> Select Image
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 space-x-3">
                        <button type="button" id="cancelBtn" class="btn-secondary">Cancel</button>
                        <button type="submit" name="upload_schedule" class="btn-primary">
                            <i class="fas fa-upload mr-2"></i> Upload Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Schedule Modal -->
    <div id="viewScheduleModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" id="viewModalOverlay"></div>
            <div class="bg-white rounded-lg shadow-xl transform transition-all max-w-4xl w-full animate-fade-in relative">
                <button type="button" id="closeViewModal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-500 z-10">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2">
                    <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-8 flex items-center justify-center rounded-l-lg">
                        <img id="view-schedule-image" src="" alt="Schedule" class="max-h-[500px] max-w-full shadow-lg rounded border-4 border-white">
                    </div>
                    <div class="p-8 custom-scrollbar" style="max-height: 80vh; overflow-y: auto;">
                        <h3 id="view-schedule-title" class="text-xl font-semibold text-gray-900 mb-4"></h3>
                        <div class="mb-6">
                            <h4 class="text-sm uppercase text-gray-500 font-medium mb-2">Description</h4>
                            <p id="view-schedule-description" class="text-gray-700 whitespace-pre-line"></p>
                        </div>
                        <div class="pt-4 border-t border-gray-200">
                            <button id="downloadScheduleBtn" class="btn-primary w-full">
                                <i class="fas fa-download mr-2"></i> Download Schedule
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" id="deleteModalOverlay"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Schedule</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete "<span id="delete-schedule-title"></span>"? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
                <form action="./conn_back/labsched_process.php" method="post" class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <input type="hidden" id="schedule_id_to_delete" name="schedule_id">
                    <button type="submit" name="delete_schedule" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button type="button" id="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal controls for Upload Schedule
            const uploadModal = document.getElementById('uploadModal');
            const uploadScheduleBtn = document.getElementById('uploadScheduleBtn');
            const emptyStateUploadBtn = document.getElementById('emptyStateUploadBtn');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const modalOverlay = document.getElementById('modalOverlay');
            
            // Show upload modal
            function showUploadModal() {
                uploadModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Hide upload modal
            function hideUploadModal() {
                uploadModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
                resetForm();
            }
            
            // Reset form fields
            function resetForm() {
                document.querySelector('form').reset();
                document.getElementById('preview-img').src = '';
                document.querySelector('.image-preview').classList.add('hidden');
                document.querySelector('.image-preview-placeholder').classList.remove('hidden');
            }
            
            // Event listeners for upload modal
            if (uploadScheduleBtn) uploadScheduleBtn.addEventListener('click', showUploadModal);
            if (emptyStateUploadBtn) emptyStateUploadBtn.addEventListener('click', showUploadModal);
            closeModal.addEventListener('click', hideUploadModal);
            cancelBtn.addEventListener('click', hideUploadModal);
            modalOverlay.addEventListener('click', hideUploadModal);
            
            // Image preview functionality
            const scheduleImageInput = document.getElementById('schedule_image');
            const previewImg = document.getElementById('preview-img');
            const imagePreview = document.querySelector('.image-preview');
            const imagePreviewPlaceholder = document.querySelector('.image-preview-placeholder');
            
            scheduleImageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        imagePreviewPlaceholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
            
            // Make the entire upload area clickable
            document.querySelector('.flex.justify-center').addEventListener('click', function() {
                scheduleImageInput.click();
            });
            
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
            
            // Modal controls for Delete Schedule
            const deleteModal = document.getElementById('deleteModal');
            const deleteScheduleBtns = document.querySelectorAll('.delete-schedule');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const deleteModalOverlay = document.getElementById('deleteModalOverlay');
            const scheduleIdToDeleteField = document.getElementById('schedule_id_to_delete');
            const deleteScheduleTitleSpan = document.getElementById('delete-schedule-title');
            
            // Show delete modal
            function showDeleteModal(scheduleId, title) {
                scheduleIdToDeleteField.value = scheduleId;
                deleteScheduleTitleSpan.textContent = title;
                deleteModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Hide delete modal
            function hideDeleteModal() {
                deleteModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            // Event listeners for delete modal
            deleteScheduleBtns.forEach(button => {
                button.addEventListener('click', function() {
                    const scheduleId = this.dataset.id;
                    const title = this.dataset.title;
                    showDeleteModal(scheduleId, title);
                });
            });
            
            cancelDeleteBtn.addEventListener('click', hideDeleteModal);
            deleteModalOverlay.addEventListener('click', hideDeleteModal);
            
            // Lab room filter functionality
            const labRoomFilter = document.getElementById('lab-room-filter');
            const searchInput = document.getElementById('search-schedule');
            const scheduleCards = document.querySelectorAll('.schedule-card');
            
            function filterSchedules() {
                const selectedLabRoom = labRoomFilter.value.toLowerCase();
                const searchTerm = searchInput.value.toLowerCase();
                
                scheduleCards.forEach(card => {
                    const labRoom = card.dataset.labRoom.toLowerCase();
                    const title = card.dataset.title.toLowerCase();
                    const description = card.dataset.description.toLowerCase();
                    
                    const matchesLabRoom = selectedLabRoom === 'all' || labRoom === selectedLabRoom;
                    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                    
                    if (matchesLabRoom && matchesSearch) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
                
                // Check if any cards are visible
                const visibleCards = document.querySelectorAll('.schedule-card[style="display: block;"]');
                const noResultsElement = document.getElementById('no-results');
                
                if (visibleCards.length === 0) {
                    if (!noResultsElement) {
                        const noResults = document.createElement('div');
                        noResults.id = 'no-results';
                        noResults.className = 'col-span-3 flex flex-col items-center justify-center bg-white rounded-lg p-8 text-center shadow-sm animate-fade-in';
                        noResults.innerHTML = `
                            <div class="mb-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-blue-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Matching Schedules</h3>
                            <p class="text-gray-500">Try adjusting your search or filter criteria.</p>
                        `;
                        document.getElementById('schedule-grid').appendChild(noResults);
                    }
                } else {
                    if (noResultsElement) {
                        noResultsElement.remove();
                    }
                }
            }
            
            labRoomFilter.addEventListener('change', filterSchedules);
            searchInput.addEventListener('input', filterSchedules);
            
            // Drag and drop functionality for image upload
            const dropArea = document.querySelector('.flex.justify-center');
            
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
                dropArea.classList.add('border-blue-500', 'bg-blue-50');
            }
            
            function unhighlight() {
                dropArea.classList.remove('border-blue-500', 'bg-blue-50');
            }
            
            dropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                scheduleImageInput.files = files;
                
                if (files && files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        imagePreview.classList.remove('hidden');
                        imagePreviewPlaceholder.classList.add('hidden');
                    }
                    reader.readAsDataURL(files[0]);
                }
            }
        });
    </script>
</body>
</html>