<?php
/**
 * Practical 7: PHP Form Processing with Server-Side Validation and CSV/JSON File Storage
 * Handler: Registration Form Processing
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', '0');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set JSON response header if requested via AJAX / Fetch
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(405);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid request method. Only POST is allowed.'
        ]);
        exit;
    } else {
        header('Location: ../Practical%202/pages/registration.html');
        exit;
    }
}

// Storage paths (in Practical 7 folder)
$storageDir = __DIR__;
$csvFile = $storageDir . DIRECTORY_SEPARATOR . 'registrations.csv';
$jsonFile = $storageDir . DIRECTORY_SEPARATOR . 'registrations.json';

// Helper function to sanitize text input
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data ?? '');
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// 1. Retrieve raw data
$rawFullName        = $_POST['fullName'] ?? '';
$rawEmail           = $_POST['email'] ?? '';
$rawMobile          = $_POST['mobile'] ?? '';
$rawPassword        = $_POST['password'] ?? '';
$rawConfirmPassword = $_POST['confirmPassword'] ?? '';
$rawCourse          = $_POST['course'] ?? '';
$rawYear            = $_POST['year'] ?? '';
$rawGender          = $_POST['gender'] ?? '';
$rawTerms           = isset($_POST['terms']) ? 'Accepted' : '';

// 2. Sanitize inputs
$fullName = sanitize_input($rawFullName);
$email    = filter_var(trim($rawEmail), FILTER_SANITIZE_EMAIL);
$mobile   = sanitize_input($rawMobile);
$course   = sanitize_input($rawCourse);
$year     = sanitize_input($rawYear);
$gender   = sanitize_input($rawGender);
$terms    = $rawTerms;

// 3. Server-side Validation
$errors = [];

// Full Name: 3-50 alphabetical characters and spaces
if (empty($fullName)) {
    $errors['fullName'] = 'Full name is required.';
} elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $fullName)) {
    $errors['fullName'] = 'Name must contain only letters and spaces (3-50 characters).';
}

// Email: Standard email filter and regex check
if (empty($email)) {
    $errors['email'] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Please enter a valid email address.';
}

// Mobile: 10 digit Indian number starting with 6,7,8,9
if (empty($mobile)) {
    $errors['mobile'] = 'Mobile number is required.';
} elseif (!preg_match("/^[6-9]\d{9}$/", $mobile)) {
    $errors['mobile'] = 'Mobile number must be a valid 10-digit number starting with 6-9.';
}

// Password: Min 8 chars, 1 uppercase, 1 lowercase, 1 digit
if (empty($rawPassword)) {
    $errors['password'] = 'Password is required.';
} elseif (strlen($rawPassword) < 8) {
    $errors['password'] = 'Password must be at least 8 characters long.';
} elseif (!preg_match("/[A-Z]/", $rawPassword)) {
    $errors['password'] = 'Password must include at least one uppercase letter.';
} elseif (!preg_match("/[a-z]/", $rawPassword)) {
    $errors['password'] = 'Password must include at least one lowercase letter.';
} elseif (!preg_match("/\d/", $rawPassword)) {
    $errors['password'] = 'Password must include at least one number.';
}

// Confirm Password
if (empty($rawConfirmPassword)) {
    $errors['confirmPassword'] = 'Please confirm your password.';
} elseif ($rawPassword !== $rawConfirmPassword) {
    $errors['confirmPassword'] = 'Passwords do not match.';
}

// Course selection
$validCourses = [
    'Computer Engineering',
    'Information Technology',
    'Electronics & Communication',
    'Mechanical Engineering',
    'Civil Engineering'
];
if (empty($course)) {
    $errors['course'] = 'Please select a course.';
} elseif (!in_array($course, $validCourses, true)) {
    $errors['course'] = 'Selected course is invalid.';
}

// Year of study
$validYears = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
if (empty($year)) {
    $errors['year'] = 'Please select year of study.';
} elseif (!in_array($year, $validYears, true)) {
    $errors['year'] = 'Selected year of study is invalid.';
}

// Gender
$validGenders = ['Male', 'Female', 'Other'];
if (empty($gender)) {
    $errors['gender'] = 'Please select your gender.';
} elseif (!in_array($gender, $validGenders, true)) {
    $errors['gender'] = 'Selected gender is invalid.';
}

// Terms & Conditions
if ($terms !== 'Accepted') {
    $errors['terms'] = 'You must accept the terms and conditions.';
}

// 4. Handle Validation Errors
if (!empty($errors)) {
    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        http_response_code(422);
        echo json_encode([
            'status' => 'error',
            'message' => 'Validation failed. Please correct the highlighted errors.',
            'errors' => $errors
        ]);
        exit;
    } else {
        // Fallback for direct form submit: Render beautiful response page
        render_response_page('Registration Failed', 'error', 'Validation failed. Please correct the errors below and try again.', $errors, [
            'Full Name' => $fullName,
            'Email' => $email,
            'Mobile' => $mobile,
            'Course' => $course,
            'Year' => $year,
            'Gender' => $gender
        ], '../Practical 2/pages/registration.html');
        exit;
    }
}

// 5. Hash password for security before storing
$passwordHash = password_hash($rawPassword, PASSWORD_BCRYPT);
$registeredAt = date('Y-m-d H:i:s');
$studentId = 'STU-' . strtoupper(substr(uniqid(), -6));

$record = [
    'id' => $studentId,
    'full_name' => $fullName,
    'email' => $email,
    'mobile' => $mobile,
    'course' => $course,
    'year' => $year,
    'gender' => $gender,
    'password_hash' => $passwordHash,
    'registered_at' => $registeredAt
];

// 6. Store in CSV format
$csvRow = [
    $studentId,
    $fullName,
    $email,
    $mobile,
    $course,
    $year,
    $gender,
    $passwordHash,
    $registeredAt
];

$csvNeedsHeader = !file_exists($csvFile) || filesize($csvFile) === 0;
$csvFp = @fopen($csvFile, 'a');
if ($csvFp) {
    if ($csvNeedsHeader) {
        fputcsv($csvFp, ['ID', 'Full Name', 'Email', 'Mobile', 'Course', 'Year', 'Gender', 'Password Hash', 'Registered At']);
    }
    fputcsv($csvFp, $csvRow);
    fclose($csvFp);
}

// 7. Store in JSON format
$existingRecords = [];
if (file_exists($jsonFile) && filesize($jsonFile) > 0) {
    $jsonContent = @file_get_contents($jsonFile);
    $decoded = json_decode($jsonContent, true);
    if (is_array($decoded)) {
        $existingRecords = $decoded;
    }
}
$existingRecords[] = $record;
@file_put_contents($jsonFile, json_encode($existingRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// 8. Output Success Response
$successMessage = "Student registration completed successfully for {$fullName} ({$studentId}). Records stored in CSV & JSON.";

if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'success',
        'message' => $successMessage,
        'student_id' => $studentId,
        'data' => [
            'fullName' => $fullName,
            'email' => $email,
            'course' => $course,
            'year' => $year,
            'registered_at' => $registeredAt
        ]
    ]);
    exit;
} else {
    render_response_page('Registration Successful', 'success', $successMessage, [], [
        'Student ID' => $studentId,
        'Full Name' => $fullName,
        'Email' => $email,
        'Mobile' => $mobile,
        'Course' => $course,
        'Year of Study' => $year,
        'Gender' => $gender,
        'Registered At' => $registeredAt
    ], '../Practical 2/pages/registration.html');
    exit;
}

/**
 * Helper to display HTML response page when form is submitted conventionally (non-AJAX)
 */
