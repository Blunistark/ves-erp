<?php 
// Include sidebar which handles authentication
include 'sidebar.php'; 
include 'con.php';

// Get user information
$user_name = $_SESSION['full_name'] ?? 'Teacher';
$teacher_user_id = $_SESSION['user_id'] ?? 0;

// Get today's date
$today = date('Y-m-d');

// Cache file for dashboard data
$cache_file = __DIR__ . '/cache/dashboard_' . $teacher_user_id . '_' . date('Y-m-d') . '.json';
$cache_duration = 300; // 5 minutes

// Function to get cached data or fetch fresh data
function getCachedDashboardData($cache_file, $cache_duration, $conn, $teacher_user_id, $today) {
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
        return json_decode(file_get_contents($cache_file), true);
    }
    
    // Query to get only class teacher assignments (one class per teacher per day)
    $dashboardQuery = "
        SELECT 
            -- Class information
            c.id as class_id,
            c.name as class_name,
            s.id as section_id,
            s.name as section_name,
            
            -- Attendance information
            COALESCE(att_count.marked_count, 0) as marked_count,
            CASE WHEN att_count.marked_count > 0 THEN 1 ELSE 0 END as is_marked,
            
            -- Student count in each class
            COALESCE(student_count.total_students, 0) as total_students
            
        FROM classes c 
        JOIN sections s ON c.id = s.class_id 
        
        -- Get attendance count for today (timezone-aware)
        LEFT JOIN (
            SELECT class_id, section_id, COUNT(DISTINCT student_user_id) as marked_count
            FROM attendance 
            WHERE (DATE(date) = ? OR DATE(date) = DATE_SUB(?, INTERVAL 1 DAY))
            GROUP BY class_id, section_id
        ) att_count ON att_count.class_id = c.id AND att_count.section_id = s.id
        
        -- Get total students in each section
        LEFT JOIN (
            SELECT section_id, COUNT(DISTINCT user_id) as total_students
            FROM students st
            JOIN users u ON st.user_id = u.id 
            WHERE u.role = 'student' AND u.status = 'active'
            GROUP BY section_id
        ) student_count ON student_count.section_id = s.id
        
        WHERE s.class_teacher_user_id = ?
        ORDER BY c.name, s.name";
    
    $stmt = $conn->prepare($dashboardQuery);
    $stmt->bind_param("ssi", $today, $today, $teacher_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $classes = [];
    $stats = [
        'total_classes' => 0,
        'attendance_marked' => false,
        'total_students' => 0,
        'classes_today' => 0,
        'next_class_time' => null,
        'next_class_name' => null
    ];
    
    while ($row = $result->fetch_assoc()) {
        $class_key = $row['class_id'] . '-' . $row['section_id'];
        
        if (!isset($classes[$class_key])) {
            $classes[$class_key] = $row;
            $stats['total_classes']++;
            $stats['total_students'] += $row['total_students'];
            
            if ($row['is_marked']) {
                $stats['attendance_marked'] = true;
            }
        }
    }
    
    // Get today's schedule separately (with proper teacher ID mapping)
    $scheduleQuery = "
        SELECT 
            c.id as class_id,
            c.name as class_name,
            s.id as section_id,
            s.name as section_name,
            sub.name as subject_name,
            tp.start_time,
            tp.end_time,
            tp.day_of_week
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id AND tt.status = 'published'
        JOIN classes c ON tt.class_id = c.id
        JOIN sections s ON tt.section_id = s.id
        LEFT JOIN subjects sub ON tp.subject_id = sub.id
        WHERE tp.teacher_id = ? AND tp.day_of_week = ?
        ORDER BY tp.start_time";
    
    $day_of_week = strtolower(date('l'));
    $stmt = $conn->prepare($scheduleQuery);
    $stmt->bind_param("is", $teacher_user_id, $day_of_week);
    $stmt->execute();
    $schedule_result = $stmt->get_result();
    
    $schedule = [];
    $current_time = date('H:i:s');
    
    while ($row = $schedule_result->fetch_assoc()) {
        $schedule[] = $row;
        $stats['classes_today']++;
        
        // Find next class (first class that hasn't started yet)
        if ($stats['next_class_time'] === null && $row['start_time'] > $current_time) {
            $stats['next_class_time'] = $row['start_time'];
            $stats['next_class_name'] = $row['class_name'] . ' ' . $row['section_name'];
        }
    }
    
    $data = [
        'classes' => $classes,
        'schedule' => $schedule,
        'stats' => $stats,
        'timestamp' => time()
    ];
    
    // Cache the data
    if (!is_dir(dirname($cache_file))) {
        mkdir(dirname($cache_file), 0755, true);
    }
    file_put_contents($cache_file, json_encode($data));
    
    return $data;
}

