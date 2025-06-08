<?php
/**
 * Academic Years API Handler
 * Endpoints for retrieving academic years data
 */

require_once __DIR__ . '/api_handler.php';

class AcademicYearsApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */    public function processRequest() {
        // Require authentication for all operations
        $this->requireAuthentication(['admin', 'teacher', 'student', 'headmaster']);
        
        // Route request based on method and path parameters
        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/academic-years - List academic years
                    $this->listAcademicYears();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/academic-years/{id} - Get specific academic year
                    $this->getAcademicYear($this->pathParams[0]);
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
     * List academic years
     */
    private function listAcademicYears() {
        // Get filter parameters
        $is_current = isset($this->queryParams['is_current']) ? (bool)$this->queryParams['is_current'] : null;
        $search = isset($this->queryParams['search']) ? $this->queryParams['search'] : null;
        
        // Base SQL query
        $sql = "SELECT * FROM academic_years WHERE 1=1";
        $params = [];
        $types = "";
        
        // Add filters if specified
        if ($is_current !== null) {
            $sql .= " AND is_current = ?";
            $params[] = $is_current ? 1 : 0;
            $types .= "i";
        }
        
        if ($search) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
            $types .= "s";
        }
        
        // Add sorting
        $sql .= " ORDER BY start_date DESC";
        
        // Execute query
        $academicYears = executeQuery($sql, $types, $params);
        
        // Send response
        $this->sendResponse(['data' => $academicYears]);
    }
    
    /**
     * Get specific academic year by ID
     */
    private function getAcademicYear($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid academic year ID'], 400);
        }
        
        // Fetch academic year
        $sql = "SELECT * FROM academic_years WHERE id = ?";
        $academicYear = executeQuery($sql, "i", [$id]);
        
        if (empty($academicYear)) {
            $this->sendResponse(['error' => 'Academic year not found'], 404);
        }
        
        // Send response
        $this->sendResponse(['data' => $academicYear[0]]);
    }
}

// Initialize and process the request
$api = new AcademicYearsApiHandler();
$api->processRequest(); 