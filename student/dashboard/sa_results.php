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

// Fetch SA assessments for this student's class/section
$sa_assessments_sql = "SELECT a.*, s.name as subject_name, s.code as subject_code 
                       FROM assessments a 
                       JOIN subjects s ON a.subject_id = s.id 
                       WHERE a.class_id = $class_id 
                       AND a.section_id = $section_id 
                       AND a.assessment_type = 'SA'
                       ORDER BY a.date DESC";
$sa_assessments_result = $conn->query($sa_assessments_sql);
$sa_assessments = [];
while ($row = $sa_assessments_result->fetch_assoc()) {
    $sa_assessments[] = $row;
}

// Fetch results for SA assessments
$results_by_assessment_id = [];
if (count($sa_assessments)) {
    $assessment_ids = array_column($sa_assessments, 'id');
    $assessment_ids_str = implode(',', $assessment_ids);
    $results_sql = "SELECT * FROM exam_results WHERE student_user_id = $student_user_id AND assessment_id IN ($assessment_ids_str)";
    $results_result = $conn->query($results_sql);
    while ($row = $results_result->fetch_assoc()) {
        $results_by_assessment_id[$row['assessment_id']] = $row;
    }
}

// Calculate summary statistics
$total_marks_sum = 0;
$marks_obtained_sum = 0;
$subjects_passed = 0;
$total_subjects = count($sa_assessments);
$overall_grade = '-';
$overall_percent = 0;

foreach ($sa_assessments as $assessment) {
    $result = $results_by_assessment_id[$assessment['id']] ?? null;
    $marks_obtained = $result['marks_obtained'] ?? 0;
    $total_marks = $assessment['total_marks'] ?? 0;
    $grade = $result['grade_code'] ?? '-';
    
    $total_marks_sum += $total_marks;
    $marks_obtained_sum += $marks_obtained;
    
    if ($grade !== '-' && isPassing($grade)) {
        $subjects_passed++;
    }
}

if ($total_marks_sum > 0) {
    $overall_percent = round(($marks_obtained_sum / $total_marks_sum) * 100, 1);
}

$overall_grade = calculateSAGrade($overall_percent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SA Results - Student Portal</title>
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
            <h1 class="header-title">SA (Summative Assessment) Results</h1>
            <span class="header-subtitle">Your academic performance in summative assessments</span>
        </header>

        <main class="dashboard-content">
            <!-- Summary Cards -->
            <div class="results-summary">
                <div class="result-stat">
                    <div class="stat-value"><?php echo $overall_percent !== 0 ? $overall_percent . '%' : '-'; ?></div>
                    <div class="stat-label">Overall Score</div>
                    <div class="stat-percentage percentage-good"><?php echo $overall_grade; ?> Grade</div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo $subjects_passed . ' / ' . $total_subjects; ?></div>
                    <div class="stat-label">Subjects Passed</div>
                    <div class="stat-percentage percentage-good">
                        <?php echo $total_subjects > 0 ? round(($subjects_passed / $total_subjects) * 100, 1) . '%' : '-'; ?>
                    </div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo count($sa_assessments); ?></div>
                    <div class="stat-label">SA Assessments</div>
                    <div class="stat-percentage percentage-info">Summative</div>
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
                            <th>Progress</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sa_assessments as $assessment):
                            $result = $results_by_assessment_id[$assessment['id']] ?? null;
                            $marks_obtained = $result['marks_obtained'] ?? '-';
                            $total_marks = $assessment['total_marks'] ?? '-';
                            $grade = $result['grade_code'] ?? '-';
                            $remark = $result['remark'] ?? '';
                            $percent = ($marks_obtained !== '-' && $total_marks && $total_marks > 0) ? round(($marks_obtained / $total_marks) * 100) : 0;
                            $status = ($grade !== '-' && isPassing($grade)) ? 'Passed' : (($grade === '-') ? 'Not Graded' : 'Failed');
                            $progress_class = $percent >= 92 ? 'progress-excellent' : ($percent >= 75 ? 'progress-good' : ($percent >= 60 ? 'progress-average' : 'progress-poor'));
                            $grade_class = getGradeColorClass($grade);
                        ?>
                        <tr>
                            <td>
                                <div class="subject-name">
                                    <div class="subject-icon"></div>
                                    <?php echo htmlspecialchars($assessment['subject_name']); ?>
                                    <span class="subject-code">(<?php echo htmlspecialchars($assessment['subject_code']); ?>)</span>
                                </div>
                            </td>
                            <td class="assessment-title"><?php echo htmlspecialchars($assessment['title']); ?></td>
                            <td class="assessment-date"><?php echo date('M d, Y', strtotime($assessment['date'])); ?></td>
                            <td class="marks"><?php echo $marks_obtained !== '-' ? $marks_obtained . ' / ' . $total_marks : '-'; ?></td>
                            <td><div class="grade <?php echo $grade_class; ?>"><?php echo $grade; ?></div></td>
                            <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>"><?php echo $status; ?></span></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value <?php echo $progress_class; ?>" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                                <span class="progress-text"><?php echo $percent; ?>%</span>
                            </td>
                            <td class="remarks"><?php echo htmlspecialchars($remark); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (empty($sa_assessments)): ?>
            <div class="no-results">
                <div class="no-results-icon">📋</div>
                <h3>No SA Results Available</h3>
                <p>No summative assessment results found for your class.</p>
            </div>
            <?php endif; ?>
            
            <!-- Grade Scale Information -->
            <div class="grade-scale-info">
                <h3>SA Grading Scale</h3>
                <div class="grade-scale-grid">
                    <?php foreach (getSAGrades() as $grade): ?>
                    <div class="grade-scale-item">
                        <span class="grade <?php echo getGradeColorClass($grade['code']); ?>"><?php echo $grade['code']; ?></span>
                        <span class="grade-description"><?php echo $grade['description']; ?></span>
                    </div>
                    <?php endforeach; ?>
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
