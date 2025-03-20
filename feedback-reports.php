<?php
session_start();
require_once 'database.php';

$feedbacks = $conn->query("
    SELECT f.*, s.lab_room, s.start_time, u.FIRSTNAME, u.LASTNAME
    FROM feedback f
    JOIN sit_in_sessions s ON f.session_id = s.id
    JOIN users u ON s.student_id = u.ID_NUMBER
    ORDER BY f.created_at DESC
");

// Calculate average ratings
$avg_rating = $conn->query("
    SELECT AVG(rating) as avg_rating, lab_room
    FROM feedback f
    JOIN sit_in_sessions s ON f.session_id = s.id
    GROUP BY lab_room
");

$ratings = [];
while ($row = $avg_rating->fetch_assoc()) {
    $ratings[$row['lab_room']] = round($row['avg_rating'], 2);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Reports</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php include 'admin-nav.php'; ?>

    <div class="max-w-7xl mx-auto p-6">
        <!-- Ratings Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <?php foreach ($ratings as $lab => $rating): ?>
            <div class="bg-white rounded-lg shadow-md p-4">
                <h3 class="font-bold text-lg mb-2"><?php echo htmlspecialchars($lab); ?></h3>
                <div class="flex items-center">
                    <?php for($i = 1; $i <= 5; $i++): ?>
                        <i class="fas fa-star <?php echo $i <= $rating ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                    <?php endfor; ?>
                    <span class="ml-2">(<?php echo $rating; ?>)</span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Feedback List -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Student Feedback</h2>
            <div class="space-y-4">
                <?php while ($feedback = $feedbacks->fetch_assoc()): ?>
                    <div class="border-b pb-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold">
                                    <?php echo htmlspecialchars($feedback['FIRSTNAME'] . ' ' . $feedback['LASTNAME']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($feedback['lab_room']); ?> |
                                    <?php echo date('M d, Y h:i A', strtotime($feedback['created_at'])); ?>
                                </p>
                            </div>
                            <div class="flex">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $feedback['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <p class="mt-2"><?php echo nl2br(htmlspecialchars($feedback['comments'])); ?></p>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</body>
</html>
