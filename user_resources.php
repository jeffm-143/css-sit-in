<?php
session_start();
include('db_connection.php');

// Verify student session
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Fetch resources
$resources = [];
$query = "SELECT * FROM resources ORDER BY upload_date DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}

// Get username for display
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learning Resources | Student Portal</title>
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
        
        .custom-shadow {
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -5px rgba(59, 130, 246, 0.04);
        }
        
        .resource-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .resource-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .category-pill {
            transition: all 0.3s ease;
        }
        
        .category-pill.active {
            background-color: #2563eb;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.5);
        }
        
        .category-pill:hover:not(.active) {
            background-color: #eff6ff;
        }
        
        .profile-gradient {
            background: linear-gradient(120deg, #e0f2fe, #dbeafe, #e0e7ff);
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
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-gradient-to-r from-blue-800 to-indigo-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Learning Resources</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <li><a href="#" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-bell mr-1"></i>Notification</a></li>
                        <li><a href="dashboard.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-home mr-1"></i>Home</a></li>
                        <li><a href="edit_profile.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-user-edit mr-1"></i>Edit Profile</a></li>
                        <li><a href="user_labsched.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-alt mr-1"></i>Lab Schedule</a></li>
                        <li><a href="reservation.php" class="text-white/80 hover:text-yellow-400 transition-colors"><i class="fas fa-calendar-check mr-1"></i>Reservation</a></li>
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
            <h1 class="text-3xl font-bold text-gray-800">Learning Resources</h1>
            <p class="text-gray-500 mt-2">Access course materials, references, and helpful links</p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Resource Categories & Filters Sidebar -->
            <div class="lg:col-span-1">
                <!-- Resource Search -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 custom-shadow">
                    <div class="profile-gradient p-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-search text-blue-600 mr-2"></i>
                            Search Resources
                        </h2>
                    </div>
                    <div class="p-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="search-resources" placeholder="Search resources..." class="block w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Resource Categories -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 custom-shadow">
                    <div class="profile-gradient p-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-layer-group text-blue-600 mr-2"></i>
                            Categories
                        </h2>
                    </div>
                    <div class="p-4">
                        <button id="all-resources" class="category-pill active w-full text-left px-4 py-3 rounded-md mb-2 flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-folder mr-3 text-blue-600"></i>
                                <span>All Resources</span>
                            </span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full"><?php echo count($resources); ?></span>
                        </button>
                        
                        <button data-type="file" class="category-pill w-full text-left px-4 py-3 rounded-md mb-2 flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-file-alt mr-3 text-blue-600"></i>
                                <span>Files & Documents</span>
                            </span>
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                <?php 
                                    $fileCount = 0;
                                    foreach ($resources as $resource) {
                                        if ($resource['resource_type'] === 'file') {
                                            $fileCount++;
                                        }
                                    }
                                    echo $fileCount;
                                ?>
                            </span>
                        </button>
                        
                        <button data-type="link" class="category-pill w-full text-left px-4 py-3 rounded-md mb-2 flex items-center justify-between">
                            <span class="flex items-center">
                                <i class="fas fa-link mr-3 text-blue-600"></i>
                                <span>External Links</span>
                            </span>
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                <?php 
                                    $linkCount = 0;
                                    foreach ($resources as $resource) {
                                        if ($resource['resource_type'] === 'link') {
                                            $linkCount++;
                                        }
                                    }
                                    echo $linkCount;
                                ?>
                            </span>
                        </button>
                    </div>
                </div>
                
                <!-- Course & Year Filters -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6 custom-shadow">
                    <div class="profile-gradient p-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-filter text-blue-600 mr-2"></i>
                            Filters
                        </h2>
                    </div>
                    <div class="p-4 space-y-4">
                        <div>
                            <label for="filter-course" class="block text-sm font-medium text-gray-700 mb-1">Course</label>
                            <select id="filter-course" class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Courses</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="BSIS">BSIS</option>
                            </select>
                        </div>
                        <div>
                            <label for="filter-year" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                            <select id="filter-year" class="block w-full px-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Years</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden custom-shadow">
                    <div class="profile-gradient p-4 border-b border-blue-100">
                        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                            <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                            Need Help?
                        </h2>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-600 mb-4">
                            Can't find what you're looking for? Contact your instructor or department for additional resources.
                        </p>
                        <a href="mailto:css@pup.edu.ph" class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-md hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-envelope mr-2"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Resources Content Area -->
            <div class="lg:col-span-3">
                <!-- Resources Statistics -->
                <div class="bg-white rounded-xl shadow-lg p-6 mb-6 custom-shadow">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Total Resources</p>
                                <h3 class="text-2xl font-bold text-blue-800"><?php echo count($resources); ?></h3>
                            </div>
                            <div class="bg-blue-200 rounded-full p-3">
                                <i class="fas fa-book-open text-blue-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Files</p>
                                <h3 class="text-2xl font-bold text-purple-800"><?php echo $fileCount; ?></h3>
                            </div>
                            <div class="bg-purple-200 rounded-full p-3">
                                <i class="fas fa-file-alt text-purple-600 text-xl"></i>
                            </div>
                        </div>
                        <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-lg p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">External Links</p>
                                <h3 class="text-2xl font-bold text-indigo-800"><?php echo $linkCount; ?></h3>
                            </div>
                            <div class="bg-indigo-200 rounded-full p-3">
                                <i class="fas fa-link text-indigo-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resources Grid -->
                <div id="resources-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php if (count($resources) > 0): ?>
                        <?php foreach ($resources as $resource): ?>
                            <div class="resource-card bg-white rounded-xl overflow-hidden shadow-lg custom-shadow" 
                                 data-type="<?php echo htmlspecialchars($resource['resource_type']); ?>" 
                                 data-course="<?php echo htmlspecialchars($resource['course']); ?>" 
                                 data-year="<?php echo htmlspecialchars($resource['year_level']); ?>">
                                <div class="p-5">
                                    <div class="flex items-center justify-between mb-3">
                                        <?php if ($resource['resource_type'] === 'file'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-file-alt mr-1"></i> File
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
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
                                            <h3 class="text-lg font-medium text-gray-900 truncate">
                                                <?php echo htmlspecialchars($resource['title']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">
                                                <?php echo htmlspecialchars($resource['description']); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo htmlspecialchars($resource['course']); ?>
                                        </span>
                                        <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo htmlspecialchars($resource['year_level']); ?> Year
                                        </span>
                                    </div>
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                        <?php if ($resource['resource_type'] === 'file'): ?>
                                            <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-md hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:-translate-y-0.5" download>
                                                <i class="fas fa-download mr-2"></i> Download
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo htmlspecialchars($resource['link_url']); ?>" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white text-sm font-medium rounded-md hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:-translate-y-0.5">
                                                <i class="fas fa-external-link-alt mr-2"></i> Visit Link
                                            </a>
                                        <?php endif; ?>
                                        <div class="text-xs text-gray-500 italic">
                                            by <?php echo htmlspecialchars($resource['uploaded_by']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-span-2 flex flex-col items-center justify-center bg-white rounded-xl p-8 text-center shadow-lg">
                            <div class="mb-4 w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center">
                                <i class="fas fa-folder-open text-blue-500 text-3xl"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-900 mb-2">No Learning Resources Available</h3>
                            <p class="text-gray-500 mb-4">There are currently no resources uploaded for your courses.</p>
                            <p class="text-sm text-gray-400">Please check back later or contact your instructor.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- No Results Found -->
                <div id="no-results" class="hidden mt-6 bg-white rounded-xl p-8 text-center shadow-lg">
                    <div class="mb-4 w-20 h-20 mx-auto bg-blue-50 rounded-full flex items-center justify-center">
                        <i class="fas fa-search text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-medium text-gray-900 mb-2">No Matching Resources</h3>
                    <p class="text-gray-500">Try adjusting your search criteria or filters.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Resource filtering functionality
            const categoryButtons = document.querySelectorAll('.category-pill');
            const searchInput = document.getElementById('search-resources');
            const filterCourse = document.getElementById('filter-course');
            const filterYear = document.getElementById('filter-year');
            const resourceCards = document.querySelectorAll('.resource-card');
            const resourcesGrid = document.getElementById('resources-grid');
            const noResults = document.getElementById('no-results');
            
            let activeCategory = 'all';
            
            // Set active category
            function setActiveCategory(type) {
                categoryButtons.forEach(button => {
                    button.classList.remove('active');
                });
                
                if (type === 'all') {
                    document.getElementById('all-resources').classList.add('active');
                } else {
                    document.querySelector(`.category-pill[data-type="${type}"]`).classList.add('active');
                }
                
                activeCategory = type;
                applyFilters();
            }
            
            // Add event listeners to category buttons
            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.getAttribute('data-type') || 'all';
                    setActiveCategory(type);
                });
            });
            
            // Filter resources function
            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const courseFilter = filterCourse.value;
                const yearFilter = filterYear.value;
                
                let visibleCount = 0;
                
                resourceCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('p').textContent.toLowerCase();
                    const type = card.getAttribute('data-type');
                    const course = card.getAttribute('data-course');
                    const year = card.getAttribute('data-year');
                    
                    const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                    const matchesType = activeCategory === 'all' || type === activeCategory;
                    const matchesCourse = courseFilter === '' || course === courseFilter;
                    const matchesYear = yearFilter === '' || year === yearFilter;
                    
                    if (matchesSearch && matchesType && matchesCourse && matchesYear) {
                        card.classList.remove('hidden');
                        visibleCount++;
                    } else {
                        card.classList.add('hidden');
                    }
                });
                
                // Show/hide no results message
                if (visibleCount === 0) {
                    resourcesGrid.classList.add('hidden');
                    noResults.classList.remove('hidden');
                } else {
                    resourcesGrid.classList.remove('hidden');
                    noResults.classList.add('hidden');
                }
                
                // Update counter badges
                updateCounters();
            }
            
            // Update counter badges based on visible resources
            function updateCounters() {
                const allResourcesButton = document.getElementById('all-resources');
                const filesButton = document.querySelector('.category-pill[data-type="file"]');
                const linksButton = document.querySelector('.category-pill[data-type="link"]');
                
                let totalCount = 0;
                let fileCount = 0;
                let linkCount = 0;
                
                resourceCards.forEach(card => {
                    if (!card.classList.contains('hidden')) {
                        totalCount++;
                        
                        if (card.getAttribute('data-type') === 'file') {
                            fileCount++;
                        } else if (card.getAttribute('data-type') === 'link') {
                            linkCount++;
                        }
                    }
                });
                
                allResourcesButton.querySelector('.rounded-full').textContent = totalCount;
                filesButton.querySelector('.rounded-full').textContent = fileCount;
                linksButton.querySelector('.rounded-full').textContent = linkCount;
            }
            
            // Connect search and filter inputs to filter function
            searchInput.addEventListener('input', applyFilters);
            filterCourse.addEventListener('change', applyFilters);
            filterYear.addEventListener('change', applyFilters);
            
            // Initialize with all resources selected
            setActiveCategory('all');
        });
    </script>
</body>
</html>