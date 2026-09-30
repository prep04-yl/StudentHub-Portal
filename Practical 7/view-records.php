<?php
/**
 * Practical 7: PHP Form Processing with Server-Side Validation and CSV/JSON File Storage
 * Viewer: Inspect and download stored CSV and JSON records
 */

$registrationsJsonFile = __DIR__ . '/registrations.json';
$registrationsCsvFile  = __DIR__ . '/registrations.csv';
$contactsJsonFile      = __DIR__ . '/contact_messages.json';
$contactsCsvFile       = __DIR__ . '/contact_messages.csv';

// Handle file download request
if (isset($_GET['download'])) {
    $target = $_GET['download'];
    $allowedFiles = [
        'reg_json' => ['path' => $registrationsJsonFile, 'name' => 'registrations.json', 'type' => 'application/json'],
        'reg_csv'  => ['path' => $registrationsCsvFile,  'name' => 'registrations.csv',  'type' => 'text/csv'],
        'con_json' => ['path' => $contactsJsonFile,      'name' => 'contact_messages.json', 'type' => 'application/json'],
        'con_csv'  => ['path' => $contactsCsvFile,       'name' => 'contact_messages.csv',  'type' => 'text/csv'],
    ];

    if (isset($allowedFiles[$target]) && file_exists($allowedFiles[$target]['path'])) {
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $allowedFiles[$target]['type']);
        header('Content-Disposition: attachment; filename="' . $allowedFiles[$target]['name'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($allowedFiles[$target]['path']));
        readfile($allowedFiles[$target]['path']);
        exit;
    }
}

// Load registrations
$registrations = [];
if (file_exists($registrationsJsonFile)) {
    $content = @file_get_contents($registrationsJsonFile);
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $registrations = $decoded;
    }
}

// Load contact messages
$contacts = [];
if (file_exists($contactsJsonFile)) {
    $content = @file_get_contents($contactsJsonFile);
    $decoded = json_decode($content, true);
    if (is_array($decoded)) {
        $contacts = $decoded;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 7 - Stored Records Viewer</title>
    <link rel="stylesheet" href="../Practical 3/style.css">
    <style>
        .records-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .records-card {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 24px;
            margin-bottom: 30px;
        }
        .records-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .records-header h2 {
            margin: 0;
            color: #b84d4d;
            font-size: 20px;
        }
        .badge-count {
            background: #b84d4d;
            color: #fff;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 13px;
            margin-left: 8px;
        }
        .action-links a {
            display: inline-block;
            margin-left: 8px;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            background: #4b6584;
            transition: opacity 0.2s;
        }
        .action-links a:hover {
            opacity: 0.9;
        }
        .action-links a.btn-csv {
            background: #20bf6b;
        }
        .action-links a.btn-json {
            background: #fa8231;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .data-table th, .data-table td {
            padding: 12px 14px;
            border: 1px solid #e5e5e5;
            text-align: left;
        }
        .data-table th {
            background-color: #f7f7f7;
            color: #333;
            font-weight: 600;
        }
        .data-table tr:hover {
            background-color: #fafafa;
        }
        .empty-placeholder {
            text-align: center;
            padding: 30px;
            color: #888;
            font-style: italic;
        }
        .top-nav-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            justify-content: flex-end;
        }
        .nav-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #c76060;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
        }
        .nav-btn:hover {
            background-color: #b84d4d;
        }
        .hash-cell {
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-family: monospace;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <header>
        <section class="top-header">
            <div class="logo"><img src="../Practical 2/Student+Hub.jpg" alt="StudentHub Logo"></div>
            <div class="title"><h1>StudentHub Portal</h1></div>
        </section>
        <section class="bottom-header">
            <nav><div id="navMenu">
                <ul>
                    <li><a href="../Practical 2/pages/home.html">Home</a></li>
                    <li><a href="../Practical 2/pages/registration.html">Register</a></li>
                    <li><a href="../Practical 2/pages/contact.html">Contact</a></li>
                    <li><a class="active" href="view-records.php">Stored Records</a></li>
                </ul>
            </div></nav>
        </section>
    </header>

    <main>
        <div class="records-container">
            <div class="top-nav-bar">
                <a href="../Practical 2/pages/registration.html" class="nav-btn">← Registration Form</a>
                <a href="../Practical 2/pages/contact.html" class="nav-btn">← Contact Form</a>
            </div>

            <!-- Registrations Card -->
            <section class="records-card">
                <div class="records-header">
                    <h2>Student Registrations <span class="badge-count"><?php echo count($registrations); ?></span></h2>
                    <div class="action-links">
                        <?php if (file_exists($registrationsCsvFile)): ?>
                            <a href="?download=reg_csv" class="btn-csv" title="Download CSV">📥 Download CSV</a>
                        <?php endif; ?>
                        <?php if (file_exists($registrationsJsonFile)): ?>
                            <a href="?download=reg_json" class="btn-json" title="Download JSON">📥 Download JSON</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if (empty($registrations)): ?>
                        <div class="empty-placeholder">No registration records found yet. Submit a registration form to view records here.</div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Course</th>
                                    <th>Year</th>
                                    <th>Gender</th>
                                    <th>Password Hash</th>
                                    <th>Registered At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $r): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($r['id'] ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($r['full_name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($r['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($r['mobile'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($r['course'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($r['year'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($r['gender'] ?? '-'); ?></td>
                                        <td class="hash-cell" title="<?php echo htmlspecialchars($r['password_hash'] ?? ''); ?>"><?php echo htmlspecialchars($r['password_hash'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($r['registered_at'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Contact Messages Card -->
            <section class="records-card">
                <div class="records-header">
                    <h2>Contact Messages & Tickets <span class="badge-count"><?php echo count($contacts); ?></span></h2>
                    <div class="action-links">
                        <?php if (file_exists($contactsCsvFile)): ?>
                            <a href="?download=con_csv" class="btn-csv" title="Download CSV">📥 Download CSV</a>
                        <?php endif; ?>
                        <?php if (file_exists($contactsJsonFile)): ?>
                            <a href="?download=con_json" class="btn-json" title="Download JSON">📥 Download JSON</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <?php if (empty($contacts)): ?>
                        <div class="empty-placeholder">No contact messages found yet. Submit a message via the Contact page to view records here.</div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ticket ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Category</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Submitted At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $c): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($c['ticket_id'] ?? '-'); ?></strong></td>
                                        <td><?php echo htmlspecialchars($c['name'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['email'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['category'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($c['subject'] ?? '-'); ?></td>
                                        <td><?php echo nl2br(htmlspecialchars($c['message'] ?? '-')); ?></td>
                                        <td><?php echo htmlspecialchars($c['submitted_at'] ?? '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <p>&copy; 2026 StudentHub Portal - Practical 7</p>
    </footer>
</body>
</html>
