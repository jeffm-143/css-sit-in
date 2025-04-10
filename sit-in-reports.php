<?php
session_start();
require_once 'database.php';

require_once __DIR__ . '/TCPDF-main/tcpdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_date'], $_POST['end_date'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $query = "
        SELECT s.*, u.FIRSTNAME, u.LASTNAME 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE s.status = 'completed' AND DATE(s.start_time) BETWEEN ? AND ?
        ORDER BY s.end_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $start_date, $end_date);
    $stmt->execute();
    $records = $stmt->get_result();
} else {
    $query = "
        SELECT s.*, u.FIRSTNAME, u.LASTNAME 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE s.status = 'completed'
        ORDER BY s.end_time ASC";
    $records = $conn->query($query);
}

if (isset($_GET['export'])) {
    $exportType = $_GET['export'];

    // Fetch data again for export
    $query = "
        SELECT s.*, u.FIRSTNAME, u.LASTNAME 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE s.status = 'completed'
        ORDER BY s.end_time ASC";
    $records = $conn->query($query);

    if ($exportType === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sit_in_reports.csv"');

        $output = fopen('php://output', 'w');

        // Add title
        fputcsv($output, ['CCS SIT-IN REPORTS']);
        fputcsv($output, []); // Empty row for spacing

        // Add headers
        fputcsv($output, ['ID Number', 'Name', 'Purpose', 'Laboratory', 'Login', 'Logout', 'Date']);

        // Add data
        while ($row = $records->fetch_assoc()) {
            fputcsv($output, [
                $row['student_id'],
                $row['FIRSTNAME'] . ' ' . $row['LASTNAME'],
                $row['purpose'],
                $row['lab_room'],
                date("h:i:sa", strtotime($row['start_time'])),
                date("h:i:sa", strtotime($row['end_time'])),
                date("Y-m-d", strtotime($row['start_time']))
            ]);
        }

        fclose($output);
        exit();
    } elseif ($exportType === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="sit_in_reports.xls"');

        echo "<table style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif;'>";
        echo "<tr><td colspan='7' style='text-align: center; font-weight: bold; font-size: 16px;'>CCS SIT-IN REPORTS</td></tr>";
        echo "<tr style='background-color: #f4f4f4; text-align: center;'>
                <th style='border: 1px solid #ddd; padding: 8px;'>ID Number</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>Name</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>Purpose</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>Laboratory</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>Login</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>Logout</th>
                <th style='border: 1px solid #ddd; padding: 8px;'>Date</th>
              </tr>";

        while ($row = $records->fetch_assoc()) {
            echo "<tr style='text-align: center;'>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['student_id']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['FIRSTNAME']} {$row['LASTNAME']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['purpose']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>{$row['lab_room']}</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . date("h:i:sa", strtotime($row['start_time'])) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . date("h:i:sa", strtotime($row['end_time'])) . "</td>";
            echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . date("Y-m-d", strtotime($row['start_time'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        exit();
    } elseif ($exportType === 'pdf') {
        require_once __DIR__ . '/TCPDF-main/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'LETTER', true, 'UTF-8', false); // Set page size to Letter
        $pdf->SetMargins(15, 10, 15); // Adjust margins for proper layout
        $pdf->SetAutoPageBreak(true, 10); // Enable auto page break with bottom margin
        $pdf->AddPage();

        $pdf->Image('images/uc.png', 15, 10, 30, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
        $pdf->Image('images/css-new.png', 175, 10, 20, 0, '', '', '', false, 300, '', false, false, 0, false, false, false);
        $pdf->Ln(20); // Adjust spacing after the images

        $pdf->SetFont('helvetica', 'B', 16); // Set font to bold and size to 16 for the title
        $pdf->Cell(0, 10, 'CCS SIT-IN REPORTS', 0, 1, 'C'); // Center the title
        $pdf->Ln(5); // Adjust spacing after the title

        $pdf->SetFont('helvetica', '', 12); // Set font to normal for table headers and data

        $html = "<table cellpadding='5' cellspacing='0' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>
        <thead style='background-color: #f4f4f4;'>
        <tr>
        <th>ID Number</th>
        <th>Name</th>
        <th>Purpose</th>
        <th>Laboratory</th>
        <th>Login</th>
        <th>Logout</th>
        <th>Date</th>
        </tr>
        </thead>
        <tbody>";

        while ($row = $records->fetch_assoc()) {
            $html .= "<tr>";
            $html .= "<td>{$row['student_id']}</td>";
            $html .= "<td>{$row['FIRSTNAME']} {$row['LASTNAME']}</td>";
            $html .= "<td>{$row['purpose']}</td>";
            $html .= "<td>{$row['lab_room']}</td>";
            $html .= "<td>" . date("h:i:sa", strtotime($row['start_time'])) . "</td>";
            $html .= "<td>" . date("h:i:sa", strtotime($row['end_time'])) . "</td>";
            $html .= "<td>" . date("Y-m-d", strtotime($row['start_time'])) . "</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('sit_in_reports.pdf', 'D');
        exit();
    } elseif ($exportType === 'print') {
        echo "<div style='text-align: center; font-family: Arial, sans-serif; font-size: 14px;'>
                <img src='images/uc.png' height='100' style='margin-bottom: 10px; float: left;'>
                <img src='images/css-new.png' height='' style='margin-bottom: 10px; float: right;'>
                <h1 style='clear: both;'>CCS SIT-IN REPORTS</h1>
              </div>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;'>";
        echo "<thead style='background-color: #f4f4f4;'>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Purpose</th>
                    <th>Laboratory</th>
                    <th>Login</th>
                    <th>Logout</th>
                    <th>Date</th>
                </tr>
              </thead>";

        while ($row = $records->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['student_id']}</td>";
            echo "<td>{$row['FIRSTNAME']} {$row['LASTNAME']}</td>";
            echo "<td>{$row['purpose']}</td>";
            echo "<td>{$row['lab_room']}</td>";
            echo "<td>" . date("h:i:sa", strtotime($row['start_time'])) . "</td>";
            echo "<td>" . date("h:i:sa", strtotime($row['end_time'])) . "</td>";
            echo "<td>" . date("Y-m-d", strtotime($row['start_time'])) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<script>window.print();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function printReport() {
            const printContent = document.getElementById('reportTable').outerHTML;
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>Print Report</title>
                    <style>
                        table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f4f4f4; }
                    </style>
                </head>
                <body>
                    <div style="text-align: center; font-family: Arial, sans-serif; font-size: 14px;">
                        <img src="images/uc.png" height="50" style="margin-bottom: 10px; float: left;">
                        <img src="images/css-new.png" height="50" style="margin-bottom: 10px; float: right;">
                        <h1 style="clear: both;">CCS SIT-IN REPORTS</h1>
                    </div>
                    ${printContent}
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    </script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-center mb-6">Generate Reports</h2>

        <div class="flex justify-between items-center mb-4">
            <!-- Search & Filter Section -->
            <form method="POST" class="flex gap-4">
                <input type="date" name="start_date" class="border rounded px-3 py-2" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                <input type="date" name="end_date" class="border rounded px-3 py-2" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
                <a href="sit-in-reports.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Reset</a>
            </form>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <a href="?export=excel" class="bg-green-600 text-white px-3 py-2 rounded">Excel</a>
                <a href="?export=pdf" class="bg-red-700 text-white px-3 py-2 rounded">PDF</a>
                <a href="#" onclick="printReport()" class="bg-black text-white px-3 py-2 rounded">Print</a>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white shadow-md rounded-lg p-4">
            <div class="max-h-[600px] overflow-y-auto"> <!-- Add this wrapper div -->
                <div id="reportTable"> <!-- Wrap the table in a div with an ID for printing -->
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
    </div>
</body>
</html>
