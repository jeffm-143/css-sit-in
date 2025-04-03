
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script src="https://cdn.tailwindcss.com"></script>
<link rel="icon" type="image/png" href="images/ccswb.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<title>CCS | Sit-In Lab</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

body {
font-family: 'Poppins', sans-serif;
}

.bg-mesh {
background-color: #0093E9;
background-image: linear-gradient(160deg, #0093E9 0%, #80D0C7 100%);
}

.text-gradient {
background-clip: text;
-webkit-background-clip: text;
color: transparent;
background-image: linear-gradient(to right, #0093E9, #80D0C7);
}

.hover-scale {
transition: transform 0.3s ease;
}

.hover-scale:hover {
transform: scale(1.02);
}

.blob {
border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%;
animation: blob-animation 8s linear infinite;
}

@keyframes blob-animation {
0% { border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; }
25% { border-radius: 45% 55% 65% 35% / 40% 60% 40% 60%; }
50% { border-radius: 50% 50% 40% 60% / 55% 45% 55% 45%; }
75% { border-radius: 60% 40% 50% 50% / 35% 65% 35% 65%; }
100% { border-radius: 42% 58% 70% 30% / 45% 45% 55% 55%; }
}
</style>
</head>
<body class="min-h-screen bg-gray-50">
<!-- Navbar Section - Floating with frosted glass effect -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-md border-b border-teal-100 shadow-sm">
<div class="container mx-auto px-4 py-3">
<div class="flex justify-between items-center">
<div class="flex items-center space-x-2">
<img src="images/ccswb.png" alt="CCS Logo" class="h-10 w-10">
<div>
<span class="text-xl font-bold text-gradient">Lab<span class="text-teal-500">Track</span></span>
<span class="text-xs block text-gray-500">Sit-In Laboratory System</span>
</div>
</div>

<div class="hidden md:flex items-center space-x-8">
<a href="#" class="text-gray-700 hover:text-teal-600 transition-colors py-1 px-2 text-sm font-medium relative group">
Home
<span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
</a>
<a href="#features" class="text-gray-700 hover:text-teal-600 transition-colors py-1 px-2 text-sm font-medium relative group">
Features
<span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
</a>
<a href="#schedule" class="text-gray-700 hover:text-teal-600 transition-colors py-1 px-2 text-sm font-medium relative group">
Schedule
<span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
</a>
<a href="#announcements" class="text-gray-700 hover:text-teal-600 transition-colors py-1 px-2 text-sm font-medium relative group">
Announcements
<span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
</a>
<a href="students.php" class="text-gray-700 hover:text-teal-600 transition-colors py-1 px-2 text-sm font-medium relative group">
Students
<span class="absolute bottom-0 left-0 w-full h-0.5 bg-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left"></span>
</a>
</div>

<div class="flex items-center space-x-4">
<a href="login.php" class="hidden md:block px-5 py-2 rounded-full bg-gradient-to-r from-teal-400 to-cyan-500 text-white text-sm font-medium transition-all hover:shadow-lg hover:from-teal-500 hover:to-cyan-600">
Log in
</a>
<button class="md:hidden text-gray-700 focus:outline-none">
<i class="fas fa-bars text-xl"></i>
</button>
</div>
</div>
</div>
</nav>

<!-- Hero Section - Split Design -->
<div class="pt-16">
<div class="grid md:grid-cols-2 min-h-[600px]">
<!-- Left Side - Content -->
<div class="flex items-center justify-center p-8 md:p-16">
<div class="max-w-lg">
<div class="inline-block px-3 py-1 rounded-full bg-teal-100 text-teal-800 text-xs font-semibold mb-6">
CCS Sit-In Laboratory System
</div>
<h1 class="text-4xl md:text-5xl font-bold mb-4 leading-tight">
Next-Gen <span class="text-gradient">Lab Management</span> For Students
</h1>
<p class="text-gray-600 mb-8">
Track attendance, manage equipment, and streamline your laboratory experience with our modern sit-in lab system designed for today's tech-savvy students.
</p>
<div class="flex flex-col sm:flex-row gap-4">
<a href="login.php" class="px-8 py-3 rounded-full bg-gradient-to-r from-teal-400 to-cyan-500 text-white font-medium transition-all hover:shadow-lg hover:from-teal-500 hover:to-cyan-600 text-center">
Get Started
</a>
<a href="#features" class="px-8 py-3 rounded-full border border-gray-300 text-gray-700 font-medium hover:bg-gray-50 transition-all text-center">
Learn More
</a>
</div>

<div class="mt-10 grid grid-cols-3 gap-4">
<div class="text-center">
<div class="text-2xl font-bold text-teal-600">500+</div>
<div class="text-sm text-gray-500">Weekly Users</div>
</div>
<div class="text-center">
<div class="text-2xl font-bold text-teal-600">24/7</div>
<div class="text-sm text-gray-500">Support</div>
</div>
<div class="text-center">
<div class="text-2xl font-bold text-teal-600">100%</div>
<div class="text-sm text-gray-500">Satisfaction</div>
</div>
</div>
</div>
</div>

<!-- Right Side - Image -->
<div class="relative bg-mesh overflow-hidden flex items-center justify-center p-10">
<div class="absolute -top-20 -right-20 w-64 h-64 bg-cyan-300/30 rounded-full blur-3xl"></div>
<div class="absolute -bottom-10 -left-10 w-72 h-72 bg-teal-300/30 rounded-full blur-3xl"></div>

<div class="relative z-10 bg-white/10 backdrop-blur-md p-6 rounded-2xl border border-white/20 shadow-xl max-w-md w-full">
<div class="flex justify-between items-center mb-6">
<h3 class="text-white font-semibold">Lab Session Active</h3>
<span class="px-2 py-1 bg-green-500/20 text-green-100 rounded-full text-xs">Live</span>
</div>

<div class="space-y-4">
<div class="bg-white/20 rounded-lg p-3">
<div class="flex items-center">
<div class="h-10 w-10 rounded-full bg-gradient-to-r from-cyan-500 to-teal-500 flex items-center justify-center text-white text-xs font-bold">
JS
</div>
<div class="ml-3">
<div class="text-white text-sm font-medium">John Smith</div>
<div class="text-white/70 text-xs">CS-2023-0012</div>
</div>
<div class="ml-auto">
<span class="text-xs bg-teal-500/30 text-teal-100 px-2 py-0.5 rounded-full">
Checked In
</span>
</div>
</div>
</div>

<div class="bg-white/20 rounded-lg p-3">
<div class="flex items-center">
<div class="h-10 w-10 rounded-full bg-gradient-to-r from-cyan-500 to-teal-500 flex items-center justify-center text-white text-xs font-bold">
AL
</div>
<div class="ml-3">
<div class="text-white text-sm font-medium">Amy Lee</div>
<div class="text-white/70 text-xs">CS-2023-0024</div>
</div>
<div class="ml-auto">
<span class="text-xs bg-teal-500/30 text-teal-100 px-2 py-0.5 rounded-full">
Checked In
</span>
</div>
</div>
</div>

<div class="bg-white/20 rounded-lg p-3">
<div class="flex items-center">
<div class="h-10 w-10 rounded-full bg-gradient-to-r from-cyan-500 to-teal-500 flex items-center justify-center text-white text-xs font-bold">
MR
</div>
<div class="ml-3">
<div class="text-white text-sm font-medium">Mike Rodriguez</div>
<div class="text-white/70 text-xs">CS-2023-0036</div>
</div>
<div class="ml-auto">
<span class="text-xs bg-teal-500/30 text-teal-100 px-2 py-0.5 rounded-full">
Checked In
</span>
</div>
</div>
</div>
</div>

<div class="mt-6 flex justify-between items-center">
<div>
<div class="text-xs text-white/70">Session Time</div>
<div class="text-white">10:00 AM - 12:00 PM</div>
</div>
<div>
<div class="text-xs text-white/70">Lab Room</div>
<div class="text-white">CS Lab 101</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- Features Section -->
<div id="features" class="py-20 px-4">
<div class="container mx-auto">
<div class="text-center max-w-2xl mx-auto mb-16">
<h2 class="text-3xl font-bold mb-4">What Makes Our <span class="text-gradient">Lab System</span> Different</h2>
<p class="text-gray-600">Our sit-in laboratory management system combines cutting-edge technology with a user-friendly interface designed specifically for college students.</p>
</div>

<div class="grid md:grid-cols-3 gap-8">
<!-- Feature 1 -->
<div class="bg-white rounded-2xl shadow-lg p-6 hover-scale">
<div class="w-14 h-14 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center mb-6">
<i class="fas fa-qrcode text-white text-xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Quick Check-In</h3>
<p class="text-gray-600">
Use your student ID or scan a QR code for instant check-in and out of laboratory sessions. No more waiting in line.
</p>
</div>

<!-- Feature 2 -->
<div class="bg-white rounded-2xl shadow-lg p-6 hover-scale">
<div class="w-14 h-14 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center mb-6">
<i class="fas fa-calendar-alt text-white text-xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Scheduling</h3>
<p class="text-gray-600">
View available lab sessions, reserve time slots, and get notifications for upcoming sessions directly to your phone.
</p>
</div>

<!-- Feature 3 -->
<div class="bg-white rounded-2xl shadow-lg p-6 hover-scale">
<div class="w-14 h-14 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center mb-6">
<i class="fas fa-laptop text-white text-xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Equipment Access</h3>
<p class="text-gray-600">
Request and reserve specialized lab equipment in advance to ensure it's ready for your session.
</p>
</div>

<!-- Feature 4 -->
<div class="bg-white rounded-2xl shadow-lg p-6 hover-scale">
<div class="w-14 h-14 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center mb-6">
<i class="fas fa-chart-line text-white text-xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Progress Tracking</h3>
<p class="text-gray-600">
Monitor your lab attendance and performance metrics over time with visual dashboards and progress reports.
</p>
</div>

<!-- Feature 5 -->
<div class="bg-white rounded-2xl shadow-lg p-6 hover-scale">
<div class="w-14 h-14 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center mb-6">
<i class="fas fa-comments text-white text-xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Peer Collaboration</h3>
<p class="text-gray-600">
Connect with classmates who are in the same lab session for easier team projects and collaborative work.
</p>
</div>

<!-- Feature 6 -->
<div class="bg-white rounded-2xl shadow-lg p-6 hover-scale">
<div class="w-14 h-14 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center mb-6">
<i class="fas fa-bell text-white text-xl"></i>
</div>
<h3 class="text-xl font-semibold mb-3">Real-Time Notifications</h3>
<p class="text-gray-600">
Get instant alerts about lab availability, schedule changes, or important announcements from instructors.
</p>
</div>
</div>
</div>
</div>

<!-- Schedule Section with 3D Card Effect -->
<div id="schedule" class="py-20 px-4 bg-gradient-to-b from-gray-50 to-teal-50">
<div class="container mx-auto">
<div class="flex flex-col md:flex-row items-center justify-between mb-16">
<div class="max-w-xl mb-10 md:mb-0">
<h2 class="text-3xl font-bold mb-4">Lab <span class="text-gradient">Schedule</span> At A Glance</h2>
<p class="text-gray-600 mb-6">
Our interactive schedule gives you a real-time view of available lab sessions, occupancy rates, and upcoming events.
</p>
<a href="#" class="inline-flex items-center text-teal-600 font-medium">
View Full Schedule
<i class="fas fa-arrow-right ml-2"></i>
</a>
</div>

<div class="bg-white rounded-2xl shadow-xl p-6 transform rotate-1 transition-all hover:rotate-0 hover:scale-105">
<h3 class="text-lg font-semibold mb-4">This Week's Highlights</h3>
<div class="space-y-3">
<div class="flex items-center bg-teal-50 p-3 rounded-lg">
<div class="h-10 w-10 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center text-white text-sm">
<i class="fas fa-laptop-code"></i>
</div>
<div class="ml-3">
<div class="text-sm font-medium text-gray-800">Programming Workshop</div>
<div class="text-xs text-gray-500">Tuesday, 2:00 PM - 4:00 PM</div>
</div>
</div>

<div class="flex items-center bg-cyan-50 p-3 rounded-lg">
<div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-cyan-400 flex items-center justify-center text-white text-sm">
<i class="fas fa-project-diagram"></i>
</div>
<div class="ml-3">
<div class="text-sm font-medium text-gray-800">Network Security Lab</div>
<div class="text-xs text-gray-500">Wednesday, 10:00 AM - 12:00 PM</div>
</div>
</div>

<div class="flex items-center bg-blue-50 p-3 rounded-lg">
<div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-400 to-blue-400 flex items-center justify-center text-white text-sm">
<i class="fas fa-database"></i>
</div>
<div class="ml-3">
<div class="text-sm font-medium text-gray-800">Database Design Session</div>
<div class="text-xs text-gray-500">Friday, 1:00 PM - 3:00 PM</div>
</div>
</div>
</div>
</div>
</div>

<div class="grid md:grid-cols-4 gap-6">
<div class="bg-white rounded-xl shadow-md p-4 hover-scale">
<div class="flex justify-between items-center mb-3">
<span class="text-sm font-semibold">Monday</span>
<span class="text-xs text-teal-600">3 sessions</span>
</div>
<div class="h-3 w-full bg-gray-100 rounded-full">
<div class="h-3 bg-gradient-to-r from-teal-400 to-cyan-500 rounded-full" style="width: 75%"></div>
</div>
<div class="mt-2 text-xs text-gray-500">75% booked</div>
</div>

<div class="bg-white rounded-xl shadow-md p-4 hover-scale">
<div class="flex justify-between items-center mb-3">
<span class="text-sm font-semibold">Tuesday</span>
<span class="text-xs text-teal-600">5 sessions</span>
</div>
<div class="h-3 w-full bg-gray-100 rounded-full">
<div class="h-3 bg-gradient-to-r from-teal-400 to-cyan-500 rounded-full" style="width: 90%"></div>
</div>
<div class="mt-2 text-xs text-gray-500">90% booked</div>
</div>

<div class="bg-white rounded-xl shadow-md p-4 hover-scale">
<div class="flex justify-between items-center mb-3">
<span class="text-sm font-semibold">Wednesday</span>
<span class="text-xs text-teal-600">4 sessions</span>
</div>
<div class="h-3 w-full bg-gray-100 rounded-full">
<div class="h-3 bg-gradient-to-r from-teal-400 to-cyan-500 rounded-full" style="width: 60%"></div>
</div>
<div class="mt-2 text-xs text-gray-500">60% booked</div>
</div>

<div class="bg-white rounded-xl shadow-md p-4 hover-scale">
<div class="flex justify-between items-center mb-3">
<span class="text-sm font-semibold">Thursday</span>
<span class="text-xs text-teal-600">3 sessions</span>
</div>
<div class="h-3 w-full bg-gray-100 rounded-full">
<div class="h-3 bg-gradient-to-r from-teal-400 to-cyan-500 rounded-full" style="width: 45%"></div>
</div>
<div class="mt-2 text-xs text-gray-500">45% booked</div>
</div>
</div>
</div>
</div>


<div class="order-1 md:order-2 text-center md:text-left">
<div class="relative inline-block">
<div class="w-64 h-64 blob bg-gradient-to-r from-cyan-400 via-teal-400 to-cyan-500"></div>
<div class="absolute inset-0 flex items-center justify-center text-white">
<div>
<div class="text-6xl font-bold">24/7</div>
<div class="text-xl mt-2">Support</div>
</div>
</div>
</div>

<h2 class="text-3xl font-bold mt-8 mb-4">Stay Updated With <span class="text-gradient">Important Notices</span></h2>
<p class="text-gray-600 mb-6 max-w-lg">
Never miss an important announcement about lab closures, special sessions, or equipment maintenance. All updates are delivered in real-time to keep you informed.
</p>
<a href="#" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-teal-400 to-cyan-500 text-white font-medium rounded-full hover:shadow-lg transition-all">
<i class="fas fa-bell mr-2"></i>
Enable Notifications
</a>
</div>
</div>
</div>
</div>

<!-- Testimonials/Student Feedback -->
<div class="py-20 px-4 bg-gradient-to-b from-teal-50 to-cyan-50">
<div class="container mx-auto">
<div class="text-center max-w-2xl mx-auto mb-16">
<h2 class="text-3xl font-bold mb-4">What <span class="text-gradient">Students</span> Are Saying</h2>
<p class="text-gray-600">
Hear from fellow students about their experience with our sit-in laboratory system.
</p>
</div>

<div class="grid md:grid-cols-3 gap-8">
<div class="bg-white p-6 rounded-2xl shadow-lg">
<div class="flex items-center mb-4">
<div class="h-12 w-12 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center text-white text-sm font-bold">
JD
</div>
<div class="ml-3">
<div class="font-medium">Jessica Davis</div>
<div class="text-xs text-gray-500">Computer Science, 3rd Year</div>
</div>
</div>
<p class="text-gray-600 mb-4">
"The lab tracking system has made it so much easier to find available slots. I love how I can check in with just my ID card!"
</p>
<div class="flex text-yellow-400">
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
</div>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg">
<div class="flex items-center mb-4">
<div class="h-12 w-12 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center text-white text-sm font-bold">
TK
</div>
<div class="ml-3">
<div class="font-medium">Tyler Kim</div>
<div class="text-xs text-gray-500">Information Technology, 2nd Year</div>
</div>
</div>
<p class="text-gray-600 mb-4">
"Being able to reserve equipment in advance has saved me so much time. The interface is super intuitive and modern."
</p>
<div class="flex text-yellow-400">
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star-half-alt"></i>
</div>
</div>

<div class="bg-white p-6 rounded-2xl shadow-lg">
<div class="flex items-center mb-4">
<div class="h-12 w-12 rounded-full bg-gradient-to-r from-cyan-400 to-teal-400 flex items-center justify-center text-white text-sm font-bold">
MP
</div>
<div class="ml-3">
<div class="font-medium">Maria Patel</div>
<div class="text-xs text-gray-500">Cybersecurity, 4th Year</div>
</div>
</div>
<p class="text-gray-600 mb-4">
"The notifications feature keeps me updated on lab availability. I never miss a session now thanks to the reminders."
</p>
<div class="flex text-yellow-400">
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
<i class="fas fa-star"></i>
</div>
</div>
</div>
</div>
</div>

<!-- CTA Section -->
<div class="py-20 px-4">
<div class="container mx-auto">
<div class="bg-gradient-to-r from-cyan-500 to-teal-500 rounded-3xl overflow-hidden shadow-xl">
<div class="grid md:grid-cols-2">
<div class="p-12 flex items-center">
<div class="max-w-lg">
<h2 class="text-3xl font-bold text-white mb-4">Ready to Streamline Your Lab Experience?</h2>
<p class="text-cyan-50 mb-8">
Join hundreds of students who are already using our system to maximize their laboratory time and boost productivity.
</p>
<div class="flex flex-col sm:flex-row gap-4">
<a href="login.php" class="px-8 py-3 bg-white text-teal-600 font-medium rounded-full hover:shadow-lg transition-all text-center">
Get Started Now
</a>
<a href="Students.php" class="px-8 py-3 bg-teal-600 text-white font-medium rounded-full hover:bg-teal-700 transition-all text-center">
Explore Students
</a>
</div>
</div>
</div>
<div class="hidden md:block relative">
<div class="absolute inset-0 bg-black/10"></div>
<img src="https://images.unsplash.com/photo-1581092921461-39b9d904ee15?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=880&q=80" alt="Students in computer lab" class="w-full h-full object-cover">
</div>
</div>
</div>
</div>
</div>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12">
<div class="container mx-auto px-4">
<div class="grid md:grid-cols-4 gap-8">
<div>
<div class="flex items-center space-x-2 mb-6">
<img src="images/ccswb.png" alt="CCS Logo" class="h-10 w-10">
<div>
<span class="text-xl font-bold">Lab<span class="text-teal-400">Track</span></span>
<span class="text-xs block text-gray-400">Sit-In Laboratory System</span>
</div>
</div>
<p class="text-gray-400 text-sm mb-6">
Streamlining the laboratory experience for students with modern technology and intuitive design.
</p>
<div class="flex space-x-4">
<a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">
<i class="fab fa-facebook-f"></i>
</a>
<a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">
<i class="fab fa-twitter"></i>
</a>
<a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">
<i class="fab fa-instagram"></i>
</a>
</div>
</div>

<div>
<h3 class="text-lg font-semibold mb-4">Quick Links</h3>
<ul class="space-y-2">
<li><a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">Home</a></li>
<li><a href="#features" class="text-gray-400 hover:text-teal-400 transition-colors">Features</a></li>
<li><a href="#schedule" class="text-gray-400 hover:text-teal-400 transition-colors">Schedule</a></li>
<li><a href="#announcements" class="text-gray-400 hover:text-teal-400 transition-colors">Announcements</a></li>
<li><a href="Students.php" class="text-gray-400 hover:text-teal-400 transition-colors">Students</a></li>
</ul>
</div>

<div>
<h3 class="text-lg font-semibold mb-4">Resources</h3>
<ul class="space-y-2">
<li><a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">Help Center</a></li>
<li><a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">FAQ</a></li>
<li><a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">Support</a></li>
<li><a href="#" class="text-gray-400 hover:text-teal-400 transition-colors">Tutorials</a></li>
</ul>
</div>

<div>
<h3 class="text-lg font-semibold mb-4">Contact Us</h3>
<ul class="space-y-2 text-gray-400">
<li class="flex items-start">
<i class="fas fa-map-marker-alt mt-1 mr-2 text-teal-400"></i>
<span>College of Computer Studies, Main Campus</span>
</li>
<li class="flex items-center">
<i class="fas fa-phone mr-2 text-teal-400"></i>
<span>(123) 456-7890</span>
</li>
<li class="flex items-center">
<i class="fas fa-envelope mr-2 text-teal-400"></i>
<span>labtrack@example.edu</span>
</li>
</ul>
</div>
</div>

<div class="border-t border-gray-800 mt-12 pt-8 flex flex-col md:flex-row justify-between items-center">
<p class="text-sm text-gray-400">
&copy; <?php echo date('Y'); ?> CCS LabTrack. All rights reserved.
</p>
<div class="mt-4 md:mt-0">
<ul class="flex space-x-6">
<li><a href="#" class="text-sm text-gray-400 hover:text-teal-400 transition-colors">Privacy Policy</a></li>
<li><a href="#" class="text-sm text-gray-400 hover:text-teal-400 transition-colors">Terms of Service</a></li>
</ul>
</div>
</div>
</div>
</footer>

<script>
// Mobile menu toggle functionality could be added here
document.addEventListener('DOMContentLoaded', function() {
const mobileMenuButton = document.querySelector('.md\\:hidden button');
if (mobileMenuButton) {
mobileMenuButton.addEventListener('click', function() {
// Mobile menu toggle logic
Swal.fire({
title: 'Navigation',
html: `
<div class="flex flex-col space-y-4 mt-6">
<a href="#" class="text-teal-600 font-medium">Home</a>
<a href="#features" class="text-gray-700">Features</a>
<a href="#schedule" class="text-gray-700">Schedule</a>
<a href="#announcements" class="text-gray-700">Announcements</a>
<a href="students.php" class="text-gray-700">Students</a>
<a href="login.php" class="bg-gradient-to-r from-teal-400 to-cyan-500 text-white py-2 px-4 rounded-full mt-4">Log in</a>
</div>
`,
showConfirmButton: false,
showCloseButton: true
});
});
}
});
</script>
</body>
</html>