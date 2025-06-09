<?php include 'sidebar.php'; ?>
<?php include 'con.php';
require_once '../../includes/grading_functions.php';

// Get teacher user id
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Handle marks saving
$save_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assessment_id']) && isset($_POST['marks'])) {
    $assessment_id = intval($_POST['assessment_id']);
    $marks_arr = $_POST['marks'];
    $remarks_arr = $_POST['remark'] ?? [];
    $success = true;
    
    // Fetch subject_id, total_marks, and assessment_type for this assessment
    $subject_id = null;
    $total_marks = null;
    $assessment_type = null;
    $stmt = $conn->prepare("SELECT subject_id, total_marks, assessment_type FROM assessments WHERE id = ?");
    $stmt->bind_param("i", $assessment_id);
    $stmt->execute();
    $stmt->bind_result($subject_id, $total_marks, $assessment_type);
    $stmt->fetch();
    $stmt->close();
    
    if (!$subject_id || !$total_marks || !$assessment_type) {
        $save_message = '<div class="alert alert-danger">Subject, total marks, or assessment type not found for this assessment.</div>';
    } else {
        foreach ($marks_arr as $student_user_id => $marks_obtained) {
            $student_user_id = intval($student_user_id);
            $marks_obtained = is_numeric($marks_obtained) ? floatval($marks_obtained) : null;
            $remark = trim($remarks_arr[$student_user_id] ?? '');
            
            if ($marks_obtained === null) continue;
            
            // Calculate grade using dual grading system
            $grade_info = calculateGrade($marks_obtained, $total_marks, $assessment_type);
            $grade_code = $grade_info['code'];
            
            // Check if record exists
            $checkSql = "SELECT id FROM exam_results WHERE assessment_id = ? AND student_user_id = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('ii', $assessment_id, $student_user_id);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            if ($checkStmt->num_rows > 0) {
                // Update
                $updateSql = "UPDATE exam_results SET marks_obtained=?, grade_code=?, remark=?, subject_id=?, updated_at=NOW() WHERE assessment_id=? AND student_user_id=?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('dsssii', $marks_obtained, $grade_code, $remark, $subject_id, $assessment_id, $student_user_id);
                if (!$updateStmt->execute()) $success = false;
                $updateStmt->close();
            } else {
                // Insert
                $insertSql = "INSERT INTO exam_results (assessment_id, student_user_id, marks_obtained, grade_code, remark, subject_id) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param('iisssi', $assessment_id, $student_user_id, $marks_obtained, $grade_code, $remark, $subject_id);
                if (!$insertStmt->execute()) $success = false;
                $insertStmt->close();
            }
            $checkStmt->close();
        }
        $save_message = $success ? '<div class="alert alert-success">Marks saved successfully using ' . $assessment_type . ' grading system!</div>' : '<div class="alert alert-danger">Error saving marks. Please try again.</div>';
    }
}

// Fetch all class-section pairs assigned to this teacher
$classSectionQuery = "SELECT c.id as class_id, c.name as class_name, s.id as section_id, s.name as section_name
    FROM classes c
    JOIN sections s ON c.id = s.class_id
    LEFT JOIN teacher_subjects ts ON ts.teacher_user_id = $teacher_user_id
    LEFT JOIN class_subjects cs ON cs.subject_id = ts.subject_id AND cs.class_id = c.id
    WHERE s.class_teacher_user_id = $teacher_user_id OR cs.class_id IS NOT NULL
    GROUP BY c.id, s.id
    ORDER BY c.name, s.name";
$classSectionResult = $conn->query($classSectionQuery);
$classSections = [];
while ($row = $classSectionResult->fetch_assoc()) {
    $classSections[] = $row;
}

// Fetch all assessments created by this teacher (across all class-sections)
$assessment_type_filter = $_GET['assessment_type'] ?? '';
$type_condition = '';
if ($assessment_type_filter && in_array($assessment_type_filter, ['SA', 'FA'])) {
    $type_condition = " AND a.assessment_type = '$assessment_type_filter'";
}

$allAssessmentsQuery = "SELECT a.*, c.name as class_name, s.name as section_name FROM assessments a JOIN classes c ON a.class_id = c.id JOIN sections s ON a.section_id = s.id WHERE a.teacher_user_id = $teacher_user_id $type_condition ORDER BY a.date DESC";
$allAssessmentsResult = $conn->query($allAssessmentsQuery);
$allAssessments = [];
foreach ($allAssessmentsResult as $row) {
    $allAssessments[] = $row;
}

// Determine selected class/section/assessment
$selected_assessment_id = $_GET['assessment_id'] ?? '';
$selected_class_id = $_GET['class_id'] ?? '';
$selected_section_id = $_GET['section_id'] ?? '';

