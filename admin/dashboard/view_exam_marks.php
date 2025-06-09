<?php
/**
 * View and Manage Student Marks for Exam Subjects
 * Displays marks for specific exam subjects with input capabilities
 */

// Start session before any output
session_start();

require_once '../../con.php';
require_once '../../includes/functions.php';

// Check if user has permission
if (!hasRole(['admin', 'headmaster', 'teacher'])) {
    header('Location: ../../login.php');
    exit();
}

// Get parameters
$session_id = $_GET['session_id'] ?? 0;
$exam_subject_id = $_GET['exam_subject_id'] ?? 0;

if (!$session_id && !$exam_subject_id) {
    header('Location: exam_session_management.php');
    exit();
}

// If session_id provided, show all subjects for that session
if ($session_id && !$exam_subject_id) {
    // Get session details
    $session_sql = "SELECT * FROM exam_sessions WHERE id = ?";
    $session_stmt = $conn->prepare($session_sql);
    $session_stmt->bind_param('i', $session_id);
    $session_stmt->execute();
    $session = $session_stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        header('Location: exam_session_management.php');
        exit();
    }
      // Get all subjects for this session
    $subjects_sql = "
        SELECT es.*, s.name as subject_name, s.code as subject_code, 
               a.title as assessment_name,
               COUNT(sem.id) as marks_recorded
        FROM exam_subjects es
        JOIN subjects s ON es.subject_id = s.id
        JOIN assessments a ON es.assessment_id = a.id
        LEFT JOIN student_exam_marks sem ON es.id = sem.exam_subject_id
        WHERE es.exam_session_id = ?
        GROUP BY es.id
        ORDER BY es.exam_date, s.name
    ";
    $subjects_stmt = $conn->prepare($subjects_sql);
    $subjects_stmt->bind_param('i', $session_id);
    $subjects_stmt->execute();
    $subjects_result = $subjects_stmt->get_result();
      $page_title = "View Marks - " . $session['session_name'];
    $show_session_overview = true;
} else {
    // Show specific exam subject details
    $exam_subject_sql = "
        SELECT es.*, s.name as subject_name, s.code as subject_code, 
               a.title as assessment_name, sess.session_name, sess.session_type,
               sess.id as session_id
        FROM exam_subjects es
        JOIN subjects s ON es.subject_id = s.id
        JOIN assessments a ON es.assessment_id = a.id
        JOIN exam_sessions sess ON es.exam_session_id = sess.id
        WHERE es.id = ?
    ";
    $exam_subject_stmt = $conn->prepare($exam_subject_sql);
    $exam_subject_stmt->bind_param('i', $exam_subject_id);
    $exam_subject_stmt->execute();
    $exam_subject = $exam_subject_stmt->get_result()->fetch_assoc();
    
    if (!$exam_subject) {
        header('Location: exam_session_management.php');
        exit();
    }      // Get all students and their marks for this exam subject
    $students_sql = "
        SELECT st.*, sem.marks_obtained, 
               (sem.marks_obtained / es.total_marks) * 100 as percentage, 
               sem.grade_code as grade, 
               sem.remarks, sem.marked_at as recorded_at, sem.id as mark_id
        FROM students st
        LEFT JOIN student_exam_marks sem ON st.user_id = sem.student_id 
            AND sem.exam_subject_id = ?
        LEFT JOIN exam_subjects es ON sem.exam_subject_id = es.id
        ORDER BY st.full_name
    ";
    $students_stmt = $conn->prepare($students_sql);
    $students_stmt->bind_param('i', $exam_subject_id);
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    $page_title = "Marks - " . $exam_subject['subject_name'];
    $show_session_overview = false;
    $session_id = $exam_subject['session_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="css/view.css">
    <style>
        /* Smooth scrolling for the entire page */
        html {
            scroll-behavior: smooth;
        }
        
        /* Custom scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: #28a745;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #218838;
        }
        
        /* Scroll to top button */
        .scroll-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            font-size: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .scroll-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        .scroll-to-top:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        /* Quick navigation */
        .quick-nav {
            position: fixed;
            top: 50%;
            right: 20px;
            transform: translateY(-50%);
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 10px;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }
        .quick-nav.visible {
            opacity: 1;
            visibility: visible;
        }
        .quick-nav-item {
            display: block;
            padding: 8px 12px;
            margin: 4px 0;
            background: #f8f9fa;
            color: #495057;
            text-decoration: none;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            transition: all 0.2s ease;
        }
        .quick-nav-item:hover {
            background: #28a745;
            color: white;
        }
        .quick-nav-item.active {
            background: #007bff;
            color: white;
        }
        
        /* Enhanced table scrolling */
        .marks-table-container {
            max-height: 600px;
            overflow-y: auto;
            overflow-x: auto;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            position: relative;
        }
        .marks-table-container::-webkit-scrollbar {
            width: 12px;
            height: 12px;
        }
        
        /* Sticky header enhancement */
        .marks-table thead th {
            position: sticky;
            top: 0;
            background: #f8f9fa;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Scroll shadows for better UX */
        .marks-table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: linear-gradient(180deg, rgba(0,0,0,0.1) 0%, transparent 100%);
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 5;
        }        .marks-table-container.scrolled::before {
            opacity: 1;
        }
        
        /* Enhanced table interactions */
        .marks-table tbody tr {
            transition: all 0.2s ease;
        }
        .marks-table tbody tr:hover {
            background: #f8f9fa;
            transform: scale(1.001);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .marks-table tbody tr.highlighted {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
        }
        
        /* Smooth input focus effects */
        .marks-input:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            transform: scale(1.05);
        }
        
        /* Progress indicator for bulk operations */
        .progress-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .progress-content {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            min-width: 300px;
        }
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            height: 100%;
            background: #28a745;
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .marks-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .marks-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .marks-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .marks-table th, .marks-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .marks-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .marks-table tr:hover {
            background: #f8f9fa;
        }
        .marks-input {
            width: 80px;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .grade-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .grade-A1, .grade-A { background: #d4edda; color: #155724; }
        .grade-A2, .grade-B { background: #d1ecf1; color: #0c5460; }
        .grade-B1, .grade-B2, .grade-C { background: #fff3cd; color: #856404; }
        .grade-C1, .grade-C2, .grade-D { background: #f8d7da; color: #721c24; }
        .grade-E { background: #f5c6cb; color: #721c24; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin: 2px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-sm { padding: 4px 8px; font-size: 12px; }
        .btn:hover { opacity: 0.9; }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-box {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        .subject-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .subject-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #28a745;
        }    </style>
</head>
<body>
    <!-- Progress overlay for bulk operations -->
    <div class="progress-overlay" id="progressOverlay">
        <div class="progress-content">
            <h4 id="progressTitle">Processing...</h4>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <p id="progressText">Please wait...</p>
        </div>
    </div>
    
    <!-- Scroll to top button -->
    <button class="scroll-to-top" id="scrollToTopBtn" onclick="scrollToTop()">↑</button>
    
    <!-- Quick navigation -->
    <div class="quick-nav" id="quickNav">
        <a href="#header" class="quick-nav-item" onclick="scrollToSection('header')">📊 Header</a>
        <a href="#statistics" class="quick-nav-item" onclick="scrollToSection('statistics')">📈 Stats</a>
        <a href="#marks-table" class="quick-nav-item" onclick="scrollToSection('marks-table')">📋 Marks</a>
    </div>
    
    <div class="admin-container">
        <!-- Header -->
        <div class="marks-header" id="header">
            <h1>📊 <?= $show_session_overview ? 'Exam Marks Overview' : 'Student Marks Management' ?></h1>
            <?php if ($show_session_overview): ?>
                <h2><?= htmlspecialchars($session['session_name']) ?></h2>
                <p>📅 <?= date('M d, Y', strtotime($session['start_date'])) ?> - <?= date('M d, Y', strtotime($session['end_date'])) ?></p>
            <?php else: ?>
                <h2><?= htmlspecialchars($exam_subject['subject_name']) ?> - <?= htmlspecialchars($exam_subject['assessment_name']) ?></h2>
                <p>📅 Exam Date: <?= date('M d, Y', strtotime($exam_subject['exam_date'])) ?> | Max Marks: <?= $exam_subject['total_marks'] ?></p>
            <?php endif; ?>
            
            <div style="margin-top: 15px;">
                <a href="exam_session_management.php" class="btn btn-secondary">← Back to Sessions</a>
                <?php if (!$show_session_overview): ?>
                    <a href="manage_exam_subjects.php?session_id=<?= $session_id ?>" class="btn btn-secondary">Manage Subjects</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($show_session_overview): ?>
            <!-- Session Overview -->
            <div class="marks-card">
                <h3>📚 Subjects in this Session</h3>
                
                <?php if ($subjects_result->num_rows > 0): ?>
                    <div class="subject-overview">
                        <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                            <div class="subject-card">
                                <h4><?= htmlspecialchars($subject['subject_name']) ?></h4>
                                <p><strong>Assessment:</strong> <?= htmlspecialchars($subject['assessment_name']) ?></p>
                                <p><strong>Date:</strong> <?= date('M d, Y', strtotime($subject['exam_date'])) ?></p>
                                <p><strong>Max Marks:</strong> <?= $subject['total_marks'] ?></p>
                                <p><strong>Marks Recorded:</strong> <?= $subject['marks_recorded'] ?> students</p>
                                
                                <div style="margin-top: 15px;">
                                    <a href="view_exam_marks.php?exam_subject_id=<?= $subject['id'] ?>" 
                                       class="btn btn-primary btn-sm">View/Edit Marks</a>
                                    <a href="exam_report.php?exam_subject_id=<?= $subject['id'] ?>" 
                                       class="btn btn-success btn-sm" target="_blank">Generate Report</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No subjects found for this session. <a href="manage_exam_subjects.php?session_id=<?= $session_id ?>">Add subjects</a> to get started.</p>
                <?php endif; ?>
            </div>
              <?php else: ?>
            <!-- Individual Subject Marks Management -->
            
            <!-- Statistics -->
            <div id="statistics">
            <?php
            $total_students = $students_result->num_rows;
            $students_result->data_seek(0);
            
            $recorded_count = 0;
            $total_marks = 0;
            $grade_counts = [];
            
            while ($student = $students_result->fetch_assoc()) {
                if (!is_null($student['marks_obtained'])) {
                    $recorded_count++;
                    $total_marks += $student['marks_obtained'];
                    
                    $grade = $student['grade'] ?? 'Not Graded';
                    $grade_counts[$grade] = ($grade_counts[$grade] ?? 0) + 1;
                }
            }
            $students_result->data_seek(0);
            
            $average_marks = $recorded_count > 0 ? round($total_marks / $recorded_count, 2) : 0;
            $completion_percentage = $total_students > 0 ? round(($recorded_count / $total_students) * 100, 1) : 0;
            ?>
            
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value"><?= $total_students ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $recorded_count ?></div>
                    <div class="stat-label">Marks Recorded</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $completion_percentage ?>%</div>
                    <div class="stat-label">Completion</div>                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $average_marks ?></div>
                    <div class="stat-label">Average Marks</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value"><?= $exam_subject['total_marks'] > 0 ? round($average_marks / $exam_subject['total_marks'] * 100, 1) : 0 ?>%</div>
                    <div class="stat-label">Average %</div>
                </div>
            </div>

            <!-- Grade Distribution -->
            <?php if (!empty($grade_counts)): ?>
                <div class="marks-card">
                    <h3>📈 Grade Distribution</h3>
                    <div class="stats-row">
                        <?php foreach ($grade_counts as $grade => $count): ?>
                            <div class="stat-box">
                                <div class="stat-value"><?= $count ?></div>
                                <div class="stat-label">Grade <?= $grade ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>                </div>
            <?php endif; ?>
            </div>

            <!-- Student Marks Table -->
            <div class="marks-card" id="marks-table">                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3>👥 Student Marks</h3>
                    <div>
                        <button onclick="saveAllMarks()" class="btn btn-success">💾 Save All Changes</button>
                        <button onclick="bulkMarkEntry()" class="btn btn-primary">⚡ Bulk Entry</button>
                        <button onclick="scrollToSection('statistics')" class="btn btn-secondary">📈 View Stats</button>
                    </div>
                </div>
                
                <!-- Search and Filter Bar -->
                <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="studentSearch" placeholder="🔍 Search students..." 
                           style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; width: 250px;"
                           onkeyup="filterStudents()">
                    <select id="gradeFilter" onchange="filterByGrade()" 
                            style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">All Grades</option>
                        <option value="A+">A+</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="-">Not Graded</option>
                    </select>
                    <button onclick="clearFilters()" class="btn btn-secondary btn-sm">Clear Filters</button>
                    <span id="studentCount" style="margin-left: auto; color: #666; font-size: 14px;"></span>
                </div>
                
                <div class="marks-table-container" id="marksTableContainer">
                    <table class="marks-table" id="marksTable">
                        <thead>
                            <tr>
                                <th>Roll No</th>
                                <th>Student Name</th>
                                <th>Marks Obtained</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>                        <tbody>
                            <?php while ($student = $students_result->fetch_assoc()): ?>
                                <tr data-student-id="<?= $student['user_id'] ?>" data-mark-id="<?= $student['mark_id'] ?? '' ?>">
                                    <td><?= htmlspecialchars($student['roll_number']) ?></td>
                                    <td><?= htmlspecialchars($student['full_name']) ?></td>
                                    <td>
                                        <input type="number" class="marks-input" 
                                               name="marks_<?= $student['user_id'] ?>"
                                               value="<?= $student['marks_obtained'] ?? '' ?>"
                                               min="0" max="<?= $exam_subject['total_marks'] ?>"
                                               step="0.5" onchange="calculateGrade(this, <?= $exam_subject['total_marks'] ?>, '<?= $exam_subject['session_type'] ?>')">
                                        <span class="max-marks">/ <?= $exam_subject['total_marks'] ?></span>
                                    </td>
                                    <td class="percentage"><?= $student['percentage'] ? round($student['percentage'], 1) . '%' : '-' ?></td>
                                    <td class="grade">
                                        <?php if ($student['grade']): ?>
                                            <span class="grade-badge grade-<?= $student['grade'] ?>"><?= $student['grade'] ?></span>
                                        <?php else: ?>
                                            <span class="grade-badge">-</span>
                                        <?php endif; ?>                                    </td>
                                    <td>
                                        <input type="text" style="width: 150px; padding: 4px; border: 1px solid #ddd; border-radius: 3px;"
                                               name="remarks_<?= $student['user_id'] ?>"
                                               value="<?= htmlspecialchars($student['remarks'] ?? '') ?>"
                                               placeholder="Optional remarks">
                                    </td>
                                    <td class="last-updated">
                                        <?= $student['recorded_at'] ? date('M d, Y H:i', strtotime($student['recorded_at'])) : '-' ?>
                                    </td>
                                    <td>
                                        <button onclick="saveStudentMark(<?= $student['user_id'] ?>)" 
                                                class="btn btn-sm btn-primary">Save</button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Calculate grade based on marks and session type
        function calculateGrade(input, maxMarks, sessionType) {
            const marks = parseFloat(input.value) || 0;
            const percentage = (marks / maxMarks) * 100;
            
            const row = input.closest('tr');
            const percentageCell = row.querySelector('.percentage');
            const gradeCell = row.querySelector('.grade .grade-badge');
            
            // Update percentage
            percentageCell.textContent = marks > 0 ? percentage.toFixed(1) + '%' : '-';
            
            // Calculate grade
            let grade = '';
            if (marks > 0) {
                if (sessionType === 'SA') {
                    if (percentage >= 91) grade = 'A1';
                    else if (percentage >= 81) grade = 'A2';
                    else if (percentage >= 71) grade = 'B1';
                    else if (percentage >= 61) grade = 'B2';
                    else if (percentage >= 51) grade = 'C1';
                    else if (percentage >= 41) grade = 'C2';
                    else if (percentage >= 33) grade = 'D';
                    else grade = 'E';
                } else { // FA
                    if (percentage >= 80) grade = 'A';
                    else if (percentage >= 70) grade = 'B';
                    else if (percentage >= 60) grade = 'C';
                    else if (percentage >= 50) grade = 'D';
                    else grade = 'E';
                }
            }
            
            // Update grade
            gradeCell.textContent = grade || '-';
            gradeCell.className = 'grade-badge' + (grade ? ' grade-' + grade : '');
        }

        // Save individual student mark
        function saveStudentMark(studentId) {
            const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
            const marks = row.querySelector(`input[name="marks_${studentId}"]`).value;
            const remarks = row.querySelector(`input[name="remarks_${studentId}"]`).value;
            const markId = row.dataset.markId;
            
            if (!marks || marks < 0) {
                alert('Please enter valid marks');
                return;
            }
            
            const action = markId ? 'update_marks' : 'record_marks';
            const data = {
                action: action,
                examSubjectId: <?= $exam_subject_id ?>,
                studentId: studentId,
                marksObtained: parseFloat(marks),
                remarks: remarks
            };
            
            if (markId) {
                data.markId = markId;
            }
            
            fetch('exam_session_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert('✅ Marks saved successfully!');
                    // Update the last updated cell
                    const lastUpdatedCell = row.querySelector('.last-updated');
                    const now = new Date();
                    lastUpdatedCell.textContent = now.toLocaleDateString('en-US', {
                        month: 'short', day: 'numeric', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                    
                    // Update mark ID if it was a new record
                    if (!markId && result.mark_id) {
                        row.dataset.markId = result.mark_id;
                    }
                } else {
                    alert('❌ Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ An error occurred while saving marks');
            });
        }        // Save all marks at once with progress tracking
        function saveAllMarks() {
            const rows = document.querySelectorAll('#marksTable tbody tr:not([style*="display: none"])'); // Only visible rows
            const marksToSave = [];
            
            // Collect marks that need to be saved
            rows.forEach(row => {
                const studentId = row.dataset.studentId;
                const marksInput = row.querySelector(`input[name="marks_${studentId}"]`);
                
                if (marksInput.value && parseFloat(marksInput.value) >= 0) {
                    marksToSave.push({
                        row: row,
                        studentId: studentId,
                        marks: parseFloat(marksInput.value)
                    });
                }
            });
            
            if (marksToSave.length === 0) {
                alert('No marks to save');
                return;
            }
            
            // Show progress overlay
            showProgress('Saving Marks', `Saving marks for ${marksToSave.length} students...`);
            
            // Save marks sequentially with progress updates
            saveMarksSequentially(marksToSave, 0);
        }
        
        function saveMarksSequentially(marksArray, index) {
            if (index >= marksArray.length) {
                hideProgress();
                alert(`✅ Successfully saved marks for ${marksArray.length} students!`);
                return;
            }
            
            const current = marksArray[index];
            const progress = ((index + 1) / marksArray.length) * 100;
            
            updateProgress(progress, `Saving marks for student ${index + 1} of ${marksArray.length}...`);
            
            // Highlight current row
            document.querySelectorAll('.marks-table tbody tr').forEach(r => r.classList.remove('highlighted'));
            current.row.classList.add('highlighted');
            
            // Save the mark
            const row = current.row;
            const marks = row.querySelector(`input[name="marks_${current.studentId}"]`).value;
            const remarks = row.querySelector(`input[name="remarks_${current.studentId}"]`).value;
            const markId = row.dataset.markId;
            
            const action = markId ? 'update_marks' : 'record_marks';
            const data = {
                action: action,
                examSubjectId: <?= $exam_subject_id ?>,
                studentId: current.studentId,
                marksObtained: current.marks,
                remarks: remarks
            };
            
            if (markId) {
                data.markId = markId;
            }
            
            fetch('exam_session_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Update the last updated cell
                    const lastUpdatedCell = row.querySelector('.last-updated');
                    const now = new Date();
                    lastUpdatedCell.textContent = now.toLocaleDateString('en-US', {
                        month: 'short', day: 'numeric', year: 'numeric',
                        hour: '2-digit', minute: '2-digit'
                    });
                    
                    // Update mark ID if it was a new record
                    if (!markId && result.mark_id) {
                        row.dataset.markId = result.mark_id;
                    }
                    
                    // Continue with next mark
                    setTimeout(() => saveMarksSequentially(marksArray, index + 1), 300);
                } else {
                    hideProgress();
                    current.row.classList.remove('highlighted');
                    alert(`❌ Error saving marks for student ${index + 1}: ${result.message}`);
                }
            })
            .catch(error => {
                hideProgress();
                current.row.classList.remove('highlighted');
                console.error('Error:', error);
                alert(`❌ Error saving marks for student ${index + 1}`);
            });
        }

        // Bulk mark entry
        function bulkMarkEntry() {
            const marks = prompt('Enter marks for all students (separated by commas):\nExample: 85,90,78,92...');
            if (!marks) return;
            
            const marksArray = marks.split(',').map(m => m.trim());
            const rows = document.querySelectorAll('#marksTable tbody tr');
            
            if (marksArray.length !== rows.length) {
                alert(`Please enter exactly ${rows.length} marks (one for each student)`);
                return;
            }
            
            rows.forEach((row, index) => {
                const studentId = row.dataset.studentId;
                const marksInput = row.querySelector(`input[name="marks_${studentId}"]`);
                const mark = parseFloat(marksArray[index]);
                
                if (!isNaN(mark) && mark >= 0) {
                    marksInput.value = mark;
                    calculateGrade(marksInput, <?= $exam_subject['total_marks'] ?>, '<?= $exam_subject['session_type'] ?>');
                }
            });            alert('Marks entered! Click "Save All Changes" to save them.');
        }
        
        // Search and filter functionality
        function filterStudents() {
            const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
            const gradeFilter = document.getElementById('gradeFilter').value;
            const rows = document.querySelectorAll('#marksTable tbody tr');
            let visibleCount = 0;
            
            rows.forEach(row => {
                const studentName = row.cells[1].textContent.toLowerCase();
                const rollNo = row.cells[0].textContent.toLowerCase();
                const grade = row.querySelector('.grade-badge')?.textContent.trim() || '-';
                
                const matchesSearch = studentName.includes(searchTerm) || rollNo.includes(searchTerm);
                const matchesGrade = !gradeFilter || grade === gradeFilter;
                
                if (matchesSearch && matchesGrade) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });
            
            updateStudentCount(visibleCount, rows.length);
        }
        
        function filterByGrade() {
            filterStudents();
        }
        
        function clearFilters() {
            document.getElementById('studentSearch').value = '';
            document.getElementById('gradeFilter').value = '';
            filterStudents();
        }
        
        function updateStudentCount(visible, total) {
            const countElement = document.getElementById('studentCount');
            if (countElement) {
                countElement.textContent = `Showing ${visible} of ${total} students`;
            }
        }
        
        // Initialize student count
        function initializeStudentCount() {
            const rows = document.querySelectorAll('#marksTable tbody tr');
            updateStudentCount(rows.length, rows.length);
        }
        
        // Scroll functionality
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
        
        function scrollToSection(sectionId) {
            const element = document.getElementById(sectionId);
            if (element) {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update active nav item
                document.querySelectorAll('.quick-nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`[onclick="scrollToSection('${sectionId}')"]`)?.classList.add('active');
            }
        }
        
        // Handle scroll events
        function handleScroll() {
            const scrollToTopBtn = document.getElementById('scrollToTopBtn');
            const quickNav = document.getElementById('quickNav');
            const marksTableContainer = document.getElementById('marksTableContainer');
            
            // Show/hide scroll to top button
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
                quickNav.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
                quickNav.classList.remove('visible');
            }
            
            // Add scroll shadow to table
            if (marksTableContainer && marksTableContainer.scrollTop > 0) {
                marksTableContainer.classList.add('scrolled');
            } else if (marksTableContainer) {
                marksTableContainer.classList.remove('scrolled');
            }
            
            // Update active navigation based on scroll position
            const sections = ['header', 'statistics', 'marks-table'];
            const scrollPos = window.pageYOffset + 100;
            
            sections.forEach(sectionId => {
                const element = document.getElementById(sectionId);
                if (element) {
                    const elementTop = element.offsetTop;
                    const elementBottom = elementTop + element.offsetHeight;
                    
                    if (scrollPos >= elementTop && scrollPos < elementBottom) {
                        document.querySelectorAll('.quick-nav-item').forEach(item => {
                            item.classList.remove('active');
                        });
                        document.querySelector(`[onclick="scrollToSection('${sectionId}')"]`)?.classList.add('active');
                    }
                }
            });
        }
          // Initialize scroll functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize student count
            initializeStudentCount();
            
            // Add scroll event listener
            window.addEventListener('scroll', handleScroll);
            
            // Add scroll event to table container
            const marksTableContainer = document.getElementById('marksTableContainer');
            if (marksTableContainer) {
                marksTableContainer.addEventListener('scroll', handleScroll);
            }
            
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });
            
            // Keyboard shortcuts for scrolling
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey) {
                    switch(e.key) {
                        case 'Home':
                            e.preventDefault();
                            scrollToSection('header');
                            break;
                        case 'End':
                            e.preventDefault();
                            scrollToSection('marks-table');
                            break;
                        case 'ArrowUp':
                            e.preventDefault();
                            window.scrollBy({ top: -100, behavior: 'smooth' });
                            break;
                        case 'ArrowDown':
                            e.preventDefault();
                            window.scrollBy({ top: 100, behavior: 'smooth' });
                            break;
                    }
                }
            });
            
            // Auto-scroll to relevant section based on URL hash
            if (window.location.hash) {
                setTimeout(() => {
                    const target = document.querySelector(window.location.hash);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }, 100);
            }
        });
    </script>
</body>
</html>
