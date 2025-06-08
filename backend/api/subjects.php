<?php
/**
 * Subjects API Handler
 * Endpoints for retrieving subjects data
 */

require_once __DIR__ . '/api_handler.php';

class SubjectsApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */    public function processRequest() {
        // Require authentication for all subject operations
        $this->requireAuthentication(['admin', 'teacher', 'student', 'headmaster']);
        
        // Route request based on method and path parameters
        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/subjects - List subjects
                    $this->listSubjects();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/subjects/{id} - Get specific subject
                    $this->getSubject($this->pathParams[0]);
                } else {
                    $this->sendResponse(['error' => 'Invalid endpoint'], 404);
                }
                break;
                
            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
                break;
        }
    }
    
    /**
     * List subjects with optional filtering
     */
    private function listSubjects() {
        // Get filter parameters
        $class_id = isset($this->queryParams['class_id']) ? (int)$this->queryParams['class_id'] : null;
        $search = isset($this->queryParams['search']) ? $this->queryParams['search'] : null;
        
        // Base SQL query
        $sql = "SELECT s.* FROM subjects s WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if specified
        if ($class_id) {
            // Join with class_subjects to filter by class
            $sql = "SELECT s.* FROM subjects s 
                    INNER JOIN class_subjects cs ON s.id = cs.subject_id 
                    WHERE cs.class_id = ?";
            $params[] = $class_id;
            $types .= "i";
        }
        
        if ($search) {
            // Add search condition
            $sql .= " AND (s.name LIKE ? OR s.code LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        // Add sorting
        $sql .= " ORDER BY s.name ASC";
        
        // Execute query
        $subjects = executeQuery($sql, $types, $params);
        
        // Send response
        $this->sendResponse(['data' => $subjects]);
    }
    
    /**
     * Get specific subject by ID
     */
    private function getSubject($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid subject ID'], 400);
        }
        
        // Fetch subject
        $sql = "SELECT * FROM subjects WHERE id = ?";
        $subject = executeQuery($sql, "i", [$id]);
        
        if (empty($subject)) {
            $this->sendResponse(['error' => 'Subject not found'], 404);
        }
        
        // Send response
        $this->sendResponse(['data' => $subject[0]]);
    }
}

// Initialize and process the request
$api = new SubjectsApiHandler();
$api->processRequest();