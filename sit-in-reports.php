<?php
session_start();
require_once 'database.php';

require_once __DIR__ . '/TCPDF-main/tcpdf.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Handle the number of entries to display
$entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10; // Default to 10 entries
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Default to page 1
$offset = ($page - 1) * $entries_per_page;

// Handle search dates
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

// Build the query based on available dates
if ($end_date && !$start_date) {
    $query = "
        SELECT s.*, u.FIRSTNAME, u.LASTNAME 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE s.status = 'completed' AND DATE(s.start_time) <= ?
        ORDER BY s.end_time ASC
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sii', $end_date, $entries_per_page, $offset);
} elseif ($start_date && $end_date) {
    $query = "
        SELECT s.*, u.FIRSTNAME, u.LASTNAME 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE s.status = 'completed' AND DATE(s.start_time) BETWEEN ? AND ?
        ORDER BY s.end_time ASC
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssii', $start_date, $end_date, $entries_per_page, $offset);
} else {
    $query = "
        SELECT s.*, u.FIRSTNAME, u.LASTNAME 
        FROM sit_in_sessions s
        JOIN users u ON s.student_id = u.ID_NUMBER
        WHERE s.status = 'completed'
        ORDER BY s.end_time ASC
        LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $entries_per_page, $offset);
}

$stmt->execute();
$records = $stmt->get_result();

// Get the total number of records for pagination
$total_query = "
    SELECT COUNT(*) AS total
    FROM sit_in_sessions s
    JOIN users u ON s.student_id = u.ID_NUMBER
    WHERE s.status = 'completed'";
