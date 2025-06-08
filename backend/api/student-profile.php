<?php
/**
 * Student Profile API Handler
 * Endpoint for student's profile information
 */

require_once __DIR__ . '/api_handler.php';

class StudentProfileApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */
    public function processRequest() {
        // Authentication check
        $this->requireAuthentication(['student']);

        switch ($this->method) {
            case 'GET':
                // GET /api/student-profile - Get current student's profile
                $this->getStudentProfile();
                break;
                
            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
                break;
        }
    }
    
    /**
     * Get current student's profile information
     */
    private function getStudentProfile() {
        $student_id = $_SESSION['user_id'];
        
        if (!$student_id) {
            $this->sendResponse(['error' => 'Unauthorized or invalid session'], 401);
        }
        
        try {
            // Get student profile with current enrollment
            $sql = "SELECT s.*, 
                    e.roll_number, e.class_id, e.section_id, 
                    c.name as class_name, sec.name as section_name,
                    ay.name as academic_year
                    FROM students s
                    LEFT JOIN enrollments e ON s.user_id = e.student_id
                    LEFT JOIN classes c ON e.class_id = c.id
                    LEFT JOIN sections sec ON e.section_id = sec.id
                    LEFT JOIN academic_years ay ON e.academic_year_id = ay.id
                    WHERE s.user_id = ? AND e.status = 'active'";
                    
            // Get only current academic year enrollment
            $current_academic_year_id = getCurrentAcademicYearId();
            if ($current_academic_year_id) {
                $sql .= " AND e.academic_year_id = ?";
                $student = executeQuery($sql, "ii", [$student_id, $current_academic_year_id]);
            } else {
                $student = executeQuery($sql, "i", [$student_id]);
            }
            
            if (empty($student)) {
                $this->sendResponse(['error' => 'Student profile not found or not enrolled in current academic year'], 404);
            } else {
                // Add additional student statistics if needed
                $this->sendResponse(['success' => true, 'data' => $student[0]]);
            }
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false, 
                'error' => 'Failed to fetch student profile', 
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

// Initialize and process the request
$api = new StudentProfileApiHandler();
$api->processRequest();
?> 