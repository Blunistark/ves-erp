<?php
ob_start();
// Include sidebar and database connection
include 'sidebar.php';
include 'con.php';

// Get parameters from URL
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : 0;
$url_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

error_log("Original date from URL: " . $url_date);

// Standardize date format to YYYY-MM-DD for all operations
$timestamp = strtotime($url_date);
$standardized_date = date('Y-m-d', $timestamp);

error_log("Standardized date for operations: " . $standardized_date);

// Formatted date for display purposes
$formatted_date = date('l, F j, Y', $timestamp);

// Get teacher ID from session
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Check if teacher has access to this class/section
$accessQuery = "SELECT COUNT(*) as count, c.name as class_name, s.name as section_name
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

// Check if attendance exists for this date
$attendanceCheckQuery = "SELECT COUNT(DISTINCT a.student_user_id) as count 
                        FROM attendance a
                        WHERE a.class_id = ? 
                        AND a.section_id = ? 
                        AND DATE(a.date) = ?";

$stmt = $conn->prepare($attendanceCheckQuery);
$stmt->bind_param("iis", $class_id, $section_id, $standardized_date);
$stmt->execute();
$attendanceCheckResult = $stmt->get_result();
$attendanceCheckData = $attendanceCheckResult->fetch_assoc();

// Get attendance details
$attendanceQuery = "SELECT a.id, a.student_user_id, a.status, a.remark, s.full_name, s.user_id,
                   COALESCE(s.roll_number, s.admission_number, 'N/A') as roll_number
                   FROM students s
                   LEFT JOIN attendance a ON a.student_user_id = s.user_id 
                   AND a.class_id = ? AND a.section_id = ? AND DATE(a.date) = ?
                   WHERE s.class_id = ? AND s.section_id = ?
                   ORDER BY s.roll_number, s.full_name";

$stmt = $conn->prepare($attendanceQuery);
$stmt->bind_param("iiiii", $class_id, $section_id, $standardized_date, $class_id, $section_id);
$stmt->execute();
$attendanceResult = $stmt->get_result();

// Get statistics
$statsQuery = "SELECT 
                  COUNT(DISTINCT CASE WHEN a.status = 'present' OR (a.status IS NULL AND s.user_id IS NOT NULL) THEN s.user_id END) as present_count,
                  COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN s.user_id END) as absent_count,
                  COUNT(DISTINCT s.user_id) as total_count
               FROM students s
               LEFT JOIN attendance a ON a.student_user_id = s.user_id 
                    AND a.class_id = ? AND a.section_id = ? AND DATE(a.date) = ?
               WHERE s.class_id = ? AND s.section_id = ?";

$stmt = $conn->prepare($statsQuery);
$stmt->bind_param("iiiii", $class_id, $section_id, $standardized_date, $class_id, $section_id);
$stmt->execute();
$statsResult = $stmt->get_result();
$statsData = $statsResult->fetch_assoc();

// Calculate attendance percentage with safe defaults
$present_count = $statsData['present_count'] ?? 0;
$absent_count = $statsData['absent_count'] ?? 0;
$total_count = $statsData['total_count'] ?? 0;

$attendance_percentage = 0;
if ($total_count > 0) {
    $attendance_percentage = round(($present_count / $total_count) * 100, 1);
}

// Handle form submission
$message = '';
$messageType = '';

