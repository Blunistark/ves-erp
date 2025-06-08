<?php
require_once 'con.php';
header('Content-Type: application/json');
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$sql = "SELECT st.id, st.teacher_id, st.class_id, st.section_id, st.subject_id, st.schedule_id, c.name AS class_name, sl.name AS section_name, CONCAT(t.first_name, ' ', t.last_name) AS teacher_name, s.name AS subject_name, 
    CONCAT(UPPER(LEFT(sch.day_of_week,1)), SUBSTRING(sch.day_of_week,2), ' (', LEFT(sch.start_time,5), ' - ', LEFT(sch.end_time,5), ')') AS schedule_label
FROM subject_teachers st
JOIN classes c ON st.class_id = c.id
JOIN section_labels sl ON st.section_id = sl.id
JOIN teachers t ON st.teacher_id = t.id
JOIN subjects s ON st.subject_id = s.id
LEFT JOIN schedules sch ON st.schedule_id = sch.id";
if ($class_id) {
    $sql .= " WHERE st.class_id = $class_id";
}
$sql .= " ORDER BY c.id, sl.name, s.id";
$res = $conn->query($sql);
$assignments = [];
while ($row = $res->fetch_assoc()) {
    $assignments[] = $row;
}
echo json_encode(['assignments' => $assignments]); 