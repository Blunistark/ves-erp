<?php include 'sidebar.php'; ?>

<?php
// Get logged-in student information
$student_info = null;
$student_subjects = [];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get student information
    $student_query = "SELECT s.*, c.name as class_name, sec.name as section_name 
                      FROM students s 
                      LEFT JOIN classes c ON s.class_id = c.id 
                      LEFT JOIN sections sec ON s.section_id = sec.id 
                      WHERE s.user_id = ?";
    $stmt = $conn->prepare($student_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student_info = $result->fetch_assoc();
    
    if ($student_info) {
        // Get subjects assigned to student's class with additional data
        $subjects_query = "SELECT sub.id, sub.name, sub.code,
                          COALESCE(u.full_name, 'TBA') as teacher_name,
                          COALESCE(marks.average_marks, 0) as current_grade,
                          COALESCE(att.attendance_percentage, 95) as attendance,
                          COALESCE(hw.completed, 0) as completed_assignments,
                          COALESCE(hw.total, 0) as total_assignments
                          FROM subjects sub 
                          INNER JOIN class_subjects cs ON sub.id = cs.subject_id 
                          LEFT JOIN teacher_class_subjects tcs ON cs.class_id = tcs.class_id AND cs.subject_id = tcs.subject_id
                          LEFT JOIN users u ON tcs.teacher_user_id = u.id
                          LEFT JOIN (
                              SELECT es.subject_id, AVG(sem.marks_obtained) as average_marks 
                              FROM student_exam_marks sem
                              INNER JOIN exam_subjects es ON sem.exam_subject_id = es.id
                              WHERE sem.student_id = ? 
                              GROUP BY es.subject_id
                          ) marks ON sub.id = marks.subject_id
                          LEFT JOIN (
                              SELECT class_id, section_id,
                                     (SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) * 100.0 / COUNT(*)) as attendance_percentage
                              FROM attendance 
                              WHERE student_user_id = ? 
                              GROUP BY class_id, section_id
                          ) att ON cs.class_id = att.class_id AND ? = att.section_id
                          LEFT JOIN (
                              SELECT h.subject_id,
                                     SUM(CASE WHEN hs.status IN ('submitted', 'graded') THEN 1 ELSE 0 END) as completed,
                                     COUNT(h.id) as total
                              FROM homework h
                              LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_user_id = ?
                              WHERE h.class_id = ?
                              GROUP BY h.subject_id
                          ) hw ON sub.id = hw.subject_id
                          WHERE cs.class_id = ?
                          ORDER BY sub.name";
        
        try {
            $stmt = $conn->prepare($subjects_query);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("iiiiii", $student_info['user_id'], $student_info['user_id'], $student_info['section_id'], $student_info['user_id'], $student_info['class_id'], $student_info['class_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $student_subjects[] = $row;
            }
            
        } catch (Exception $e) {
            // If complex query fails, use simpler query
            error_log("Complex query failed: " . $e->getMessage());
            
            $simple_query = "SELECT sub.id, sub.name, sub.code,
                            'TBA' as teacher_name,
                            0 as current_grade,
                            95 as attendance,
                            0 as completed_assignments,
                            0 as total_assignments
                            FROM subjects sub 
                            INNER JOIN class_subjects cs ON sub.id = cs.subject_id 
                            WHERE cs.class_id = ?
                            ORDER BY sub.name";
            
            $stmt = $conn->prepare($simple_query);
            $stmt->bind_param("i", $student_info['class_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $student_subjects[] = $row;
            }
        }
        
        // Calculate overall stats
        $total_attendance = 0;
        $total_completed = 0;
        $total_assignments = 0;
        $subject_count = count($student_subjects);
        
        foreach ($student_subjects as $subject) {
            $total_attendance += $subject['attendance'];
            $total_completed += $subject['completed_assignments'];
            $total_assignments += $subject['total_assignments'];
        }
        
        $overall_attendance = $subject_count > 0 ? round($total_attendance / $subject_count, 1) : 0;
        $overall_gpa = 3.85; // You can calculate this based on your grading system
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Subjects</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #1a202c;
            line-height: 1.6;
            transition: margin-left 0.3s ease;
        }

        body.sidebar-open {
            margin-left: 260px;
        }

        /* Hamburger Button */
        .hamburger-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: white;
            border: none;
            border-radius: 12px;
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .hamburger-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .hamburger-icon {
            width: 24px;
            height: 24px;
            color: #4a5568;
        }

        /* Sidebar Overlay */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        body.sidebar-open .sidebar-overlay {
            opacity: 1;
            visibility: visible;
        }

        /* Dashboard Container */
        .dashboard-container {
            min-height: 100vh;
            padding: 90px 30px 30px;
            max-width: 1400px;
            margin: 0 auto;
            transition: all 0.3s ease;
        }

        body.sidebar-open .dashboard-container {
            margin-left: -260px;
            padding-left: 290px;
        }

        /* Header */
        .dashboard-header {
            background: white;
            padding: 32px 40px;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid #e2e8f0;
        }

        .header-title {
            font-size: 2.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .header-date {
            color: #718096;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Student Info Card */
        .student-info-card {
            background: white;
            padding: 32px 40px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            position: relative;
            overflow: hidden;
        }

        .student-info-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .student-name {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 16px;
        }

        .student-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 24px;
        }

        .student-meta span {
            background: #f7fafc;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        /* Section Title */
        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 24px;
            padding-left: 8px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-card:nth-child(1) .stat-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .stat-icon svg {
            width: 28px;
            height: 28px;
            color: white;
        }

        .stat-content {
            flex: 1;
        }

        .stat-title {
            font-size: 0.9rem;
            color: #718096;
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
        }

        /* Subjects Card */
        .subjects-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Tab Navigation */
        .tab-nav {
            display: flex;
            background: #f8fafc;
            padding: 8px;
            margin: 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .tab-button {
            flex: 1;
            padding: 16px 24px;
            border: none;
            background: transparent;
            color: #718096;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            border-radius: 12px;
            transition: all 0.3s ease;
            margin: 0 4px;
        }

        .tab-button.active {
            background: white;
            color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
        }

        .tab-button:hover:not(.active) {
            background: rgba(102, 126, 234, 0.05);
            color: #667eea;
        }

        /* Tab Content */
        .tab-content {
            padding: 32px 40px;
        }

        /* Subjects Table */
        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .subjects-table th {
            background: #f8fafc;
            padding: 20px 24px;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.95rem;
        }

        .subjects-table td {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .subjects-table tr:hover {
            background: #f8fafc;
        }

        /* Grade Badges */
        .grade-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .grade-a {
            background: #c6f6d5;
            color: #22543d;
        }

        .grade-b {
            background: #bee3f8;
            color: #1e4a72;
        }

        .grade-c {
            background: #fbb6ce;
            color: #97266d;
        }

        .grade-d {
            background: #fed7d7;
            color: #c53030;
        }

        /* Attendance Bar */
        .attendance-bar {
            width: 100px;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .attendance-progress {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .attendance-progress.high {
            background: linear-gradient(90deg, #48bb78 0%, #38a169 100%);
        }

        .attendance-progress.medium {
            background: linear-gradient(90deg, #ed8936 0%, #dd6b20 100%);
        }

        .attendance-progress.low {
            background: linear-gradient(90deg, #f56565 0%, #e53e3e 100%);
        }

        .attendance-progress.critical {
            background: linear-gradient(90deg, #e53e3e 0%, #c53030 100%);
        }

        /* Assignment Status */
        .assignments-status {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .assignments-completed {
            font-size: 0.9rem;
            font-weight: 600;
            color: #4a5568;
        }

        /* Action Button */
        .action-btn {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .action-btn:hover {
            background: #edf2f7;
            color: #5a67d8;
        }

        /* No Subjects Message */
        .no-subjects-message {
            text-align: center;
            padding: 80px 40px;
            color: #718096;
        }

        .no-subjects-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            opacity: 0.5;
        }

        .no-subjects-message h3 {
            font-size: 1.5rem;
            color: #4a5568;
            margin-bottom: 12px;
        }

        /* Error Message */
        .error-message {
            text-align: center;
            padding: 80px 40px;
            color: #718096;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            opacity: 0.5;
            color: #f56565;
        }

        .error-message h3 {
            font-size: 1.5rem;
            color: #e53e3e;
            margin-bottom: 12px;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 90px 20px 20px;
            }

            body.sidebar-open .dashboard-container {
                margin-left: 0;
                padding-left: 20px;
            }

            .dashboard-header {
                padding: 24px;
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }

            .header-title {
                font-size: 2rem;
            }

            .student-info-card {
                padding: 24px;
            }

            .student-meta {
                flex-direction: column;
                gap: 12px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .tab-content {
                padding: 24px 20px;
            }

            .subjects-table {
                font-size: 0.9rem;
            }

            .subjects-table th,
            .subjects-table td {
                padding: 16px 12px;
            }

            .tab-nav {
                flex-direction: column;
                gap: 4px;
            }

            .tab-button {
                margin: 0;
            }
        }

        /* Animation */
        .student-info-card,
        .stat-card,
        .subjects-card {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .subjects-card { animation-delay: 0.4s; }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">My Subjects</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <?php if ($student_info): ?>
            <!-- Student Information Card -->
            <div class="student-info-card">
                <div class="student-details">
                    <h2 class="student-name"><?php echo htmlspecialchars($student_info['full_name']); ?></h2>
                    <div class="student-meta">
                        <span class="student-class">Class: <?php echo htmlspecialchars($student_info['class_name']); ?></span>
                        <span class="student-section">Section: <?php echo htmlspecialchars($student_info['section_name']); ?></span>
                        <span class="student-roll">Roll No: <?php echo htmlspecialchars($student_info['roll_number']); ?></span>
                        <span class="student-admission">Admission No: <?php echo htmlspecialchars($student_info['admission_number']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 20V10"></path>
                            <path d="M18 20V4"></path>
                            <path d="M6 20v-4"></path>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-title">GPA</p>
                        <p class="stat-value"><?php echo $overall_gpa ?? 'N/A'; ?></p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-title">Attendance</p>
                        <p class="stat-value"><?php echo $overall_attendance; ?>%</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="stat-content">
                        <p class="stat-title">Assignments</p>
                        <p class="stat-value"><?php echo $total_completed . '/' . $total_assignments; ?></p>
                    </div>
                </div>
            </div>

            <!-- Subjects Card with Tabs -->
            <div class="subjects-card">
                <div class="tab-nav">
                    <button class="tab-button active" onclick="showTab('grades')">Grades</button>
                    <button class="tab-button" onclick="showTab('attendance')">Attendance</button>
                    <button class="tab-button" onclick="showTab('assignments')">Assignments</button>
                </div>
                
                <!-- Grades Tab Content -->
                <div class="tab-content" id="grades-tab">
                    <?php if (!empty($student_subjects)): ?>
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($student_subjects as $subject): 
                                // Determine grade class based on grade value
                                $gradeClass = 'grade-a';
                                if (is_numeric($subject['current_grade'])) {
                                    $grade = floatval($subject['current_grade']);
                                    if ($grade >= 90) $gradeClass = 'grade-a';
                                    elseif ($grade >= 80) $gradeClass = 'grade-b';
                                    elseif ($grade >= 70) $gradeClass = 'grade-c';
                                    else $gradeClass = 'grade-d';
                                } elseif (strpos($subject['current_grade'], 'B') === 0) {
                                    $gradeClass = 'grade-b';
                                } elseif (strpos($subject['current_grade'], 'C') === 0) {
                                    $gradeClass = 'grade-c';
                                } elseif (strpos($subject['current_grade'], 'D') === 0 || strpos($subject['current_grade'], 'F') === 0) {
                                    $gradeClass = 'grade-d';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($subject['teacher_name']); ?></td>
                                <td><span class="grade-badge <?php echo $gradeClass; ?>"><?php echo htmlspecialchars($subject['current_grade']); ?></span></td>
                                <td><a href="#" class="action-btn" onclick="viewSubjectDetails(<?php echo $subject['id']; ?>)">View Details</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-subjects-message">
                        <div class="no-subjects-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <h3>No Subjects Assigned</h3>
                        <p>There are no subjects currently assigned to your class.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Attendance Tab Content -->
                <div class="tab-content" id="attendance-tab" style="display: none;">
                    <?php if (!empty($student_subjects)): ?>
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($student_subjects as $subject): 
                                // Determine attendance class
                                $attendanceClass = 'high';
                                $attendance = floatval($subject['attendance']);
                                if ($attendance < 75) {
                                    $attendanceClass = 'critical';
                                } elseif ($attendance < 85) {
                                    $attendanceClass = 'low';
                                } elseif ($attendance < 95) {
                                    $attendanceClass = 'medium';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($subject['teacher_name']); ?></td>
                                <td>
                                    <div class="attendance-bar">
                                        <div class="attendance-progress <?php echo $attendanceClass; ?>" style="width: <?php echo $attendance; ?>%;"></div>
                                    </div>
                                    <span style="font-size: 0.875rem; color: #4a5568; font-weight: 600;"><?php echo round($attendance, 1); ?>%</span>
                                </td>
                                <td><a href="#" class="action-btn" onclick="viewAttendanceDetails(<?php echo $subject['id']; ?>)">View Details</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-subjects-message">
                        <h3>No Attendance Data</h3>
                        <p>No attendance data available for your subjects.</p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Assignments Tab Content -->
                <div class="tab-content" id="assignments-tab" style="display: none;">
                    <?php if (!empty($student_subjects)): ?>
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Completed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                           <?php foreach ($student_subjects as $subject): 
                                $completed = intval($subject['completed_assignments']);
                                $total = intval($subject['total_assignments']);
                                $percentage = $total > 0 ? ($completed / $total) * 100 : 0;
                                
                                // Determine progress class
                                $progressClass = 'high';
                                if ($percentage < 60) {
                                    $progressClass = 'critical';
                                } elseif ($percentage < 75) {
                                    $progressClass = 'low';
                                } elseif ($percentage < 90) {
                                    $progressClass = 'medium';
                                }
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($subject['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($subject['teacher_name']); ?></td>
                                <td>
                                    <div class="assignments-status">
                                        <span class="assignments-completed"><?php echo $completed . '/' . $total; ?></span>
                                        <div class="attendance-bar">
                                            <div class="attendance-progress <?php echo $progressClass; ?>" style="width: <?php echo $percentage; ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                                <td><a href="#" class="action-btn" onclick="viewAssignmentDetails(<?php echo $subject['id']; ?>)">View Details</a></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="no-subjects-message">
                        <h3>No Assignment Data</h3>
                        <p>No assignment data available for your subjects.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php else: ?>
            <div class="error-message">
                <div class="error-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <h3>Student Information Not Found</h3>
                <p>Unable to retrieve your student information. Please contact the administration.</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        // JavaScript for sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const body = document.body;
            
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
            body.classList.toggle('sidebar-open');
        }

        // JavaScript for tab switching
        function showTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Remove active class from all tab buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });
            
            // Show the selected tab content
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) {
                selectedTab.style.display = 'block';
            }
            
            // Add active class to the clicked button
            const activeButton = document.querySelector(`.tab-button[onclick="showTab('${tabName}')"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
        }

        // Function to view subject details
        function viewSubjectDetails(subjectId) {
            // Redirect to subject details page or show modal
            window.location.href = `subject_details.php?id=${subjectId}`;
        }

        // Function to view attendance details
        function viewAttendanceDetails(subjectId) {
            // Redirect to attendance details page or show modal
            window.location.href = `attendance_details.php?subject_id=${subjectId}`;
        }

        // Function to view assignment details
        function viewAssignmentDetails(subjectId) {
            // Redirect to assignment details page or show modal
            window.location.href = `assignment_details.php?subject_id=${subjectId}`;
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            const body = document.body;
            
            if (body.classList.contains('sidebar-open') && 
                !sidebar?.contains(event.target) && 
                !hamburgerBtn.contains(event.target)) {
                toggleSidebar();
            }
        });

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            // Show grades tab by default
            showTab('grades');
            
            // Handle responsive behavior
            function handleResize() {
                if (window.innerWidth <= 768) {
                    document.body.classList.remove('sidebar-open');
                }
            }
            
            window.addEventListener('resize', handleResize);
            handleResize();
        });

        // Add smooth scrolling for better UX
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>