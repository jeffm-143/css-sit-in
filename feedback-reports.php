<?php
session_start();

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'css_sit_in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = intval($_POST['delete_id']);

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Feedback deleted successfully.";
    } else {
        $_SESSION['message'] = "Failed to delete feedback.";
    }

    $stmt->close();
    header("Location: feedback-reports.php");
    exit();
}

// Fetch feedback records
$feedbacks = $conn->query("
    SELECT f.id, f.comments, f.created_at, s.lab_room, s.start_time, u.ID_NUMBER, u.FIRSTNAME, u.LASTNAME
    FROM feedback f
    JOIN sit_in_sessions s ON f.session_id = s.id
    JOIN users u ON s.student_id = u.ID_NUMBER
    ORDER BY f.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include 'admin-nav.php'; ?> <!-- Include the Navbar -->

    <div class="max-w-7xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold text-center mb-6">Student Feedback</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full table-auto bg-white shadow-lg rounded-lg overflow-hidden">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2">Student ID</th>
                            <th class="px-4 py-2">Student Name</th>
                            <th class="px-4 py-2">Laboratory</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Feedback</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-800 text-center"><?php echo htmlspecialchars($feedback['ID_NUMBER']); ?></td>
                                <td class="px-4 py-2 text-gray-800 text-center"><?php echo htmlspecialchars($feedback['FIRSTNAME'] . ' ' . $feedback['LASTNAME']); ?></td>
                                <td class="px-4 py-2 text-gray-800 text-center"><?php echo htmlspecialchars($feedback['lab_room']); ?></td>
                                <td class="px-4 py-2 text-gray-800 text-center"><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                <td class="px-4 py-2 text-gray-800 text-center truncate max-w-xs" title="<?php echo htmlspecialchars($feedback['comments']); ?>">
                                    <?php echo nl2br(htmlspecialchars(preg_replace("/\r\n|\r|\n/", " ", $feedback['comments']))); ?>
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <form method="POST">
                                        <input type="hidden" name="delete_id" value="<?php echo $feedback['id']; ?>">
                                        <button type="submit" class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-700 transition">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteForms = document.querySelectorAll('form');
            const modal = document.createElement('div');
            modal.id = 'deleteModal';
            modal.className = 'fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden';
            modal.innerHTML = `
                <div class="bg-white rounded-lg shadow-lg w-1/3">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-bold">Confirm Deletion</h3>
                    </div>
                    <div class="p-4">
                        <p>Are you sure you want to delete this feedback?</p>
                    </div>
                    <div class="flex justify-end p-4 border-t">
                        <button id="cancelDelete" class="bg-gray-500 text-white px-4 py-2 rounded-lg mr-2 hover:bg-gray-700">Cancel</button>
                        <button id="confirmDelete" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-700">Delete</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);

            let formToSubmit = null;

            deleteForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    formToSubmit = form;
                    modal.classList.remove('hidden');
                });
            });

            document.getElementById('cancelDelete').addEventListener('click', () => {
                modal.classList.add('hidden');
                formToSubmit = null;
            });

            document.getElementById('confirmDelete').addEventListener('click', () => {
                if (formToSubmit) {
                    formToSubmit.submit();
                }
            });

            // Allow pressing Enter to confirm deletion
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !modal.classList.contains('hidden')) {
                    document.getElementById('confirmDelete').click();
                }
            });
        });
    </script>
</body>
</html>
