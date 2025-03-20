<?php
session_start();
require_once 'database.php';

$message = '';
$searchResults = [];

// Define available lab rooms
$available_labs = [
    '524', '526', '528', '530', '542', '547', 'MAC'
];

// Simplified purpose list
$purposes = [
    'C',
    'C#',
    'Python',
    'PHP',
    'Java',
    'ASP.Net',
];

if (isset($_POST['search'])) {
    $student_id = $_POST['student_id'];
    
    // Check if student already has an active session
    $active_check = $conn->prepare("
        SELECT id FROM sit_in_sessions 
        WHERE student_id = ? AND status = 'active'
    ");
    $active_check->bind_param("s", $student_id);
    $active_check->execute();
    $active_result = $active_check->get_result();
    
    if ($active_result->num_rows > 0) {
        $message = "Student already has an active sit-in session.";
    } else {
        // Search for student
        $stmt = $conn->prepare("
            SELECT * FROM users 
            WHERE ID_NUMBER = ? AND user_type = 'student'
        ");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $searchResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (empty($searchResults)) {
            $message = "No student found with ID: " . htmlspecialchars($student_id);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Students</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6">Search Student for Sit-in</h2>
            
            <!-- Search Form -->
            <form method="POST" class="mb-6">
                <div class="flex gap-4">
                    <input type="text" name="student_id" 
                           class="flex-1 border rounded-lg px-4 py-2" 
                           placeholder="Enter Student ID"
                           required>
                    <button type="submit" name="search" 
                            class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                        Search
                    </button>
                </div>
            </form>

            <?php if ($message): ?>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Search Results -->
            <?php if (!empty($searchResults)): ?>
                <?php foreach ($searchResults as $student): ?>
                    <div class="bg-white border rounded-lg shadow-sm p-6 mt-4">
                        <!-- Student Info Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-xl font-bold text-gray-800">
                                    <?php echo htmlspecialchars($student['FIRSTNAME'] . ' ' . $student['LASTNAME']); ?>
                                </h3>
                                <div class="mt-1 space-y-1">
                                    <p class="text-gray-600">
                                        <span class="font-medium">ID Number:</span> 
                                        <?php echo htmlspecialchars($student['ID_NUMBER']); ?>
                                    </p>
                                    <p class="text-gray-600">
                                        <span class="font-medium">Course:</span> 
                                        <?php echo htmlspecialchars($student['COURSE']); ?>
                                    </p>
                                    <p class="text-gray-600">
                                        <span class="font-medium">Year Level:</span> 
                                        <?php echo htmlspecialchars($student['YEAR']); ?>
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Session Status -->
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <div class="text-center">
                                    <p class="text-sm text-blue-800 font-medium">Remaining Sessions</p>
                                    <p class="text-2xl font-bold text-blue-900 mt-1">
                                        <?php echo htmlspecialchars($student['SESSION']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Sit-in Form -->
                        <form action="process-sit-in.php" method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <input type="hidden" name="student_id" value="<?php echo $student['ID_NUMBER']; ?>">
                            
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Laboratory Room</label>
                                <select name="lab_room" id="lab_room" 
                                        class="w-full border rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Room Number</option>
                                    <?php foreach ($available_labs as $lab): ?>
                                        <option value="<?php echo $lab; ?>">Room <?php echo $lab; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Purpose</label>
                                <select name="purpose" 
                                        class="w-full border rounded-lg px-4 py-2.5 bg-white focus:ring-2 focus:ring-blue-500" required>
                                    <option value="">Select Purpose</option>
                                    <?php foreach ($purposes as $purpose): ?>
                                        <option value="<?php echo $purpose; ?>"><?php echo $purpose; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <button type="submit" 
                                        class="w-full bg-green-600 text-white px-6 py-3 rounded-lg font-medium hover:bg-green-700 
                                               transition-colors duration-200 flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path>
                                    </svg>
                                    Start Sit-in Session
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Simplified JavaScript -->
                    <script>
                        const labData = <?php echo json_encode($available_labs); ?>;

                        function updateLabRooms(labType) {
                            const roomSelect = document.getElementById('lab_room');
                            roomSelect.innerHTML = '<option value="">Select Room Number</option>';
                            
                            if (labType && labData[labType]) {
                                labData[labType].rooms.forEach(room => {
                                    roomSelect.innerHTML += `<option value="${room}">${room}</option>`;
                                });
                            }
                        }
                    </script>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
