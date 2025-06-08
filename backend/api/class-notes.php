<?php

require_once __DIR__ . '/api_handler.php';

// Remove header as it should be handled by ApiHandler
// header('Content-Type: application/json');

// Remove procedural routing logic
/*
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];
$parsed_url = parse_url($request_uri);
$path_parts = explode('/', trim($parsed_url['path'], '/'));

$resource = $path_parts[1] ?? null;
$id = isset($path_parts[2]) && is_numeric($path_parts[2]) ? intval($path_parts[2]) : null;

// Removed global $conn;

switch ($method) {
    case 'GET':
        if ($id) {
            getNoteById($conn, $id);
        } else {
            listNotes($conn);
        }
        break;

    case 'POST':
        createNote($conn);
        break;

    case 'PUT':
        if ($id) {
            updateNote($conn, $id);
        } else {
            sendErrorResponse(400, 'Note ID required for update.');
        }
        break;

    case 'DELETE':
        if ($id) {
            deleteNote($conn, $id);
        } else {
            sendErrorResponse(400, 'Note ID required for delete.');
        }
        break;

    default:
        sendErrorResponse(405, 'Method Not Allowed.');
        break;
}
*/

// Define the Class Notes API Handler class, extending ApiHandler
class ClassNotesApiHandler extends ApiHandler {

    /**
     * Process the API request based on method and path parameters
     */
    public function processRequest() {
        // Authentication check (can be done here or within ApiHandler based on its implementation)
        // $this->requireAuthentication(['teacher']); // Assuming only teachers can manage notes

        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/class-notes - List notes
                    $this->listNotes();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/class-notes/{id} - Get specific note
                    $this->getNoteById($this->pathParams[0]);
                } else {
                    $this->sendResponse(['error' => 'Invalid endpoint'], 404);
                }
                break;

            case 'POST':
                // POST /api/class-notes - Create note
                $this->createNote();
                break;

            case 'PUT':
                if (count($this->pathParams) === 1) {
                    // PUT /api/class-notes/{id} - Update specific note
                     $this->updateNote($this->pathParams[0]);
                } else {
                    $this->sendResponse(['error' => 'Note ID required for update'], 400);
                }
                break;

