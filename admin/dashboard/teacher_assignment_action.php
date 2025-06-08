<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'con.php';
header('Content-Type: application/json');

// This script should handle assigning an EXISTING teacher (user_id) to an EXISTING section (section.id)
// by updating the sections table's class_teacher_user_id, room_id, and capacity.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get data from POST request
    $teacher_user_id = intval($_POST['teacher_id'] ?? 0); // Assuming 'teacher_id' from form is actually user_id
    $class_id = intval($_POST['class_id'] ?? 0);
    $section_label_id = isset($_POST['section_label_id']) ? intval($_POST['section_label_id']) : 0;
    $capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 32;
    $room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : null; // Use null for optional room_id
    $action = $_POST['action'] ?? 'assign'; // Add an action parameter

    // Validate required fields for assignment
    if ($action === 'assign' && (!$teacher_user_id || !$class_id || !$section_label_id)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields for assignment.']);
        exit;
    }

    if ($action === 'assign') {
        $conn->begin_transaction();
        try {
            // Find the section_id based on class_id and section_label_id
            $section_id = 0;
            $find_section_sql = "SELECT id FROM sections WHERE class_id = ? AND section_label_id = ? LIMIT 1";
            $find_section_stmt = $conn->prepare($find_section_sql);
             if (!$find_section_stmt) {
                 throw new Exception('Database error preparing to find section: ' . $conn->error);
             }
            $find_section_stmt->bind_param("ii", $class_id, $section_label_id);
            $find_section_stmt->execute();
            $find_section_result = $find_section_stmt->get_result();

            if ($row = $find_section_result->fetch_assoc()) {
                $section_id = $row['id'];
            }
            $find_section_stmt->close();

            if ($section_id > 0) {
                // Section found, update it
                $update_sql = "UPDATE sections SET class_teacher_user_id = ?, capacity = ?, room_id = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                 if (!$update_stmt) {
                     throw new Exception('Database error preparing to update section: ' . $conn->error);
                 }
                $update_stmt->bind_param("iiii", $teacher_user_id, $capacity, $room_id, $section_id);
                $update_stmt->execute();
                $update_stmt->close();

                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Class teacher, capacity, and room assigned successfully.', 'section_id' => $section_id]);
                exit;

            } else {
                // Section not found
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => 'Section not found for the selected class and section label.']);
                exit;
            }

        } catch (Exception $e) {
            $conn->rollback();
             error_log("Error in teacher assignment action: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error processing assignment: ' . $e->getMessage()]);
            exit;
        }
    } else if ($action === 'delete') {
        // Handle delete action if needed for assignments
        // The current teachersassign.php doesn't seem to have a delete assignment button,
        // but if it did, this is where the logic would go.
        // Need an assignment_id (which is section.id in this context) to delete the assignment.
        $section_id_to_unassign = intval($_POST['assignment_id'] ?? 0); // Assuming assignment_id is section.id
        if ($section_id_to_unassign > 0) {
            $conn->begin_transaction();
             try {
                // Unassign the teacher and room from the section
                $unassign_sql = "UPDATE sections SET class_teacher_user_id = NULL, room_id = NULL WHERE id = ?";
                $unassign_stmt = $conn->prepare($unassign_sql);
                 if (!$unassign_stmt) {
                     throw new Exception('Database error preparing to unassign section: ' . $conn->error);
                 }
                $unassign_stmt->bind_param("i", $section_id_to_unassign);
                $unassign_stmt->execute();
                $unassign_stmt->close();
                $conn->commit();
                echo json_encode(['success' => true, 'message' => 'Assignment removed successfully.']);
                exit;
             } catch (Exception $e) {
                 $conn->rollback();
                 error_log("Error in teacher assignment delete action: " . $e->getMessage());
                 echo json_encode(['success' => false, 'message' => 'Error removing assignment: ' . $e->getMessage()]);
                 exit;
             }
        }
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>