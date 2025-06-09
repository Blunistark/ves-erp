<?php
require_once 'admin/dashboard/con.php';

echo "Database Tables:\n";
echo "================\n";

$result = $conn->query('SHOW TABLES');
while($row = $result->fetch_row()) {
    echo $row[0] . "\n";
}

echo "\nChecking for exam_sessions table:\n";
$check = $conn->query("SHOW TABLES LIKE 'exam_sessions'");
echo "exam_sessions exists: " . ($check->num_rows > 0 ? "YES" : "NO") . "\n";

echo "\nChecking for exam_subjects table:\n";
$check = $conn->query("SHOW TABLES LIKE 'exam_subjects'");
echo "exam_subjects exists: " . ($check->num_rows > 0 ? "YES" : "NO") . "\n";

echo "\nChecking assessments table structure:\n";
$result = $conn->query("DESCRIBE assessments");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

$conn->close();
?>
