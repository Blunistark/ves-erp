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
   <style>
   * {
       margin: 0;
       padding: 0;
       box-sizing: border-box;
   }

   body {
       font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
       background-color: #f8fafc;
       color: #334155;
       line-height: 1.6;
       overflow-x: hidden;
   }

   /* Main layout container */
   .dashboard-container {
       display: flex;
       min-height: 100vh;
   }

   /* Main content area - positioned to avoid sidebar overlap */
   .main-content {
       flex: 1;
       margin-left: 280px; /* Match sidebar width */
       min-height: 100vh;
       background: #f8fafc;
       transition: margin-left 0.3s ease;
       position: relative;
   }

   /* Mobile responsiveness */
   @media (max-width: 1024px) {
       .main-content {
           margin-left: 0;
       }
   }

   /* Content container */
   .marks-container {
       padding: 16px 20px;
       max-width: 1400px;
       width: 100%;
       margin: 0 auto;
   }

   /* Header Section */
   .marks-header {
       background: white;
       border: 1px solid #e2e8f0;
       border-radius: 12px;
       padding: 24px;
       margin-bottom: 20px;
       box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
   }

   .marks-header h1 {
       font-size: 24px;
       font-weight: 700;
       color: #1e293b;
       margin-bottom: 8px;
   }

   .marks-header h2 {
       font-size: 18px;
       font-weight: 600;
       color: #3b82f6;
       margin-bottom: 12px;
   }

   .marks-header p {
       color: #64748b;
       margin-bottom: 4px;
       font-size: 14px;
   }

   /* Breadcrumb Navigation */
   .breadcrumb {
       display: flex;
       align-items: center;
       gap: 8px;
       margin-bottom: 16px;
       font-size: 14px;
       color: #64748b;
   }

   .breadcrumb a {
       color: #3b82f6;
       text-decoration: none;
   }

   .breadcrumb a:hover {
       text-decoration: underline;
   }

   /* Workflow Navigation */
   .workflow-buttons {
       display: flex;
       gap: 10px;
       margin-bottom: 16px;
       flex-wrap: wrap;
   }

   .workflow-btn {
       display: flex;
       align-items: center;
       gap: 6px;
       padding: 10px 16px;
       background: white;
       border: 1px solid #e2e8f0;
       border-radius: 8px;
       color: #64748b;
       text-decoration: none;
       font-weight: 500;
       font-size: 14px;
       transition: all 0.2s ease;
   }

   .workflow-btn:hover {
       border-color: #3b82f6;
       color: #3b82f6;
       background: #f8fafc;
   }

   .workflow-btn.active {
       background: #3b82f6;
       border-color: #3b82f6;
       color: white;
   }

   .workflow-btn svg {
       width: 16px;
       height: 16px;
   }

   /* Subject Grid Layout */
   .subject-grid {
       display: grid;
       grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
       gap: 16px;
       padding: 20px;
   }

   .subject-card {
       background: white;
       border: 1px solid #e2e8f0;
       border-radius: 12px;
       padding: 20px;
       transition: all 0.2s ease;
       cursor: pointer;
   }

   .subject-card:hover {
       border-color: #3b82f6;
       box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
       transform: translateY(-2px);
   }

   .subject-card-header {
       display: flex;
       justify-content: space-between;
       align-items: flex-start;
       margin-bottom: 12px;
   }

   .subject-title {
       font-size: 16px;
       font-weight: 600;
       color: #1e293b;
       margin: 0;
   }

   .marks-badge {
       background: #eff6ff;
       color: #3b82f6;
       padding: 4px 10px;
       border-radius: 16px;
       font-size: 11px;
       font-weight: 500;
   }

   .subject-meta {
       color: #64748b;
       font-size: 13px;
       margin-bottom: 16px;
   }

   .subject-meta div {
       margin-bottom: 3px;
   }

   .subject-stats {
       display: grid;
       grid-template-columns: repeat(2, 1fr);
       gap: 12px;
       margin-bottom: 16px;
   }

   .stat-item {
       text-align: center;
       padding: 10px;
       background: #f8fafc;
       border-radius: 8px;
   }

   .stat-value {
       font-size: 18px;
       font-weight: 700;
       color: #3b82f6;
       display: block;
   }

   .stat-label {
       font-size: 11px;
       color: #64748b;
       text-transform: uppercase;
       letter-spacing: 0.5px;
       margin-top: 2px;
   }

   .btn-view-marks {
       display: flex;
       align-items: center;
       justify-content: center;
       gap: 6px;
       width: 100%;
       background: #3b82f6;
       color: white;
       border: none;
       padding: 10px 14px;
       border-radius: 8px;
       font-size: 13px;
       font-weight: 500;
       text-decoration: none;
       transition: all 0.2s ease;
   }

   .btn-view-marks:hover {
       background: #2563eb;
       transform: translateY(-1px);
   }

   .btn-view-marks svg {
       width: 14px;
       height: 14px;
   }

   /* Table Containers */
   .marks-table-container {
       background: white;
       border: 1px solid #e2e8f0;
       border-radius: 12px;
       margin-bottom: 20px;
       overflow: hidden;
   }

   @media (max-width: 768px) {
       .marks-table-container {
           overflow-x: auto;
           -webkit-overflow-scrolling: touch;
       }
       
       .marks-table {
           min-width: 800px;
       }
   }

   .table-header {
       padding: 16px 20px;
       border-bottom: 1px solid #e2e8f0;
       background: #f8fafc;
   }

   .table-title {
       font-size: 16px;
       font-weight: 600;
       color: #1e293b;
       margin: 0;
   }

   /* Scrollable table wrapper for students table only */
   .table-scroll-wrapper {
       overflow-x: auto;
       -webkit-overflow-scrolling: touch;
   }

   /* Custom scrollbar for table */
   .table-scroll-wrapper::-webkit-scrollbar {
       height: 8px;
   }

   .table-scroll-wrapper::-webkit-scrollbar-track {
       background: #f1f5f9;
       border-radius: 4px;
   }

   .table-scroll-wrapper::-webkit-scrollbar-thumb {
       background: #cbd5e1;
       border-radius: 4px;
   }

   .table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
       background: #94a3b8;
   }

   .marks-table {
       width: 100%;
       border-collapse: collapse;
       min-width: 900px; /* Minimum width to ensure all columns are visible */
   }

   .marks-table th,
   .marks-table td {
       padding: 12px;
       text-align: left;
       border-bottom: 1px solid #f1f5f9;
       font-size: 14px;
       white-space: nowrap; /* Prevent text wrapping */
   }

   /* Specific column widths for better layout */
   .marks-table th:nth-child(1),
   .marks-table td:nth-child(1) {
       min-width: 200px; /* Student name column */
   }

   .marks-table th:nth-child(2),
   .marks-table td:nth-child(2) {
       min-width: 80px; /* Class column */
   }

   .marks-table th:nth-child(3),
   .marks-table td:nth-child(3) {
       min-width: 80px; /* Section column */
   }

   .marks-table th:nth-child(4),
   .marks-table td:nth-child(4) {
       min-width: 120px; /* Marks column */
   }

   .marks-table th:nth-child(5),
   .marks-table td:nth-child(5) {
       min-width: 100px; /* Percentage column */
   }

   .marks-table th:nth-child(6),
   .marks-table td:nth-child(6) {
       min-width: 80px; /* Grade column */
   }

   .marks-table th:nth-child(7),
   .marks-table td:nth-child(7) {
       min-width: 150px; /* Remarks column */
   }

   .marks-table th:nth-child(8),
   .marks-table td:nth-child(8) {
       min-width: 130px; /* Last updated column */
   }

   .marks-table th:nth-child(9),
   .marks-table td:nth-child(9) {
       min-width: 80px; /* Actions column */
   }

   .marks-table th {
       background: #f8fafc;
       font-weight: 600;
       color: #475569;
       font-size: 13px;
       position: sticky;
       top: 0;
       z-index: 2;
   }

   .marks-table tbody tr:hover {
       background: #f8fafc;
   }

   /* Student Info Display */
   .student-info {
       display: flex;
       align-items: center;
       gap: 10px;
   }

   .student-avatar {
       width: 36px;
       height: 36px;
       border-radius: 50%;
       background: #3b82f6;
       color: white;
       display: flex;
       align-items: center;
       justify-content: center;
       font-weight: 600;
       font-size: 14px;
       flex-shrink: 0; /* Prevent avatar from shrinking */
   }

   /* Form Elements */
   .marks-input {
       width: 75px;
       padding: 6px 8px;
       border: 1px solid #e2e8f0;
       border-radius: 6px;
       text-align: center;
       font-size: 13px;
       transition: all 0.2s ease;
   }

   .marks-input:focus {
       outline: none;
       border-color: #3b82f6;
       box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
   }

   /* Grade Badge Styles */
   .grade-badge {
       padding: 4px 8px;
       border-radius: 16px;
       font-size: 11px;
       font-weight: 600;
       text-align: center;
       min-width: 40px;
       display: inline-block;
   }

   .grade-A1, .grade-A { background: #dcfce7; color: #166534; }
   .grade-A2, .grade-B { background: #dbeafe; color: #1e40af; }
   .grade-B1, .grade-B2, .grade-C { background: #fef3c7; color: #d97706; }
   .grade-C1, .grade-C2, .grade-D { background: #fee2e2; color: #dc2626; }
   .grade-E { background: #fecaca; color: #b91c1c; }

   /* Bulk Actions Bar */
   .bulk-actions {
       display: flex;
       gap: 10px;
       align-items: center;
       margin-bottom: 20px;
       padding: 12px 16px;
       background: white;
       border: 1px solid #e2e8f0;
       border-radius: 8px;
       flex-wrap: wrap;
   }

   .bulk-actions label {
       font-weight: 600;
       color: #374151;
       font-size: 14px;
   }

   .bulk-actions input,
   .bulk-actions select {
       padding: 6px 10px;
       border: 1px solid #e2e8f0;
       border-radius: 6px;
       background: white;
       font-size: 13px;
   }

   .bulk-actions input:focus,
   .bulk-actions select:focus {
       outline: none;
       border-color: #3b82f6;
       box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
   }

   /* Action Buttons */
   .action-btn {
       display: inline-flex;
       align-items: center;
       gap: 4px;
       padding: 8px 12px;
       border: none;
       border-radius: 6px;
       font-weight: 500;
       font-size: 13px;
       cursor: pointer;
       transition: all 0.2s ease;
       white-space: nowrap;
   }

   .action-btn.primary {
       background: #3b82f6;
       color: white;
   }

   .action-btn.primary:hover {
       background: #2563eb;
   }

   .action-btn.success {
       background: #10b981;
       color: white;
   }

   .action-btn.success:hover {
       background: #059669;
   }

   .action-btn:hover {
       transform: translateY(-1px);
   }

   .action-btn svg {
       width: 14px;
       height: 14px;
   }

   /* Empty State */
   .empty-state {
       text-align: center;
       padding: 40px 20px;
       color: #64748b;
   }

   .empty-state svg {
       width: 48px;
       height: 48px;
       margin-bottom: 16px;
       opacity: 0.5;
   }

   .empty-state h3 {
       font-size: 16px;
       font-weight: 600;
       margin-bottom: 6px;
       color: #374151;
   }

   /* Progress Overlay */
   .progress-overlay {
       position: fixed;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
       background: rgba(0, 0, 0, 0.5);
       display: none;
       align-items: center;
       justify-content: center;
       z-index: 9999;
   }

   .progress-content {
       background: white;
       padding: 24px;
       border-radius: 12px;
       text-align: center;
       max-width: 350px;
       width: 90%;
   }

   .progress-bar {
       width: 100%;
       height: 6px;
       background: #f1f5f9;
       border-radius: 3px;
       overflow: hidden;
       margin: 12px 0;
   }

   .progress-bar-fill {
       height: 100%;
       background: #3b82f6;
       transition: width 0.3s ease;
   }

   /* Flash Messages */
   .success-flash {
       background: #dcfce7;
       border: 1px solid #bbf7d0;
       color: #166534;
       padding: 10px 14px;
       border-radius: 8px;
       margin-bottom: 12px;
       font-size: 14px;
   }

   .error-flash {
       background: #fee2e2;
       border: 1px solid #fecaca;
       color: #dc2626;
       padding: 10px 14px;
       border-radius: 8px;
       margin-bottom: 12px;
       font-size: 14px;
   }

   /* Highlighted row for operations */
   .marks-table tbody tr.highlighted {
       background: #eff6ff !important;
       border-left: 3px solid #3b82f6;
   }

   /* Responsive adjustments */
   @media (max-width: 768px) {
       .marks-container {
           padding: 12px;
       }

       .workflow-buttons {
           flex-direction: column;
       }

       .bulk-actions {
           flex-direction: column;
           align-items: stretch;
           gap: 8px;
       }

       .subject-grid {
           grid-template-columns: 1fr;
           padding: 16px;
       }

       .marks-table {
           font-size: 12px;
           min-width: 1000px; /* Increase min-width on mobile for better scrolling */
       }

       .marks-table th,
       .marks-table td {
           padding: 8px 6px;
       }

       .marks-header {
           padding: 16px;
       }

       .marks-header h1 {
           font-size: 20px;
       }

       .marks-header h2 {
           font-size: 16px;
       }

       /* Adjust sticky column width on mobile */
       .marks-table th:nth-child(1),
       .marks-table td:nth-child(1) {
           min-width: 180px;
       }
   }

   /* Ensure proper spacing from sidebar */
   @media (min-width: 1025px) {
       .main-content {
           margin-left: 280px; /* Exact sidebar width */
       }
   }

   /* Small screens - sidebar overlay */
   @media (max-width: 1024px) {
       .main-content {
           margin-left: 0;
           width: 100%;
       }
   }

   /* Scroll indicator for mobile */
   @media (max-width: 768px) {
       .table-scroll-wrapper::after {
           content: "← Scroll horizontally to see more →";
           display: block;
           text-align: center;
           padding: 8px;
           background: #f8fafc;
           color: #64748b;
           font-size: 12px;
           border-top: 1px solid #e2e8f0;
       }
   }
</style>
</head>
<body>
    <!-- Progress Overlay -->
    <div class="progress-overlay" id="progressOverlay">
        <div class="progress-content">
            <h3 id="progressTitle">Processing...</h3>
            <div class="progress-bar">
                <div class="progress-bar-fill" id="progressBarFill" style="width: 0%"></div>
            </div>
            <p id="progressMessage">Please wait...</p>
        </div>
    </div>

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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Sessions
                    </a>
                    <?php if (!$show_session_overview): ?>
                        <a href="view_exam_marks.php?session_id=<?= $session_id ?>" class="workflow-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            Session Overview
                        </a>
                        <a href="schedule.php?session_id=<?= $session_id ?>" class="workflow-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Schedule Exam
                        </a>
                        <a href="#" class="workflow-btn active">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <div class="subject-grid">
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
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                   
                   <div class="marks-table-container">
                       <div class="table-header">
                           <h3 class="table-title">📊 Performance Statistics</h3>
                       </div>
                       <div style="padding: 20px;">
                           <div class="subject-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 16px;">
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
                               <div style="margin-top: 20px;">
                                   <h4 style="margin-bottom: 12px; color: #1e293b; font-weight: 600; font-size: 15px;">Grade Distribution</h4>
                                   <div class="subject-stats" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 12px;">
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
                       <label>Quick Actions:</label>
                       <button onclick="saveAllMarks()" class="action-btn success">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-3m-1 4l-3 3 7-7" />
                           </svg>
                           Save All Changes
                       </button>
                       <button onclick="bulkMarkEntry()" class="action-btn primary">
                           <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                           </svg>
                           Bulk Entry
                       </button>
                       <input type="text" id="studentSearch" placeholder="🔍 Search students..." 
                              style="flex: 1; max-width: 250px;"
                              onkeyup="filterStudents()">
                       <select id="gradeFilter" onchange="filterByGrade()">
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
                                   <th>Student</th>
                                   <th>Class</th>
                                   <th>Section</th>
                                   <th>Marks</th>
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
                                               <div>
                                                   <div style="font-weight: 600; color: #1e293b; font-size: 14px;"><?= htmlspecialchars($student['full_name']) ?></div>
                                                   <div style="font-size: 11px; color: #64748b;">Roll: <?= htmlspecialchars($student['roll_number'] ?? 'N/A') ?></div>
                                               </div>
                                           </div>
                                       </td>
                                       <td>
                                           <span style="color: #3b82f6; font-weight: 500; font-size: 13px;"><?= htmlspecialchars($student['class_name'] ?? 'N/A') ?></span>
                                       </td>
                                       <td>
                                           <span style="color: #3b82f6; font-weight: 500; font-size: 13px;"><?= htmlspecialchars($student['section_name'] ?? 'N/A') ?></span>
                                       </td>
                                       <td>
                                           <div style="display: flex; align-items: center; gap: 6px;">
                                               <input type="number" class="marks-input" 
                                                      name="marks_<?= $student['user_id'] ?>"
                                                      value="<?= $student['marks_obtained'] ?? '' ?>"
                                                      min="0" max="<?= $exam_subject['total_marks'] ?>"
                                                      step="0.5" 
                                                      onchange="calculateGrade(this, <?= $exam_subject['total_marks'] ?>, '<?= $exam_subject['session_type'] ?>')">
                                               <span style="color: #64748b; font-size: 11px;">/ <?= $exam_subject['total_marks'] ?></span>
                                           </div>
                                       </td>
                                       <td class="percentage">
                                           <span style="font-weight: 500; color: #374151; font-size: 13px;">
                                               <?= $student['percentage'] ? round($student['percentage'], 1) . '%' : '-' ?>
                                           </span>
                                       </td>
                                       <td class="grade">
                                           <?php if ($student['grade']): ?>
                                               <span class="grade-badge grade-<?= $student['grade'] ?>"><?= $student['grade'] ?></span>
                                           <?php else: ?>
                                               <span class="grade-badge" style="background: #f1f5f9; color: #64748b;">-</span>
                                           <?php endif; ?>
                                       </td>
                                       <td>
                                           <input type="text" 
                                                  style="width: 130px; padding: 6px 8px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 12px;"
                                                  name="remarks_<?= $student['user_id'] ?>"
                                                  value="<?= htmlspecialchars($student['remarks'] ?? '') ?>"
                                                  placeholder="Optional remarks">
                                       </td>
                                       <td class="last-updated" style="color: #64748b; font-size: 11px;">
                                           <?= $student['recorded_at'] ? date('M d, Y H:i', strtotime($student['recorded_at'])) : '-' ?>
                                       </td>
                                       <td>
                                           <button onclick="saveStudentMark(<?= $student['user_id'] ?>)" 
                                                   class="action-btn primary" style="padding: 6px 10px; font-size: 11px;">
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
       // Progress overlay functions
       function showProgress(title, message) {
           document.getElementById('progressTitle').textContent = title;
           document.getElementById('progressMessage').textContent = message;
           document.getElementById('progressBarFill').style.width = '0%';
           document.getElementById('progressOverlay').style.display = 'flex';
       }

       function updateProgress(percentage, message) {
           document.getElementById('progressBarFill').style.width = percentage + '%';
           document.getElementById('progressMessage').textContent = message;
       }

       function hideProgress() {
           document.getElementById('progressOverlay').style.display = 'none';
           document.querySelectorAll('.marks-table tbody tr').forEach(r => r.classList.remove('highlighted'));
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
           const percentageCell = row.querySelector('.percentage span');
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
           if (!grade) {
               gradeCell.style.background = '#f1f5f9';
               gradeCell.style.color = '#64748b';
           }
       }

       // Save individual student mark
       function saveStudentMark(studentId) {
           const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
           const marksInput = row.querySelector(`input[name="marks_${studentId}"]`);
           const remarks = row.querySelector(`input[name="remarks_${studentId}"]`).value;
           const markId = row.dataset.markId;
           
           if (!marksInput.value || parseFloat(marksInput.value) < 0) {
               alert('Please enter valid marks');
               return;
           }
           
           // Add loading state
           const saveBtn = row.querySelector('button');
           const originalText = saveBtn.textContent;
           saveBtn.textContent = 'Saving...';
           saveBtn.disabled = true;
           
           const action = markId ? 'update_marks' : 'record_marks';
           const data = {
               action: action,
               examSubjectId: <?= $exam_subject_id ?>,
               studentId: studentId,
               marksObtained: parseFloat(marksInput.value),
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
                   // Show success feedback
                   showFlashMessage('Marks saved successfully!', 'success');
                   
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
                   showFlashMessage('Error: ' + result.message, 'error');
               }
           })
           .catch(error => {
               console.error('Error:', error);
               showFlashMessage('An error occurred while saving marks', 'error');
           })
           .finally(() => {
               saveBtn.textContent = originalText;
               saveBtn.disabled = false;
           });
       }

       // Flash message function
       function showFlashMessage(message, type) {
           const existingFlash = document.querySelector('.flash-message');
           if (existingFlash) {
               existingFlash.remove();
           }
           
           const flashDiv = document.createElement('div');
           flashDiv.className = `flash-message ${type === 'success' ? 'success-flash' : 'error-flash'}`;
           flashDiv.textContent = message;
           
           const container = document.querySelector('.marks-container');
           container.insertBefore(flashDiv, container.firstChild);
           
           setTimeout(() => {
               flashDiv.remove();
           }, 5000);
       }

       // Save all marks at once with progress tracking
       function saveAllMarks() {
           const rows = document.querySelectorAll('#marksTable tbody tr:not([style*="display: none"])');
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
               showFlashMessage(`Successfully saved marks for ${marksArray.length} students!`, 'success');
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
                   showFlashMessage(`Error saving marks for student ${index + 1}: ${result.message}`, 'error');
               }
           })
           .catch(error => {
               hideProgress();
               current.row.classList.remove('highlighted');
               console.error('Error:', error);
               showFlashMessage(`Error saving marks for student ${index + 1}`, 'error');
           });
       }

       // Bulk mark entry
       function bulkMarkEntry() {
           const visibleRows = document.querySelectorAll('#marksTable tbody tr:not([style*="display: none"])');
           const marks = prompt(`Enter marks for ${visibleRows.length} visible students (separated by commas):\nExample: 85,90,78,92...`);
           if (!marks) return;
           
           const marksArray = marks.split(',').map(m => m.trim());
           
           if (marksArray.length !== visibleRows.length) {
               alert(`Please enter exactly ${visibleRows.length} marks (one for each visible student)`);
               return;
           }
           
           visibleRows.forEach((row, index) => {
               const studentId = row.dataset.studentId;
               const marksInput = row.querySelector(`input[name="marks_${studentId}"]`);
               const mark = parseFloat(marksArray[index]);
               
               if (!isNaN(mark) && mark >= 0) {
                   marksInput.value = mark;
                   calculateGrade(marksInput, <?= $exam_subject['total_marks'] ?>, '<?= $exam_subject['session_type'] ?>');
               }
           });
           
           showFlashMessage('Marks entered! Click "Save All Changes" to save them.', 'success');
       }
       
       // Search and filter functionality
       function filterStudents() {
           const searchTerm = document.getElementById('studentSearch').value.toLowerCase();
           const gradeFilter = document.getElementById('gradeFilter').value;
           const rows = document.querySelectorAll('#marksTable tbody tr');
           let visibleCount = 0;
           
           rows.forEach(row => {
               const studentName = row.querySelector('.student-info div div').textContent.toLowerCase();
               const rollNo = row.querySelector('.student-info div div:last-child').textContent.toLowerCase();
               const className = row.cells[1].textContent.toLowerCase();
               const sectionName = row.cells[2].textContent.toLowerCase();
               const grade = row.querySelector('.grade-badge')?.textContent.trim() || '-';
               const hasMarks = row.querySelector(`input[name^="marks_"]`).value !== '';
               
               const matchesSearch = studentName.includes(searchTerm) || 
                                   rollNo.includes(searchTerm) || 
                                   className.includes(searchTerm) || 
                                   sectionName.includes(searchTerm);
               
               let matchesGrade = true;
               if (gradeFilter === 'recorded') {
                   matchesGrade = hasMarks;
               } else if (gradeFilter === 'pending') {
                   matchesGrade = !hasMarks;
               } else if (gradeFilter && gradeFilter !== '') {
                   matchesGrade = grade === gradeFilter;
               }
               
               if (matchesSearch && matchesGrade) {
                   row.style.display = '';
                   visibleCount++;
               } else {
                   row.style.display = 'none';
               }
           });
       }
       
       function filterByGrade() {
           filterStudents();
       }

       // Initialize on page load
       document.addEventListener('DOMContentLoaded', function() {
           // Auto-focus first empty marks input
           const firstEmptyInput = document.querySelector('input[name^="marks_"][value=""]');
           if (firstEmptyInput) {
               firstEmptyInput.focus();
           }
           
           // Add keyboard shortcuts
           document.addEventListener('keydown', function(e) {
               if (e.ctrlKey && e.key === 's') {
                   e.preventDefault();
                   saveAllMarks();
               }
               
               if (e.ctrlKey && e.key === 'f') {
                   e.preventDefault();
                   document.getElementById('studentSearch').focus();
               }
           });

           // Handle sidebar responsiveness
           function handleResize() {
               const sidebar = document.querySelector('.sidebar');
               const mainContent = document.querySelector('.main-content');
               
               if (window.innerWidth <= 1024) {
                   mainContent.style.marginLeft = '0';
               } else {
                   mainContent.style.marginLeft = '280px';
               }
           }

           window.addEventListener('resize', handleResize);
           handleResize(); // Call on initial load
       });
   </script>
</body>
</html>
