<?php
/**
 * Practical 8: Student & Event Operations using PDO Prepared Statements
 * File: operations.php
 * Demonstrates secure CRUD operations protecting against SQL Injection.
 */

require_once __DIR__ . '/db.php';

/**
 * Register a new student into the students table using prepared statements
 */
function registerStudent(PDO $pdo, array $data): array {
    $sql = "INSERT INTO `students` (`full_name`, `email`, `mobile`, `password_hash`, `course`, `academic_year`, `gender`)
            VALUES (:full_name, :email, :mobile, :password_hash, :course, :academic_year, :gender)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':full_name'     => trim($data['fullName'] ?? ''),
        ':email'         => strtolower(trim($data['email'] ?? '')),
        ':mobile'        => trim($data['mobile'] ?? ''),
        ':password_hash' => password_hash($data['password'] ?? '', PASSWORD_DEFAULT),
        ':course'        => trim($data['course'] ?? ''),
        ':academic_year' => trim($data['year'] ?? ''),
        ':gender'        => trim($data['gender'] ?? 'Other')
    ]);

    return [
        'student_id' => (int) $pdo->lastInsertId(),
        'message'    => 'Student registered successfully'
    ];
}

/**
 * Fetch all available events
 */
function getAllEvents(PDO $pdo): array {
    $stmt = $pdo->query("SELECT `event_id`, `title`, `category`, `event_date`, `venue`, `max_seats` FROM `events` ORDER BY `event_date` ASC");
    return $stmt->fetchAll();
}

/**
 * Register a student for a specific event
 */
function registerForEvent(PDO $pdo, int $studentId, int $eventId): array {
    $sql = "INSERT INTO `registrations` (`student_id`, `event_id`, `status`)
            VALUES (:student_id, :event_id, 'Confirmed')
            ON DUPLICATE KEY UPDATE `status` = 'Confirmed'";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':student_id' => $studentId,
        ':event_id'   => $eventId
    ]);

    return [
        'registration_id' => (int) $pdo->lastInsertId(),
        'message'         => 'Event registered successfully'
    ];
}

/**
 * Get all registrations with joined student and event details
 */
function getEventRegistrations(PDO $pdo): array {
    $sql = "SELECT 
                r.registration_id,
                r.registered_at,
                r.status,
                s.student_id,
                s.full_name AS student_name,
                s.email AS student_email,
                s.course,
                e.event_id,
                e.title AS event_title,
                e.category AS event_category,
                e.event_date,
                e.venue
            FROM `registrations` r
            INNER JOIN `students` s ON r.student_id = s.student_id
            INNER JOIN `events` e   ON r.event_id = e.event_id
            ORDER BY r.registered_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Check if script is run directly to display data as JSON/test
 */
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status'  => 'success',
        'events'  => getAllEvents($pdo),
        'records' => getEventRegistrations($pdo)
    ], JSON_PRETTY_PRINT);
    exit;
}