function render_response_page($title, $status, $message, $errors = [], $details = [], $backUrl = '#') {
    $isSuccess = ($status === 'success');
    $accentColor = $isSuccess ? '#2e7d32' : '#c62828';
    $badgeBg = $isSuccess ? '#e8f5e9' : '#ffebee';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($title); ?> - StudentHub Portal</title>
        <link rel="stylesheet" href="../Practical 3/style.css">
        <style>
            .result-wrapper {
                max-width: 650px;
                margin: 40px auto;
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 4px 16px rgba(0,0,0,0.12);
                overflow: hidden;
                border-top: 6px solid <?php echo $accentColor; ?>;
            }
            .result-header {
                padding: 24px;
                text-align: center;
                background: <?php echo $badgeBg; ?>;
            }
            .result-header h2 {
                margin: 0;
                color: <?php echo $accentColor; ?>;
                font-size: 24px;
            }
            .result-header p {
                margin: 8px 0 0 0;
                color: #444;
                font-size: 15px;
            }
            .result-body {
                padding: 24px 30px;
            }
            .error-list {
                list-style: none;
                padding: 0;
                margin: 0 0 20px 0;
            }
            .error-list li {
                background: #ffebee;
                color: #c62828;
                padding: 10px 14px;
                border-radius: 6px;
                margin-bottom: 8px;
                font-size: 14px;
                border-left: 4px solid #c62828;
            }
            .details-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 24px;
            }
            .details-table th, .details-table td {
                padding: 10px 12px;
                text-align: left;
                border-bottom: 1px solid #eee;
                font-size: 14px;
            }
            .details-table th {
                width: 35%;
                color: #666;
                font-weight: 600;
            }
            .details-table td {
                color: #222;
                font-weight: 500;
            }
            .btn-back {
                display: inline-block;
                padding: 10px 24px;
                background-color: #c76060;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                transition: background-color 0.2s ease;
            }
            .btn-back:hover {
                background-color: #b84d4d;
            }
            .btn-view-records {
                display: inline-block;
                padding: 10px 24px;
                background-color: #4b6584;
                color: #fff;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 600;
                margin-left: 10px;
                transition: background-color 0.2s ease;
            }
            .btn-view-records:hover {
                background-color: #384f6b;
            }
        </style>
    </head>
    <body>
        <header>
            <section class="top-header">
                <div class="logo"><img src="../Practical 2/Student+Hub.jpg" alt="StudentHub Logo"></div>
                <div class="title"><h1>StudentHub</h1></div>
            </section>
        </header>
        <main>
            <div class="result-wrapper">
                <div class="result-header">
                    <h2><?php echo $isSuccess ? '✓ Registration Successful' : '⚠ Registration Error'; ?></h2>
                    <p><?php echo htmlspecialchars($message); ?></p>
                </div>
                <div class="result-body">
                    <?php if (!empty($errors)): ?>
                        <ul class="error-list">
                            <?php foreach ($errors as $field => $err): ?>
                                <li><strong><?php echo htmlspecialchars(ucfirst($field)); ?>:</strong> <?php echo htmlspecialchars($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <?php if (!empty($details)): ?>
                        <table class="details-table">
                            <?php foreach ($details as $key => $val): ?>
                                <tr>
                                    <th><?php echo htmlspecialchars($key); ?></th>
                                    <td><?php echo htmlspecialchars($val); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="<?php echo htmlspecialchars($backUrl); ?>" class="btn-back">← Back to Registration</a>
                        <a href="view-records.php" class="btn-view-records">📊 View Stored Records</a>
                    </div>
                </div>
            </div>
        </main>
        <footer>
            <p>&copy; 2026 StudentHub Portal</p>
        </footer>
    </body>
    </html>
    <?php
}
