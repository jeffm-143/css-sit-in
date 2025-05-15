<?php 
    include('conn_back/resources_process.php');
    
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
    <title>Resource Management | Admin Dashboard</title>
    <link rel="icon" type="image/png" href="images/css.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        
        .btn-primary {
            @apply bg-gradient-to-r from-blue-600 to-blue-800 hover:from-blue-700 hover:to-blue-900 text-white px-4 py-2.5 rounded-md transition duration-200 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
        }
        
        .btn-secondary {
            @apply bg-white text-gray-700 border border-gray-300 px-4 py-2.5 rounded-md hover:bg-gray-50 transition duration-200 font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2;
        }
        
        .input-field {
            @apply block w-full px-3 py-2.5 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 sm:text-sm;
        }
        
        .resource-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .resource-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .resource-type-badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
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
                        Resource Management
                    </h1>
                    <p class="mt-2 max-w-3xl text-sm text-gray-500">
                        Upload, manage, and organize learning materials and resources for students.
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <button type="button" id="addResourceBtn" class="btn-primary flex items-center">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>Add New Resource</span>
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

        <!-- Resource Filtering & Search -->
        <div class="bg-white rounded-lg shadow-sm p-5 mb-6 card-shadow">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="search-resources" class="input-field pl-10" placeholder="Search resources by title or description...">
                    </div>
                </div>
                <div class="flex flex-col md:flex-row gap-3">
                    <select id="filter-type" class="input-field">
                        <option value="">All Types</option>
                        <option value="file">Files</option>
                        <option value="link">External Links</option>
                    </select>
                    <select id="filter-course" class="input-field">
                        <option value="">All Courses</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCS">BSCS</option>
                    </select>
                    <select id="filter-year" class="input-field">
                        <option value="">All Years</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Resources Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <?php if (count($resources) > 0): ?>
                <?php foreach ($resources as $resource): ?>
                    <div class="bg-white rounded-lg overflow-hidden resource-card card-shadow animate-fade-in">
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-3">
                                <?php if ($resource['resource_type'] === 'file'): ?>
                                    <span class="resource-type-badge bg-blue-100 text-blue-800">
                                        <i class="fas fa-file-alt mr-1"></i> File
                                    </span>
                                <?php else: ?>
                                    <span class="resource-type-badge bg-purple-100 text-purple-800">
                                        <i class="fas fa-link mr-1"></i> Link
                                    </span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-500">
                                    <?php echo date('M d, Y', strtotime($resource['upload_date'])); ?>
                                </span>
                            </div>
                            <div class="flex items-start">
                                <div class="mr-4 bg-blue-50 rounded-lg p-2 w-12 h-12 flex items-center justify-center">
                                    <?php if ($resource['resource_type'] === 'file'): ?>
                                        <?php 
                                            $file_ext = pathinfo($resource['file_path'], PATHINFO_EXTENSION);
                                            $icon_class = 'fa-file';
                                            if (in_array($file_ext, ['pdf'])) {
                                                $icon_class = 'fa-file-pdf';
                                            } elseif (in_array($file_ext, ['doc', 'docx'])) {
                                                $icon_class = 'fa-file-word';
                                            } elseif (in_array($file_ext, ['xls', 'xlsx'])) {
                                                $icon_class = 'fa-file-excel';
                                            } elseif (in_array($file_ext, ['ppt', 'pptx'])) {
                                                $icon_class = 'fa-file-powerpoint';
                                            } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                $icon_class = 'fa-file-image';
                                            }
                                        ?>
                                        <i class="fas <?php echo $icon_class; ?> text-blue-600 text-2xl"></i>
                                    <?php else: ?>
                                        <i class="fas fa-globe text-blue-600 text-2xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-medium text-gray-900 truncate"><?php echo htmlspecialchars($resource['title']); ?></h3>
                                    <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?php echo htmlspecialchars($resource['description']); ?></p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($resource['course']); ?>
                                    </span>
                                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                        <?php echo htmlspecialchars($resource['year_level']); ?> Year
                                    </span>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-500">by <?php echo htmlspecialchars($resource['uploaded_by']); ?></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                <?php if ($resource['resource_type'] === 'file'): ?>
                                    <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center" download>
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo htmlspecialchars($resource['link_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center">
                                        <i class="fas fa-external-link-alt mr-1"></i> Visit Link
                                    </a>
                                <?php endif; ?>
                                <div class="flex space-x-2">
                                    <button class="edit-resource text-gray-500 hover:text-blue-600 transition-colors duration-200" 
                                            data-id="<?php echo $resource['id']; ?>"
                                            data-title="<?php echo htmlspecialchars($resource['title']); ?>"
                                            data-description="<?php echo htmlspecialchars($resource['description']); ?>"
                                            data-resource-type="<?php echo $resource['resource_type']; ?>"
                                            data-year-level="<?php echo $resource['year_level']; ?>"
                                            data-course="<?php echo $resource['course']; ?>"
                                            data-link-url="<?php echo htmlspecialchars($resource['link_url'] ?? ''); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="delete-resource text-gray-500 hover:text-red-600 transition-colors duration-200" data-id="<?php echo $resource['id']; ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-3 flex flex-col items-center justify-center bg-white rounded-lg p-8 text-center">
                    <div class="mb-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-folder-open text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900">No resources found</h3>
                    <p class="mt-2 text-gray-500">Add your first resource by clicking the "Add New Resource" button.</p>
                    <button id="emptyAddResourceBtn" class="mt-4 btn-primary">
                        <i class="fas fa-plus-circle mr-2"></i> Add Resource
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Add Resource Modal -->
    <div id="resourceModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity" id="modalOverlay"></div>
            <div class="bg-white rounded-lg shadow-xl transform transition-all max-w-lg w-full p-6 animate-slide-up">
                <div class="flex items-center justify-between mb-5">
                    <h3 class="text-lg font-medium text-gray-900">Add New Resource</h3>
                    <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form action="conn_back/resources_process.php" method="post" enctype="multipart/form-data" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" id="title" name="title" required class="input-field mt-1" placeholder="Enter resource title">
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" class="input-field mt-1" placeholder="Enter resource description"></textarea>
                    </div>
                    <div>
                        <label for="resource_type" class="block text-sm font-medium text-gray-700">Resource Type</label>
                        <select id="resource_type" name="resource_type" required class="input-field mt-1">
                            <option value="">Select type</option>
                            <option value="file">File Upload</option>
                            <option value="link">External Link</option>
                        </select>
                    </div>
                    <div id="fileUploadField" class="hidden">
                        <label for="resource_file" class="block text-sm font-medium text-gray-700">Upload File</label>
                        <div class="mt-1 flex items-center">
                            <label class="w-full flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                                <i class="fas fa-cloud-upload-alt mr-2 text-gray-400"></i>
                                <span id="fileName">Choose a file</span>
                                <input id="resource_file" name="resource_file" type="file" class="sr-only">
                            </label>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, JPG, PNG files are supported</p>
                    </div>
                    <div id="linkUrlField" class="hidden">
                        <label for="link_url" class="block text-sm font-medium text-gray-700">Link URL</label>
                        <input type="url" id="link_url" name="link_url" class="input-field mt-1" placeholder="Enter URL (https://...)">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level</label>
                            <select id="year_level" name="year_level" required class="input-field mt-1">
                                <option value="">Select year</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                        <div>
                            <label for="course" class="block text-sm font-medium text-gray-700">Course</label>
                            <select id="course" name="course" required class="input-field mt-1">
                                <option value="">Select course</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="BSIS">BSIS</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end pt-4 space-x-3">
                        <button type="button" id="cancelBtn" class="btn-secondary">Cancel</button>
                        <button type="submit" name="add_resource" class="btn-primary">
                            <i class="fas fa-save mr-2"></i> Save Resource
                        </button>
                    </div>
                </form>
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
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Resource</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">Are you sure you want to delete this resource? This action cannot be undone.</p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <a href="#" id="confirmDelete" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </a>
                    <button type="button" id="cancelDelete" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal controls
            const resourceModal = document.getElementById('resourceModal');
            const addResourceBtn = document.getElementById('addResourceBtn');
            const emptyAddResourceBtn = document.getElementById('emptyAddResourceBtn');
            const closeModal = document.getElementById('closeModal');
            const cancelBtn = document.getElementById('cancelBtn');
            const modalOverlay = document.getElementById('modalOverlay');
            
            // Show modal
            function showResourceModal() {
                resourceModal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
            
            // Hide modal
            function hideResourceModal() {
                resourceModal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
            
            // Event listeners for modal
            if (addResourceBtn) addResourceBtn.addEventListener('click', showResourceModal);
            if (emptyAddResourceBtn) emptyAddResourceBtn.addEventListener('click', showResourceModal);
            closeModal.addEventListener('click', hideResourceModal);
            cancelBtn.addEventListener('click', hideResourceModal);
            modalOverlay.addEventListener('click', hideResourceModal);
            
            // Resource type selection change
            const resourceTypeSelect = document.getElementById('resource_type');
            const fileUploadField = document.getElementById('fileUploadField');
            const linkUrlField = document.getElementById('linkUrlField');
            
            resourceTypeSelect.addEventListener('change', function() {
                if (this.value === 'file') {
                    fileUploadField.classList.remove('hidden');
                    linkUrlField.classList.add('hidden');
                    document.getElementById('link_url').value = '';
                } else if (this.value === 'link') {
                    fileUploadField.classList.add('hidden');
                    linkUrlField.classList.remove('hidden');
                    document.getElementById('resource_file').value = '';
                } else {
                    fileUploadField.classList.add('hidden');
                    linkUrlField.classList.add('hidden');
                }
            });
            
            // File input change
            const fileInput = document.getElementById('resource_file');
            const fileNameElement = document.getElementById('fileName');
            
            fileInput.addEventListener('change', function() {
                if (this.files.length > 0) {
                    fileNameElement.textContent = this.files[0].name;
                } else {
                    fileNameElement.textContent = 'Choose a file';
                }
            });
            
            // Delete resource functionality
            const deleteButtons = document.querySelectorAll('.delete-resource');
            const deleteModal = document.getElementById('deleteModal');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const deleteModalOverlay = document.getElementById('deleteModalOverlay');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const resourceId = this.dataset.id;
                    confirmDeleteBtn.href = `conn_back/resources_process.php?delete=${resourceId}`;
                    deleteModal.classList.remove('hidden');
                });
            });
            
            // Hide delete modal
            function hideDeleteModal() {
                deleteModal.classList.add('hidden');
            }
            
            cancelDeleteBtn.addEventListener('click', hideDeleteModal);
            deleteModalOverlay.addEventListener('click', hideDeleteModal);
            
            // Filter and search functionality
            const searchInput = document.getElementById('search-resources');
            const filterType = document.getElementById('filter-type');
            const filterCourse = document.getElementById('filter-course');
            const filterYear = document.getElementById('filter-year');
            
            const resourceCards = document.querySelectorAll('.resource-card');
            
            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const typeFilter = filterType.value.toLowerCase();
                const courseFilter = filterCourse.value;
                const yearFilter = filterYear.value;
                
                resourceCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('p').textContent.toLowerCase();
                    const type = card.querySelector('.resource-type-badge').textContent.toLowerCase();
                    const course = card.querySelectorAll('.bg-gray-100')[0].textContent.trim();
                    const year = card.querySelectorAll('.bg-gray-100')[1].textContent.trim();
                    
                    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                    const matchesType = typeFilter === '' || type.includes(typeFilter);
                    const matchesCourse = courseFilter === '' || course === courseFilter;
                    const matchesYear = yearFilter === '' || year.startsWith(yearFilter);
                    
                    if (matchesSearch && matchesType && matchesCourse && matchesYear) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }
            
            searchInput.addEventListener('input', applyFilters);
            filterType.addEventListener('change', applyFilters);
            filterCourse.addEventListener('change', applyFilters);
            filterYear.addEventListener('change', applyFilters);
        });
    </script>
</body>
</html>