<?php
/**
 * Practical 10: Authenticated Admin Dashboard (Role-Based Access)
 */
require_once __DIR__ . '/check-session.php';

// Enforce admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$fullName = htmlspecialchars($_SESSION['full_name'] ?? 'Admin');
$email    = htmlspecialchars($_SESSION['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - StudentHub Portal</title>
    <link rel="stylesheet" href="../Practical 3/style.css">
</head>
<body>
    <header class="profile-header">
        <div class="user-logo">
            <img src="../Practical 2/Student+Hub.jpg" alt="StudentHub Logo" onerror="this.src='../Student+Hub.jpg'">
        </div>
        <div class="title" style="flex:1; text-align:center;">
            <h1 style="color:white; margin:0;">Admin Control Panel</h1>
        </div>
        <div class="user-menu">
            <div class="user-details">
                <p><?= $fullName ?></p>
                <small>Administrator</small>
            </div>
            <a href="logout.php" style="color:white; margin-left:15px; text-decoration:none; font-weight:bold;">Logout 🚪</a>
        </div>
    </header>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <a href="admin.php" class="active">🛡️ Admin Overview</a>
            <a href="../Practical 2/pages/events.html">📅 Manage Events</a>
            <a href="logout.php">🚪 Logout</a>
        </aside>
        <main class="dashboard-main">
            <h2>Welcome, Administrator!</h2>
            <p style="color:#666; margin-bottom: 20px;">Role-based access granted to <strong><?= $email ?></strong></p>

            <section class="stats">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <p>Protected</p>
                </div>
                <div class="stat-card">
                    <h3>Active Session</h3>
                    <p>Secured</p>
                </div>
            </section>

            <section class="dashboard-card">
                <h3>Admin Privileges</h3>
                <p>Only users with the <code>admin</code> role can access this page. Non-admin users are automatically redirected to the Student Dashboard.</p>
                <a href="logout.php" style="display:inline-block; margin-top:10px; color:#c76060; font-weight:bold;">Log out securely &rarr;</a>
            </section>
        </main>
    </div>
    <footer>
        <p>&copy; 2026 StudentHub Portal</p>
    </footer>
</body>
</html>
