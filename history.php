<?php
// Database connection (update with your actual credentials)
$servername = 'localhost'; 
$username = "root";
$password = "";
$dbname = "css_sit_in";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the table exists
$table_check_query = "SHOW TABLES LIKE 'history'";
$table_check_result = $conn->query($table_check_query);

if ($table_check_result->num_rows == 0) {
    die("Table 'history' doesn't exist in the database.");
}

$sql = "SELECT * FROM history";
$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
        }
        .nav-bar {
            background-color: navy;
            padding: 15px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-bar ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center; 
            gap: 10px;
        }
        .nav-bar ul li {
            display: inline-block;
        }

        .nav-bar ul li a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 15px; 
            display: block; 
            transition: color 0.3s ease-in-out;
        }

        .nav-bar ul li a:hover {
            color: yellow;
        }

        .logout {
            background: yellow;
            color: navy;
            padding: 8px 12px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 5px;
            transition: background 0.3s ease-in-out, color 0.3s ease-in-out;
        }

        .logout:hover {
            background: navy;
            color: yellow;
        }
        .container { width: 80%; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #004080; color: white; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
        <div class="nav-bar w3-container">
            <h2 style="margin-right: auto;">History</h2>
            <nav>
                <ul>
                    <li><a href="notification.php">Notification</a></li>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="edit_profile.php">Edit Profile</a></li>
                    <li><a href="history.php">History</a></li>
                    <li><a href="reservation.php">Reservation</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout">Log out</a>
        </div>
    </header>

    <div class="container">
        <h2>History Information</h2>
        <table id="historyTable" class="display">
            <thead>
                <tr>
                    <th>ID Number</th>
                    <th>Name</th>
                    <th>Sit Purpose</th>
                    <th>Laboratory</th>
                    <th>Login</th>
                    <th>Logout</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id_number'] ?></td>
                    <td><?= $row['name'] ?></td>
                    <td><?= $row['purpose'] ?></td>
                    <td><?= $row['laboratory'] ?></td>
                    <td><?= $row['login_time'] ?></td>
                    <td><?= $row['logout_time'] ?></td>
                    <td><?= $row['date'] ?></td>
                    <td><button>View</button></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php $conn->close(); ?>