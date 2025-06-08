<?php
require_once 'con.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$class_id = isset($input['class_id']) ? intval($input['class_id']) : 0;

if (!$class_id) {
    echo json_encode(['success' => false, 'sections' => []]);
    exit;
}

$res = mysqli_query($conn, "SELECT id, name FROM sections WHERE class_id = $class_id ORDER BY name");
$sections = [];
while ($row = mysqli_fetch_assoc($res)) {
    $sections[] = [
        'id' => $row['id'],
        'name' => $row['name']
    ];
}
echo json_encode(['success' => true, 'sections' => $sections]); 