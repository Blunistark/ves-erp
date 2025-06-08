<?php
require_once 'con.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT * FROM schedules ORDER BY id ASC");
$schedules = [];
while ($row = $result->fetch_assoc()) {
    $schedules[] = $row;
}
echo json_encode(['success' => true, 'schedules' => $schedules]); 