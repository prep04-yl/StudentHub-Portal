<?php
session_start();
$conn = new mysqli("localhost", "root", "", "studenthub_db");

// Simple role switcher / login simulation
if (isset($_GET['switch_role'])) {
    $_SESSION['role'] = $_GET['switch_role'];
    $_SESSION['username'] = $_GET['switch_role'] . "_user";

    // Simple Audit Log insertion
    $user = $_SESSION['username'];
    $role = $_SESSION['role'];
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $conn->query("INSERT INTO audit_logs (username, action, details, ip_address) VALUES ('$user', 'ROLE_SWITCH', 'Switched role to $role', '$ip')");
}

// Default role if not set
if (!isset($_SESSION['role'])) {
    $_SESSION['role'] = 'admin';
    $_SESSION['username'] = 'admin_user';
}

$currentRole = $_SESSION['role'];
$currentUser = $_SESSION['username'];

// 1. Fetch Stats
$totalStudents = $conn->query("SELECT COUNT(*) AS total FROM students")->fetch_assoc()['total'] ?? 0;
$totalEvents   = $conn->query("SELECT COUNT(*) AS total FROM events")->fetch_assoc()['total'] ?? 0;
$totalUsers    = $conn->query("SELECT COUNT(*) AS total FROM users")->fetch_assoc()['total'] ?? 0;

// 2. Fetch Latest Registrations
$latestStudents = $conn->query("SELECT * FROM students ORDER BY id DESC LIMIT 5");

// 3. Fetch Audit Logs (Admin only)
$auditLogs = null;
if ($currentRole === 'admin') {
    $auditLogs = $conn->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 5");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 14 - Admin Dashboard & RBAC</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 20px auto; padding: 0 10px; }
        .nav { background: #1e293b; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .nav a { color: #fff; text-decoration: none; margin-right: 15px; font-size: 14px; }
        .nav a:hover { text-decoration: underline; }
        .stats { display: flex; gap: 15px; margin-bottom: 20px; }
        .stat-box { flex: 1; padding: 15px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; text-align: center; }
        .stat-box h3 { margin: 0; font-size: 14px; color: #475569; }
        .stat-box p { font-size: 24px; font-weight: bold; margin: 5px 0 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .role-bar { background: #e0f2fe; padding: 8px 12px; margin-bottom: 15px; border-radius: 4px; }
    </style>
</head>
<body>
    <h2>Practical 14: Admin Dashboard with RBAC & Audit Log</h2>

    <!-- Quick Role Switcher for Testing -->
    <div class="role-bar">
        Current User: <strong><?php echo htmlspecialchars($currentUser); ?></strong> 
        (Role: <strong><?php echo strtoupper($currentRole); ?></strong>) | Switch Role:
        <a href="index.php?switch_role=admin">Admin</a> | 
        <a href="index.php?switch_role=faculty">Faculty</a> | 
        <a href="index.php?switch_role=student">Student</a>
    </div>

    <!-- Dynamic Menu based on Role -->
    <nav class="nav">
        <a href="index.php">Dashboard</a>
        <?php if ($currentRole === 'admin' || $currentRole === 'faculty'): ?>
            <a href="../Practical 11/index.php">Student Records</a>
            <a href="../Practical 12/index.php">Event Records</a>
        <?php endif; ?>
        <?php if ($currentRole === 'admin'): ?>
            <a href="#audit-log">Audit Logs (Admin Only)</a>
            <a href="#settings">System Settings</a>
        <?php endif; ?>
        <?php if ($currentRole === 'student'): ?>
            <a href="#">My Profile</a>
        <?php endif; ?>
    </nav>

    <!-- Statistics Cards -->
    <div class="stats">
        <div class="stat-box">
            <h3>Total Students</h3>
            <p><?php echo $totalStudents; ?></p>
        </div>
        <div class="stat-box">
            <h3>Total Events</h3>
            <p><?php echo $totalEvents; ?></p>
        </div>
        <?php if ($currentRole === 'admin'): ?>
            <div class="stat-box">
                <h3>Total Users</h3>
                <p><?php echo $totalUsers; ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Latest Registrations -->
    <h3>Latest Student Registrations</h3>
    <table>
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Department</th>
        </tr>
        <?php if ($latestStudents && $latestStudents->num_rows > 0): ?>
            <?php while ($s = $latestStudents->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($s['roll_no']); ?></td>
                    <td><?php echo htmlspecialchars($s['name']); ?></td>
                    <td><?php echo htmlspecialchars($s['department'] ?? 'N/A'); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="3">No registrations found.</td></tr>
        <?php endif; ?>
    </table>

    <!-- Audit Log: Restricted to Admin -->
    <?php if ($currentRole === 'admin'): ?>
        <h3 id="audit-log">System Audit Logs (Admin Restricted)</h3>
        <table>
            <tr>
                <th>Username</th>
                <th>Action</th>
                <th>Details</th>
                <th>IP Address</th>
                <th>Time</th>
            </tr>
            <?php if ($auditLogs && $auditLogs->num_rows > 0): ?>
                <?php while ($log = $auditLogs->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($log['created_at']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No logs recorded.</td></tr>
            <?php endif; ?>
        </table>
    <?php else: ?>
        <p style="color: #64748b; font-style: italic;">* Audit log is restricted and only visible to administrators.</p>
    <?php endif; ?>

</body>
</html>
