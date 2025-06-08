<?php
require_once 'con.php';
header('Content-Type: application/json');

$sql = "SELECT 
    t.id as assignment_id,
    t.teacher_id, CONCAT(te.first_name, ' ', te.last_name) as teacher_name,
    t.class_id, c.name as class_name,
    t.room_id, r.name as room_name,
    sch.day_of_week, sch.start_time, sch.end_time, t.subject_id, sub.name as subject_name, t.status,
    t.created_at, t.updated_at,
    s.name as section_name, s.id as section_id
FROM timetable t
JOIN teachers te ON t.teacher_id = te.id
JOIN classes c ON t.class_id = c.id
JOIN rooms r ON t.room_id = r.id
JOIN sections s ON t.section_id = s.id
JOIN subjects sub ON t.subject_id = sub.id
JOIN schedules sch ON t.schedule_id = sch.id
ORDER BY t.created_at DESC";

$result = $conn->query($sql);
$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

echo json_encode([
    'success' => true,
    'assignments' => $assignments
]); 