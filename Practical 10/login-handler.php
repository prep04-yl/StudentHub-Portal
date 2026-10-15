<?php
/**
 * Practical 10: Secure Login Handler
 * - Verifies password using password_verify()
 * - Regenerates session ID upon login
 * - Sets role-based session data (admin / student)
 * - Sets session timestamp for timeout tracking
 * - Sets secure Remember-Me cookie if requested
 */

require_once __DIR__ . '/db.php';

// Check if request is AJAX
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        exit;
    } else {
        header('Location: ../Practical%202/pages/login.html');
        exit;
    }
}

$emailOrId = trim($_POST['email'] ?? $_POST['student_id'] ?? '');
$password  = $_POST['password'] ?? '';
$remember  = isset($_POST['remember']);

if (empty($emailOrId) || empty($password)) {
    $msg = 'Please enter both your email/student ID and password.';
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(422);
        echo json_encode(['status' => 'error', 'message' => $msg]);
        exit;
    } else {
        die($msg);
    }
}

// 1. Fetch user by email or student_id using prepared statement
$stmt = $conn->prepare("SELECT student_id, full_name, email, password_hash, course, academic_year FROM students WHERE email = ? OR student_id = ? LIMIT 1");
$stmt->bind_param("ss", $emailOrId, $emailOrId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 2. Verify user and password using password_verify()
if (!$user || !password_verify($password, $user['password_hash'])) {
    $errorMsg = 'Invalid credentials. Please check your email/ID and password.';
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(401);
        echo json_encode(['status' => 'error', 'message' => $errorMsg]);
        exit;
    } else {
        die($errorMsg);
    }
}

// 3. Start Session & Regenerate Session ID to prevent session fixation
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_regenerate_id(true);

// 4. Role-based determination (admin if email starts with admin@, otherwise student)
$role = (stripos($user['email'], 'admin@') === 0) ? 'admin' : 'student';

$_SESSION['logged_in']    = true;
$_SESSION['user_id']      = $user['student_id'];
$_SESSION['full_name']    = $user['full_name'];
$_SESSION['email']        = $user['email'];
$_SESSION['role']         = $role;
$_SESSION['course']       = $user['course'];
$_SESSION['year']         = $user['academic_year'];
$_SESSION['last_activity']= time(); // Used for session timeout

// 5. Remember-Me Token (stored in secure HTTP-only cookie for 7 days)
if ($remember) {
    $token = bin2hex(random_bytes(16));
    setcookie('remember_token', $user['student_id'] . ':' . $token, [
        'expires'  => time() + (7 * 24 * 60 * 60),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// 6. Role-based Redirection URL
$redirectUrl = ($role === 'admin') ? 'admin.php' : 'dashboard.php';

if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status'       => 'success',
        'message'      => 'Login successful!',
        'role'         => $role,
        'redirect_url' => $redirectUrl
    ]);
    exit;
} else {
    header("Location: $redirectUrl");
    exit;
}
?>
