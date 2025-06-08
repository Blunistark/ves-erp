<?php
require_once 'con.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Get the action to perform
$action = $_POST['action'] ?? '';

// Process based on the action
switch ($action) {
    case 'add':
        addAcademicYear();
        break;
    case 'edit':
        editAcademicYear();
        break;
    case 'delete':
        deleteAcademicYear();
        break;
    default:
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
        break;
}

/**
 * Add a new academic year
 */
function addAcademicYear() {
    global $conn;
    
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    
    // Validate input
    $errors = [];
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($startDate)) {
        $errors['start_date'] = 'Start date is required';
    } elseif (!validateDate($startDate)) {
        $errors['start_date'] = 'Invalid start date format';
    }
    
    if (empty($endDate)) {
        $errors['end_date'] = 'End date is required';
    } elseif (!validateDate($endDate)) {
        $errors['end_date'] = 'Invalid end date format';
    }
    
    // Check if start date is before end date
    if (empty($errors['start_date']) && empty($errors['end_date']) && strtotime($startDate) >= strtotime($endDate)) {
        $errors['end_date'] = 'End date must be after start date';
    }
    
    // Check if name is already in use
    if (empty($errors['name'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM academic_years WHERE name = ?');
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['name'] = 'An academic year with this name already exists';
        }
    }
    
    // Check for overlapping dates
    if (empty($errors['start_date']) && empty($errors['end_date'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM academic_years WHERE 
                               (start_date <= ? AND end_date >= ?) OR 
                               (start_date <= ? AND end_date >= ?) OR
                               (start_date >= ? AND end_date <= ?)');
        $stmt->bind_param('ssssss', $endDate, $startDate, $endDate, $startDate, $startDate, $endDate);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['date_overlap'] = 'The specified date range overlaps with an existing academic year';
        }
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Insert the new academic year
    $stmt = $conn->prepare('INSERT INTO academic_years (name, start_date, end_date) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $name, $startDate, $endDate);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        $newId = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Academic year added successfully', 'id' => $newId]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to add academic year: ' . $conn->error]);
    }
}

/**
 * Edit an existing academic year
 */
function editAcademicYear() {
    global $conn;
    
    // Get and sanitize input
    $id = intval($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    
    // Validate input
    $errors = [];
    
    if ($id <= 0) {
        $errors['id'] = 'Invalid academic year ID';
    }
    
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($startDate)) {
        $errors['start_date'] = 'Start date is required';
    } elseif (!validateDate($startDate)) {
        $errors['start_date'] = 'Invalid start date format';
    }
    
    if (empty($endDate)) {
        $errors['end_date'] = 'End date is required';
    } elseif (!validateDate($endDate)) {
        $errors['end_date'] = 'Invalid end date format';
    }
    
    // Check if start date is before end date
    if (empty($errors['start_date']) && empty($errors['end_date']) && strtotime($startDate) >= strtotime($endDate)) {
        $errors['end_date'] = 'End date must be after start date';
    }
    
    // Check if name is already in use by another academic year
    if (empty($errors['name'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM academic_years WHERE name = ? AND id != ?');
        $stmt->bind_param('si', $name, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['name'] = 'An academic year with this name already exists';
        }
    }
    
    // Check for overlapping dates
    if (empty($errors['start_date']) && empty($errors['end_date'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM academic_years WHERE 
                               ((start_date <= ? AND end_date >= ?) OR 
                               (start_date <= ? AND end_date >= ?) OR
                               (start_date >= ? AND end_date <= ?))
                               AND id != ?');
        $stmt->bind_param('ssssssi', $endDate, $startDate, $endDate, $startDate, $startDate, $endDate, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['date_overlap'] = 'The specified date range overlaps with an existing academic year';
        }
    }
    
    // Check if academic year exists
    if (empty($errors['id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM academic_years WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            $errors['id'] = 'Academic year not found';
        }
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Update the academic year
    $stmt = $conn->prepare('UPDATE academic_years SET name = ?, start_date = ?, end_date = ? WHERE id = ?');
    $stmt->bind_param('sssi', $name, $startDate, $endDate, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Academic year updated successfully']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to update academic year: ' . $conn->error]);
    }
}

/**
 * Delete an academic year
 */
function deleteAcademicYear() {
    global $conn;
    
    // Get and sanitize input
    $id = intval($_POST['id'] ?? 0);
    
    // Validate input
    $errors = [];
    
    if ($id <= 0) {
        $errors['id'] = 'Invalid academic year ID';
    }
    
    // Check if academic year exists
    if (empty($errors['id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM academic_years WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            $errors['id'] = 'Academic year not found';
        }
    }
    
    // Check if academic year has associated terms
    if (empty($errors['id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE academic_year_id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0 && empty($_POST['force'])) {
            $errors['has_terms'] = 'This academic year has associated terms. Use force=1 to delete anyway.';
        }
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete associated terms first if any exist and force flag is set
        if (!empty($_POST['force'])) {
            $stmt = $conn->prepare('DELETE FROM terms WHERE academic_year_id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete the academic year
        $stmt = $conn->prepare('DELETE FROM academic_years WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Academic year deleted successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to delete academic year: ' . $e->getMessage()]);
    }
}

/**
 * Validate a date string
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
} 