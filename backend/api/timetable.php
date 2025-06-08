<?php
/**
 * Timetable API Handler
 * Endpoints for timetable management
 */

require_once __DIR__ . '/api_handler.php';

class TimetableApiHandler extends ApiHandler {
    /**
     * Process the API request based on method and path
     */
    public function processRequest() {
        // Check if this is a GET request for a specific timetable
        if ($this->method === 'GET' && !empty($this->pathParams) && count($this->pathParams) === 1) {
            // Skip authentication for viewing timetables (for now)
            // This allows edittimetable.php to work without an active session
        } else {
            // Require authentication for all other timetable operations
            $this->requireAuthentication(['admin', 'teacher', 'student']);
        }
        
        // Debug session information - will help figure out what's happening with the teacher login
        error_log("Timetable API called by user ID: " . ($_SESSION['user_id'] ?? 'not logged in'));
        error_log("User role: " . ($_SESSION['role'] ?? 'no role'));
        error_log("Is teacher role: " . (hasRole('teacher') ? 'yes' : 'no'));

        // Route request based on method and path parameters
        switch ($this->method) {
            case 'GET':
                if (empty($this->pathParams)) {
                    // GET /api/timetables - List timetables
                    $this->listTimetables();
                } else if (count($this->pathParams) === 1) {
                    // GET /api/timetables/{id} - Get specific timetable
                    $this->getTimetable($this->pathParams[0]);
                } else if (count($this->pathParams) === 2 && $this->pathParams[1] === 'download') {
                    // GET /api/timetables/{id}/download - Download timetable
                    $this->downloadTimetable($this->pathParams[0]);
                } else {
                    $this->sendResponse(['error' => 'Invalid endpoint'], 404);
                }
                break;
                
            case 'POST':
                if (empty($this->pathParams)) {
                    // POST /api/timetables - Create new timetable
                    $this->createTimetable();
                } else if (count($this->pathParams) === 2 && $this->pathParams[1] === 'status') {
                    // POST /api/timetables/{id}/status - Change timetable status
                    $this->changeTimetableStatus($this->pathParams[0]);
                } else if (count($this->pathParams) === 1 && $this->pathParams[0] === 'validate') {
                    // POST /api/timetables/validate - Validate timetable data for conflicts
                    $this->validateTimetable();
                } else {
                    $this->sendResponse(['error' => 'Invalid endpoint'], 404);
                }
                break;
                
            case 'PUT':
                if (count($this->pathParams) === 1) {
                    // PUT /api/timetables/{id} - Update timetable
                    $this->updateTimetable($this->pathParams[0]);
                } else {
                    $this->sendResponse(['error' => 'Invalid endpoint'], 404);
                }
                break;
                
            case 'DELETE':
                if (count($this->pathParams) === 1) {
                    // DELETE /api/timetables/{id} - Delete timetable
                    $this->deleteTimetable($this->pathParams[0]);
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
     * List timetables with filtering and pagination
     */
    private function listTimetables() {
        // Check role-specific access
        if (hasRole('admin')) {
            // Admins can see all timetables with filtering
            $this->listTimetablesForAdmin();
        } else if (hasRole('teacher')) {
            // Teachers see only their timetables
            $this->listTimetablesForTeacher();
        } else if (hasRole('student')) {
            // Students see only their class timetables
            $this->listTimetablesForStudent();
        }
    }
    
    /**
     * List timetables for admin with filtering
     */
    private function listTimetablesForAdmin() {
        // Get filter parameters
        $academic_year = isset($this->queryParams['academic_year']) ? (int)$this->queryParams['academic_year'] : null;
        $class_id = isset($this->queryParams['class_id']) ? (int)$this->queryParams['class_id'] : null;
        $section_id = isset($this->queryParams['section_id']) ? (int)$this->queryParams['section_id'] : null;
        $status = isset($this->queryParams['status']) ? $this->queryParams['status'] : null;
        
        // Pagination parameters
        $page = isset($this->queryParams['page']) ? (int)$this->queryParams['page'] : 1;
        $limit = isset($this->queryParams['limit']) ? (int)$this->queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Build SQL query
        $sql = "SELECT t.*, 
                ay.name as academic_year_name, 
                c.name as class_name, 
                s.name as section_name 
                FROM timetables t
                LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections s ON t.section_id = s.id
                WHERE 1=1";
        
        $params = [];
        $types = "";
        
        // Add filters if specified
        if ($academic_year) {
            $sql .= " AND t.academic_year_id = ?";
            $params[] = $academic_year;
            $types .= "i";
        }
        
        if ($class_id) {
            $sql .= " AND t.class_id = ?";
            $params[] = $class_id;
            $types .= "i";
        }
        
        if ($section_id) {
            $sql .= " AND t.section_id = ?";
            $params[] = $section_id;
            $types .= "i";
        }
        
        if ($status) {
            $sql .= " AND t.status = ?";
            $params[] = $status;
            $types .= "s";
        }
        
        // Get total count for pagination
        $countSql = str_replace("SELECT t.*, ay.name as academic_year_name, c.name as class_name, s.name as section_name", "SELECT COUNT(*) as total", $sql);
        $countResult = executeQuery($countSql, $types, $params);
        $total = $countResult[0]['total'] ?? 0;
        
        // Add sorting and pagination
        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        // Execute query
        $timetables = executeQuery($sql, $types, $params);
        
        // Send response with pagination metadata
        $this->sendResponse([
            'data' => $timetables,
            'pagination' => [
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    /**
     * List timetables for teacher
     */
    private function listTimetablesForTeacher() {
        $teacher_id = $_SESSION['user_id'];
        
        // Debug info
        error_log("listTimetablesForTeacher called for teacher_id: {$teacher_id}");
        error_log("Session data: " . json_encode($_SESSION));
        error_log("Has teacher role: " . (hasRole('teacher') ? 'yes' : 'no'));
        
        // Get filter parameters
        $academic_year = isset($this->queryParams['academic_year']) ? (int)$this->queryParams['academic_year'] : null;
        $class_id = isset($this->queryParams['class_id']) ? (int)$this->queryParams['class_id'] : null;
        $subject_id = isset($this->queryParams['subject_id']) ? (int)$this->queryParams['subject_id'] : null;
        
        // Pagination parameters
        $page = isset($this->queryParams['page']) ? (int)$this->queryParams['page'] : 1;
        $limit = isset($this->queryParams['limit']) ? (int)$this->queryParams['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        // Build SQL query to find timetables where this teacher has periods
        // Only include published timetables for the teacher view
        $sql = "SELECT DISTINCT t.*, 
                ay.name as academic_year_name, 
                c.name as class_name, 
                s.name as section_name 
                FROM timetables t
                INNER JOIN timetable_periods tp ON t.id = tp.timetable_id
                LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections s ON t.section_id = s.id
                WHERE tp.teacher_id = ? AND t.status = 'published'";
        
        $params = [$teacher_id];
        $types = "i";
        
        error_log("Initial SQL query: {$sql}");
        error_log("Initial query params: " . json_encode($params));
        
        // Add academic year filter if specified
        if ($academic_year) {
            $sql .= " AND t.academic_year_id = ?";
            $params[] = $academic_year;
            $types .= "i";
            error_log("Added academic year filter: {$academic_year}");
        }
        
        // Add class filter if specified
        if ($class_id) {
            $sql .= " AND t.class_id = ?";
            $params[] = $class_id;
            $types .= "i";
            error_log("Added class filter: {$class_id}");
        }

        // Add subject filter if specified
        if ($subject_id) {
             $sql .= " AND tp.subject_id = ?";
             $params[] = $subject_id;
             $types .= "i";
             error_log("Added subject filter: {$subject_id}");
         }
        
        // Get total count for pagination
        $countSql = str_replace("SELECT DISTINCT t.*, ay.name as academic_year_name, c.name as class_name, s.name as section_name", "SELECT COUNT(DISTINCT t.id) as total", $sql);
        
        // Need to handle the case where subject filter is applied, as DISTINCT t.id might be less than rows in tp
        // Recalculate countSql if subject filter is active
        if ($subject_id) {
             $countSql = "SELECT COUNT(DISTINCT t.id) as total FROM timetables t INNER JOIN timetable_periods tp ON t.id = tp.timetable_id WHERE tp.teacher_id = ? AND t.status = 'published' AND tp.subject_id = ?";
             $countParams = [$teacher_id, $subject_id];
             $countTypes = "ii";
             // Re-add academic year and class filters to count query if present
             if ($academic_year) { $countSql .= " AND t.academic_year_id = ?"; $countParams[] = $academic_year; $countTypes .= "i"; }
             if ($class_id) { $countSql .= " AND t.class_id = ?"; $countParams[] = $class_id; $countTypes .= "i"; }
             $countResult = executeQuery($countSql, $countTypes, $countParams);
        } else {
             // Use original countSql and params if no subject filter
             $countResult = executeQuery($countSql, $types, $params);
        }
        
        $total = $countResult[0]['total'] ?? 0;
        
        error_log("Total timetables found for teacher with filters: {$total}");
        
        // Add sorting and pagination
        $sql .= " ORDER BY t.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        error_log("Final SQL with pagination and filters: {$sql}");
        error_log("Final params: " . json_encode($params));
        
        // Execute query
        $timetables = executeQuery($sql, $types, $params);
        
        // Log what we found
        error_log("Timetables found: " . json_encode($timetables));
        
        // Send response with pagination metadata
        $this->sendResponse([
            'data' => $timetables,
            'pagination' => [
                'total' => (int)$total,
                'page' => $page,
                'limit' => $limit,
                'pages' => ceil($total / $limit)
            ]
        ]);
    }
    
    /**
     * List timetables for student
     */
    private function listTimetablesForStudent() {
        $student_id = $_SESSION['user_id'];
        
        // First, get student's class and section
        $sql = "SELECT class_id, section_id FROM students WHERE user_id = ?";
        $student = executeQuery($sql, "i", [$student_id]);
        
        if (empty($student)) {
            $this->sendResponse(['error' => 'Student information not found'], 404);
        }
        
        $class_id = $student[0]['class_id'];
        $section_id = $student[0]['section_id'];
        
        // Get current academic year if not specified
        $academic_year = isset($this->queryParams['academic_year']) ? (int)$this->queryParams['academic_year'] : null;
        if (!$academic_year) {
            $currentYearSql = "SELECT id FROM academic_years WHERE is_current = 1 LIMIT 1";
            $currentYear = executeQuery($currentYearSql);
            $academic_year = $currentYear[0]['id'] ?? null;
        }
        
        // Build query to get published timetables for student's class and section
        $sql = "SELECT t.*, 
                ay.name as academic_year_name, 
                c.name as class_name, 
                s.name as section_name 
                FROM timetables t
                LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections s ON t.section_id = s.id
                WHERE t.class_id = ? AND t.section_id = ? AND t.status = 'published'";
        
        $params = [$class_id, $section_id];
        $types = "ii";
        
        // Add academic year filter if available
        if ($academic_year) {
            $sql .= " AND t.academic_year_id = ?";
            $params[] = $academic_year;
            $types .= "i";
        }
        
        // Add sorting
        $sql .= " ORDER BY t.effective_date DESC";
        
        // Execute query
        $timetables = executeQuery($sql, $types, $params);
        
        // Send response (no pagination needed as there will be few timetables per student)
        $this->sendResponse(['data' => $timetables]);
    }
    
    /**
     * Get specific timetable with periods
     */
    private function getTimetable($id) {
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid timetable ID'], 400);
        }
        
        // Fetch timetable details
        $sql = "SELECT t.*, 
                ay.name as academic_year_name, 
                c.name as class_name, 
                s.name as section_name 
                FROM timetables t
                LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections s ON t.section_id = s.id
                WHERE t.id = ?";
        
        $timetable = executeQuery($sql, "i", [$id]);
        
        if (empty($timetable)) {
            $this->sendResponse(['error' => 'Timetable not found'], 404);
        }
        
        // Check access permission based on role
        if (hasRole('student')) {
            // Students can only view their class timetables if published
            $student_id = $_SESSION['user_id'];
            $sql = "SELECT class_id, section_id FROM students WHERE user_id = ?";
            $student = executeQuery($sql, "i", [$student_id]);
            
            if (empty($student) || 
                $student[0]['class_id'] != $timetable[0]['class_id'] || 
                $student[0]['section_id'] != $timetable[0]['section_id'] ||
                $timetable[0]['status'] != 'published') {
                $this->sendResponse(['error' => 'Unauthorized'], 403);
            }
        } else if (hasRole('teacher') && $timetable[0]['status'] != 'published') {
            // Teachers can only view published timetables (unless they're in them)
            $teacher_id = $_SESSION['user_id'];
            $sql = "SELECT COUNT(*) as count FROM timetable_periods WHERE timetable_id = ? AND teacher_id = ?";
            $teacherInTimetable = executeQuery($sql, "ii", [$id, $teacher_id]);
            
            if (empty($teacherInTimetable) || $teacherInTimetable[0]['count'] == 0) {
                $this->sendResponse(['error' => 'Unauthorized'], 403);
            }
        }
        
        // Fetch periods for this timetable
        $sql = "SELECT tp.*, 
                s.name as subject_name, 
                t.full_name as teacher_name
                FROM timetable_periods tp
                LEFT JOIN subjects s ON tp.subject_id = s.id
                LEFT JOIN teachers tc ON tp.teacher_id = tc.user_id
                LEFT JOIN users t ON tc.user_id = t.id
                WHERE tp.timetable_id = ?
                ORDER BY tp.day_of_week, tp.start_time";
        
        $periods = executeQuery($sql, "i", [$id]);
        
        // Combine timetable and periods
        $result = $timetable[0];
        $result['periods'] = $periods;
        
        $this->sendResponse(['data' => $result]);
    }
    
    /**
     * Create new timetable
     */
    private function createTimetable() {
        // Only admins can create timetables
        $this->requireAuthentication(['admin']);
        
        // Validate required fields
        $requiredFields = ['academic_year_id', 'class_id', 'section_id', 'effective_date', 'status'];
        if (!$this->validateRequiredFields($requiredFields)) {
            $this->sendResponse(['error' => 'Missing required fields'], 400);
        }
        
        // Validate periods array if present
        if (!isset($this->data['periods']) || !is_array($this->data['periods'])) {
            $this->sendResponse(['error' => 'Periods array is required'], 400);
        }
        
        // Begin transaction
        $conn = getDbConnection();
        $conn->begin_transaction();
        
        try {
            // Insert timetable
            $sql = "INSERT INTO timetables (
                    academic_year_id, class_id, section_id, 
                    effective_date, description, status, 
                    created_by, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $params = [
                (int)$this->data['academic_year_id'],
                (int)$this->data['class_id'],
                (int)$this->data['section_id'],
                $this->data['effective_date'],
                $this->data['description'] ?? '',
                $this->data['status'],
                $_SESSION['user_id']
            ];
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiisssi", ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create timetable: " . $stmt->error);
            }
            
            $timetableId = $stmt->insert_id;
            $stmt->close();
            
            // Insert periods
            foreach ($this->data['periods'] as $period) {
                // Validate required fields for each period
                if (!isset($period['day_of_week'], $period['period_number'], $period['start_time'], $period['end_time'], $period['subject_id'], $period['teacher_id'])) {
                    throw new Exception("Missing required fields for period");
                }
                
                // Validate day_of_week is a valid enum value
                $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $day = strtolower(trim($period['day_of_week']));
                if (!in_array($day, $validDays)) {
                    throw new Exception("Invalid day_of_week value: {$period['day_of_week']}. Must be one of: " . implode(', ', $validDays));
                }
                
                // Validate period_number is in valid range (1-8)
                $periodNumber = (int)$period['period_number'];
                if ($periodNumber < 1 || $periodNumber > 8) {
                    throw new Exception("Invalid period_number: {$periodNumber}. Must be between 1 and 8.");
                }
                
                $sql = "INSERT INTO timetable_periods (
                        timetable_id, day_of_week, period_number, period_label, start_time, 
                        end_time, subject_id, teacher_id, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $periodParams = [
                    $timetableId,
                    $day, // Use validated day string, not casting to int
                    $periodNumber, // Use validated period number
                    $period['period_label'] ?? '',
                    $period['start_time'],
                    $period['end_time'],
                    (int)$period['subject_id'],
                    (int)$period['teacher_id'],
                    $period['notes'] ?? null
                ];
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isiissiis", ...$periodParams);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create period: " . $stmt->error);
                }
                
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Log audit
            logAudit('timetables', $timetableId, 'INSERT');
            
            // Return success with new timetable ID
            $this->sendResponse(['success' => true, 'id' => $timetableId], 201);
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $this->sendResponse(['error' => $e->getMessage()], 400);
        }
        
        $conn->close();
    }
    
    /**
     * Update existing timetable
     */
    private function updateTimetable($id) {
        // Only admins can update timetables
        $this->requireAuthentication(['admin']);
        
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid timetable ID'], 400);
        }
        
        // Check if timetable exists
        $sql = "SELECT * FROM timetables WHERE id = ?";
        $timetable = executeQuery($sql, "i", [$id]);
        
        if (empty($timetable)) {
            $this->sendResponse(['error' => 'Timetable not found'], 404);
        }
        
        // Validate required fields
        $requiredFields = ['academic_year_id', 'class_id', 'section_id', 'effective_date', 'status'];
        if (!$this->validateRequiredFields($requiredFields)) {
            $this->sendResponse(['error' => 'Missing required fields'], 400);
        }
        
        // Validate periods array if present
        if (!isset($this->data['periods']) || !is_array($this->data['periods'])) {
            $this->sendResponse(['error' => 'Periods array is required'], 400);
        }
        
        // Begin transaction
        $conn = getDbConnection();
        $conn->begin_transaction();
        
        try {
            // Update timetable
            $sql = "UPDATE timetables SET 
                    academic_year_id = ?, 
                    class_id = ?, 
                    section_id = ?, 
                    effective_date = ?, 
                    description = ?, 
                    status = ?, 
                    updated_by = ?, 
                    updated_at = NOW() 
                    WHERE id = ?";
            
            $params = [
                (int)$this->data['academic_year_id'],
                (int)$this->data['class_id'],
                (int)$this->data['section_id'],
                $this->data['effective_date'],
                $this->data['description'] ?? '',
                $this->data['status'],
                $_SESSION['user_id'],
                $id
            ];
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiissisi", ...$params);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update timetable: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Delete existing periods
            $sql = "DELETE FROM timetable_periods WHERE timetable_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete existing periods: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Insert new periods
            foreach ($this->data['periods'] as $period) {
                // Validate required fields for each period
                if (!isset($period['day_of_week'], $period['period_number'], $period['start_time'], $period['end_time'], $period['subject_id'], $period['teacher_id'])) {
                    throw new Exception("Missing required fields for period");
                }
                
                // Validate day_of_week is a valid enum value
                $validDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                $day = strtolower(trim($period['day_of_week']));
                if (!in_array($day, $validDays)) {
                    throw new Exception("Invalid day_of_week value: {$period['day_of_week']}. Must be one of: " . implode(', ', $validDays));
                }
                
                // Validate period_number is in valid range (1-8)
                $periodNumber = (int)$period['period_number'];
                if ($periodNumber < 1 || $periodNumber > 8) {
                    throw new Exception("Invalid period_number: {$periodNumber}. Must be between 1 and 8.");
                }
                
                $sql = "INSERT INTO timetable_periods (
                        timetable_id, day_of_week, period_number, period_label, start_time, 
                        end_time, subject_id, teacher_id, notes
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $periodParams = [
                    $id,
                    $day, // Use validated day string, not casting to int
                    $periodNumber, // Use validated period number
                    $period['period_label'] ?? '',
                    $period['start_time'],
                    $period['end_time'],
                    (int)$period['subject_id'],
                    (int)$period['teacher_id'],
                    $period['notes'] ?? null
                ];
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isiissiis", ...$periodParams);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to create period: " . $stmt->error);
                }
                
                $stmt->close();
            }
            
            // Commit transaction
            $conn->commit();
            
            // Log audit
            logAudit('timetables', $id, 'UPDATE');
            
            // Return success
            $this->sendResponse(['success' => true, 'id' => $id]);
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $this->sendResponse(['error' => $e->getMessage()], 400);
        }
        
        $conn->close();
    }
    
    /**
     * Delete timetable
     */
    private function deleteTimetable($id) {
        // Only admins can delete timetables
        $this->requireAuthentication(['admin']);
        
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid timetable ID'], 400);
        }
        
        // Check if timetable exists
        $sql = "SELECT * FROM timetables WHERE id = ?";
        $timetable = executeQuery($sql, "i", [$id]);
        
        if (empty($timetable)) {
            $this->sendResponse(['error' => 'Timetable not found'], 404);
        }
        
        // Begin transaction
        $conn = getDbConnection();
        $conn->begin_transaction();
        
        try {
            // Delete periods first
            $sql = "DELETE FROM timetable_periods WHERE timetable_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete periods: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Delete timetable
            $sql = "DELETE FROM timetables WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to delete timetable: " . $stmt->error);
            }
            
