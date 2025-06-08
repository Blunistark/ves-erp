<?php
require_once 'con.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

// Update teacher subjects
if ($action === 'update') {
    $teacherUserId = intval($_POST['teacher_id'] ?? 0); // Assuming this is user_id now
    $subjectIds = json_decode($_POST['subject_ids'] ?? '[]', true);

    if (!$teacherUserId) {
        echo json_encode(['success' => false, 'message' => 'Teacher User ID is required.']);
        exit;
    }

    // Verify teacher (user) exists with role 'teacher'
    $stmt = $conn->prepare('SELECT 1 FROM users WHERE id = ? AND role = "teacher"');
     if (!$stmt) {
         error_log('Prepare (verify user) failed: ' . $conn->error);
         echo json_encode(['success' => false, 'message' => 'Database error during user verification. ']);
         exit;
     }
    $stmt->bind_param('i', $teacherUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
         $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Teacher not found or invalid user role.']);
        exit;
    }
     $stmt->close();


    // Begin transaction
    $conn->begin_transaction();

    try {
        // First, delete all existing assignments for this teacher
        $stmt = $conn->prepare('DELETE FROM teacher_subjects WHERE teacher_user_id = ?');
         if (!$stmt) {
             throw new Exception('Failed to prepare delete statement: ' . $conn->error);
         }
        $stmt->bind_param('i', $teacherUserId);
        $stmt->execute();
         $stmt->close(); // Close statement after execution

        // Then, insert new assignments
        if (!empty($subjectIds)) {
            $values = [];
            $params = [];

            foreach ($subjectIds as $subjectId) {
                $subjectId = intval($subjectId);
                // Optional: Verify subject_id exists in subjects table before inserting
                // This adds robustness but increases queries. Skipping for now to match original pattern.
                if ($subjectId > 0) {
                    $values[] = "(?, ?)";
                    $params[] = $teacherUserId;
                    $params[] = $subjectId;
                }
            }

            if (!empty($values)) {
                // Construct the INSERT statement with multiple value sets
                $sql = 'INSERT INTO teacher_subjects (teacher_user_id, subject_id) VALUES ' . implode(', ', $values);
                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception('Failed to prepare insert statement: ' . $conn->error);
                }

                // Dynamically build the types string based on the number of parameters
                $types = str_repeat('i', count($params));

                // Bind all parameters using ... splat operator (requires PHP 5.6+) or call_user_func_array
                // Using splat operator is cleaner if PHP version supports it.
                // For broader compatibility or if splat is not preferred, call_user_func_array is used.
                // The original code's `this_refs` and `call_user_func_array` looked incorrect.
                // Let's use a standard way to bind parameters dynamically.

                 // Create references for parameters if using call_user_func_array (for PHP < 5.6 compatibility with bind_param)
                $bindParamsReferences = [];
                foreach ($params as $key => $value) {
                    $bindParamsReferences[$key] = &$params[$key];
                }
                 // Prepend the types string to the parameters array
                array_unshift($bindParamsReferences, $types);

                 // Call bind_param dynamically
                 if (!call_user_func_array([$stmt, 'bind_param'], $bindParamsReferences)) {
                      throw new Exception('Failed to bind parameters for insert: ' . $stmt->error);
                 }


                if (!$stmt->execute()) {
                    throw new Exception('Failed to execute insert statement: ' . $stmt->error);
                }
                $stmt->close(); // Close statement after execution

            }
        }

        // Commit transaction
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Teacher subject assignments updated successfully.']);

    } catch (Exception $e) {
        // Roll back transaction on error
        $conn->rollback();
        error_log("Error updating teacher subjects: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error updating assignments: ' . $e->getMessage()]);
    }

    exit;
}

// Invalid action
echo json_encode(['success' => false, 'message' => 'Invalid action.']);

// The helper function `this_refs` is likely not needed or was used incorrectly. Removing it.
/*
function this_refs($arr) {
    $refs = [];
    foreach ($arr as $key => $value) {
        $refs[$key] = &$arr[$key];
    }
    return $refs;
}
*/
?> 