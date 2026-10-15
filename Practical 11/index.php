<?php
// Simple Database Connection
$conn = new mysqli("localhost", "root", "", "studenthub_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";

// 1. ADD STUDENT
if (isset($_POST['add'])) {
    $roll = $_POST['roll_no'];
    $name = $_POST['name'];
    $dept = $_POST['department'];
    $stmt = $conn->prepare("INSERT INTO students (roll_no, name, department) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $roll, $name, $dept);
    $msg = $stmt->execute() ? "Student added successfully!" : "Error adding student!";
}

// 2. UPDATE STUDENT
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $roll = $_POST['roll_no'];
    $name = $_POST['name'];
    $dept = $_POST['department'];
    $stmt = $conn->prepare("UPDATE students SET roll_no=?, name=?, department=? WHERE id=?");
    $stmt->bind_param("sssi", $roll, $name, $dept, $id);
    $msg = $stmt->execute() ? "Student updated successfully!" : "Error updating student!";
}

// 3. DELETE STUDENT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM students WHERE id=?");
    $stmt->bind_param("i", $id);
    $msg = $stmt->execute() ? "Student deleted successfully!" : "Error deleting student!";
}

// 4. FETCH FOR EDIT
$editRow = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM students WHERE id=$id");
    $editRow = $res->fetch_assoc();
}

// 5. SEARCH & FILTER
$search = $_GET['search'] ?? '';
$filter = $_GET['dept'] ?? '';
$query = "SELECT * FROM students WHERE 1=1";
if (!empty($search)) $query .= " AND (name LIKE '%$search%' OR roll_no LIKE '%$search%')";
if (!empty($filter)) $query .= " AND department='$filter'";
$students = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 11 - Student Management</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        .msg { padding: 8px; margin-bottom: 10px; background: #e0f2fe; border: 1px solid #bae6fd; }
        form { margin-bottom: 15px; }
        input, select, button { padding: 6px; margin: 4px 0; }
    </style>
</head>
<body>
    <h2>Practical 11: Student Management (CRUD, Search & Filter)</h2>
    
    <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

    <!-- Add / Edit Form -->
    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $editRow['id'] ?? ''; ?>">
        <input type="text" name="roll_no" placeholder="Roll No" required value="<?php echo $editRow['roll_no'] ?? ''; ?>">
        <input type="text" name="name" placeholder="Name" required value="<?php echo $editRow['name'] ?? ''; ?>">
        <select name="department" required>
            <option value="">Select Dept</option>
            <option value="Computer" <?php if(($editRow['department']??'')=='Computer') echo 'selected'; ?>>Computer</option>
            <option value="IT" <?php if(($editRow['department']??'')=='IT') echo 'selected'; ?>>IT</option>
            <option value="Mechanical" <?php if(($editRow['department']??'')=='Mechanical') echo 'selected'; ?>>Mechanical</option>
        </select>
        <button type="submit" name="<?php echo $editRow ? 'update' : 'add'; ?>">
            <?php echo $editRow ? 'Update Student' : 'Add Student'; ?>
        </button>
        <?php if ($editRow): ?><a href="index.php">Cancel</a><?php endif; ?>
    </form>

    <!-- Search & Filter Form -->
    <form method="GET">
        <input type="text" name="search" placeholder="Search by name / roll" value="<?php echo htmlspecialchars($search); ?>">
        <select name="dept">
            <option value="">All Departments</option>
            <option value="Computer" <?php if($filter=='Computer') echo 'selected'; ?>>Computer</option>
            <option value="IT" <?php if($filter=='IT') echo 'selected'; ?>>IT</option>
            <option value="Mechanical" <?php if($filter=='Mechanical') echo 'selected'; ?>>Mechanical</option>
        </select>
        <button type="submit">Filter</button>
        <a href="index.php">Reset</a>
    </form>

    <!-- Student Table -->
    <table>
        <tr>
            <th>Roll No</th>
            <th>Name</th>
            <th>Department</th>
            <th>Action</th>
        </tr>
        <?php if ($students && $students->num_rows > 0): ?>
            <?php while ($row = $students->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['roll_no']); ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                    <td>
                        <a href="index.php?edit=<?php echo $row['id']; ?>">Edit</a> | 
                        <a href="index.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No records found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
