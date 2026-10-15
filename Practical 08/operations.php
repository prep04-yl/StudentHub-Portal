<?php
/**
 * Practical 8: Basic Database Operations using PDO Prepared Statements
 */
require_once "db.php";

// 1. Insert a student using PDO prepared statement
function addStudent($pdo, $name, $email, $mobile, $password, $course, $year, $gender) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO students (full_name, email, mobile, password_hash, course, academic_year, gender) 
            VALUES (:name, :email, :mobile, :password, :course, :year, :gender)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':name'     => $name,
        ':email'    => $email,
        ':mobile'   => $mobile,
        ':password' => $hashedPassword,
        ':course'   => $course,
        ':year'     => $year,
        ':gender'   => $gender
    ]);
    return $pdo->lastInsertId();
}

// 2. Fetch all students
function getStudents($pdo) {
    $stmt = $pdo->query("SELECT student_id, full_name, email, course FROM students");
    return $stmt->fetchAll();
}

// 3. Fetch all events
function getEvents($pdo) {
    $stmt = $pdo->query("SELECT event_id, title, event_date, venue FROM events");
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>StudentHub Portal - Directory &amp; Events</title>
    <link rel="stylesheet" href="../Practical 3/style.css">
    <style>
        .p8-container { max-width: 800px; margin: 30px auto; padding: 20px; background: #fff; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
    </style>
</head>
<body>
    <div class="p8-container">
        <h2>Student Directory &amp; Campus Events</h2>
        
        <h3>Students List</h3>
        <table>
            <tr><th>ID</th><th>Name</th><th>Email</th><th>Course</th></tr>
            <?php foreach (getStudents($pdo) as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['student_id']) ?></td>
                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= htmlspecialchars($s['course']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <h3 style="margin-top: 30px;">Events List</h3>
        <table>
            <tr><th>ID</th><th>Title</th><th>Date</th><th>Venue</th></tr>
            <?php foreach (getEvents($pdo) as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['event_id']) ?></td>
                    <td><?= htmlspecialchars($e['title']) ?></td>
                    <td><?= htmlspecialchars($e['event_date']) ?></td>
                    <td><?= htmlspecialchars($e['venue']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
