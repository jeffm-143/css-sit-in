<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea, #50ac6b);
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
    </style>
</head>
<body>
    <!-- Navigation -->
    <header>
    <div class="nav-bar w3-container">
        <h2 style="margin-right: auto;">History</h2>
        <nav>
            <ul>
                <li><a href="#">Notification</a></li>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="edit_profile.php">Edit Profile</a></li>
                <li><a href="history.php">History</a></li>
                <li><a href="reservation.php">Reservation</a></li>
            </ul>
        </nav>
        <a href="logout.php" class="logout">Log out</a>
    </div>
</header>


</body>
</html>