<?php
session_start();
require_once 'database.php';

// Handle filters
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

// Build the query based on available dates
if ($end_date && !$start_date) {
    $query = "SELECT s.*, u.FIRSTNAME, u.LASTNAME, u.COURSE, u.YEAR 
              FROM sit_in_sessions s
              JOIN users u ON s.student_id = u.ID_NUMBER
              WHERE DATE(s.start_time) <= ?
              ORDER BY s.id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $end_date);
} elseif ($start_date && $end_date) {
    $query = "SELECT s.*, u.FIRSTNAME, u.LASTNAME, u.COURSE, u.YEAR 
              FROM sit_in_sessions s
              JOIN users u ON s.student_id = u.ID_NUMBER
              WHERE DATE(s.start_time) BETWEEN ? AND ?
              ORDER BY s.id ASC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
} else {
    $query = "SELECT s.*, u.FIRSTNAME, u.LASTNAME, u.COURSE, u.YEAR 
              FROM sit_in_sessions s
              JOIN users u ON s.student_id = u.ID_NUMBER
              ORDER BY s.id ASC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$sessions = $stmt->get_result();

// Process data for charts
$purpose_counts = [];
$lab_counts = [];
while ($row = $sessions->fetch_assoc()) {
    $purpose = $row['purpose'];
    $lab = $row['lab_room'];
    
    $purpose_counts[$purpose] = isset($purpose_counts[$purpose]) ? $purpose_counts[$purpose] + 1 : 1;
    $lab_counts[$lab] = isset($lab_counts[$lab]) ? $lab_counts[$lab] + 1 : 1;
}

// Enhanced default values for empty charts
if (empty($purpose_counts)) {
    $purpose_counts = ['No Records Available' => 1];
}
if (empty($lab_counts)) {
    $lab_counts = ['No Records Available' => 1];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-in Records</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-center mb-6">Sit-in Records</h2>

        <!-- Chart Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="flex justify-center items-center">
                <div style="width: 400px; height: 400px;">
                    <canvas id="purposeChart"></canvas>
                </div>
            </div>
            <div class="flex justify-center items-center">
                <div style="width: 400px; height: 400px;">
                    <canvas id="labChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form method="POST" class="flex gap-4">
            <input type="date" name="start_date" class="border rounded px-3 py-2" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
            <input type="date" name="end_date" class="border rounded px-3 py-2" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
            <a href="view-sit-in.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Reset</a>
        </form>

        <!-- Table -->
        <div class="overflow-x-auto">
            <div class="max-h-[600px] overflow-y-auto"> 
                <table class="min-w-full table-auto border-collapse border border-gray-300">
                    <thead class="bg-gray-200 sticky top-0"> 
                        <tr>
                            <th class="px-4 py-2 border">ID Number</th>
                            <th class="px-4 py-2 border">Name</th>
                            <th class="px-4 py-2 border">Purpose</th>
                            <th class="px-4 py-2 border">Lab</th>
                            <th class="px-4 py-2 border">Time In</th>
                            <th class="px-4 py-2 border">Time Out</th>
                            <th class="px-4 py-2 border">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sessions->data_seek(0); // Reset pointer
                        while ($session = $sessions->fetch_assoc()): ?>
                            <tr class="bg-white">
                                <td class="border px-4 py-2"><?php echo $session['student_id']; ?></td>
                                <td class="border px-4 py-2"><?php echo $session['FIRSTNAME'] . ' ' . $session['LASTNAME']; ?></td>
                                <td class="border px-4 py-2"><?php echo $session['purpose']; ?></td>
                                <td class="border px-4 py-2"><?php echo $session['lab_room']; ?></td>
                                <td class="border px-4 py-2"><?php echo date('h:i:s A', strtotime($session['start_time'])); ?></td>
                                <td class="border px-4 py-2"><?php echo $session['end_time'] ? date('h:i:s A', strtotime($session['end_time'])) : '-'; ?></td>
                                <td class="border px-4 py-2"><?php echo date('Y-m-d', strtotime($session['start_time'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div> 
        </div>
    </div>

    <!-- JavaScript for Charts -->
    <script>
        const purposeData = {
            labels: <?php echo json_encode(array_keys($purpose_counts)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($purpose_counts)); ?>,
                backgroundColor: Object.keys(<?php echo json_encode($purpose_counts); ?>)[0] === 'No Records Available' 
                    ? ['#d1d5db'] 
                    : ['#4CAF50', '#FF5733', '#FFC300', '#36A2EB', '#C70039']
            }]
        };

        const labData = {
            labels: <?php echo json_encode(array_keys($lab_counts)); ?>,
            datasets: [{
                data: <?php echo json_encode(array_values($lab_counts)); ?>,
                backgroundColor: Object.keys(<?php echo json_encode($lab_counts); ?>)[0] === 'No Records Available' 
                    ? ['#d1d5db'] 
                    : ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        };

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 15,
                        font: { size: 12 }
                    }
                },
                title: {
                    display: true,
                    font: {
                        size: 16,
                        weight: 'bold'
                    },
                    padding: 20
                }
            }
        };

        new Chart(document.getElementById('purposeChart'), {
            type: 'pie',
            data: purposeData,
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                }
            }
        });

        new Chart(document.getElementById('labChart'), {
            type: 'pie',
            data: labData,
            options: {
                ...chartOptions,
                plugins: {
                    ...chartOptions.plugins,
                }
            }
        });
        
    </script>
</body>
</html>