// Handle mark attendance form submission (before HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'mark_attendance') {
    try {
        $statuses = $_POST['status'] ?? [];
        $remarks = $_POST['remark'] ?? [];
        $class_id = intval($_POST['class_id'] ?? 0);
        $section_id = intval($_POST['section_id'] ?? 0);
        $post_url_date = $_POST['date'] ?? date('Y-m-d'); // Date from the form
        
        // Standardize the date from the POST request
        $post_timestamp = strtotime($post_url_date);
        $post_standardized_date = date('Y-m-d', $post_timestamp);
        
        error_log("Mark attendance form submitted for date: $post_url_date (Standardized to: $post_standardized_date)");
        
        if (empty($statuses)) {
            throw new Exception("No attendance data submitted");
        }
        $conn->begin_transaction();
        $insertQuery = "INSERT INTO attendance (student_user_id, class_id, section_id, date, status, remark) VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertedCount = 0;
        foreach ($statuses as $student_id => $status) {
            if (!in_array($status, ['present', 'absent'])) {
                throw new Exception("Invalid status value for student #$student_id");
            }
            $remark = $remarks[$student_id] ?? '';
            
            error_log("Marking attendance for student $student_id as $status for date $post_standardized_date");
            
            $insertStmt->bind_param("iiisss", $student_id, $class_id, $section_id, $post_standardized_date, $status, $remark);
            $result = $insertStmt->execute();
            if ($result) {
                $insertedCount++;
            } else {
                throw new Exception("Failed to insert attendance for student #$student_id: " . $insertStmt->error);
            }
        }
        $conn->commit();
        error_log("Successfully marked attendance for $insertedCount students for date $post_standardized_date");
        
        $message = "Attendance marked successfully! ($insertedCount records inserted)";
        $messageType = 'success';
        // Redirect using the date from the form, which should be in Y-m-d format
        header("Location: attendance_details.php?class_id=$class_id&section_id=$section_id&date=$post_url_date&message=" . urlencode($message) . "&messageType=$messageType");
        exit;
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        $message = "Error marking attendance: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle update attendance form submission (before HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_attendance') {
    try {
        // Log raw POST data for debugging
        error_log("Raw POST data: " . print_r($_POST, true));

        $statuses = $_POST['status'] ?? [];
        $remarks = $_POST['remark'] ?? [];
        $class_id = intval($_POST['class_id'] ?? 0);
        $section_id = intval($_POST['section_id'] ?? 0);
        $post_url_date = $_POST['date'] ?? date('Y-m-d'); // Date from the form
        
        // Standardize the date from the POST request
        $post_timestamp = strtotime($post_url_date);
        $post_standardized_date = date('Y-m-d', $post_timestamp);
        
        error_log("Update attendance form submitted for date: $post_url_date (Standardized to: $post_standardized_date)");
        error_log("Form data: class_id=$class_id, section_id=$section_id");
        error_log("Statuses: " . print_r($statuses, true));
        error_log("Remarks: " . print_r($remarks, true));
        
        if (empty($statuses)) {
            throw new Exception("No attendance data submitted");
        }
        
        $conn->begin_transaction();
        
        // First, get all existing attendance records for this class, section, and date
        $existingQuery = "SELECT id, student_user_id FROM attendance 
                         WHERE class_id = ? AND section_id = ? AND DATE(date) = ?";
        $existingStmt = $conn->prepare($existingQuery);
        $existingStmt->bind_param("iis", $class_id, $section_id, $post_standardized_date);
        $existingStmt->execute();
        $existingResult = $existingStmt->get_result();
        
        $existingRecords = [];
        while ($row = $existingResult->fetch_assoc()) {
            $existingRecords[$row['student_user_id']] = $row['id'];
        }
        
        // Prepare statements for both insert and update
        $updateQuery = "UPDATE attendance SET status = ?, remark = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        $insertQuery = "INSERT INTO attendance (student_user_id, class_id, section_id, date, status, remark) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        
        $updatedCount = 0;
        $insertedCount = 0;
        
        foreach ($statuses as $student_id => $status) {
            if (!in_array($status, ['present', 'absent'])) {
                throw new Exception("Invalid status value for student #$student_id");
            }
            
            $remark = $remarks[$student_id] ?? '';
            
            error_log("Processing student_id: $student_id with status: $status for date $post_standardized_date");
            
            // Check if this student already has an attendance record
            if (isset($existingRecords[$student_id])) {
                // Update existing record
                $attendance_id = $existingRecords[$student_id];
                error_log("Updating existing record ID: $attendance_id for date $post_standardized_date. Binding params: status='$status', remark='$remark', id=$attendance_id");
                $updateStmt->bind_param("ssi", $status, $remark, $attendance_id);
                $result = $updateStmt->execute();
                if ($result) {
                    $updatedCount++;
                } else {
                    throw new Exception("Failed to update attendance record #$attendance_id: " . $updateStmt->error);
                }
            } else {
                // Insert new record
                error_log("Inserting new record for student: $student_id for date $post_standardized_date. Binding params: student_user_id=$student_id, class_id=$class_id, section_id=$section_id, date='$post_standardized_date', status='$status', remark='$remark'");
                $insertStmt->bind_param("iiisss", $student_id, $class_id, $section_id, $post_standardized_date, $status, $remark);
                $result = $insertStmt->execute();
                if ($result) {
                    $insertedCount++;
                } else {
                    throw new Exception("Failed to insert attendance for student #$student_id: " . $insertStmt->error);
                }
            }
        }
        
        $conn->commit();
        
        error_log("Successfully updated attendance. Updated: $updatedCount, Inserted: $insertedCount for date $post_standardized_date");
        
        $message = "Attendance updated successfully! ($updatedCount records updated, $insertedCount records inserted)";
        $messageType = 'success';
        
        // Redirect using the date from the form, which should be in Y-m-d format
        header("Location: attendance_details.php?class_id=$class_id&section_id=$section_id&date=$post_url_date&message=" . urlencode($message) . "&messageType=$messageType");
        exit;
        
    } catch (Exception $e) {
        error_log("Error in attendance update: " . $e->getMessage());
        if ($conn->inTransaction()) {
            $conn->rollback();
        }
        $message = "Error updating attendance: " . $e->getMessage();
        $messageType = 'error';
    }
}

