<?php
require_once 'con.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit;
}

$homework_id = $_GET['id'] ?? 0;
if (!$homework_id) {
    header('Location: homework.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Get homework details
$conn = getDbConnection();
$sql = "SELECT h.*, c.name AS class_name, s.name AS section_name, sub.name AS subject_name
        FROM homework h
        JOIN classes c ON h.class_id = c.id
        JOIN sections s ON h.section_id = s.id
        JOIN subjects sub ON h.subject_id = sub.id
        WHERE h.id = ? AND h.teacher_user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $homework_id, $teacher_id);
$stmt->execute();
$homework = $stmt->get_result()->fetch_assoc();

if (!$homework) {
    header('Location: homework.php');
    exit;
}

// Get submissions
$sql = "SELECT hs.*, s.full_name AS student_name, s.roll_number
        FROM homework_submissions hs
        JOIN students s ON hs.student_user_id = s.user_id
        WHERE hs.homework_id = ?
        ORDER BY hs.submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $homework_id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total students in class/section
$sql = "SELECT COUNT(*) as total_students
        FROM students
        WHERE class_id = ? AND section_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $homework['class_id'], $homework['section_id']);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total_students'];

$stmt->close();
$conn->close();
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Submissions</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/homework.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modern font and base styles */
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #fafbfc;
            color: #1a202c;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Fix sidebar positioning */
        .sidebar {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            height: 100vh !important;
            width: 260px !important;
            z-index: 999 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            background: white;
            border-right: 1px solid #e2e8f0;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        /* Modern hamburger button */
        .hamburger-btn {
            position: fixed !important;
            top: 20px;
            left: 20px;
            z-index: 1000 !important;
            background: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .hamburger-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .hamburger-btn:active {
            transform: translateY(0);
        }

        .hamburger-icon {
            width: 24px;
            height: 24px;
            color: #4a5568;
            transition: color 0.2s;
        }

        .hamburger-btn:hover .hamburger-icon {
            color: #2d3748;
        }

        /* Dashboard container with modern styling */
        .dashboard-container {
            margin-left: 260px;
            width: calc(100% - 260px);
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
    
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Modern content area */
        .dashboard-content {
            padding: 24px;
            max-width: 100%;
            min-height: calc(100vh - 48px);
            background: #fafbfc;
            border-radius: 24px 0 0 0;
            margin-top: 80px;
            position: relative;
        }

        /* Modern header with gradient */
        .dashboard-header {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            padding: 32px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0;
            box-shadow: none;
            border: none;
            margin: 0;
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0 0 8px 0;
            letter-spacing: -0.02em;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin: 0;
            font-weight: 400;
        }

        /* Modern stats grid */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 20px 20px 0 0;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .stat-title {
            font-size: 0.875rem;
            color: #64748b;
            margin-bottom: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 3rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            line-height: 1;
        }

        .stat-subtitle {
            font-size: 0.875rem;
            color: #64748b;
            margin-top: 12px;
            font-weight: 400;
        }

        .progress-container {
            width: 100%;
            height: 8px;
            background-color: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 4px;
            transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Modern card styles */
        .card {
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
            overflow: hidden;
            margin-bottom: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            padding: 28px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e293b;
            margin: 0 0 8px 0;
            letter-spacing: -0.01em;
        }

        .homework-meta {
            display: flex;
            gap: 16px;
            font-size: 0.875rem;
            color: #64748b;
            flex-wrap: wrap;
        }

        .homework-meta span {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-weight: 500;
        }

        .card-body {
            padding: 28px;
        }

        .description {
            color: #475569;
            line-height: 1.7;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        /* Modern table styles */
        .table-responsive {
            overflow-x: auto;
            margin: -28px;
            padding: 28px;
            border-radius: 20px;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            font-size: 0.875rem;
        }

        .table th {
            padding: 16px;
            text-align: left;
            background: #f8fafc;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table th:first-child {
            border-radius: 12px 0 0 0;
        }

        .table th:last-child {
            border-radius: 0 12px 0 0;
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        .table tr:hover {
            background-color: #f8fafc;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        /* Modern button styles */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 12px;
            font-weight: 500;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.875rem;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: white;
            color: #475569;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-sm {
            padding: 8px 16px;
            font-size: 0.8rem;
        }

        /* Modern status badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: capitalize;
            letter-spacing: 0.02em;
        }

        .status-badge.submitted {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }

        .status-badge.graded {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .status-badge.not_submitted {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        /* Mobile responsive design */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed !important;
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                height: 100vh !important;
                width: 260px !important;
                z-index: 999 !important;
            }
            
            .sidebar.show {
                transform: translateX(0) !important;
            }

            .dashboard-container {
                margin-left: 0;
                width: 100%;
                height: 100vh;
            }
            
            .dashboard-content {
                padding: 16px;
                margin-top: 120px;
                border-radius: 20px 20px 0 0;
                min-height: calc(100vh - 32px);
            }

            .dashboard-header {
                padding: 24px 16px;
            }

            .header-title {
                font-size: 2rem;
            }

            .header-subtitle {
                font-size: 1rem;
            }

            .hamburger-btn {
                top: 16px;
                left: 16px;
                padding: 10px;
            }

            .quick-stats {
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }

            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 2.2rem;
            }

            .card-header, .card-body {
                padding: 20px;
            }

            .table-responsive {
                margin: -20px;
                padding: 20px;
            }

            .table th,
            .table td {
                padding: 12px 8px;
                font-size: 0.8rem;
            }

            .btn {
                padding: 10px 16px;
                font-size: 0.8rem;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 0.75rem;
            }

            .homework-meta {
                gap: 8px;
            }

            .homework-meta span {
                padding: 4px 8px;
                font-size: 0.8rem;
            }
        }

        /* Tablet adjustments */
        @media (min-width: 769px) and (max-width: 1024px) {
            .dashboard-container {
                margin-left: 260px;
                width: calc(100% - 260px);
            }

            .quick-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Desktop optimizations */
        @media (min-width: 1025px) {
            .dashboard-content {
                padding: 32px;
                margin-top: 100px;
                min-height: calc(100vh - 64px);
            }

            .quick-stats {
                grid-template-columns: repeat(4, 1fr);
                gap: 24px;
            }
        }

        /* Sidebar collapsed state */
        .sidebar.collapsed + .dashboard-container {
            margin-left: 80px;
            width: calc(100% - 80px);
        }

        @media (max-width: 768px) {
            .sidebar.collapsed + .dashboard-container {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Modern modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(8px);
            padding: 20px;
        }

        .modal-content {
            background: white;
            margin: 5% auto;
            padding: 0;
            border: none;
            width: 90%;
            max-width: 500px;
            border-radius: 20px;
            position: relative;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            overflow: hidden;
        }

        .modal-header {
            padding: 28px;
            border-bottom: 1px solid #f1f5f9;
            background: #fafbfc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.2s;
        }

        .close-btn:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .modal-body {
            padding: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            font-size: 0.875rem;
        }

        .form-input, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.875rem;
            transition: all 0.2s;
            background: white;
        }

        .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .form-output {
            font-weight: 600;
            color: #1e293b;
            font-size: 1.1rem;
        }

        .form-actions {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
            font-style: italic;
            font-size: 1rem;
        }

        /* Sidebar overlay */
        .sidebar-overlay {
            position: fixed !important;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh !important;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* Large desktop screens */
        @media (min-width: 1400px) {
            .dashboard-content {
                padding: 40px;
                margin-top: 120px;
                min-height: calc(100vh - 80px);
            }

            .quick-stats {
                gap: 32px;
            }

            .stat-card {
                padding: 32px;
            }

            .header-title {
                font-size: 3rem;
            }
        }

        /* Smooth scrolling */
        .dashboard-container {
            scroll-behavior: smooth;
        }

        /* Loading animation for buttons */
        .btn:active {
            transform: scale(0.98);
        }

        /* Enhanced focus states for accessibility */
        .btn:focus-visible,
        .form-input:focus-visible,
        .form-textarea:focus-visible {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }
    </style>
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
            <h1 class="header-title">Homework Submissions</h1>
            <p class="header-subtitle">View and grade student submissions</p>
        </header>

        <main class="dashboard-content">
            <!-- Homework Details -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= htmlspecialchars($homework['title']) ?></h2>
                    <div class="homework-meta">
                        <span class="class-info">
                            <?= htmlspecialchars($homework['class_name']) ?>
                        </span>
                        <span class="section-info">
                            <?= htmlspecialchars($homework['section_name']) ?>
                        </span>
                        <span class="subject-info">
                            <?= htmlspecialchars($homework['subject_name']) ?>
                        </span>
                        <span class="due-date">Due: <?= date('M j, Y', strtotime($homework['due_date'])) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="description"><?= nl2br(htmlspecialchars($homework['description'])) ?></p>
                    <?php if ($homework['attachment']): ?>
                        <div class="attachment">
                            <a href="<?= htmlspecialchars($homework['attachment']) ?>" target="_blank" class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                View Attachment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submission Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-title">Total Submissions</div>
                    <div class="stat-value"><?= count($submissions) ?></div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= ($total_students > 0 ? (count($submissions) / $total_students * 100) : 0) ?>%"></div>
                    </div>
                    <div class="stat-subtitle"><?= count($submissions) ?> of <?= $total_students ?> students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Graded</div>
                    <div class="stat-value"><?= count(array_filter($submissions, fn($s) => $s['status'] === 'graded')) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Pending</div>
                    <div class="stat-value"><?= count(array_filter($submissions, fn($s) => $s['status'] !== 'graded')) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Average Grade</div>
                    <?php
                    $graded = array_filter($submissions, fn($s) => $s['status'] === 'graded');
                    $avg_grade = count($graded) > 0 ? array_sum(array_column($graded, 'grade_code')) / count($graded) : 0;
                    ?>
                    <div class="stat-value"><?= number_format($avg_grade, 1) ?></div>
                </div>
            </div>

            <!-- Submissions List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Student Submissions</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($submissions)): ?>
                        <div class="no-data">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 16px; color: #cbd5e1;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <br>No submissions yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Roll Number</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr data-submission-id="<?= $submission['id'] ?>">
                                            <td><strong><?= htmlspecialchars($submission['student_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($submission['roll_number']) ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></td>
                                            <td>
                                                <span class="status-badge <?= $submission['status'] ?>">
                                                    <?= ucfirst($submission['status']) ?>
                                                </span>
                                            </td>
                                            <td><strong><?= $submission['grade_code'] ?? '-' ?></strong></td>
                                            <td>
                                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                    <?php if ($submission['file_path']): ?>
                                                        <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                           </svg>
                                                           View
                                                       </a>
                                                   <?php endif; ?>
                                                   <button class="btn btn-primary btn-sm" onclick="gradeSubmission(<?= $submission['id'] ?>)">
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                       </svg>
                                                       Grade
                                                   </button>
                                               </div>
                                           </td>
                                       </tr>
                                   <?php endforeach; ?>
                               </tbody>
                           </table>
                       </div>
                   <?php endif; ?>
               </div>
           </div>
       </main>
   </div>

   <!-- Grade Submission Modal -->
   <div id="gradeModal" class="modal" style="display: none;">
       <div class="modal-content">
           <div class="modal-header">
               <h3>Grade Submission</h3>
               <button class="close-btn" onclick="closeGradeModal()">&times;</button>
           </div>
           <div class="modal-body">
               <form id="gradeForm">
                   <input type="hidden" id="submissionId" name="submission_id">
                   <div class="form-group">
                       <label for="totalMarks" class="form-label">Total Marks:</label>
                       <span id="totalMarks" class="form-output"></span>
                   </div>
                   <div class="form-group">
                       <label for="marksObtained" class="form-label">Marks Obtained</label>
                       <input type="number" id="marksObtained" name="marks_obtained" class="form-input" min="0" required>
                   </div>
                   <div class="form-group">
                       <label for="feedback" class="form-label">Feedback</label>
                       <textarea id="feedback" name="feedback" class="form-textarea" rows="4" placeholder="Enter your feedback for the student..."></textarea>
                   </div>
                   <div class="form-actions">
                       <button type="button" class="btn btn-secondary" onclick="closeGradeModal()">Cancel</button>
                       <button type="submit" class="btn btn-primary">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                           </svg>
                           Save Grade
                       </button>
                   </div>
               </form>
           </div>
       </div>
   </div>

   <script>
       // Sidebar toggle function
       function toggleSidebar() {
           const sidebar = document.querySelector('.sidebar');
           const overlay = document.querySelector('.sidebar-overlay');
           
           if (sidebar) {
               sidebar.classList.toggle('show');
           }
           if (overlay) {
               overlay.classList.toggle('active');
           }
       }

       // Close sidebar when clicking overlay
       document.querySelector('.sidebar-overlay')?.addEventListener('click', function() {
           const sidebar = document.querySelector('.sidebar');
           if (sidebar) {
               sidebar.classList.remove('show');
           }
           this.classList.remove('active');
       });

       // Grade submission modal
       function gradeSubmission(submissionId) {
           // Fetch submission details including total marks
           fetch('homework_actions.php?action=get_submissions&submission_id=' + submissionId)
               .then(response => response.json())
               .then(data => {
                   if (data.status === 'success' && data.submissions.length > 0) {
                       const submission = data.submissions[0]; // Assuming only one submission per student per homework
                       document.getElementById('submissionId').value = submission.id;
                       document.getElementById('totalMarks').textContent = submission.total_marks;
                       document.getElementById('marksObtained').value = submission.marks_obtained ?? ''; // Populate if already graded
                       document.getElementById('feedback').value = submission.feedback ?? ''; // Populate feedback
                       document.getElementById('gradeModal').style.display = 'block';
                   } else {
                       alert('Error fetching submission details: ' + (data.message || 'Submission not found.'));
                   }
               })
               .catch(error => {
                   console.error('Error:', error);
                   alert('Error fetching submission details. Please try again.');
               });
       }

       function closeGradeModal() {
           document.getElementById('gradeModal').style.display = 'none';
           document.getElementById('gradeForm').reset();
           // Clear total marks display
           document.getElementById('totalMarks').textContent = '';
       }

       // Handle grade form submission
       document.getElementById('gradeForm').addEventListener('submit', function(e) {
           e.preventDefault();
           const formData = {
               action: 'grade_homework',
               submission_id: document.getElementById('submissionId').value,
               marks_obtained: document.getElementById('marksObtained').value, // Send marks obtained
               feedback: document.getElementById('feedback').value
           };

           fetch('homework_actions.php', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json',
               },
               body: JSON.stringify(formData)
           })
           .then(response => response.json())
           .then(data => {
               if (data.status === 'success') {
                   // Update the specific row in the table instead of reloading the whole page
                   const submissionId = data.submission_id; // Get submission ID from response if needed, or use the one we have
                   const marksObtained = data.marks_obtained; // Get updated marks from response
                   const gradeCode = data.grade_code; // Get calculated grade from response

                   // Find the row in the table for this submission using the data-submission-id attribute
                   const row = document.querySelector(`tr[data-submission-id='${submissionId}']`);
                   if (row) {
                       // Update Grade and Marks Obtained columns
                       const cells = row.querySelectorAll('td');
                       // Assuming Grade is the 5th column (index 4) and Status is the 4th (index 3)
                       cells[4].innerHTML = '<strong>' + gradeCode + '</strong>'; // Update Grade column
                       
                        // If you want to display marks obtained in the table, you'll need to add a column for it
                        // and update the corresponding cell here.

                       const statusBadge = cells[3].querySelector('.status-badge'); 
                        if (statusBadge) {
                            statusBadge.textContent = 'Graded';
                            statusBadge.className = 'status-badge graded';
                        }
                   }

                   closeGradeModal();
                   // No need to reload: window.location.reload();
               } else {
                   alert('Error saving grade: ' + data.message);
               }
           })
           .catch(error => {
               console.error('Error:', error);
               alert('Error saving grade. Please try again.');
           });
       });

       // Close modal when clicking outside
       window.onclick = function(event) {
           const modal = document.getElementById('gradeModal');
           if (event.target === modal) {
               closeGradeModal();
           }
       }

       // Add smooth scrolling behavior
       document.querySelectorAll('a[href^="#"]').forEach(anchor => {
           anchor.addEventListener('click', function (e) {
               e.preventDefault();
               document.querySelector(this.getAttribute('href')).scrollIntoView({
                   behavior: 'smooth'
               });
           });
       });
   </script>
</body>
</html>
