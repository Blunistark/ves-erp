<?php
require_once "con.php";

// Prevent any PHP warnings/errors from being output in the CSV
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=sections_export_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');

// CSV header
fputcsv($output, [
    'Class',
    'Section',
    'Student Count',
    'Class Teacher',
    'Room Number',
    'Boys',
    'Girls',
    'Capacity',
    'Status',
    'Description'
]);

// Query all sections with stats, grouped by class
$sql = "SELECT s.*, c.name AS class_name, t.full_name AS teacher_name, r.name AS room_name,
    (SELECT COUNT(*) FROM students st WHERE st.section_id = s.id) AS student_count,
    (SELECT COUNT(*) FROM students st WHERE st.section_id = s.id AND st.gender = 'Male') AS boys,
    (SELECT COUNT(*) FROM students st WHERE st.section_id = s.id AND st.gender = 'Female') AS girls
FROM sections s
LEFT JOIN classes c ON s.class_id = c.id
LEFT JOIN teachers t ON s.class_teacher_id = t.id
LEFT JOIN rooms r ON s.room_id = r.id
ORDER BY c.id, s.name";

$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        fputcsv($output, [
            $row['class_name'] ?? '',
            strtoupper($row['name'] ?? ''),
            $row['student_count'] ?? 0,
            $row['teacher_name'] ?? '',
            $row['room_name'] ?? '',
            $row['boys'] ?? 0,
            $row['girls'] ?? 0,
            $row['capacity'] ?? '',
            ucfirst($row['status'] ?? ''),
            $row['description'] ?? ''
        ]);
    }
}
fclose($output);
exit; 