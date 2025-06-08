<?php
/**
 * Student Timetable API Handler
 * Dedicated endpoint for student timetable data
 */

require_once __DIR__ . '/api_handler.php';

class StudentTimetableApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */
    public function processRequest() {
        // Authentication check
        $this->requireAuthentication(['student']);

        switch ($this->method) {
            case 'GET':
                // GET /api/student-timetable - Get student's timetable with periods
                $this->getStudentTimetable();
                break;
                
            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
                break;
        }
    }
    
    /**
     * Get student's timetable with periods
     */
    private function getStudentTimetable() {
        $student_id = $_SESSION['user_id'];
        
        if (!$student_id) {
            $this->sendResponse(['error' => 'Unauthorized or invalid session'], 401);
        }
        
        try {
            // Step 1: Get student's class and section
            $sql = "SELECT s.*, 
                    e.roll_number, e.class_id, e.section_id, 
                    c.name as class_name, sec.name as section_name
                    FROM students s
                    LEFT JOIN enrollments e ON s.user_id = e.student_id
                    LEFT JOIN classes c ON e.class_id = c.id
                    LEFT JOIN sections sec ON e.section_id = sec.id
                    WHERE s.user_id = ? AND e.status = 'active'";
                    
            // Get only current academic year enrollment
            $current_academic_year_id = getCurrentAcademicYearId();
            if (!$current_academic_year_id) {
                $this->sendResponse(['error' => 'Current academic year not set'], 500);
                return;
            }
            
            $sql .= " AND e.academic_year_id = ?";
            $student = executeQuery($sql, "ii", [$student_id, $current_academic_year_id]);
            
            if (empty($student)) {
                $this->sendResponse(['error' => 'Student enrollment not found for current academic year'], 404);
                return;
            }
            
            $studentData = $student[0];
            $class_id = $studentData['class_id'];
            $section_id = $studentData['section_id'];
            
            // Step 2: Get the latest published timetable for this class/section
            $sql = "SELECT t.*, 
                    ay.name as academic_year_name, 
                    c.name as class_name, 
                    s.name as section_name 
                    FROM timetables t
                    LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                    LEFT JOIN classes c ON t.class_id = c.id
                    LEFT JOIN sections s ON t.section_id = s.id
                    WHERE t.class_id = ? AND t.section_id = ? 
                    AND t.academic_year_id = ? 
                    AND t.status = 'published'
                    ORDER BY t.effective_date DESC 
                    LIMIT 1";
            
            $timetable = executeQuery($sql, "iii", [$class_id, $section_id, $current_academic_year_id]);
            
            if (empty($timetable)) {
                $this->sendResponse([
                    'success' => true,
                    'student' => $studentData,
                    'timetable' => null,
                    'message' => 'No published timetable found for your class'
                ]);
                return;
            }
            
            $timetableData = $timetable[0];
            $timetable_id = $timetableData['id'];
            
            // Step 3: Get the periods for this timetable
            $sql = "SELECT tp.*, 
                    s.name as subject_name, 
                    s.code as subject_code,
                    t.full_name as teacher_name
                    FROM timetable_periods tp
                    LEFT JOIN subjects s ON tp.subject_id = s.id
                    LEFT JOIN teachers tc ON tp.teacher_id = tc.user_id
                    LEFT JOIN users t ON tc.user_id = t.id
                    WHERE tp.timetable_id = ?
                    ORDER BY FIELD(tp.day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'), 
                    tp.start_time";
            
            $periods = executeQuery($sql, "i", [$timetable_id]);
            
            // Step 4: Combine everything into a comprehensive response
            $timetableData['periods'] = $periods;
            
            $this->sendResponse([
                'success' => true,
                'student' => $studentData,
                'timetable' => $timetableData
            ]);
            
        } catch (Exception $e) {
            $this->sendResponse([
                'success' => false, 
                'error' => 'Failed to fetch student timetable', 
                'details' => $e->getMessage()
            ], 500);
        }
    }
}

// Initialize and process the request
$api = new StudentTimetableApiHandler();
$api->processRequest();
?> 