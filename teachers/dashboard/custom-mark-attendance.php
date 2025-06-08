<?php
// Include sidebar for authentication and navigation
include 'sidebar.php'; 

// Include database connection
include 'con.php';

// Get teacher ID from session
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Fetch classes assigned to this teacher (where they are class teacher or teach subjects)
$classQuery = "SELECT DISTINCT c.id, c.name, s.id AS section_id, s.name AS section_name 
               FROM classes c 
               JOIN sections s ON c.id = s.class_id 
               LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
               LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
               WHERE s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL
               ORDER BY c.name, s.name";

$stmt = $conn->prepare($classQuery);
$stmt->bind_param("ii", $teacher_user_id, $teacher_user_id);
$stmt->execute();
$classResult = $stmt->get_result();

// Get URL parameters if provided
$selected_class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$selected_section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Validate the selected class and section
$valid_selection = false;
if ($selected_class_id && $selected_section_id) {
    if ($classResult) {
        $classResult->data_seek(0);
        while ($row = $classResult->fetch_assoc()) {
            if ($row['id'] == $selected_class_id && $row['section_id'] == $selected_section_id) {
                $valid_selection = true;
                break;
            }
        }
    }
}

// If valid selection, fetch students for the selected class and section
$students = [];
if ($valid_selection) {
    $studentQuery = "SELECT u.id as student_user_id, u.full_name, e.roll_number 
                    FROM users u 
                    JOIN enrollments e ON u.id = e.student_id 
                    WHERE e.class_id = ? AND e.section_id = ? 
                    AND e.status = 'active'
                    ORDER BY e.roll_number, u.full_name";
    
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param("ii", $selected_class_id, $selected_section_id);
    $stmt->execute();
    $studentResult = $stmt->get_result();
    
    while ($row = $studentResult->fetch_assoc()) {
        $students[] = $row;
    }
}

// Check if attendance already exists for this date
$existing_attendance = [];
if ($valid_selection) {
    $attendanceQuery = "SELECT student_user_id, status, remark 
                       FROM attendance 
                       WHERE class_id = ? AND section_id = ? AND date = ?";
    
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param("iis", $selected_class_id, $selected_section_id, $selected_date);
    $stmt->execute();
    $attendanceResult = $stmt->get_result();
    
    while ($row = $attendanceResult->fetch_assoc()) {
        $existing_attendance[$row['student_user_id']] = [
            'status' => $row['status'],
            'remark' => $row['remark']
        ];
    }
}

// Initialize variables
$message = '';
$messageType = '';

