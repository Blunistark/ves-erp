<?php
/**
 * API Handler Base Class
 * Common functionality for all API endpoints
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/functions.php';

class ApiHandler {
    protected $method;
    protected $data;
    protected $pathParams = [];
    protected $queryParams = [];
    protected $conn;
    
    /**
     * Initialize the API handler
     */
    public function __construct() {
        // Start session for authentication
        startSecureSession();
        
        // Set content type to JSON
        header('Content-Type: application/json');
        
        // Get HTTP method
        $this->method = $_SERVER['REQUEST_METHOD'];
        
        // Initialize database connection
        $this->conn = getDbConnection();
        if (!$this->conn) {
            $this->sendResponse(['error' => 'Database connection failed'], 500);
            exit;
        }
        
        // Parse request data
        $this->parseRequest();
        
        // Parse URL parameters
        $this->parseUrlParams();
    }
    
    /**
     * Clean up resources
     */
    public function __destruct() {
        // Close database connection if open
        if ($this->conn) {
            $this->conn->close();
        }
    }
    
    /**
     * Parse the request data based on content type and method
     */
    protected function parseRequest() {
        switch ($this->method) {
            case 'GET':
                $this->data = $_GET;
                break;
            case 'POST':
            case 'PUT':
            case 'DELETE':
                $content = file_get_contents('php://input');
                if (!empty($content)) {
                    $this->data = json_decode($content, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $this->sendResponse(['error' => 'Invalid JSON payload'], 400);
                        exit;
                    }
                } else {
                    $this->data = $_POST;
                }
                break;
        }
    }
    
    /**
     * Parse URL path parameters and query parameters
     */
    protected function parseUrlParams() {
        // Get path info if available
        $pathInfo = $_SERVER['PATH_INFO'] ?? '';
        
        // Extract path parameters
        if (!empty($pathInfo)) {
            $parts = explode('/', trim($pathInfo, '/'));
            if (count($parts) > 0) {
                $this->pathParams = $parts;
            }
        }
        
        // Extract query parameters
        $this->queryParams = $_GET;
    }
    
    /**
     * Check if user is authenticated
     * @param array $roles Required roles
     * @return bool Whether user is authenticated with required role
     */
    protected function isAuthenticated($roles = null) {
        if (!isLoggedIn()) {
            return false;
        }
        
        // If roles specified, check if user has one of the roles
        if ($roles !== null) {
            return hasRole($roles);
        }
        
        return true;
    }
    
    /**
     * Require authentication
     * @param array $roles Required roles
     */
    protected function requireAuthentication($roles = null) {
        if (!$this->isAuthenticated($roles)) {
            $this->sendResponse(['error' => 'Unauthorized'], 401);
            exit;
        }
    }
    
    /**
     * Send JSON response
     * @param mixed $data Response data
     * @param int $statusCode HTTP status code
     */
    protected function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Validate required fields
     * @param array $requiredFields Required field names
     * @return bool Whether all required fields are present
     */
    protected function validateRequiredFields($requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($this->data[$field]) || (empty($this->data[$field]) && $this->data[$field] !== '0')) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Get current logged in user ID
     * @return int|null User ID if logged in, null otherwise
     */
    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Process the API request
     */
    public function processRequest() {
        // Override in child classes
        $this->sendResponse(['error' => 'Method not implemented'], 501);
    }
} 