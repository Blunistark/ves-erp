<?php
require_once 'con.php';
header('Content-Type: application/json');

// Debug: log incoming POST data
file_put_contents('delete_debug.log', "POST: " . json_encode($_POST) . "\n", FILE_APPEND);

$action = $_POST['action'] ?? '';
$classId = intval($_POST['class_id'] ?? 0);

if ($action === 'delete' && $classId) {
    // Delete sections
    $stmt = $conn->prepare('DELETE FROM sections WHERE class_id = ?');
    $stmt->bind_param('i', $classId);
    $ok1 = $stmt->execute();
    file_put_contents('delete_debug.log', "Delete sections: " . ($ok1 ? 'OK' : $stmt->error) . "\n", FILE_APPEND);
    $stmt->close();
    // Delete class_subjects
    $stmt = $conn->prepare('DELETE FROM class_subjects WHERE class_id = ?');
    $stmt->bind_param('i', $classId);
    $ok2 = $stmt->execute();
    file_put_contents('delete_debug.log', "Delete class_subjects: " . ($ok2 ? 'OK' : $stmt->error) . "\n", FILE_APPEND);
    $stmt->close();
    // Delete class
    $stmt = $conn->prepare('DELETE FROM classes WHERE id = ?');
    $stmt->bind_param('i', $classId);
    $ok3 = $stmt->execute();
    file_put_contents('delete_debug.log', "Delete class: " . ($ok3 ? 'OK' : $stmt->error) . "\n", FILE_APPEND);
    $stmt->close();
    if ($ok3) {
        echo json_encode(['success' => true, 'message' => 'Class deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete class.']);
    }
    exit;
}

if ($action === 'update' && $classId) {
    $name = $_POST['name'] ?? '';
    $year = $_POST['academic_year'] ?? '';
    $dept = $_POST['department'] ?? '';
    $desc = $_POST['description'] ?? '';
    $teacher_id = isset($_POST['teacher_id']) ? intval($_POST['teacher_id']) : 0;
    $subject = $_POST['subject'] ?? '';
    $stmt = $conn->prepare('UPDATE classes SET name=?, academic_year=?, department=?, description=?, teacher_id=?, subject=? WHERE id=?');
    $stmt->bind_param('ssssisi', $name, $year, $dept, $desc, $teacher_id, $subject, $classId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Class updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update class.']);
    }
    exit;
}

file_put_contents('delete_debug.log', "Invalid request\n", FILE_APPEND);
echo json_encode(['success' => false, 'message' => 'Invalid request.']); 
