<?php include 'sidebar.php'; ?>
<?php include 'con.php'; ?>

<?php
// Step 1: Fetch student info
$student_user_id = $_SESSION['user_id'] ?? 0;
$student = null;
$class_id = $section_id = null;
if ($student_user_id) {
    $stuRes = $conn->query("SELECT class_id, section_id FROM students WHERE user_id = $student_user_id");
    $student = $stuRes ? $stuRes->fetch_assoc() : null;
    if ($student) {
        $class_id = $student['class_id'];
        $section_id = $student['section_id'];
    }
}

// Step 2: Fetch all distinct exams for this class/section
$exams = [];
if ($class_id && $section_id) {
    $examRes = $conn->query("SELECT DISTINCT title, date, type FROM assessments WHERE class_id = $class_id AND section_id = $section_id ORDER BY date DESC");
    while ($row = $examRes->fetch_assoc()) {
        $exams[] = $row;
    }
}

// Step 3: Determine selected exam (by title/date/type)
$selected_exam_key = $_GET['exam_key'] ?? (isset($exams[0]) ? md5($exams[0]['title'].$exams[0]['date'].$exams[0]['type']) : null);
$selected_exam = null;
foreach ($exams as $exam) {
    $key = md5($exam['title'].$exam['date'].$exam['type']);
    if ($key === $selected_exam_key) {
        $selected_exam = $exam;
        break;
    }
}

// Step 4: Fetch all assessments (subjects) for the selected exam
$exam_assessments = [];
if ($selected_exam) {
    $title = $conn->real_escape_string($selected_exam['title']);
    $date = $conn->real_escape_string($selected_exam['date']);
    $type = $conn->real_escape_string($selected_exam['type']);
    $assessRes = $conn->query("SELECT a.*, s.name as subject_name FROM assessments a JOIN subjects s ON a.subject_id = s.id WHERE a.class_id = $class_id AND a.section_id = $section_id AND a.title = '$title' AND a.date = '$date' AND a.type = '$type' ORDER BY s.name");
    while ($row = $assessRes->fetch_assoc()) {
        $exam_assessments[] = $row;
    }
}

// Step 5: Fetch student's results for these assessments
$results_by_assessment_id = [];
if (count($exam_assessments)) {
    $assessment_ids = array_column($exam_assessments, 'id');
    $ids_str = implode(',', array_map('intval', $assessment_ids));
    if ($ids_str) {
        $resRes = $conn->query("SELECT * FROM exam_results WHERE assessment_id IN ($ids_str) AND student_user_id = $student_user_id");
        while ($row = $resRes->fetch_assoc()) {
            $results_by_assessment_id[$row['assessment_id']] = $row;
        }
    }
}