            $stmt->close();
            
            // Commit transaction
            $conn->commit();
            
            // Log audit
            logAudit('timetables', $id, 'DELETE');
            
            // Return success
            $this->sendResponse(['success' => true]);
            
        } catch (Exception $e) {
            // Rollback on error
            $conn->rollback();
            $this->sendResponse(['error' => $e->getMessage()], 400);
        }
        
        $conn->close();
    }
    
    /**
     * Change timetable status (publish, archive, draft)
     */
    private function changeTimetableStatus($id) {
        // Only admins can change timetable status
        $this->requireAuthentication(['admin']);
        
        // Validate ID
        $id = (int)$id;
        if ($id <= 0) {
            $this->sendResponse(['error' => 'Invalid timetable ID'], 400);
        }
        
        // Validate status
        if (!isset($this->data['status']) || !in_array($this->data['status'], ['draft', 'published', 'archived'])) {
            $this->sendResponse(['error' => 'Invalid status. Must be draft, published, or archived'], 400);
        }
        
        // Check if timetable exists
        $sql = "SELECT * FROM timetables WHERE id = ?";
        $timetable = executeQuery($sql, "i", [$id]);
        
        if (empty($timetable)) {
            $this->sendResponse(['error' => 'Timetable not found'], 404);
        }
        
        // Update status
        $sql = "UPDATE timetables SET status = ?, updated_by = ?, updated_at = NOW() WHERE id = ?";
        $result = executeQuery($sql, "sii", [$this->data['status'], $_SESSION['user_id'], $id]);
        
        if ($result === false) {
            $this->sendResponse(['error' => 'Failed to update status'], 500);
        }
        
        // Log audit
        logAudit('timetables', $id, 'UPDATE_STATUS');
        
        // Return success
        $this->sendResponse(['success' => true]);
    }
    
    /**
     * Validate timetable data for conflicts
     */
    private function validateTimetable() {
        // Require admin authentication
        $this->requireAuthentication(['admin']);
        
        // Validate periods array
        if (!isset($this->data['periods']) || !is_array($this->data['periods'])) {
            $this->sendResponse(['error' => 'Periods array is required'], 400);
        }
        
        // Validate required context
        if (!isset($this->data['timetable_id'], $this->data['class_id'], $this->data['section_id'])) {
            $this->sendResponse(['error' => 'Missing context information (timetable_id, class_id, section_id)'], 400);
        }
        
        $timetableId = (int)$this->data['timetable_id'];
        $classId = (int)$this->data['class_id'];
        $sectionId = (int)$this->data['section_id'];
        
        // Find conflicts
        $conflicts = [];
        
        // Check for teacher conflicts (teacher assigned to multiple classes at the same time)
        $teacherConflicts = $this->findTeacherConflicts($timetableId, $this->data['periods']);
        if (!empty($teacherConflicts)) {
            $conflicts['teacher_conflicts'] = $teacherConflicts;
        }
        
        // Check for class conflicts (multiple subjects for same class at the same time)
        $classConflicts = $this->findClassConflicts($timetableId, $classId, $sectionId, $this->data['periods']);
        if (!empty($classConflicts)) {
            $conflicts['class_conflicts'] = $classConflicts;
        }
        
        // Return conflicts or success
        if (empty($conflicts)) {
            $this->sendResponse(['valid' => true]);
        } else {
            $this->sendResponse(['valid' => false, 'conflicts' => $conflicts]);
        }
    }
    
    /**
     * Find teacher conflicts in timetable data
     */
    private function findTeacherConflicts($timetableId, $periods) {
        $conflicts = [];
        $teacherSchedule = [];
        
        // Build teacher schedule from periods
        foreach ($periods as $idx => $period) {
            $teacherId = (int)$period['teacher_id'];
            $day = (int)$period['day_of_week'];
            $start = strtotime($period['start_time']);
            $end = strtotime($period['end_time']);
            
            // Skip if missing required data
            if (!$teacherId || !$day || !$start || !$end) {
                continue;
            }
            
            // Initialize teacher schedule array if not exists
            if (!isset($teacherSchedule[$teacherId])) {
                $teacherSchedule[$teacherId] = [];
            }
            
            // Initialize day array if not exists
            if (!isset($teacherSchedule[$teacherId][$day])) {
                $teacherSchedule[$teacherId][$day] = [];
            }
            
            // Check for conflicts with existing periods for this teacher
            foreach ($teacherSchedule[$teacherId][$day] as $existingIdx => $existing) {
                // Check for time overlap
                if (max($start, $existing['start']) < min($end, $existing['end'])) {
                    // Add conflict
                    $conflicts[] = [
                        'type' => 'teacher',
                        'teacher_id' => $teacherId,
                        'day' => $day,
                        'periods' => [$idx, $existingIdx],
                        'message' => "Teacher has conflicting periods on day $day"
                    ];
                }
            }
            
            // Add period to teacher schedule
            $teacherSchedule[$teacherId][$day][] = [
                'index' => $idx,
                'start' => $start,
                'end' => $end
            ];
        }
        
        // Check for conflicts with existing timetables
        if ($timetableId > 0) {
            // For each teacher in the new schedule
            foreach ($teacherSchedule as $teacherId => $days) {
                // Get teacher's existing periods from other active timetables
                $sql = "SELECT tp.*, t.class_id, t.section_id, s.name as subject_name
                        FROM timetable_periods tp
                        JOIN timetables t ON tp.timetable_id = t.id
                        LEFT JOIN subjects s ON tp.subject_id = s.id
                        WHERE tp.teacher_id = ? AND t.id != ? AND t.status = 'published'";
                
                $existingPeriods = executeQuery($sql, "ii", [$teacherId, $timetableId]);
                
                foreach ($existingPeriods as $existing) {
                    $existingDay = (int)$existing['day_of_week'];
                    $existingStart = strtotime($existing['start_time']);
                    $existingEnd = strtotime($existing['end_time']);
                    
                    // Check conflicts with new periods for this day
                    if (isset($teacherSchedule[$teacherId][$existingDay])) {
                        foreach ($teacherSchedule[$teacherId][$existingDay] as $newPeriod) {
                            if (max($newPeriod['start'], $existingStart) < min($newPeriod['end'], $existingEnd)) {
                                // Add external conflict
                                $conflicts[] = [
                                    'type' => 'teacher_external',
                                    'teacher_id' => $teacherId,
                                    'day' => $existingDay,
                                    'period_index' => $newPeriod['index'],
                                    'existing_timetable' => [
                                        'id' => $existing['timetable_id'],
                                        'class_id' => $existing['class_id'],
                                        'section_id' => $existing['section_id'],
                                        'subject' => $existing['subject_name'],
                                        'time' => $existing['start_time'] . ' - ' . $existing['end_time']
                                    ],
                                    'message' => "Teacher already has a class at this time in another timetable"
                                ];
                            }
                        }
                    }
                }
            }
        }
        
        return $conflicts;
    }
    
    /**
     * Find class conflicts in timetable data
     */
    private function findClassConflicts($timetableId, $classId, $sectionId, $periods) {
        $conflicts = [];
        $classSchedule = [];
        
        // Build class schedule from periods
        foreach ($periods as $idx => $period) {
            $day = (int)$period['day_of_week'];
            $start = strtotime($period['start_time']);
            $end = strtotime($period['end_time']);
            
            // Skip if missing required data
            if (!$day || !$start || !$end) {
                continue;
            }
            
            // Initialize day array if not exists
            if (!isset($classSchedule[$day])) {
                $classSchedule[$day] = [];
            }
            
            // Check for conflicts with existing periods for this class/section
            foreach ($classSchedule[$day] as $existingIdx => $existing) {
                // Check for time overlap
                if (max($start, $existing['start']) < min($end, $existing['end'])) {
                    // Add conflict
                    $conflicts[] = [
                        'type' => 'class',
                        'day' => $day,
                        'periods' => [$idx, $existingIdx],
                        'message' => "Class has conflicting periods on day $day"
                    ];
                }
            }
            
            // Add period to class schedule
            $classSchedule[$day][] = [
                'index' => $idx,
                'start' => $start,
                'end' => $end
            ];
        }
        
        return $conflicts;
    }

    /**
     * Download a specific timetable as a PDF generated from HTML.
     * Requires an external HTML-to-PDF tool (like wkhtmltopdf) on the server.
     */
    private function downloadTimetable($id) {
        // Fetch timetable data and periods
        $sql = "SELECT t.*, 
                ay.name as academic_year_name, 
                c.name as class_name, 
                s.name as section_name 
                FROM timetables t
                LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections s ON t.section_id = s.id
                WHERE t.id = ? LIMIT 1";
        
        $timetable = executeQuery($sql, "i", [$id]);
        
        if (empty($timetable)) {
            $this->sendResponse(['error' => 'Timetable not found'], 404);
            return;
        }
        
        $timetable = $timetable[0];
        
        // Fetch periods for the timetable, ordered for the table structure
        $periodsSql = "SELECT tp.*, 
                       sub.name as subject_name, 
                       t.full_name as teacher_name 
                       FROM timetable_periods tp
                       LEFT JOIN subjects sub ON tp.subject_id = sub.id
                       LEFT JOIN teachers tch ON tp.teacher_id = tch.user_id
                       LEFT JOIN users t ON tch.user_id = t.id
                       WHERE tp.timetable_id = ? ORDER BY tp.start_time, tp.day_of_week";
                       
        $periods = executeQuery($periodsSql, "i", [$id]);
        
        // Organize periods by start time and then by day for the grid structure
        $timeSlots = [
            '08:00:00' => ['label' => '8:00 - 8:45<br>Period 1', 'days' => []],
            '08:50:00' => ['label' => '8:50 - 9:35<br>Period 2', 'days' => []],
            '09:40:00' => ['label' => '9:40 - 10:25<br>Period 3', 'days' => []],
            '10:25:00' => ['label' => '10:25 - 10:40<br>Break', 'isBreak' => true],
            '10:40:00' => ['label' => '10:40 - 11:25<br>Period 4', 'days' => []],
            '11:30:00' => ['label' => '11:30 - 12:15<br>Period 5', 'days' => []],
            '12:20:00' => ['label' => '12:20 - 1:05<br>Period 6', 'days' => []],
            '13:05:00' => ['label' => '1:05 - 1:45<br>Lunch', 'isBreak' => true],
            '13:45:00' => ['label' => '1:45 - 2:30<br>Period 7', 'days' => []],
            '14:35:00' => ['label' => '2:35 - 3:20<br>Period 8', 'days' => []],
        ];

        $daysOrder = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        // Populate time slots with periods
        foreach ($periods as $period) {
            $day = strtolower($period['day_of_week']);
            $startTime = $period['start_time'];
            
            // Find the correct time slot
            $targetSlotTime = null;
            foreach ($timeSlots as $slotTime => $slotData) {
                if ($startTime === $slotTime) {
                    $targetSlotTime = $slotTime;
                    break;
                }
            }

            if ($targetSlotTime && !isset($timeSlots[$targetSlotTime]['isBreak'])) {
                 // Store period data, ensuring day key exists
                 $timeSlots[$targetSlotTime]['days'][$day] = $period;
            }
        }

        // Fill in missing days with null for consistent grid structure
        foreach ($timeSlots as &$slotData) {
            if (!isset($slotData['isBreak'])) {
                foreach ($daysOrder as $day) {
                    if (!isset($slotData['days'][$day])) {
                        $slotData['days'][$day] = null; // Mark as empty
                    }
                }
                 // Sort days within the slot by the defined order
                 $sortedDays = [];
                 foreach($daysOrder as $day) {
                      $sortedDays[$day] = $slotData['days'][$day];
                 }
                 $slotData['days'] = $sortedDays;
            }
        }
        unset($slotData); // Unset the reference

        // --- Generate HTML Content ---
        
        $htmlContent = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Preview - ' . htmlspecialchars($timetable['class_name'] . '-' . $timetable['section_name']) . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .school-name { font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .timetable-title { font-size: 18px; margin: 10px 0; }
        .timetable-info-section { margin-bottom: 20px; text-align: left; }
        .timetable-info-section p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; table-layout: fixed; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; word-wrap: break-word; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .time-slot { background-color: #f9fafb; font-weight: bold; width: 15%; /* Adjust width as needed */}
        .break-cell { background-color: #f3f4f6; font-weight: bold; text-align: center; }
        .period-content { font-size: 12px; }
        .subject-name { display: block; font-weight: bold; }
        .teacher-name { display: block; font-size: 10px; color: #555; margin-top: 3px; }
        .no-class { color: #999; font-style: italic; }
        /* Optional: Print-specific styles */
        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">Vinodh English School</div> <!-- Replace with actual school name -->
        <div class="timetable-title">Timetable for Class ' . htmlspecialchars($timetable['class_name']) . ' - ' . htmlspecialchars($timetable['section_name']) . '</div>
    </div>
    
    <div class="timetable-info-section">
        <p><strong>Academic Year:</strong> ' . htmlspecialchars($timetable['academic_year_name']) . '</p>
        <p><strong>Effective From:</strong> ' . htmlspecialchars($timetable['effective_date']) . '</p>
        ' . (!empty($timetable['description']) ? '<p><strong>Description:</strong> ' . htmlspecialchars($timetable['description']) . '</p>' : '') . '
    </div>

    <table>
        <thead>
            <tr>
                <th class="time-slot">Time/Day</th>
                <th>Monday</th>
                <th>Tuesday</th>
                <th>Wednesday</th>
                <th>Thursday</th>
                <th>Friday</th>
                <th>Saturday</th>
            </tr>
        </thead>
        <tbody>
';

        foreach ($timeSlots as $slotTime => $slotData) {
            $htmlContent .= '<tr>';
            $htmlContent .= '<td class="time-slot">' . $slotData['label'] . '</td>';

            if (isset($slotData['isBreak'])) {
                // Break row spans all days
                $htmlContent .= '<td class="break-cell" colspan="' . count($daysOrder) . '">' . str_replace('<br>', ' ', $slotData['label']) . '</td>';
            } else {
                // Regular period row
                foreach ($daysOrder as $day) {
                    $period = $slotData['days'][$day];
                    if ($period) {
                        $htmlContent .= '
                            <td>
                                <div class="period-content">
                                    <span class="subject-name">' . htmlspecialchars($period['subject_name'] ?? '') . '</span>
                                    <span class="teacher-name">' . htmlspecialchars($period['teacher_name'] ?? '') . '</span>
                                </div>
                            </td>
                        ';
                    } else {
                        $htmlContent .= '<td class="no-class">No Class</td>';
                    }
                }
            }
            $htmlContent .= '</tr>';
        }

        $htmlContent .= '
        </tbody>
    </table>

</body>
</html>
';
        
        // --- HTML-to-PDF Conversion (Requires an external tool/library) ---
        // You would typically use a library or tool here to convert $htmlContent to PDF.
        // Example using wkhtmltopdf via exec (ensure wkhtmltopdf is installed and in PATH):
        /*
        $filename = "timetable_class_{$timetable['class_name']}_section_{$timetable['section_name']}.pdf";
        $tempHtmlFile = tempnam(sys_get_temp_dir(), 'timetable_html') . '.html';
        $tempPdfFile = tempnam(sys_get_temp_dir(), 'timetable_pdf') . '.pdf';

        file_put_contents($tempHtmlFile, $htmlContent);

        // Command to convert HTML to PDF - adjust path to wkhtmltopdf if necessary
        $command = "wkhtmltopdf \"{$tempHtmlFile}\" \"{$tempPdfFile}\"";
        
        // Execute the command
        $output = [];
        $return_var = 0;
        exec($command, $output, $return_var);

        // Check if conversion was successful
        if ($return_var === 0 && file_exists($tempPdfFile)) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tempPdfFile));
            readfile($tempPdfFile);
            
            // Clean up temporary files
            unlink($tempHtmlFile);
            unlink($tempPdfFile);
            exit; // Stop further execution
        } else {
            // If conversion failed
            if (file_exists($tempHtmlFile)) unlink($tempHtmlFile);
            if (file_exists($tempPdfFile)) unlink($tempPdfFile);
            error_log("wkhtmltopdf command failed: " . implode("\n", $output));
            $this->sendResponse(['error' => 'Failed to generate PDF. Check server logs.'], 500);
        }
        */
        
        // --- Fallback: Output HTML for debugging/inspection --- 
        // If you don't have an HTML-to-PDF tool, you can output the HTML directly
        // for debugging the generated structure. Comment this out once PDF conversion is working.
        header('Content-Type: text/html');
        echo $htmlContent;
        exit; // Stop further execution

        // $this->sendResponse(['error' => 'PDF generation from HTML not fully implemented. Integrate an HTML-to-PDF tool.'], 500);
        // exit; // Ensure script stops after outputting
    }
}

// Initialize and process the request
$api = new TimetableApiHandler();
$api->processRequest(); 