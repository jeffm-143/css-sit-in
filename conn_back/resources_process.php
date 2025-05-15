<?php
session_start();
include(__DIR__ . '/../db_connection.php');

// Initialize resources array
$resources = [];

// Fetch all resources
$query = "SELECT * FROM resources ORDER BY upload_date DESC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }
}

// Handle resource upload
if (isset($_POST['add_resource'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $resource_type = $_POST['resource_type'];
    $year_level = $_POST['year_level'];
    $course = $_POST['course'];
    $uploaded_by = $_SESSION['username'];
    
    $file_path = '';
    $link_url = '';
    
    if ($resource_type === 'link') {
        $link_url = $_POST['link_url'];
        $stmt = $conn->prepare("INSERT INTO resources (title, description, resource_type, year_level, course, uploaded_by, link_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $title, $description, $resource_type, $year_level, $course, $uploaded_by, $link_url);
    } else {
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] === 0) {
            $upload_dir = '../uploads/resources/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_name = time() . '_' . $_FILES['resource_file']['name'];
            $file_path = 'uploads/resources/' . $file_name;
            
            if (move_uploaded_file($_FILES['resource_file']['tmp_name'], $upload_dir . $file_name)) {
                $stmt = $conn->prepare("INSERT INTO resources (title, description, resource_type, year_level, course, uploaded_by, file_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssssss", $title, $description, $resource_type, $year_level, $course, $uploaded_by, $file_path);
            }
        }
    }
    
    if (isset($stmt) && $stmt->execute()) {
        $_SESSION['success_message'] = "Resource added successfully!";
    } else {
        $_SESSION['error_message'] = "Error adding resource.";
    }
    
    header("Location: ../admin-resources.php");
    exit();
}

// Handle resource deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Get file path before deleting
    $stmt = $conn->prepare("SELECT file_path FROM resources WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($file = $result->fetch_assoc()) {
        if (!empty($file['file_path'])) {
            $file_path = '../' . $file['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }
    
    $stmt = $conn->prepare("DELETE FROM resources WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Resource deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting resource.";
    }
    
    header("Location: ../admin-resources.php");
    exit();
}
?>