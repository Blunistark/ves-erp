<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'con.php';
header('Content-Type: application/json');

// Log errors to file
function logError($message) {
    $logFile = __DIR__ . '/student_transfer_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message" . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Get the next class based on current class
function getNextClass($conn, $currentClassId) {
    // First, get the current class name
    $stmt = $conn->prepare("SELECT name FROM classes WHERE id = ?");
    $stmt->bind_param("i", $currentClassId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        logError("Class ID $currentClassId not found");
        return null;
    }
    
    $currentClass = $result->fetch_assoc();
    $currentClassName = $currentClass['name'];
    
    // Try different patterns to extract a number from the class name
    
    // Pattern 1: Extract numbers (like "Class 5" -> "Class 6")
    if (preg_match('/(\d+)/', $currentClassName, $matches)) {
        $currentNumber = (int) $matches[0];
        $nextNumber = $currentNumber + 1;
        
        // Replace the number in the original string to maintain format
        $nextClassName = preg_replace('/\d+/', $nextNumber, $currentClassName, 1);
        
        $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
        $stmt->bind_param("s", $nextClassName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $nextClass = $result->fetch_assoc();
            return $nextClass['id'];
        }
    }
    
    // Pattern 2: Roman numerals (like "Grade VI" -> "Grade VII")
    $romanNumerals = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 
                      'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10,
                      'XI' => 11, 'XII' => 12];
    
    foreach ($romanNumerals as $roman => $arabic) {
        if (stripos($currentClassName, $roman) !== false) {
            // Found a roman numeral
            $currentNumber = $arabic;
            $nextNumber = $currentNumber + 1;
            
            // If next number has a roman equivalent, replace it
            if (array_search($nextNumber, $romanNumerals) !== false) {
                $nextRoman = array_search($nextNumber, $romanNumerals);
                $nextClassName = str_ireplace($roman, $nextRoman, $currentClassName);
                
                $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
                $stmt->bind_param("s", $nextClassName);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $nextClass = $result->fetch_assoc();
                    return $nextClass['id'];
                }
            }
        }
    }
    
    // Pattern 3: Ordinal suffixes (like "1st Grade" -> "2nd Grade")
    $ordinals = ['1st' => 1, '2nd' => 2, '3rd' => 3, '4th' => 4, '5th' => 5,
                '6th' => 6, '7th' => 7, '8th' => 8, '9th' => 9, '10th' => 10,
                '11th' => 11, '12th' => 12];
    
    foreach ($ordinals as $ordinal => $number) {
        if (stripos($currentClassName, $ordinal) !== false) {
            // Found an ordinal
            $currentNumber = $number;
            $nextNumber = $currentNumber + 1;
            
            // Get the corresponding ordinal suffix
            $nextOrdinal = $nextNumber;
            if ($nextNumber == 1) $nextOrdinal .= 'st';
            else if ($nextNumber == 2) $nextOrdinal .= 'nd';
            else if ($nextNumber == 3) $nextOrdinal .= 'rd';
            else $nextOrdinal .= 'th';
            
            $nextClassName = str_ireplace($ordinal, $nextOrdinal, $currentClassName);
            
            $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
            $stmt->bind_param("s", $nextClassName);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $nextClass = $result->fetch_assoc();
                return $nextClass['id'];
            }
        }
    }
    
    // If all patterns fail, try to find a class with a higher ID
    // This is a fallback and assumes class IDs are ordered by grade level
    $stmt = $conn->prepare("SELECT id FROM classes WHERE id > ? ORDER BY id ASC LIMIT 1");
    $stmt->bind_param("i", $currentClassId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $nextClass = $result->fetch_assoc();
        return $nextClass['id'];
    }
    
    // If we can't find a next class, log and return null
    logError("Could not determine next class for: $currentClassName (ID: $currentClassId)");
    return null;
}

