<?php
session_start();
require_once 'database.php';

// Fetch only completed sit-in records
$query = "
    SELECT s.*, u.FIRSTNAME, u.LASTNAME 
    FROM sit_in_sessions s
    JOIN users u ON s.student_id = u.ID_NUMBER
    WHERE s.status = 'completed'  /* Add this condition */
    ORDER BY s.end_time ASC"; // Sort by latest completed session
$records = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-center mb-6">Generate Reports</h2>

        <!-- Search & Filter Section -->
        <div class="flex gap-4 mb-4">
            <input type="date" name="start_date" class="border rounded px-3 py-2">
            <input type="date" name="end_date" class="border rounded px-3 py-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
            <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Reset</button>
        </div>

        <!-- Export Buttons -->
        <div class="flex gap-2 mb-4">
            <button class="bg-gray-500 text-white px-3 py-2 rounded">CSV</button>
            <button class="bg-green-600 text-white px-3 py-2 rounded">Excel</button>
            <button class="bg-red-700 text-white px-3 py-2 rounded">PDF</button>
            <button class="bg-black text-white px-3 py-2 rounded">Print</button>
        </div>

        <!-- Table -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <div class="max-h-[600px] overflow-y-auto"> <!-- Add this wrapper div -->
                <table class="w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-200 sticky top-0"> <!-- Add sticky header -->
                        <tr>
                            <th class="border px-4 py-2">ID Number</th>
                            <th class="border px-4 py-2">Name</th>
                            <th class="border px-4 py-2">Purpose</th>
                            <th class="border px-4 py-2">Laboratory</th>
                            <th class="border px-4 py-2">Login</th>
                            <th class="border px-4 py-2">Logout</th>
                            <th class="border px-4 py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $records->fetch_assoc()): ?>
                        <tr class="text-center">
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['student_id']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['purpose']); ?></td>
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['lab_room']); ?></td>
                            <td class="border px-4 py-2"><?php echo date("h:i:sa", strtotime($row['start_time'])); ?></td>
                            <td class="border px-4 py-2"><?php echo date("h:i:sa", strtotime($row['end_time'])); ?></td>
                            <td class="border px-4 py-2"><?php echo date("Y-m-d", strtotime($row['start_time'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div> <!-- Close wrapper div -->
        </div>
    </div>
</body>
</html>
