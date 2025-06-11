<?php
require_once 'con.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Add new subject
if ($action === 'add') {
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    // Validate input
    if (empty($name) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Subject name and code are required.']);
        exit;
    }
    
    // Check if code already exists
    $stmt = $conn->prepare('SELECT id FROM subjects WHERE code = ?');
    $stmt->bind_param('s', $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Subject code already exists. Please use a unique code.']);
        exit;
    }
    
    // Insert the new subject
    $stmt = $conn->prepare('INSERT INTO subjects (name, code) VALUES (?, ?)');
    $stmt->bind_param('ss', $name, $code);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Subject added successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add subject: ' . $conn->error]);
    }
    
    $stmt->close();
    exit;
}

// Update existing subject
if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $code = trim($_POST['code'] ?? '');
    
    // Validate input
    if (!$id || empty($name) || empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Subject ID, name, and code are required.']);
        exit;
    }
    
    // Check if code already exists for other subjects
    $stmt = $conn->prepare('SELECT id FROM subjects WHERE code = ? AND id != ?');
    $stmt->bind_param('si', $code, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Subject code already exists. Please use a unique code.']);
        exit;
    }
    
    // Update the subject
    $stmt = $conn->prepare('UPDATE subjects SET name = ?, code = ? WHERE id = ?');
    $stmt->bind_param('ssi', $name, $code, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Subject updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update subject: ' . $conn->error]);
    }
    
    $stmt->close();
    exit;
}

// Delete subject
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    
    // Validate input
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Subject ID is required.']);
        exit;
    }
    
    // Check if subject is assigned to classes
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM class_subjects WHERE subject_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'This subject is assigned to one or more classes. Please remove these assignments before deleting.'
        ]);
        exit;
    }
    
    // Check if subject is assigned to teachers
    $stmt = $conn->prepare('SELECT COUNT(*) as count FROM teacher_subjects WHERE subject_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'This subject is assigned to one or more teachers. Please remove these assignments before deleting.'
        ]);
        exit;
    }
    
    // Delete the subject
    $stmt = $conn->prepare('DELETE FROM subjects WHERE id = ?');
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Subject deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete subject: ' . $conn->error]);
    }
    
    $stmt->close();
    exit;
}

// Invalid action
echo json_encode(['success' => false, 'message' => 'Invalid action.']); 