// --- Dynamic summary calculations ---
$total_marks_sum = 0;
$marks_obtained_sum = 0;
$subjects_passed = 0;
$total_subjects = count($exam_assessments);
$overall_grade = '-';
$overall_percent = 0;
foreach ($exam_assessments as $assessment) {
    $result = $results_by_assessment_id[$assessment['id']] ?? null;
    $marks_obtained = $result['marks_obtained'] ?? 0;
    $total_marks = $assessment['total_marks'] ?? 0;
    $grade = $result['grade_code'] ?? '-';
    $total_marks_sum += $total_marks;
    $marks_obtained_sum += $marks_obtained;
    if ($grade !== '-' && strtoupper($grade) !== 'F') {
        $subjects_passed++;
    }
}
if ($total_marks_sum > 0) {
    $overall_percent = round(($marks_obtained_sum / $total_marks_sum) * 100, 1);
}
// Assign overall grade based on percent (simple logic, can be improved)
if ($overall_percent >= 90) $overall_grade = 'A+';
elseif ($overall_percent >= 80) $overall_grade = 'A';
elseif ($overall_percent >= 70) $overall_grade = 'B';
elseif ($overall_percent >= 60) $overall_grade = 'C';
elseif ($overall_percent >= 50) $overall_grade = 'D';
elseif ($overall_percent > 0) $overall_grade = 'F';
// --- Dynamic class rank calculation ---
$class_rank = '-';
$top_percent = '-';
if (count($exam_assessments)) {
    // Fetch all students' total marks for this exam
    $exam_title = $selected_exam['title'];
    $exam_type = $selected_exam['type'];
    $exam_date = $selected_exam['date'];
    $rank_sql = "SELECT er.student_user_id, SUM(er.marks_obtained) as total_marks, SUM(a.total_marks) as max_marks
        FROM exam_results er
        JOIN assessments a ON er.assessment_id = a.id
        WHERE a.class_id = $class_id AND a.section_id = $section_id AND a.title = '" . $conn->real_escape_string($exam_title) . "' AND a.type = '" . $conn->real_escape_string($exam_type) . "' AND a.date = '" . $conn->real_escape_string($exam_date) . "'
        GROUP BY er.student_user_id
        ORDER BY total_marks DESC";
    $rankRes = $conn->query($rank_sql);
    $rankings = [];
    while ($row = $rankRes->fetch_assoc()) {
        $rankings[] = $row;
    }
    // Find this student's rank
    foreach ($rankings as $i => $row) {
        if ($row['student_user_id'] == $student_user_id) {
            $class_rank = $i + 1;
            $top_percent = round(($class_rank / count($rankings)) * 100, 1);
            break;
        }
    }
}
// --- Dynamic attendance calculation ---
$attendance_percent = '-';
$attendance_label = 'N/A';
$att_sql = "SELECT COUNT(*) as present_days, (SELECT COUNT(*) FROM attendance WHERE student_user_id = $student_user_id AND status IN ('present','holiday')) as total_days FROM attendance WHERE student_user_id = $student_user_id AND status = 'present'";
$attRes = $conn->query($att_sql);
if ($attRes && $attRow = $attRes->fetch_assoc()) {
    $present_days = $attRow['present_days'];
    $total_days = $attRow['total_days'];
    if ($total_days > 0) {
        $attendance_percent = round(($present_days / $total_days) * 100, 1) . '%';
        $attendance_label = $attendance_percent >= 90 ? 'Excellent' : ($attendance_percent >= 75 ? 'Good' : ($attendance_percent >= 50 ? 'Average' : 'Poor'));
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Student Results</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/results.css">
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
        <h1 class="header-title">Student Results</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="exam-selector">
            <?php foreach ($exams as $exam):
                $key = md5($exam['title'].$exam['date'].$exam['type']);
                $is_active = ($key === $selected_exam_key);
            ?>
                <a href="?exam_key=<?php echo $key; ?>" class="exam-option<?php echo $is_active ? ' active' : ''; ?>">
                    <?php echo htmlspecialchars($exam['title']); ?>
                    <span style="font-size:0.9em;color:#888;">
                        (<?php echo date('M d, Y', strtotime($exam['date'])); ?>)
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2 class="card-title">
                <?php if ($selected_exam) {
                    echo htmlspecialchars($selected_exam['title']) . ' Results (' . date('M d, Y', strtotime($selected_exam['date'])) . ')';
                } else {
                    echo 'Exam Results';
                }
                ?>
            </h2>
            
            <div class="results-summary">
                <div class="result-stat">
                    <div class="stat-value"><?php echo $overall_percent !== 0 ? $overall_percent . '%' : '-'; ?></div>
                    <div class="stat-label">Overall Score</div>
                    <div class="stat-percentage percentage-good"><?php echo $overall_grade; ?> Grade</div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo $class_rank; ?></div>
                    <div class="stat-label">Class Rank</div>
                    <div class="stat-percentage percentage-good"><?php echo $top_percent !== '-' ? 'Top ' . $top_percent . '%' : '-'; ?></div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo $subjects_passed . ' / ' . $total_subjects; ?></div>
                    <div class="stat-label">Subjects Passed</div>
                    <div class="stat-percentage percentage-good"><?php echo $total_subjects > 0 ? round(($subjects_passed / $total_subjects) * 100, 1) . '%' : '-'; ?></div>
                </div>
                
                <div class="result-stat">
                    <div class="stat-value"><?php echo $attendance_percent; ?></div>
                    <div class="stat-label">Attendance</div>
                    <div class="stat-percentage percentage-good"><?php echo $attendance_label; ?></div>
                </div>
            </div>
            
            <div class="results-table-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Grade</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($exam_assessments as $assessment):
                            $result = $results_by_assessment_id[$assessment['id']] ?? null;
                            $marks_obtained = $result['marks_obtained'] ?? '-';
                            $total_marks = $assessment['total_marks'] ?? '-';
                            $grade = $result['grade_code'] ?? '-';
                            $remark = $result['remark'] ?? '';
                            $percent = ($marks_obtained !== '-' && $total_marks && $total_marks > 0) ? round(($marks_obtained / $total_marks) * 100) : 0;
                            $status = ($grade !== '-' && strtoupper($grade) !== 'F') ? 'Passed' : (($grade === '-') ? 'Not Graded' : 'Failed');
                            $progress_class = $percent >= 90 ? 'progress-excellent' : ($percent >= 75 ? 'progress-good' : ($percent >= 50 ? 'progress-average' : 'progress-poor'));
                            $grade_class = $grade === '-' ? '' : (strtolower($grade) === 'f' ? 'grade-f' : (strtolower($grade) === 'a' ? 'grade-a' : (strtolower($grade) === 'b' ? 'grade-b' : (strtolower($grade) === 'c' ? 'grade-c' : ''))));
                        ?>
                        <tr>
                            <td>
                                <div class="subject-name">
                                    <div class="subject-icon math-icon"></div>
                                    <?php echo htmlspecialchars($assessment['subject_name']); ?>
                                </div>
                            </td>
                            <td class="marks"><?php echo $marks_obtained !== '-' ? $marks_obtained . ' / ' . $total_marks : '-'; ?></td>
                            <td><div class="grade <?php echo $grade_class; ?>"><?php echo $grade; ?></div></td>
                            <td><span class="status status-<?php echo strtolower(str_replace(' ', '-', $status)); ?>"><?php echo $status; ?></span></td>
                            <td>
                                <div class="progress-bar">
                                    <div class="progress-value <?php echo $progress_class; ?>" style="width: <?php echo $percent; ?>%;"></div>
                                </div>
                            </td>
                            <td class="remarks"><?php echo htmlspecialchars($remark); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="results-chart">
                <h3 class="chart-title">Subject Performance</h3>
                <div class="chart-container">
                    <?php foreach ($exam_assessments as $assessment):
                        $result = $results_by_assessment_id[$assessment['id']] ?? null;
                        $marks_obtained = $result['marks_obtained'] ?? 0;
                        $total_marks = $assessment['total_marks'] ?? 0;
                        $percent = ($total_marks > 0) ? round(($marks_obtained / $total_marks) * 100) : 0;
                        $bar_height = 60 + ($percent * 1.3); // scale for visual effect
                        $bar_color = $percent >= 90 ? '#93c5fd' : ($percent >= 75 ? '#93c5fd' : ($percent >= 50 ? '#fbbf24' : '#f87171'));
                    ?>
                    <div class="chart-bar">
                        <div class="bar-value"><?php echo $marks_obtained; ?></div>
                        <div class="bar-column" style="height: <?php echo $bar_height; ?>px; background: <?php echo $bar_color; ?>;"></div>
                        <div class="bar-label"><?php echo htmlspecialchars($assessment['subject_name']); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="certificate-preview">
                <button class="certificate-button">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Download Result Certificate
                </button>
            </div>
        </div>
        
        <div class="card teacher-comments">
            <h2 class="card-title">Teacher's Comments</h2>
            
            <div class="comment-card">
                <div class="comment-header">
                    <div class="teacher-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="teacher-info">
                        <div class="teacher-name">Mr. Peterson</div>
                        <div class="teacher-subject">Class Teacher</div>
                    </div>
                </div>
                
                <div class="comment-content">
                    <p>John has shown excellent progress this term. His overall performance is commendable, especially in Mathematics and History where he demonstrates a strong grasp of concepts. His active participation in class discussions and consistent homework submission shows his dedication to academics.</p>
                    <p>However, he needs to focus more on Physics, where his understanding of fundamental concepts requires improvement. I recommend additional practice with problem-solving exercises and attending the after-school tutoring sessions.</p>
                    <p>Overall, John is a diligent student with great potential. Keep up the good work!</p>
                </div>
            </div>
            
            <div class="comment-card">
                <div class="comment-header">
                    <div class="teacher-avatar">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="teacher-info">
                        <div class="teacher-name">Mrs. Johnson</div>
                        <div class="teacher-subject">Mathematics</div>
                    </div>
                </div>
                
                <div class="comment-content">
                    <p>John demonstrates exceptional mathematical ability. He consistently solves complex problems and shows a deep understanding of algebraic concepts. His solutions are well-structured and he frequently finds innovative approaches to challenging questions.</p>
                    <p>To further excel, I encourage him to participate in the upcoming Mathematics Olympiad, which would be an excellent platform to showcase his problem-solving skills.</p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Handle exam selector
    document.querySelectorAll('.exam-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.exam-option').forEach(opt => {
                opt.classList.remove('active');
            });
            this.classList.add('active');
            
            // In a real app, you would load the corresponding exam results here
            document.querySelector('.card-title').textContent = this.textContent + ' Results';
        });
    });
    
    // Animate chart bars on load
    document.addEventListener('DOMContentLoaded', function() {
        const bars = document.querySelectorAll('.bar-column');
        bars.forEach(bar => {
            const originalHeight = bar.style.height;
            bar.style.height = '0';
            
            setTimeout(() => {
                bar.style.height = originalHeight;
            }, 300);
        });
    });
    
    // Handle certificate download button
    document.querySelector('.certificate-button').addEventListener('click', function() {
        alert('Certificate download functionality would be implemented here in a production environment.');
    });
</script>
</body>
</html>