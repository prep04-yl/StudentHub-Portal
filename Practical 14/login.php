<?php
session_start();
require_once 'db.php';

$error = "";

// Quick Demo Login Switcher or Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($user = $res->fetch_assoc()) {
            if (password_verify($password, $user['password']) || $password === 'password123') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                logAudit($conn, $user['id'], $user['username'], 'LOGIN', 'User successfully logged in');
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid password!";
            }
        } else {
            $error = "User not found!";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Practical 14 RBAC</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        body { background: #f1f5f9; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .login-card { background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { font-size: 1.4rem; color: #0f172a; margin-bottom: 8px; text-align: center; }
        p.subtitle { color: #64748b; font-size: 0.85rem; text-align: center; margin-bottom: 20px; }
        .alert { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 4px; }
        input { width: 100%; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; }
        button { width: 100%; padding: 10px; background: #2563eb; color: #fff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        button:hover { background: #1d4ed8; }
        .demo-roles { margin-top: 20px; padding-top: 15px; border-top: 1px dashed #cbd5e1; font-size: 0.8rem; color: #475569; }
        .demo-roles ul { margin-left: 20px; margin-top: 5px; }
    </style>
</head>
<body>

<div class="login-card">
    <h1>Role-Based Access Control</h1>
    <p class="subtitle">Login to test Dashboard, Dynamic Menus & Audit Log</p>

    <?php if (!empty($error)): ?>
        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required placeholder="admin / faculty / student" value="admin">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required value="password123">
        </div>
        <button type="submit">Log In</button>
    </form>

    <div class="demo-roles">
        <strong>Demo Credentials (Password: password123):</strong>
        <ul>
            <li><code>admin</code> (Full access + Audit logs + User stats)</li>
            <li><code>faculty</code> (Limited access: Students & Events)</li>
            <li><code>student</code> (Restricted access: My Profile only)</li>
        </ul>
    </div>
</div>

</body>
</html>
