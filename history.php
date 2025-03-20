<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation History</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <header class="bg-navy-800 shadow-lg">
        <div class="container mx-auto px-4">
            <nav class="flex items-center justify-between h-16">
                <h2 class="text-2xl font-bold text-white">Reservation History</h2>
                <div class="flex items-center space-x-8">
                    <ul class="flex space-x-6">
                        <li><a href="#" class="text-white hover:text-yellow-400 transition">Notification</a></li>
                        <li><a href="dashboard.php" class="text-white hover:text-yellow-400 transition">Home</a></li>
                        <li><a href="edit_profile.php" class="text-white hover:text-yellow-400 transition">Edit Profile</a></li>
                        <li><a href="history.php" class="text-white hover:text-yellow-400 transition">History</a></li>
                        <li><a href="reservation.php" class="text-white hover:text-yellow-400 transition">Reservation</a></li>
                    </ul>
                    <a href="logout.php" class="bg-yellow-400 text-navy-800 px-4 py-2 rounded-lg font-bold hover:bg-navy-800 hover:text-yellow-400 transition duration-300">Log out</a>
                </div>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <!-- History Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Laboratory</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Slot</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <!-- Sample Data -->
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-20</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Computer Lab 1</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">9:00 AM - 10:00 AM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Project Work</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Approved
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-19</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Computer Lab 2</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2:00 PM - 3:00 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Research</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">2024-01-18</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Computer Lab 3</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">3:00 PM - 4:00 PM</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Assignment</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    Rejected
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'navy': {
                            800: '#000080',
                        }
                    }
                }
            }
        }
    </script>
</body>
</html>