// Create a student transfer record
function createTransferRecord($conn, $studentId, $fromClassId, $fromSectionId, $toClassId, $toSectionId, $effectiveDate, $reason) {
    $stmt = $conn->prepare("INSERT INTO student_transfers 
        (student_id, from_class_id, from_section_id, to_class_id, to_section_id, transfer_date, reason, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    
    $stmt->bind_param("iiiiiss", $studentId, $fromClassId, $fromSectionId, $toClassId, $toSectionId, $effectiveDate, $reason);
    $result = $stmt->execute();
    
    if (!$result) {
        logError("Failed to create transfer record for student ID $studentId: " . $stmt->error);
    }
    
    return $result;
}

// Update student's class and section
function updateStudentClass($conn, $studentId, $classId, $sectionId) {
    $stmt = $conn->prepare("UPDATE students SET class_id = ?, section_id = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("iii", $classId, $sectionId, $studentId);
    $result = $stmt->execute();
    
    if (!$result) {
        logError("Failed to update class for student ID $studentId: " . $stmt->error);
    }
    
    return $result;
}

// Fetch transfer records for the history page
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_transfers') {
    // Initialize parameters
    $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
    $limit = 10; // Records per page
    $offset = ($page - 1) * $limit;
    
    // Build query based on filters
    $query = "SELECT st.*, 
              s.full_name as student_name, s.admission_number,
              fc.name as from_class_name, fs.name as from_section_name,
              tc.name as to_class_name, ts.name as to_section_name
              FROM student_transfers st
              JOIN students s ON st.student_id = s.user_id
              JOIN classes fc ON st.from_class_id = fc.id
              JOIN sections fs ON st.from_section_id = fs.id
              JOIN classes tc ON st.to_class_id = tc.id
              JOIN sections ts ON st.to_section_id = ts.id
              WHERE 1=1";
    
    $countQuery = "SELECT COUNT(*) as total FROM student_transfers st
                  JOIN students s ON st.student_id = s.user_id
                  WHERE 1=1";
                  
    $params = [];
    $types = "";
    
    // Apply filters
    if (isset($_GET['student']) && !empty($_GET['student'])) {
        $searchTerm = "%" . $_GET['student'] . "%";
        $query .= " AND (s.full_name LIKE ? OR s.admission_number LIKE ?)";
        $countQuery .= " AND (s.full_name LIKE ? OR s.admission_number LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    if (isset($_GET['class']) && !empty($_GET['class'])) {
        $classId = (int) $_GET['class'];
        $query .= " AND (st.from_class_id = ? OR st.to_class_id = ?)";
        $countQuery .= " AND (st.from_class_id = ? OR st.to_class_id = ?)";
        $params[] = $classId;
        $params[] = $classId;
        $types .= "ii";
    }
    
    if (isset($_GET['reason']) && !empty($_GET['reason'])) {
        $reason = $_GET['reason'];
        $query .= " AND st.reason = ?";
        $countQuery .= " AND st.reason = ?";
        $params[] = $reason;
        $types .= "s";
    }
    
    // Add sorting and pagination
    $query .= " ORDER BY st.transfer_date DESC, st.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";
    
    // Execute count query to get total records
    $countStmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        // Remove limit and offset parameters for count query
        $countParams = array_slice($params, 0, -2);
        $countTypes = substr($types, 0, -2);
        $countStmt->bind_param($countTypes, ...$countParams);
    }
    
    $totalRecords = 0;
    $totalPages = 0;
    
    if ($countStmt->execute()) {
        $countResult = $countStmt->get_result();
        if ($row = $countResult->fetch_assoc()) {
            $totalRecords = $row['total'];
            $totalPages = ceil($totalRecords / $limit);
        }
    } else {
        logError("Failed to count transfer records: " . $countStmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to count records']);
        exit;
    }
    
    // Execute main query to get records
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $records = [];
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $records[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'records' => $records,
            'total_records' => $totalRecords,
            'total_pages' => $totalPages,
            'current_page' => $page
        ]);
    } else {
        logError("Failed to fetch transfer records: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch records: ' . $stmt->error]);
    }
    
    exit;
}

// Fetch students for transfer
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'fetch_students') {
    $academicYearId = isset($_GET['academic_year']) ? (int) $_GET['academic_year'] : 0;
    $classId = isset($_GET['class']) ? (int) $_GET['class'] : 0;
    $sectionId = isset($_GET['section']) ? (int) $_GET['section'] : 0;
    
    if (!$academicYearId || !$classId) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }
    
    $students = [];
    
    // Build query based on whether section is specified
    $query = "SELECT s.user_id, s.admission_number, s.full_name, s.roll_number, 
               s.gender_code, s.class_id, s.section_id, c.name as class_name, 
               sec.name as section_name
               FROM students s
               JOIN classes c ON s.class_id = c.id
               JOIN sections sec ON s.section_id = sec.id
               WHERE s.academic_year_id = ?
               AND s.class_id = ?";
    
    $params = [$academicYearId, $classId];
    $types = "ii";
    
    if ($sectionId) {
        $query .= " AND s.section_id = ?";
        $params[] = $sectionId;
        $types .= "i";
    }
    
    $query .= " ORDER BY s.full_name ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        echo json_encode(['success' => true, 'students' => $students]);
    } else {
        logError("Failed to fetch students: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch students: ' . $stmt->error]);
    }
    
    exit;
}

