<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    logAudit($conn, $_SESSION['user_id'], $_SESSION['username'], 'LOGOUT', 'User logged out');
}

session_unset();
session_destroy();

header("Location: login.php");
exit;
?>
