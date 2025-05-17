<?php
session_start();
require_once 'database.php';

// Fetch sit-in records
$query = "SELECT s.*, u.FIRSTNAME, u.LASTNAME, u.COURSE, u.YEAR 
          FROM sit_in_sessions s
          JOIN users u ON s.student_id = u.ID_NUMBER
          ORDER BY s.start_time DESC";

$stmt = $conn->prepare($query);
$stmt->execute();
$sessions = $stmt->get_result();

// Fetch approved reservations for today
$reservations_query = "SELECT r.*, u.FIRSTNAME, u.LASTNAME, u.COURSE, u.YEAR 
                      FROM reservations r
                      JOIN users u ON r.student_id = u.ID_NUMBER
                      WHERE r.status = 'approved' 
                      ORDER BY r.time_in ASC";

$stmt_reservations = $conn->prepare($reservations_query);
$stmt_reservations->execute();
$reservations = $stmt_reservations->get_result();

// Process data for sit-in charts
$purpose_counts = [];
$lab_counts = [];
while ($row = $sessions->fetch_assoc()) {
    $purpose = $row['purpose'];
    $lab = $row['lab_room'];
    
    $purpose_counts[$purpose] = isset($purpose_counts[$purpose]) ? $purpose_counts[$purpose] + 1 : 1;
    $lab_counts[$lab] = isset($lab_counts[$lab]) ? $lab_counts[$lab] + 1 : 1;
}

// Process data for reservation charts
$reservation_purpose_counts = [];
$reservation_lab_counts = [];
$reservations_data = $reservations->fetch_all(MYSQLI_ASSOC);
foreach ($reservations_data as $row) {
    $purpose = $row['purpose'];
    $lab = $row['lab_room'];
    
    $reservation_purpose_counts[$purpose] = isset($reservation_purpose_counts[$purpose]) ? $reservation_purpose_counts[$purpose] + 1 : 1;
    $reservation_lab_counts[$lab] = isset($reservation_lab_counts[$lab]) ? $reservation_lab_counts[$lab] + 1 : 1;
}

// Enhanced default values for empty charts
if (empty($purpose_counts)) $purpose_counts = ['No Records Available' => 1];
if (empty($lab_counts)) $lab_counts = ['No Records Available' => 1];
if (empty($reservation_purpose_counts)) $reservation_purpose_counts = ['No Records Available' => 1];
if (empty($reservation_lab_counts)) $reservation_lab_counts = ['No Records Available' => 1];
?>

<!DOCTYPE html>
<html lang="en">
<head>    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Sit-in Records</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <!-- Tab Navigation -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button onclick="switchTab('records')" id="records-tab" class="tab-button px-6 py-3 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-gray-300">
                        Sit-in Records
                    </button>
                    <button onclick="switchTab('direct')" id="direct-tab" class="tab-button px-6 py-3 text-sm font-medium rounded-t-lg border-b-2 border-transparent hover:border-gray-300">
                        Direct Sit-in
                    </button>
                </nav>
            </div>
        </div>

        <!-- Records Tab Content -->
        <div id="records-content" class="tab-content">
            <h2 class="text-2xl font-bold text-center mb-6">Sit-in Records</h2>
            <div class="overflow-x-auto mb-6">
                <div class="max-h-[400px] overflow-y-auto">
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
                            $sessions->data_seek(0);
                            if ($sessions->num_rows > 0):
                                while ($session = $sessions->fetch_assoc()): ?>
                                    <tr class="bg-white hover:bg-gray-50">
                                        <td class="border px-4 py-2"><?php echo $session['student_id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $session['FIRSTNAME'] . ' ' . $session['LASTNAME']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $session['purpose']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $session['lab_room']; ?></td>
                                        <td class="border px-4 py-2"><?php echo date('h:i:s A', strtotime($session['start_time'])); ?></td>
                                        <td class="border px-4 py-2"><?php echo $session['end_time'] ? date('h:i:s A', strtotime($session['end_time'])) : '-'; ?></td>
                                        <td class="border px-4 py-2"><?php echo $session['end_time'] ? date('Y-m-d', strtotime($session['end_time'])) : '-'; ?></td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-gray-500">No records found for the selected filters.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div> 
            </div>
            <!-- Sit-in Records Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <canvas id="purposeChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <canvas id="labChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Direct Sit-in Tab Content -->
        <div id="direct-content" class="tab-content hidden">
            <h2 class="text-2xl font-bold text-center mb-6">Direct Sit-in</h2>
            <div class="overflow-x-auto mb-6">
                <div class="max-h-[400px] overflow-y-auto">
                    <table class="min-w-full table-auto border-collapse border border-gray-300">
                        <thead class="bg-gray-200 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 border">ID Number</th>
                                <th class="px-4 py-2 border">Name</th>
                                <th class="px-4 py-2 border">Course</th>
                                <th class="px-4 py-2 border">Year</th>
                                <th class="px-4 py-2 border">Lab Room</th>
                                <th class="px-4 py-2 border">PC Number</th>
                                <th class="px-4 py-2 border">Purpose</th>
                                <th class="px-4 py-2 border">Time In</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reservations_data) > 0):
                                foreach ($reservations_data as $reservation): ?>
                                    <tr class="bg-white hover:bg-gray-50">
                                        <td class="border px-4 py-2"><?php echo $reservation['student_id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['FIRSTNAME'] . ' ' . $reservation['LASTNAME']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['COURSE']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['YEAR']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['lab_room']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['pc_number']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['purpose']; ?></td>
                                        <td class="border px-4 py-2"><?php echo date('h:i A', strtotime($reservation['time_in'])); ?></td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-gray-500">No approved reservations found for today.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Direct Sit-in Charts -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                <div class="bg-white p-4 rounded-lg shadow">
                    <canvas id="reservationPurposeChart"></canvas>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <canvas id="reservationLabChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <style>
        .tab-button.active {
            border-bottom-color: #2563eb;
            color: #2563eb;
        }
        .tab-content.hidden {
            display: none;
        }
    </style>

    <script>
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.querySelectorAll('.tab-button').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(`${tabName}-content`).classList.remove('hidden');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            switchTab('records');

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

            // Sit-in Records Charts
            new Chart(document.getElementById('purposeChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($purpose_counts)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($purpose_counts)); ?>,
                        backgroundColor: ['#4CAF50', '#FF5733', '#FFC300', '#36A2EB', '#C70039']
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        title: {
                            ...chartOptions.plugins.title,
                            text: 'Purpose Distribution - Sit-in Records'
                        }
                    }
                }
            });

            new Chart(document.getElementById('labChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($lab_counts)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($lab_counts)); ?>,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        title: {
                            ...chartOptions.plugins.title,
                            text: 'Laboratory Usage - Sit-in Records'
                        }
                    }
                }
            });

            // Direct Sit-in Charts
            new Chart(document.getElementById('reservationPurposeChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($reservation_purpose_counts)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($reservation_purpose_counts)); ?>,
                        backgroundColor: ['#4CAF50', '#FF5733', '#FFC300', '#36A2EB', '#C70039']
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        title: {
                            ...chartOptions.plugins.title,
                            text: 'Purpose Distribution - Direct Sit-in'
                        }
                    }
                }
            });

            new Chart(document.getElementById('reservationLabChart'), {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($reservation_lab_counts)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($reservation_lab_counts)); ?>,
                        backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        title: {
                            ...chartOptions.plugins.title,
                            text: 'Laboratory Usage - Direct Sit-in'
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$stmt_reservations->close();
$conn->close();
?>
