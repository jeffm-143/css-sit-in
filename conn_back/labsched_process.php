<?php
session_start();
include(__DIR__ . '/../db_connection.php');

// Initialize alert variables
$sweetAlert = false;
$alertType = '';
$alertTitle = '';
$alertText = '';

// Fetch all schedules
$result = null;
$query = "SELECT * FROM lab_schedules ORDER BY upload_date DESC";
$result = $conn->query($query);

// Handle schedule upload
if (isset($_POST['upload_schedule'])) {
    $lab_room = $_POST['lab_room'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $uploaded_by = $_SESSION['username'];

    // Handle file upload
    if (isset($_FILES['schedule_image'])) {
        $file = $_FILES['schedule_image'];
        $fileName = time() . '_' . $file['name'];
        $targetPath = '../uploads/schedules/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            $sql = "INSERT INTO lab_schedules (lab_room, title, description, schedule_image, uploaded_by) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $lab_room, $title, $description, $fileName, $uploaded_by);

            if ($stmt->execute()) {
                $sweetAlert = true;
                $alertType = 'success';
                $alertTitle = 'Success!';
                $alertText = 'Schedule has been uploaded successfully.';
                header("Location: ../admin-labsched.php");
                exit();
            } else {
                $sweetAlert = true;
                $alertType = 'error';
                $alertTitle = 'Error!';
                $alertText = 'Failed to save schedule details.';
            }
        } else {
            $sweetAlert = true;
            $alertType = 'error';
            $alertTitle = 'Error!';
            $alertText = 'Failed to upload schedule image.';
        }
    }
}

// Handle schedule deletion
if (isset($_POST['delete_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    
    // First get the image filename
    $sql = "SELECT schedule_image FROM lab_schedules WHERE schedule_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $schedule = $result->fetch_assoc();
    
    if ($schedule) {
        // Delete the file
        $imagePath = '../uploads/schedules/' . $schedule['schedule_image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        // Delete from database
        $sql = "DELETE FROM lab_schedules WHERE schedule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $schedule_id);
        
        if ($stmt->execute()) {
            $sweetAlert = true;
            $alertType = 'success';
            $alertTitle = 'Deleted!';
            $alertText = 'Schedule has been deleted successfully.';
            header("Location: ../admin-labsched.php");
            exit();
        } else {
            $sweetAlert = true;
            $alertType = 'error';
            $alertTitle = 'Error!';
            $alertText = 'Failed to delete schedule.';
        }
    } else {
        $sweetAlert = true;
        $alertType = 'error';
        $alertTitle = 'Error!';
        $alertText = 'Schedule not found.';
    }
    
    header("Location: ../admin-labsched.php");
    exit();
}

// Handle schedule update
if (isset($_POST['update_schedule'])) {
    $schedule_id = $_POST['schedule_id'];
    $lab_room = $_POST['lab_room'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    
    // Check if a new image is uploaded
    if (!empty($_FILES['schedule_image']['name'])) {
        // Get the old image first to delete it
        $sql = "SELECT schedule_image FROM lab_schedules WHERE schedule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $schedule_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedule = $result->fetch_assoc();
        
        if ($schedule) {
            $oldImagePath = '../uploads/schedules/' . $schedule['schedule_image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }
        
        // Upload new image
        $file = $_FILES['schedule_image'];
        $fileName = time() . '_' . $file['name'];
        $targetPath = '../uploads/schedules/' . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Update record with new image
            $sql = "UPDATE lab_schedules SET lab_room = ?, title = ?, description = ?, schedule_image = ? WHERE schedule_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $lab_room, $title, $description, $fileName, $schedule_id);
        } else {
            $sweetAlert = true;
            $alertType = 'error';
            $alertTitle = 'Error!';
            $alertText = 'Failed to upload new schedule image.';
            header("Location: ../admin-labsched.php");
            exit();
        }
    } else {
        // Update record without changing image
        $sql = "UPDATE lab_schedules SET lab_room = ?, title = ?, description = ? WHERE schedule_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $lab_room, $title, $description, $schedule_id);
    }
    
    if ($stmt->execute()) {
        $sweetAlert = true;
        $alertType = 'success';
        $alertTitle = 'Updated!';
        $alertText = 'Schedule has been updated successfully.';
    } else {
        $sweetAlert = true;
        $alertType = 'error';
        $alertTitle = 'Error!';
        $alertText = 'Failed to update schedule.';
    }
    
    header("Location: ../admin-labsched.php");
    exit();
}
?>