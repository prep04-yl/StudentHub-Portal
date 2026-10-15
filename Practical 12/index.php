<?php
// Simple Database Connection
$conn = new mysqli("localhost", "root", "", "studenthub_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg = "";
$uploadDir = "uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Helper for poster upload with validation
function uploadPoster($file, &$error) {
    global $uploadDir;
    if (empty($file['name'])) return null;

    // 1. File Size Validation (Max 2MB)
    if ($file['size'] > 2 * 1024 * 1024) {
        $error = "File exceeds 2MB limit!";
        return false;
    }

    // 2. File Type Validation
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
        $error = "Only JPG and PNG allowed!";
        return false;
    }

    $fileName = time() . "_" . basename($file['name']);
    move_uploaded_file($file['tmp_name'], $uploadDir . $fileName);
    return $fileName;
}

// 1. ADD EVENT
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $date  = $_POST['event_date'];
    $err = "";
    $poster = uploadPoster($_FILES['poster'], $err);

    if ($poster === false) {
        $msg = $err;
    } else {
        $stmt = $conn->prepare("INSERT INTO events (title, event_date, poster) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $date, $poster);
        $msg = $stmt->execute() ? "Event added successfully!" : "Error adding event!";
    }
}

// 2. UPDATE EVENT
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $date  = $_POST['event_date'];
    $err = "";
    $poster = uploadPoster($_FILES['poster'], $err);

    if ($poster === false) {
        $msg = $err;
    } else {
        if ($poster) {
            $stmt = $conn->prepare("UPDATE events SET title=?, event_date=?, poster=? WHERE id=?");
            $stmt->bind_param("sssi", $title, $date, $poster, $id);
        } else {
            $stmt = $conn->prepare("UPDATE events SET title=?, event_date=? WHERE id=?");
            $stmt->bind_param("ssi", $title, $date, $id);
        }
        $msg = $stmt->execute() ? "Event updated successfully!" : "Error updating event!";
    }
}

// 3. DELETE EVENT
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $id);
    $msg = $stmt->execute() ? "Event deleted successfully!" : "Error deleting event!";
}

// 4. FETCH FOR EDIT
$editRow = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $res = $conn->query("SELECT * FROM events WHERE id=$id");
    $editRow = $res->fetch_assoc();
}

$events = $conn->query("SELECT * FROM events ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Practical 12 - Event Management</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: middle; }
        th { background: #eee; }
        .msg { padding: 8px; margin-bottom: 10px; background: #e0f2fe; border: 1px solid #bae6fd; }
        img { width: 50px; height: 50px; object-fit: cover; }
        input, button { padding: 6px; margin: 4px 0; }
    </style>
</head>
<body>
    <h2>Practical 12: Event Management CRUD with Poster Upload</h2>
    
    <?php if ($msg): ?><div class="msg"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

    <!-- Event Form -->
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $editRow['id'] ?? ''; ?>">
        <input type="text" name="title" placeholder="Event Title" required value="<?php echo $editRow['title'] ?? ''; ?>">
        <input type="date" name="event_date" required value="<?php echo $editRow['event_date'] ?? ''; ?>">
        <input type="file" name="poster" accept=".jpg,.png">
        <button type="submit" name="<?php echo $editRow ? 'update' : 'add'; ?>">
            <?php echo $editRow ? 'Update Event' : 'Add Event'; ?>
        </button>
        <?php if ($editRow): ?><a href="index.php">Cancel</a><?php endif; ?>
    </form>

    <!-- Events List -->
    <table>
        <tr>
            <th>Poster</th>
            <th>Title</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
        <?php if ($events && $events->num_rows > 0): ?>
            <?php while ($row = $events->fetch_assoc()): ?>
                <tr>
                    <td>
                        <?php if (!empty($row['poster'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['poster']); ?>" alt="Poster">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                    <td>
                        <a href="index.php?edit=<?php echo $row['id']; ?>">Edit</a> | 
                        <a href="index.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Delete?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="4">No events found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
