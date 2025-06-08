<?php
/**
 * Sections API Handler
 * Endpoints for retrieving sections data
 */

require_once __DIR__ . '/api_handler.php';

class SectionsApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */    public function processRequest() {
        // Require authentication for all operations
        $this->requireAuthentication(['admin', 'teacher', 'student', 'headmaster']);
        
        // Route request based on method and path parameters
        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/sections - List sections
                    $this->listSections();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/sections/{id} - Get specific section
                    $this->getSection($this->pathParams[0]);
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
     * List sections
     */
    private function listSections() {
        // Get filter parameters
        $class_id = isset($this->queryParams['class_id']) ? (int)$this->queryParams['class_id'] : null;
        $search = isset($this->queryParams['search']) ? $this->queryParams['search'] : null;
        
        // Base SQL query
        $sql = "SELECT s.*, c.name as class_name 
                FROM sections s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if specified
        if ($class_id) {
            $sql .= " AND s.class_id = ?";
            $params[] = $class_id;
            $types .= "i";
        }
        
        if ($search) {
            $sql .= " AND (s.name LIKE ? OR c.name LIKE ?)";
            $searchParam = "%$search%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $types .= "ss";
        }
        
        // Add sorting
        $sql .= " ORDER BY c.name ASC, s.name ASC";
        
        // Execute query
        $sections = executeQuery($sql, $types, $params);
        
        // Send response
        $this->sendResponse(['data' => $sections]);
    }
    
    /**
     * Get specific section by ID
     */
    private function getSection($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid section ID'], 400);
        }
        
        // Fetch section with class information
        $sql = "SELECT s.*, c.name as class_name 
                FROM sections s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.id = ?";
        
        $section = executeQuery($sql, "i", [$id]);
        
        if (empty($section)) {
            $this->sendResponse(['error' => 'Section not found'], 404);
        }
        
        // Send response
        $this->sendResponse(['data' => $section[0]]);
    }
}

// Initialize and process the request
$api = new SectionsApiHandler();
$api->processRequest(); 