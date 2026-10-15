<?php
/**
 * Practical 10: Secure Logout Handler
 * - Clears session array
 * - Destroys session
 * - Clears session cookie and remember-me token
 * - Redirects to login page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Unset all session variables
$_SESSION = [];

// Delete session cookie if present
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear Remember-Me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
}

// Destroy session
session_destroy();

// Redirect back to login page
header("Location: ../Practical%202/pages/login.html?logged_out=1");
exit;
?>