// Check for message in URL parameters
if (isset($_GET['message']) && !empty($_GET['message'])) {
    $message = $_GET['message'];
    $messageType = $_GET['messageType'] ?? 'info';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Attendance Details</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/attendance.css">
    <style>
        .no-access {
            text-align: center;
            padding: 2rem;
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .no-access h3 {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        .attendance-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .stat-box {
            background-color: white;
            border-radius: 8px;
            padding: 1rem;
            flex: 1;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .stat-box .value {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .stat-box .label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .stat-box.present {
            border-top: 4px solid #28a745;
        }
        
        .stat-box.absent {
            border-top: 4px solid #dc3545;
        }
        
        .stat-box.percentage {
            border-top: 4px solid #007bff;
        }
        
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
        
        .actions-container {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .student-cards-container {
            margin-bottom: 1.5rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1rem;
        }
        .student-card {
            border: 2px solid #eee;
            transition: border 0.2s, background 0.2s;
            position: relative;
            min-width: 0;
            max-width: 100%;
            box-sizing: border-box;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            padding: 1rem;
            padding-top: 2.2rem;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            cursor: pointer;
            margin: 0;
        }
        .student-card.present { border-color: #28a745; background: #28a745 !important; color: #fff; }
        .student-card.absent { border-color: #dc3545; background: #dc3545 !important; color: #fff; }
        .status-label {
            font-size: 0.95rem;
            font-weight: 700;
            position: absolute;
            top: 0.7rem;
            right: 0.7rem;
            padding: 0.15rem 0.7rem;
            border-radius: 16px;
            background: rgba(255,255,255,0.85);
            color: inherit;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            z-index: 2;
            white-space: nowrap;
            pointer-events: none;
        }
        .student-card input.form-input { background: rgba(255,255,255,0.85); color: #222; border: 1px solid #ccc; border-radius: 4px; margin-top: 0.5rem; width: 100%; }
        
        .attendance-stats-row {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: stretch;
            margin-bottom: 0.5rem;
            gap: 1.5rem;
        }
        .attendance-stats-row .stat-box {
            flex: 1;
            margin: 0 0.5rem;
            min-width: 80px;
        }
        .attendance-stats-row .stat-box.present { margin-left: 0; }
        .attendance-stats-row .stat-box.percentage { margin-right: 0; }
        
        @media (max-width: 900px) {
            .student-cards-container { grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 0.75rem; }
            .attendance-stats-row { flex-wrap: wrap; gap: 0.75rem; }
            .attendance-stats-row .stat-box { margin: 0 0.25rem; min-width: 90px; }
        }
        @media (max-width: 600px) {
            .student-cards-container { grid-template-columns: 1fr; gap: 0.5rem; }
            .student-card { margin-bottom: 0.5rem; }
            .attendance-stats-row { flex-direction: column; gap: 0.5rem; }
            .attendance-stats-row .stat-box { margin: 0; min-width: 0; }
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
        <h1 class="header-title">Attendance Details</h1>
        <span class="header-subtitle">View and manage attendance records</span>
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
        
        <?php if ($accessData['count'] == 0): ?>
        <div class="no-access">
            <h3>Access Denied</h3>
            <p>You do not have access to view this class's attendance.</p>
        </div>
        <?php elseif ($attendanceCheckData['count'] == 0): ?>
<?php
    // Fetch students for this class/section
    $students = [];
    $studentQuery = "SELECT user_id, full_name, roll_number FROM students WHERE class_id = ? AND section_id = ? ORDER BY roll_number, full_name";
    $stmt = $conn->prepare($studentQuery);
    $stmt->bind_param("ii", $class_id, $section_id);
    $stmt->execute();
    $studentResult = $stmt->get_result();
    while ($row = $studentResult->fetch_assoc()) {
        $students[] = $row;
    }
?>
<div class="no-access">
    <h3>No Records Found</h3>
    <p>No attendance records found for this class on the selected date.</p>
    <?php if (count($students) > 0): ?>
    <form method="post" action="" id="attendanceForm">
        <input type="hidden" name="action" value="mark_attendance">
        <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
        <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
        <input type="hidden" name="date" value="<?php echo htmlspecialchars($standardized_date); ?>">
        <!-- Search bar -->
        <div style="margin-bottom: 1.2rem; text-align:left;">
            <input type="text" id="studentSearchInput" placeholder="Search by name or roll number..." style="width: 100%; max-width: 400px; padding: 0.6rem 1rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;">
        </div>
        <div class="attendance-stats-row" style="display: flex; width: 100%; justify-content: space-between; align-items: stretch; margin-bottom: 0.5rem; gap: 1.5rem;">
            <div class="stat-box present" style="flex:1; margin:0 0.5rem 0 0;">
                <div class="value"><?php echo count($students); ?></div>
                <div class="label">Present</div>
            </div>
            <div class="stat-box absent" style="flex:1; margin:0 0.5rem;">
                <div class="value">0</div>
                <div class="label">Absent</div>
            </div>
            <div class="stat-box percentage" style="flex:1; margin:0 0 0 0.5rem;">
                <div class="value">100%</div>
                <div class="label">Attendance</div>
            </div>
        </div>
        <div class="actions-container" style="margin-bottom: 1.5rem;">
            <button type="button" onclick="window.history.back()" class="btn btn-secondary">Cancel</button>
            <button type="submit" class="btn btn-primary">Mark Attendance</button>
        </div>
        <div class="student-cards-container" style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: flex-start;">
            <?php foreach ($students as $stu): ?>
            <div class="student-card present" data-id="<?php echo $stu['user_id']; ?>" data-name="<?php echo htmlspecialchars(strtolower($stu['full_name'])); ?>" data-roll="<?php echo htmlspecialchars(strtolower($stu['roll_number'])); ?>" style="width: 260px; min-width: 220px; background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 1rem; display: flex; flex-direction: column; align-items: flex-start; cursor: pointer; border: 2px solid transparent; transition: border 0.2s, background 0.2s; position: relative;">
                <!-- Status label at top right -->
                <div class="status-label" style="position: absolute; top: 1rem; right: 1rem; font-weight: 700; font-size: 1rem; padding: 0.2rem 0.8rem; border-radius: 16px; background: rgba(255,255,255,0.85); color: inherit; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                    Present
                </div>
                <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">
                    <?php echo htmlspecialchars($stu['full_name']); ?>
                </div>
                <div style="font-size: 0.95rem; color: #222; margin-bottom: 0.5rem;">
                    Roll: <?php echo htmlspecialchars($stu['roll_number']); ?>
                </div>
                <input type="hidden" name="status[<?php echo $stu['user_id']; ?>]" value="present" class="status-input">
                <input type="text" name="remark[<?php echo $stu['user_id']; ?>]" class="form-input" value="" placeholder="Add remark (optional)" style="width:100%; font-size:0.95rem;">
            </div>
            <?php endforeach; ?>
        </div>
    </form>
    <script>
    // Card status toggle logic for mark attendance (same as update)
    document.addEventListener('DOMContentLoaded', function() {
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
            });
        });
        // Search/filter logic
        var searchInput = document.getElementById('studentSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var val = this.value.trim().toLowerCase();
                document.querySelectorAll('.student-card').forEach(function(card) {
                    var name = card.getAttribute('data-name') || '';
                    var roll = card.getAttribute('data-roll') || '';
                    if (val === '' || name.includes(val) || roll.includes(val)) {
                        card.style.display = '';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        }
    });
    </script>
    <?php else: ?>
        <div style="margin-top:1rem; color:#888;">No students found in this class/section.</div>
    <?php endif; ?>
</div>
        <?php else: ?>
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">
                    <?php echo htmlspecialchars($accessData['class_name'] . ' ' . $accessData['section_name']); ?> - 
                    <?php echo $formatted_date; ?>
                </h2>
            </div>
            <div class="card-body">
                <form method="post" action="" id="attendanceForm">
                    <input type="hidden" name="action" value="update_attendance">
                    <input type="hidden" name="class_id" value="<?php echo $class_id; ?>">
                    <input type="hidden" name="section_id" value="<?php echo $section_id; ?>">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($standardized_date); ?>">
                    <!-- Search bar -->
                    <div style="margin-bottom: 1.2rem; text-align:left;">
                        <input type="text" id="studentSearchInput" placeholder="Search by name or roll number..." style="width: 100%; max-width: 400px; padding: 0.6rem 1rem; border: 1px solid #ccc; border-radius: 6px; font-size: 1rem;">
                    </div>
                    <!-- Stats row: full width, evenly spaced -->
                    <div class="attendance-stats-row" style="display: flex; width: 100%; justify-content: space-between; align-items: stretch; margin-bottom: 0.5rem; gap: 1.5rem;">
                        <div class="stat-box present" style="flex:1; margin:0 0.5rem 0 0;">
                            <div class="value"><?php echo $present_count; ?></div>
                            <div class="label">Present</div>
                        </div>
                        <div class="stat-box absent" style="flex:1; margin:0 0.5rem;">
                            <div class="value"><?php echo $absent_count; ?></div>
                            <div class="label">Absent</div>
                        </div>
                        <div class="stat-box percentage" style="flex:1; margin:0 0 0 0.5rem;">
                            <div class="value"><?php echo $attendance_percentage; ?>%</div>
                            <div class="label">Attendance</div>
                        </div>
                    </div>
                    <!-- Actions row -->
                    <div class="actions-container" style="margin-bottom: 1.5rem;">
                        <button type="button" onclick="window.history.back()" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Attendance</button>
                    </div>
                    <div class="student-cards-container" style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: flex-start;">
                        <?php
                        $attendanceResult->data_seek(0);
                        while ($row = $attendanceResult->fetch_assoc()):
                            $status = $row['status'] ?? 'present'; // Default to present if no attendance record
                            $id = $row['id'] ?? null;
                            $remark = $row['remark'] ?? '';
                            $isPresent = $status === 'present';
                            $isAbsent = $status === 'absent';
                            
                            // If no attendance record exists, we'll use student_user_id instead of attendance id
                            $formId = $id ?? $row['user_id'];
                            $isNewRecord = $id === null;
                        ?>
                        <div class="student-card <?php echo $isPresent ? 'present' : ($isAbsent ? 'absent' : ''); ?>" 
                             data-id="<?php echo $formId; ?>" 
                             data-name="<?php echo htmlspecialchars(strtolower($row['full_name'])); ?>" 
                             data-roll="<?php echo htmlspecialchars(strtolower($row['roll_number'])); ?>" 
                             style="width: 260px; min-width: 220px; background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.08); padding: 1rem; display: flex; flex-direction: column; align-items: flex-start; cursor: pointer; border: 2px solid transparent; transition: border 0.2s, background 0.2s; position: relative;">
                            <!-- Status label at top right -->
                            <div class="status-label" style="position: absolute; top: 1rem; right: 1rem; font-weight: 700; font-size: 1rem; padding: 0.2rem 0.8rem; border-radius: 16px; background: rgba(255,255,255,0.85); color: inherit; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                                <?php echo $isPresent ? 'Present' : ($isAbsent ? 'Absent' : ''); ?>
                            </div>
                            <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem;">
                                <?php echo htmlspecialchars($row['full_name']); ?>
                            </div>
                            <div style="font-size: 0.95rem; color: #222; margin-bottom: 0.5rem;">
                                Roll: <?php echo htmlspecialchars($row['roll_number']); ?>
                            </div>
                            <?php if ($isNewRecord): ?>
                            <input type="hidden" name="status[<?php echo $formId; ?>]" value="<?php echo $status; ?>" class="status-input">
                            <input type="text" name="remark[<?php echo $formId; ?>]" class="form-input" value="" placeholder="Add remark (optional)" style="width:100%; font-size:0.95rem;">
                            <?php else: ?>
                            <input type="hidden" name="status[<?php echo $formId; ?>]" value="<?php echo $status; ?>" class="status-input">
                            <input type="text" name="remark[<?php echo $formId; ?>]" class="form-input" value="<?php echo htmlspecialchars($remark); ?>" placeholder="Add remark (optional)" style="width:100%; font-size:0.95rem;">
                            <?php endif; ?>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </form>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    if (e.target.tagName === 'INPUT') return; // Don't toggle if clicking on input fields
                    
                    let current = statusInput.value;
                    let next = current === 'present' ? 'absent' : 'present';
                    
                    // Log the change before updating
                    console.log('Card clicked. Student ID:', card.getAttribute('data-id'), 'Current status:', current, 'Next status:', next);
                    
                    // Update status input value
                    statusInput.value = next;
                    
                    // Log the value after updating
                    console.log('Status input value after update:', statusInput.value);
                    
                    // Update card classes
                    card.classList.remove('present', 'absent');
                    card.classList.add(next);
                    
                    // Update status label text
                    const label = card.querySelector('.status-label');
                    if (label) {
                        label.textContent = next.charAt(0).toUpperCase() + next.slice(1);
                    }
                    
                    // Update statistics
                    updateStats();
                });
            });

            // Search/filter logic
            var searchInput = document.getElementById('studentSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    var val = this.value.trim().toLowerCase();
                    document.querySelectorAll('.student-card').forEach(function(card) {
                        var name = card.getAttribute('data-name') || '';
                        var roll = card.getAttribute('data-roll') || '';
                        if (val === '' || name.includes(val) || roll.includes(val)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }

            // Initialize stats on page load
            updateStats();

            // Add a submit listener to the form to copy values before submission
            const attendanceForm = document.getElementById('attendanceForm');
            if (attendanceForm) {
                attendanceForm.addEventListener('submit', function(event) {
                    // REVERTED: PREVENT DEFAULT SUBMISSION TEMPORARILY FOR DEBUGGING
                    // event.preventDefault();

                    // Clear any previously added temporary inputs
                    document.querySelectorAll('.temp-status-input').forEach(input => input.remove());

                    // Iterate over all status inputs and create temporary hidden inputs
                    document.querySelectorAll('.status-input').forEach(input => {
                        const tempInput = document.createElement('input');
                        tempInput.type = 'hidden';
                        tempInput.name = input.name; // Keep the original name like status[id]
                        tempInput.value = input.value;
                        tempInput.classList.add('temp-status-input'); // Add a class to easily find and remove them
                        attendanceForm.appendChild(tempInput);
                        console.log('Preparing for submission:', tempInput.name, tempInput.value);
                    });
                    // The form will now submit, including these temporary inputs
                });
            }
        });
        </script>
        <?php endif; ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to toggle sidebar (defined in sidebar.php)
    window.toggleSidebar = function() {
        const sidebar = document.getElementById('sidebar');
        const body = document.body;
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
    };
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.style.display = 'none';
            }, 500);
        }, 5000);
    });
});
</script>
</body>
</html> 