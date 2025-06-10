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

// Format date for display
$timestamp = strtotime($selected_date);
$formatted_date = date('l, F j, Y', $timestamp);

// Validate the selected class and section
$valid_selection = false;
$class_name = '';
$section_name = '';
if ($selected_class_id && $selected_section_id) {
    if ($classResult) {
        $classResult->data_seek(0);
        while ($row = $classResult->fetch_assoc()) {
            if ($row['id'] == $selected_class_id && $row['section_id'] == $selected_section_id) {
                $valid_selection = true;
                $class_name = $row['name'];
                $section_name = $row['section_name'];
                break;
            }
        }
    }
}

// If valid selection, fetch students for the selected class and section
$students = [];
if ($valid_selection) {
    $studentQuery = "SELECT s.user_id, s.admission_number, s.full_name, s.roll_number
                     FROM students s
                     WHERE s.class_id = ? AND s.section_id = ?
                     ORDER BY s.roll_number";
    
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

// Calculate statistics
$present_count = 0;
$absent_count = 0;
$total_count = count($students);

foreach ($students as $student) {
    $status = $existing_attendance[$student['user_id']]['status'] ?? 'present';
    if ($status === 'present') {
        $present_count++;
    } elseif ($status === 'absent') {
        $absent_count++;
    }
}

$attendance_percentage = $total_count > 0 ? round(($present_count / $total_count) * 100, 1) : 0;

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
        
        // Begin transaction
        $conn->begin_transaction();
        
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
        
        // Determine message based on whether attendance was updated or newly created
        $action = $attendance_exists ? "updated" : "marked";
        $message = "Attendance successfully $action for $inserted students.";
        $messageType = 'success';
        
        // Refresh data to show updated attendance
        $existing_attendance = [];
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
        
        // Recalculate statistics
        $present_count = 0;
        $absent_count = 0;
        foreach ($students as $student) {
            $status = $existing_attendance[$student['user_id']]['status'] ?? 'present';
            if ($status === 'present') {
                $present_count++;
            } elseif ($status === 'absent') {
                $absent_count++;
            }
        }
        $attendance_percentage = $total_count > 0 ? round(($present_count / $total_count) * 100, 1) : 0;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollback();
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
        
        .alert-error {
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

        /* Card-based styling similar to attendance_details.php */
        .student-cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .student-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            padding: 1rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border 0.2s, background 0.2s;
            position: relative;
        }

        .student-card.present { 
            border-color: #28a745; 
            background: #28a745 !important; 
            color: #fff; 
        }
        
        .student-card.absent { 
            border-color: #dc3545; 
            background: #dc3545 !important; 
            color: #fff; 
        }

        .student-card .status-label {
            position: absolute;
            top: 1rem;
            right: 1rem;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.2rem 0.8rem;
            border-radius: 16px;
            background: rgba(255,255,255,0.85);
            color: inherit;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .student-card input.form-input { 
            background: rgba(255,255,255,0.85); 
            color: #222; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            margin-top: 0.5rem; 
            width: 100%; 
            padding: 0.4rem;
        }

        .attendance-stats-row {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: stretch;
            margin-bottom: 0.5rem;
            gap: 1.5rem;
        }

        .stat-box {
            flex: 1;
            text-align: center;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-box.present {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .stat-box.absent {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
        }

        .stat-box.percentage {
            background: linear-gradient(135deg, #007bff, #6610f2);
            color: white;
        }

        .stat-box .value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.25rem;
        }

        .stat-box .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .actions-container {
            margin-bottom: 1.5rem;
        }

        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            margin: 0.25rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .student-cards-container { 
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); 
                gap: 0.75rem; 
            }
        }

        @media (max-width: 480px) {
            .student-cards-container { 
                grid-template-columns: 1fr; 
                gap: 0.5rem; 
            }
            .student-card { 
                margin-bottom: 0.5rem; 
            }
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
                <h2 class="card-title">Select Class & Date</h2>
            </div>
            <div class="card-body">
                <form method="GET" action="" id="selectionForm">
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
                    
                    <button type="submit" class="btn btn-primary">Load Students</button>
                </form>
            </div>
        </div>
        
        <?php if ($valid_selection && !empty($students)): ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <?php echo htmlspecialchars($class_name . ' ' . $section_name); ?> - 
                    <?php echo $formatted_date; ?>
                </h2>
            </div>
            <div class="card-body">
                <!-- Statistics -->
                <div class="attendance-stats-row">
                    <div class="stat-box present">
                        <div class="value"><?php echo $present_count; ?></div>
                        <div class="label">Present</div>
                    </div>
                    <div class="stat-box absent">
                        <div class="value"><?php echo $absent_count; ?></div>
                        <div class="label">Absent</div>
                    </div>
                    <div class="stat-box percentage">
                        <div class="value"><?php echo $attendance_percentage; ?>%</div>
                        <div class="label">Attendance</div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="actions-container">
                    <button type="button" class="btn btn-success" onclick="markAllPresent()">Mark All Present</button>
                    <button type="button" class="btn btn-primary" onclick="submitAttendance()">Save Attendance</button>
                </div>
                
                <!-- Search bar -->
                <div style="margin-bottom: 1.2rem;">
                    <input type="text" id="studentSearchInput" placeholder="Search by name or roll number..." 
                           style="width: 100%; max-width: 400px; padding: 0.6rem 1rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;">
                </div>
                
                <form method="POST" action="" id="attendanceForm">
                    <input type="hidden" name="action" value="mark_attendance">
                    <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
                    <input type="hidden" name="section_id" value="<?php echo $selected_section_id; ?>">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                    
                    <div class="student-cards-container">
                        <?php foreach ($students as $student): 
                            $status = $existing_attendance[$student['user_id']]['status'] ?? 'present';
                            $remark = $existing_attendance[$student['user_id']]['remark'] ?? '';
                            $isPresent = $status === 'present';
                            $isAbsent = $status === 'absent';
                        ?>
                        <div class="student-card <?php echo $isPresent ? 'present' : ($isAbsent ? 'absent' : ''); ?>" 
                             data-id="<?php echo $student['user_id']; ?>" 
                             data-name="<?php echo htmlspecialchars(strtolower($student['full_name'])); ?>" 
                             data-roll="<?php echo htmlspecialchars(strtolower($student['roll_number'])); ?>">
                            
                            <!-- Status label at top right -->
                            <div class="status-label">
                                <?php echo $isPresent ? 'Present' : ($isAbsent ? 'Absent' : 'Present'); ?>
                            </div>
                            
                            <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($student['full_name']); ?>
                            </div>
                            <div style="font-size: 0.95rem; color: #222; margin-bottom: 0.5rem;">
                                Roll: <?php echo htmlspecialchars($student['roll_number']); ?>
                            </div>
                            
                            <input type="hidden" name="status[<?php echo $student['user_id']; ?>]" 
                                   value="<?php echo $status; ?>" class="status-input">
                            <input type="text" name="remark[<?php echo $student['user_id']; ?>]" 
                                   class="form-input" value="<?php echo htmlspecialchars($remark); ?>" 
                                   placeholder="Add remark (optional)" style="width:100%; font-size:0.95rem;">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
        </div>
        <?php elseif ($valid_selection): ?>
        <div class="card">
            <div class="card-body">
                <div class="alert alert-info">No students found in this class.</div>
            </div>
        </div>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectionForm = document.getElementById('selectionForm');
    const classSection = document.getElementById('class_section');
    const dateInput = document.getElementById('date');
    
    // Handle class selection change
    classSection.addEventListener('change', function() {
        updateUrl();
    });
    
    // Handle date change
    dateInput.addEventListener('change', function() {
        updateUrl();
    });
    
    function updateUrl() {
        if (classSection.value && dateInput.value) {
            const [class_id, section_id] = classSection.value.split('-');
            const date = dateInput.value;
            window.location.href = `custom-mark-attendance.php?class_id=${class_id}&section_id=${section_id}&date=${date}`;
        }
    }
    
    // Function to update stats display
    function updateStats() {
        let present = 0, absent = 0, total = 0;
        document.querySelectorAll('.student-card').forEach(card => {
            total++;
            const status = card.querySelector('.status-input').value;
            if (status === 'present') present++;
            else if (status === 'absent') absent++;
        });
        
        // Update stats display
        document.querySelector('.stat-box.present .value').textContent = present;
        document.querySelector('.stat-box.absent .value').textContent = absent;
        const percentage = total > 0 ? Math.round((present / total) * 100) : 0;
        document.querySelector('.stat-box.percentage .value').textContent = percentage + '%';
    }

    // Card status toggle logic
    document.querySelectorAll('.student-card').forEach(function(card) {
        const statusInput = card.querySelector('.status-input');
        card.addEventListener('click', function(e) {
            if (e.target.tagName === 'INPUT') return;
            
            let current = statusInput.value;
            let next = current === 'present' ? 'absent' : 'present';
            statusInput.value = next;
            card.classList.remove('present', 'absent');
            card.classList.add(next);
            
            // Update status label text
            const label = card.querySelector('.status-label');
            if (label) label.textContent = next.charAt(0).toUpperCase() + next.slice(1);
            
            // Update stats
            updateStats();
        });
    });
    
    // Search/filter logic
    const searchInput = document.getElementById('studentSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const val = this.value.trim().toLowerCase();
            document.querySelectorAll('.student-card').forEach(function(card) {
                const name = card.getAttribute('data-name') || '';
                const roll = card.getAttribute('data-roll') || '';
                if (val === '' || name.includes(val) || roll.includes(val)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

// Mark all students as present
function markAllPresent() {
    document.querySelectorAll('.student-card').forEach(function(card) {
        const statusInput = card.querySelector('.status-input');
        statusInput.value = 'present';
        card.classList.remove('present', 'absent');
        card.classList.add('present');
        
        // Update status label
        const label = card.querySelector('.status-label');
        if (label) label.textContent = 'Present';
    });
    
    // Update stats
    const total = document.querySelectorAll('.student-card').length;
    document.querySelector('.stat-box.present .value').textContent = total;
    document.querySelector('.stat-box.absent .value').textContent = '0';
    document.querySelector('.stat-box.percentage .value').textContent = '100%';
}

// Submit attendance form
function submitAttendance() {
    document.getElementById('attendanceForm').submit();
}
</script>

</body>
</html>
