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

// Fetch upcoming FA assessments for this student's class/section
$upcoming_fa_sql = "SELECT a.*, s.name as subject_name, s.code as subject_code 
                    FROM assessments a 
                    JOIN subjects s ON a.subject_id = s.id 
                    WHERE a.class_id = $class_id 
                    AND a.section_id = $section_id 
                    AND a.assessment_type = 'FA'
                    AND a.date >= CURDATE()
                    ORDER BY a.date ASC";

$upcoming_fa_result = $conn->query($upcoming_fa_sql);
$upcoming_fa_assessments = [];
while ($row = $upcoming_fa_result->fetch_assoc()) {
    $upcoming_fa_assessments[] = $row;
}

// Group assessments by date
$assessments_by_date = [];
foreach ($upcoming_fa_assessments as $assessment) {
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
    <title>FA Timetable - Student Portal</title>
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
            <h1 class="header-title">FA (Formative Assessment) Timetable</h1>
            <span class="header-subtitle">Upcoming formative assessments and continuous evaluations</span>
        </header>

        <main class="dashboard-content">
            <!-- Assessment Info -->
            <div class="exam-info">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Assessment Type</div>
                        <div class="info-value">Formative Assessment (FA)</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Total Assessments</div>
                        <div class="info-value"><?php echo count($upcoming_fa_assessments); ?> Scheduled</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Grading Scale</div>
                        <div class="info-value">Marks Based (19+ out of 25 = A+)</div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Assessment Types</div>
                        <div class="info-value">Quiz, Assignment, Project, Presentation</div>
                    </div>
                </div>
                
                <div class="info-note">
                    <strong>Note:</strong> FA assessments are continuous evaluations designed to track your learning progress. They include quizzes, assignments, projects, and presentations. Prepare regularly and participate actively.
                </div>
            </div>

            <?php if (!empty($upcoming_fa_assessments)): ?>
            <!-- Timetable -->
            <div class="card">
                <h2 class="card-title">FA Assessment Schedule</h2>
                
                <div class="timetable-container">
                    <table class="timetable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Subject</th>
                                <th>Assessment</th>
                                <th>Type</th>
                                <th>Time</th>
                                <th>Duration</th>
                                <th>Venue</th>
                                <th>Max Marks</th>
                                <th>Preparation</th>
                            </tr>
                        </thead>
                        <tbody>                            <?php foreach ($upcoming_fa_assessments as $assessment): 
                                $date = date('M d, Y', strtotime($assessment['date']));
                                $day = date('l', strtotime($assessment['date']));
                                $time_display = 'TBA'; // Time fields don't exist in database yet
                                $duration = $assessment['duration'] ?? 'N/A';
                                $venue = $assessment['venue'] ?? 'Classroom';
                                $total_marks = $assessment['total_marks'] ?? 25;
                                $assessment_type = $assessment['type'] ?? 'Assessment';
                            ?>
                            <tr>
                                <td class="date-column">
                                    <div class="exam-date"><?php echo $date; ?></div>
                                    <div class="exam-day"><?php echo $day; ?></div>
                                </td>
                                <td class="subject-column">
                                    <div class="subject-name">
                                        <span class="subject-icon fa-icon"></span>
                                        <?php echo htmlspecialchars($assessment['subject_name']); ?>
                                        <span class="subject-code">(<?php echo htmlspecialchars($assessment['subject_code']); ?>)</span>
                                    </div>
                                </td>
                                <td class="assessment-title"><?php echo htmlspecialchars($assessment['title']); ?></td>
                                <td class="type-column">
                                    <span class="assessment-type-badge <?php echo strtolower($assessment_type); ?>-type">
                                        <?php echo ucfirst($assessment_type); ?>
                                    </span>
                                </td>
                                <td class="time-column">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <?php echo $time_display; ?>
                                </td>
                                <td class="duration-column"><?php echo $duration; ?></td>
                                <td class="venue-column">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    <?php echo htmlspecialchars($venue); ?>
                                </td>
                                <td class="marks-column">
                                    <span class="total-marks"><?php echo $total_marks; ?> marks</span>
                                </td>
                                <td class="notes-column">
                                    <button class="notes-btn" onclick="showPreparationTips('<?php echo htmlspecialchars($assessment_type); ?>', '<?php echo htmlspecialchars($assessment['subject_name']); ?>')">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364-.636l-.707.707M21 12h-1M19.071 19.071l-.707-.707M12 20v1m-6.364-.636l.707-.707M3 12h1M4.929 4.929l.707.707" />
                                        </svg>
                                        Tips
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Assessment Calendar -->
            <div class="card">
                <h2 class="card-title">FA Assessment Calendar</h2>
                
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
                        $has_assessment = isset($assessments_by_date[$date_str]);
                        $class = $has_assessment ? 'calendar-day has-exam' : 'calendar-day';
                        
                        echo '<div class="' . $class . '">';
                        echo '<div class="day-number">' . $day . '</div>';
                        
                        if ($has_assessment) {
                            foreach ($assessments_by_date[$date_str] as $assessment) {
                                echo '<div class="exam-marker fa-marker">' . htmlspecialchars($assessment['subject_code']) . '</div>';
                            }
                        }
                        
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            <?php else: ?>
            <!-- No Assessments -->
            <div class="no-results">
                <div class="no-results-icon">📝</div>
                <h3>No FA Assessments Scheduled</h3>
                <p>There are currently no upcoming formative assessments scheduled for your class.</p>
            </div>
            <?php endif; ?>

            <!-- Assessment Types Info -->
            <div class="assessment-types-info">
                <h3>FA Assessment Types</h3>
                <div class="assessment-types-grid">
                    <div class="assessment-type-item">
                        <span class="type-icon">📝</span>
                        <span class="type-name">Quiz</span>
                        <span class="type-description">Short tests on recent topics</span>
                    </div>
                    <div class="assessment-type-item">
                        <span class="type-icon">📋</span>
                        <span class="type-name">Assignment</span>
                        <span class="type-description">Written homework and exercises</span>
                    </div>
                    <div class="assessment-type-item">
                        <span class="type-icon">🔬</span>
                        <span class="type-name">Project</span>
                        <span class="type-description">Research and practical work</span>
                    </div>
                    <div class="assessment-type-item">
                        <span class="type-icon">🎤</span>
                        <span class="type-name">Presentation</span>
                        <span class="type-description">Oral presentations and demonstrations</span>
                    </div>
                </div>
            </div>

            <!-- FA Grading Information -->
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

            <!-- Study Tips -->
            <div class="card">
                <h2 class="card-title">FA Assessment Preparation</h2>
                
                <ul class="tips-list">
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Stay Current:</strong> FA assessments test recent learning. Review class notes and materials regularly rather than cramming.
                        </div>
                    </li>
                    
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <div>
                            <strong>Active Participation:</strong> Engage actively in class discussions and activities. FA assessments often reflect classroom learning.
                        </div>
                    </li>
                    
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <div>
                            <strong>Collaborate Effectively:</strong> For group projects and presentations, communicate well with teammates and divide tasks fairly.
                        </div>
                    </li>
                    
                    <li class="tips-item">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <strong>Seek Feedback:</strong> Don't hesitate to ask teachers for clarification or feedback on your work to improve continuously.
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

        function showPreparationTips(type, subject) {
            let tips = '';
            
            switch(type.toLowerCase()) {
                case 'quiz':
                    tips = `Quiz Preparation for ${subject}:\n\n1. Review recent class notes and textbook sections\n2. Focus on key concepts from the last 1-2 weeks\n3. Practice quick problem-solving techniques\n4. Remember important definitions and formulas\n5. Get enough rest before the quiz`;
                    break;
                case 'assignment':
                    tips = `Assignment Tips for ${subject}:\n\n1. Read instructions carefully and understand requirements\n2. Plan your work and start early\n3. Research thoroughly and cite sources properly\n4. Proofread for grammar and content errors\n5. Submit before the deadline`;
                    break;
                case 'project':
                    tips = `Project Guidelines for ${subject}:\n\n1. Choose a topic that interests you and fits the criteria\n2. Create a timeline with milestones\n3. Gather diverse and reliable sources\n4. Include visual aids and practical examples\n5. Practice presenting your findings`;
                    break;
                case 'presentation':
                    tips = `Presentation Skills for ${subject}:\n\n1. Structure your content with clear introduction, body, conclusion\n2. Use visual aids effectively (slides, charts, models)\n3. Practice speaking clearly and at appropriate pace\n4. Prepare for potential questions\n5. Maintain eye contact with audience`;
                    break;
                default:
                    tips = `General FA Preparation for ${subject}:\n\n1. Stay updated with class materials\n2. Participate actively in discussions\n3. Ask questions when in doubt\n4. Complete work on time\n5. Seek teacher feedback regularly`;
            }
            
            alert(tips);
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
