<?php
session_start();

$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

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
    SELECT f.id, f.comments, f.created_at, s.lab_room, s.purpose, s.start_time, 
           u.ID_NUMBER, u.FIRSTNAME, u.LASTNAME
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
        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hover-shadow {
            transition: all 0.2s ease;
        }
        .hover-shadow:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <div class="bg-white rounded-2xl shadow-lg p-8 hover-shadow fade-in">
            <h2 class="text-3xl font-bold text-center mb-8 text-gray-800">Student Feedback Reports</h2>
            
            <div class="overflow-x-auto rounded-xl shadow-sm border border-gray-100">
                <table class="min-w-full table-auto bg-white">
                    <thead>
                        <tr class="bg-gradient-to-r from-blue-50 to-blue-100">
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Student ID</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Laboratory</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Feedback</th>
                            <th class="px-6 py-4 text-sm font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                            <tr class="transition-colors hover:bg-blue-50">
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($feedback['ID_NUMBER']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700 font-medium"><?php echo htmlspecialchars($feedback['FIRSTNAME'] . ' ' . $feedback['LASTNAME']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($feedback['lab_room']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo htmlspecialchars($feedback['purpose']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?php echo date('M d, Y', strtotime($feedback['created_at'])); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700 truncate max-w-xs" title="<?php echo htmlspecialchars($feedback['comments']); ?>">
                                    <?php echo nl2br(htmlspecialchars(preg_replace("/\r\n|\r|\n/", " ", $feedback['comments']))); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <form method="POST" class="inline-block">
                                        <input type="hidden" name="delete_id" value="<?php echo $feedback['id']; ?>">
                                        <button type="submit" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200 ease-in-out transform hover:scale-105">
                                            <i class="fas fa-trash-alt mr-2"></i> Delete
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

    <!-- Enhanced Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
        <div class="bg-white rounded-2xl shadow-xl w-96 transform transition-all duration-300 scale-95 opacity-0">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900">Confirm Deletion</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600">Are you sure you want to delete this feedback? This action cannot be undone.</p>
            </div>
            <div class="flex justify-end gap-4 px-6 py-4 bg-gray-50 rounded-b-2xl">
                <button id="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 transition-colors">
                    Cancel
                </button>
                <button id="confirmDelete" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    Delete
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const deleteForms = document.querySelectorAll('form');
            const modal = document.getElementById('deleteModal');
            const modalContent = modal.querySelector('div');
            let formToSubmit = null;

            const showModal = () => {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modalContent.classList.remove('scale-95', 'opacity-0');
                    modalContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            };

            const hideModal = () => {
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-95', 'opacity-0');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 200);
            };

            deleteForms.forEach(form => {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    formToSubmit = form;
                    showModal();
                });
            });

            document.getElementById('cancelDelete').addEventListener('click', hideModal);

            document.getElementById('confirmDelete').addEventListener('click', () => {
                if (formToSubmit) formToSubmit.submit();
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) hideModal();
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) hideModal();
                if (e.key === 'Enter' && !modal.classList.contains('hidden')) {
                    document.getElementById('confirmDelete').click();
                }
            });
        });
    </script>
</body>
</html>
