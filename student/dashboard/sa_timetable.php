<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
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

// Fetch upcoming SA assessments for this student's class/section
$upcoming_sa_sql = "SELECT a.*, s.name as subject_name, s.code as subject_code 
                    FROM assessments a 
                    JOIN subjects s ON a.subject_id = s.id 
                    WHERE a.class_id = $class_id 
                    AND a.section_id = $section_id 
                    AND a.assessment_type = 'SA'
                    AND a.date >= CURDATE()
                    ORDER BY a.date ASC, a.title ASC";

$upcoming_sa_result = $conn->query($upcoming_sa_sql);
$upcoming_sa_assessments = [];
while ($row = $upcoming_sa_result->fetch_assoc()) {
    $upcoming_sa_assessments[] = $row;
}

// Group assessments by date
$assessments_by_date = [];
foreach ($upcoming_sa_assessments as $assessment) {
    $date = $assessment['date'];
    if (!isset($assessments_by_date[$date])) {
        $assessments_by_date[$date] = [];
    }
    $assessments_by_date[$date][] = $assessment;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SA Timetable - Student Portal</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/exams.css">
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
            <h1 class="header-title">SA (Summative Assessment) Timetable</h1>
            <span class="header-subtitle">Upcoming summative exams and assessments</span>
        </header>

        <main class="dashboard-content">
            <!-- Exam Info -->
            <div class="exam-info">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Assessment Type</div>
                        <div class="info-value">Summative Assessment (SA)</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Total Exams</div>
                        <div class="info-value"><?php echo count($upcoming_sa_assessments); ?> Scheduled</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Grading Scale</div>
                        <div class="info-value">Percentage Based (92%+ = A+)</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Reporting Time</div>
                        <div class="info-value">30 minutes before exam</div>
                    </div>
                </div>
                
                <div class="info-note">
                    <strong>Important:</strong> SA assessments are formal examinations that contribute to your final grade. Ensure you have all necessary stationery and arrive on time. Electronic devices are not permitted during exams.
                </div>
            </div>

            <?php if (!empty($upcoming_sa_assessments)): ?>
            <!-- Timetable -->
            <div class="card">
                <h2 class="card-title">SA Examination Schedule</h2>
                
                <div class="timetable-container">
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Assessment</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Total Marks</th>
                                <th>Study Notes</th>
                            </tr>
                        </thead>
                        <tbody>                            <?php foreach ($upcoming_sa_assessments as $assessment): 
                                $date = date('M d, Y', strtotime($assessment['date']));
                                $day = date('l', strtotime($assessment['date']));
                                $duration = $assessment['duration'] ?? 'TBA';
                                $total_marks = $assessment['total_marks'] ?? 'N/A';
                            ?>
                            <tr>
                                <td class="date-column">
                                    <div class="exam-date"><?php echo $date; ?></div>
                                    <div class="exam-day"><?php echo $day; ?></div>
                                </td>
                                <td class="subject-column">
                                    <div class="subject-name">
                                        <span class="subject-icon sa-icon"></span>
                                        <?php echo htmlspecialchars($assessment['subject_name']); ?>
                                        <span class="subject-code">(<?php echo htmlspecialchars($assessment['subject_code']); ?>)</span>
                                    </div>
                                </td>
                                <td class="assessment-title"><?php echo htmlspecialchars($assessment['title']); ?></td>                                <td class="time-column">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    TBA
                                </td>
                                <td class="duration-column"><?php echo $duration; ?></td>
                                <td class="marks-column">
                                    <span class="total-marks"><?php echo $total_marks; ?> marks</span>
                                </td>
                                <td class="notes-column">
                                    <button class="notes-btn" onclick="showStudyTips('<?php echo htmlspecialchars($assessment['subject_name']); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        View Notes
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Exam Calendar -->
            <div class="card">
                <h2 class="card-title">SA Exam Calendar</h2>
                
                <div class="calendar-header">
                    <button class="calendar-nav-btn" id="prevMonth">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <h3 class="calendar-month" id="currentMonth"><?php echo date('F Y'); ?></h3>
                    <button class="calendar-nav-btn" id="nextMonth">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                
                <div class="calendar-grid">
                    <div class="calendar-weekday">Sun</div>
                    <div class="calendar-weekday">Mon</div>
                    <div class="calendar-weekday">Tue</div>
                    <div class="calendar-weekday">Wed</div>
                    <div class="calendar-weekday">Thu</div>
                    <div class="calendar-weekday">Fri</div>
                    <div class="calendar-weekday">Sat</div>
                    
                    <?php
                    $current_date = date('Y-m-01');
                    $first_day = date('w', strtotime($current_date));
                    $days_in_month = date('t', strtotime($current_date));
                    
                    // Previous month's trailing days
                    for ($i = 0; $i < $first_day; $i++) {
                        echo '<div class="calendar-day different-month"><div class="day-number">' . (date('t', strtotime($current_date . ' -1 month')) - $first_day + $i + 1) . '</div></div>';
                    }
                    
                    // Current month's days
                    for ($day = 1; $day <= $days_in_month; $day++) {
                        $date_str = date('Y-m-' . sprintf('%02d', $day));
                        $has_exam = isset($assessments_by_date[$date_str]);
                        $class = $has_exam ? 'calendar-day has-exam' : 'calendar-day';
                        
                        echo '<div class="' . $class . '">';
                        echo '<div class="day-number">' . $day . '</div>';
                        
                        if ($has_exam) {
                            foreach ($assessments_by_date[$date_str] as $exam) {
                                echo '<div class="exam-marker">' . htmlspecialchars($exam['subject_code']) . '</div>';
                            }
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <?php else: ?>
            <!-- No Exams -->
            <div class="no-results">
                <div class="no-results-icon">📋</div>
                <h3>No SA Exams Scheduled</h3>
                <p>There are currently no upcoming summative assessments scheduled for your class.</p>
            </div>
            <?php endif; ?>

            <!-- SA Grading Information -->
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

            <!-- Study Tips -->
            <div class="card">
                <h2 class="card-title">SA Exam Preparation Tips</h2>
                
                <ul class="tips-list">
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Start Early:</strong> SA exams cover extensive syllabi. Begin preparation at least 2-3 weeks before the exam date to allow thorough revision.
                        </div>
                    </li>
                    
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Focus on Core Concepts:</strong> SA assessments test understanding of fundamental concepts. Ensure you have a strong grasp of basic principles.
                        </div>
                    </li>
                    
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <div>
                            <strong>Practice Previous Papers:</strong> Solve past SA question papers to understand the exam pattern and question types.
                        </div>
                    </li>
                    
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Time Management:</strong> SA exams are longer. Practice answering questions within time limits to improve speed and accuracy.
                        </div>
                    </li>
                </ul>
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
            
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                body.classList.remove('sidebar-open');
            });
        }

        function showStudyTips(subject) {
            alert(`Study tips for ${subject}:\n\n1. Review all class notes and textbook chapters\n2. Practice sample questions and previous year papers\n3. Create summary notes for quick revision\n4. Form study groups for discussion\n5. Take regular breaks during study sessions`);
        }

        // Calendar navigation (basic implementation)
        document.getElementById('prevMonth')?.addEventListener('click', function() {
            // Previous month navigation would be implemented here
            console.log('Previous month');
        });

        document.getElementById('nextMonth')?.addEventListener('click', function() {
            // Next month navigation would be implemented here
            console.log('Next month');
        });
    </script>
</body>
</html>
