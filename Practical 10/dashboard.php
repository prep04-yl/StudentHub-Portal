<?php
/**
 * Practical 10: Authenticated Student Dashboard
 * Uses session data to display profile and secure controls
 */
require_once __DIR__ . '/check-session.php';

// Enforce student role
if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit;
}

$fullName = htmlspecialchars($_SESSION['full_name'] ?? 'Student');
$email    = htmlspecialchars($_SESSION['email'] ?? '');
$userId   = htmlspecialchars($_SESSION['user_id'] ?? '');
$course   = htmlspecialchars($_SESSION['course'] ?? 'Engineering');
$year     = htmlspecialchars($_SESSION['year'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - StudentHub Portal</title>
    <link rel="stylesheet" href="../Practical 3/style.css">
</head>
<body>
    <header class="profile-header">
        <div class="user-logo">
            <img src="../Practical 2/Student+Hub.jpg" alt="StudentHub Logo" onerror="this.src='../Student+Hub.jpg'">
        </div>
        <div class="search-info">
            <input type="search" placeholder="Search dashboard...">
        </div>
        <div class="user-menu">
            <button id="themeSwitcher" class="theme-switcher" title="Toggle dark/light mode" onclick="toggleTheme()">🌙</button>
            <span class="notification" onclick="showNotification('info', 'Welcome back, <?= $fullName ?>!')" style="cursor:pointer;" title="Notifications">🔔</span>
            <div class="user-details">
                <p><?= $fullName ?></p>
                <small>ID: #<?= $userId ?></small>
            </div>
            <img src="../Practical 2/blank-profile-picture.png" class="mini-profile" alt="Profile" onerror="this.src='../blank-profile-picture.png'">
        </div>
    </header>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <a href="../Practical 2/pages/home.html">🏠 Home</a>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="../Practical 2/pages/profile.html">👤 Profile</a>
            <a href="../Practical 2/pages/events.html">📅 Events</a>
            <a href="../Practical 2/pages/faq.html">❓ FAQ</a>
            <a href="logout.php">🚪 Logout</a>
        </aside>
        <main class="dashboard-main">
            <h2>Welcome, <?= $fullName ?>!</h2>
            <p style="color:#666; margin-bottom: 20px;">Role: <strong>Student</strong> | Department: <?= $course ?> (<?= $year ?>)</p>

            <section class="stats">
                <div class="stat-card">
                    <h3>Attendance</h3>
                    <p>94%</p>
                </div>
                <div class="stat-card">
                    <h3>CGPA</h3>
                    <p>8.85</p>
                </div>
            </section>
            
            <section class="dashboard-card">
                <h3>Today's Timetable</h3>
                <p><strong>09:00 AM:</strong> Data Structures & Algorithms (Lab 3)</p>
                <p><strong>11:00 AM:</strong> Web Development Frameworks (Room 204)</p>
            </section>

            <section class="dashboard-card">
                <h3>Session Details</h3>
                <p>Logged in as: <strong><?= $email ?></strong></p>
                <p>Session Timeout: Inactivity limit of 10 minutes</p>
                <a href="logout.php" style="display:inline-block; margin-top:10px; color:#c76060; font-weight:bold;">Log out securely &rarr;</a>
            </section>
        </main>
    </div>
    <footer>
        <p>&copy; 2026 StudentHub Portal</p>
    </footer>

    <!-- JavaScript for Dynamic Components -->
    <script src="../Practical 4/dynamic-components.js"></script>
</body>
</html>