            case 'DELETE':
                 if (count($this->pathParams) === 1) {
                    // DELETE /api/class-notes/{id} - Delete specific note
                     $this->deleteNote($this->pathParams[0]);
                 } else {
                    $this->sendResponse(['error' => 'Note ID required for delete'], 400);
                 }
                 break;

            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
                break;
        }
    }

    /**
     * List class notes
     */
    private function listNotes() {
        // Get filter parameters from query parameters ($this->queryParams)
        $filter_class_id = $this->queryParams['class_id'] ?? null;
        $filter_section_id = $this->queryParams['section_id'] ?? null;
        $filter_subject_id = $this->queryParams['subject_id'] ?? null;
        $filter_date = $this->queryParams['note_date'] ?? null;

        // Base SQL query
        $sql = "SELECT cn.*, c.name AS class_name, s.name AS section_name, sub.name AS subject_name
                FROM class_notes cn
                JOIN classes c ON cn.class_id = c.id
                JOIN sections s ON cn.section_id = s.id
                JOIN subjects sub ON cn.subject_id = sub.id
                WHERE 1=1"; // Start with a true condition to easily append filters

        $params = [];
        $types = "";

        // Add filters
        if ($filter_class_id) {
            $sql .= " AND cn.class_id = ?";
            $params[] = $filter_class_id;
            $types .= "i";
        }
        if ($filter_section_id) {
            $sql .= " AND cn.section_id = ?";
            $params[] = $filter_section_id;
            $types .= "i";
        }
         if ($filter_subject_id) {
            $sql .= " AND cn.subject_id = ?";
            $params[] = $filter_subject_id;
            $types .= "i";
        }
        if ($filter_date) {
            $sql .= " AND cn.note_date = ?";
            $params[] = $filter_date;
            $types .= "s";
        }

        // Add sorting
        $sql .= " ORDER BY cn.note_date DESC, cn.created_at DESC";

        // Execute query using the helper function (assuming it's provided by ApiHandler or functions.php)
        // Need to ensure executeQuery is available in this scope or via $this if it's a method
        // Assuming executeQuery is globally available or in functions.php included by api_handler.php
        $notes = executeQuery($sql, $types, $params);

        // Send response using the handler method
        $this->sendResponse(['success' => true, 'data' => $notes]);
    }

    /**
     * Get specific class note by ID
     */
    private function getNoteById($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid note ID'], 400);
            return;
        }

        // Fetch note
        $sql = "SELECT cn.*, c.name AS class_name, s.name AS section_name, sub.name AS subject_name
                FROM class_notes cn
                JOIN classes c ON cn.class_id = c.id
                JOIN sections s ON cn.section_id = s.id
                JOIN subjects sub ON cn.subject_id = sub.id
                WHERE cn.id = ?";

        $note = executeQuery($sql, "i", [$id]);

        if (empty($note)) {
            $this->sendResponse(['error' => 'Note not found'], 404);
        } else {
            $this->sendResponse(['success' => true, 'data' => $note[0]]); // executeQuery returns an array
        }
    }

    /**
     * Create a new class note
     */
    private function createNote() {
        // Check authentication first
        $this->requireAuthentication(['teacher']);

        // Get data from the request body ($this->data)
        $data = $this->data;

        if (empty($data['class_info']) || empty($data['subject_id']) || empty($data['note_date']) || empty($data['note_content'])) {
            $this->sendResponse(['error' => 'Missing required fields'], 400);
            return;
        }

        $class_info = $data['class_info'];
        $class_id = $class_info['class_id'];
        $section_id = $class_info['section_id'];
        $subject_id = $data['subject_id'];
        $note_date = $data['note_date'];
        $note_content = htmlspecialchars($data['note_content'], ENT_QUOTES, 'UTF-8');

        // Validate note_date
        $date_obj = DateTime::createFromFormat('Y-m-d', $note_date);
        if (!$date_obj || $date_obj->format('Y-m-d') !== $note_date) {
            $this->sendResponse(['error' => 'Invalid note_date format. Expected YYYY-MM-DD.'], 400);
            return;
        }

        // Get teacher ID from session
        $teacher_id = $this->getCurrentUserId();
        if (!$teacher_id) {
            $this->sendResponse(['error' => 'Not authenticated as a teacher'], 401);
            return;
        }

        // Academic year (get using a helper function)
        $current_academic_year_id = getCurrentAcademicYearId();
        if (!$current_academic_year_id) {
            $this->sendResponse(['error' => 'Academic year not found.'], 500);
            return;
        }

        // Get timetable_id if exists
        $sql = "SELECT id FROM timetables WHERE class_id = ? AND section_id = ? AND academic_year_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $this->sendResponse(['error' => 'Failed to prepare timetable query'], 500);
            return;
        }

        $stmt->bind_param("iii", $class_id, $section_id, $current_academic_year_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $timetable_id = $result->fetch_assoc()['id'] ?? null;
        $stmt->close();

        // Prepare and execute the INSERT statement
        $sql = "INSERT INTO class_notes (teacher_id, timetable_id, class_id, section_id, subject_id, note_date, note_content)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        if (!$stmt) {
            $this->sendResponse(['error' => 'Failed to prepare insert query'], 500);
            return;
        }

        $stmt->bind_param("iiiiiss", $teacher_id, $timetable_id, $class_id, $section_id, $subject_id, $note_date, $note_content);
        
        try {
            if ($stmt->execute()) {
                $note_id = $stmt->insert_id;
                $stmt->close();
                $this->sendResponse(['success' => true, 'message' => 'Note created successfully.', 'id' => $note_id]);
            } else {
                $stmt->close();
                $this->sendResponse(['error' => 'Failed to create note.'], 500);
            }
        } catch (Exception $e) {
            $stmt->close();
            $this->sendResponse(['error' => 'Failed to create note.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an existing class note
     */
    private function updateNote($id) {
         // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid note ID'], 400);
            return;
        }

        // Get data from the request body ($this->data)
        $data = $this->data;

        if (!isset($data['note_content']) && !isset($data['note_date']) && !isset($data['status'])) {
            $this->sendResponse(['error' => 'No fields provided for update.'], 400);
            return;
        }

        // Teacher ID (ensure teacher owns the note if authentication is enabled)
        // $teacher_id = $this->getCurrentUserId(); // Get current user ID
        // Add a check: WHERE id = ? AND teacher_id = ?

        // Build the update query dynamically
        $update_fields = [];
        $params = [];
        $types = "";

        if (isset($data['note_date'])) {
            $update_fields[] = "note_date = ?";
            $params[] = $data['note_date'];
            $types .= "s";
             // Validate note_date if provided
            $date_obj = DateTime::createFromFormat('Y-m-d', $data['note_date']);
            if (!$date_obj || $date_obj->format('Y-m-d') !== $data['note_date']) {
                $this->sendResponse(['error' => 'Invalid note_date format. Expected YYYY-MM-DD.'], 400);
                return;
            }
        }

        if (isset($data['note_content'])) {
            $update_fields[] = "note_content = ?";
            $params[] = htmlspecialchars($data['note_content'], ENT_QUOTES, 'UTF-8');
            $types .= "s";
        }
         if (isset($data['status'])) {
            $update_fields[] = "status = ?";
            $params[] = $data['status'];
            $types .= "s";
        }

        if (empty($update_fields)) {
            $this->sendResponse(['error' => 'No valid fields provided for update.'], 400);
            return;
        }

        $sql = "UPDATE class_notes SET " . implode(', ', $update_fields) . " WHERE id = ?";
        $params[] = $id; // Add note ID parameter
        $types .= "i"; // Add type for note ID

        // If authentication is enabled, add teacher_id to WHERE clause and params/types
        // $sql .= " AND teacher_id = ?";
        // $params[] = $teacher_id;
        // $types .= "i";

        // Execute update using executeQuery (assuming it handles updates)
        try {
            $affectedRows = executeQuery($sql, $types, $params, true); // Assuming executeQuery returns affected rows for updates if 4th param is true
            if ($affectedRows > 0) {
                 $this->sendResponse(['success' => true, 'message' => 'Note updated successfully.']);
            } else {
                 $this->sendResponse(['error' => 'Note not found or no changes were made.'], 404);
            }
        } catch (Exception $e) {
             $this->sendResponse(['error' => 'Failed to update note.', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a class note
     */
    private function deleteNote($id) {
         // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid note ID'], 400);
            return;
        }

        // Teacher ID (ensure teacher owns the note if authentication is enabled)
        // $teacher_id = $this->getCurrentUserId(); // Get current user ID

        $sql = "DELETE FROM class_notes WHERE id = ?";
        $params = [$id];
        $types = "i";

        // If authentication is enabled, add teacher_id to WHERE clause and params/types
        // $sql .= " AND teacher_id = ?";
        // $params[] = $teacher_id;
        // $types .= "i";

        // Execute delete using executeQuery (assuming it handles deletes)
        try {
             $affectedRows = executeQuery($sql, $types, $params, true); // Assuming executeQuery returns affected rows for deletes if 4th param is true
             if ($affectedRows > 0) {
                $this->sendResponse(['success' => true, 'message' => 'Note deleted successfully.']);
            } else {
                $this->sendResponse(['error' => 'Note not found or already deleted.'], 404);
            }
        } catch (Exception $e) {
             $this->sendResponse(['error' => 'Failed to delete note.', 'details' => $e->getMessage()], 500);
        }
    }

     // Assuming getCurrentAcademicYearId is a global function or in functions.php
    /*
     private function getCurrentAcademicYearId() {
          // Implement logic to get current academic year ID
          // Might need access to database connection, which is available via $this->conn if ApiHandler sets it up
          // For now, assuming a global function or one from functions.php is used
          global $conn; // Access global connection if needed
          $res = $conn->query("SELECT id FROM academic_years WHERE NOW() BETWEEN start_date AND end_date LIMIT 1");
          return $res->fetch_assoc()['id'] ?? null;
     }
     */

    // Assuming executeQuery is provided by ApiHandler or functions.php and handles queries
    // This function will handle preparing and executing statements and returning results/affected rows
    /*
    protected function executeQuery($sql, $types = "", $params = [], $returnAffectedRows = false) {
         // Implementation depends on how ApiHandler manages the connection ($this->conn)
         // You would typically prepare, bind, execute, and get results/affected rows here
         // Example (assuming $this->conn is the mysqli connection):
         $stmt = $this->conn->prepare($sql);
         if ($types && $params) {
              $stmt->bind_param($types, ...$params);
         }
         $stmt->execute();

         if ($returnAffectedRows) {
              return $stmt->affected_rows;
         } else {
              $result = $stmt->get_result();
              $data = [];
              while ($row = $result->fetch_assoc()) {
                   $data[] = $row;
              }
              $stmt->close();
              return $data;
         }
    }
    */
}

// Initialize and process the request
$api = new ClassNotesApiHandler();
$api->processRequest();

// Remove old helper functions
/*
function sendJsonResponse($data) { ... }
function sendErrorResponse($status_code, $message, $additional_data = []) { ... }
function getCurrentAcademicYearId($conn) { ... }
*/
?>
