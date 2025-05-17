<?php
// add_admin_viewed_column.php - Add admin_viewed column to reservations table
session_start();

// Check if user is admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    echo "Unauthorized access";
    exit();
}

// Database connection
$host = 'localhost';  
$username = 'root';  
$password = '';      
$dbname = 'ccs-sit-in'; 

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column already exists
$checkColumn = $conn->query("SHOW COLUMNS FROM reservations LIKE 'admin_viewed'");
if ($checkColumn->num_rows > 0) {
    echo "Column 'admin_viewed' already exists in the reservations table.";
} else {
    // Add the admin_viewed column
    $alterTable = "ALTER TABLE reservations ADD COLUMN admin_viewed TINYINT(1) DEFAULT 0";
    
    if ($conn->query($alterTable) === TRUE) {
        echo "Column 'admin_viewed' added successfully to reservations table.";
    } else {
        echo "Error adding column: " . $conn->error;
    }
}

$conn->close();
?>