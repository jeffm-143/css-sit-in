<?php
require_once '../database.php';

$sql = file_get_contents(__DIR__ . '/create_notifications_table.sql');

try {
    if ($conn->multi_query($sql)) {
        echo "Notifications table created successfully\n";
    }
} catch (Exception $e) {
    echo "Error creating notifications table: " . $e->getMessage() . "\n";
}
?>