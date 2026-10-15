<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost", "root", "", "studenthub_db");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

// 1. GET: Read All or Single
if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $res = $conn->query("SELECT * FROM students WHERE id=$id");
        echo json_encode($res->fetch_assoc());
    } else {
        $res = $conn->query("SELECT * FROM students ORDER BY id DESC");
        $data = [];
        while ($row = $res->fetch_assoc()) $data[] = $row;
        echo json_encode($data);
    }
}

// 2. POST: Create
if ($method === 'POST') {
    $roll = $input['roll_no'] ?? '';
    $name = $input['name'] ?? '';
    $dept = $input['department'] ?? '';
    $stmt = $conn->prepare("INSERT INTO students (roll_no, name, department) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $roll, $name, $dept);
    echo json_encode(["success" => $stmt->execute()]);
}

// 3. PUT: Update
if ($method === 'PUT') {
    $id   = $input['id'] ?? 0;
    $roll = $input['roll_no'] ?? '';
    $name = $input['name'] ?? '';
    $dept = $input['department'] ?? '';
    $stmt = $conn->prepare("UPDATE students SET roll_no=?, name=?, department=? WHERE id=?");
    $stmt->bind_param("sssi", $roll, $name, $dept, $id);
    echo json_encode(["success" => $stmt->execute()]);
}

// 4. DELETE: Delete
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    echo json_encode(["success" => $stmt->execute()]);
}
?>
