<!DOCTYPE html>
<html>
<head>
    <title>Practical 13 - AJAX / Fetch API CRUD</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 20px auto; padding: 0 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #eee; }
        input, select, button { padding: 6px; margin: 4px 0; }
    </style>
</head>
<body>
    <h2>Practical 13: Asynchronous CRUD without Page Reload (Fetch API)</h2>

    <!-- Input Form -->
    <form id="student-form" onsubmit="saveStudent(event)">
        <input type="hidden" id="student_id">
        <input type="text" id="roll_no" placeholder="Roll No" required>
        <input type="text" id="name" placeholder="Name" required>
        <select id="department" required>
            <option value="Computer">Computer</option>
            <option value="IT">IT</option>
            <option value="Mechanical">Mechanical</option>
        </select>
        <button type="submit" id="save-btn">Add Student</button>
        <button type="button" onclick="resetForm()">Reset</button>
    </form>

    <!-- Records Table -->
    <table>
        <thead>
            <tr><th>Roll No</th><th>Name</th><th>Department</th><th>Action</th></tr>
        </thead>
        <tbody id="student-table"></tbody>
    </table>

<script>
// 1. GET: Load all records
async function loadStudents() {
    const res = await fetch('api.php');
    const data = await res.json();
    const tbody = document.getElementById('student-table');
    tbody.innerHTML = data.map(s => `
        <tr>
            <td>${s.roll_no}</td>
            <td>${s.name}</td>
            <td>${s.department}</td>
            <td>
                <button onclick="editStudent(${s.id})">Edit</button>
                <button onclick="deleteStudent(${s.id})">Delete</button>
            </td>
        </tr>
    `).join('');
}

// 2. POST / PUT: Save record
async function saveStudent(e) {
    e.preventDefault();
    const id = document.getElementById('student_id').value;
    const payload = {
        roll_no: document.getElementById('roll_no').value,
        name: document.getElementById('name').value,
        department: document.getElementById('department').value
    };

    const method = id ? 'PUT' : 'POST';
    if (id) payload.id = id;

    await fetch('api.php', {
        method: method,
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });

    resetForm();
    loadStudents();
}

// 3. Populate for Edit
async function editStudent(id) {
    const res = await fetch(`api.php?id=${id}`);
    const data = await res.json();
    document.getElementById('student_id').value = data.id;
    document.getElementById('roll_no').value = data.roll_no;
    document.getElementById('name').value = data.name;
    document.getElementById('department').value = data.department;
    document.getElementById('save-btn').textContent = "Update Student";
}

// 4. DELETE
async function deleteStudent(id) {
    if (!confirm('Delete record asynchronously?')) return;
    await fetch(`api.php?id=${id}`, { method: 'DELETE' });
    loadStudents();
}

function resetForm() {
    document.getElementById('student-form').reset();
    document.getElementById('student_id').value = '';
    document.getElementById('save-btn').textContent = "Add Student";
}

loadStudents();
</script>
</body>
</html>
