<?php
/**
 * Practical 10: Session Guard & Timeout Check
 * - Restricts access to authenticated users
 * - Implements Session Timeout (e.g., 10 minutes of inactivity)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$timeoutDuration = 600; // 10 minutes in seconds

// 1. Check if user is logged in
if (empty($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header("Location: ../Practical%202/pages/login.html?error=unauthorized");
    exit;
}

// 2. Check session inactivity timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeoutDuration)) {
    // Session expired
    $_SESSION = [];
    session_destroy();
    header("Location: ../Practical%202/pages/login.html?error=timeout");
    exit;
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
