<?php
/**
 * View and Manage Student Marks for Exam Subjects
 * Displays marks for specific exam subjects with input capabilities
 */

// Start session before any output
session_start();

require_once 'con.php';
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
    }      // Get all students from classes/sections associated with this exam session and their marks
    $students_sql = "
        SELECT st.*, sem.marks_obtained, 
               (sem.marks_obtained / es.total_marks) * 100 as percentage, 
               sem.grade_code as grade, 
               sem.remarks, sem.marked_at as recorded_at, sem.id as mark_id,
               c.name as class_name, sec.name as section_name
        FROM students st
        INNER JOIN exam_session_classes esc ON (st.class_id = esc.class_id AND st.section_id = esc.section_id)
        INNER JOIN exam_subjects es_session ON esc.exam_session_id = es_session.exam_session_id
        LEFT JOIN student_exam_marks sem ON st.user_id = sem.student_id 
            AND sem.exam_subject_id = ?
        LEFT JOIN exam_subjects es ON sem.exam_subject_id = es.id
        LEFT JOIN classes c ON st.class_id = c.id
        LEFT JOIN sections sec ON st.section_id = sec.id
        WHERE es_session.id = ?
        ORDER BY c.name, sec.name, st.roll_number, st.full_name
    ";
    $students_stmt = $conn->prepare($students_sql);
    $students_stmt->bind_param('ii', $exam_subject_id, $exam_subject_id);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/exam.css">
    <style>
        .marks-container {
            padding: 20px;
        }

        .marks-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .marks-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .marks-header h2 {
            margin: 10px 0 5px 0;
            font-size: 20px;
            opacity: 0.9;
        }

        .marks-header p {
            margin: 5px 0;
            opacity: 0.8;
        }

        .subject-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .subject-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .subject-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            border-color: #667eea;
        }

        .subject-card-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .subject-title {
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .marks-badge {
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .subject-meta {
            color: #718096;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .subject-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 600;
            color: #667eea;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subject-actions {
            display: flex;
            gap: 10px;
        }

        .btn-view-marks {
            flex: 1;
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
        }

        .btn-view-marks:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        .marks-table-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            background: #f8fafc;
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-title {
            font-size: 20px;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
        }

        .marks-table {
            width: 100%;
            border-collapse: collapse;
        }

        .marks-table th,
        .marks-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .marks-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }

        .marks-table tbody tr {
            transition: all 0.2s ease;
        }

        .marks-table tbody tr:hover {
            background: #f8fafc;
        }

        .student-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .student-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .marks-input {
            width: 80px;
            padding: 8px;
            border: 2px solid #e2e8f0;
            border-radius: 6px;
            text-align: center;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .marks-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .grade-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-align: center;
            min-width: 40px;
        }

        .grade-A1, .grade-A { background: #d4edda; color: #155724; }
        .grade-A2, .grade-B { background: #d1ecf1; color: #0c5460; }
        .grade-B1, .grade-B2, .grade-C { background: #fff3cd; color: #856404; }
        .grade-C1, .grade-C2, .grade-D { background: #f8d7da; color: #721c24; }
        .grade-E { background: #f5c6cb; color: #721c24; }

        .workflow-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .workflow-btn {
            padding: 12px 20px;
            border: 2px solid #e2e8f0;
            background: white;
            color: #4a5568;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .workflow-btn:hover {
            border-color: #667eea;
            color: #667eea;
            transform: translateY(-1px);
        }

        .workflow-btn.active {
            background: #667eea;
            border-color: #667eea;
            color: white;
        }

        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .bulk-actions select {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: white;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .action-btn.primary {
            background: #667eea;
            color: white;
        }

        .action-btn.success {
            background: #48bb78;
            color: white;
        }

        .action-btn:hover {
            transform: translateY(-1px);
            opacity: 0.9;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #a0aec0;
        }

        .empty-state svg {
            width: 64px;
            height: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #718096;
        }

        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content">
            <div class="marks-container">
                <!-- Breadcrumb Navigation -->
                <div class="breadcrumb">
                    <a href="exam_session_management.php">Exam Sessions</a>
                    <span>›</span>
                    <?php if ($show_session_overview): ?>
                        <span><?= htmlspecialchars($session['session_name']) ?></span>
                    <?php else: ?>
                        <a href="view_exam_marks.php?session_id=<?= $session_id ?>">Session Overview</a>
                        <span>›</span>
                        <span><?= htmlspecialchars($exam_subject['subject_name']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Workflow Navigation -->
                <div class="workflow-buttons">
                    <a href="exam_session_management.php" class="workflow-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Sessions
                    </a>
                    <?php if (!$show_session_overview): ?>
                        <a href="view_exam_marks.php?session_id=<?= $session_id ?>" class="workflow-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Session Overview
                        </a>
                        <a href="schedule.php?session_id=<?= $session_id ?>" class="workflow-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Schedule Exam
                        </a>
                        <a href="#" class="workflow-btn active">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            View Marks
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Header -->
                <div class="marks-header">
                    <h1><?= $show_session_overview ? 'Exam Session Overview' : 'Student Marks Management' ?></h1>
                    <?php if ($show_session_overview): ?>
                        <h2><?= htmlspecialchars($session['session_name']) ?></h2>
                        <p>📅 <?= date('M d, Y', strtotime($session['start_date'])) ?> - <?= date('M d, Y', strtotime($session['end_date'])) ?></p>
                        <p>📋 <?= htmlspecialchars($session['session_type']) ?> | <?= htmlspecialchars($session['academic_year']) ?></p>
                    <?php else: ?>
                        <h2><?= htmlspecialchars($exam_subject['subject_name']) ?> - <?= htmlspecialchars($exam_subject['assessment_name']) ?></h2>
                        <p>📅 Exam Date: <?= date('M d, Y', strtotime($exam_subject['exam_date'])) ?> | ⏱️ Duration: <?= $exam_subject['duration_minutes'] ?> minutes</p>
                        <p>📝 Total Marks: <?= $exam_subject['total_marks'] ?> | ✅ Passing Marks: <?= $exam_subject['passing_marks'] ?></p>
                    <?php endif; ?>
                </div>

                <?php if ($show_session_overview): ?>
                    <!-- Session Overview -->
                    <div class="marks-table-container">
                        <div class="table-header">
                            <h3 class="table-title">📚 Subjects in this Session</h3>
                        </div>
                        
                        <?php if ($subjects_result->num_rows > 0): ?>
                            <div class="subject-grid" style="padding: 20px;">
                                <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                    <div class="subject-card" onclick="viewSubjectMarks(<?= $subject['id'] ?>)">
                                        <div class="subject-card-header">
                                            <h4 class="subject-title"><?= htmlspecialchars($subject['subject_name']) ?></h4>
                                            <span class="marks-badge"><?= $subject['marks_recorded'] ?> recorded</span>
                                        </div>
                                        
                                        <div class="subject-meta">
                                            <div>📝 <?= htmlspecialchars($subject['assessment_name']) ?></div>
                                            <div>📅 <?= date('M d, Y', strtotime($subject['exam_date'])) ?></div>
                                            <?php if ($subject['exam_time']): ?>
                                                <div>⏰ <?= date('g:i A', strtotime($subject['exam_time'])) ?></div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="subject-stats">
                                            <div class="stat-item">
                                                <span class="stat-value"><?= $subject['total_marks'] ?></span>
                                                <span class="stat-label">Total Marks</span>
                                            </div>
                                            <div class="stat-item">
                                                <span class="stat-value"><?= $subject['duration_minutes'] ?>m</span>
                                                <span class="stat-label">Duration</span>
                                            </div>
                                        </div>
                                        
                                        <div class="subject-actions">
                                            <a href="view_exam_marks.php?exam_subject_id=<?= $subject['id'] ?>" 
                                               class="btn-view-marks" onclick="event.stopPropagation();">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                                </svg>
                                                View/Edit Marks
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                <h3>No Subjects Found</h3>
                                <p>No subjects have been added to this session yet.</p>
                                <a href="exam_session_management.php?session_id=<?= $session_id ?>" class="workflow-btn">
                                    Add Subjects
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <!-- Individual Subject Marks Management -->
                    
                    <!-- Statistics Section -->
                    <?php
                    $total_students = $students_result->num_rows;
                    $students_result->data_seek(0);
                    
                    $recorded_count = 0;
                    $total_marks = 0;
                    $grade_counts = [];
                    $pass_count = 0;
                    
                    while ($student = $students_result->fetch_assoc()) {
                        if (!is_null($student['marks_obtained'])) {
                            $recorded_count++;
                            $total_marks += $student['marks_obtained'];
                            
                            if ($student['marks_obtained'] >= $exam_subject['passing_marks']) {
                                $pass_count++;
                            }
                            
                            $grade = $student['grade'] ?? 'Not Graded';
                            $grade_counts[$grade] = ($grade_counts[$grade] ?? 0) + 1;
                        }
                    }
                    $students_result->data_seek(0);
                    
                    $average_marks = $recorded_count > 0 ? round($total_marks / $recorded_count, 2) : 0;
                    $completion_percentage = $total_students > 0 ? round(($recorded_count / $total_students) * 100, 1) : 0;
                    $pass_percentage = $recorded_count > 0 ? round(($pass_count / $recorded_count) * 100, 1) : 0;
                    ?>
                    
                    <div class="marks-table-container" style="margin-bottom: 20px;">
                        <div class="table-header">
                            <h3 class="table-title">📊 Performance Statistics</h3>
                        </div>
                        <div style="padding: 20px;">
                            <div class="subject-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 20px;">
                                <div class="stat-item">
                                    <span class="stat-value"><?= $total_students ?></span>
                                    <span class="stat-label">Total Students</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?= $recorded_count ?></span>
                                    <span class="stat-label">Marks Recorded</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?= $completion_percentage ?>%</span>
                                    <span class="stat-label">Completion</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?= $average_marks ?></span>
                                    <span class="stat-label">Average Marks</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?= $exam_subject['total_marks'] > 0 ? round($average_marks / $exam_subject['total_marks'] * 100, 1) : 0 ?>%</span>
                                    <span class="stat-label">Average %</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-value"><?= $pass_percentage ?>%</span>
                                    <span class="stat-label">Pass Rate</span>
                                </div>
                            </div>
                            
                            <?php if (!empty($grade_counts)): ?>
                                <div style="margin-top: 30px;">
                                    <h4 style="margin-bottom: 15px; color: #2d3748;">Grade Distribution</h4>
                                    <div class="subject-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 15px;">
                                        <?php foreach ($grade_counts as $grade => $count): ?>
                                            <div class="stat-item">
                                                <span class="stat-value"><?= $count ?></span>
                                                <span class="stat-label">Grade <?= $grade ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="bulk-actions">
                        <label style="font-weight: 500; color: #4a5568;">Quick Actions:</label>
                        <button onclick="saveAllMarks()" class="action-btn success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-3m-1 4l-3 3 7-7" />
                            </svg>
                            Save All Changes
                        </button>
                        <button onclick="bulkMarkEntry()" class="action-btn primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Bulk Entry
                        </button>
                        <input type="text" id="studentSearch" placeholder="🔍 Search students, class, or section..." 
                               style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; flex: 1; max-width: 300px;"
                               onkeyup="filterStudents()">
                        <select id="gradeFilter" onchange="filterByGrade()" 
                                style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <option value="">All Grades</option>
                            <option value="recorded">With Marks</option>
                            <option value="pending">Pending</option>
                            <?php foreach (array_keys($grade_counts) as $grade): ?>
                                <option value="<?= $grade ?>">Grade <?= $grade ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Student Marks Table -->
                    <div class="marks-table-container">
                        <div class="table-header">
                            <h3 class="table-title">👥 Student Marks Entry</h3>
                        </div>
                        <table class="marks-table" id="marksTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Student Name</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Marks Obtained</th>
                                    <th>Percentage</th>
                                    <th>Grade</th>
                                    <th>Remarks</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $students_result->fetch_assoc()): ?>
                                    <tr data-student-id="<?= $student['user_id'] ?>" data-mark-id="<?= $student['mark_id'] ?? '' ?>">
                                        <td>
                                            <div class="student-info">
                                                <div class="student-avatar">
                                                    <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
                                                </div>
                                                <span><?= htmlspecialchars($student['roll_number'] ?? 'N/A') ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($student['full_name']) ?></strong>
                                        </td>
                                        <td>
                                            <span style="color: #667eea; font-weight: 500;"><?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></span>
                                        </td>
                                        <td>
                                            <span style="color: #667eea; font-weight: 500;"><?= htmlspecialchars($student['section_name'] ?? 'N/A') ?></span>
                                        </td>
                                        <td>
                                            <input type="number" class="marks-input" 
                                                   name="marks_<?= $student['user_id'] ?>"
                                                   value="<?= $student['marks_obtained'] ?? '' ?>"
                                                   min="0" max="<?= $exam_subject['total_marks'] ?>"
                                                   step="0.5" 
                                                   onchange="calculateGrade(this, <?= $exam_subject['total_marks'] ?>, '<?= $exam_subject['session_type'] ?>')">
                                            <span style="color: #a0aec0; font-size: 12px; margin-left: 5px;">/ <?= $exam_subject['total_marks'] ?></span>
                                        </td>
                                        <td class="percentage">
                                            <?= $student['percentage'] ? round($student['percentage'], 1) . '%' : '-' ?>
                                        </td>
                                        <td class="grade">
                                            <?php if ($student['grade']): ?>
                                                <span class="grade-badge grade-<?= $student['grade'] ?>"><?= $student['grade'] ?></span>
                                            <?php else: ?>
                                                <span class="grade-badge">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <input type="text" 
                                                   style="width: 150px; padding: 8px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: 14px;"
                                                   name="remarks_<?= $student['user_id'] ?>"
                                                   value="<?= htmlspecialchars($student['remarks'] ?? '') ?>"
                                                   placeholder="Optional remarks">
                                        </td>
                                        <td class="last-updated" style="color: #718096; font-size: 12px;">
                                            <?= $student['recorded_at'] ? date('M d, Y H:i', strtotime($student['recorded_at'])) : '-' ?>
                                        </td>
                                        <td>
                                            <button onclick="saveStudentMark(<?= $student['user_id'] ?>)" 
                                                    class="action-btn primary" style="padding: 6px 12px; font-size: 12px;">
                                                Save
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Sidebar toggle function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }

        // View subject marks functionality
        function viewSubjectMarks(subjectId) {
            window.location.href = `view_exam_marks.php?exam_subject_id=${subjectId}`;
        }

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
                const className = row.cells[2].textContent.toLowerCase();
                const sectionName = row.cells[3].textContent.toLowerCase();
                const grade = row.querySelector('.grade-badge')?.textContent.trim() || '-';
                
                const matchesSearch = studentName.includes(searchTerm) || 
                                    rollNo.includes(searchTerm) || 
                                    className.includes(searchTerm) || 
                                    sectionName.includes(searchTerm);
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
