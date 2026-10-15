<?php
/**
 * Practical 9: Secure User Registration with MySQLi
 * - Database Insert
 * - Duplicate Email Check
 * - Password Hashing via password_hash()
 */

// Include MySQLi database connection
require_once __DIR__ . '/db.php';

// Ensure response format
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(405);
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
        exit;
    } else {
        header('Location: ../Practical%202/pages/registration.html');
        exit;
    }
}

// 1. Retrieve and sanitize input fields
$fullName        = trim($_POST['fullName'] ?? '');
$email           = trim($_POST['email'] ?? '');
$mobile          = trim($_POST['mobile'] ?? '');
$password        = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$course          = trim($_POST['course'] ?? '');
$year            = trim($_POST['year'] ?? '');
$gender          = trim($_POST['gender'] ?? '');

$errors = [];

// 2. Server-side Validation
if (empty($fullName)) {
    $errors['fullName'] = 'Full name is required.';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'A valid email address is required.';
}
if (empty($mobile) || !preg_match('/^[6-9]\d{9}$/', $mobile)) {
    $errors['mobile'] = 'Valid 10-digit mobile number starting with 6-9 is required.';
}
if (empty($password) || strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}
if ($password !== $confirmPassword) {
    $errors['confirmPassword'] = 'Passwords do not match.';
}
if (empty($course)) {
    $errors['course'] = 'Please select a course.';
}
if (empty($year)) {
    $errors['year'] = 'Please select an academic year.';
}
if (empty($gender)) {
    $errors['gender'] = 'Please select gender.';
}

// 3. Duplicate Email Check using MySQLi Prepared Statement
if (empty($errors)) {
    $checkStmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? LIMIT 1");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $errors['email'] = 'This email address is already registered. Please use another email.';
    }
    $checkStmt->close();
}

// Return validation / duplicate errors
if (!empty($errors)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(422);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Validation error.',
            'errors'  => $errors
        ]);
        exit;
    } else {
        die("<h3>Registration Failed:</h3><p>" . implode("<br>", $errors) . "</p><a href='../Practical%202/pages/registration.html'>Go Back</a>");
    }
}

// 4. Password Hashing using password_hash()
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 5. Secure Database Insert using MySQLi Prepared Statement
$insertStmt = $conn->prepare("INSERT INTO students (full_name, email, mobile, password_hash, course, academic_year, gender) VALUES (?, ?, ?, ?, ?, ?, ?)");
$insertStmt->bind_param("sssssss", $fullName, $email, $mobile, $passwordHash, $course, $year, $gender);

if ($insertStmt->execute()) {
    $newId = $conn->insert_id;
    $insertStmt->close();

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'status'     => 'success',
            'message'    => "Registration successful! Record saved to database with ID #{$newId}.",
            'student_id' => $newId
        ]);
        exit;
    } else {
        echo "<h3>Registration Successful!</h3><p>User ID: #{$newId}</p><a href='../Practical%202/pages/registration.html'>Back to Registration</a>";
        exit;
    }
} else {
    $err = $conn->error;
    $insertStmt->close();
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => "Database error: " . $err]);
        exit;
    } else {
        die("Database error: " . $err);
    }
}
?>
