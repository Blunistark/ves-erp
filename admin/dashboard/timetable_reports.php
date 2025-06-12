<?php
/**
 * Timetable Reports Generator
 * Generates various reports for timetable management
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role
if (!isLoggedIn() || !hasRole(['admin', 'headmaster'])) {
    header("Location: ../../index.php");
    exit;
}

require_once 'con.php';

$report_type = $_GET['type'] ?? 'teacher_workload';
$format = $_GET['format'] ?? 'pdf';

// Set appropriate headers based on format
switch ($format) {
    case 'pdf':
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="timetable_report_' . $report_type . '_' . date('Y-m-d') . '.pdf"');
        break;
    case 'excel':
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="timetable_report_' . $report_type . '_' . date('Y-m-d') . '.xlsx"');
        break;
    case 'csv':
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="timetable_report_' . $report_type . '_' . date('Y-m-d') . '.csv"');
        break;
}

try {
    switch ($report_type) {
        case 'teacher_workload':
            generateTeacherWorkloadReport($format);
            break;
        case 'class_schedules':
            generateClassSchedulesReport($format);
            break;
        case 'conflict_summary':
            generateConflictSummaryReport($format);
            break;
        case 'utilization':
            generateUtilizationReport($format);
            break;
        default:
            throw new Exception('Invalid report type');
    }
} catch (Exception $e) {
    header('Content-Type: text/html');
    echo '<h1>Error</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
}

function generateTeacherWorkloadReport($format) {
    global $conn;
    
    $sql = "
        SELECT 
            u.full_name,
            t.employee_number,
            COUNT(DISTINCT s.id) as class_teacher_assignments,
            COUNT(DISTINCT ts.subject_id) as subject_assignments,
            COUNT(tp.id) as timetable_periods,
            GROUP_CONCAT(DISTINCT sub.name ORDER BY sub.name SEPARATOR ', ') as subjects_taught
        FROM users u
        JOIN teachers t ON u.id = t.user_id
        LEFT JOIN sections s ON u.id = s.class_teacher_user_id
        LEFT JOIN teacher_subjects ts ON u.id = ts.teacher_user_id
        LEFT JOIN subjects sub ON ts.subject_id = sub.id
        LEFT JOIN timetable_periods tp ON u.id = tp.teacher_id
        LEFT JOIN timetables tt ON tp.timetable_id = tt.id AND tt.status = 'published'
        WHERE u.role = 'teacher' AND u.status = 'active'
        GROUP BY u.id, u.full_name, t.employee_number
        ORDER BY u.full_name
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        outputCSV($data, [
            'Teacher Name' => 'full_name',
            'Employee Number' => 'employee_number',
            'Class Teacher Assignments' => 'class_teacher_assignments',
            'Subject Assignments' => 'subject_assignments',
            'Timetable Periods' => 'timetable_periods',
            'Subjects Taught' => 'subjects_taught'
        ]);
    } else {
        // For PDF/Excel, you would integrate with libraries like TCPDF or PhpSpreadsheet
        outputHTMLReport('Teacher Workload Report', $data, [
            'Teacher Name' => 'full_name',
            'Employee Number' => 'employee_number',
            'Class Teacher Assignments' => 'class_teacher_assignments',
            'Subject Assignments' => 'subject_assignments',
            'Timetable Periods' => 'timetable_periods',
            'Subjects Taught' => 'subjects_taught'
        ]);
    }
}

function generateClassSchedulesReport($format) {
    global $conn;
    
    $sql = "
        SELECT 
            c.name as class_name,
            s.name as section_name,
            tp.day_of_week,
            tp.period_number,
            tp.start_time,
            tp.end_time,
            sub.name as subject,
            u.full_name as teacher_name
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id
        JOIN classes c ON tt.class_id = c.id
        JOIN sections s ON tt.section_id = s.id
        LEFT JOIN subjects sub ON tp.subject_id = sub.id
        LEFT JOIN users u ON tp.teacher_id = u.id
        WHERE tt.status = 'published'
        ORDER BY c.name, s.name, 
                 FIELD(tp.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
                 tp.period_number
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        outputCSV($data, [
            'Class' => 'class_name',
            'Section' => 'section_name',
            'Day' => 'day_of_week',
            'Period' => 'period_number',
            'Start Time' => 'start_time',
            'End Time' => 'end_time',
            'Subject' => 'subject',
            'Teacher' => 'teacher_name'
        ]);
    } else {
        outputHTMLReport('Class Schedules Report', $data, [
            'Class' => 'class_name',
            'Section' => 'section_name',
            'Day' => 'day_of_week',
            'Period' => 'period_number',
            'Start Time' => 'start_time',
            'End Time' => 'end_time',
            'Subject' => 'subject',
            'Teacher' => 'teacher_name'
        ]);
    }
}

function generateConflictSummaryReport($format) {
    global $conn;
    
    $sql = "
        SELECT 
            'Teacher Conflict' as conflict_type,
            u.full_name as teacher_name,
            tp1.day_of_week,
            tp1.period_number,
            tp1.start_time,
            tp1.end_time,
            COUNT(*) as conflict_count
        FROM timetable_periods tp1
        JOIN timetable_periods tp2 ON tp1.teacher_id = tp2.teacher_id 
            AND tp1.day_of_week = tp2.day_of_week
            AND tp1.period_number = tp2.period_number
            AND tp1.id != tp2.id
        JOIN timetables tt1 ON tp1.timetable_id = tt1.id
        JOIN timetables tt2 ON tp2.timetable_id = tt2.id
        JOIN users u ON tp1.teacher_id = u.id
        WHERE tt1.status = 'published' AND tt2.status = 'published'
        GROUP BY u.id, tp1.day_of_week, tp1.period_number, tp1.start_time
        ORDER BY u.full_name, tp1.day_of_week, tp1.period_number
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        outputCSV($data, [
            'Conflict Type' => 'conflict_type',
            'Teacher' => 'teacher_name',
            'Day' => 'day_of_week',
            'Period' => 'period_number',
            'Start Time' => 'start_time',
            'End Time' => 'end_time',
            'Conflict Count' => 'conflict_count'
        ]);
    } else {
        outputHTMLReport('Conflict Summary Report', $data, [
            'Conflict Type' => 'conflict_type',
            'Teacher' => 'teacher_name',
            'Day' => 'day_of_week',
            'Period' => 'period_number',
            'Start Time' => 'start_time',
            'End Time' => 'end_time',
            'Conflict Count' => 'conflict_count'
        ]);
    }
}

function generateUtilizationReport($format) {
    global $conn;
    
    $sql = "
        SELECT 
            tp.day_of_week,
            tp.period_number,
            tp.start_time,
            tp.end_time,
            COUNT(tp.id) as classes_scheduled,
            (COUNT(tp.id) / (SELECT COUNT(*) FROM classes c JOIN sections s ON c.id = s.class_id)) * 100 as utilization_percentage
        FROM timetable_periods tp
        JOIN timetables tt ON tp.timetable_id = tt.id
        WHERE tt.status = 'published'
        GROUP BY tp.day_of_week, tp.period_number, tp.start_time, tp.end_time
        ORDER BY FIELD(tp.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'),
                 tp.period_number
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($format === 'csv') {
        outputCSV($data, [
            'Day' => 'day_of_week',
            'Period' => 'period_number',
            'Start Time' => 'start_time',
            'End Time' => 'end_time',
            'Classes Scheduled' => 'classes_scheduled',
            'Utilization %' => 'utilization_percentage'
        ]);
    } else {
        outputHTMLReport('Utilization Report', $data, [
            'Day' => 'day_of_week',
            'Period' => 'period_number',
            'Start Time' => 'start_time',
            'End Time' => 'end_time',
            'Classes Scheduled' => 'classes_scheduled',
            'Utilization %' => 'utilization_percentage'
        ]);
    }
}

function outputCSV($data, $columns) {
    $output = fopen('php://output', 'w');
    
    // Write header
    fputcsv($output, array_keys($columns));
    
    // Write data
    foreach ($data as $row) {
        $csvRow = [];
        foreach ($columns as $column) {
            $csvRow[] = $row[$column] ?? '';
        }
        fputcsv($output, $csvRow);
    }
    
    fclose($output);
}

function outputHTMLReport($title, $data, $columns) {
    echo "<!DOCTYPE html>
<html>
<head>
    <title>$title</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { margin-bottom: 20px; }
        .print-btn { margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>$title</h1>
        <p>Generated on: " . date('Y-m-d H:i:s') . "</p>
        <button class='print-btn' onclick='window.print()'>Print Report</button>
    </div>
    
    <table>
        <thead>
            <tr>";
    
    foreach (array_keys($columns) as $header) {
        echo "<th>" . htmlspecialchars($header) . "</th>";
    }
    
    echo "</tr>
        </thead>
        <tbody>";
    
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($columns as $column) {
            echo "<td>" . htmlspecialchars($row[$column] ?? '') . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</tbody>
    </table>
</body>
</html>";
}
?>