// Get dashboard data (cached or fresh)
$dashboard_data = getCachedDashboardData($cache_file, $cache_duration, $conn, $teacher_user_id, $today);
$classes = $dashboard_data['classes'];
$schedule = $dashboard_data['schedule'];
$stats = $dashboard_data['stats'];

// Ensure all required stats keys exist (for backward compatibility with cache)
if (!isset($stats['next_class_time'])) {
    $stats['next_class_time'] = null;
}
if (!isset($stats['next_class_name'])) {
    $stats['next_class_name'] = null;
}
if (!isset($stats['attendance_marked'])) {
    $stats['attendance_marked'] = false;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Teacher Dashboard</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/index.css">
   <style>
        /* Enhanced styles matching student dashboard theme */
        /* DASHBOARD CONTAINER - MATCHES WORKING LAYOUT */
        body {
            margin: 0 !important;
            padding: 0 !important;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #ffffff !important;
            overflow-x: hidden !important;
        }

        html {
            margin: 0 !important;
            padding: 0 !important;
        }

        .dashboard-container {
            margin-left: 280px !important;
            padding: 2rem !important;
            background-color: #ffffff !important;
            min-height: 100vh !important;
            transition: margin-left 0.3s ease !important;
            width: calc(100% - 280px) !important;
            box-sizing: border-box !important;
            position: relative !important;
        }

        .sidebar.collapsed ~ .dashboard-container {
            margin-left: 60px !important;
            width: calc(100% - 60px) !important;
        }

        /* FIXED MOBILE OVERLAY - NO MORE UNRESPONSIVE SCREEN */
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
            pointer-events: none;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                margin-left: 0 !important;
                padding: 1rem !important;
                width: 100% !important;
            }
        }
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #ffffff;
        }

        .dashboard-container {
            margin-left: 280px;
            padding: 2rem;
            background-color: #ffffff;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed ~ .dashboard-container {
            margin-left: 60px;
        }

        /* HAMBURGER BUTTON */
        .hamburger-btn {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: #ffffff;
            color: #4a5568;
            border: 1px solid #e1e5e9;
            border-radius: 8px;
            padding: 12px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .hamburger-btn:hover {
            background: #f7fafc;
            transform: scale(1.05);
        }

        .hamburger-icon {
            width: 20px;
            height: 20px;
        }

        /* SIDEBAR OVERLAY */
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
            pointer-events: none;
        }

        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 0 30px 0;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 30px;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 25px;
            position: relative;
            z-index: 50;
        }
        
        .school-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header-title {
            margin: 0;
            color: #1a202c;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        
        .header-date {
            color: #718096;
            font-size: 0.95rem;
            font-weight: 500;
            background: #f7fafc;
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }

        /* NOTIFICATION BELL STYLES */
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
        }

        .notification-bell {
            position: relative;
            order: 2;
        }

        .notification-icon {
            position: relative;
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #4285f4, #0d47a1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(66, 133, 244, 0.3);
        }

        .notification-icon:hover {
            background: linear-gradient(135deg, #3367d6, #0b3d91);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(66, 133, 244, 0.4);
        }

        .notification-icon svg {
            width: 24px;
            height: 24px;
            color: white;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: linear-gradient(135deg, #ff4444, #cc0000);
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            min-width: 24px;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .notification-badge.hidden {
            display: none;
        }

        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 380px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            border: 1px solid #e0e0e0;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            margin-top: 8px;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .notification-header h3 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .mark-all-read {
            background: #4285f4;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .mark-all-read:hover {
            background: #3367d6;
        }

        .notification-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 16px 20px;
            border-bottom: 1px solid #f8f8f8;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: #f0f8ff;
            border-left: 4px solid #4285f4;
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .notification-type-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-type-icon.system { background: #e3f2fd; color: #1976d2; }
        .notification-type-icon.admin { background: #f3e5f5; color: #7b1fa2; }
        .notification-type-icon.teacher { background: #e8f5e8; color: #388e3c; }
        .notification-type-icon.announcement { background: #fff3e0; color: #f57c00; }
        .notification-type-icon.notice { background: #fce4ec; color: #c2185b; }

        .notification-details {
            flex: 1;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
            font-size: 14px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 8px;
        }

        .notification-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .notification-time {
            color: #999;
        }

        .notification-priority {
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .notification-priority.high { background: #ffebee; color: #d32f2f; }
        .notification-priority.medium { background: #fff3e0; color: #f57c00; }
        .notification-priority.low { background: #e8f5e8; color: #388e3c; }

        .notification-footer {
            padding: 16px 20px;
            border-top: 1px solid #f0f0f0;
            text-align: center;
        }

        .view-all-btn {
            color: #4285f4;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
        }

        .view-all-btn:hover {
            color: #3367d6;
        }

        .notification-loading, .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #999;
            font-style: italic;
        }
        
        /* Quick Stats Section */
        .quick-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
     .stat-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-color: #cbd5e0;
        }
        
        .stat-number {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #718096;
            font-weight: 500;
            line-height: 1.3;
        }
        
        /* Special styling for next class info */
        .stat-card.students .stat-number {
            font-size: 1.8rem;
        }
        
        .stat-card.students .stat-label {
            font-size: 0.8rem;
        }
        
        /* Enhanced dashboard cards */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .dashboard-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #cbd5e0;
        }
        
        .dashboard-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        .dashboard-card-title {
            margin: 0;
            font-size: 1.5rem;
            color: #1a202c;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        
      .dashboard-card-icon {
            width: 28px;
            height: 28px;
            color: #4299e1;
        }
        
        /* Enhanced attendance items */
        .attendance-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .attendance-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #cbd5e0;
        }
        
        .attendance-item:last-child {
            margin-bottom: 0;
        }
        
        .attendance-class-name {
            font-weight: 700;
            margin-bottom: 8px;
            color: #1a202c;
            font-size: 1.125rem;
        }
        
        .attendance-class-info {
            font-size: 0.875rem;
            color: #718096;
            font-weight: 500;
        }
        
        .attendance-action {
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.875rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-mark {
            background-color: #48bb78;
            color: white;
        }
        
        .btn-mark:hover {
            background-color: #38a169;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.3);
        }
        
        .btn-view {
            background-color: #4299e1;
            color: white;
        }
        
        .btn-view:hover {
            background-color: #3182ce;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.3);
        }
        
        /* Today's Schedule Card */
        .schedule-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }
        
        .schedule-item {
            display: flex;
            align-items: center;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 16px;
            transition: all 0.3s ease;
        }
        
        .schedule-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-color: #cbd5e0;
        }
        
        .schedule-item:last-child {
            margin-bottom: 0;
        }
        
        .schedule-time {
            width: 120px;
            padding-right: 20px;
        }
        
        .schedule-time-start {
            font-weight: 700;
            margin-bottom: 3px;
            color: #1a202c;
        }
        
        .schedule-time-end {
            font-size: 0.875rem;
            color: #718096;
        }
        
        .schedule-class {
            flex: 1;
        }
        
        .schedule-class-name {
            font-weight: 700;
            margin-bottom: 3px;
            color: #1a202c;
        }
        
        .schedule-class-subject {
            font-size: 0.875rem;
            color: #718096;
        }
        
        /* Navigation Cards Section */
        .nav-cards-section {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        /* Enhanced search */
        .search-container {
            position: relative;
            margin-bottom: 30px;
            max-width: 500px;
        }
        
        .search-input {
            width: 100%;
            padding: 16px 50px 16px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.2s ease;
            box-sizing: border-box;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .search-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }
        
        /* Enhanced nav cards */
        .nav-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .nav-card {
            display: flex;
            align-items: center;
            padding: 24px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .nav-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            border-color: #667eea;
            text-decoration: none;
            color: inherit;
        }
        
       .nav-card-icon {
            width: 48px;
            height: 48px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .nav-card-icon svg {
            width: 24px;
            height: 24px;
            color: #4299e1;
        }
        
        .nav-card-content h3 {
            margin: 0 0 8px 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
        }
        
        .nav-card-content p {
            margin: 0;
            font-size: 0.875rem;
            color: #6b7280;
            line-height: 1.4;
        }
        
        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }
        
        .skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .hamburger-btn {
                display: flex !important;
                align-items: center;
                justify-content: center;
            }

            .dashboard-container {
                margin-left: 0 !important;
                padding: 1rem !important;
            }

            .dashboard-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
                padding-bottom: 20px;
            }
            
            .header-left {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .header-right {
                flex-direction: row;
                justify-content: center;
                gap: 15px;
            }
            
            .school-logo {
                width: 40px;
                height: 40px;
            }
            
            .header-title {
                font-size: 1.5rem;
            }
            
            .notification-dropdown {
                width: 300px;
                right: -50px;
            }
            
            .user-welcome {
                flex-direction: column;
                text-align: center;
                gap: 20px;
                padding: 25px;
            }

            .welcome-text h2 {
                font-size: 1.5rem;
            }

            .date-time {
                min-width: auto;
                width: 100%;
            }
            
            .quick-stats {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .dashboard-cards {
                grid-template-columns: 1fr;
            }

            .nav-cards-section {
                padding: 20px;
            }
            
            .nav-cards-grid {
                grid-template-columns: 1fr;
            }

            .nav-card {
                padding: 20px;
            }

            .nav-card-icon {
                width: 44px;
                height: 44px;
                margin-right: 16px;
            }

            .nav-card-icon svg {
                width: 22px;
                height: 22px;
            }

            .nav-card-content h3 {
                font-size: 1rem;
            }

            .nav-card-content p {
                font-size: 0.8rem;
            }
        }
        
        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0.75rem !important;
            }

            .user-welcome {
                padding: 20px;
            }

            .welcome-text h2 {
                font-size: 1.25rem;
            }

            .nav-cards-section {
                padding: 16px;
            }

            .nav-card {
                padding: 16px;
            }

            .nav-card-icon {
                width: 40px;
                height: 40px;
                margin-right: 14px;
            }

            .nav-card-icon svg {
                width: 20px;
                height: 20px;
            }
            
            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .header-left {
                flex-direction: row;
                gap: 10px;
            }
            
            .header-right {
                flex-direction: row;
                justify-content: center;
                gap: 10px;
            }
            
            .school-logo {
                width: 35px;
                height: 35px;
            }
            
            .header-title {
                font-size: 1.2rem;
            }
            
            .notification-dropdown {
                width: 280px;
                right: -80px;
            }
        }
        
        /* Notification badge */
        .notification-badge {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #f56565;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border: 3px solid white;
            z-index: 110;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        
        /* Notification Bell Styles */
        .notification-container {
            position: relative;
            z-index: 100;
            order: 2; /* Move notification bell to the far right */
        }
        
        .notification-bell {
            background: #4299e1;
            border: 2px solid #2b77cb;
            cursor: pointer;
            position: relative;
            padding: 14px;
            border-radius: 50%;
            transition: all 0.2s ease;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(66, 153, 225, 0.3);
            min-width: 48px;
            min-height: 48px;
        }
        
        .notification-bell:hover {
            background-color: #3182ce;
            border-color: #2c5aa0;
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(66, 153, 225, 0.4);
        }
        
        .notification-bell svg {
            width: 22px;
            height: 22px;
        }
        
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 360px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow: hidden;
        }
        
        .notification-dropdown.show {
            display: block;
        }
        
        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .notification-header h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #1a202c;
        }
        
        .mark-all-read {
            background: none;
            border: none;
            color: #4299e1;
            font-size: 0.875rem;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
        }
        
        .mark-all-read:hover {
            background-color: #ebf8ff;
        }
        
        .notification-list {
            max-height: 280px;
            overflow-y: auto;
        }
        
        .notification-item {
            padding: 12px 20px;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
            transition: background-color 0.2s ease;
            position: relative;
        }
        
        .notification-item:hover {
            background-color: #f8fafc;
        }
        
        .notification-item.unread {
            background-color: #ebf8ff;
            border-left: 3px solid #4299e1;
        }
        
        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background-color: #4299e1;
            border-radius: 50%;
        }
        
        .notification-title {
            font-weight: 600;
            font-size: 0.875rem;
            color: #1a202c;
            margin-bottom: 4px;
        }
        
        .notification-message {
            font-size: 0.8rem;
            color: #4a5568;
            line-height: 1.4;
            margin-bottom: 6px;
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: #718096;
        }
        
        .notification-priority-urgent {
            border-left-color: #f56565 !important;
        }
        
        .notification-priority-important {
            border-left-color: #ed8936 !important;
        }
        
        .notification-loading {
            padding: 20px;
            text-align: center;
            color: #718096;
        }
        
        .notification-empty {
            padding: 40px 20px;
            text-align: center;
            color: #718096;
        }
        
        .notification-footer {
            padding: 12px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .view-all-link {
            display: block;
            text-align: center;
            color: #4299e1;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .view-all-link:hover {
            text-decoration: underline;
        }
        
        .no-classes {
            color: #718096;
            font-style: italic;
            padding: 20px 0;
            text-align: center;
            background: #f7fafc;
            border: 2px dashed #cbd5e0;
            border-radius: 16px;
            margin: 10px 0;
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
        <div class="header-left">
            <img src="../../assets/images/school-logo.png" alt="VES School Logo" class="school-logo">
            <h1 class="header-title">Teacher Dashboard</h1>
        </div>
        <div class="header-right">
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        

    <!-- Notification Bell - Fixed Position -->
    <div class="notification-bell" id="notificationBell" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
        <div class="notification-icon" onclick="toggleNotificationDropdown()">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notification-badge" id="notificationBadge">0</span>
                </div>
                
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <button class="mark-all-read" onclick="markAllAsRead()">Mark all as read</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-loading">Loading notifications...</div>
                    </div>
                    <div class="notification-footer">
                        <a href="notifications.php" class="view-all-btn">View All Notifications</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="dashboard-content">
        <!-- User Welcome Section -->
        <div class="user-welcome">
            <div class="welcome-text">
                <h2>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p>Here's your teaching overview for today. Stay organized and efficient!</p>
            </div>
            <div class="date-time">
                <div class="time" id="current-time">00:00:00</div>
                <div class="date"><?php echo date('l, F j, Y'); ?></div>
            </div>
        </div>
        
        <!-- Quick Stats Section -->
        <div class="quick-stats">
            <div class="stat-card attendance">
                <div class="stat-number"><?php echo $stats['total_classes']; ?></div>
                <div class="stat-label">Total Classes</div>
            </div>
            <div class="stat-card students">
                <?php if (isset($stats['next_class_time']) && $stats['next_class_time'] && isset($stats['next_class_name']) && $stats['next_class_name']): ?>
                    <div class="stat-number"><?php echo date('g:i A', strtotime($stats['next_class_time'])); ?></div>
                    <div class="stat-label">Next Class: <?php echo htmlspecialchars($stats['next_class_name']); ?></div>
                <?php elseif (isset($stats['classes_today']) && $stats['classes_today'] > 0): ?>
                    <div class="stat-number">Done</div>
                    <div class="stat-label">All Classes Complete</div>
                <?php else: ?>
                    <div class="stat-number">None</div>
                    <div class="stat-label">No Classes Today</div>
                <?php endif; ?>
            </div>
            <div class="stat-card schedule">
                <div class="stat-number"><?php echo $stats['total_students']; ?></div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>
        
        <!-- Dashboard Overview Cards -->
        <div class="dashboard-cards">
            <!-- Attendance Card -->
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">Today's Attendance</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="dashboard-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
                <p>Manage attendance for your assigned classes</p>
                <div class="attendance-status">
                    <?php 
                    if (!empty($classes)) {
                        foreach ($classes as $class) {
                            echo '<div class="attendance-item">';
                            echo '<div class="attendance-class">';
                            echo '<div class="attendance-class-name">' . htmlspecialchars($class['class_name'] . ' ' . $class['section_name']) . '</div>';
                            
                            if ($class['is_marked']) {
                                echo '<div class="attendance-class-info">' . $class['marked_count'] . '/' . $class['total_students'] . ' students marked</div>';
                            } else {
                                echo '<div class="attendance-class-info">' . $class['total_students'] . ' students - Not marked yet</div>';
                            }
                            
                            echo '</div>';
                            
                            if ($class['is_marked']) {
                                echo '<a href="attendance_details.php?class_id=' . $class['class_id'] . '&section_id=' . $class['section_id'] . '&date=' . $today . '" class="attendance-action btn-view">View Details</a>';
                            } else {
                                echo '<a href="custom-mark-attendance.php?class_id=' . $class['class_id'] . '&section_id=' . $class['section_id'] . '&date=' . $today . '" class="attendance-action btn-mark">Mark Now</a>';
                            }
                            
                            echo '</div>';
                        }
                    } else {
                        echo '<div class="no-classes">No classes assigned to you.</div>';
                    }
                    ?>
                </div>
            </div>

            <!-- Today's Schedule Card -->
            <?php if (!empty($schedule)): ?>
            <div class="dashboard-card">
                <div class="dashboard-card-header">
                    <h3 class="dashboard-card-title">Today's Schedule</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="dashboard-card-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <p>Your class schedule for <?php echo date('l'); ?></p>
                <div class="schedule-status">
                    <?php foreach ($schedule as $class): ?>
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <div class="schedule-time-start"><?php echo date('g:i A', strtotime($class['start_time'])); ?></div>
                            <div class="schedule-time-end"><?php echo date('g:i A', strtotime($class['end_time'])); ?></div>
                        </div>
                        <div class="schedule-class">
                            <div class="schedule-class-name"><?php echo htmlspecialchars($class['class_name'] . ' ' . $class['section_name']); ?></div>
                            <div class="schedule-class-subject"><?php echo htmlspecialchars($class['subject_name'] ?? 'Class Teacher'); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    
        <!-- Search and Navigation Cards Section -->
        <div class="nav-cards-section">
            <div class="search-container">
                <input type="text" id="cardSearch" class="search-input" placeholder="Search any feature...">
                <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <div class="nav-cards-grid">
                <!-- Attendance Management -->
                <a href="attendance.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Attendance Management</h3>
                        <p>Mark attendance for assigned classes</p>
                    </div>
                </a>

                <!-- Digital ID Card -->
                <a href="id-card.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <circle cx="12" cy="10" r="3"/>
                            <path d="M8 16a4 4 0 0 1 8 0"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Digital ID Card</h3>
                        <p>View teacher's digital ID</p>
                    </div>
                </a>

                <!-- Class Timetable -->
                <a href="timetable.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <path d="M3 9h18"/>
                            <path d="M9 21V9"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Class Timetable</h3>
                        <p>Access to assigned class schedules</p>
                    </div>
                </a>

                <!-- Student Performance Tracker -->
                <a href="performance.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 6l-9.5 9.5-5-5L1 18"/>
                            <path d="M17 6h6v6"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Student Performance</h3>
                        <p>Overview of students' grades and progress</p>
                    </div>
                </a>

                <!-- Homework Manager -->
                <a href="homework.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/>
                            <line x1="8" y1="7" x2="15" y2="7"/>
                            <line x1="8" y1="11" x2="15" y2="11"/>
                            <line x1="8" y1="15" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Homework Manager</h3>
                        <p>Assign, edit, and view submitted homework</p>
                    </div>
                </a>

                <!-- Exam & Test Schedules -->
                <a href="exams.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                            <path d="M8 14h2"/>
                            <path d="M14 14h2"/>
                            <path d="M8 18h2"/>
                            <path d="M14 18h2"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Exam & Test Schedules</h3>
                        <p>View and manage test/exam details</p>
                    </div>
                </a>

                <!-- Notifications & Announcements -->
                <a href="notifications.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Notifications</h3>
                        <p>Create and view announcements</p>
                    </div>
                </a>

                <!-- Class Notice Board -->
                <a href="notice.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/>
                            <path d="M12 8v8"/>
                            <path d="M8 12h8"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Class Notice Board</h3>
                        <p>Post and view class-specific notices</p>
                    </div>
                </a>

                <!-- Leave Requests -->
                <a href="leave.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                            <path d="M8 14h.01"/>
                            <path d="M12 14h.01"/>
                            <path d="M16 14h.01"/>
                            <path d="M8 18h.01"/>
                            <path d="M12 18h.01"/>
                            <path d="M16 18h.01"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Leave Requests</h3>
                        <p>Apply for leaves and view leave status</p>
                    </div>
                </a>

                <!-- Resources -->
                <a href="resources.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                            <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                            <path d="M12 11h4"></path>
                            <path d="M12 16h4"></path>
                            <path d="M8 11h.01"></path>
                            <path d="M8 16h.01"></path>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Resources</h3>
                        <p>Access teaching materials and resources</p>
                    </div>
                </a>

                <!-- Profile -->
                <a href="profile.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>My Profile</h3>
                        <p>View and update your profile information</p>
                    </div>
                </a>

                <!-- Online Classes -->
                <a href="online-classes.php" class="nav-card">
                    <div class="nav-card-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/>
                            <line x1="8" y1="21" x2="16" y2="21"/>
                            <line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                    </div>
                    <div class="nav-card-content">
                        <h3>Online Classes</h3>
                        <p>Schedule and manage virtual classes</p>
                    </div>
                </a>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Update current time with smooth animation
    function updateTime() {
        const currentTimeElement = document.getElementById('current-time');
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const newTime = `${hours}:${minutes}:${seconds}`;
        
        if (currentTimeElement.textContent !== newTime) {
            currentTimeElement.style.opacity = '0.7';
            setTimeout(() => {
                currentTimeElement.textContent = newTime;
                currentTimeElement.style.opacity = '1';
            }, 100);
        }
    }
    
    // Update time immediately and every second
    updateTime();
    setInterval(updateTime, 1000);
    
    // Enhanced search functionality with debouncing
    const searchInput = document.getElementById('cardSearch');
    const navCards = document.querySelectorAll('.nav-card');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = this.value.trim().toLowerCase();
        
        searchTimeout = setTimeout(() => {
            navCards.forEach(card => {
                const cardTitle = card.querySelector('h3').textContent.toLowerCase();
                const cardDescription = card.querySelector('p').textContent.toLowerCase();
                
                if (searchTerm === '' || cardTitle.includes(searchTerm) || cardDescription.includes(searchTerm)) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                } else {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(-10px)';
                    setTimeout(() => {
                        if (card.style.opacity === '0') {
                            card.style.display = 'none';
                        }
                    }, 200);
                }
            });
        }, 300);
    });
    
    // Add loading states for attendance actions
    document.querySelectorAll('.attendance-action').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!this.classList.contains('loading')) {
                this.classList.add('loading');
                this.textContent = this.classList.contains('btn-mark') ? 'Loading...' : 'Loading...';
            }
        });
    });
    
    // Add smooth scroll for better UX
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add intersection observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe cards for animation
    document.querySelectorAll('.dashboard-card, .nav-card, .stat-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });
    
    // Auto-refresh dashboard data every 5 minutes
    setInterval(() => {
        // Only refresh if the page is visible
        if (!document.hidden) {
            fetch(window.location.href, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => {
                if (response.ok) {
                    console.log('Dashboard data refreshed');
                }
            }).catch(error => {
                console.log('Auto-refresh failed:', error);
            });
        }
    }, 300000); // 5 minutes

    // NOTIFICATION SYSTEM JAVASCRIPT
    let notificationDropdownOpen = false;

    // Toggle notification dropdown
    function toggleNotificationDropdown() {
        const dropdown = document.getElementById('notificationDropdown');
        notificationDropdownOpen = !notificationDropdownOpen;
        
        if (notificationDropdownOpen) {
            dropdown.classList.add('show');
            loadNotifications();
        } else {
            dropdown.classList.remove('show');
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const bell = document.getElementById('notificationBell');
        const dropdown = document.getElementById('notificationDropdown');
        
        if (!bell.contains(event.target) && notificationDropdownOpen) {
            dropdown.classList.remove('show');
            notificationDropdownOpen = false;
        }
    });

    // Load notifications from API
    async function loadNotifications() {
        try {
            const response = await fetch('notification_api.php?action=get_notifications&limit=10');
            const data = await response.json();
            
            if (data.notifications) {
                displayNotifications(data.notifications);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            document.getElementById('notificationList').innerHTML = 
                '<div class="notification-empty">Failed to load notifications</div>';
        }
    }

    // Display notifications in the dropdown
    function displayNotifications(notifications) {
        const listContainer = document.getElementById('notificationList');
        
        if (notifications.length === 0) {
            listContainer.innerHTML = '<div class="notification-empty">No notifications yet</div>';
            return;
        }
        
        const notificationsHTML = notifications.map(notification => {
            const typeIcon = getNotificationTypeIcon(notification.type);
            const unreadClass = notification.is_read == '0' ? 'unread' : '';
            
            return `
                <div class="notification-item ${unreadClass}" onclick="markAsRead(${notification.id})">
                    <div class="notification-content">
                        <div class="notification-type-icon ${notification.type}">
                            ${typeIcon}
                        </div>
                        <div class="notification-details">
                            <div class="notification-title">${notification.title || 'Notification'}</div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-meta">
                                <span class="notification-time">${notification.time_ago}</span>
                                <span class="notification-priority ${notification.priority}">${notification.priority}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        listContainer.innerHTML = notificationsHTML;
    }

    // Get icon for notification type
    function getNotificationTypeIcon(type) {
        const icons = {
            system: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
            admin: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>',
            teacher: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
            announcement: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
            notice: '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>'
        };
        return icons[type] || icons.system;
    }

    // Mark single notification as read
    async function markAsRead(notificationId) {
        try {
            const formData = new FormData();
            formData.append('notification_id', notificationId);
            
            const response = await fetch('notification_api.php?action=mark_as_read', {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                loadNotifications(); // Reload to update UI
                updateUnreadCount(); // Update badge
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all notifications as read
    async function markAllAsRead() {
        try {
            const response = await fetch('notification_api.php?action=mark_all_as_read', {
                method: 'POST'
            });
            
            if (response.ok) {
                loadNotifications(); // Reload to update UI
                updateUnreadCount(); // Update badge
            }
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    // Update unread count badge
    async function updateUnreadCount() {
        try {
            const response = await fetch('notification_api.php?action=get_unread_count');
            const data = await response.json();
            
            const badge = document.getElementById('notificationBadge');
            const count = parseInt(data.unread_count) || 0;
            
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        } catch (error) {
            console.error('Error updating unread count:', error);
        }
    }

    // Initialize notification system when DOM is loaded
    updateUnreadCount();
    
    // Update unread count every 30 seconds
    setInterval(updateUnreadCount, 30000);

    // Make functions globally available
    window.toggleNotificationDropdown = toggleNotificationDropdown;
    window.markAsRead = markAsRead;
    window.markAllAsRead = markAllAsRead;
});
</script>
</body>
</html>
