<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'con.php';
header('Content-Type: application/json');

function validate_row($row, $rowNum, $existingAdmissionNos) {
    $errors = [];
    $warnings = [];
    $required = [
        'Admission No.', 'Name', 'Gender', 'DOB', 'Class', 'Section', 
        'Admission Date', 'Address'
    ];
    
    foreach ($required as $field) {
        if (empty($row[$field])) {
            $errors[] = "$field is required";
        }
    }
    
    if (in_array($row['Admission No.'], $existingAdmissionNos)) {
        $errors[] = 'Duplicate Admission Number';
    }
    
    // Validate date formats
    $dateFields = ['DOB', 'Admission Date'];
    foreach ($dateFields as $field) {
        if (!empty($row[$field])) {
            // Convert DD/MM/YYYY to YYYY-MM-DD
            $date = DateTime::createFromFormat('d/m/Y', $row[$field]);
            if (!$date) {
                $errors[] = "Invalid date format for $field. Please use DD/MM/YYYY format.";
            }
        }
    }
    
    // Validate email if present
    if (!empty($row['Contact Email Id']) && !filter_var($row['Contact Email Id'], FILTER_VALIDATE_EMAIL)) {
        $warnings[] = 'Invalid email format';
    }
    
    return [
        'rowNum' => $rowNum,
        'data' => $row,
        'errors' => $errors,
        'warnings' => $warnings,
        'status' => count($errors) ? 'error' : (count($warnings) ? 'warning' : 'valid')
    ];
}

/**
 * Get class ID by name or create it if it doesn't exist
 * @param mysqli $conn Database connection
 * @param string $className Class name
 * @return int|null Class ID or null on failure
 */
function getOrCreateClass($conn, $className) {
    // First try to find the class by name
    $className = trim($className);
    $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt->bind_param("s", $className);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        // Class already exists
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'];
    }
    
    // Class doesn't exist, create it
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO classes (name) VALUES (?)");
    $stmt->bind_param("s", $className);
    
    if ($stmt->execute()) {
        $classId = $conn->insert_id;
        $stmt->close();
        return $classId;
    } else {
        $stmt->close();
        return null;
    }
}

/**
 * Get section ID by name and class ID or create it if it doesn't exist
 * @param mysqli $conn Database connection
 * @param int $classId Class ID
 * @param string $sectionName Section name
 * @return int|null Section ID or null on failure
 */
