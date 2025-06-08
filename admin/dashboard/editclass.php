<?php
require_once 'con.php';
include 'sidebar.php';

$classId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = '';
$class = [
    'name' => '',
    'academic_year' => '',
    'department' => '',
    'description' => '',
    'teacher_id' => '',
    'subject' => ''
];
if ($classId) {
    $stmt = $conn->prepare('SELECT name, academic_year, department, description, teacher_id, subject FROM classes WHERE id=?');
    $stmt->bind_param('i', $classId);
    $stmt->execute();
    $stmt->bind_result($class['name'], $class['academic_year'], $class['department'], $class['description'], $class['teacher_id'], $class['subject']);
    $stmt->fetch();
    $stmt->close();
}
// Fetch teachers for dropdown
$teachers = [];
$res = $conn->query("SELECT id, full_name FROM teachers WHERE status='active' ORDER BY full_name");
while ($row = $res->fetch_assoc()) {
    $teachers[] = $row;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $classId) {
    $name = trim($_POST['className'] ?? '');
    $academicYear = trim($_POST['academicYear'] ?? '');
    $department = trim($_POST['classDepartment'] ?? '');
    $description = trim($_POST['classDescription'] ?? '');
    $teacherId = intval($_POST['teacherId'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $stmt = $conn->prepare('UPDATE classes SET name=?, academic_year=?, department=?, description=?, teacher_id=?, subject=? WHERE id=?');
    $stmt->bind_param('ssssisi', $name, $academicYear, $department, $description, $teacherId, $subject, $classId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        $message = "<div class='success'>Class updated successfully.</div>";
        $class = [
            'name' => $name,
            'academic_year' => $academicYear,
            'department' => $department,
            'description' => $description,
            'teacher_id' => $teacherId,
            'subject' => $subject
        ];
    } else {
        $message = "<div class='error'>Failed to update class.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/createclasses.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Edit Class</h1>
            <span class="header-path">Dashboard > Classes > Edit</span>
        </header>
        <main class="dashboard-content">
            <?php if ($message) echo $message; ?>
            <form method="POST" action="editclass.php?id=<?= $classId ?>">
                <div class="form-container">
                    <div class="main-form">
                        <div class="form-section">
                            <h2 class="section-title">Basic Information</h2>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="className" class="form-label required">Class Name</label>
                                    <input type="text" id="className" name="className" class="form-input" required value="<?= htmlspecialchars($class['name']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="academicYear" class="form-label required">Academic Year</label>
                                    <input type="text" id="academicYear" name="academicYear" class="form-input" required value="<?= htmlspecialchars($class['academic_year']) ?>">
                                </div>
                                <div class="form-group">
                                    <label for="classDepartment" class="form-label">Department</label>
                                    <input type="text" id="classDepartment" name="classDepartment" class="form-input" value="<?= htmlspecialchars($class['department']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="classDescription" class="form-label">Description</label>
                                    <textarea id="classDescription" name="classDescription" class="form-textarea" rows="4"><?= htmlspecialchars($class['description']) ?></textarea>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="teacherId" class="form-label">Class Teacher</label>
                                    <select id="teacherId" name="teacherId" class="form-select">
                                        <option value="">Select Teacher</option>
                                        <?php foreach ($teachers as $teacher): ?>
                                            <option value="<?= $teacher['id'] ?>" <?= $class['teacher_id']==$teacher['id']?'selected':'' ?>><?= htmlspecialchars($teacher['full_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="subject" class="form-label">Subject</label>
                                    <input type="text" id="subject" name="subject" class="form-input" value="<?= htmlspecialchars($class['subject']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }
    </script>
</body>
</html> 