// If assessment is selected, update class/section to match
if ($selected_assessment_id) {
    foreach ($allAssessments as $a) {
        if ($a['id'] == $selected_assessment_id) {
            $selected_class_id = $a['class_id'];
            $selected_section_id = $a['section_id'];
            break;
        }
    }
}
// If no selection, default to first available
if (!$selected_class_id && count($classSections)) {
    $selected_class_id = $classSections[0]['class_id'];
    $selected_section_id = $classSections[0]['section_id'];
}
if (!$selected_assessment_id && count($allAssessments)) {
    $selected_assessment_id = $allAssessments[0]['id'];
}

// Fetch students for this class/section
$students = [];
if ($selected_class_id && $selected_section_id) {
    $studentQuery = "SELECT user_id, full_name, roll_number FROM students WHERE class_id = $selected_class_id AND section_id = $selected_section_id ORDER BY roll_number";
    $studentResult = $conn->query($studentQuery);
    while ($row = $studentResult->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch marks for this assessment
$marks = [];
if ($selected_assessment_id) {
    $marksQuery = "SELECT student_user_id, marks_obtained, grade_code, remark FROM exam_results WHERE assessment_id = $selected_assessment_id";
    $marksResult = $conn->query($marksQuery);
    while ($row = $marksResult->fetch_assoc()) {
        $marks[$row['student_user_id']] = $row;
    }
}

// --- Calculate statistics for current and previous assessment ---
$stat_class_avg = $stat_highest = $stat_lowest = $stat_passing_rate = '-';
$trend_class_avg = $trend_highest = $trend_lowest = $trend_passing_rate = null;
if ($selected_class_id && $selected_section_id && $selected_assessment_id) {
    // Get assessment info
    $assessmentInfo = null;
    foreach ($allAssessments as $a) {
        if ($a['id'] == $selected_assessment_id) {
            $assessmentInfo = $a;
            break;
        }
    }
    if ($assessmentInfo) {
        $total_marks = $assessmentInfo['total_marks'];
        $marksArr = [];
        $pass_count = 0;
        $studentQuery = "SELECT user_id FROM students WHERE class_id = $selected_class_id AND section_id = $selected_section_id";
        $studentResult = $conn->query($studentQuery);
        while ($stu = $studentResult->fetch_assoc()) {
            $stu_id = $stu['user_id'];
            $marksQ = "SELECT marks_obtained FROM exam_results WHERE assessment_id = $selected_assessment_id AND student_user_id = $stu_id";
            $marksR = $conn->query($marksQ);
            $m = $marksR->fetch_assoc();
            $stu_marks = $m['marks_obtained'] ?? null;
            if ($stu_marks !== null && $total_marks > 0) {
                $percent = ($stu_marks / $total_marks) * 100;
                $marksArr[] = $percent;
                if ($percent >= 40) $pass_count++;
            }
        }
        if (count($marksArr)) {
            $stat_class_avg = round(array_sum($marksArr) / count($marksArr), 1);
            $stat_highest = round(max($marksArr), 1);
            $stat_lowest = round(min($marksArr), 1);
            $stat_passing_rate = round(($pass_count / count($marksArr)) * 100, 1);
        } else {
            $stat_class_avg = $stat_highest = $stat_lowest = $stat_passing_rate = 0;
        }
        // --- Calculate previous assessment stats for trend ---
        $prevAssessment = null;
        foreach ($allAssessments as $a) {
            if ($a['class_id'] == $selected_class_id && $a['section_id'] == $selected_section_id && $a['id'] != $selected_assessment_id && strtotime($a['date']) < strtotime($assessmentInfo['date'])) {
                if ($prevAssessment === null || strtotime($a['date']) > strtotime($prevAssessment['date'])) {
                    $prevAssessment = $a;
                }
            }
        }
        if ($prevAssessment) {
            $prev_marksArr = [];
            $prev_pass_count = 0;
            $prev_total_marks = $prevAssessment['total_marks'];
            $studentResult = $conn->query($studentQuery);
            while ($stu = $studentResult->fetch_assoc()) {
                $stu_id = $stu['user_id'];
                $marksQ = "SELECT marks_obtained FROM exam_results WHERE assessment_id = {$prevAssessment['id']} AND student_user_id = $stu_id";
                $marksR = $conn->query($marksQ);
                $m = $marksR->fetch_assoc();
                $stu_marks = $m['marks_obtained'] ?? null;
                if ($stu_marks !== null && $prev_total_marks > 0) {
                    $percent = ($stu_marks / $prev_total_marks) * 100;
                    $prev_marksArr[] = $percent;
                    if ($percent >= 40) $prev_pass_count++;
                }
            }
            if (count($prev_marksArr)) {
                $prev_class_avg = round(array_sum($prev_marksArr) / count($prev_marksArr), 1);
                $prev_highest = round(max($prev_marksArr), 1);
                $prev_lowest = round(min($prev_marksArr), 1);
                $prev_passing_rate = round(($prev_pass_count / count($prev_marksArr)) * 100, 1);
                $trend_class_avg = $stat_class_avg - $prev_class_avg;
                $trend_highest = $stat_highest - $prev_highest;
                $trend_lowest = $stat_lowest - $prev_lowest;
                $trend_passing_rate = $stat_passing_rate - $prev_passing_rate;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Marks Entry Manager</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/marks.css">
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
        <h1 class="header-title">Marks Entry Manager</h1>
        <span class="header-subtitle">Online grading and result management</span>
    </header>

    <main class="dashboard-content">
        <!-- Statistics Overview -->
        <section class="stats-grid">
            <div class="stat-card">
                <h3 class="stat-title">Class Average</h3>
                <div class="stat-value">
                    <?php if ($stat_class_avg !== '-'): ?>
                        <?php
                        // Show average as number/total and percent
                        $avg_num = '-';
                        if (isset($marksArr) && count($marksArr)) {
                            $avg_num = round(($stat_class_avg / 100) * $total_marks, 1) . ' / ' . $total_marks;
                        }
                        ?>
                        <?php echo $avg_num; ?> (<?php echo $stat_class_avg; ?>%)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div class="stat-trend <?php echo ($trend_class_avg !== null && $trend_class_avg > 0) ? 'trend-up' : (($trend_class_avg !== null && $trend_class_avg < 0) ? 'trend-down' : ''); ?>">
                    <?php if ($trend_class_avg !== null): ?>
                        <?php if ($trend_class_avg > 0): ?>
                            <span style="color:#22c55e;font-size:1.1em;vertical-align:middle;">&#8593;</span>
                            <span style="color:#22c55e;font-size:0.95em;vertical-align:middle;">+<?php echo round($trend_class_avg, 1); ?>% increase</span>
                        <?php elseif ($trend_class_avg < 0): ?>
                            <span style="color:#ef4444;font-size:1.1em;vertical-align:middle;">&#8595;</span>
                            <span style="color:#ef4444;font-size:0.95em;vertical-align:middle;"><?php echo round($trend_class_avg, 1); ?>% decrease</span>
                        <?php else: ?>
                            <span style="color:#888;font-size:0.95em;">0%</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#888;font-size:0.95em;">—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3 class="stat-title">Highest Score</h3>
                <div class="stat-value">
                    <?php if ($stat_highest !== '-'): ?>
                        <?php
                        $high_num = '-';
                        if (isset($marksArr) && count($marksArr)) {
                            $high_num = round((max($marksArr) / 100) * $total_marks, 1) . ' / ' . $total_marks;
                        }
                        ?>
                        <?php echo $high_num; ?> (<?php echo $stat_highest; ?>%)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div class="stat-trend <?php echo ($trend_highest !== null && $trend_highest > 0) ? 'trend-up' : (($trend_highest !== null && $trend_highest < 0) ? 'trend-down' : ''); ?>">
                    <?php if ($trend_highest !== null): ?>
                        <?php if ($trend_highest > 0): ?>
                            <span style="color:#22c55e;font-size:1.1em;vertical-align:middle;">&#8593;</span>
                            <span style="color:#22c55e;font-size:0.95em;vertical-align:middle;">+<?php echo round($trend_highest, 1); ?>% increase</span>
                        <?php elseif ($trend_highest < 0): ?>
                            <span style="color:#ef4444;font-size:1.1em;vertical-align:middle;">&#8595;</span>
                            <span style="color:#ef4444;font-size:0.95em;vertical-align:middle;"><?php echo round($trend_highest, 1); ?>% decrease</span>
                        <?php else: ?>
                            <span style="color:#888;font-size:0.95em;">0%</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#888;font-size:0.95em;">—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3 class="stat-title">Lowest Score</h3>
                <div class="stat-value">
                    <?php if ($stat_lowest !== '-'): ?>
                        <?php
                        $low_num = '-';
                        if (isset($marksArr) && count($marksArr)) {
                            $low_num = round((min($marksArr) / 100) * $total_marks, 1) . ' / ' . $total_marks;
                        }
                        ?>
                        <?php echo $low_num; ?> (<?php echo $stat_lowest; ?>%)
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div class="stat-trend <?php echo ($trend_lowest !== null && $trend_lowest > 0) ? 'trend-up' : (($trend_lowest !== null && $trend_lowest < 0) ? 'trend-down' : ''); ?>">
                    <?php if ($trend_lowest !== null): ?>
                        <?php if ($trend_lowest > 0): ?>
                            <span style="color:#22c55e;font-size:1.1em;vertical-align:middle;">&#8593;</span>
                            <span style="color:#22c55e;font-size:0.95em;vertical-align:middle;">+<?php echo round($trend_lowest, 1); ?>% increase</span>
                        <?php elseif ($trend_lowest < 0): ?>
                            <span style="color:#ef4444;font-size:1.1em;vertical-align:middle;">&#8595;</span>
                            <span style="color:#ef4444;font-size:0.95em;vertical-align:middle;"><?php echo round($trend_lowest, 1); ?>% decrease</span>
                        <?php else: ?>
                            <span style="color:#888;font-size:0.95em;">0%</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#888;font-size:0.95em;">—</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="stat-card">
                <h3 class="stat-title">Passing Rate</h3>
                <div class="stat-value">
                    <?php if ($stat_passing_rate !== '-'): ?>
                        <?php echo $stat_passing_rate; ?>%
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
                <div class="stat-trend <?php echo ($trend_passing_rate !== null && $trend_passing_rate > 0) ? 'trend-up' : (($trend_passing_rate !== null && $trend_passing_rate < 0) ? 'trend-down' : ''); ?>">
                    <?php if ($trend_passing_rate !== null): ?>
                        <?php if ($trend_passing_rate > 0): ?>
                            <span style="color:#22c55e;font-size:1.1em;vertical-align:middle;">&#8593;</span>
                            <span style="color:#22c55e;font-size:0.95em;vertical-align:middle;">+<?php echo round($trend_passing_rate, 1); ?>% increase</span>
                        <?php elseif ($trend_passing_rate < 0): ?>
                            <span style="color:#ef4444;font-size:1.1em;vertical-align:middle;">&#8595;</span>
                            <span style="color:#ef4444;font-size:0.95em;vertical-align:middle;"><?php echo round($trend_passing_rate, 1); ?>% decrease</span>
                        <?php else: ?>
                            <span style="color:#888;font-size:0.95em;">0%</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#888;font-size:0.95em;">—</span>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Import/Export Buttons -->
        <section class="import-export-buttons">
            <button class="btn btn-secondary" onclick="showImportModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                Import Marks
            </button>
            <button class="btn btn-secondary" onclick="exportMarks()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.5rem;">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Export Marks
            </button>
        </section>

        <!-- Filter and Search -->
        <section class="filter-bar">
            <form method="get" id="filterForm">
                <div class="filter-group">
                    <div class="select-container">
                        <select class="filter-select" name="assessment_type" id="assessmentTypeSelect" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Assessment Types</option>
                            <option value="SA" <?php if ($assessment_type_filter === 'SA') echo 'selected'; ?>>
                                <span class="sa-icon">📊</span> SA (Summative Assessment)
                            </option>
                            <option value="FA" <?php if ($assessment_type_filter === 'FA') echo 'selected'; ?>>
                                <span class="fa-icon">📝</span> FA (Formative Assessment)
                            </option>
                        </select>
                        <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="select-container">
                        <select class="filter-select" name="class_id" id="classSelect" onchange="document.getElementById('filterForm').submit()">
                            <?php foreach ($classSections as $cs): ?>
                                <option value="<?php echo $cs['class_id']; ?>" data-section="<?php echo $cs['section_id']; ?>" <?php if ($selected_class_id == $cs['class_id'] && $selected_section_id == $cs['section_id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($cs['class_name'] . ' - ' . $cs['section_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="select-container">
                        <select class="filter-select" name="assessment_id" id="assessmentSelect" onchange="document.getElementById('filterForm').submit()">
                            <?php foreach ($allAssessments as $a): ?>
                                <option value="<?php echo $a['id']; ?>" data-class="<?php echo $a['class_id']; ?>" data-section="<?php echo $a['section_id']; ?>" data-type="<?php echo $a['assessment_type'] ?? ''; ?>" <?php if ($selected_assessment_id == $a['id']) echo 'selected'; ?>>
                                    <?php 
                                    $type_badge = '';
                                    if (isset($a['assessment_type'])) {
                                        $type_badge = $a['assessment_type'] === 'SA' ? '📊 SA' : '📝 FA';
                                        $type_badge = "[$type_badge] ";
                                    }
                                    echo htmlspecialchars($type_badge . $a['title'] . ' (' . $a['class_name'] . ' - ' . $a['section_name'] . ', ' . date('M d, Y', strtotime($a['date'])) . ')'); 
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <svg class="select-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </div>
                <!-- Hidden inputs to preserve other filter values -->
                <input type="hidden" name="section_id" value="<?php echo $selected_section_id; ?>">
            </form>
        </section>

        <!-- Tabs -->
        <div class="tabs-container">
            <div class="tab active" onclick="changeTab(this, 'marks-entry')">Marks Entry</div>
            <div class="tab" onclick="changeTab(this, 'assessments')">Assessments</div>
            <div class="tab" onclick="changeTab(this, 'performance')">Student Performance</div>
        </div>

        <!-- Marks Entry Section -->
        <section id="marks-entry" class="marks-entry-section">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">
                        <?php 
                        if ($selected_assessment_id && count($allAssessments)) {
                            $a = array_filter($allAssessments, function($x) use ($selected_assessment_id) { return $x['id'] == $selected_assessment_id; });
                            $a = array_values($a);
                            if (isset($a[0])) {
                                $type_badge = '';
                                $grading_info = '';
                                if (isset($a[0]['assessment_type'])) {
                                    $type = $a[0]['assessment_type'];
                                    $type_badge = $type === 'SA' ? '<span class="assessment-type-badge sa-badge">📊 SA</span>' : '<span class="assessment-type-badge fa-badge">📝 FA</span>';
                                    
                                    if ($type === 'SA') {
                                        $grading_info = '<div class="grading-info">SA Grading: A+ (92%+), A (75%+), B (60%+), C (50%+), D (<50%)</div>';
                                    } else {
                                        $grading_info = '<div class="grading-info">FA Grading: A+ (19+/25), A (16+/25), B (13+/25), C (10+/25), D (<10/25)</div>';
                                    }
                                }
                                echo $type_badge . ' ' . htmlspecialchars($a[0]['title']) . ' (' . date('M d, Y', strtotime($a[0]['date'])) . ')';
                                echo $grading_info;
                            }
                        } else {
                            echo 'Select an assessment';
                        }
                        ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if ($save_message) echo $save_message; ?>
                    <?php if ($selected_assessment_id && count($students)): ?>
                    <form method="post" id="marksEntryForm">
                        <input type="hidden" name="assessment_id" value="<?php echo $selected_assessment_id; ?>">
                        <div class="marks-table-container">
                            <table class="marks-table">
                                <thead>
                                    <tr>
                                        <th>Student Name</th>
                                        <th>Roll No.</th>
                                        <th>Marks (<?php echo $a[0]['total_marks']; ?>)</th>
                                        <th>Grade</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): 
                                        $m = $marks[$student['user_id']] ?? null;
                                        $current_grade = '';
                                        if ($m && isset($a[0]['assessment_type']) && $m['marks_obtained'] !== null) {
                                            $grade_info = calculateGrade($m['marks_obtained'], $a[0]['total_marks'], $a[0]['assessment_type']);
                                            $current_grade = $grade_info['code'] . ' (' . $grade_info['description'] . ')';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                        <td>
                                            <input type="number" name="marks[<?php echo $student['user_id']; ?>]" min="0" max="<?php echo $a[0]['total_marks']; ?>" value="<?php echo $m['marks_obtained'] ?? ''; ?>" style="width:80px;" onchange="updateGrade(this, <?php echo $a[0]['total_marks']; ?>, '<?php echo $a[0]['assessment_type'] ?? 'SA'; ?>')">
                                        </td>
                                        <td class="grade-display" id="grade_<?php echo $student['user_id']; ?>">
                                            <?php echo $current_grade; ?>
                                        </td>
                                        <td>
                                            <input type="text" name="remark[<?php echo $student['user_id']; ?>]" value="<?php echo htmlspecialchars($m['remark'] ?? ''); ?>" style="width:150px;">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="marks-actions">
                            <button class="btn btn-primary" type="submit">Save Marks</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <div style="color:#888; padding:2rem; text-align:center;">Select a class-section and assessment to enter marks.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Assessments Section -->
        <section id="assessments" class="assessments-section" style="display: none;">
            <div class="assessment-grid">
                <?php if (count($allAssessments)): ?>
                    <?php foreach ($allAssessments as $assess):
                        // Count students with marks for this assessment
                        $countResult = $conn->query("SELECT COUNT(*) as cnt FROM exam_results WHERE assessment_id = " . intval($assess['id']));
                        $markedCount = $countResult ? $countResult->fetch_assoc()['cnt'] : 0;
                        // Get total students for this class-section
                        $stuResult = $conn->query("SELECT COUNT(*) as cnt FROM students WHERE class_id = " . intval($assess['class_id']) . " AND section_id = " . intval($assess['section_id']));
                        $totalStudents = $stuResult ? $stuResult->fetch_assoc()['cnt'] : 0;
                        $progress = $totalStudents > 0 ? intval(($markedCount / $totalStudents) * 100) : 0;
                        $status = $progress >= 100 ? 'Completed' : ($progress > 0 ? 'Grading' : 'Pending');
                    ?>
                    <div class="assessment-card">
                        <div class="assessment-header">
                            <h3 class="assessment-title"><?php echo htmlspecialchars($assess['title']); ?></h3>
                            <p class="assessment-subtitle"><?php echo htmlspecialchars($assess['class_name'] . ' - ' . $assess['section_name']); ?> | Date: <?php echo date('M d, Y', strtotime($assess['date'])); ?> | Total: <?php echo $assess['total_marks']; ?></p>
                        </div>
                        <div class="assessment-body">
                            <div class="assessment-details">
                                <div class="assessment-detail">
                                    <span class="detail-label">Status</span>
                                    <span class="detail-value">
                                        <span class="assessment-status status-<?php echo strtolower($status); ?>"><?php echo $status; ?></span>
                                    </span>
                                </div>
                                <div class="assessment-detail">
                                    <span class="detail-label">Progress</span>
                                    <span class="detail-value"><?php echo $markedCount; ?>/<?php echo $totalStudents; ?> Students</span>
                                </div>
                            </div>
                            <div class="assessment-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?php echo $progress; ?>%;"></div>
                                </div>
                                <div class="progress-info">
                                    <span><?php echo $progress; ?>% Complete</span>
                                </div>
                            </div>
                        </div>
                        <div class="assessment-footer">
                            <button class="btn btn-primary btn-sm" onclick="window.location.href='marks.php?class_id=<?php echo $assess['class_id']; ?>&section_id=<?php echo $assess['section_id']; ?>&assessment_id=<?php echo $assess['id']; ?>'">Enter Marks</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="color:#888; padding:2rem; text-align:center;">No assessments found.</div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Student Performance Section -->
        <section id="performance" class="performance-section" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Student Performance for Selected Assessment</h2>
                </div>
                <div class="card-body">
                    <div class="performance-overview">
                        <?php
                        if ($selected_class_id && $selected_section_id && $selected_assessment_id) {
                            // Get assessment info
                            $assessmentInfo = null;
                            foreach ($allAssessments as $a) {
                                if ($a['id'] == $selected_assessment_id) {
                                    $assessmentInfo = $a;
                                    break;
                                }
                            }
                            if ($assessmentInfo) {
                                $total_marks = $assessmentInfo['total_marks'];
                                $studentQuery = "SELECT user_id, full_name FROM students WHERE class_id = $selected_class_id AND section_id = $selected_section_id ORDER BY roll_number";
                                $studentResult = $conn->query($studentQuery);
                                $studentPerf = [];
                                while ($stu = $studentResult->fetch_assoc()) {
                                    $stu_id = $stu['user_id'];
                                    // Fetch marks for this student for the selected assessment
                                    $marksQ = "SELECT marks_obtained, grade_code FROM exam_results WHERE assessment_id = $selected_assessment_id AND student_user_id = $stu_id";
                                    $marksR = $conn->query($marksQ);
                                    $m = $marksR->fetch_assoc();
                                    $marks = $m['marks_obtained'] ?? null;
                                    $grade = $m['grade_code'] ?? '';
                                    $percent = ($marks !== null && $total_marks > 0) ? round(($marks / $total_marks) * 100, 2) : 0;
                                    $studentPerf[] = [
                                        'name' => $stu['full_name'],
                                        'marks' => $marks,
                                        'percent' => $percent,
                                        'grade' => $grade,
                                    ];
                                }
                                // Sort by percent desc
                                usort($studentPerf, function($a, $b) { return $b['percent'] <=> $a['percent']; });
                                foreach ($studentPerf as $perf):
                        ?>
                        <div class="student-performance-row">
                            <div class="student-info">
                                <span><?php echo htmlspecialchars($perf['name']); ?></span>
                            </div>
                            <div class="performance-bars">
                                <div class="performance-bar performance-excellent" style="width: <?php echo $perf['percent']; ?>%;"></div>
                            </div>
                            <div class="performance-score"><?php echo $perf['marks'] !== null ? $perf['marks'] . ' / ' . $total_marks : '-'; ?> (<?php echo $perf['percent']; ?>%)</div>
                            <div class="performance-grade" style="margin-left:1rem; color:#4b5563; font-weight:500;">
                                <?php echo $perf['grade']; ?>
                            </div>
                        </div>
                        <?php endforeach; }
                            else {
                                echo '<div style="color:#888; padding:2rem; text-align:center;">Assessment not found.</div>';
                            }
                        } else {
                            echo '<div style="color:#888; padding:2rem; text-align:center;">Select a class-section and assessment to view student performance.</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Import Marks Modal -->
<div class="modal-overlay" id="importModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Import Marks</h3>
            <button class="modal-close" onclick="hideImportModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Assessment</label>
                <select class="form-select" id="importAssessment">
                    <option value="">Select Assessment</option>
                    <option value="term1">Term 1 Examination</option>
                    <option value="midterm">Midterm Assessment</option>
                    <option value="quiz1">Quiz 1</option>
                    <option value="assignment1">Assignment 1</option>
                    <option value="project1">Project 1</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">File <span class="form-required">*</span></label>
                <div class="file-upload" id="fileUploadContainer">
                    <input type="file" id="fileUpload" class="file-input" accept=".csv,.xlsx,.xls">
                    <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <div class="file-upload-text">Drag and drop file here, or click to browse</div>
                    <div class="file-upload-hint">Supported formats: CSV, Excel (XLSX, XLS)</div>
                </div>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="skipHeader" class="form-checkbox" checked>
                    <label for="skipHeader" class="form-check-label">Skip header row</label>
                </div>
                <div class="form-check">
                    <input type="checkbox" id="overwriteExisting" class="form-checkbox">
                    <label for="overwriteExisting" class="form-check-label">Overwrite existing marks</label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Template</label>
                <p class="form-hint">Download a template file to ensure your data is in the correct format.</p>
                <button class="btn btn-secondary btn-sm" onclick="downloadTemplate()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 0.375rem;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download Template
                </button>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideImportModal()">Cancel</button>
            <button class="btn btn-primary" onclick="importMarks()">Import</button>
        </div>
    </div>
</div>

<script>
    // Function to toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');
        const body = document.querySelector('body');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        dashboardContainer.classList.toggle('sidebar-open');
    }
    
    // Function to handle tab changes
    function changeTab(tab, tabId) {
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.tab');
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to selected tab
        tab.classList.add('active');
        
        // Hide all sections
        document.getElementById('marks-entry').style.display = 'none';
        document.getElementById('assessments').style.display = 'none';
        document.getElementById('performance').style.display = 'none';
        
        // Show selected section
        document.getElementById(tabId).style.display = 'block';
    }
    
    // Function to calculate total marks and assign grade
    function calculateTotal(input) {
        const row = input.closest('tr');
        const theory = parseFloat(row.querySelector('input[data-type="theory"]').value) || 0;
        const practical = parseFloat(row.querySelector('input[data-type="practical"]').value) || 0;
        const assignment = parseFloat(row.querySelector('input[data-type="assignment"]').value) || 0;
        
        // Calculate total
        const total = theory + practical + assignment;
        row.querySelector('.total-marks').textContent = total;
        
        // Assign grade based on total
        let grade = '';
        if (total >= 90) grade = 'A+';
        else if (total >= 80) grade = 'A';
        else if (total >= 75) grade = 'B+';
        else if (total >= 70) grade = 'B';
        else if (total >= 65) grade = 'C+';
        else if (total >= 60) grade = 'C';
        else if (total >= 55) grade = 'D+';
        else if (total >= 50) grade = 'D';
        else grade = 'F';
        
        row.querySelector('.grade').textContent = grade;
        
        // Validate input value is within range
        const max = parseInt(input.getAttribute('max'));
        if (input.value > max) {
            input.value = max;
            input.classList.add('invalid-mark');
            setTimeout(() => {
                input.classList.remove('invalid-mark');
            }, 2000);
            calculateTotal(input);
        }
    }
    
    // Function to filter students by name
    function filterStudents() {
        const searchText = document.getElementById('studentSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.marks-table tbody tr');
        
        rows.forEach(row => {
            const studentName = row.querySelector('.student-name span').textContent.toLowerCase();
            if (studentName.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Function to filter performance by name
    function filterPerformance() {
        const searchText = document.getElementById('performanceSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.student-performance-row');
        
        rows.forEach(row => {
            const studentName = row.querySelector('.student-info span').textContent.toLowerCase();
            if (studentName.includes(searchText)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Function to save marks
    function saveMarks() {
        // In a real application, this would submit the form data via AJAX
        alert('Marks saved successfully!');
    }
    
    // Function to reset marks
    function resetMarks() {
        if (confirm('Are you sure you want to reset all marks? This action cannot be undone.')) {
            // In a real application, this would reset the form
            alert('Marks have been reset.');
        }
    }
    
    // Function to change class
    function changeClass() {
        const classSelect = document.getElementById('classSelect');
        const selectedClass = classSelect.value;
        
        // In a real application, this would load the data for the selected class
        document.querySelector('.card-title').textContent = `Term 1 Examination - Mathematics (${selectedClass.replace('class-', 'Class ').toUpperCase()})`;
    }
    
    // Function to change assessment
    function changeAssessment() {
        const assessmentSelect = document.getElementById('assessmentSelect');
        const selectedAssessment = assessmentSelect.value;
        const subjectSelect = document.getElementById('subjectSelect');
        const selectedSubject = subjectSelect.value;
        const classSelect = document.getElementById('classSelect');
        const selectedClass = classSelect.value;
        
        // In a real application, this would load the data for the selected assessment
        let assessmentTitle = '';
        
        switch (selectedAssessment) {
            case 'term1':
                assessmentTitle = 'Term 1 Examination';
                break;
            case 'midterm':
                assessmentTitle = 'Midterm Assessment';
                break;
            case 'quiz1':
                assessmentTitle = 'Quiz 1';
                break;
            case 'assignment1':
                assessmentTitle = 'Assignment 1';
                break;
            case 'project1':
                assessmentTitle = 'Project 1';
                break;
        }
        
        document.querySelector('.card-title').textContent = `${assessmentTitle} - ${selectedSubject.charAt(0).toUpperCase() + selectedSubject.slice(1)} (${selectedClass.replace('class-', 'Class ').toUpperCase()})`;
    }
    
    // Function to show import modal
    function showImportModal() {
        const modal = document.getElementById('importModal');
        modal.classList.add('show');
    }
    
    // Function to hide import modal
    function hideImportModal() {
        const modal = document.getElementById('importModal');
        modal.classList.remove('show');
    }
    
    // Function to download marks template
    function downloadTemplate() {
        // In a real application, this would generate and download a template file
        alert('Template downloaded.');
    }
    
    // Function to import marks
    function importMarks() {
        const fileInput = document.getElementById('fileUpload');
        
        if (!fileInput.files.length) {
            alert('Please select a file to import.');
            return;
        }
        
        // In a real application, this would process the file and import the data
        alert('Marks imported successfully!');
        hideImportModal();
    }
    
    // Function to export marks
    function exportMarks() {
        // In a real application, this would generate and download an export file
        alert('Marks exported successfully!');
    }
    
    // Function to edit an assessment
    function editAssessment(id) {
        // In a real application, this would redirect to the marks entry page for the selected assessment
        // For demo purposes, we'll just switch to the marks entry tab
        changeTab(document.querySelector('.tab'), 'marks-entry');
    }
    
    // Function to view assessment details
    function viewAssessment(id) {
        // In a real application, this would show a modal with assessment details
        alert('Viewing assessment details for ID: ' + id);
    }
    
    // Initialize the file upload container
    document.addEventListener('DOMContentLoaded', function() {
        const fileUploadContainer = document.getElementById('fileUploadContainer');
        const fileInput = document.getElementById('fileUpload');
        
        fileUploadContainer.addEventListener('click', function() {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length) {
                fileUploadContainer.querySelector('.file-upload-text').textContent = fileInput.files[0].name;
            }
        });
        
        // Add drag and drop functionality
        fileUploadContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            fileUploadContainer.style.borderColor = '#667eea';
            fileUploadContainer.style.backgroundColor = '#f9fafb';
        });
        
        fileUploadContainer.addEventListener('dragleave', function() {
            fileUploadContainer.style.borderColor = '#e5e7eb';
            fileUploadContainer.style.backgroundColor = '';
        });
        
        fileUploadContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            fileUploadContainer.style.borderColor = '#e5e7eb';
            fileUploadContainer.style.backgroundColor = '';
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                fileUploadContainer.querySelector('.file-upload-text').textContent = fileInput.files[0].name;
            }
        });
    });
    
    // Function to update grade display in real-time
    function updateGrade(input, totalMarks, assessmentType) {
        const marks = parseFloat(input.value) || 0;
        const row = input.closest('tr');
        const gradeCell = row.querySelector('.grade-display');
        
        if (marks === 0) {
            gradeCell.textContent = '';
            return;
        }
        
        let grade = '';
        let description = '';
        
        if (assessmentType === 'SA') {
            const percentage = (marks / totalMarks) * 100;
            if (percentage >= 92) {
                grade = 'A+';
                description = 'Excellent (92-100%)';
            } else if (percentage >= 75) {
                grade = 'A';
                description = 'Very Good (75-91%)';
            } else if (percentage >= 60) {
                grade = 'B';
                description = 'Good (60-74%)';
            } else if (percentage >= 50) {
                grade = 'C';
                description = 'Average (50-59%)';
            } else {
                grade = 'D';
                description = 'Below Average (0-49%)';
            }
        } else {
            // FA grading based on marks out of 25
            const normalizedMarks = (marks / totalMarks) * 25;
            if (normalizedMarks >= 19) {
                grade = 'A+';
                description = 'Excellent (19-25)';
            } else if (normalizedMarks >= 16) {
                grade = 'A';
                description = 'Very Good (16-18)';
            } else if (normalizedMarks >= 13) {
                grade = 'B';
                description = 'Good (13-15)';
            } else if (normalizedMarks >= 10) {
                grade = 'C';
                description = 'Average (10-12)';
            } else {
                grade = 'D';
                description = 'Below Average (0-9)';
            }
        }
        
        gradeCell.textContent = grade + ' (' + description + ')';
        gradeCell.className = 'grade-display grade-' + grade.toLowerCase().replace('+', 'plus');
    }
    
    // Add page transition animations
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href]:not([target="_blank"])');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip for anchors, javascript: links, etc.
                if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                    return;
                }
                
                e.preventDefault();
                document.body.classList.add('fade-out');
                
                setTimeout(function() {
                    window.location.href = href;
                }, 500); // Match this to the CSS animation duration
            });
        });
    });
</script>
</body>
</html>