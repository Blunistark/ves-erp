<?php
/**
 * Classes API Handler
 * Endpoints for retrieving classes data
 */

require_once __DIR__ . '/api_handler.php';

class ClassesApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */
    public function processRequest() {
        // Require authentication for all operations
        $this->requireAuthentication(['admin', 'teacher', 'student']);
        
        // Route request based on method and path parameters
        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/classes - List classes
                    $this->listClasses();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/classes/{id} - Get specific class
                    $this->getClass($this->pathParams[0]);
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
     * List classes
     */
    private function listClasses() {
        // Get filter parameters
        $search = isset($this->queryParams['search']) ? $this->queryParams['search'] : null;
        
        // Base SQL query
        $sql = "SELECT * FROM classes WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if specified
        if ($search) {
            $sql .= " AND (name LIKE ? OR description LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        // Add sorting
        $sql .= " ORDER BY name ASC";
        
        // Execute query
        $classes = executeQuery($sql, $types, $params);
        
        // Send response
        $this->sendResponse(['data' => $classes]);
    }
    
    /**
     * Get specific class by ID
     */
    private function getClass($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid class ID'], 400);
        }
        
        // Fetch class
        $sql = "SELECT * FROM classes WHERE id = ?";
        $class = executeQuery($sql, "i", [$id]);
        
        if (empty($class)) {
            $this->sendResponse(['error' => 'Class not found'], 404);
        }
        
        // Fetch sections for this class
        $sql = "SELECT * FROM sections WHERE class_id = ? ORDER BY name ASC";
        $sections = executeQuery($sql, "i", [$id]);
        
        // Add sections to class data
        $classData = $class[0];
        $classData['sections'] = $sections;
        
        // Send response
        $this->sendResponse(['data' => $classData]);
    }
}

// Initialize and process the request
$api = new ClassesApiHandler();
$api->processRequest(); 