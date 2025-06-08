<?php
require_once 'con.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$sectionId = intval($_POST['id'] ?? 0);

if (!$action || !$sectionId) {
    echo json_encode(['success' => false, 'message' => 'Missing action or section id.']);
    exit;
}

file_put_contents(__DIR__ . '/section_debug.log', date('c') . "\n" . print_r($_POST, true) . "\n", FILE_APPEND);

if ($action === 'update') {
    $classId = intval($_POST['class_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $capacity = intval($_POST['capacity'] ?? 0);
    $teacher = trim($_POST['teacher'] ?? '');
    $classroom = trim($_POST['classroom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = trim($_POST['status'] ?? 'active');

    if (!$classId || !$name || !$capacity) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
        exit;
    }
    if (!in_array($status, ['active', 'inactive'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit;
    }
    $stmt = $conn->prepare('UPDATE sections SET class_id=?, name=?, capacity=?, teacher=?, classroom=?, description=?, status=? WHERE id=?');
    if (!$stmt) {
        file_put_contents(__DIR__ . '/section_debug.log', date('c') . " Prepare failed: " . $conn->error . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('isissssi', $classId, $name, $capacity, $teacher, $classroom, $description, $status, $sectionId);
    $ok = $stmt->execute();
    if (!$ok) {
        file_put_contents(__DIR__ . '/section_debug.log', date('c') . " Execute failed: " . $stmt->error . "\n", FILE_APPEND);
    }
    $stmt->close();
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Section updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $conn->error]);
    }
    exit;
}

if ($action === 'delete') {
    $stmt = $conn->prepare('DELETE FROM sections WHERE id=?');
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('i', $sectionId);
    $ok = $stmt->execute();
    $stmt->close();
    if ($ok) {
        echo json_encode(['success' => true, 'message' => 'Section deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $conn->error]);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']); 