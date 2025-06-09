<?php include 'sidebar.php'; ?>
<?php include 'con.php'; ?>
<?php require_once '../../includes/grading_functions.php'; ?>

<?php
// Get student user id
$student_user_id = $_SESSION['user_id'] ?? 0;

// Get student's class and section
$student_sql = "SELECT class_id, section_id FROM students WHERE user_id = $student_user_id";
$student_result = $conn->query($student_sql);
$student_data = $student_result->fetch_assoc();
$class_id = $student_data['class_id'] ?? 0;
$section_id = $student_data['section_id'] ?? 0;

// Fetch FA assessments for this student's class/section
$fa_assessments_sql = "SELECT a.*, s.name as subject_name, s.code as subject_code 
                       FROM assessments a 
                       JOIN subjects s ON a.subject_id = s.id 
                       WHERE a.class_id = $class_id 
                       AND a.section_id = $section_id 
                       AND a.assessment_type = 'FA'
                       ORDER BY a.date DESC";
$fa_assessments_result = $conn->query($fa_assessments_sql);
$fa_assessments = [];
while ($row = $fa_assessments_result->fetch_assoc()) {
    $fa_assessments[] = $row;
}

// Fetch results for FA assessments
$results_by_assessment_id = [];
if (count($fa_assessments)) {
    $assessment_ids = array_column($fa_assessments, 'id');
    $assessment_ids_str = implode(',', $assessment_ids);
    $results_sql = "SELECT * FROM exam_results WHERE student_user_id = $student_user_id AND assessment_id IN ($assessment_ids_str)";
    $results_result = $conn->query($results_sql);
    while ($row = $results_result->fetch_assoc()) {
        $results_by_assessment_id[$row['assessment_id']] = $row;
    }
}

// Calculate summary statistics for FA
$total_assessments = count($fa_assessments);
$completed_assessments = 0;
$average_grade = '-';
$subjects_passed = 0;

$grade_points = ['A+' => 5, 'A' => 4, 'B' => 3, 'C' => 2, 'D' => 1];
$total_grade_points = 0;
$graded_assessments = 0;

foreach ($fa_assessments as $assessment) {
    $result = $results_by_assessment_id[$assessment['id']] ?? null;
    $grade = $result['grade_code'] ?? '-';
    
    if ($grade !== '-') {
        $completed_assessments++;
        $graded_assessments++;
        
        if (isPassing($grade)) {
            $subjects_passed++;
        }
        
        if (isset($grade_points[$grade])) {
            $total_grade_points += $grade_points[$grade];
        }
    }
}

if ($graded_assessments > 0) {
    $avg_points = $total_grade_points / $graded_assessments;
    if ($avg_points >= 4.5) $average_grade = 'A+';
    elseif ($avg_points >= 3.5) $average_grade = 'A';
    elseif ($avg_points >= 2.5) $average_grade = 'B';
    elseif ($avg_points >= 1.5) $average_grade = 'C';
    else $average_grade = 'D';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FA Results - Student Portal</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/results.css">
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
            <h1 class="header-title">FA (Formative Assessment) Results</h1>
            <span class="header-subtitle">Your continuous assessment performance</span>
        </header>

        <main class="dashboard-content">
            <!-- Summary Cards -->
            <div class="results-summary">
                <div class="result-stat">
                    <div class="stat-value"><?php echo $average_grade; ?></div>
                    <div class="stat-label">Average Grade</div>
                    <div class="stat-percentage percentage-info">Continuous</div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo $completed_assessments . ' / ' . $total_assessments; ?></div>
                    <div class="stat-label">Completed</div>
                    <div class="stat-percentage percentage-good">
                        <?php echo $total_assessments > 0 ? round(($completed_assessments / $total_assessments) * 100, 1) . '%' : '-'; ?>
                    </div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo $subjects_passed; ?></div>
                    <div class="stat-label">Passing Grades</div>
                    <div class="stat-percentage percentage-good">
                        <?php echo $completed_assessments > 0 ? round(($subjects_passed / $completed_assessments) * 100, 1) . '%' : '-'; ?>
                    </div>
                </div>
            </div>
            
            <!-- Results Table -->
            <div class="results-table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Assessment</th>
                            <th>Date</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($fa_assessments as $assessment):
                            $result = $results_by_assessment_id[$assessment['id']] ?? null;
                            $marks_obtained = $result['marks_obtained'] ?? '-';
                            $total_marks = $assessment['total_marks'] ?? '-';
                            $grade = $result['grade_code'] ?? '-';
                            $remark = $result['remark'] ?? '';
                            $status = ($grade !== '-' && isPassing($grade)) ? 'Passed' : (($grade === '-') ? 'Not Graded' : 'Failed');
                            $grade_class = getGradeColorClass($grade);
                        ?>
                        <tr>
                            <td>
                                <div class="subject-name">
                                    <div class="subject-icon fa-icon"></div>
                                    <?php echo htmlspecialchars($assessment['subject_name']); ?>
                                    <span class="subject-code">(<?php echo htmlspecialchars($assessment['subject_code']); ?>)</span>
                                </div>
                            </td>
                            <td class="assessment-title"><?php echo htmlspecialchars($assessment['title']); ?></td>
                            <td class="assessment-date"><?php echo date('M d, Y', strtotime($assessment['date'])); ?></td>
                            <td class="marks"><?php echo $marks_obtained !== '-' ? $marks_obtained . ' / ' . $total_marks : '-'; ?></td>
                            <td><div class="grade <?php echo $grade_class; ?>"><?php echo $grade; ?></div></td>
                            <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>"><?php echo $status; ?></span></td>
                            <td><span class="assessment-type fa-type"><?php echo strtoupper($assessment['type'] ?? 'FA'); ?></span></td>
                            <td class="remarks"><?php echo htmlspecialchars($remark); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($fa_assessments)): ?>
            <div class="no-results">
                <div class="no-results-icon">📝</div>
                <h3>No FA Results Available</h3>
                <p>No formative assessment results found for your class.</p>
            </div>
            <?php endif; ?>
            
            <!-- Grade Scale Information -->
            <div class="grade-scale-info">
                <h3>FA Grading Scale</h3>
                <div class="grade-scale-grid">
                    <?php foreach (getFAGrades() as $grade): ?>
                    <div class="grade-scale-item">
                        <span class="grade <?php echo getGradeColorClass($grade['code']); ?>"><?php echo $grade['code']; ?></span>
                        <span class="grade-description"><?php echo $grade['description']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Assessment Types Info -->
            <div class="assessment-types-info">
                <h3>FA Assessment Types</h3>
                <div class="assessment-types-grid">
                    <div class="assessment-type-item">
                        <span class="type-icon">📝</span>
                        <span class="type-name">Quiz</span>
                        <span class="type-description">Quick assessments</span>
                    </div>
                    <div class="assessment-type-item">
                        <span class="type-icon">📋</span>
                        <span class="type-name">Assignment</span>
                        <span class="type-description">Take-home tasks</span>
                    </div>
                    <div class="assessment-type-item">
                        <span class="type-icon">🎯</span>
                        <span class="type-name">Project</span>
                        <span class="type-description">Extended work</span>
                    </div>
                    <div class="assessment-type-item">
                        <span class="type-icon">🔬</span>
                        <span class="type-name">Practical</span>
                        <span class="type-description">Hands-on activities</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const dashboardContainer = document.querySelector('.dashboard-container');
            const body = document.querySelector('body');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.toggle('show');
            body.classList.toggle('sidebar-open');
            dashboardContainer.classList.toggle('sidebar-open');
        }
    </script>
</body>
</html>
