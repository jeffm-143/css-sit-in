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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </div>
                    <span class="font-medium">Back to Dashboard</span>
                </a>
                <div class="hidden md:flex items-center space-x-2">
                    <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                        <i class="fas fa-book-reader text-blue-600 text-sm"></i>
                    </div>
                    <span class="font-medium text-gray-700">Lab Resources Management</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 animate-fade-in">
            <div>
                <div class="inline-flex items-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-4 py-1 rounded-full text-sm mb-3">
                    <i class="fas fa-lightbulb mr-2"></i>
                    Resource Center
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Learning Resources Management</h1>
                <p class="text-gray-600 mt-2 max-w-2xl">Create and manage high-quality learning materials for students.</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="mt-6 md:mt-0 flex items-center space-x-3">
                <div class="relative inline-block text-left">
                    <button id="filterBtn" type="button" class="inline-flex items-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        <i class="fas fa-filter mr-2 text-blue-500"></i>
                        Filter Resources
                    </button>
                    <div id="filterMenu" class="origin-top-right absolute right-0 mt-2 w-64 rounded-xl shadow-xl bg-white ring-1 ring-black ring-opacity-5 hidden z-10 divide-y divide-gray-100">
                        <!-- Filter menu content -->
                        <div class="py-3 px-4">
                            <p class="text-sm font-semibold text-gray-900">Filter Resources</p>
                            <p class="text-xs text-gray-500 mt-1">Select filters to apply</p>
                        </div>
                        
                        <!-- Year Level Filter -->
                        <div class="py-2 px-4">
                            <label class="text-xs font-medium text-gray-700 block mb-1">Year Level</label>
                            <select id="yearFilter" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Years</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                                <option value="4th Year">4th Year</option>
                            </select>
                        </div>

                        <!-- Course Filter -->
                        <div class="py-2 px-4">
                            <label class="text-xs font-medium text-gray-700 block mb-1">Course Program</label>
                            <select id="courseFilter" class="block w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Courses</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="ACT">ACT</option>
                                <!-- Add more course options as needed -->
                            </select>
                        </div>

                        <!-- Apply Button -->
                        <div class="py-2 px-4">
                            <button id="applyFilters" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>
                
                <button id="addResourceBtn" class="inline-flex items-center px-5 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add Resource
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

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">            <!-- Total Resources -->
            <div class="stats-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-blue-100">
                        <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Total Resources</h3>
                        <p class="text-3xl font-bold text-blue-600"><?php echo count($resources); ?></p>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="stats-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-indigo-100">
                        <i class="fas fa-file-pdf text-indigo-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Documents</h3>
                        <p class="text-3xl font-bold text-indigo-600"><?php echo count(array_filter($resources, function($r) { return $r['resource_type'] == 'document'; })); ?></p>
                    </div>
                </div>
            </div>

            <!-- Videos -->
            <div class="stats-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-red-100">
                        <i class="fas fa-video text-red-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">Videos</h3>
                        <p class="text-3xl font-bold text-red-600"><?php echo count(array_filter($resources, function($r) { return $r['resource_type'] == 'video'; })); ?></p>
                    </div>
                </div>
            </div>

            <!-- External Links -->
            <div class="stats-card bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center">
                    <div class="p-3 rounded-lg bg-green-100">
                        <i class="fas fa-link text-green-600 text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">External Links</h3>
                        <p class="text-3xl font-bold text-green-600"><?php echo count(array_filter($resources, function($r) { return $r['resource_type'] == 'link'; })); ?></p>
                    </div>
                </div>            </div>
        </div>

        <!-- Course Stats Cards -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Resources by Course</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-3">
                <?php
                $courses = ['BSCS', 'BSIT', 'BSCompE', 'BSEE', 'BSCE', 'BSME', 'BSBA', 'BSCrim'];
                $courseColors = [
                    'BSCS' => ['bg-blue-100', 'text-blue-600'],
                    'BSIT' => ['bg-indigo-100', 'text-indigo-600'],
                    'BSCompE' => ['bg-purple-100', 'text-purple-600'],
                    'BSEE' => ['bg-red-100', 'text-red-600'],
                    'BSCE' => ['bg-amber-100', 'text-amber-600'],
                    'BSME' => ['bg-green-100', 'text-green-600'],
                    'BSBA' => ['bg-teal-100', 'text-teal-600'],
                    'BSCrim' => ['bg-emerald-100', 'text-emerald-600'],
                ];
                
                foreach ($courses as $course) {
                    $count = count(array_filter($resources, function($r) use ($course) { 
                        return isset($r['course']) && $r['course'] == $course; 
                    }));
                    
                    $bgColor = $courseColors[$course][0] ?? 'bg-gray-100';
                    $textColor = $courseColors[$course][1] ?? 'text-gray-600';
                ?>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md">
                        <div class="p-3 flex items-center justify-between">
                            <div class="<?php echo $bgColor; ?> rounded-lg p-2">
                                <i class="fas fa-book-open <?php echo $textColor; ?>"></i>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500"><?php echo $course; ?></p>
                                <p class="text-lg font-bold text-gray-800"><?php echo $count; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Resource Form -->
        <div id="resourceForm" class="hidden bg-white rounded-xl shadow-lg mb-8 border border-gray-200">
            <div class="px-6 py-5 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h3 class="text-lg leading-6 font-semibold text-gray-900 flex items-center">
                        <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                            <i class="fas fa-cloud-upload-alt text-blue-600"></i>
                        </div>
                        Upload New Resource
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Fill out the form below to add a new resource for students
                    </p>
                </div>
                <button id="closeFormBtn" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="px-6 py-6">
                <form action="conn_back/resources_process.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Resource Title</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-heading text-gray-400"></i>
                                </div>
                                <input type="text" id="title" name="title" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg" placeholder="Enter resource title" required>
                            </div>
                        </div>
                        
                        <div>
                            <label for="resource_type" class="block text-sm font-medium text-gray-700 mb-1">Resource Type</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-list text-gray-400"></i>
                                </div>
                                <select id="resource_type" name="resource_type" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg appearance-none" required>
                                    <option value="">-- Select Type --</option>
                                    <option value="document">Document</option>
                                    <option value="video">Video</option>
                                    <option value="link">External Link</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                                <i class="fas fa-align-left text-gray-400"></i>
                            </div>
                            <textarea id="description" name="description" rows="4" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg" placeholder="Describe what this resource contains" required></textarea>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="year_level" class="block text-sm font-medium text-gray-700 mb-1">Year Level</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-user-graduate text-gray-400"></i>
                                </div>
                                <select id="year_level" name="year_level" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg appearance-none" required>
                                    <option value="">-- Select Year Level --</option>
                                    <option value="1st Year">1st Year</option>
                                    <option value="2nd Year">2nd Year</option>
                                    <option value="3rd Year">3rd Year</option>
                                    <option value="4th Year">4th Year</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label for="course" class="block text-sm font-medium text-gray-700 mb-1">Course Program</label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-graduation-cap text-gray-400"></i>
                                </div>
                                <select id="course" name="course" class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 pr-3 py-3 sm:text-sm border-gray-300 rounded-lg appearance-none" required>
                                    <option value="">-- Select Course --</option>
                                    <option value="BSIT">BSIT</option>
                                    <option value="BSCS">BSCS</option>
                                    <option value="ACT">ACT</option>
                                    <option value="BSCE">BSCE</option>
                                    <option value="BSME">BSME</option>
                                    <option value="BSEE">BSEE</option>
                                    <option value="BSCompE">BSCompE</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div id="file_upload_section">
                        <label for="resource_file" class="block text-sm font-medium text-gray-700 mb-1">Upload File</label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-xl hover:border-blue-400 transition-colors duration-200">
                            <div class="space-y-2 text-center">
                                <div class="mx-auto h-20 w-20 text-gray-400 flex items-center justify-center">
                                    <i class="fas fa-cloud-upload-alt text-5xl"></i>
                                </div>
                                <div class="flex flex-col text-sm text-gray-600">
                                    <label for="resource_file" class="relative cursor-pointer py-2 px-4 bg-blue-50 rounded-lg text-blue-600 font-medium hover:bg-blue-100 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500 mx-auto mb-2 transition-colors">
                                        <span>Upload a file</span>
                                        <input id="resource_file" name="resource_file" type="file" class="sr-only">
                                    </label>
                                    <p class="text-xs text-gray-500">or drag and drop your file here</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOCX, MP4, etc. up to 10MB</p>
                            </div>
                        </div>
                        <p id="selected_file" class="mt-2 text-sm text-gray-500"></p>
                    </div>
                    
                    <div id="link_section" class="hidden">
                        <label for="link_url" class="block text-sm font-medium text-gray-700 mb-1">External URL</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <span class="inline-flex items-center px-4 py-2 rounded-l-lg border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                                <i class="fas fa-link"></i>
                            </span>
                            <input type="url" id="link_url" name="link_url" class="flex-1 min-w-0 block w-full px-3 py-3 rounded-none rounded-r-lg focus:ring-blue-500 focus:border-blue-500 sm:text-sm border-gray-300" placeholder="https://example.com/resource">
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 pt-5 border-t border-gray-200">
                        <button type="button" id="cancelBtn" class="px-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" name="add_resource" class="inline-flex justify-center items-center py-2.5 px-5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-upload mr-2"></i> Upload Resource
                        </button>
                    </div>
                </form>
            </div>
        </div>        <!-- Resources Table -->
        <div class="bg-white shadow-lg overflow-hidden sm:rounded-xl border border-gray-200 animate-fade-in">
            <div class="px-6 py-5 border-b border-gray-200 bg-gradient-to-r from-blue-600 to-indigo-600 sm:px-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-white flex items-center">
                        <i class="fas fa-book-reader mr-2"></i>
                        Lab Resources
                    </h3>
                    <span id="resourceCount" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white text-blue-600">
                        <?php echo count($resources); ?> total
                    </span>
                </div>
            </div>
            
            <?php if (empty($resources)): ?>
                <div class="px-4 py-16 sm:px-6 text-center">
                    <div class="flex flex-col items-center">
                        <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-blue-100">
                            <i class="fas fa-folder-open text-blue-400 text-3xl"></i>
                        </div>
                        <h3 class="mt-5 text-xl font-medium text-gray-900">No resources yet</h3>
                        <p class="mt-3 text-sm text-gray-500 max-w-md mx-auto">
                            You haven't added any learning resources yet. Get started by clicking the "Add Resource" button above.
                        </p>
                        <div class="mt-6">
                            <button id="emptyStateBtn" type="button" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                                <i class="fas fa-plus mr-2"></i>
                                Add Your First Resource
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Resource Details
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Year Level
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Course
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Uploaded
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($resources as $resource): ?>
                                <tr class="resource-row hover:bg-gray-50 transition-colors" 
                                    data-year="<?php echo $resource['year_level']; ?>" 
                                    data-course="<?php echo $resource['course'] ?? ''; ?>">
                                    <td class="px-6 py-5">
                                        <div class="flex items-center">
                                            <?php
                                                $icon = 'fa-file-alt text-blue-500';
                                                $bgColor = 'bg-blue-100';
                                                
                                                if ($resource['resource_type'] == 'video') {
                                                    $icon = 'fa-video text-red-500';
                                                    $bgColor = 'bg-red-100';
                                                } elseif ($resource['resource_type'] == 'link') {
                                                    $icon = 'fa-link text-green-500';
                                                    $bgColor = 'bg-green-100';
                                                }
                                            ?>
                                            <div class="flex-shrink-0 h-12 w-12 rounded-lg <?php echo $bgColor; ?> flex items-center justify-center shadow-sm">
                                                <i class="fas <?php echo $icon; ?> text-xl"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-base font-semibold text-gray-900">
                                                    <?php echo htmlspecialchars($resource['title']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500 max-w-md truncate mt-1">
                                                    <?php echo htmlspecialchars($resource['description']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                                if ($resource['resource_type'] == 'document') echo 'bg-blue-100 text-blue-800';
                                                elseif ($resource['resource_type'] == 'video') echo 'bg-red-100 text-red-800';
                                                else echo 'bg-green-100 text-green-800';
                                            ?>">
                                            <?php echo ucfirst($resource['resource_type']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?php echo $resource['year_level']; ?></div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                            <?php echo isset($resource['course']) ? $resource['course'] : 'N/A'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap">
                                        <div class="text-sm text-gray-900"><?php echo date('M d, Y', strtotime($resource['upload_date'])); ?></div>
                                        <div class="text-xs text-gray-500 mt-1">by <?php echo htmlspecialchars($resource['uploaded_by']); ?></div>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-4">
                                            <?php if (!empty($resource['file_path'])): ?>
                                                <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" target="_blank" class="text-blue-600 hover:text-blue-900 transition-colors flex items-center" title="View File">
                                                    <span class="w-8 h-8 flex items-center justify-center bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                                                        <i class="fas fa-eye"></i>
                                                    </span>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($resource['link_url'])): ?>
                                                <a href="<?php echo htmlspecialchars($resource['link_url']); ?>" target="_blank" class="text-green-600 hover:text-green-900 transition-colors flex items-center" title="Open Link">
                                                    <span class="w-8 h-8 flex items-center justify-center bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                                                        <i class="fas fa-external-link-alt"></i>
                                                    </span>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <button class="text-red-600 hover:text-red-900 transition-colors flex items-center delete-resource" data-id="<?php echo $resource['id']; ?>" title="Delete Resource">
                                                <span class="w-8 h-8 flex items-center justify-center bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                                    <i class="fas fa-trash-alt"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle form visibility
            const addResourceBtn = document.getElementById('addResourceBtn');
            const emptyStateBtn = document.getElementById('emptyStateBtn');
            const resourceForm = document.getElementById('resourceForm');
            const cancelBtn = document.getElementById('cancelBtn');
            const closeFormBtn = document.getElementById('closeFormBtn');
            
            // Filter dropdown
            const filterBtn = document.getElementById('filterBtn');
            const filterMenu = document.getElementById('filterMenu');
            const yearFilter = document.getElementById('yearFilter');
            const courseFilter = document.getElementById('courseFilter');
            const applyFilters = document.getElementById('applyFilters');
            const resourceRows = document.querySelectorAll('.resource-row');
            const resourceCount = document.getElementById('resourceCount');
            
            function showAddResourceForm() {
                resourceForm.classList.remove('hidden');
                // Smooth scroll to form
                resourceForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            function hideAddResourceForm() {
                resourceForm.classList.add('hidden');
            }
            
            if (addResourceBtn) {
                addResourceBtn.addEventListener('click', showAddResourceForm);
            }
            
            if (emptyStateBtn) {
                emptyStateBtn.addEventListener('click', showAddResourceForm);
            }
            
            if (cancelBtn) {
                cancelBtn.addEventListener('click', hideAddResourceForm);
            }
            
            if (closeFormBtn) {
                closeFormBtn.addEventListener('click', hideAddResourceForm);
            }
            
            // Filter dropdown toggle
            if (filterBtn) {
                filterBtn.addEventListener('click', function() {
                    filterMenu.classList.toggle('hidden');
                });
                
                // Close filter dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!filterBtn.contains(event.target) && !filterMenu.contains(event.target)) {
                        filterMenu.classList.add('hidden');
                    }
                });
                
                // Apply filters
                if (applyFilters) {
                    applyFilters.addEventListener('click', function() {
                        const selectedYear = yearFilter.value;
                        const selectedCourse = courseFilter.value;
                        
                        // Filter table rows
                        let visibleCount = 0;
                        
                        resourceRows.forEach(row => {
                            const yearLevel = row.getAttribute('data-year');
                            const courseName = row.getAttribute('data-course');
                            
                            const yearMatch = selectedYear === 'all' || yearLevel === selectedYear;
                            const courseMatch = selectedCourse === 'all' || courseName === selectedCourse;
                            
                            if (yearMatch && courseMatch) {
                                row.classList.remove('hidden');
                                visibleCount++;
                            } else {
                                row.classList.add('hidden');
                            }
                        });
                        
                        // Update count badge
                        resourceCount.textContent = visibleCount + ' of ' + resourceRows.length;
                        
                        // Close dropdown
                        filterMenu.classList.add('hidden');
                    });
                }
            }
            
            // Handle resource type change
            const resourceType = document.getElementById('resource_type');
            const fileUploadSection = document.getElementById('file_upload_section');
            const linkSection = document.getElementById('link_section');
            
            if (resourceType) {
                resourceType.addEventListener('change', function() {
                    if (this.value === 'link') {
                        fileUploadSection.classList.add('hidden');
                        linkSection.classList.remove('hidden');
                    } else {
                        fileUploadSection.classList.remove('hidden');
                        linkSection.classList.add('hidden');
                    }
                });
            }
            
            // Display selected filename
            const resourceFile = document.getElementById('resource_file');
            const selectedFile = document.getElementById('selected_file');
            
            if (resourceFile && selectedFile) {
                resourceFile.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        selectedFile.innerHTML = '<div class="flex items-center p-2 bg-blue-50 rounded-lg mt-2"><i class="fas fa-check-circle text-blue-500 mr-2"></i><span class="font-medium">Selected file:</span>&nbsp;' + 
                            '<span class="text-blue-600">' + this.files[0].name + '</span></div>';
                    } else {
                        selectedFile.textContent = '';
                    }
                });
            }
            
            // Delete confirmation
            const deleteButtons = document.querySelectorAll('.delete-resource');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const resourceId = this.getAttribute('data-id');
                    
                    Swal.fire({
                        title: 'Delete Resource?',
                        text: "This resource will no longer be available to students. This action cannot be undone.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Yes, delete it',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'rounded-xl',
                            confirmButton: 'px-4 py-2 rounded-lg',
                            cancelButton: 'px-4 py-2 rounded-lg'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `conn_back/resources_process.php?delete=${resourceId}`;
                        }
                    });
                });
            });
            
            // File upload drag and drop
            const dropZone = document.querySelector('#file_upload_section .border-dashed');
            
            if (dropZone) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        this.classList.add('border-blue-400', 'bg-blue-50');
                    });
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    dropZone.addEventListener(eventName, function(e) {
                        e.preventDefault();
                        this.classList.remove('border-blue-400', 'bg-blue-50');
                    });
                });
                
                dropZone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    if (e.dataTransfer.files.length) {
                        resourceFile.files = e.dataTransfer.files;
                        const event = new Event('change');
                        resourceFile.dispatchEvent(event);
                    }
                });
            }
        });
    </script>
</body>
</html>