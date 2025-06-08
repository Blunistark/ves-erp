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
        addTerm();
        break;
    case 'edit':
        editTerm();
        break;
    case 'delete':
        deleteTerm();
        break;
    default:
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Invalid action specified']);
        break;
}

/**
 * Add a new term
 */
function addTerm() {
    global $conn;
    
    // Get and sanitize input
    $academicYearId = intval($_POST['academic_year_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate input
    $errors = [];
    
    if ($academicYearId <= 0) {
        $errors['academic_year_id'] = 'Invalid academic year ID';
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
    
    // Check if academic year exists
    if (empty($errors['academic_year_id'])) {
        $stmt = $conn->prepare('SELECT start_date, end_date FROM academic_years WHERE id = ?');
        $stmt->bind_param('i', $academicYearId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($yearStartDate, $yearEndDate);
        $exists = $stmt->fetch();
        $stmt->close();
        
        if (!$exists) {
            $errors['academic_year_id'] = 'Academic year not found';
        } else {
            // Check if term dates are within academic year dates
            if (strtotime($startDate) < strtotime($yearStartDate) || strtotime($endDate) > strtotime($yearEndDate)) {
                $errors['date_range'] = 'Term dates must be within the academic year date range';
            }
        }
    }
    
    // Check if name is unique within the academic year
    if (empty($errors['name']) && empty($errors['academic_year_id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE name = ? AND academic_year_id = ?');
        $stmt->bind_param('si', $name, $academicYearId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['name'] = 'A term with this name already exists in this academic year';
        }
    }
    
    // Check for overlapping dates within the academic year
    if (empty($errors['start_date']) && empty($errors['end_date']) && empty($errors['academic_year_id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE 
                               academic_year_id = ? AND (
                               (start_date <= ? AND end_date >= ?) OR 
                               (start_date <= ? AND end_date >= ?) OR
                               (start_date >= ? AND end_date <= ?))');
        $stmt->bind_param('issssss', $academicYearId, $endDate, $startDate, $endDate, $startDate, $startDate, $endDate);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['date_overlap'] = 'The specified date range overlaps with an existing term in this academic year';
        }
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Insert the new term
    $stmt = $conn->prepare('INSERT INTO terms (academic_year_id, name, start_date, end_date, description) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('issss', $academicYearId, $name, $startDate, $endDate, $description);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        $newId = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Term added successfully', 'id' => $newId]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to add term: ' . $conn->error]);
    }
}

/**
 * Edit an existing term
 */
function editTerm() {
    global $conn;
    
    // Get and sanitize input
    $id = intval($_POST['id'] ?? 0);
    $academicYearId = intval($_POST['academic_year_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate = trim($_POST['end_date'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Validate input
    $errors = [];
    
    if ($id <= 0) {
        $errors['id'] = 'Invalid term ID';
    }
    
    if ($academicYearId <= 0) {
        $errors['academic_year_id'] = 'Invalid academic year ID';
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
    
    // Check if term exists
    if (empty($errors['id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            $errors['id'] = 'Term not found';
        }
    }
    
    // Check if academic year exists
    if (empty($errors['academic_year_id'])) {
        $stmt = $conn->prepare('SELECT start_date, end_date FROM academic_years WHERE id = ?');
        $stmt->bind_param('i', $academicYearId);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($yearStartDate, $yearEndDate);
        $exists = $stmt->fetch();
        $stmt->close();
        
        if (!$exists) {
            $errors['academic_year_id'] = 'Academic year not found';
        } else {
            // Check if term dates are within academic year dates
            if (strtotime($startDate) < strtotime($yearStartDate) || strtotime($endDate) > strtotime($yearEndDate)) {
                $errors['date_range'] = 'Term dates must be within the academic year date range';
            }
        }
    }
    
    // Check if name is unique within the academic year
    if (empty($errors['name']) && empty($errors['academic_year_id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE name = ? AND academic_year_id = ? AND id != ?');
        $stmt->bind_param('sii', $name, $academicYearId, $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['name'] = 'A term with this name already exists in this academic year';
        }
    }
    
    // Check for overlapping dates within the academic year
    if (empty($errors['start_date']) && empty($errors['end_date']) && empty($errors['academic_year_id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE 
                               academic_year_id = ? AND 
                               id != ? AND (
                               (start_date <= ? AND end_date >= ?) OR 
                               (start_date <= ? AND end_date >= ?) OR
                               (start_date >= ? AND end_date <= ?))');
        $stmt->bind_param('iissssss', $academicYearId, $id, $endDate, $startDate, $endDate, $startDate, $startDate, $endDate);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            $errors['date_overlap'] = 'The specified date range overlaps with an existing term in this academic year';
        }
    }
    
    // If there are validation errors, return them
    if (!empty($errors)) {
        http_response_code(422); // Unprocessable Entity
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit;
    }
    
    // Update the term
    $stmt = $conn->prepare('UPDATE terms SET academic_year_id = ?, name = ?, start_date = ?, end_date = ?, description = ? WHERE id = ?');
    $stmt->bind_param('issssi', $academicYearId, $name, $startDate, $endDate, $description, $id);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Term updated successfully']);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to update term: ' . $conn->error]);
    }
}

/**
 * Delete a term
 */
function deleteTerm() {
    global $conn;
    
    // Get and sanitize input
    $id = intval($_POST['id'] ?? 0);
    
    // Validate input
    $errors = [];
    
    if ($id <= 0) {
        $errors['id'] = 'Invalid term ID';
    }
    
    // Check if term exists
    if (empty($errors['id'])) {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM terms WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count == 0) {
            $errors['id'] = 'Term not found';
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
        // Delete data associated with this term (if any)
        // For example, timetables, exams, etc.
        // This would depend on your database schema
        
        // Delete the term
        $stmt = $conn->prepare('DELETE FROM terms WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode(['success' => true, 'message' => 'Term deleted successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        http_response_code(500); // Internal Server Error
        echo json_encode(['success' => false, 'message' => 'Failed to delete term: ' . $e->getMessage()]);
    }
}

/**
 * Validate a date string
 */
function validateDate($date) {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
} 