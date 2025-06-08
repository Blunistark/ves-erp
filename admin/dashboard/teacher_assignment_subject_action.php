<?php
require_once 'con.php';
header('Content-Type: application/json');

if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $assignment_id = intval($_POST['assignment_id'] ?? 0);
    if (!$assignment_id) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }
    $stmt = $conn->prepare('DELETE FROM subject_teachers WHERE id = ?');
    $stmt->bind_param('i', $assignment_id);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete assignment']);
    }
    exit;
}

$class_id = intval($_POST['class_id'] ?? 0);
$section_id = intval($_POST['section_id'] ?? 0);
$subject_id = intval($_POST['subject_id'] ?? 0);
$teacher_id = intval($_POST['teacher_id'] ?? 0);
$schedule_id = intval($_POST['schedule_id'] ?? 0);

if (!$class_id || !$section_id || !$subject_id || !$teacher_id || !$schedule_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Insert or update subject teacher assignment
$stmt = $conn->prepare("INSERT INTO subject_teachers (class_id, section_id, subject_id, teacher_id, schedule_id) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE teacher_id = VALUES(teacher_id), schedule_id = VALUES(schedule_id)");
$stmt->bind_param('iiiii', $class_id, $section_id, $subject_id, $teacher_id, $schedule_id);
$ok = $stmt->execute();
if ($ok) {
    echo json_encode(['success' => true, 'message' => 'Subject teacher assigned successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to assign subject teacher', 'error' => $stmt->error]);
}
$stmt->close(); 