<?php
include 'includes/config.php';

echo "Checking exam_sessions table structure:\n";
$result = $conn->query('DESCRIBE exam_sessions');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

echo "\nChecking exam_subjects table structure:\n";
$result = $conn->query('DESCRIBE exam_subjects');
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}

// Check if term field exists in exam_sessions
$term_check = $conn->query("SHOW COLUMNS FROM exam_sessions LIKE 'term'");
if ($term_check->num_rows > 0) {
    echo "\nWARNING: 'term' field still exists in exam_sessions table!\n";
} else {
    echo "\n✓ 'term' field successfully removed from exam_sessions table\n";
}

// Check if venue field exists in exam_subjects
$venue_check = $conn->query("SHOW COLUMNS FROM exam_subjects LIKE 'venue'");
if ($venue_check->num_rows > 0) {
    echo "WARNING: 'venue' field still exists in exam_subjects table!\n";
} else {
    echo "✓ 'venue' field successfully removed from exam_subjects table\n";
}

$conn->close();
?>
