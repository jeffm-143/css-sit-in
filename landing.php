<?php
// Redirect to public landing page for non-logged-in users
session_start();

if (isset($_SESSION['user'])) {
// User is logged in, redirect to the main dashboard
header("Location: dashboard.php");
exit();
} else {
// User is not logged in, redirect to public page
header("Location: index.php");
exit();
}
?>