function getOrCreateSection($conn, $classId, $sectionName) {
    // First try to find the section by name and class ID
    $sectionName = trim($sectionName);
    $stmt = $conn->prepare("SELECT id FROM sections WHERE class_id = ? AND name = ?");
    $stmt->bind_param("is", $classId, $sectionName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        // Section already exists
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'];
    }
    
    // Section doesn't exist, create it
    $stmt->close();
    $capacity = 30; // Default capacity for new sections
    $stmt = $conn->prepare("INSERT INTO sections (class_id, name, capacity) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $classId, $sectionName, $capacity);
    
    if ($stmt->execute()) {
        $sectionId = $conn->insert_id;
        $stmt->close();
        return $sectionId;
    } else {
        $stmt->close();
        return null;
    }
}

/**
 * Get academic year ID by name or create it if it doesn't exist
 * @param mysqli $conn Database connection
 * @param string $academicYear Academic year name (e.g. "2023-2024")
 * @return int|null Academic year ID or null on failure
 */
function getOrCreateAcademicYear($conn, $academicYear) {
    // First try to find the academic year by name
    $academicYear = trim($academicYear);
    $stmt = $conn->prepare("SELECT id FROM academic_years WHERE name = ?");
    $stmt->bind_param("s", $academicYear);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        // Academic year already exists
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row['id'];
    }
    
    // Academic year doesn't exist, try to create it
    $stmt->close();
    
    // Parse the academic year format (e.g., "2024-25")
    $years = explode('-', $academicYear);
    if (count($years) == 2) {
        // Handle formats like "2024-25" or "2024-2025"
        $startYear = $years[0];
        $endYear = $years[1];
        
        // If end year is just 2 digits, add the century
        if (strlen($endYear) == 2) {
            $endYear = substr($startYear, 0, 2) . $endYear;
        }
        
        $startDate = $startYear . '-06-01'; // June 1st of first year
        $endDate = $endYear . '-05-31';     // May 31st of second year
        
        $stmt = $conn->prepare("INSERT INTO academic_years (name, start_date, end_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $academicYear, $startDate, $endDate);
        
        if ($stmt->execute()) {
            $academicYearId = $conn->insert_id;
            $stmt->close();
            return $academicYearId;
        }
    }
    
    if (isset($stmt)) $stmt->close();
    return null;
}

/**
 * Function to create parent account
 * @param mysqli $conn Database connection
 * @param int $studentUserId Student user ID
 * @param string $name Parent name
 * @param string $relationship Relationship (father/mother/other)
 * @param string $phone Phone number
 * @param string $email Email
 * @param string $admissionNumber Student's admission number (used for login)
 * @return array Parent account details
 */
function createParentAccount($conn, $studentUserId, $name, $relationship, $phone, $email, $admissionNumber) {
    // Generate parent email if not provided
    if (empty($email)) {
        $email = strtolower(str_replace(' ', '.', $name)) . rand(100, 999) . '@example.com';
    }
    
    // Use admission number as username with parent prefix
    $username = 'p_' . $admissionNumber;
    
    // Generate password (simple pattern: 'parent' + last 4 digits of phone or random if no phone)
    if (!empty($phone) && strlen($phone) >= 4) {
        $password = 'parent' . substr($phone, -4);
    } else {
        $password = 'parent' . rand(1000, 9999);
    }
    
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Create user entry
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, full_name, role, status) VALUES (?, ?, ?, 'parent', 'active')");
    $stmt->bind_param('sss', $email, $passwordHash, $name);
    $stmt->execute();
    $parentUserId = $conn->insert_id;
    $stmt->close();
    
    // Link parent to student
    $stmt = $conn->prepare("INSERT INTO parent_accounts (user_id, student_user_id, relationship, phone, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('iisss', $parentUserId, $studentUserId, $relationship, $phone, $email);
    $stmt->execute();
    $stmt->close();
    
    return [
        "user_id" => $parentUserId,
        "username" => $username,
        "email" => $email,
        "default_password" => $password
    ];
}

// Update createParentAccounts to capture parent information in student record
function captureParentInfo($conn, $studentUserId, $motherName, $fatherName, $phone, $email) {
    // Update student record with parent information
    $stmt = $conn->prepare("UPDATE students SET 
                           mother_name = ?, 
                           father_name = ?, 
                           mobile = ?, 
                           contact_email = ? 
                           WHERE user_id = ?");
    $stmt->bind_param('ssssi', $motherName, $fatherName, $phone, $email, $studentUserId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Function to initialize common blood groups in the database if they don't exist
 * @param mysqli $conn Database connection
 */
function initializeBloodGroups($conn) {
    // Common blood groups
    $bloodGroups = [
        'A+' => 'A Positive',
        'A-' => 'A Negative',
        'B+' => 'B Positive',
        'B-' => 'B Negative',
        'AB+' => 'AB Positive',
        'AB-' => 'AB Negative',
        'O+' => 'O Positive',
        'O-' => 'O Negative',
        'UNKNOWN' => 'Unknown',
        'NA' => 'Not Available'
    ];
    
    foreach ($bloodGroups as $code => $label) {
        // Check if this blood group already exists
        $stmt = $conn->prepare("SELECT code FROM blood_groups WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            // Blood group doesn't exist, insert it
            $insertStmt = $conn->prepare("INSERT INTO blood_groups (code, label) VALUES (?, ?)");
            $insertStmt->bind_param("ss", $code, $label);
            $insertStmt->execute();
            $insertStmt->close();
        }
        
        $stmt->close();
    }
}

/**
 * Function to initialize common genders in the database if they don't exist
 * @param mysqli $conn Database connection
 */
function initializeGenders($conn) {
    // Common genders
    $genders = [
        'M' => 'Male',
        'F' => 'Female',
        'O' => 'Other',
        'MALE' => 'Male',
        'FEMALE' => 'Female',
        'OTHER' => 'Other',
        'NA' => 'Not Available'
    ];
    
    foreach ($genders as $code => $label) {
        // Check if this gender already exists
        $stmt = $conn->prepare("SELECT code FROM genders WHERE code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            // Gender doesn't exist, insert it
            $insertStmt = $conn->prepare("INSERT INTO genders (code, label) VALUES (?, ?)");
            $insertStmt->bind_param("ss", $code, $label);
            $insertStmt->execute();
            $insertStmt->close();
        }
        
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CSV to DB column mapping (only columns that exist in the students table)
    $csvToDb = [
        'Admission No.' => 'admission_number',
        'Name' => 'full_name',
        'Gender' => 'gender_code',
        'DOB' => 'dob',
        'Mother Name' => 'mother_name',
        'Father Name' => 'father_name',
        'Address' => 'address',
        'Pincode' => 'pincode',
        'Mobile No.' => 'mobile',
        'Alternate Mobile No.' => 'alt_mobile',
        'Contact Email Id' => 'contact_email',
        'Mother Tongue' => 'mother_tongue',
        'Blood Group' => 'blood_group_code',
        'Admission Date' => 'admission_date',
        'Student State Code' => 'student_state_code'
    ];

    if ($action === 'validate') {
        if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'File upload failed.']);
            exit;
        }
        
        $file = $_FILES['csvFile']['tmp_name'];
        $handle = fopen($file, 'r');
        if (!$handle) {
            echo json_encode(['success' => false, 'message' => 'Cannot open file.']);
            exit;
        }
        
        $header = fgetcsv($handle);
        $rows = [];
        $admissionNosInFile = [];
        $firstRow = null;
        
        while (($data = fgetcsv($handle)) !== false) {
            $row = array_combine($header, $data);
            if ($firstRow === null) $firstRow = $row;
            
            $adNo = $row['Admission No.'] ?? '';
            if ($adNo) {
                if (in_array($adNo, $admissionNosInFile)) {
                    $row['DuplicateInFile'] = true;
                }
                $admissionNosInFile[] = $adNo;
            }
            $rows[] = $row;
        }
        fclose($handle);
        
        if (!$header || !$firstRow) {
            echo json_encode([
                'success' => false,
                'message' => 'CSV parsing failed. Check header and data.',
                'header' => $header,
                'firstRow' => $firstRow
            ]);
            exit;
        }
        
        // Check if admission numbers already exist in the database
        $existingAdmissionNos = [];
        if (!empty($admissionNosInFile)) {
            $inClause = implode(",", array_map(function($n) use ($conn) {
                return "'" . $conn->real_escape_string($n) . "'";
            }, $admissionNosInFile));
            
            $sql = "SELECT admission_number FROM students WHERE admission_number IN ($inClause)";
            $result = $conn->query($sql);
            if ($result) {
            while ($r = $result->fetch_assoc()) {
                    $existingAdmissionNos[] = $r['admission_number'];
                }
            }
        }
        
        $preview = [];
        $rowNum = 1;
        foreach ($rows as $row) {
            if (!is_array($row) || empty($row['Admission No.'])) {
                $preview[] = [
                    'rowNum' => $rowNum,
                    'data' => $row,
                    'errors' => ['Missing Admission Number.'],
                    'warnings' => [],
                    'status' => 'error'
                ];
                $rowNum++;
                continue;
            }
            
            $res = validate_row($row, $rowNum, $existingAdmissionNos);
            if (!empty($row['DuplicateInFile'])) {
                $res['errors'][] = 'Duplicate Admission Number in file';
                $res['status'] = 'error';
            }
            $preview[] = $res;
            $rowNum++;
        }
        
        echo json_encode(['success' => true, 'preview' => $preview]);
        exit;
    } elseif ($action === 'import') {
        $students = json_decode($_POST['students'], true);
        $createParentAccounts = isset($_POST['createParentAccounts']) ? filter_var($_POST['createParentAccounts'], FILTER_VALIDATE_BOOLEAN) : true;
        $academicYearId = isset($_POST['academicYear']) ? intval($_POST['academicYear']) : null;
        
        if (!is_array($students)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input data.']);
            exit;
        }
        
        $imported = 0;
        $duplicates = [];
        $errors = [];
        $createdClasses = [];
        $createdSections = [];
        $createdYears = [];
        $studentCredentials = [];
        $parentCredentials = [];
        $conn = $GLOBALS['conn'];
        
        // Initialize common values first
        initializeBloodGroups($conn);
        initializeGenders($conn);
        
        // Start a transaction
        $conn->begin_transaction();
        
        try {
        foreach ($students as $row) {
            $data = $row['data'];
                
                if (!isset($data['Admission No.']) || trim($data['Admission No.']) === '') {
                    $errors[] = "Row {$row['rowNum']}: Missing Admission Number.";
                    continue;
                }
                
                // Check for duplicate admission number
                $admissionNo = $conn->real_escape_string($data['Admission No.']);
                $check = $conn->query("SELECT user_id FROM students WHERE admission_number='$admissionNo' LIMIT 1");
                if ($check && $check->num_rows > 0) {
                    $duplicates[] = [
                        'rowNum' => $row['rowNum'],
                        'admission_no' => $admissionNo
                    ];
                    continue;
                }
                
                // Get or create class
                $className = isset($data['Class']) ? trim($data['Class']) : null;
                $classId = null;
                
                if ($className) {
                    $classId = getOrCreateClass($conn, $className);
                    if (!$classId) {
                        $errors[] = "Row {$row['rowNum']}: Failed to create Class '{$className}'.";
                        continue;
                    }
                    if (!in_array($className, $createdClasses)) {
                        $createdClasses[] = $className;
                    }
                } else {
                    $errors[] = "Row {$row['rowNum']}: Class is required.";
                    continue;
                }
                
                // Get or create section
                $sectionName = isset($data['Section']) ? trim($data['Section']) : null;
                $sectionId = null;
                
                if ($sectionName) {
                    $sectionId = getOrCreateSection($conn, $classId, $sectionName);
                    if (!$sectionId) {
                        $errors[] = "Row {$row['rowNum']}: Failed to create Section '{$sectionName}' for Class '{$className}'.";
                        continue;
                    }
                    $sectionKey = $className . '-' . $sectionName;
                    if (!in_array($sectionKey, $createdSections)) {
                        $createdSections[] = $sectionKey;
                }
                } else {
                    $errors[] = "Row {$row['rowNum']}: Section is required.";
                    continue;
                }
                
                // Get or create academic year if not provided as parameter
                if (!$academicYearId && isset($data['Academic Year'])) {
                    $academicYear = trim($data['Academic Year']);
                    $academicYearId = getOrCreateAcademicYear($conn, $academicYear);
                    if (!$academicYearId) {
                        $errors[] = "Row {$row['rowNum']}: Failed to create Academic Year '{$academicYear}'.";
                continue;
            }
                    if (!in_array($academicYear, $createdYears)) {
                        $createdYears[] = $academicYear;
                    }
                }
                
                // Check gender code in genders table
                $gender = isset($data['Gender']) ? trim($data['Gender']) : null;
                $genderCode = null;
                
                if ($gender) {
                    $genderQuery = $conn->query("SELECT code FROM genders WHERE code = '" . $conn->real_escape_string($gender) . "' LIMIT 1");
                    if ($genderQuery && $genderQuery->num_rows > 0) {
                        $genderRow = $genderQuery->fetch_assoc();
                        $genderCode = $genderRow['code'];
                    } else {
                        // Insert the gender if it doesn't exist
                        $insertGender = $conn->prepare("INSERT INTO genders (code, label) VALUES (?, ?)");
                        $insertGender->bind_param("ss", $gender, $gender);
                        if ($insertGender->execute()) {
                            $genderCode = $gender;
                        }
                        $insertGender->close();
                    }
                }
                
                // Check blood group code in blood_groups table
                $bloodGroup = isset($data['Blood Group']) ? trim($data['Blood Group']) : null;
                $bloodGroupCode = null;
                
                if ($bloodGroup) {
                    // Handle special cases like "Under Investigation"
                    if (strlen($bloodGroup) > 10 || 
                        stripos($bloodGroup, 'under') !== false || 
                        stripos($bloodGroup, 'investigation') !== false ||
                        stripos($bloodGroup, 'soon') !== false) {
                        
                        // Use a standard code for these cases
                        $bloodGroupCode = 'UNKNOWN';
                    } else {
                        // Normalize common formats (removing spaces, etc.)
                        $normalizedBloodGroup = str_replace(' ', '', strtoupper($bloodGroup));
                        
                        // Check common patterns like "A POS" -> "A+", "O NEG" -> "O-"
                        if (preg_match('/^(A|B|AB|O)(POS|POSITIVE)$/i', $normalizedBloodGroup, $matches)) {
                            $normalizedBloodGroup = $matches[1] . '+';
                        } else if (preg_match('/^(A|B|AB|O)(NEG|NEGATIVE)$/i', $normalizedBloodGroup, $matches)) {
                            $normalizedBloodGroup = $matches[1] . '-';
                        }
                        
                        // Use the normalized blood group or default to the original
                        $bloodGroupToCheck = in_array($normalizedBloodGroup, ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']) 
                            ? $normalizedBloodGroup 
                            : $bloodGroup;
                        
                        // Try to find the normalized blood group in the database
                        $bloodQuery = $conn->prepare("SELECT code FROM blood_groups WHERE code = ? LIMIT 1");
                        $bloodQuery->bind_param("s", $bloodGroupToCheck);
                        $bloodQuery->execute();
                        $bloodQuery->store_result();
                        
                        if ($bloodQuery->num_rows > 0) {
                            $bloodGroupCode = $bloodGroupToCheck;
                        } else {
                            // If not found and it's a short code (likely valid), add it
                            if (strlen($bloodGroup) <= 5) {
                                // Insert as new blood group with safe values
                                $safeCode = preg_replace('/[^A-Za-z0-9\+\-]/', '', $bloodGroup);
                                $insertBlood = $conn->prepare("INSERT INTO blood_groups (code, label) VALUES (?, ?)");
                                $insertBlood->bind_param("ss", $safeCode, $bloodGroup);
                                
                                if ($insertBlood->execute()) {
                                    $bloodGroupCode = $safeCode;
                                } else {
                                    // If insert fails, fall back to UNKNOWN
                                    $bloodGroupCode = 'UNKNOWN';
                                }
                                $insertBlood->close();
                            } else {
                                // Too long, use UNKNOWN
                                $bloodGroupCode = 'UNKNOWN';
                            }
                        }
                        $bloodQuery->close();
                    }
                }
                
                // Format dates from DD/MM/YYYY to YYYY-MM-DD
                $dob = null;
                if (!empty($data['DOB'])) {
                    $date = DateTime::createFromFormat('d/m/Y', $data['DOB']);
                    if ($date) {
                        $dob = $date->format('Y-m-d');
                    }
                }
                
                $admissionDate = null;
                if (!empty($data['Admission Date'])) {
                    $date = DateTime::createFromFormat('d/m/Y', $data['Admission Date']);
                    if ($date) {
                        $admissionDate = $date->format('Y-m-d');
                    } else {
                        $admissionDate = date('Y-m-d');
                    }
                } else {
                    $admissionDate = date('Y-m-d');
                }
                
                // Use admission number as login ID
                $username = $admissionNo;
                $email = isset($data['Contact Email Id']) && !empty($data['Contact Email Id']) ? 
                         trim($data['Contact Email Id']) : 
                         $username . '@example.com';
                
                // Check if email is 'NA' or invalid, generate a unique one
                if ($email == 'NA' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    // Generate unique email based on name and admission number
                    $cleanName = preg_replace('/[^a-zA-Z0-9]/', '', $data['Name']);
                    $cleanName = strtolower($cleanName);
                    if (empty($cleanName)) {
                        $cleanName = 'student';
                    }
                    $email = $cleanName . '.' . $admissionNo . '.' . rand(100, 999) . '@example.com';
                }
                
                // Check if this email already exists and ensure uniqueness
                $checkEmailSql = "SELECT id FROM users WHERE email = ?";
                $checkStmt = $conn->prepare($checkEmailSql);
                $checkStmt->bind_param('s', $email);
                $checkStmt->execute();
                $checkStmt->store_result();
                
                // If email exists, keep generating new ones until we find a unique one
                if ($checkStmt->num_rows > 0) {
                    $originalEmail = $email;
                    $attempts = 0;
                    do {
                        $attempts++;
                        // Add a timestamp and random number to make it unique
                        $emailParts = explode('@', $originalEmail);
                        $email = $emailParts[0] . '.' . time() . '.' . rand(100, 999) . '@' . (isset($emailParts[1]) ? $emailParts[1] : 'example.com');
                        
                        // Check if new email is unique
                        $checkStmt->close();
                        $checkStmt = $conn->prepare($checkEmailSql);
                        $checkStmt->bind_param('s', $email);
                        $checkStmt->execute();
                        $checkStmt->store_result();
                        
                        // If we've tried 5 times and still can't get a unique email, use a completely different format
                        if ($attempts >= 5 && $checkStmt->num_rows > 0) {
                            $email = 'student' . time() . '.' . rand(1000, 9999) . '@example.com';
                            
                            // One last check to ensure uniqueness
                            $checkStmt->close();
                            $checkStmt = $conn->prepare($checkEmailSql);
                            $checkStmt->bind_param('s', $email);
                            $checkStmt->execute();
                            $checkStmt->store_result();
                            
                            if ($checkStmt->num_rows > 0) {
                                // Really last resort - use UUID to ensure uniqueness
                                $email = 'student_' . uniqid() . '@example.com';
                }
                            
                            break;
                        }
                    } while ($checkStmt->num_rows > 0 && $attempts < 10);
                }
                $checkStmt->close();
                
                // Generate a simple password based on admission number
                $password = 'student' . substr(preg_replace('/[^0-9]/', '', $admissionNo), -4);
                if (strlen($password) < 10) { // If not enough numbers in admission number
                    $password = 'student' . rand(1000, 9999);
                }
                
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $fullName = $data['Name'];
                
                // Now insert into users table with guaranteed unique email
                $userSql = "INSERT INTO users (email, password_hash, full_name, role, status, created_at) 
                           VALUES (?, ?, ?, 'student_family', 'active', NOW())";
                $userStmt = $conn->prepare($userSql);
                if (!$userStmt) {
                    $errors[] = "Row {$row['rowNum']}: DB preparation error: " . $conn->error;
                continue;
            }
                
                $userStmt->bind_param('sss', $email, $passwordHash, $fullName);
                if (!$userStmt->execute()) {
                    $errors[] = "Row {$row['rowNum']}: Failed to create user: " . $userStmt->error;
                    $userStmt->close();
                continue;
            }
                
                $userId = $conn->insert_id;
                $userStmt->close();
                
                // Store student credentials
                $studentCredentials[] = [
                    'name' => $fullName,
                    'admission_no' => $admissionNo,
                    'username' => $username,
                    'password' => $password,
                    'email' => $email
                ];
                
                // Prepare data for students table
                $rollNumber = 0;
                // Extract roll number from admission number if it follows format XX/YEAR-YY
                if (preg_match('/^(\d+)\//', $admissionNo, $matches)) {
                    $rollNumber = intval($matches[1]);
                }
                
                $studentData = [
                    'user_id' => $userId,
                    'admission_number' => $admissionNo,
                    'admission_date' => $admissionDate,
                    'class_id' => $classId,
                    'section_id' => $sectionId,
                    'roll_number' => $rollNumber,
                    'full_name' => $fullName,
                    'gender_code' => $genderCode,
                    'dob' => $dob,
                    'mother_name' => isset($data['Mother Name']) ? $data['Mother Name'] : null,
                    'father_name' => isset($data['Father Name']) ? $data['Father Name'] : null,
                    'address' => isset($data['Address']) ? $data['Address'] : null,
                    'pincode' => isset($data['Pincode']) ? $data['Pincode'] : null,
                    'mobile' => isset($data['Mobile No.']) ? $data['Mobile No.'] : null,
                    'alt_mobile' => isset($data['Alternate Mobile No.']) ? $data['Alternate Mobile No.'] : null,
                    'contact_email' => $email,
                    'mother_tongue' => isset($data['Mother Tongue']) ? $data['Mother Tongue'] : null,
                    'blood_group_code' => $bloodGroupCode,
                    'student_state_code' => isset($data['Student State Code']) ? $data['Student State Code'] : null,
                    'academic_year_id' => $academicYearId,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                
                // Build insert query for students table
                $fields = array_keys($studentData);
                $placeholders = array_fill(0, count($fields), '?');
                $types = '';
                $values = [];
                
                foreach ($studentData as $key => $value) {
                    if ($key === 'user_id' || $key === 'class_id' || $key === 'section_id' || 
                        $key === 'roll_number' || $key === 'academic_year_id') {
                        $types .= 'i';
                    } else {
                        $types .= 's';
                    }
                    $values[] = $value;
                }
                
                $studentSql = "INSERT INTO students (`" . implode('`, `', $fields) . "`) VALUES (" . implode(', ', $placeholders) . ")";
                $studentStmt = $conn->prepare($studentSql);
                
                if (!$studentStmt) {
                    $errors[] = "Row {$row['rowNum']}: DB preparation error: " . $conn->error;
                    continue;
                }
                
                $studentStmt->bind_param($types, ...$values);
                
                if (!$studentStmt->execute()) {
                    $errors[] = "Row {$row['rowNum']}: Failed to create student: " . $studentStmt->error;
                    $studentStmt->close();
                continue;
            }
                
                $studentStmt->close();
                
                // Store parent information within the student record
                // Parents will use the same account as the student
                if ($createParentAccounts) {
                    // Add parent information to note field or a designated field
                    // This information can be displayed on the student profile for parents
                    $parentInfo = [];
                    if (!empty($data['Father Name'])) {
                        $parentInfo[] = "Father: " . $data['Father Name'];
                    }
                    if (!empty($data['Mother Name'])) {
                        $parentInfo[] = "Mother: " . $data['Mother Name'];
                    }
                    
                    // Update student record with parent information
                    $mothername = !empty($data['Mother Name']) ? $data['Mother Name'] : null;
                    $fathername = !empty($data['Father Name']) ? $data['Father Name'] : null;
                    $phone = !empty($data['Mobile No.']) ? $data['Mobile No.'] : null;
                    captureParentInfo($conn, $userId, $mothername, $fathername, $phone, $email);
                    
                    // Instead of separate parent credentials, just annotate that this is a shared account
                    $parentNote = "Parents will use the same account as the student.";
                    $parentCredentials[] = [
                        'student_name' => $fullName,
                        'admission_no' => $admissionNo,
                        'parent_name' => $mothername ?? $fathername,
                        'relationship' => !empty($mothername) ? 'Mother' : 'Father',
                        'username' => $username,
                        'password' => $password,
                        'email' => $email,
                        'note' => $parentNote
                    ];
                }
                
                $imported++;
            }
            
            // Commit transaction if we have at least one successful import
            if ($imported > 0) {
                $conn->commit();
            } else {
                $conn->rollback();
            }
            
        echo json_encode([
            'success' => true,
            'imported' => $imported,
            'duplicates' => $duplicates,
                'errors' => $errors,
                'student_credentials' => $studentCredentials,
                'parent_credentials' => $parentCredentials,
                'created_classes' => $createdClasses,
                'created_sections' => $createdSections,
                'created_years' => $createdYears
            ]);
            
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            'errors' => $errors
        ]);
        }
        
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']); 