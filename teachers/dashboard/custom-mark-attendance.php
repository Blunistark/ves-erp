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
$existing_attendance = [];
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

    // Check if attendance already exists for this date
    $attendanceQuery = "SELECT student_user_id, status 
                       FROM attendance 
                       WHERE class_id = ? AND section_id = ? AND date = ?";
    
    $stmt = $conn->prepare($attendanceQuery);
    $stmt->bind_param("iis", $selected_class_id, $selected_section_id, $selected_date);
    $stmt->execute();
    $attendanceResult = $stmt->get_result();
    
    while ($row = $attendanceResult->fetch_assoc()) {
        $existing_attendance[$row['student_user_id']] = [
            'status' => $row['status']
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
        $insertQuery = "INSERT INTO attendance (student_user_id, class_id, section_id, date, status, created_at)
                       VALUES (?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insertQuery);
        
        $inserted = 0;
        foreach ($statuses as $student_id => $status) {
            $stmt->bind_param("iisss", $student_id, $class_id, $section_id, $date, $status);
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
        
        /* Card-based attendance interface styles */
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
            color: #333;
        }
        
        /* Default state - neutral background with dark text */
        .student-card:not(.present):not(.absent) {
            background: #f8f9fa;
            border-color: #dee2e6;
            color: #333;
        }
        
        .student-card:not(.present):not(.absent) * {
            color: #333 !important;
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
        
        /* Ensure text is visible on colored backgrounds */
        .student-card.present *,
        .student-card.absent * {
            color: #fff !important;
        }
        
        .status-label {
            font-size: 1rem;
            font-weight: 700;
            position: absolute;
            top: 0.7rem;
            right: 0.7rem;
            padding: 0.2rem 0.8rem;
            border-radius: 16px;
            background: rgba(255,255,255,0.95);
            color: #333 !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15);
            z-index: 2;
            white-space: nowrap;
            pointer-events: none;
            text-shadow: none;
        }
        
        .student-card input.form-input { 
            background: rgba(255,255,255,0.85); 
            color: #222; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
            margin-top: 0.5rem; 
            width: 100%; 
        }
        
        /* Statistics row styling */
        .attendance-stats-row {
            display: flex;
            width: 100%;
            justify-content: space-between;
            align-items: stretch;
            margin-bottom: 1.5rem;
            gap: 1.5rem;
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
        
        /* Bulk actions styling */
        .bulk-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
        }
        
        .btn-bulk {
            padding: 0.5rem 1rem;
            border: 1px solid #e5e7eb;
            background: white;
            border-radius: 6px;
            color: #4b5563;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }
        
        .btn-bulk:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }
        
        .btn-bulk.btn-all-present {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        
        .btn-bulk.btn-all-present:hover {
            background: #218838;
        }
        
        /* Actions container */
        .actions-container {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        /* Search styling */
        .search-container {
            margin-bottom: 1.2rem;
            text-align: left;
        }
        
        .search-input {
            width: 100%;
            max-width: 400px;
            padding: 0.6rem 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        @media (max-width: 900px) {
            .student-cards-container { 
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); 
                gap: 0.75rem; 
            }
            .attendance-stats-row { 
                flex-wrap: wrap; 
                gap: 0.75rem; 
            }
            .attendance-stats-row .stat-box { 
                margin: 0 0.25rem; 
                min-width: 90px; 
            }
        }
        
        @media (max-width: 600px) {
            .student-cards-container { 
                grid-template-columns: 1fr; 
                gap: 0.5rem; 
            }
            .student-card { 
                margin-bottom: 0.5rem; 
            }
            .attendance-stats-row { 
                flex-direction: column; 
                gap: 0.5rem; 
            }
            .attendance-stats-row .stat-box { 
                margin: 0; 
                min-width: 0; 
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
                <h2 class="card-title">Mark Attendance</h2>
            </div>
            <div class="card-body">
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
                    
                    <?php if ($valid_selection && !empty($students)): ?>
                    
                    <!-- Search Bar -->
                    <div class="search-container">
                        <input type="text" id="studentSearchInput" placeholder="Search by name or roll number..." class="search-input">
                    </div>
                    
                    <!-- Statistics Row -->
                    <div class="attendance-stats-row">
                        <div class="stat-box present">
                            <div class="value"><?php echo count($students); ?></div>
                            <div class="label">Present</div>
                        </div>
                        <div class="stat-box absent">
                            <div class="value">0</div>
                            <div class="label">Absent</div>
                        </div>
                        <div class="stat-box percentage">
                            <div class="value">100%</div>
                            <div class="label">Attendance</div>
                        </div>
                    </div>
                    
                    <!-- Bulk Actions -->
                    <div class="bulk-actions">
                        <button type="button" class="btn-bulk btn-all-present" onclick="markAllPresent()">Mark All Present</button>
                        <button type="button" class="btn-bulk" onclick="markAllAbsent()">Mark All Absent</button>
                    </div>
                    
                    <!-- Actions Container -->
                    <div class="actions-container">
                        <button type="button" onclick="window.history.back()" class="btn btn-secondary">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Attendance</button>
                    </div>
                    
                    <!-- Student Cards Container -->
                    <div class="student-cards-container">
                        <?php foreach ($students as $student): ?>
                            <?php 
                            $currentStatus = isset($existing_attendance[$student['user_id']]) ? $existing_attendance[$student['user_id']]['status'] : 'present';
                            ?>
                            <div class="student-card <?php echo $currentStatus; ?>" 
                                 data-id="<?php echo $student['user_id']; ?>" 
                                 data-name="<?php echo htmlspecialchars(strtolower($student['full_name'])); ?>" 
                                 data-roll="<?php echo htmlspecialchars(strtolower($student['roll_number'])); ?>">
                                
                                <!-- Status label at top right -->
                                <div class="status-label">
                                    <?php echo ucfirst($currentStatus); ?>
                                </div>
                                
                                <div style="font-weight: 600; font-size: 1.1rem; margin-bottom: 0.25rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                                    <?php echo htmlspecialchars($student['full_name']); ?>
                                </div>
                                
                                <div style="font-size: 1rem; font-weight: 600; margin-bottom: 0.5rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">
                                    Roll: <?php echo htmlspecialchars($student['roll_number']); ?>
                                </div>
                                
                                <input type="hidden" name="status[<?php echo $student['user_id']; ?>]" value="<?php echo $currentStatus; ?>" class="status-input">
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php elseif ($valid_selection): ?>
                        <div class="alert alert-info">No students found in this class.</div>
                    <?php endif; ?>
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
    
    // Function to update statistics display
    function updateStats() {
        let present = 0, absent = 0, total = 0;
        document.querySelectorAll('.student-card').forEach(card => {
            if (card.style.display !== 'none') { // Only count visible cards
                total++;
                const status = card.querySelector('.status-input').value;
                if (status === 'present') present++;
                else if (status === 'absent') absent++;
            }
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
            // Don't toggle if clicking on input fields
            if (e.target.tagName === 'INPUT') return;
            
            let current = statusInput.value;
            let next = current === 'present' ? 'absent' : 'present';
            
            // Update status input value
            statusInput.value = next;
            
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
            // Update stats after filtering
            updateStats();
        });
    }
    
    // Initialize stats
    updateStats();
});

// Bulk action functions
function markAllPresent() {
    document.querySelectorAll('.student-card').forEach(function(card) {
        if (card.style.display !== 'none') { // Only affect visible cards
            const statusInput = card.querySelector('.status-input');
            statusInput.value = 'present';
            card.classList.remove('present', 'absent');
            card.classList.add('present');
            
            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Present';
            }
        }
    });
    
    // Update statistics
    updateStats();
}

function markAllAbsent() {
    document.querySelectorAll('.student-card').forEach(function(card) {
        if (card.style.display !== 'none') { // Only affect visible cards
            const statusInput = card.querySelector('.status-input');
            statusInput.value = 'absent';
            card.classList.remove('present', 'absent');
            card.classList.add('absent');
            
            const label = card.querySelector('.status-label');
            if (label) {
                label.textContent = 'Absent';
            }
        }
    });
    
    // Update statistics
    updateStats();
}

function updateStats() {
    let present = 0, absent = 0, total = 0;
    document.querySelectorAll('.student-card').forEach(card => {
        if (card.style.display !== 'none') { // Only count visible cards
            total++;
            const status = card.querySelector('.status-input').value;
            if (status === 'present') present++;
            else if (status === 'absent') absent++;
        }
    });
    
    // Update stats display
    document.querySelector('.stat-box.present .value').textContent = present;
    document.querySelector('.stat-box.absent .value').textContent = absent;
    const percentage = total > 0 ? Math.round((present / total) * 100) : 0;
    document.querySelector('.stat-box.percentage .value').textContent = percentage + '%';
}
</script>

</body>
</html> 