<?php
/**
 * Normalized Exam Session Management System
 * Handles SA/FA examination sessions with proper database normalization
 */

// Start session before any output
session_start();

require_once 'con.php';
require_once '../../includes/functions.php';
require_once '../../includes/grading_functions.php';

// Check if user has permission to manage exams
if (!hasRole(['admin', 'headmaster'])) {
    header('Location: ../../login.php');
    exit();
}

// Get current user
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Session Management - Normalized System</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        .exam-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .exam-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .session-type {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .session-type.SA {
            background: #e3f2fd;
            color: #1976d2;
        }
        .session-type.FA {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .status.active { background: #4caf50; color: white; }
        .status.draft { background: #ff9800; color: white; }
        .status.completed { background: #2196f3; color: white; }
        .status.cancelled { background: #f44336; color: white; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .stat-item {
            text-align: center;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .btn-group {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .subject-list {
            margin-top: 10px;
        }
        .subject-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            background: #f9f9f9;
            margin: 4px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>📊 Normalized Exam Session Management</h1>
            <p>Manage SA/FA examination sessions with proper database normalization</p>
        </header>

        <main class="admin-content">
            <!-- Create New Exam Session -->
            <div class="exam-card">
                <div class="exam-header">
                    <h2>🆕 Create New Exam Session</h2>
                    <button onclick="toggleCreateForm()" class="btn btn-primary">Toggle Form</button>
                </div>
                
                <form id="examSessionForm" class="form-grid" style="display: none;">
                    <div class="form-group">
                        <label for="sessionName">Session Name*</label>
                        <input type="text" id="sessionName" name="sessionName" required
                               placeholder="e.g., Mid-Term SA Examination 2025">
                    </div>
                      <div class="form-group">
                        <label for="sessionType">Session Type*</label>
                        <select id="sessionType" name="sessionType" required>
                            <option value="">Select Type</option>
                            <option value="SA">SA (Summative Assessment)</option>
                            <?php if (hasRole(['admin', 'teacher', 'headmaster'])): ?>
                            <option value="FA">FA (Formative Assessment)</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="academicYear">Academic Year*</label>
                        <input type="text" id="academicYear" name="academicYear" required
                               value="2024-25" placeholder="2024-25">
                    </div>
                    
                    <div class="form-group">
                        <label for="term">Term*</label>
                        <select id="term" name="term" required>
                            <option value="">Select Term</option>
                            <option value="Term1">Term 1</option>
                            <option value="Term2" selected>Term 2</option>
                            <option value="Term3">Term 3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="startDate">Start Date*</label>
                        <input type="date" id="startDate" name="startDate" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="endDate">End Date*</label>
                        <input type="date" id="endDate" name="endDate" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="classId">Class*</label>
                        <select id="classId" name="classId" required>
                            <option value="">Select Class</option>
                            <?php
                            $classes_sql = "SELECT id, name FROM classes ORDER BY name";
                            $classes_result = $conn->query($classes_sql);
                            while ($class = $classes_result->fetch_assoc()) {
                                echo "<option value='{$class['id']}'>{$class['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sectionId">Section</label>
                        <select id="sectionId" name="sectionId">
                            <option value="">All Sections</option>
                            <?php
                            $sections_sql = "SELECT id, name FROM sections ORDER BY name";
                            $sections_result = $conn->query($sections_sql);
                            while ($section = $sections_result->fetch_assoc()) {
                                echo "<option value='{$section['id']}'>{$section['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">Create Session</button>
                        <button type="reset" class="btn btn-secondary">Reset</button>
                    </div>
                </form>
            </div>

            <!-- Statistics Overview -->
            <div class="exam-card">
                <h2>📈 Statistics Overview</h2>
                <div class="stats-grid">
                    <?php
                    // Get exam statistics
                    $stats_sql = "
                        SELECT 
                            COUNT(*) as total_sessions,
                            SUM(CASE WHEN session_type = 'SA' THEN 1 ELSE 0 END) as sa_sessions,
                            SUM(CASE WHEN session_type = 'FA' THEN 1 ELSE 0 END) as fa_sessions,
                            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_sessions,
                            COUNT(DISTINCT academic_year) as academic_years
                        FROM exam_sessions
                    ";
                    $stats_result = $conn->query($stats_sql);
                    $stats = $stats_result->fetch_assoc();

                    // Get subjects and marks count
                    $subjects_sql = "
                        SELECT 
                            COUNT(DISTINCT esub.id) as total_subjects,
                            COUNT(DISTINCT sem.id) as total_marks
                        FROM exam_subjects esub
                        LEFT JOIN student_exam_marks sem ON esub.id = sem.exam_subject_id
                    ";
                    $subjects_result = $conn->query($subjects_sql);
                    $subjects_stats = $subjects_result->fetch_assoc();
                    ?>
                    
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['total_sessions'] ?></div>
                        <div class="stat-label">Total Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['sa_sessions'] ?></div>
                        <div class="stat-label">SA Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['fa_sessions'] ?></div>
                        <div class="stat-label">FA Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $stats['active_sessions'] ?></div>
                        <div class="stat-label">Active Sessions</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $subjects_stats['total_subjects'] ?></div>
                        <div class="stat-label">Exam Subjects</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?= $subjects_stats['total_marks'] ?></div>
                        <div class="stat-label">Marks Entered</div>
                    </div>
                </div>
            </div>

            <!-- Existing Exam Sessions -->
            <div class="exam-card">
                <h2>📋 Existing Exam Sessions</h2>
                
                <?php
                $sessions_sql = "
                    SELECT 
                        es.*,
                        c.name as class_name,
                        s.name as section_name,
                        COUNT(DISTINCT esub.id) as subject_count,
                        COUNT(DISTINCT sem.id) as marks_entered,
                        ROUND(AVG(sem.marks_obtained), 2) as avg_marks
                    FROM exam_sessions es
                    LEFT JOIN classes c ON es.class_id = c.id
                    LEFT JOIN sections s ON es.section_id = s.id
                    LEFT JOIN exam_subjects esub ON es.id = esub.exam_session_id
                    LEFT JOIN student_exam_marks sem ON esub.id = sem.exam_subject_id
                    GROUP BY es.id
                    ORDER BY es.created_at DESC
                ";
                
                $sessions_result = $conn->query($sessions_sql);
                
                if ($sessions_result && $sessions_result->num_rows > 0) {
                    while ($session = $sessions_result->fetch_assoc()) {
                        $duration = date('M d', strtotime($session['start_date'])) . ' - ' . date('M d, Y', strtotime($session['end_date']));
                        $class_info = $session['class_name'] . ($session['section_name'] ? ' - ' . $session['section_name'] : '');
                        
                        echo "<div class='exam-card' style='margin-left: 20px; border-left: 4px solid " . ($session['session_type'] == 'SA' ? '#1976d2' : '#7b1fa2') . ";'>";
                        echo "<div class='exam-header'>";
                        echo "<div>";
                        echo "<h3>{$session['session_name']} <span class='session-type {$session['session_type']}'>{$session['session_type']}</span></h3>";
                        echo "<p><strong>Class:</strong> {$class_info} | <strong>Duration:</strong> {$duration}</p>";
                        echo "</div>";
                        echo "<span class='status {$session['status']}'>{$session['status']}</span>";
                        echo "</div>";
                        
                        echo "<div class='stats-grid'>";
                        echo "<div class='stat-item'><div class='stat-value'>{$session['subject_count']}</div><div class='stat-label'>Subjects</div></div>";
                        echo "<div class='stat-item'><div class='stat-value'>{$session['marks_entered']}</div><div class='stat-label'>Marks Entered</div></div>";
                        if ($session['avg_marks']) {
                            echo "<div class='stat-item'><div class='stat-value'>{$session['avg_marks']}</div><div class='stat-label'>Avg Marks</div></div>";
                        }
                        echo "<div class='stat-item'><div class='stat-value'>{$session['academic_year']}</div><div class='stat-label'>Academic Year</div></div>";
                        echo "</div>";
                        
                        // Show subjects for this session
                        $subjects_sql = "
                            SELECT 
                                esub.*,
                                s.name as subject_name,
                                COUNT(sem.id) as students_marked,
                                ROUND(AVG(sem.marks_obtained), 2) as avg_marks
                            FROM exam_subjects esub
                            JOIN subjects s ON esub.subject_id = s.id
                            LEFT JOIN student_exam_marks sem ON esub.id = sem.exam_subject_id
                            WHERE esub.exam_session_id = {$session['id']}
                            GROUP BY esub.id
                            ORDER BY esub.exam_date
                        ";
                        $subjects_result = $conn->query($subjects_sql);
                        
                        if ($subjects_result && $subjects_result->num_rows > 0) {
                            echo "<div class='subject-list'>";
                            echo "<h4>📚 Exam Subjects:</h4>";
                            while ($subject = $subjects_result->fetch_assoc()) {
                                echo "<div class='subject-item'>";
                                echo "<div>";
                                echo "<strong>{$subject['subject_name']}</strong><br>";
                                echo "<small>📅 {$subject['exam_date']} at {$subject['exam_time']} | 🏛️ {$subject['venue']} | ⏱️ {$subject['duration_minutes']}min</small>";
                                echo "</div>";
                                echo "<div style='text-align: right;'>";
                                echo "<div><strong>{$subject['total_marks']}</strong> marks</div>";
                                echo "<div><small>{$subject['students_marked']} students marked</small></div>";
                                if ($subject['avg_marks']) {
                                    echo "<div><small>Avg: {$subject['avg_marks']}</small></div>";
                                }
                                echo "</div>";
                                echo "</div>";
                            }
                            echo "</div>";
                        }
                        
                        echo "<div class='btn-group'>";
                        echo "<button onclick='manageSubjects({$session['id']})' class='btn btn-primary'>Manage Subjects</button>";
                        echo "<button onclick='viewMarks({$session['id']})' class='btn btn-info'>View Marks</button>";
                        echo "<button onclick='generateReport({$session['id']})' class='btn btn-success'>Generate Report</button>";
                        if ($session['status'] != 'completed') {
                            echo "<button onclick='editSession({$session['id']})' class='btn btn-warning'>Edit</button>";
                        }
                        echo "</div>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-center'>No exam sessions found. Create your first session above!</p>";
                }
                ?>
            </div>
        </main>
    </div>

    <script>
        function toggleCreateForm() {
            const form = document.getElementById('examSessionForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        // Form submission
        document.getElementById('examSessionForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData);
            
            fetch('exam_session_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'create_session',
                    ...data
                })
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ Exam session created successfully!');
                    location.reload();
                } else {
                    alert('❌ Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred while creating the exam session');
            });
        });

        // Management functions
        function manageSubjects(sessionId) {
            window.location.href = `manage_exam_subjects.php?session_id=${sessionId}`;
        }

        function viewMarks(sessionId) {
            window.location.href = `view_exam_marks.php?session_id=${sessionId}`;
        }

        function generateReport(sessionId) {
            window.open(`exam_report.php?session_id=${sessionId}`, '_blank');
        }

        function editSession(sessionId) {
            // Implementation for editing session
            console.log('Edit session:', sessionId);
            // Could open a modal or redirect to edit page
        }

        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
            
            document.getElementById('startDate').value = today.toISOString().split('T')[0];
            document.getElementById('endDate').value = nextWeek.toISOString().split('T')[0];
        });
    </script>
</body>
</html>
