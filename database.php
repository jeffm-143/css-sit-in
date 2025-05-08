<?php
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'ccs-sit-in';

try {
    $conn = new mysqli($host, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");

    // Add POINTS column if it doesn't exist
    $add_points_column = "ALTER TABLE users ADD COLUMN IF NOT EXISTS POINTS INT DEFAULT 0";
    if (!$conn->query($add_points_column)) {
        die("Error adding POINTS column: " . $conn->error);
    }
    
    // Update existing rows where POINTS is NULL
    $update_nulls = "UPDATE users SET POINTS = 0 WHERE POINTS IS NULL";
    $conn->query($update_nulls);

} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}