// Process transfer/promotion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action !== 'promote' && $action !== 'transfer') {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit;
    }
    
    // Common parameters
    if (!isset($_POST['student_ids']) || !isset($_POST['effective_date'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
        exit;
    }
    
    $studentIds = json_decode($_POST['student_ids'], true);
    $effectiveDate = $_POST['effective_date'];
    
    if (!is_array($studentIds) || empty($studentIds)) {
        echo json_encode(['success' => false, 'message' => 'No students selected']);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        $successCount = 0;
        $errors = [];
        
        if ($action === 'promote') {
            // Promotion to next class
            if (!isset($_POST['target_academic_year_id'])) {
                throw new Exception('Target academic year is required for promotion');
            }
            
            $targetAcademicYearId = (int) $_POST['target_academic_year_id'];
            
            foreach ($studentIds as $studentId) {
                // Get student's current class and section
                $stmt = $conn->prepare("SELECT class_id, section_id FROM students WHERE user_id = ?");
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $errorMsg = "Student ID $studentId not found";
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                $student = $result->fetch_assoc();
                $fromClassId = $student['class_id'];
                $fromSectionId = $student['section_id'];
                
                // Find next class
                $nextClassId = getNextClass($conn, $fromClassId);
                if (!$nextClassId) {
                    $errorMsg = "Could not determine next class for student ID $studentId";
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                // Find a section in the next class (use the first one for simplicity)
                $stmt = $conn->prepare("SELECT id FROM sections WHERE class_id = ? LIMIT 1");
                $stmt->bind_param("i", $nextClassId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    // Create a default section if none exists
                    $stmt = $conn->prepare("INSERT INTO sections (name, class_id, created_at) VALUES ('A', ?, NOW())");
                    $stmt->bind_param("i", $nextClassId);
                    
                    if ($stmt->execute()) {
                        $nextSectionId = $stmt->insert_id;
                        logError("Created default section 'A' for class ID $nextClassId");
                    } else {
                        $errorMsg = "No sections found for the next class and couldn't create one for student ID $studentId";
                        $errors[] = $errorMsg;
                        logError($errorMsg);
                    continue;
                }
                } else {
                $section = $result->fetch_assoc();
                $nextSectionId = $section['id'];
                }
                
                // Create transfer record
                $reason = "Annual Promotion";
                if (!createTransferRecord($conn, $studentId, $fromClassId, $fromSectionId, $nextClassId, $nextSectionId, $effectiveDate, $reason)) {
                    $errorMsg = "Failed to create transfer record for student ID $studentId: " . $stmt->error;
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                // Update student's class and section
                if (!updateStudentClass($conn, $studentId, $nextClassId, $nextSectionId)) {
                    $errorMsg = "Failed to update class for student ID $studentId: " . $stmt->error;
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                // Update academic year
                $stmt = $conn->prepare("UPDATE students SET academic_year_id = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->bind_param("ii", $targetAcademicYearId, $studentId);
                
                if (!$stmt->execute()) {
                    $errorMsg = "Failed to update academic year for student ID $studentId: " . $stmt->error;
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                // Reset roll number for the new class/section if needed
                $stmt = $conn->prepare("UPDATE students SET roll_number = NULL WHERE user_id = ?");
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                
                $successCount++;
            }
        } else {
            // Transfer to specific class/section
            if (!isset($_POST['target_class_id']) || !isset($_POST['target_section_id'])) {
                throw new Exception('Target class and section are required for transfer');
            }
            
            $targetClassId = (int) $_POST['target_class_id'];
            $targetSectionId = (int) $_POST['target_section_id'];
            
            // Verify target class and section exist
            $stmt = $conn->prepare("SELECT c.name, s.name as section_name FROM classes c JOIN sections s ON c.id = s.class_id WHERE c.id = ? AND s.id = ?");
            $stmt->bind_param("ii", $targetClassId, $targetSectionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid target class or section');
            }
            
            foreach ($studentIds as $studentId) {
                // Get student's current class and section
                $stmt = $conn->prepare("SELECT class_id, section_id FROM students WHERE user_id = ?");
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $errorMsg = "Student ID $studentId not found";
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                $student = $result->fetch_assoc();
                $fromClassId = $student['class_id'];
                $fromSectionId = $student['section_id'];
                
                // Create transfer record
                $reason = "Transfer to different class/section";
                if (!createTransferRecord($conn, $studentId, $fromClassId, $fromSectionId, $targetClassId, $targetSectionId, $effectiveDate, $reason)) {
                    $errorMsg = "Failed to create transfer record for student ID $studentId: " . $stmt->error;
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                // Update student's class and section
                if (!updateStudentClass($conn, $studentId, $targetClassId, $targetSectionId)) {
                    $errorMsg = "Failed to update class for student ID $studentId: " . $stmt->error;
                    $errors[] = $errorMsg;
                    logError($errorMsg);
                    continue;
                }
                
                // Reset roll number for the new class/section
                $stmt = $conn->prepare("UPDATE students SET roll_number = NULL WHERE user_id = ?");
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                
                $successCount++;
            }
        }
        
        // Commit or rollback based on success
        if ($successCount > 0) {
            $conn->commit();
            echo json_encode([
                'success' => true, 
                'success_count' => $successCount,
                'errors' => $errors
            ]);
        } else {
            $conn->rollback();
            $errorMsg = 'No students were processed successfully';
            logError($errorMsg);
            echo json_encode([
                'success' => false, 
                'message' => $errorMsg,
                'errors' => $errors
            ]);
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        logError("Error: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
    
    exit;
}

// Default response for invalid requests
echo json_encode(['success' => false, 'message' => 'Invalid request']); 