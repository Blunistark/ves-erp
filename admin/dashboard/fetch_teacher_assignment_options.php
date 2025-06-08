<?php
require_once 'con.php';
header('Content-Type: application/json');

// Fetch teachers
$teachers = [];
$res = $conn->query("SELECT id, CONCAT(first_name, ' ', last_name) as name FROM teachers WHERE status = 'active'");
while ($row = $res->fetch_assoc()) {
    $teachers[] = $row;
}
// Fetch classes
$classes = [];
$res = $conn->query("SELECT id, name FROM classes");
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}
// Fetch rooms
$rooms = [];
$res = $conn->query("SELECT id, name, type FROM rooms WHERE status = 'Active'");
while ($row = $res->fetch_assoc()) {
    $rooms[] = $row;
}
// Fetch sections
$sections = [];
$res = $conn->query("SELECT id, name FROM section_labels");
while ($row = $res->fetch_assoc()) {
    $sections[] = $row;
}
// Fetch subjects
$subjects = [];
$res = $conn->query("SELECT id, name FROM subjects");
while ($row = $res->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode([
    'success' => true,
    'teachers' => $teachers,
    'classes' => $classes,
    'rooms' => $rooms,
    'sections' => $sections,
    'subjects' => $subjects
]); 