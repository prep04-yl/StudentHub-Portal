<?php
/**
 * Practical 8: Database Connection using PHP Data Objects (PDO)
 * File: db.php
 * Provides a secure, reusable PDO connection instance with error handling.
 */

$host     = 'localhost';
$dbname   = 'studenthub_db';
$username = 'root';
$password = '';
$charset  = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Native prepared statements
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // If running in an API context, return JSON; otherwise display clean error
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Database connection error: ' . $e->getMessage()
        ]);
        exit;
    } else {
        die("<h3>Database Connection Failed</h3><p>" . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>");
    }
}
