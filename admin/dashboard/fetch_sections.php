<?php
require_once 'con.php';
header('Content-Type: application/json');
$class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$sections = [];
if ($class_id) {
    $res = $conn->query("SELECT id, name FROM sections WHERE class_id = $class_id");
    while ($row = $res->fetch_assoc()) {
        $sections[] = $row;
    }
}
echo json_encode(['sections' => $sections]); 