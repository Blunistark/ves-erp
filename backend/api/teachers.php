<?php
/**
 * Teachers API Handler
 * Endpoints for retrieving teachers data
 */

require_once __DIR__ . '/api_handler.php';

class TeachersApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */
    public function processRequest() {
        // Require authentication for all teacher operations
        $this->requireAuthentication(['admin', 'teacher', 'student']);
        
        // Route request based on method and path parameters
        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/teachers - List teachers
                    $this->listTeachers();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/teachers/{id} - Get specific teacher
                    $this->getTeacher($this->pathParams[0]);
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
     * List teachers with optional filtering
     */
    private function listTeachers() {
        // Get filter parameters
        $subject_id = isset($this->queryParams['subject_id']) ? (int)$this->queryParams['subject_id'] : null;
        $search = isset($this->queryParams['search']) ? $this->queryParams['search'] : null;
        
        // Base SQL query
        $sql = "SELECT t.user_id, u.id as user_id, u.full_name, u.email, t.employee_number 
                FROM teachers t
                JOIN users u ON t.user_id = u.id
                WHERE u.status = 'active'";
        $params = [];
        $types = "";
        
        // Add filters if specified
        if ($subject_id) {
            // Join with teacher_subjects to filter by subject
            $sql = "SELECT t.user_id, u.id as user_id, u.full_name, u.email, t.employee_number 
                    FROM teachers t
                    JOIN users u ON t.user_id = u.id
                    JOIN teacher_subjects ts ON t.user_id = ts.teacher_id
                    WHERE u.status = 'active' AND ts.subject_id = ?";
            $params[] = $subject_id;
            $types .= "i";
        }
        
        if ($search) {
            // Add search condition
            $sql .= " AND (u.full_name LIKE ? OR u.email LIKE ? OR t.employee_number LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "sss";
        }
        
        // Add sorting
        $sql .= " ORDER BY u.full_name ASC";
        
        // Execute query
        $teachers = executeQuery($sql, $types, $params);
        
        // Send response
        $this->sendResponse(['data' => $teachers]);
    }
    
    /**
     * Get specific teacher by ID
     */
    private function getTeacher($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid teacher ID'], 400);
        }
        
        // Fetch teacher
        $sql = "SELECT t.user_id, u.id as user_id, u.full_name, u.email, t.employee_number
                FROM teachers t
                JOIN users u ON t.user_id = u.id
                WHERE t.user_id = ? AND u.status = 'active'";
        $teacher = executeQuery($sql, "i", [$id]);
        
        if (empty($teacher)) {
            $this->sendResponse(['error' => 'Teacher not found'], 404);
        }
        
        // Fetch teacher's subjects
        $sql = "SELECT s.id, s.name, s.code
                FROM subjects s
                JOIN teacher_subjects ts ON s.id = ts.subject_id
                WHERE ts.teacher_id = ?";
        $subjects = executeQuery($sql, "i", [$id]);
        
        // Add subjects to teacher data
        $teacherData = $teacher[0];
        $teacherData['subjects'] = $subjects;
        
        // Send response
        $this->sendResponse(['data' => $teacherData]);
    }
}

// Initialize and process the request
$api = new TeachersApiHandler();
$api->processRequest(); 