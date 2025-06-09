<?php
/**
 * Exam Report Generation
 * Generates comprehensive reports for SA/FA examinations
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
$report_type = $_GET['type'] ?? 'detailed';

if (!$session_id && !$exam_subject_id) {
    header('Location: exam_session_management.php');
    exit();
}

// Determine what report to generate
if ($exam_subject_id) {    // Single subject report
    $exam_subject_sql = "
        SELECT es.*, s.name as subject_name, s.code as subject_code, 
               a.title as assessment_name, sess.session_name, sess.session_type,
               sess.academic_year, sess.start_date, sess.end_date
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
    }
    
    // Get student marks
    $marks_sql = "
        SELECT st.*, sem.marks_obtained, 
               (sem.marks_obtained / es.total_marks) * 100 as percentage, 
               sem.grade_code as grade, 
               sem.remarks, sem.marked_at as recorded_at
        FROM students st
        LEFT JOIN student_exam_marks sem ON st.user_id = sem.student_id 
            AND sem.exam_subject_id = ?
        LEFT JOIN exam_subjects es ON sem.exam_subject_id = es.id
        WHERE sem.marks_obtained IS NOT NULL
        ORDER BY (sem.marks_obtained / es.total_marks) * 100 DESC, st.full_name
    ";
    $marks_stmt = $conn->prepare($marks_sql);
    $marks_stmt->bind_param('i', $exam_subject_id);
    $marks_stmt->execute();
    $marks_result = $marks_stmt->get_result();
    
    $report_title = $exam_subject['subject_name'] . ' - ' . $exam_subject['assessment_name'];
    $is_single_subject = true;
} else {
    // Session-wide report
    $session_sql = "SELECT * FROM exam_sessions WHERE id = ?";
    $session_stmt = $conn->prepare($session_sql);
    $session_stmt->bind_param('i', $session_id);
    $session_stmt->execute();
    $session = $session_stmt->get_result()->fetch_assoc();
    
    if (!$session) {
        header('Location: exam_session_management.php');
        exit();
    }
      // Get all subjects and their statistics
    $subjects_sql = "
        SELECT es.*, s.name as subject_name, s.code as subject_code, 
               a.title as assessment_name,
               COUNT(sem.id) as students_appeared,
               AVG((sem.marks_obtained / es.total_marks) * 100) as avg_percentage,
               MAX((sem.marks_obtained / es.total_marks) * 100) as max_percentage,
               MIN((sem.marks_obtained / es.total_marks) * 100) as min_percentage
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
    
    $report_title = $session['session_name'];
    $is_single_subject = false;
}

// Calculate statistics
if ($is_single_subject) {
    $total_students = $marks_result->num_rows;
    $marks_result->data_seek(0);
    
    $total_marks = 0;
    $grade_counts = [];
    $pass_count = 0;
    
    while ($mark = $marks_result->fetch_assoc()) {
        $total_marks += $mark['marks_obtained'];
        
        $grade = $mark['grade'];
        $grade_counts[$grade] = ($grade_counts[$grade] ?? 0) + 1;
        
        // Count as pass if percentage >= 33%
        if ($mark['percentage'] >= 33) {
            $pass_count++;
        }
    }
    $marks_result->data_seek(0);
    
    $average_marks = $total_students > 0 ? round($total_marks / $total_students, 2) : 0;
    $average_percentage = $average_marks > 0 ? round(($average_marks / $exam_subject['total_marks']) * 100, 2) : 0;
    $pass_percentage = $total_students > 0 ? round(($pass_count / $total_students) * 100, 2) : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Report - <?= htmlspecialchars($report_title) ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background: #f8f9fa;
        }
        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .report-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .report-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .report-header h2 {
            margin: 10px 0 0 0;
            font-size: 1.5em;
            opacity: 0.9;
        }
        .report-meta {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #dee2e6;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .meta-item {
            display: flex;
            justify-content: space-between;
        }
        .meta-label {
            font-weight: 600;
            color: #495057;
        }
        .meta-value {
            color: #212529;
        }
        .report-content {
            padding: 30px;
        }
        .stats-section {
            margin-bottom: 40px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #667eea;
        }
        .stat-value {
            font-size: 2em;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9em;
            margin-top: 5px;
        }
        .table-section {
            margin-bottom: 40px;
        }
        .section-title {
            color: #495057;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .report-table th,
        .report-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .report-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .report-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        .grade-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .grade-A1, .grade-A { background: #d4edda; color: #155724; }
        .grade-A2, .grade-B { background: #d1ecf1; color: #0c5460; }
        .grade-B1, .grade-B2, .grade-C { background: #fff3cd; color: #856404; }
        .grade-C1, .grade-C2, .grade-D { background: #f8d7da; color: #721c24; }
        .grade-E { background: #f5c6cb; color: #721c24; }
        .grade-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .grade-item {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .grade-count {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }
        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
            font-size: 14px;
        }
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        @media print {
            .print-controls { display: none; }
            body { background: white; }
            .report-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <!-- Print Controls -->
    <div class="print-controls">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary">✖️ Close</button>
    </div>

    <div class="report-container">
        <!-- Report Header -->
        <div class="report-header">
            <h1>📊 Examination Report</h1>
            <h2><?= htmlspecialchars($report_title) ?></h2>
        </div>

        <!-- Report Metadata -->
        <div class="report-meta">
            <div class="meta-grid">
                <?php if ($is_single_subject): ?>
                    <div class="meta-item">
                        <span class="meta-label">Subject:</span>
                        <span class="meta-value"><?= htmlspecialchars($exam_subject['subject_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Subject Code:</span>
                        <span class="meta-value"><?= htmlspecialchars($exam_subject['subject_code']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Assessment:</span>
                        <span class="meta-value"><?= htmlspecialchars($exam_subject['assessment_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Session Type:</span>
                        <span class="meta-value"><?= $exam_subject['session_type'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Exam Date:</span>
                        <span class="meta-value"><?= date('F j, Y', strtotime($exam_subject['exam_date'])) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Maximum Marks:</span>
                        <span class="meta-value"><?= $exam_subject['total_marks'] ?></span>
                    </div>
                <?php else: ?>
                    <div class="meta-item">
                        <span class="meta-label">Session Name:</span>
                        <span class="meta-value"><?= htmlspecialchars($session['session_name']) ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Session Type:</span>
                        <span class="meta-value"><?= $session['session_type'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Academic Year:</span>
                        <span class="meta-value"><?= $session['academic_year'] ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Duration:</span>
                        <span class="meta-value"><?= date('M j', strtotime($session['start_date'])) ?> - <?= date('M j, Y', strtotime($session['end_date'])) ?></span>
                    </div>
                <?php endif; ?>
                <div class="meta-item">
                    <span class="meta-label">Generated On:</span>
                    <span class="meta-value"><?= date('F j, Y \a\t g:i A') ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Generated By:</span>
                    <span class="meta-value"><?= htmlspecialchars($_SESSION['name']) ?></span>
                </div>
            </div>
        </div>

        <div class="report-content">
            <?php if ($is_single_subject): ?>
                <!-- Single Subject Report -->
                
                <!-- Statistics -->
                <div class="stats-section">
                    <h3 class="section-title">📈 Performance Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <span class="stat-value"><?= $total_students ?></span>
                            <div class="stat-label">Students Appeared</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $average_marks ?></span>
                            <div class="stat-label">Average Marks</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $average_percentage ?>%</span>
                            <div class="stat-label">Average Percentage</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $pass_percentage ?>%</span>
                            <div class="stat-label">Pass Percentage</div>
                        </div>
                    </div>
                </div>

                <!-- Grade Distribution -->
                <?php if (!empty($grade_counts)): ?>
                    <div class="stats-section">
                        <h3 class="section-title">🏆 Grade Distribution</h3>
                        <div class="grade-distribution">
                            <?php foreach ($grade_counts as $grade => $count): ?>
                                <div class="grade-item">
                                    <div class="grade-count"><?= $count ?></div>
                                    <div class="grade-badge grade-<?= $grade ?>">Grade <?= $grade ?></div>
                                    <div style="font-size: 0.8em; color: #6c757d; margin-top: 5px;">
                                        <?= round(($count / $total_students) * 100, 1) ?>%
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Student Results -->
                <div class="table-section">
                    <h3 class="section-title">👥 Student Results</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Roll Number</th>
                                <th>Student Name</th>
                                <th>Marks Obtained</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank = 1;
                            while ($mark = $marks_result->fetch_assoc()): 
                            ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($mark['roll_number']) ?></td>
                                    <td><?= htmlspecialchars($mark['name']) ?></td>
                                    <td><?= $mark['marks_obtained'] ?> / <?= $exam_subject['total_marks'] ?></td>
                                    <td><?= round($mark['percentage'], 1) ?>%</td>
                                    <td><span class="grade-badge grade-<?= $mark['grade'] ?>"><?= $mark['grade'] ?></span></td>
                                    <td><?= htmlspecialchars($mark['remarks'] ?? '') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <!-- Session-wide Report -->
                
                <!-- Session Statistics -->
                <div class="stats-section">
                    <h3 class="section-title">📊 Session Overview</h3>
                    <div class="stats-grid">
                        <?php
                        $total_subjects = $subjects_result->num_rows;
                        $subjects_result->data_seek(0);
                        
                        $total_appeared = 0;
                        $overall_avg = 0;
                        $completed_subjects = 0;
                        
                        while ($subject = $subjects_result->fetch_assoc()) {
                            $total_appeared += $subject['students_appeared'];
                            if ($subject['avg_percentage']) {
                                $overall_avg += $subject['avg_percentage'];
                                $completed_subjects++;
                            }
                        }
                        $subjects_result->data_seek(0);
                        
                        $session_avg = $completed_subjects > 0 ? round($overall_avg / $completed_subjects, 2) : 0;
                        ?>
                        
                        <div class="stat-card">
                            <span class="stat-value"><?= $total_subjects ?></span>
                            <div class="stat-label">Total Subjects</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $total_appeared ?></span>
                            <div class="stat-label">Total Examinations</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $session_avg ?>%</span>
                            <div class="stat-label">Session Average</div>
                        </div>
                        <div class="stat-card">
                            <span class="stat-value"><?= $completed_subjects ?></span>
                            <div class="stat-label">Completed Subjects</div>
                        </div>
                    </div>
                </div>

                <!-- Subject-wise Performance -->
                <div class="table-section">
                    <h3 class="section-title">📚 Subject-wise Performance</h3>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Assessment</th>
                                <th>Exam Date</th>
                                <th>Max Marks</th>
                                <th>Students Appeared</th>
                                <th>Average %</th>
                                <th>Highest %</th>
                                <th>Lowest %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($subject['subject_name']) ?> (<?= htmlspecialchars($subject['subject_code']) ?>)</td>
                                    <td><?= htmlspecialchars($subject['assessment_name']) ?></td>
                                    <td><?= date('M j, Y', strtotime($subject['exam_date'])) ?></td>
                                    <td><?= $subject['total_marks'] ?></td>
                                    <td><?= $subject['students_appeared'] ?></td>
                                    <td><?= $subject['avg_percentage'] ? round($subject['avg_percentage'], 1) . '%' : '-' ?></td>
                                    <td><?= $subject['max_percentage'] ? round($subject['max_percentage'], 1) . '%' : '-' ?></td>
                                    <td><?= $subject['min_percentage'] ? round($subject['min_percentage'], 1) . '%' : '-' ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <!-- Footer -->
            <div style="margin-top: 50px; padding-top: 20px; border-top: 2px solid #dee2e6; text-align: center; color: #6c757d;">
                <p><strong>School Management System</strong></p>
                <p>This is a computer-generated report. No signature is required.</p>
                <p style="font-size: 0.8em;">Generated on <?= date('F j, Y \a\t g:i A') ?></p>
            </div>
        </div>
    </div>
</body>
</html>
