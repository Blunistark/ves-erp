<?php include 'sidebar.php'; ?>
<?php include 'con.php'; ?>
<?php
// Get teacher user id
$teacher_user_id = $_SESSION['user_id'] ?? 0;

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
// Use the first class-section as the default
$selected_class_id = $classSections[0]['class_id'] ?? '';
$selected_section_id = $classSections[0]['section_id'] ?? '';

// Fetch unique assessment types for this teacher's class-section(s)
$typeQuery = "SELECT DISTINCT type FROM assessments WHERE teacher_user_id = $teacher_user_id AND class_id = $selected_class_id AND section_id = $selected_section_id";
$typeResult = $conn->query($typeQuery);
$assessmentTypes = [];
while ($row = $typeResult->fetch_assoc()) {
    $assessmentTypes[] = $row['type'];
}
$selected_type = $_GET['assessment_type'] ?? 'all';

// Handle teacher note save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_note_student_id'])) {
    $note_student_id = intval($_POST['teacher_note_student_id']);
    $note_text = trim($_POST['teacher_note_text'] ?? '');
    $note_class_id = $selected_class_id;
    $note_section_id = $selected_section_id;
    // Upsert note
    $checkQ = "SELECT * FROM teacher_notes WHERE student_user_id=? AND teacher_user_id=? AND class_id=? AND section_id=?";
    $checkStmt = $conn->prepare($checkQ);
    $checkStmt->bind_param('iiii', $note_student_id, $teacher_user_id, $note_class_id, $note_section_id);
    $checkStmt->execute();
    $checkStmt->store_result();
    if ($checkStmt->num_rows > 0) {
        $updateQ = "UPDATE teacher_notes SET note=?, updated_at=NOW() WHERE student_user_id=? AND teacher_user_id=? AND class_id=? AND section_id=?";
        $updateStmt = $conn->prepare($updateQ);
        $updateStmt->bind_param('siiii', $note_text, $note_student_id, $teacher_user_id, $note_class_id, $note_section_id);
        $updateStmt->execute();
        $updateStmt->close();
    } else {
        $insertQ = "INSERT INTO teacher_notes (student_user_id, teacher_user_id, class_id, section_id, note, updated_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $insertStmt = $conn->prepare($insertQ);
        $insertStmt->bind_param('iiiis', $note_student_id, $teacher_user_id, $note_class_id, $note_section_id, $note_text);
        $insertStmt->execute();
        $insertStmt->close();
    }
    $checkStmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Performance Tracker</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/performance.css">
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
        <h1 class="header-title">Student Performance Tracker</h1>
        <p class="header-subtitle">Monitor student grades and progress</p>
    </header>

    <main class="dashboard-content">
        <!-- Filters -->
        <div class="filters">
            <div class="filter-item">
                <label for="assessmentSelect" class="form-label">Assessment Type</label>
                <form method="get" id="assessmentTypeForm" style="display:inline;">
                <select id="assessmentSelect" name="assessment_type" class="form-select" onchange="document.getElementById('assessmentTypeForm').submit()">
                    <option value="all" <?php if ($selected_type == 'all') echo 'selected'; ?>>All Assessments</option>
                    <?php foreach ($assessmentTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type); ?>" <?php if ($selected_type == $type) echo 'selected'; ?>><?php echo ucfirst($type); ?></option>
                    <?php endforeach; ?>
                </select>
                </form>
            </div>
        </div>

        <!-- Performance Stats -->
        <div class="stats-grid">
            <?php
            $stat_class_avg = $stat_highest = $stat_lowest = $stat_passing_rate = '-';
            if ($selected_class_id && $selected_section_id) {
                // Build assessment type filter
                $typeFilter = ($selected_type !== 'all') ? "AND type = '" . $conn->real_escape_string($selected_type) . "'" : '';
                // Get all relevant assessments
                $assessmentsQ = "SELECT id, total_marks FROM assessments WHERE teacher_user_id = $teacher_user_id AND class_id = $selected_class_id AND section_id = $selected_section_id $typeFilter";
                $assessmentsR = $conn->query($assessmentsQ);
                $assessmentIds = [];
                $assessmentMarks = [];
                while ($a = $assessmentsR->fetch_assoc()) {
                    $assessmentIds[] = $a['id'];
                    $assessmentMarks[$a['id']] = $a['total_marks'];
                }
                if (count($assessmentIds)) {
                    $marksArr = [];
                    $pass_count = 0;
                    $total_count = 0;
                    $studentQuery = "SELECT user_id FROM students WHERE class_id = $selected_class_id AND section_id = $selected_section_id";
                    $studentResult = $conn->query($studentQuery);
                    while ($stu = $studentResult->fetch_assoc()) {
                        $stu_id = $stu['user_id'];
                        foreach ($assessmentIds as $aid) {
                            $marksQ = "SELECT marks_obtained FROM exam_results WHERE assessment_id = $aid AND student_user_id = $stu_id";
                            $marksR = $conn->query($marksQ);
                            $m = $marksR->fetch_assoc();
                            $marks = $m['marks_obtained'] ?? null;
                            $total_marks = $assessmentMarks[$aid];
                            if ($marks !== null && $total_marks > 0) {
                                $percent = ($marks / $total_marks) * 100;
                                $marksArr[] = $percent;
                                if ($percent >= 40) $pass_count++;
                                $total_count++;
                            }
                        }
                    }
                    if (count($marksArr)) {
                        $stat_class_avg = round(array_sum($marksArr) / count($marksArr), 2) . '%';
                        $stat_highest = round(max($marksArr), 2) . '%';
                        $stat_lowest = round(min($marksArr), 2) . '%';
                        $stat_passing_rate = $total_count ? round(($pass_count / $total_count) * 100, 2) . '%' : '0%';
                    } else {
                        $stat_class_avg = $stat_highest = $stat_lowest = $stat_passing_rate = '0%';
                    }
                } else {
                    $stat_class_avg = $stat_highest = $stat_lowest = $stat_passing_rate = '0%';
                }
            }
            ?>
            <div class="stat-card">
                <div class="stat-title">Class Average</div>
                <div class="stat-value"><?php echo $stat_class_avg; ?></div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-good" style="width: <?php echo is_numeric($stat_class_avg) ? $stat_class_avg : '0'; ?>%"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Highest Score</div>
                <div class="stat-value"><?php echo $stat_highest; ?></div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-excellent" style="width: <?php echo is_numeric($stat_highest) ? $stat_highest : '0'; ?>%"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Lowest Score</div>
                <div class="stat-value"><?php echo $stat_lowest; ?></div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-needs-improvement" style="width: <?php echo is_numeric($stat_lowest) ? $stat_lowest : '0'; ?>%"></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-title">Pass Rate</div>
                <div class="stat-value"><?php echo $stat_passing_rate; ?></div>
                <div class="progress-bar-container">
                    <div class="progress-bar progress-excellent" style="width: <?php echo is_numeric($stat_passing_rate) ? $stat_passing_rate : '0'; ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Performance Distribution Chart -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Performance Distribution</h2>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <?php
                    // Calculate distribution for selected type
                    $dist = [0,0,0,0,0]; // 0-59, 60-69, 70-79, 80-89, 90-100
                    if ($selected_class_id && $selected_section_id) {
                        $typeFilter = ($selected_type !== 'all') ? "AND type = '" . $conn->real_escape_string($selected_type) . "'" : '';
                        $assessmentsQ = "SELECT id, total_marks FROM assessments WHERE teacher_user_id = $teacher_user_id AND class_id = $selected_class_id AND section_id = $selected_section_id $typeFilter";
                        $assessmentsR = $conn->query($assessmentsQ);
                        $assessmentIds = [];
                        $assessmentMarks = [];
                        while ($a = $assessmentsR->fetch_assoc()) {
                            $assessmentIds[] = $a['id'];
                            $assessmentMarks[$a['id']] = $a['total_marks'];
                        }
                        $studentQuery = "SELECT user_id FROM students WHERE class_id = $selected_class_id AND section_id = $selected_section_id";
                        $studentResult = $conn->query($studentQuery);
                        while ($stu = $studentResult->fetch_assoc()) {
                            $stu_id = $stu['user_id'];
                            foreach ($assessmentIds as $aid) {
                                $marksQ = "SELECT marks_obtained FROM exam_results WHERE assessment_id = $aid AND student_user_id = $stu_id";
                                $marksR = $conn->query($marksQ);
                                $m = $marksR->fetch_assoc();
                                $marks = $m['marks_obtained'] ?? null;
                                $total_marks = $assessmentMarks[$aid];
                                if ($marks !== null && $total_marks > 0) {
                                    $percent = ($marks / $total_marks) * 100;
                                    if ($percent < 60) $dist[0]++;
                                    else if ($percent < 70) $dist[1]++;
                                    else if ($percent < 80) $dist[2]++;
                                    else if ($percent < 90) $dist[3]++;
                                    else $dist[4]++;
                                }
                            }
                        }
                    }
                    $maxDist = max($dist) ?: 1;
                    $heights = array_map(function($v) use ($maxDist) { return intval(($v/$maxDist)*75); }, $dist); // max 75% height
                    ?>
                    <div class="bar-chart">
                        <div class="bar" style="height: <?php echo $heights[0]; ?>%; background-color: #ef4444;">
                            <div class="bar-value"><?php echo $dist[0]; ?></div>
                            <div class="bar-label">0-59%</div>
                        </div>
                        <div class="bar" style="height: <?php echo $heights[1]; ?>%; background-color: #f59e0b;">
                            <div class="bar-value"><?php echo $dist[1]; ?></div>
                            <div class="bar-label">60-69%</div>
                        </div>
                        <div class="bar" style="height: <?php echo $heights[2]; ?>%; background-color: #3b82f6;">
                            <div class="bar-value"><?php echo $dist[2]; ?></div>
                            <div class="bar-label">70-79%</div>
                        </div>
                        <div class="bar" style="height: <?php echo $heights[3]; ?>%; background-color: #10b981;">
                            <div class="bar-value"><?php echo $dist[3]; ?></div>
                            <div class="bar-label">80-89%</div>
                        </div>
                        <div class="bar" style="height: <?php echo $heights[4]; ?>%; background-color: #10b981;">
                            <div class="bar-value"><?php echo $dist[4]; ?></div>
                            <div class="bar-label">90-100%</div>
                        </div>
                    </div>
                </div>
                <div class="chart-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #ef4444;"></div>
                        <span>Needs Improvement (0-59%)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #f59e0b;"></div>
                        <span>Average (60-69%)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #3b82f6;"></div>
                        <span>Good (70-79%)</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #10b981;"></div>
                        <span>Excellent (80-100%)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comparison Card -->
        <div class="comparison-card">
            <div class="comparison-item">
                <div class="circular-progress">
                    <svg viewBox="0 0 100 100">
                        <circle class="circular-bg" cx="50" cy="50" r="45"></circle>
                        <circle class="circular-progress-value" cx="50" cy="50" r="45" style="stroke-dasharray: 283; stroke-dashoffset: 62;"></circle>
                    </svg>
                    <div class="circular-text">78%</div>
                </div>
                <div class="comparison-label">Current Average</div>
                <div class="comparison-trend trend-up">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    Up 3% from previous
                </div>
            </div>
            <div class="comparison-item">
                <div class="circular-progress">
                    <svg viewBox="0 0 100 100">
                        <circle class="circular-bg" cx="50" cy="50" r="45"></circle>
                        <circle class="circular-progress-value" cx="50" cy="50" r="45" style="stroke-dasharray: 283; stroke-dashoffset: 73;"></circle>
                    </svg>
                    <div class="circular-text">75%</div>
                </div>
                <div class="comparison-label">Previous Term</div>
                <div class="comparison-trend trend-up">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    Up 2% from before
                </div>
            </div>
            <div class="comparison-item">
                <div class="circular-progress">
                    <svg viewBox="0 0 100 100">
                        <circle class="circular-bg" cx="50" cy="50" r="45"></circle>
                        <circle class="circular-progress-value" cx="50" cy="50" r="45" style="stroke-dasharray: 283; stroke-dashoffset: 90;"></circle>
                    </svg>
                    <div class="circular-text">71%</div>
                </div>
                <div class="comparison-label">School Average</div>
                <div class="comparison-trend trend-up">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                    Your class is 7% higher
                </div>
            </div>
        </div>

        <!-- Student Performance Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Student Performance</h2>
            </div>
            <div class="card-body">
                <div class="tabs">
                    <div class="tab active" data-tab="all">All Students</div>
                    <div class="tab" data-tab="top">Top Performers</div>
                    <div class="tab" data-tab="risk">At Risk</div>
                </div>
                
                <div class="tab-content active" id="all-content">
                    <div style="overflow-x: auto;">
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Student Name</th>
                                    <th style="width: 15%;">Overall Grade</th>
                                    <th style="width: 15%;">Last Assessment</th>
                                    <th style="width: 15%;">Tests Average</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            // Fetch students for this class-section
                            $students = [];
                            if ($selected_class_id && $selected_section_id) {
                                $studentQuery = "SELECT user_id, full_name, roll_number FROM students WHERE class_id = $selected_class_id AND section_id = $selected_section_id ORDER BY roll_number";
                                $studentResult = $conn->query($studentQuery);
                                while ($row = $studentResult->fetch_assoc()) {
                                    $students[] = $row;
                                }
                            }
                            foreach ($students as $stu):
                                $stu_id = $stu['user_id'];
                                // Get all assessments for this student (filtered by type if needed)
                                $typeFilter = ($selected_type !== 'all') ? "AND a.type = '" . $conn->real_escape_string($selected_type) . "'" : '';
                                $assessQ = "SELECT a.id, a.type, a.date, a.total_marks, er.marks_obtained FROM assessments a LEFT JOIN exam_results er ON a.id = er.assessment_id AND er.student_user_id = $stu_id WHERE a.teacher_user_id = $teacher_user_id AND a.class_id = $selected_class_id AND a.section_id = $selected_section_id $typeFilter ORDER BY a.date DESC";
                                $assessR = $conn->query($assessQ);
                                $all_percents = [];
                                $test_percents = [];
                                $hw_percents = [];
                                $last_percent = '-';
                                while ($a = $assessR->fetch_assoc()) {
                                    if ($a['marks_obtained'] !== null && $a['total_marks'] > 0) {
                                        $percent = round(($a['marks_obtained'] / $a['total_marks']) * 100, 2);
                                        $all_percents[] = $percent;
                                        if ($last_percent === '-') $last_percent = $percent;
                                        if ($a['type'] === 'test') $test_percents[] = $percent;
                                        if ($a['type'] === 'homework') $hw_percents[] = $percent;
                                    }
                                }
                                $overall = count($all_percents) ? round(array_sum($all_percents)/count($all_percents),2).'%' : '-';
                                $last = $last_percent !== '-' ? $last_percent.'%' : '-';
                                $test_avg = count($test_percents) ? round(array_sum($test_percents)/count($test_percents),2).'%' : '-';
                                $hw_avg = count($hw_percents) ? round(array_sum($hw_percents)/count($hw_percents),2).'%' : '-';
                            ?>
                                <tr>
                                    <td>
                                        <div class="student-name">
                                            <div class="student-info">
                                                <div class="student-info-name"><?php echo htmlspecialchars($stu['full_name']); ?></div>
                                                <div class="student-info-roll">Roll #<?php echo htmlspecialchars($stu['roll_number']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="performance-badge badge-<?php echo ($overall !== '-' && $overall >= 90) ? 'excellent' : (($overall !== '-' && $overall >= 75) ? 'good' : (($overall !== '-' && $overall >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $overall; ?></span>
                                    </td>
                                    <td class="grade-cell grade-<?php echo ($last !== '-' && $last >= 90) ? 'excellent' : (($last !== '-' && $last >= 75) ? 'good' : (($last !== '-' && $last >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $last; ?></td>
                                    <td class="grade-cell grade-<?php echo ($test_avg !== '-' && $test_avg >= 90) ? 'excellent' : (($test_avg !== '-' && $test_avg >= 75) ? 'good' : (($test_avg !== '-' && $test_avg >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $test_avg; ?></td>
                                    <td class="action-cell">
                                        <button class="btn btn-secondary action-btn">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-content" id="top-content">
                    <div style="overflow-x: auto;">
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Student Name</th>
                                    <th style="width: 15%;">Overall Grade</th>
                                    <th style="width: 15%;">Last Assessment</th>
                                    <th style="width: 15%;">Tests Average</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            // Reuse $students and their averages from above
                            $studentPerf = [];
                            foreach ($students as $stu):
                                $stu_id = $stu['user_id'];
                                $typeFilter = ($selected_type !== 'all') ? "AND a.type = '" . $conn->real_escape_string($selected_type) . "'" : '';
                                $assessQ = "SELECT a.id, a.type, a.date, a.total_marks, er.marks_obtained FROM assessments a LEFT JOIN exam_results er ON a.id = er.assessment_id AND er.student_user_id = $stu_id WHERE a.teacher_user_id = $teacher_user_id AND a.class_id = $selected_class_id AND a.section_id = $selected_section_id $typeFilter ORDER BY a.date DESC";
                                $assessR = $conn->query($assessQ);
                                $all_percents = [];
                                $test_percents = [];
                                $hw_percents = [];
                                $last_percent = '-';
                                while ($a = $assessR->fetch_assoc()) {
                                    if ($a['marks_obtained'] !== null && $a['total_marks'] > 0) {
                                        $percent = round(($a['marks_obtained'] / $a['total_marks']) * 100, 2);
                                        $all_percents[] = $percent;
                                        if ($last_percent === '-') $last_percent = $percent;
                                        if ($a['type'] === 'test') $test_percents[] = $percent;
                                        if ($a['type'] === 'homework') $hw_percents[] = $percent;
                                    }
                                }
                                $overall = count($all_percents) ? round(array_sum($all_percents)/count($all_percents),2) : null;
                                $last = $last_percent !== '-' ? $last_percent : null;
                                $test_avg = count($test_percents) ? round(array_sum($test_percents)/count($test_percents),2) : null;
                                $hw_avg = count($hw_percents) ? round(array_sum($hw_percents)/count($hw_percents),2) : null;
                                $studentPerf[] = [
                                    'name' => $stu['full_name'],
                                    'roll' => $stu['roll_number'],
                                    'overall' => $overall,
                                    'last' => $last,
                                    'test_avg' => $test_avg,
                                    'hw_avg' => $hw_avg
                                ];
                            endforeach;
                            // Sort by overall desc, take top 3
                            usort($studentPerf, function($a, $b) { return ($b['overall'] ?? 0) <=> ($a['overall'] ?? 0); });
                            $top = array_slice($studentPerf, 0, 3);
                            foreach ($top as $perf):
                                if ($perf['overall'] === null) continue;
                            ?>
                                <tr>
                                    <td>
                                        <div class="student-name">
                                            <div class="student-info">
                                                <div class="student-info-name"><?php echo htmlspecialchars($perf['name']); ?></div>
                                                <div class="student-info-roll">Roll #<?php echo htmlspecialchars($perf['roll']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="performance-badge badge-<?php echo ($perf['overall'] >= 90) ? 'excellent' : (($perf['overall'] >= 75) ? 'good' : (($perf['overall'] >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $perf['overall']; ?>%</span></td>
                                    <td class="grade-cell grade-<?php echo ($perf['last'] !== null && $perf['last'] >= 90) ? 'excellent' : (($perf['last'] !== null && $perf['last'] >= 75) ? 'good' : (($perf['last'] !== null && $perf['last'] >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $perf['last'] !== null ? $perf['last'].'%' : '-'; ?></td>
                                    <td class="grade-cell grade-<?php echo ($perf['test_avg'] !== null && $perf['test_avg'] >= 90) ? 'excellent' : (($perf['test_avg'] !== null && $perf['test_avg'] >= 75) ? 'good' : (($perf['test_avg'] !== null && $perf['test_avg'] >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $perf['test_avg'] !== null ? $perf['test_avg'].'%' : '-'; ?></td>
                                    <td class="action-cell">
                                        <button class="btn btn-secondary action-btn">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; if (!count($top) || $top[0]['overall'] === null): ?>
                                <tr><td colspan="6" style="text-align:center; color:#888;">No data available.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-content" id="risk-content">
                    <div style="overflow-x: auto;">
                        <table class="performance-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Student Name</th>
                                    <th style="width: 15%;">Overall Grade</th>
                                    <th style="width: 15%;">Last Assessment</th>
                                    <th style="width: 15%;">Tests Average</th>
                                    <th style="width: 10%;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            // At Risk: overall < 60%
                            $atRisk = array_filter($studentPerf, function($perf) { return $perf['overall'] !== null && $perf['overall'] < 60; });
                            foreach ($atRisk as $perf): ?>
                                <tr>
                                    <td>
                                        <div class="student-name">
                                            <div class="student-info">
                                                <div class="student-info-name"><?php echo htmlspecialchars($perf['name']); ?></div>
                                                <div class="student-info-roll">Roll #<?php echo htmlspecialchars($perf['roll']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="performance-badge badge-needs-improvement"><?php echo $perf['overall']; ?>%</span></td>
                                    <td class="grade-cell grade-<?php echo ($perf['last'] !== null && $perf['last'] >= 90) ? 'excellent' : (($perf['last'] !== null && $perf['last'] >= 75) ? 'good' : (($perf['last'] !== null && $perf['last'] >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $perf['last'] !== null ? $perf['last'].'%' : '-'; ?></td>
                                    <td class="grade-cell grade-<?php echo ($perf['test_avg'] !== null && $perf['test_avg'] >= 90) ? 'excellent' : (($perf['test_avg'] !== null && $perf['test_avg'] >= 75) ? 'good' : (($perf['test_avg'] !== null && $perf['test_avg'] >= 60) ? 'average' : 'needs-improvement')); ?>"><?php echo $perf['test_avg'] !== null ? $perf['test_avg'].'%' : '-'; ?></td>
                                    <td class="action-cell">
                                        <button class="btn btn-secondary action-btn">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; if (!count($atRisk)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#888;">No at-risk students found.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="risk-students">
                        <div class="risk-title">
                            <svg xmlns="http://www.w3.org/2000/svg" class="risk-icon" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            Students Requiring Attention
                        </div>
                        <?php if (count($atRisk)): ?>
                            <?php foreach ($atRisk as $perf): ?>
                                <div class="risk-student">
                                    <div>
                                        <div class="risk-student-name"><?php echo htmlspecialchars($perf['name']); ?></div>
                                        <div class="risk-student-issue">Low average score</div>
                                    </div>
                                    <div class="risk-student-action">Provide Additional Support</div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="color:#888; padding:1rem;">No students currently require special attention.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Individual Progress Card -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Individual Progress</h2>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="studentSelect" class="form-label">Select Student</label>
                    <form method="get" id="studentSelectForm">
                        <select id="studentSelect" name="student_id" class="form-select" onchange="document.getElementById('studentSelectForm').submit()">
                            <option value="">-- Select a student --</option>
                            <?php foreach ($students as $stu): ?>
                                <option value="<?php echo $stu['user_id']; ?>" <?php if (isset($_GET['student_id']) && $_GET['student_id'] == $stu['user_id']) echo 'selected'; ?>><?php echo htmlspecialchars($stu['full_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                <?php $selected_student_id = $_GET['student_id'] ?? ''; ?>
                <div id="studentProgressContent" style="<?php echo $selected_student_id ? '' : 'display:none;'; ?>">
                    <?php if ($selected_student_id):
                        // Fetch all assessments for this student
                        $typeFilter = ($selected_type !== 'all') ? "AND a.type = '" . $conn->real_escape_string($selected_type) . "'" : '';
                        $assessQ = "SELECT a.title, a.type, a.date, a.total_marks, er.marks_obtained FROM assessments a LEFT JOIN exam_results er ON a.id = er.assessment_id AND er.student_user_id = $selected_student_id WHERE a.teacher_user_id = $teacher_user_id AND a.class_id = $selected_class_id AND a.section_id = $selected_section_id $typeFilter ORDER BY a.date ASC";
                        $assessR = $conn->query($assessQ);
                        $scores = [];
                        while ($a = $assessR->fetch_assoc()) {
                            if ($a['marks_obtained'] !== null && $a['total_marks'] > 0) {
                                $percent = round(($a['marks_obtained'] / $a['total_marks']) * 100, 2);
                                $scores[] = [
                                    'label' => $a['title'],
                                    'percent' => $percent
                                ];
                            }
                        }
                    ?>
                    <div class="two-col">
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">Assessment Scores</h3>
                            <div class="chart-container" style="height: 200px;">
                                <div class="bar-chart">
                                    <?php if (count($scores)): foreach ($scores as $s): ?>
                                        <div class="bar" style="height: <?php echo intval($s['percent']); ?>%; background-color: #3b82f6;">
                                            <div class="bar-value"><?php echo $s['percent']; ?>%</div>
                                            <div class="bar-label"><?php echo htmlspecialchars($s['label']); ?></div>
                                        </div>
                                    <?php endforeach; else: ?>
                                        <div style="color:#888; padding:2rem;">No assessment data available.</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">Subject Breakdown</h3>
                            <div style="margin-bottom: 1rem; color:#888;">No subject breakdown data available.</div>
                        </div>
                    </div>
                    <div style="margin-top: 1.5rem;">
                        <h3 style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 1rem;">Teacher's Notes</h3>
                        <?php
                        // Fetch teacher note for this student/class-section
                        $note_text = '';
                        if ($selected_student_id) {
                            $noteQ = "SELECT note FROM teacher_notes WHERE student_user_id=$selected_student_id AND teacher_user_id=$teacher_user_id AND class_id=$selected_class_id AND section_id=$selected_section_id";
                            $noteR = $conn->query($noteQ);
                            if ($noteR && $noteR->num_rows > 0) {
                                $note_text = $noteR->fetch_assoc()['note'];
                            }
                        }
                        ?>
                        <form method="post" style="margin-bottom:0;">
                            <input type="hidden" name="teacher_note_student_id" value="<?php echo htmlspecialchars($selected_student_id); ?>">
                            <textarea name="teacher_note_text" rows="3" style="width:100%; border-radius:6px; padding:0.5rem; font-size:0.95rem; border:1px solid #d1d5db; margin-bottom:0.5rem;" placeholder="Enter your note for this student..."><?php echo htmlspecialchars($note_text); ?></textarea>
                            <button type="submit" class="btn btn-primary">Save Note</button>
                        </form>
                    </div>
                    <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem; gap: 0.75rem;">
                        <button class="btn btn-secondary">Generate Report</button>
                        <button class="btn btn-primary">Contact Parent</button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tab switching
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Show the corresponding tab content
                const tabId = this.getAttribute('data-tab');
                document.getElementById(`${tabId}-content`).classList.add('active');
            });
        });
        
        // Function to toggle the sidebar (defined in the sidebar.php)
        window.toggleSidebar = function() {
            const sidebar = document.getElementById('sidebar');
            const body = document.body;
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
        };
    });
</script>
</body>
</html>