// Handle form submission for marking attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
    try {
        // Validate inputs
        $class_id = isset($_POST['class_id']) ? intval($_POST['class_id']) : 0;
        $section_id = isset($_POST['section_id']) ? intval($_POST['section_id']) : 0;
        $date = isset($_POST['date']) ? $_POST['date'] : '';
        $statuses = isset($_POST['status']) ? $_POST['status'] : [];
        $remarks = isset($_POST['remark']) ? $_POST['remark'] : [];
        
        if (!$class_id || !$section_id || !$date) {
            throw new Exception("Please select a class, section, and date.");
        }
        
        if (empty($statuses)) {
            throw new Exception("No attendance data provided.");
        }
        
        // Check if teacher has access to this class/section
        $accessQuery = "SELECT COUNT(*) as count
                       FROM classes c 
                       JOIN sections s ON c.id = s.class_id 
                       LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = ? 
                       LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
                       WHERE c.id = ? AND s.id = ? AND (s.class_teacher_user_id = ? OR cs.class_id IS NOT NULL)";
        
        $stmt = $conn->prepare($accessQuery);
        $stmt->bind_param("iiii", $teacher_user_id, $class_id, $section_id, $teacher_user_id);
        $stmt->execute();
        $accessResult = $stmt->get_result();
        $accessData = $accessResult->fetch_assoc();
        
        if ($accessData['count'] == 0) {
            throw new Exception("You do not have access to mark attendance for this class.");
        }
        
        // Check if attendance already exists for this date
        $checkQuery = "SELECT COUNT(*) as count FROM attendance 
                      WHERE class_id = ? AND section_id = ? AND date = ?";
        $stmt = $conn->prepare($checkQuery);
        $stmt->bind_param("iis", $class_id, $section_id, $date);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $attendance_exists = ($data['count'] > 0);
        
        // Disable autocommit
        $conn->autocommit(FALSE);
        
        try {
        // Delete existing attendance records if any
        if ($attendance_exists) {
            $deleteQuery = "DELETE FROM attendance 
                           WHERE class_id = ? 
                           AND section_id = ? 
                           AND date = ?";
            
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bind_param("iis", $class_id, $section_id, $date);
            $stmt->execute();
        }
        
        // Insert new attendance records
        $insertQuery = "INSERT INTO attendance (student_user_id, class_id, section_id, date, status, remark, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insertQuery);
        
        $inserted = 0;
        foreach ($statuses as $student_id => $status) {
            $remark = $remarks[$student_id] ?? '';
            
            $stmt->bind_param("iiisss", $student_id, $class_id, $section_id, $date, $status, $remark);
            $stmt->execute();
            $inserted++;
        }
        
        // Commit transaction
        $conn->commit();
            
            // Re-enable autocommit
            $conn->autocommit(TRUE);
        
        // Determine message based on whether attendance was updated or newly created
        $action = $attendance_exists ? "updated" : "marked";
        $message = "Attendance successfully $action for $inserted students.";
        $messageType = 'success';
        
    } catch (Exception $e) {
        // Rollback transaction on error
            try {
                $conn->rollback();
            } catch (Exception $rollbackError) {
                // Ignore rollback errors
            }
            
            // Re-enable autocommit
            try {
                $conn->autocommit(TRUE);
            } catch (Exception $autocommitError) {
                // Ignore autocommit errors
            }
            
            $message = "Error: " . $e->getMessage();
            $messageType = 'error';
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        try {
            $conn->rollback();
        } catch (Exception $rollbackError) {
            // Ignore rollback errors
        }
        
        $message = "Error: " . $e->getMessage();
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Custom Mark Attendance</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 1rem;
            color: #007bff;
            text-decoration: none;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .back-icon {
            width: 16px;
            height: 16px;
            margin-right: 4px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<div class="sidebar-overlay"></div>
<button class="hamburger-btn" type="button" onclick="toggleSidebar()">
    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1 class="header-title">Custom Mark Attendance</h1>
        <span class="header-subtitle">Mark attendance for a specific date</span>
    </header>

    <main class="dashboard-content">
        <a href="attendance.php" class="back-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="back-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Attendance
        </a>
        
        <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Mark Attendance</h2>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="attendanceForm">
                    <input type="hidden" name="action" value="mark_attendance">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    <input type="hidden" name="section_id" value="<?php echo $selected_section_id; ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="class_section">Class & Section</label>
                            <select name="class_section" id="class_section" class="form-select" required>
                                <option value="">Select Class & Section</option>
                                <?php
                                if ($classResult) {
                                    $classResult->data_seek(0);
                                    while ($row = $classResult->fetch_assoc()) {
                                        $value = $row['id'] . '-' . $row['section_id'];
                                        $selected = ($row['id'] == $selected_class_id && $row['section_id'] == $selected_section_id) ? 'selected' : '';
                                        echo "<option value='" . $value . "' " . $selected . ">" .
                                            htmlspecialchars($row['name'] . " " . $row['section_name']) . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" name="date" id="date" class="form-input" 
                                   value="<?php echo htmlspecialchars($selected_date); ?>" required>
                        </div>
                    </div>
                    
                    <div id="studentList" class="mt-4">
                        <?php if ($valid_selection && !empty($students)): ?>
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Roll No</th>
                                        <th>Student Name</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td>
                                                <select name="status[<?php echo $student['student_user_id']; ?>]" class="form-select status-select" required>
                                                    <option value="present" <?php echo (isset($existing_attendance[$student['student_user_id']]) && $existing_attendance[$student['student_user_id']]['status'] == 'present') ? 'selected' : ''; ?>>Present</option>
                                                    <option value="absent" <?php echo (isset($existing_attendance[$student['student_user_id']]) && $existing_attendance[$student['student_user_id']]['status'] == 'absent') ? 'selected' : ''; ?>>Absent</option>
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="remark[<?php echo $student['student_user_id']; ?>]" 
                                                       class="form-input remark-input" 
                                                       value="<?php echo isset($existing_attendance[$student['student_user_id']]) ? htmlspecialchars($existing_attendance[$student['student_user_id']]['remark']) : ''; ?>"
                                                       placeholder="Optional remarks">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Save Attendance</button>
                            </div>
                        <?php elseif ($valid_selection): ?>
                            <div class="alert alert-info">No students found in this class.</div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('attendanceForm');
    const classSection = document.getElementById('class_section');
    
    // Handle class selection change
    classSection.addEventListener('change', function() {
        if (this.value) {
            const [class_id, section_id] = this.value.split('-');
            const date = document.getElementById('date').value;
            window.location.href = `custom-mark-attendance.php?class_id=${class_id}&section_id=${section_id}&date=${date}`;
        }
    });
});
</script>

</body>
</html> 