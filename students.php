<?php
session_start();
require_once 'database.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Edit Student
    if (isset($_POST['edit_student'])) {
        $id = $_POST['id'];
        $id_number = $_POST['id_number'];
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $year = $_POST['year'];
        $course = $_POST['course'];

        $stmt = $conn->prepare("UPDATE users SET ID_NUMBER=?, FIRSTNAME=?, LASTNAME=?, YEAR=?, COURSE=? WHERE ID=? AND user_type='student'");
        $stmt->bind_param("sssisi", $id_number, $firstname, $lastname, $year, $course, $id);

        if ($stmt->execute()) {
            $_SESSION['alert'] = ['title' => 'Success', 'message' => 'Student updated successfully!'];
        } else {
            $_SESSION['alert'] = ['title' => 'Error', 'message' => 'Error updating student.'];
        }
        header('Location: students.php');
        exit();
    }

    // Delete Student
    if (isset($_POST['delete_student'])) {
        $id = $_POST['id'];

        // Check for active sessions
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM sit_in_sessions WHERE student_id = (SELECT ID_NUMBER FROM users WHERE ID = ?) AND status = 'active'");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $active_sessions = $check_stmt->get_result()->fetch_row()[0];

        if ($active_sessions > 0) {
            $_SESSION['alert'] = ['title' => 'Error', 'message' => 'Cannot delete student with active sessions.'];
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE ID = ? AND user_type = 'student'");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $_SESSION['alert'] = ['title' => 'Success', 'message' => 'Student deleted successfully!'];
            } else {
                $_SESSION['alert'] = ['title' => 'Error', 'message' => 'Error deleting student.'];
            }
        }
        header('Location: students.php');
        exit();
    }

    // Reset All Sessions
    if (isset($_POST['reset_sessions'])) {
        // Check if all students already have the maximum session
        $check_stmt = $conn->prepare("SELECT COUNT(*) AS total_students, SUM(CASE WHEN SESSION = 30 THEN 1 ELSE 0 END) AS max_sessions FROM users WHERE user_type = 'student'");
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();

        if ($result['total_students'] == $result['max_sessions']) {
            $_SESSION['alert'] = ['title' => 'Information', 'message' => 'All students already have the maximum session (30).'];
        } else {
            $stmt = $conn->prepare("UPDATE users SET SESSION = 30 WHERE user_type = 'student'");
            if ($stmt->execute()) {
                $_SESSION['alert'] = ['title' => 'Success', 'message' => 'All student sessions have been reset!'];
            } else {
                $_SESSION['alert'] = ['title' => 'Error', 'message' => 'Error resetting sessions.'];
            }
        }
        header('Location: students.php');
        exit();
    }

    // Reset Individual Student Session
    if (isset($_POST['reset_student_session'])) {
        $id = $_POST['id'];

        // Check if the session is already at the maximum
        $check_stmt = $conn->prepare("SELECT SESSION FROM users WHERE ID = ? AND user_type = 'student'");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $current_session = $check_stmt->get_result()->fetch_assoc()['SESSION'];

        if ($current_session >= 30) {
            $_SESSION['alert'] = ['title' => 'Information', 'message' => 'This student already has the maximum session (30).'];
        } else {
            $stmt = $conn->prepare("UPDATE users SET SESSION = 30 WHERE ID = ? AND user_type = 'student'");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $_SESSION['alert'] = ['title' => 'Success', 'message' => 'Student session reset successfully!'];
            } else {
                $_SESSION['alert'] = ['title' => 'Error', 'message' => 'Error resetting session.'];
            }
        }
        header('Location: students.php');
        exit();
    }
}

// Fetch students
$query = "SELECT *, COALESCE(POINTS, 0) as POINTS FROM users WHERE user_type = 'student' ORDER BY ID ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>
    <div class="max-w-7xl mx-auto p-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-2xl font-bold mb-6 text-center">Students Information</h2>

            <div class="flex mb-4">
                <form method="POST" class="flex gap-2">
                    <button type="submit" name="reset_sessions" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                        Reset All Sessions
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-white text-left text-sm">
                    <thead class="bg-gray-200">
                        <tr class="text-center">                            <th class="p-3">ID Number</th>
                            <th class="p-3">Name</th>
                            <th class="p-3">Year Level</th>
                            <th class="p-3">Course</th>
                            <th class="p-3">Remaining Session</th>
                            <th class="p-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="border-t">                                <td class="p-3"><?= htmlspecialchars($row['ID_NUMBER']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($row['LASTNAME'] . ", " . $row['FIRSTNAME']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($row['YEAR']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($row['COURSE']) ?></td>
                                <td class="p-3"><?= htmlspecialchars($row['SESSION']) ?></td>
                                <td class="p-3 flex gap-2 justify-center items-center">
                                    <button onclick="editStudent(<?= htmlspecialchars(json_encode($row)) ?>)" 
                                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                                        Edit
                                    </button>
                                    <button onclick="deleteStudent(<?= $row['ID'] ?>)"
                                            class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">
                                        Delete
                                    </button>
                                    <button onclick="resetSession(<?= $row['ID'] ?>)"
                                            class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">
                                        Reset Session
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-bold mb-4">Edit Student</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="edit_student" value="1">
                <input type="hidden" name="id" id="edit_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">ID Number</label>
                    <input type="text" name="id_number" id="edit_id_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">First Name</label>
                    <input type="text" name="firstname" id="edit_firstname" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Last Name</label>
                    <input type="text" name="lastname" id="edit_lastname" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Year Level</label>
                    <select name="year" id="edit_year" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <?php for($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Course</label>
                    <input type="text" name="course" id="edit_course" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                </div>
                
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                        Cancel
                    </button>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alert Modal -->
    <div id="alertModal" class="hidden fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-lg p-6 w-96">
            <h3 id="alertTitle" class="text-lg font-bold mb-4 text-center">Alert</h3>
            <p id="alertMessage" class="text-sm text-gray-700 mb-6 text-center"></p>
            <div class="flex justify-center">
                <button onclick="closeAlertModal()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    OK
                </button>
            </div>
        </div>
    </div>

    <script>
        function showAlertModal(title, message) {
            document.getElementById('alertTitle').innerText = title;
            document.getElementById('alertMessage').innerText = message;
            document.getElementById('alertModal').classList.remove('hidden');
        }

        function closeAlertModal() {
            document.getElementById('alertModal').classList.add('hidden');
        }

        function editStudent(student) {
            document.getElementById('edit_id').value = student.ID;
            document.getElementById('edit_id_number').value = student.ID_NUMBER;
            document.getElementById('edit_firstname').value = student.FIRSTNAME;
            document.getElementById('edit_lastname').value = student.LASTNAME;
            document.getElementById('edit_year').value = student.YEAR;
            document.getElementById('edit_course').value = student.COURSE;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        function deleteStudent(id) {
            showAlertModal('Delete Student', 'Are you sure you want to delete this student?');
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="delete_student" value="1">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        function resetSession(id) {
            showAlertModal('Reset Session', 'Are you sure you want to reset this student\'s session?');
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="reset_student_session" value="1">
                <input type="hidden" name="id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['alert'])): ?>
                showAlertModal('<?php echo $_SESSION['alert']['title']; ?>', '<?php echo $_SESSION['alert']['message']; ?>');
                <?php unset($_SESSION['alert']); ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
