<?php

require_once __DIR__ . '/api_handler.php';

class StudentsApiHandler extends ApiHandler {
    
    /**
     * Process the API request based on method and path parameters
     */
    public function processRequest() {
        // Authentication check
        $this->requireAuthentication(['teacher', 'admin']);

        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/students - List students with optional filters
                    $this->listStudents();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/students/{id} - Get specific student
                    $this->getStudentById($this->pathParams[0]);
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
     * List students with optional filtering
     */
    private function listStudents() {
        // Get filter parameters from query parameters
        $filter_class_id = $this->queryParams['class_id'] ?? null;
        $filter_section_id = $this->queryParams['section_id'] ?? null;
        $filter_name = $this->queryParams['name'] ?? null;

        // Base SQL query
        $sql = "SELECT s.*, e.roll_number, e.class_id, e.section_id, c.name as class_name, sec.name as section_name
                FROM students s
                LEFT JOIN enrollments e ON s.user_id = e.student_id
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN sections sec ON e.section_id = sec.id
                WHERE 1=1"; // Start with a true condition to easily append filters

        $params = [];
        $types = "";

        // Add filters
        if ($filter_class_id) {
            $sql .= " AND e.class_id = ?";
            $params[] = $filter_class_id;
            $types .= "i";
        }
        
        if ($filter_section_id) {
            $sql .= " AND e.section_id = ?";
            $params[] = $filter_section_id;
            $types .= "i";
        }
        
        if ($filter_name) {
            $sql .= " AND s.full_name LIKE ?";
            $params[] = "%$filter_name%";
            $types .= "s";
        }

        // Get only active enrollments and current academic year
        $current_academic_year_id = getCurrentAcademicYearId();
        if ($current_academic_year_id) {
            $sql .= " AND e.academic_year_id = ?";
            $params[] = $current_academic_year_id;
            $types .= "i";
        }
        
        $sql .= " AND e.status = 'active'";

        // Add sorting
        $sql .= " ORDER BY s.full_name";

        // Execute query
        try {
            $students = executeQuery($sql, $types, $params);
            $this->sendResponse(['success' => true, 'data' => $students]);
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Failed to fetch students', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Get specific student by ID
     */
    private function getStudentById($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid student ID'], 400);
            return;
        }

        // Fetch student with enrollment details
        $sql = "SELECT s.*, e.roll_number, e.class_id, e.section_id, 
                       c.name as class_name, sec.name as section_name
                FROM students s
                LEFT JOIN enrollments e ON s.user_id = e.student_id
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN sections sec ON e.section_id = sec.id
                WHERE s.user_id = ? AND e.status = 'active'";

        try {
            $student = executeQuery($sql, "i", [$id]);

            if (empty($student)) {
                $this->sendResponse(['error' => 'Student not found'], 404);
            } else {
                // Get additional student details like attendance, grades, etc.
                $studentDetails = $this->getStudentDetails($id);
                $student[0]['details'] = $studentDetails;
                
                $this->sendResponse(['success' => true, 'data' => $student[0]]);
            }
        } catch (Exception $e) {
            $this->sendResponse(['error' => 'Failed to fetch student details', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Get additional student details
     */
    private function getStudentDetails($studentId) {
        // This would fetch additional details like:
        // - Attendance statistics
        // - Recent grades
        // - Behavior reports
        // - etc.
        
        // For now, just return placeholder data
        return [
            'attendance' => [
                'present_days' => 85,
                'absent_days' => 5,
                'late_days' => 2,
                'attendance_percentage' => 95
            ],
            'academics' => [
                'gpa' => 3.7,
                'rank' => 5,
                'total_students' => 40
            ]
        ];
    }
}

// Initialize and process the request
$api = new StudentsApiHandler();
$api->processRequest();
?> 