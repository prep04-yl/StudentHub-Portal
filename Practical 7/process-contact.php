<?php
/**
 * Practical 7: PHP Form Processing with Server-Side Validation and CSV/JSON File Storage
 * Handler: Contact Message Form Processing
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
        header('Location: ../Practical%202/pages/contact.html');
        exit;
    }
}

// Storage paths (in Practical 7 folder)
$storageDir = __DIR__;
$csvFile = $storageDir . DIRECTORY_SEPARATOR . 'contact_messages.csv';
$jsonFile = $storageDir . DIRECTORY_SEPARATOR . 'contact_messages.json';

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
$rawName     = $_POST['contactName'] ?? '';
$rawEmail    = $_POST['contactEmail'] ?? '';
$rawCategory = $_POST['contactCategory'] ?? '';
$rawSubject  = $_POST['contactSubject'] ?? '';
$rawMessage  = $_POST['contactMessage'] ?? '';

// 2. Sanitize inputs
$contactName     = sanitize_input($rawName);
$contactEmail    = filter_var(trim($rawEmail), FILTER_SANITIZE_EMAIL);
$contactCategory = sanitize_input($rawCategory);
$contactSubject  = sanitize_input($rawSubject);
$contactMessage  = sanitize_input($rawMessage);

// 3. Server-side Validation
$errors = [];

// Name: 3-50 chars
if (empty($contactName)) {
    $errors['contactName'] = 'Full name is required.';
} elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $contactName)) {
    $errors['contactName'] = 'Name must contain only letters and spaces (3-50 characters).';
}

// Email: Standard email filter
if (empty($contactEmail)) {
    $errors['contactEmail'] = 'Official email address is required.';
} elseif (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
    $errors['contactEmail'] = 'Please enter a valid email address.';
}

// Category
$validCategories = [
    'General Enquiry',
    'Technical Support',
    'Technical Support / Portal Bug',
    'Academic Support',
    'Academic & Exam Support',
    'Fee Payment',
    'Fee & Scholarship Enquiry'
];
if (empty($contactCategory)) {
    $errors['contactCategory'] = 'Please select a topic / category.';
} elseif (!in_array($contactCategory, $validCategories, true)) {
    $errors['contactCategory'] = 'Selected category is invalid.';
}

// Subject: 3-100 characters
if (empty($contactSubject)) {
    $errors['contactSubject'] = 'Subject is required.';
} elseif (strlen($contactSubject) < 3 || strlen($contactSubject) > 100) {
    $errors['contactSubject'] = 'Subject must be between 3 and 100 characters.';
}

// Message: 10-1000 characters
if (empty($contactMessage)) {
    $errors['contactMessage'] = 'Detailed message is required.';
} elseif (strlen($contactMessage) < 10) {
    $errors['contactMessage'] = 'Message must be at least 10 characters long.';
} elseif (strlen($contactMessage) > 1000) {
    $errors['contactMessage'] = 'Message cannot exceed 1000 characters.';
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
        render_contact_response_page('Message Failed', 'error', 'Validation failed. Please correct the errors below and try again.', $errors, [
            'Name' => $contactName,
            'Email' => $contactEmail,
            'Category' => $contactCategory,
            'Subject' => $contactSubject,
            'Message' => $contactMessage
        ], '../Practical 2/pages/contact.html');
        exit;
    }
}

// 5. Generate record details
$submittedAt = date('Y-m-d H:i:s');
$ticketId = 'TKT-' . strtoupper(substr(uniqid(), -6));

$record = [
    'ticket_id' => $ticketId,
    'name' => $contactName,
    'email' => $contactEmail,
    'category' => $contactCategory,
    'subject' => $contactSubject,
    'message' => $contactMessage,
    'submitted_at' => $submittedAt
];

// 6. Store in CSV format
$csvRow = [
    $ticketId,
    $contactName,
    $contactEmail,
    $contactCategory,
    $contactSubject,
    $contactMessage,
    $submittedAt
];

$csvNeedsHeader = !file_exists($csvFile) || filesize($csvFile) === 0;
$csvFp = @fopen($csvFile, 'a');
if ($csvFp) {
    if ($csvNeedsHeader) {
        fputcsv($csvFp, ['Ticket ID', 'Name', 'Email', 'Category', 'Subject', 'Message', 'Submitted At']);
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
$successMessage = "Thank you, {$contactName}! Your message regarding '{$contactCategory}' has been logged as Ticket #{$ticketId}. Our team will contact you shortly.";

if ($isAjax) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'success',
        'message' => $successMessage,
        'ticket_id' => $ticketId,
        'data' => [
            'name' => $contactName,
            'email' => $contactEmail,
            'category' => $contactCategory,
            'subject' => $contactSubject,
            'submitted_at' => $submittedAt
        ]
    ]);
    exit;
} else {
    render_contact_response_page('Message Received', 'success', $successMessage, [], [
        'Ticket ID' => $ticketId,
        'Full Name' => $contactName,
        'Email Address' => $contactEmail,
        'Category' => $contactCategory,
        'Subject' => $contactSubject,
        'Message' => $contactMessage,
        'Submitted At' => $submittedAt
    ], '../Practical 2/pages/contact.html');
    exit;
}

/**
 * Helper to display HTML response page when form is submitted conventionally (non-AJAX)
 */
function render_contact_response_page($title, $status, $message, $errors = [], $details = [], $backUrl = '#') {
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
                    <h2><?php echo $isSuccess ? '✓ Message Sent Successfully' : '⚠ Message Error'; ?></h2>
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
                                    <td><?php echo nl2br(htmlspecialchars($val)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>

                    <div style="text-align: center; margin-top: 20px;">
                        <a href="<?php echo htmlspecialchars($backUrl); ?>" class="btn-back">← Back to Contact Form</a>
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
