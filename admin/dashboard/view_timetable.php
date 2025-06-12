<?php
/**
 * General Timetable Viewing System
 * Allows viewing of class/section timetables
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

// Get parameters
$class_id = $_GET['class_id'] ?? null;
$section_id = $_GET['section_id'] ?? null;
$view_mode = $_GET['view'] ?? 'grid'; // grid, list
$academic_year = $_GET['year'] ?? date('Y');
$term = $_GET['term'] ?? 'Term 1';

// Get available classes and sections for dropdown
$classes_sql = "SELECT id, name FROM classes ORDER BY name";
$classes = $conn->query($classes_sql)->fetchAll(PDO::FETCH_ASSOC);

$sections_sql = "SELECT id, name, class_id FROM sections ORDER BY class_id, name";
$all_sections = $conn->query($sections_sql)->fetchAll(PDO::FETCH_ASSOC);

// Get timetable data if class and section are selected
$timetable_data = [];
$timetable_info = null;

if ($class_id && $section_id) {
    // Get timetable info
    $info_sql = "
        SELECT 
            tt.*,
            c.name as class_name,
            s.name as section_name,
            u.full_name as created_by_name
        FROM timetables tt
        JOIN classes c ON tt.class_id = c.id
        JOIN sections s ON tt.section_id = s.id
        LEFT JOIN users u ON tt.created_by = u.id
        WHERE tt.class_id = ? AND tt.section_id = ? 
        AND tt.academic_year = ? AND tt.term = ?
        AND tt.status = 'published'
    ";
    $stmt = $conn->prepare($info_sql);
    $stmt->execute([$class_id, $section_id, $academic_year, $term]);
    $timetable_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($timetable_info) {
        // Get timetable periods
        $periods_sql = "
            SELECT 
                tp.*,
                sub.name as subject_name,
                sub.code as subject_code,
                u.full_name as teacher_name
            FROM timetable_periods tp
            LEFT JOIN subjects sub ON tp.subject_id = sub.id
            LEFT JOIN users u ON tp.teacher_id = u.id
            WHERE tp.timetable_id = ?
            ORDER BY 
                FIELD(tp.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
                tp.period_number
        ";
        $stmt = $conn->prepare($periods_sql);
        $stmt->execute([$timetable_info['id']]);
        $timetable_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Organize data for grid view
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$periods = range(1, 8);
$schedule_grid = [];

foreach ($days as $day) {
    $schedule_grid[$day] = [];
    foreach ($periods as $period) {
        $schedule_grid[$day][$period] = null;
    }
}

foreach ($timetable_data as $slot) {
    $schedule_grid[$slot['day_of_week']][$slot['period_number']] = $slot;
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Timetable</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        .main-content {
            margin-left: 250px;
            padding: 20px;
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .filters-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #495057;
        }
        
        .form-group select,
        .form-group input {
            padding: 0.75rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
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
        
        .view-controls {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .timetable-info {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .info-item h4 {
            margin: 0 0 0.5rem 0;
            color: #667eea;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-item p {
            margin: 0;
            font-size: 1.1rem;
            color: #495057;
        }
        
        .timetable-container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
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
            min-height: 70px;
            position: relative;
        }
        
        .slot-occupied {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            border-left: 4px solid #2196f3;
            padding: 8px;
            border-radius: 4px;
        }
        
        .slot-empty {
            background: #f8f9fa;
            color: #6c757d;
            font-style: italic;
            padding: 8px;
        }
        
        .subject-info {
            font-weight: 600;
            color: #1976d2;
            font-size: 0.9rem;
            margin-bottom: 4px;
        }
        
        .teacher-info {
            color: #424242;
            font-size: 0.8rem;
            margin-bottom: 2px;
        }
        
        .room-info {
            color: #666;
            font-size: 0.7rem;
        }
        
        .list-view {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .day-section {
            margin-bottom: 2rem;
        }
        
        .day-header {
            background: #667eea;
            color: white;
            padding: 1rem;
            border-radius: 8px 8px 0 0;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .periods-list {
            border: 1px solid #e9ecef;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        
        .period-item {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .period-item:last-child {
            border-bottom: none;
        }
        
        .period-number {
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 50%;
            font-weight: bold;
            min-width: 40px;
            text-align: center;
        }
        
        .period-details {
            flex: 1;
        }
        
        .no-timetable {
            text-align: center;
            padding: 3rem;
            color: #6c757d;
        }
        
        .no-timetable i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }
        
        @media print {
            .sidebar, .filters-section, .view-controls, .btn {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .page-header {
                background: #667eea !important;
                -webkit-print-color-adjust: exact;
            }
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .schedule-table {
                font-size: 0.8rem;
            }
            
            .schedule-table th,
            .schedule-table td {
                padding: 6px 4px;
            }
            
            .view-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt"></i> View Timetable</h1>
            <p>Browse and view class timetables</p>
        </div>
        
        <div class="filters-section">
            <h3><i class="fas fa-filter"></i> Filters</h3>
            <form method="GET" action="">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="academic_year">Academic Year</label>
                        <input type="text" id="academic_year" name="year" value="<?php echo htmlspecialchars($academic_year); ?>" placeholder="2024">
                    </div>
                    
                    <div class="form-group">
                        <label for="term">Term</label>
                        <select id="term" name="term">
                            <option value="Term 1" <?php echo $term === 'Term 1' ? 'selected' : ''; ?>>Term 1</option>
                            <option value="Term 2" <?php echo $term === 'Term 2' ? 'selected' : ''; ?>>Term 2</option>
                            <option value="Term 3" <?php echo $term === 'Term 3' ? 'selected' : ''; ?>>Term 3</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" onchange="updateSections()">
                            <option value="">Select Class</option>
                            <?php foreach ($classes as $class): ?>
                            <option value="<?php echo $class['id']; ?>" <?php echo $class_id == $class['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($class['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="section_id">Section</label>
                        <select id="section_id" name="section_id">
                            <option value="">Select Section</option>
                            <?php foreach ($all_sections as $section): ?>
                            <option value="<?php echo $section['id']; ?>" 
                                    data-class="<?php echo $section['class_id']; ?>"
                                    <?php echo $section_id == $section['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($section['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="view">View Mode</label>
                        <select id="view" name="view">
                            <option value="grid" <?php echo $view_mode === 'grid' ? 'selected' : ''; ?>>Grid View</option>
                            <option value="list" <?php echo $view_mode === 'list' ? 'selected' : ''; ?>>List View</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-top: 1rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> View Timetable
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($timetable_info): ?>
        <div class="timetable-info">
            <h3><i class="fas fa-info-circle"></i> Timetable Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <h4>Class & Section</h4>
                    <p><?php echo htmlspecialchars($timetable_info['class_name'] . ' - ' . $timetable_info['section_name']); ?></p>
                </div>
                <div class="info-item">
                    <h4>Academic Year</h4>
                    <p><?php echo htmlspecialchars($timetable_info['academic_year']); ?></p>
                </div>
                <div class="info-item">
                    <h4>Term</h4>
                    <p><?php echo htmlspecialchars($timetable_info['term']); ?></p>
                </div>
                <div class="info-item">
                    <h4>Created By</h4>
                    <p><?php echo htmlspecialchars($timetable_info['created_by_name'] ?? 'Unknown'); ?></p>
                </div>
                <div class="info-item">
                    <h4>Status</h4>
                    <p><span class="badge badge-success"><?php echo ucfirst($timetable_info['status']); ?></span></p>
                </div>
                <div class="info-item">
                    <h4>Last Updated</h4>
                    <p><?php echo date('Y-m-d H:i', strtotime($timetable_info['updated_at'])); ?></p>
                </div>
            </div>
        </div>
        
        <?php if ($view_mode === 'grid'): ?>
        <div class="timetable-container">
            <h3><i class="fas fa-table"></i> Weekly Schedule</h3>
            
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
                                    <div class="subject-info"><?php echo htmlspecialchars($slot['subject_name'] ?? 'Unknown Subject'); ?></div>
                                    <div class="teacher-info"><?php echo htmlspecialchars($slot['teacher_name'] ?? 'No Teacher'); ?></div>
                                    <?php if ($slot['room']): ?>
                                    <div class="room-info">Room: <?php echo htmlspecialchars($slot['room']); ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="slot-empty">Free Period</div>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php else: // List view ?>
        <div class="list-view">
            <h3><i class="fas fa-list"></i> Schedule List</h3>
            
            <?php foreach ($days as $day): ?>
            <div class="day-section">
                <div class="day-header"><?php echo $day; ?></div>
                <div class="periods-list">
                    <?php 
                    $day_has_periods = false;
                    foreach ($periods as $period): 
                        if ($schedule_grid[$day][$period]): 
                            $day_has_periods = true;
                            $slot = $schedule_grid[$day][$period];
                    ?>
                    <div class="period-item">
                        <div class="period-number"><?php echo $period; ?></div>
                        <div class="period-details">
                            <div class="subject-info"><?php echo htmlspecialchars($slot['subject_name'] ?? 'Unknown Subject'); ?></div>
                            <div class="teacher-info">Teacher: <?php echo htmlspecialchars($slot['teacher_name'] ?? 'No Teacher'); ?></div>
                            <?php if ($slot['room']): ?>
                            <div class="room-info">Room: <?php echo htmlspecialchars($slot['room']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php 
                        endif;
                    endforeach; 
                    
                    if (!$day_has_periods):
                    ?>
                    <div class="period-item">
                        <div style="flex: 1; text-align: center; color: #6c757d; font-style: italic;">
                            No scheduled periods for this day
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php elseif ($class_id && $section_id): ?>
        <div class="no-timetable">
            <i class="fas fa-calendar-times"></i>
            <h3>No Timetable Found</h3>
            <p>No published timetable found for the selected class, section, and term.</p>
        </div>
        
        <?php else: ?>
        <div class="no-timetable">
            <i class="fas fa-calendar-alt"></i>
            <h3>Select Class and Section</h3>
            <p>Please select a class and section to view the timetable.</p>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        function updateSections() {
            const classId = document.getElementById('class_id').value;
            const sectionSelect = document.getElementById('section_id');
            const sections = sectionSelect.querySelectorAll('option[data-class]');
            
            // Reset section selection
            sectionSelect.value = '';
            
            // Show/hide sections based on selected class
            sections.forEach(option => {
                if (!classId || option.dataset.class === classId) {
                    option.style.display = 'block';
                } else {
                    option.style.display = 'none';
                }
            });
        }
        
        // Initialize sections on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateSections();
            
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
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #e8f5e8;
            color: #2e7d32;
        }
    </style>
</body>
</html>
