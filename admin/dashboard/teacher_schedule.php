<?php
/**
 * Individual Teacher Schedule View
 * Displays detailed schedule for a specific teacher
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication
if (!isLoggedIn()) {
    header("Location: ../../index.php");
    exit;
}

require_once 'con.php';

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Get teacher ID from URL or session
$teacher_id = $_GET['teacher_id'] ?? null;

// If no teacher ID specified and user is a teacher, show their own schedule
if (!$teacher_id && $user_role === 'teacher') {
    $teacher_id = $user_id;
}

// Verify permission to view this teacher's schedule
if (!hasRole(['admin', 'headmaster']) && $teacher_id != $user_id) {
    die("You don't have permission to view this teacher's schedule.");
}

if (!$teacher_id) {
    die("Teacher ID is required.");
}

// Get teacher information
$teacher_sql = "
    SELECT u.id, u.full_name, u.email, t.employee_number, u.status
    FROM users u 
    JOIN teachers t ON u.id = t.user_id 
    WHERE u.id = ?
";
$stmt = $conn->prepare($teacher_sql);
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$teacher) {
    die("Teacher not found.");
}

// Get teacher's current schedule
$schedule_sql = "
    SELECT 
        tp.day_of_week,
        tp.period_number,
        s.name as subject_name,
        s.code as subject_code,
        c.name as class_name,
        sec.name as section_name,
        tp.room,
        tp.notes,
        tt.academic_year,
        tt.term,
        tt.status as timetable_status
    FROM timetable_periods tp
    JOIN timetables tt ON tp.timetable_id = tt.id
    JOIN subjects s ON tp.subject_id = s.id
    JOIN classes c ON tt.class_id = c.id
    JOIN sections sec ON tt.section_id = sec.id
    WHERE tp.teacher_id = ? AND tt.status = 'published'
    ORDER BY 
        FIELD(tp.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
        tp.period_number
";
$stmt = $conn->prepare($schedule_sql);
$stmt->execute([$teacher_id]);
$schedule = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get class teacher assignments
$class_teacher_sql = "
    SELECT 
        c.name as class_name,
        s.name as section_name,
        'Class Teacher' as role
    FROM sections s
    JOIN classes c ON s.class_id = c.id
    WHERE s.class_teacher_id = ?
";
$stmt = $conn->prepare($class_teacher_sql);
$stmt->execute([$teacher_id]);
$class_assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get teacher subjects
$subjects_sql = "
    SELECT s.name, s.code
    FROM teacher_subjects ts
    JOIN subjects s ON ts.subject_id = s.id
    WHERE ts.teacher_id = ?
";
$stmt = $conn->prepare($subjects_sql);
$stmt->execute([$teacher_id]);
$teacher_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize schedule by day and period
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$periods = range(1, 8); // Assuming 8 periods per day
$schedule_grid = [];

foreach ($days as $day) {
    $schedule_grid[$day] = [];
    foreach ($periods as $period) {
        $schedule_grid[$day][$period] = null;
    }
}

foreach ($schedule as $slot) {
    $schedule_grid[$slot['day_of_week']][$slot['period_number']] = $slot;
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Schedule - <?php echo htmlspecialchars($teacher['full_name']); ?></title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            overflow-y: auto;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .teacher-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .teacher-info {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 2rem;
            margin-top: 1rem;
        }
        
        .info-item h4 {
            margin: 0 0 0.5rem 0;
            opacity: 0.9;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-item p {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .schedule-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .schedule-table th,
        .schedule-table td {
            border: 1px solid #e9ecef;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
        }
        
        .schedule-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .schedule-table th:first-child {
            background: #667eea;
            color: white;
            width: 80px;
        }
        
        .schedule-slot {
            min-height: 60px;
            position: relative;
        }
        
        .slot-occupied {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196f3;
        }
        
        .slot-empty {
            background: #f8f9fa;
            color: #6c757d;
            font-style: italic;
        }
        
        .subject-info {
            font-weight: 600;
            color: #1976d2;
            font-size: 0.9rem;
        }
        
        .class-info {
            color: #424242;
            font-size: 0.8rem;
            margin-top: 2px;
        }
        
        .room-info {
            color: #666;
            font-size: 0.7rem;
            margin-top: 2px;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .summary-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .summary-card h3 {
            margin: 0 0 1rem 0;
            color: #495057;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .summary-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .summary-list li:last-child {
            border-bottom: none;
        }
        
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-primary {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-success {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .workload-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .stat-item {
            text-align: center;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
            display: block;
        }
        
        .stat-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .print-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .print-btn:hover {
            background: #218838;
        }
        
        @media print {
            .sidebar, .print-btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .teacher-header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .teacher-info {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .schedule-table {
                font-size: 0.8rem;
            }
            
            .schedule-table th,
            .schedule-table td {
                padding: 8px 4px;
            }
            
            .workload-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="teacher-header">
            <h1><i class="fas fa-user-graduate"></i> <?php echo htmlspecialchars($teacher['full_name']); ?></h1>
            <div class="teacher-info">
                <div class="info-item">
                    <h4>Employee Number</h4>
                    <p><?php echo htmlspecialchars($teacher['employee_number']); ?></p>
                </div>
                <div class="info-item">
                    <h4>Email</h4>
                    <p><?php echo htmlspecialchars($teacher['email']); ?></p>
                </div>
                <div class="info-item">
                    <h4>Status</h4>
                    <p><span class="badge badge-<?php echo $teacher['status'] === 'active' ? 'success' : 'secondary'; ?>">
                        <?php echo ucfirst($teacher['status']); ?>
                    </span></p>
                </div>
            </div>
        </div>
        
        <button class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Schedule
        </button>
        
        <div class="summary-grid">
            <?php if (!empty($class_assignments)): ?>
            <div class="summary-card">
                <h3><i class="fas fa-chalkboard-teacher"></i> Class Teacher Assignments</h3>
                <ul class="summary-list">
                    <?php foreach ($class_assignments as $assignment): ?>
                    <li>
                        <span><?php echo htmlspecialchars($assignment['class_name'] . ' - ' . $assignment['section_name']); ?></span>
                        <span class="badge badge-primary"><?php echo htmlspecialchars($assignment['role']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <div class="summary-card">
                <h3><i class="fas fa-book"></i> Qualified Subjects</h3>
                <ul class="summary-list">
                    <?php foreach ($teacher_subjects as $subject): ?>
                    <li>
                        <span><?php echo htmlspecialchars($subject['name']); ?></span>
                        <span class="badge badge-primary"><?php echo htmlspecialchars($subject['code']); ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="summary-card">
                <h3><i class="fas fa-chart-bar"></i> Workload Statistics</h3>
                <div class="workload-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count($schedule); ?></span>
                        <span class="stat-label">Total Periods</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_unique(array_column($schedule, 'class_name'))); ?></span>
                        <span class="stat-label">Classes</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_unique(array_column($schedule, 'subject_name'))); ?></span>
                        <span class="stat-label">Subjects</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo count(array_unique(array_column($schedule, 'day_of_week'))); ?></span>
                        <span class="stat-label">Days/Week</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="schedule-container">
            <h2><i class="fas fa-calendar-alt"></i> Weekly Schedule</h2>
            
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <?php foreach ($days as $day): ?>
                        <th><?php echo $day; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($periods as $period): ?>
                    <tr>
                        <th>Period <?php echo $period; ?></th>
                        <?php foreach ($days as $day): ?>
                        <td class="schedule-slot">
                            <?php if ($schedule_grid[$day][$period]): ?>
                                <?php $slot = $schedule_grid[$day][$period]; ?>
                                <div class="slot-occupied">
                                    <div class="subject-info"><?php echo htmlspecialchars($slot['subject_name']); ?></div>
                                    <div class="class-info"><?php echo htmlspecialchars($slot['class_name'] . ' - ' . $slot['section_name']); ?></div>
                                    <?php if ($slot['room']): ?>
                                    <div class="room-info">Room: <?php echo htmlspecialchars($slot['room']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="slot-empty">Free</div>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (hasRole(['admin', 'headmaster'])): ?>
        <div class="schedule-container">
            <h3><i class="fas fa-cogs"></i> Actions</h3>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button onclick="location.href='teacher_management_unified.php'" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Teacher Management
                </button>
                <button onclick="exportSchedule('pdf')" class="btn btn-secondary">
                    <i class="fas fa-file-pdf"></i> Export as PDF
                </button>
                <button onclick="exportSchedule('excel')" class="btn btn-secondary">
                    <i class="fas fa-file-excel"></i> Export as Excel
                </button>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function exportSchedule(format) {
            const teacherId = <?php echo $teacher_id; ?>;
            const url = `timetable_reports.php?action=teacher_schedule&teacher_id=${teacherId}&format=${format}`;
            window.open(url, '_blank');
        }
        
        // Add some interactivity for better UX
        document.addEventListener('DOMContentLoaded', function() {
            // Highlight current time slot if it's a school day
            const now = new Date();
            const currentDay = now.toLocaleDateString('en-US', { weekday: 'long' });
            const currentHour = now.getHours();
            
            // Assuming school hours are 8 AM to 4 PM (periods 1-8)
            if (currentHour >= 8 && currentHour < 16) {
                const currentPeriod = currentHour - 7; // Rough calculation
                const dayIndex = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'].indexOf(currentDay);
                
                if (dayIndex !== -1 && currentPeriod >= 1 && currentPeriod <= 8) {
                    const rows = document.querySelectorAll('.schedule-table tbody tr');
                    if (rows[currentPeriod - 1]) {
                        const cells = rows[currentPeriod - 1].querySelectorAll('td');
                        if (cells[dayIndex]) {
                            cells[dayIndex].style.boxShadow = '0 0 0 3px #ffc107';
                            cells[dayIndex].style.backgroundColor = '#fff3cd';
                        }
                    }
                }
            }
        });
    </script>
    
    <style>
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</body>
</html>