$total_result = $conn->query($total_query);
$total_records = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $entries_per_page);

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

        // Add title, subtitle, and generated date
        fputcsv($output, ['UNIVERSITY OF CEBU']);
        fputcsv($output, ['College of Computer Studies']);
        fputcsv($output, ['Generated on: ' . date("Y-m-d h:i:sa")]);
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
        // Use the current entries_per_page and page settings for PDF export
        $entries_per_page = isset($_GET['entries']) ? (int)$_GET['entries'] : 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $entries_per_page;

        // Fetch records with pagination for PDF
        $pdf_query = "
            SELECT s.*, u.FIRSTNAME, u.LASTNAME 
            FROM sit_in_sessions s
            JOIN users u ON s.student_id = u.ID_NUMBER
            WHERE s.status = 'completed'
            ORDER BY s.end_time ASC
            LIMIT ? OFFSET ?";
        $pdf_stmt = $conn->prepare($pdf_query);
        $pdf_stmt->bind_param('ii', $entries_per_page, $offset);
        $pdf_stmt->execute();
        $pdf_records = $pdf_stmt->get_result();

        require_once __DIR__ . '/TCPDF-main/tcpdf.php';

        $pdf = new TCPDF('L', 'mm', 'LETTER', true, 'UTF-8', false);
        $pdf->SetMargins(15, 10, 15);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();

        // Get current date and time
        $generatedDate = date("Y-m-d h:i:sa");

        // Define the absolute path to the image
        $imagePath = __DIR__ . '/images/uc.png';

        // Check if the image exists
        if (!file_exists($imagePath)) {
            $imagePath = '';
        }

        // Define the HTML content
        $html = '
        <div style="text-align: center; font-family: Arial, sans-serif; font-size: 14px;">
            ' . ($imagePath ? '<img src="' . $imagePath . '" height="70" style="margin-bottom: 10px;">' : '') . '
            <h1 style="margin: 0;">UNIVERSITY OF CEBU</h1>
            <h2 style="margin: 0;">College of Computer Studies</h2>
            <p style="margin: 0;">Generated on: ' . $generatedDate . '</p>
        </div>
        <table style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; margin-top: 20px;">
            <thead style="background-color: #f4f4f4;">
                <tr>
                    <th style="border: 1px solid #ddd; padding: 8px;">ID Number</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Name</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Purpose</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Laboratory</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Login</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Logout</th>
                    <th style="border: 1px solid #ddd; padding: 8px;">Date</th>
                </tr>
            </thead>
            <tbody>';

        while ($row = $pdf_records->fetch_assoc()) {
            $html .= '
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['student_id']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['FIRSTNAME'] . ' ' . $row['LASTNAME']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['purpose']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . htmlspecialchars($row['lab_room']) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . date("h:i:sa", strtotime($row['start_time'])) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . date("h:i:sa", strtotime($row['end_time'])) . '</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">' . date("Y-m-d", strtotime($row['start_time'])) . '</td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('sit_in_reports.pdf', 'D');
        exit();
    } elseif ($exportType === 'print') {
        $generatedDate = date("Y-m-d h:i:sa");
        
        // Define the absolute path to the image
        $imagePath = __DIR__ . '/images/uc.png';

        // Check if the image exists
        if (!file_exists($imagePath)) {
            $imagePath = '';
        }

        echo "
        <div style='text-align: center; font-family: Arial, sans-serif; font-size: 14px;'>
            " . ($imagePath ? "<img src='" . $imagePath . "' height='70' style='margin-bottom: 10px;'>" : "") . "
            <h1 style='margin: 0;'>UNIVERSITY OF CEBU</h1>
            <h2 style='margin: 0;'>College of Computer Studies</h2>
            <p style='margin: 0;'>Generated on: $generatedDate</p>
        </div>
        <table border='1' style='border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; margin-top: 20px;'>
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
            echo "
                <tr>
                    <td>{$row['student_id']}</td>
                    <td>{$row['FIRSTNAME']} {$row['LASTNAME']}</td>
                    <td>{$row['purpose']}</td>
                    <td>{$row['lab_room']}</td>
                    <td>" . date("h:i:sa", strtotime($row['start_time'])) . "</td>
                    <td>" . date("h:i:sa", strtotime($row['end_time'])) . "</td>
                    <td>" . date("Y-m-d", strtotime($row['start_time'])) . "</td>
                </tr>";
        }

        echo "
            </tbody>
        </table>
        <script>window.print();</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sit-in Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script> 
    <script>
        function printReport() {
            const originalContent = document.body.innerHTML; 
            const printContent = document.getElementById('reportTable').outerHTML;

                document.body.innerHTML = `
            <div style="text-align: center; font-family: Arial, sans-serif; font-size: 14px;">
                <img src="css-sit-in/images/uc.png" style="margin-bottom: 10px; width: 150px; height: auto;" onerror="this.onerror=null; this.src='images/uc.png';">
                <h1 style="margin: 0;">UNIVERSITY OF CEBU</h1>
                <h2 style="margin: 0;">College of Computer Studies</h2>
                <p style="margin: 0;">Generated on: ${new Date().toLocaleString()}</p>
            </div>
            ${printContent}
            `;

            window.print(); 
            document.body.innerHTML = originalContent; 
        }

        function exportToExcel() {
           
            const workbook = XLSX.utils.book_new();
            const worksheetData = [];

            
            worksheetData.push(['UNIVERSITY OF CEBU']);
            worksheetData.push(['College of Computer Studies']);
            worksheetData.push([`Generated on: ${new Date().toLocaleString()}`]);
            worksheetData.push([]); 

            // Add headers
            worksheetData.push(['ID Number', 'Name', 'Purpose', 'Laboratory', 'Login', 'Logout', 'Date']);

            // Add table data
            const rows = document.querySelectorAll('#reportTable tbody tr');
            rows.forEach(row => {
                const rowData = Array.from(row.querySelectorAll('td')).map(cell => cell.innerText);
                worksheetData.push(rowData);
            });

            // Create worksheet and append to workbook
            const worksheet = XLSX.utils.aoa_to_sheet(worksheetData);

            // Adjust column widths for better readability
            const columnWidths = [
                { wch: 15 }, // ID Number
                { wch: 25 }, // Name
                { wch: 20 }, // Purpose
                { wch: 20 }, // Laboratory
                { wch: 15 }, // Login
                { wch: 15 }, // Logout
                { wch: 15 }  // Date
            ];
            worksheet['!cols'] = columnWidths;

            // Merge cells for title, subtitle, and generated date
            worksheet['!merges'] = [
                { s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }, // Merge title row
                { s: { r: 1, c: 0 }, e: { r: 1, c: 6 } }, // Merge subtitle row
                { s: { r: 2, c: 0 }, e: { r: 2, c: 6 } }  // Merge generated date row
            ];

            // Center-align title, subtitle, and generated date
            ['A1', 'A2', 'A3'].forEach(cell => {
                if (!worksheet[cell]) return;
                worksheet[cell].s = {
                    alignment: { horizontal: 'center', vertical: 'center' },
                    font: { bold: true }
                };
            });

            // Center-align all headers and data
            const range = XLSX.utils.decode_range(worksheet['!ref']);
            for (let row = range.s.r; row <= range.e.r; row++) {
                for (let col = range.s.c; col <= range.e.c; col++) {
                    const cellAddress = XLSX.utils.encode_cell({ r: row, c: col });
                    if (!worksheet[cellAddress]) continue; // Skip empty cells
                    if (!worksheet[cellAddress].s) worksheet[cellAddress].s = {};
                    worksheet[cellAddress].s.alignment = { horizontal: 'center', vertical: 'center' };
                }
            }

            XLSX.utils.book_append_sheet(workbook, worksheet, 'Sheet1');

            // Export the workbook to an .xlsx file
            XLSX.writeFile(workbook, 'sit_in_reports.xlsx');
        }
    </script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-center mb-6">Sit-in Reports</h2>

        <div class="flex justify-between items-center mb-4">
            <!-- Search & Filter Section -->
            <form method="POST" class="flex gap-4">
                <input type="date" name="start_date" class="border rounded px-3 py-2" value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>">
                <input type="date" name="end_date" class="border rounded px-3 py-2" value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
                <a href="sit-in-reports.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Reset</a>
            </form>

            <!-- Entries Dropdown -->
            <form method="GET" class="flex items-center gap-2">
                <label for="entries" class="text-sm font-medium">Show</label>
                <select name="entries" id="entries" class="border rounded px-3 py-2" onchange="this.form.submit()">
                    <option value="10" <?= $entries_per_page == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $entries_per_page == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $entries_per_page == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $entries_per_page == 100 ? 'selected' : '' ?>>100</option>
                </select>
                <span class="text-sm font-medium">entries</span>
            </form>

            <!-- Export Buttons -->
            <div class="flex gap-2">
                <a href="#" onclick="exportToExcel()" class="bg-green-600 text-white px-3 py-2 rounded">Excel</a>
                <a href="?export=csv" class="bg-purple-600 text-white px-3 py-2 rounded">CSV</a>
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

        <!-- Pagination -->
        <div class="flex justify-center mt-4">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?entries=<?= $entries_per_page ?>&page=<?= $i ?>" class="px-3 py-2 mx-1 border rounded <?= $i == $page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
</body>
</html>
</html>

