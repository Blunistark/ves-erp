<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'con.php';

// Check database connection
if ($conn->connect_error) {
    file_put_contents(__DIR__ . '/db_connection_error.log', date('Y-m-d H:i:s') . " - Database connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
}

header('Content-Type: application/json');

// Add a student
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    file_put_contents(__DIR__ . '/student_debug.log', "ADD POST: " . json_encode($_POST) . "\n", FILE_APPEND);
    // Validate required fields
    $required = [
        'firstName', 'lastName', 'dateOfBirth', 'gender', 'admissionNo', 'admissionDate',
        'class', 'section', 'academicYear', 'address', 'city'
    ];
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "$field is required";
        }
    }
    if (!empty($errors)) {
        file_put_contents(__DIR__ . '/student_debug.log', "ADD ERRORS: " . json_encode($errors) . "\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => implode(', ', $errors)]);
        exit;
    }
    // Check for duplicate admission number
    $admissionNo = $_POST['admissionNo'];
    file_put_contents(__DIR__ . '/student_debug.log', "ADD BEFORE DUPLICATE CHECK\n", FILE_APPEND);
    $check = $conn->prepare("SELECT user_id FROM students WHERE admission_number = ?");
    if (!$check) {
        file_put_contents(__DIR__ . '/student_debug.log', "ADD DUPLICATE PREPARE ERROR: " . $conn->error . "\n", FILE_APPEND);
    }
    $check->bind_param("s", $admissionNo);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        file_put_contents(__DIR__ . '/student_debug.log', "ADD DUPLICATE: $admissionNo\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => "Admission number already exists."]);
        exit;
    }
    $check->close();
    file_put_contents(__DIR__ . '/student_debug.log', "ADD AFTER DUPLICATE CHECK\n", FILE_APPEND);
    // Handle photo upload if present
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $photoPath = 'uploads/students/' . uniqid('student_') . '.' . $ext;
            if (!is_dir('uploads/students')) {
                mkdir('uploads/students', 0777, true);
            }
            move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
        }
    }
    
    // Start transaction for student and parent accounts
    $conn->begin_transaction();
    
    try {
        // Create student user account
        $email = isset($_POST['email']) ? $_POST['email'] : $_POST['firstName'] . '.' . $_POST['lastName'] . '@example.com';
        $password = strtolower(substr($_POST['firstName'], 0, 1)) . strtolower(substr($_POST['lastName'], 0, 1)) . rand(1000, 9999);
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Get full name for users table
        $fullName = $_POST['firstName'];
        if (!empty($_POST['middleName'])) {
            $fullName .= ' ' . $_POST['middleName'];
        }
        $fullName .= ' ' . $_POST['lastName'];
        
        // Create user entry with a unified role for both student and parent access
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, full_name, role, status) VALUES (?, ?, ?, 'student_family', 'active')");
        $stmt->bind_param('sss', $email, $passwordHash, $fullName);
        $stmt->execute();
        $studentUserId = $conn->insert_id;
        $stmt->close();
        
        // Check if gender_code exists in genders table
        $gender = $_POST['gender'];
        $genderCode = null;
        $checkGender = $conn->prepare("SELECT code FROM genders WHERE code = ?");
        $checkGender->bind_param("s", $gender);
        $checkGender->execute();
        $checkGender->store_result();
        if ($checkGender->num_rows > 0) {
            $genderCode = $gender;
        }
        $checkGender->close();
        
        // Check if blood_group exists in blood_groups table
        $bloodGroup = isset($_POST['bloodGroup']) ? $_POST['bloodGroup'] : null;
        $bloodGroupCode = null;
        if ($bloodGroup) {
            $checkBlood = $conn->prepare("SELECT code FROM blood_groups WHERE code = ?");
            $checkBlood->bind_param("s", $bloodGroup);
            $checkBlood->execute();
            $checkBlood->store_result();
            if ($checkBlood->num_rows > 0) {
                $bloodGroupCode = $bloodGroup;
            }
            $checkBlood->close();
        }
        
        // Create student entry
        $stmt = $conn->prepare("INSERT INTO students (
            user_id, admission_number, admission_date, class_id, section_id, roll_number, 
            full_name, gender_code, dob, mother_name, father_name, address, pincode, 
            mobile, alt_mobile, contact_email, mother_tongue, blood_group_code, 
            nationality, academic_year_id, aadhar_card_number, medical_conditions, photo,
            created_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
        )");
        
        $bindTypes = "issiiisssssssssssssisss";
        $rollNumber = isset($_POST['rollNumber']) ? intval($_POST['rollNumber']) : 0;
        $motherName = isset($_POST['motherName']) ? $_POST['motherName'] : null;
        $fatherName = isset($_POST['fatherName']) ? $_POST['fatherName'] : null;
        $pincode = isset($_POST['postalCode']) ? $_POST['postalCode'] : null;
        $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
        $altPhone = isset($_POST['altPhone']) ? $_POST['altPhone'] : null;
        $contactEmail = isset($_POST['email']) ? $_POST['email'] : null;
        $motherTongue = isset($_POST['motherTongue']) ? $_POST['motherTongue'] : null;
        $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : null;
        $academicYearId = intval($_POST['academicYear']);
        $aadharCard = isset($_POST['aadharCard']) ? $_POST['aadharCard'] : null;
        $medicalConditions = isset($_POST['medicalConditions']) ? $_POST['medicalConditions'] : null;
        
        $bindValues = [
            $studentUserId,
            $_POST['admissionNo'],
            $_POST['admissionDate'],
            intval($_POST['class']),
            intval($_POST['section']),
            $rollNumber,
            $fullName,
            $genderCode,
            $_POST['dateOfBirth'],
            $motherName,
            $fatherName,
            $_POST['address'],
            $pincode,
            $phone,
            $altPhone,
            $contactEmail,
            $motherTongue,
            $bloodGroupCode,
            $nationality,
            $academicYearId,
            $aadharCard,
            $medicalConditions,
            $photoPath
        ];
        
        $stmt->bind_param($bindTypes, ...$bindValues);
        $stmt->execute();
        $studentId = $conn->insert_id;
        $stmt->close();
        
        // Ensure parent information is stored in the student record
        // No separate parent accounts are needed as parents will use the student account
        $fatherName = isset($_POST['fatherName']) ? $_POST['fatherName'] : null;
        $motherName = isset($_POST['motherName']) ? $_POST['motherName'] : null;
        $guardianName = isset($_POST['guardianName']) ? $_POST['guardianName'] : null;
        
        // Add guardian name to notes or a special field if needed
        $parentPhone = $_POST['fatherPhone'] ?? $_POST['motherPhone'] ?? $_POST['guardianPhone'] ?? null;
        $parentEmail = $_POST['fatherEmail'] ?? $_POST['motherEmail'] ?? $_POST['guardianEmail'] ?? null;
        
        // Store the parent information in the student record
        captureParentInfo($conn, $studentUserId, $motherName, $fatherName, $parentPhone, $parentEmail);
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            "success" => true, 
            "message" => "Student added successfully.",
            "student_id" => $studentId,
            "email" => $email,
            "default_password" => $password
        ]);
        
    } catch (Exception $e) {
        // Roll back transaction in case of error
        $conn->rollback();
        file_put_contents(__DIR__ . '/student_debug.log', "ADD EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => "Error creating student: " . $e->getMessage()]);
    }
    
    exit;
}

// Edit a student
if (isset($_POST['action']) && $_POST['action'] == 'edit') {
    // Validate required fields
    $required = [
        'student_id', 'fullName', 'dateOfBirth', 'gender', 'admissionNo', 'admissionDate',
        'class', 'section', 'academicYear', 'address'
    ];
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "$field is required";
        }
    }
    if (!empty($errors)) {
        echo json_encode(["success" => false, "message" => implode(', ', $errors)]);
        exit;
    }

    $studentId = intval($_POST['student_id']);
    
    // Check if admission number is unique (excluding current student)
    $admissionNo = $_POST['admissionNo'];
    $check = $conn->prepare("SELECT user_id FROM students WHERE admission_number = ? AND user_id != ?");
    $check->bind_param("si", $admissionNo, $studentId);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "Admission number already exists for another student."]);
        exit;
    }
    $check->close();

    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update users table
        $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
        $stmt->bind_param('si', $_POST['fullName'], $studentId);
        $stmt->execute();
        $stmt->close();
        
        // Check if gender_code exists in genders table
        $gender = $_POST['gender'];
        $genderCode = null;
        $checkGender = $conn->prepare("SELECT code FROM genders WHERE code = ?");
        $checkGender->bind_param("s", $gender);
        $checkGender->execute();
        $checkGender->store_result();
        if ($checkGender->num_rows > 0) {
            $genderCode = $gender;
        }
        $checkGender->close();
        
        // Check if blood_group exists in blood_groups table
        $bloodGroup = isset($_POST['bloodGroup']) ? $_POST['bloodGroup'] : null;
        $bloodGroupCode = null;
        if ($bloodGroup) {
            $checkBlood = $conn->prepare("SELECT code FROM blood_groups WHERE code = ?");
            $checkBlood->bind_param("s", $bloodGroup);
            $checkBlood->execute();
            $checkBlood->store_result();
            if ($checkBlood->num_rows > 0) {
                $bloodGroupCode = $bloodGroup;
            }
            $checkBlood->close();
        }
        
        // Update student entry
        $stmt = $conn->prepare("UPDATE students SET 
            admission_number = ?, 
            admission_date = ?, 
            class_id = ?, 
            section_id = ?, 
            roll_number = ?, 
            full_name = ?, 
            gender_code = ?, 
            dob = ?, 
            mother_name = ?, 
            father_name = ?, 
            mother_aadhar_number = ?,
            father_aadhar_number = ?,
            address = ?, 
            pincode = ?, 
            mobile = ?, 
            alt_mobile = ?, 
            contact_email = ?, 
            blood_group_code = ?, 
            nationality = ?, 
            academic_year_id = ?, 
            aadhar_card_number = ?, 
            medical_conditions = ?
            WHERE user_id = ?");
        
        $rollNumber = isset($_POST['rollNumber']) ? intval($_POST['rollNumber']) : 0;
        $motherName = isset($_POST['motherName']) ? $_POST['motherName'] : null;
        $fatherName = isset($_POST['fatherName']) ? $_POST['fatherName'] : null;
        $motherAadhar = isset($_POST['motherAadhar']) ? $_POST['motherAadhar'] : null;
        $fatherAadhar = isset($_POST['fatherAadhar']) ? $_POST['fatherAadhar'] : null;
        $pincode = isset($_POST['postalCode']) ? $_POST['postalCode'] : null;
        $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
        $altPhone = isset($_POST['altPhone']) ? $_POST['altPhone'] : null;
        $contactEmail = isset($_POST['email']) ? $_POST['email'] : null;
        $nationality = isset($_POST['nationality']) ? $_POST['nationality'] : null;
        $academicYearId = intval($_POST['academicYear']);
        $aadharCard = isset($_POST['aadharCard']) ? $_POST['aadharCard'] : null;
        $medicalConditions = isset($_POST['medicalConditions']) ? $_POST['medicalConditions'] : null;
        
        $stmt->bind_param("ssiiisssssssssssssissi", 
            $_POST['admissionNo'],
            $_POST['admissionDate'],
            intval($_POST['class']),
            intval($_POST['section']),
            $rollNumber,
            $_POST['fullName'],
            $genderCode,
            $_POST['dateOfBirth'],
            $motherName,
            $fatherName,
            $motherAadhar,
            $fatherAadhar,
            $_POST['address'],
            $pincode,
            $phone,
            $altPhone,
            $contactEmail,
            $bloodGroupCode,
            $nationality,
            $academicYearId,
            $aadharCard,
            $medicalConditions,
            $studentId
        );
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            "success" => true, 
            "message" => "Student updated successfully.",
            "student_id" => $studentId
        ]);
        
    } catch (Exception $e) {
        // Roll back transaction in case of error
        $conn->rollback();
        echo json_encode(["success" => false, "message" => "Error updating student: " . $e->getMessage()]);
    }
    
    exit;
}

// Function to create parent account and link to student
function createParentAccount($conn, $studentUserId, $name, $email, $phone, $aadhar = null, $relationship = 'other') {
    // Generate email if not provided
    if (empty($email)) {
        $email = strtolower(str_replace(' ', '.', $name)) . rand(100, 999) . '@example.com';
    }
    
    // Generate password
    $password = 'parent' . rand(1000, 9999);
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Create user entry
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, full_name, role, status) VALUES (?, ?, ?, 'parent', 'active')");
    $stmt->bind_param('sss', $email, $passwordHash, $name);
    $stmt->execute();
    $parentUserId = $conn->insert_id;
    $stmt->close();
    
    // Link parent to student
    $stmt = $conn->prepare("INSERT INTO parent_accounts (user_id, student_user_id, relationship, phone, email, aadhar_card_number) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iissss', $parentUserId, $studentUserId, $relationship, $phone, $email, $aadhar);
    $stmt->execute();
    $stmt->close();
    
    return [
        "user_id" => $parentUserId,
        "email" => $email,
        "default_password" => $password
    ];
}

// Function to capture parent information in student record
function captureParentInfo($conn, $studentUserId, $motherName, $fatherName, $phone = null, $email = null, $relationship = 'both') {
    // Update student record with parent information
    $sql = "UPDATE students SET 
            mother_name = ?, 
            father_name = ?";
    
    $params = [$motherName, $fatherName];
    $types = "ss";
    
    // Add phone if provided
    if ($phone !== null) {
        $sql .= ", mobile = ?";
        $params[] = $phone;
        $types .= "s";
    }
    
    // Add email if provided
    if ($email !== null) {
        $sql .= ", contact_email = ?";
        $params[] = $email;
        $types .= "s";
    }
    
    $sql .= " WHERE user_id = ?";
    $params[] = $studentUserId;
    $types .= "i";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();
    
    return [
        "student_user_id" => $studentUserId,
        "relationship" => $relationship
    ];
}

// Fetch students
if (isset($_GET['fetch'])) {
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $perPage = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 10;
    $offset = ($page - 1) * $perPage;
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $class = isset($_GET['class']) ? trim($_GET['class']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $where = [];
    $params = [];
    $types = '';
    if ($search !== '') {
        $where[] = "(full_name LIKE ? OR admission_number LIKE ? OR father_name LIKE ? OR mobile LIKE ?)";
        $params = array_merge($params, array_fill(0, 4, "%$search%"));
        $types .= str_repeat('s', 4);
    }
    if ($class !== '') {
        $where[] = "class_id = ?";
        $params[] = $class;
        $types .= 's';
    }
    if ($status !== '') {
        $where[] = "s.status = ?";
        $params[] = $status;
        $types .= 's';
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    // Get total count
    $countSql = "SELECT COUNT(*) FROM students s $whereSql";
    $countStmt = $conn->prepare($countSql);
    if ($types) $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countStmt->bind_result($total);
    $countStmt->fetch();
    $countStmt->close();
    // Get paginated data
    $sql = "SELECT s.*, sec.name AS section_name, c.name AS class_name, g.label AS gender_label, bg.label AS blood_group_label 
            FROM students s 
            LEFT JOIN sections sec ON s.section_id = sec.id
            LEFT JOIN classes c ON s.class_id = c.id
            LEFT JOIN genders g ON s.gender_code = g.code
            LEFT JOIN blood_groups bg ON s.blood_group_code = bg.code
            $whereSql 
            ORDER BY s.user_id DESC 
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    if ($types) {
        $allParams = array_merge($params, [$perPage, $offset]);
        $allTypes = $types . 'ii';
        $stmt->bind_param($allTypes, ...$allParams);
    } else {
        $stmt->bind_param('ii', $perPage, $offset);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    $stmt->close();
    echo json_encode([
        'success' => true,
        'students' => $students,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage
    ]);
    exit;
}

// Get parent information for a student
if (isset($_GET['fetch_parents'])) {
    $studentId = intval($_GET['fetch_parents']);
    if ($studentId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        exit;
    }
    
    // Get parent information from student record
    $stmt = $conn->prepare("
        SELECT 
            user_id, 
            mother_name, 
            father_name, 
            mobile, 
            alt_mobile, 
            contact_email
        FROM students 
        WHERE user_id = ?
    ");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $studentData = $result->fetch_assoc();
    $stmt->close();
    
    // Prepare parent information from student record
    $parents = [];
    
    // Add father if present
    if (!empty($studentData['father_name'])) {
        $parents[] = [
            'relationship' => 'father',
            'full_name' => $studentData['father_name'],
            'phone' => $studentData['mobile'] ?? null,
            'email' => $studentData['contact_email'] ?? null
        ];
    }
    
    // Add mother if present
    if (!empty($studentData['mother_name'])) {
        $parents[] = [
            'relationship' => 'mother',
            'full_name' => $studentData['mother_name'],
            'phone' => $studentData['alt_mobile'] ?? $studentData['mobile'] ?? null,
            'email' => $studentData['contact_email'] ?? null
        ];
    }
    
    echo json_encode([
        'success' => true,
        'parents' => $parents,
        'note' => 'Parents use the same account as the student'
    ]);
    exit;
}

// Add parent information to existing student
if (isset($_POST['action']) && $_POST['action'] == 'add_parent') {
    $studentId = intval($_POST['student_id']);
    $relationship = $_POST['relationship'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    if ($studentId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid student ID']);
        exit;
    }
    
    // Get the student's current information first
    $stmt = $conn->prepare("SELECT user_id, mother_name, father_name FROM students WHERE user_id = ?");
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
        exit;
    }
    
    $student = $result->fetch_assoc();
    $stmt->close();
    
    try {
        $conn->begin_transaction();
        
        // Update the appropriate parent field based on relationship
        $motherName = $student['mother_name'];
        $fatherName = $student['father_name'];
        
        if ($relationship === 'mother') {
            $motherName = $name;
        } else if ($relationship === 'father') {
            $fatherName = $name;
        }
        
        // Update student record with parent information
        captureParentInfo($conn, $studentId, $motherName, $fatherName, $phone, $email);
        
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Parent information added to student record',
            'parent' => [
                'relationship' => $relationship,
                'full_name' => $name,
                'phone' => $phone,
                'email' => $email
            ]
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Error updating parent information: ' . $e->getMessage()]);
    }
    exit;
}

// Delete student
if (isset($_POST['action']) && $_POST['action'] == 'delete') {
    // In this case, we need to delete the user first which will cascade to the student
    $stmt = $conn->prepare("
        DELETE u FROM users u
        JOIN students s ON u.id = s.user_id
        WHERE s.user_id = ?
    ");
    $stmt->bind_param("i", $_POST['id']);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Student deleted."]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete student."]);
    }
    exit;
}

// Update student (adjust columns as needed for your schema)
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    file_put_contents(__DIR__ . '/student_debug.log', "UPDATE POST: " . json_encode($_POST) . "\n", FILE_APPEND);
    $required = [
        'firstName', 'lastName', 'dateOfBirth', 'gender', 'admissionNo', 'admissionDate',
        'address', 'city', 'user_id'
    ];
    $errors = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[] = "$field is required";
        }
    }
    if (!empty($errors)) {
        file_put_contents(__DIR__ . '/student_debug.log', "UPDATE ERRORS: " . json_encode($errors) . "\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => implode(', ', $errors)]);
        exit;
    }
    
    // Handle photo upload if present
    $photoPath = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png'];
        if (in_array(strtolower($ext), $allowed)) {
            $photoPath = 'uploads/students/' . uniqid('student_') . '.' . $ext;
            if (!is_dir('uploads/students')) {
                mkdir('uploads/students', 0777, true);
            }
            move_uploaded_file($_FILES['photo']['tmp_name'], $photoPath);
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    try {
        $userId = intval($_POST['user_id']);
        
        // Get full name for users table
        $fullName = $_POST['firstName'];
        if (!empty($_POST['middleName'])) {
            $fullName .= ' ' . $_POST['middleName'];
        }
        $fullName .= ' ' . $_POST['lastName'];
        
        // Update password if provided
        if (!empty($_POST['password'])) {
            $passwordHash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, password_hash = ? WHERE id = ?");
            $stmt->bind_param('ssi', $fullName, $passwordHash, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ? WHERE id = ?");
            $stmt->bind_param('si', $fullName, $userId);
        }
        $stmt->execute();
        $stmt->close();
        
        // Get existing student record
        $stmt = $conn->prepare("SELECT * FROM students WHERE user_id = ?");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $existingStudent = $result->fetch_assoc();
        $stmt->close();
        
        if (!$existingStudent) {
            throw new Exception("Student record not found for user ID: $userId");
        }
        
        // Check if gender_code exists in genders table
        $gender = $_POST['gender'];
        $genderCode = null;
        $checkGender = $conn->prepare("SELECT code FROM genders WHERE code = ?");
        $checkGender->bind_param("s", $gender);
        $checkGender->execute();
        $checkGender->store_result();
        if ($checkGender->num_rows > 0) {
            $genderCode = $gender;
        }
        $checkGender->close();
        
        // Check if blood_group exists in blood_groups table
        $bloodGroup = isset($_POST['bloodGroup']) ? $_POST['bloodGroup'] : null;
        $bloodGroupCode = null;
        if ($bloodGroup) {
            $checkBlood = $conn->prepare("SELECT code FROM blood_groups WHERE code = ?");
            $checkBlood->bind_param("s", $bloodGroup);
            $checkBlood->execute();
            $checkBlood->store_result();
            if ($checkBlood->num_rows > 0) {
                $bloodGroupCode = $bloodGroup;
            }
            $checkBlood->close();
        }
        
        // Build the SQL query dynamically
        $updateFields = [
            'full_name = ?',
            'first_name = ?',
            'middle_name = ?',
            'last_name = ?',
            'gender_code = ?',
            'dob = ?',
            'admission_number = ?',
            'admission_date = ?',
            'roll_number = ?',
            'address = ?',
            'city = ?',
            'postal_code = ?',
            'blood_group_code = ?',
            'nationality = ?',
            'father_name = ?',
            'father_occupation = ?',
            'father_phone = ?',
            'mother_name = ?',
            'mother_occupation = ?',
            'mother_phone = ?',
            'guardian_name = ?',
            'guardian_relation = ?',
            'guardian_phone = ?',
            'mobile = ?',
            'medical_conditions = ?',
            'updated_at = NOW()'
        ];
        
        // Add photo only if uploaded
        if ($photoPath) {
            $updateFields[] = 'photo = ?';
        }
        
        $sql = "UPDATE students SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
        
        // Prepare bind types and values
        $bindTypes = 'sssssssssssssssssssssssss';
        $bindValues = [
            $fullName,
            $_POST['firstName'],
            $_POST['middleName'] ?? '',
            $_POST['lastName'],
            $genderCode,
            $_POST['dateOfBirth'],
            $_POST['admissionNo'],
            $_POST['admissionDate'],
            $_POST['rollNumber'] ?? '',
            $_POST['address'],
            $_POST['city'],
            $_POST['postalCode'] ?? '',
            $bloodGroupCode,
            $_POST['nationality'] ?? '',
            $_POST['fatherName'] ?? '',
            $_POST['fatherOccupation'] ?? '',
            $_POST['fatherPhone'] ?? '',
            $_POST['motherName'] ?? '',
            $_POST['motherOccupation'] ?? '',
            $_POST['motherPhone'] ?? '',
            $_POST['guardianName'] ?? '',
            $_POST['guardianRelation'] ?? '',
            $_POST['guardianPhone'] ?? '',
            $_POST['phone'] ?? '',
            $_POST['medicalConditions'] ?? ''
        ];
        
        // Add photo value if present
        if ($photoPath) {
            $bindTypes .= 's';
            $bindValues[] = $photoPath;
        }
        
        // Add user_id for WHERE clause
        $bindTypes .= 'i';
        $bindValues[] = $userId;
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($bindTypes, ...$bindValues);
        $result = $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        if (!$result) {
            throw new Exception("Error updating student: " . $conn->error);
        }
        
        $conn->commit();
        
        echo json_encode([
            "success" => true, 
            "message" => "Student updated successfully.",
            "affected_rows" => $affected,
            "student_id" => $userId
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        file_put_contents(__DIR__ . '/student_debug.log', "UPDATE EXCEPTION: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(["success" => false, "message" => "Error updating student: " . $e->getMessage()]);
    }
    exit;
}

// View single student
if (isset($_GET['view'])) {
    $id = intval($_GET['view']);
    $stmt = $conn->prepare("
        SELECT s.*, u.email, u.status as user_status, c.name as class_name, sec.name as section_name,
        g.label as gender_label, bg.label as blood_group_label
        FROM students s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN genders g ON s.gender_code = g.code
        LEFT JOIN blood_groups bg ON s.blood_group_code = bg.code
        WHERE s.user_id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Student not found"]);
    }
    exit;
}

// Fetch academic years
if (isset($_GET['fetch_academic_years'])) {
    // Log query for debugging
    file_put_contents(__DIR__ . '/academic_years_debug.log', date('Y-m-d H:i:s') . " - Fetching academic years\n", FILE_APPEND);
    
    // First check if is_current column exists
    $checkIsCurrentCol = $conn->query("SHOW COLUMNS FROM academic_years LIKE 'is_current'");
    $hasIsCurrentCol = ($checkIsCurrentCol && $checkIsCurrentCol->num_rows > 0);
    
    if ($hasIsCurrentCol) {
        // Use original query if is_current column exists
        $sql = "SELECT id, name, is_current FROM academic_years ORDER BY name DESC";
    } else {
        // Fall back to query without is_current if column doesn't exist
        $sql = "SELECT id, name, 0 AS is_current FROM academic_years ORDER BY name DESC";
        
        // Add is_current column
        $alterSql = "ALTER TABLE academic_years ADD COLUMN is_current TINYINT(1) NOT NULL DEFAULT 0";
        if ($conn->query($alterSql)) {
            file_put_contents(__DIR__ . '/academic_years_debug.log', "Added missing is_current column\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . '/academic_years_debug.log', "Failed to add is_current column: " . $conn->error . "\n", FILE_APPEND);
        }
    }
    
    file_put_contents(__DIR__ . '/academic_years_debug.log', "SQL: $sql\n", FILE_APPEND);
    
    $result = $conn->query($sql);
    
    if (!$result) {
        file_put_contents(__DIR__ . '/academic_years_debug.log', "Query Error: " . $conn->error . "\n", FILE_APPEND);
    }
    
    $years = [];
    if ($result && $result->num_rows > 0) {
        file_put_contents(__DIR__ . '/academic_years_debug.log', "Found " . $result->num_rows . " academic years\n", FILE_APPEND);
        while ($row = $result->fetch_assoc()) {
            $years[] = $row;
            file_put_contents(__DIR__ . '/academic_years_debug.log', "Year: " . json_encode($row) . "\n", FILE_APPEND);
        }
    } else {
        file_put_contents(__DIR__ . '/academic_years_debug.log', "No academic years found\n", FILE_APPEND);
    }
    
    header('Content-Type: application/json');
    echo json_encode($years);
    exit;
}

// Fetch classes
if (isset($_GET['fetch_classes'])) {
    $sql = "SELECT id, name FROM classes ORDER BY name ASC";
    $result = $conn->query($sql);
    
    $classes = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $classes[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($classes);
    exit;
}

// Fetch sections for a class
if (isset($_GET['fetch_sections'])) {
    $classId = (int) $_GET['fetch_sections'];
    
    $sql = "SELECT id, name, capacity FROM sections WHERE class_id = ? ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $classId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $sections = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $sections[] = $row;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($sections);
    exit;
}

// CSV export
if (isset($_GET['export']) && $_GET['export'] == '1') {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    $class = isset($_GET['class']) ? trim($_GET['class']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $where = [];
    $params = [];
    $types = '';
    if ($search !== '') {
        $where[] = "(full_name LIKE ? OR admission_number LIKE ? OR father_name LIKE ? OR mobile LIKE ?)";
        $params = array_merge($params, array_fill(0, 4, "%$search%"));
        $types .= str_repeat('s', 4);
    }
    if ($class !== '') {
        $where[] = "class_id = ?";
        $params[] = $class;
        $types .= 's';
    }
    if ($status !== '') {
        $where[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
    
    // Get data with joined table info for better export
    $sql = "SELECT 
            s.*, 
            c.name as class_name, 
            sec.name as section_name,
            g.label as gender,
            bg.label as blood_group,
            ay.name as academic_year_name
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN genders g ON s.gender_code = g.code
        LEFT JOIN blood_groups bg ON s.blood_group_code = bg.code
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        $whereSql 
        ORDER BY s.user_id DESC";
        
    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="students_export.csv"');
    $out = fopen('php://output', 'w');
    $header = false;
    while ($row = $result->fetch_assoc()) {
        if (!$header) {
            fputcsv($out, array_keys($row));
            $header = true;
        }
        fputcsv($out, $row);
    }
    fclose($out);
    $stmt->close();
    exit;
}

// Handle student transfers
if (isset($_POST['action']) && ($_POST['action'] === 'transfer' || $_POST['action'] === 'promote')) {
    $action = $_POST['action'];
    $response = ['success' => false, 'message' => 'Invalid request'];
    
    // Check required parameters
    if (!isset($_POST['student_ids']) || !isset($_POST['effective_date'])) {
        $response['message'] = 'Missing required parameters';
        echo json_encode($response);
        exit;
    }
    
    $studentIds = json_decode($_POST['student_ids'], true);
    $effectiveDate = $_POST['effective_date'];
    
    if (!is_array($studentIds) || empty($studentIds)) {
        $response['message'] = 'No students selected';
        echo json_encode($response);
        exit;
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        $successCount = 0;
        $errors = [];
        
        if ($action === 'promote') {
            // For promotion to next class
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
                    $errors[] = "Student ID $studentId not found";
                    continue;
                }
                
                $student = $result->fetch_assoc();
                $fromClassId = $student['class_id'];
                $fromSectionId = $student['section_id'];
                
                // Find next class
                $stmt = $conn->prepare("SELECT name FROM classes WHERE id = ?");
                $stmt->bind_param("i", $fromClassId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $errors[] = "Class not found for student ID $studentId";
                    continue;
                }
                
                $currentClass = $result->fetch_assoc();
                $currentClassName = $currentClass['name'];
                
                // Try to extract a number from the class name
                if (preg_match('/(\d+)/', $currentClassName, $matches)) {
                    $currentNumber = (int) $matches[0];
                    $nextNumber = $currentNumber + 1;
                    
                    // Search for a class with the next number
                    $nextClassName = str_replace($currentNumber, $nextNumber, $currentClassName);
                    
                    $stmt = $conn->prepare("SELECT id FROM classes WHERE name = ?");
                    $stmt->bind_param("s", $nextClassName);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        $errors[] = "Could not find next class for student ID $studentId";
                        continue;
                    }
                    
                    $nextClass = $result->fetch_assoc();
                    $toClassId = $nextClass['id'];
                    
                    // Find a section in the next class
                    $stmt = $conn->prepare("SELECT id FROM sections WHERE class_id = ? LIMIT 1");
                    $stmt->bind_param("i", $toClassId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 0) {
                        $errors[] = "No sections found for the next class for student ID $studentId";
                        continue;
                    }
                    
                    $section = $result->fetch_assoc();
                    $toSectionId = $section['id'];
                    
                    // Create transfer record
                    $reason = "Annual Promotion";
                    $stmt = $conn->prepare("INSERT INTO student_transfers 
                        (student_id, from_class_id, from_section_id, to_class_id, to_section_id, transfer_date, reason, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                    
                    $stmt->bind_param("iiiiiss", $studentId, $fromClassId, $fromSectionId, $toClassId, $toSectionId, $effectiveDate, $reason);
                    
                    if (!$stmt->execute()) {
                        $errors[] = "Failed to create transfer record for student ID $studentId: " . $stmt->error;
                        continue;
                    }
                    
                    // Update student's class and section
                    $stmt = $conn->prepare("UPDATE students SET class_id = ?, section_id = ?, academic_year_id = ?, updated_at = NOW() WHERE user_id = ?");
                    $stmt->bind_param("iiii", $toClassId, $toSectionId, $targetAcademicYearId, $studentId);
                    
                    if (!$stmt->execute()) {
                        $errors[] = "Failed to update class for student ID $studentId: " . $stmt->error;
                        continue;
                    }
                    
                    $successCount++;
                } else {
                    $errors[] = "Could not determine next class for student ID $studentId";
                }
            }
        } else {
            // Transfer to specific class/section
            if (!isset($_POST['target_class_id']) || !isset($_POST['target_section_id'])) {
                throw new Exception('Target class and section are required for transfer');
            }
            
            $toClassId = (int) $_POST['target_class_id'];
            $toSectionId = (int) $_POST['target_section_id'];
            
            // Verify target class and section exist
            $stmt = $conn->prepare("SELECT c.name, s.name as section_name FROM classes c JOIN sections s ON c.id = s.class_id WHERE c.id = ? AND s.id = ?");
            $stmt->bind_param("ii", $toClassId, $toSectionId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                throw new Exception('Invalid target class or section');
            }
            
            foreach ($studentIds as $studentId) {
                // Get student's current class and section
                $stmt = $conn->prepare("SELECT class_id, section_id, academic_year_id FROM students WHERE user_id = ?");
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows === 0) {
                    $errors[] = "Student ID $studentId not found";
                    continue;
                }
                
                $student = $result->fetch_assoc();
                $fromClassId = $student['class_id'];
                $fromSectionId = $student['section_id'];
                $academicYearId = $student['academic_year_id'];
                
                // Create transfer record
                $reason = "Transfer to different class/section";
                $stmt = $conn->prepare("INSERT INTO student_transfers 
                    (student_id, from_class_id, from_section_id, to_class_id, to_section_id, transfer_date, reason, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                
                $stmt->bind_param("iiiiiss", $studentId, $fromClassId, $fromSectionId, $toClassId, $toSectionId, $effectiveDate, $reason);
                
                if (!$stmt->execute()) {
                    $errors[] = "Failed to create transfer record for student ID $studentId: " . $stmt->error;
                    continue;
                }
                
                // Update student's class and section
                $stmt = $conn->prepare("UPDATE students SET class_id = ?, section_id = ?, updated_at = NOW() WHERE user_id = ?");
                $stmt->bind_param("iii", $toClassId, $toSectionId, $studentId);
                
                if (!$stmt->execute()) {
                    $errors[] = "Failed to update class for student ID $studentId: " . $stmt->error;
                    continue;
                }
                
                $successCount++;
            }
        }
        
        // Commit or rollback based on success
        if ($successCount > 0) {
            $conn->commit();
            $response = [
                'success' => true, 
                'success_count' => $successCount,
                'errors' => $errors
            ];
        } else {
            $conn->rollback();
            $response = [
                'success' => false, 
                'message' => 'No students were processed successfully',
                'errors' => $errors
            ];
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $response = [
            'success' => false, 
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    
    echo json_encode($response);
    exit;
}

// Add academic year (endpoint for AJAX)
if (isset($_POST['action']) && $_POST['action'] == 'add_academic_year') {
    // Log the request for debugging
    file_put_contents(__DIR__ . '/academic_year_debug.log', date('Y-m-d H:i:s') . " - Add academic year request\n", FILE_APPEND);
    file_put_contents(__DIR__ . '/academic_year_debug.log', "POST data: " . json_encode($_POST) . "\n", FILE_APPEND);
    
    // Get parameters
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $startDate = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
    $endDate = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
    $isCurrent = isset($_POST['is_current']) ? (int)$_POST['is_current'] : 0;
    
    // Validate parameters
    if (empty($name)) {
        file_put_contents(__DIR__ . '/academic_year_debug.log', "Error: Academic year name is required\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Academic year name is required']);
        exit;
    }
    
    // If no dates provided, generate them based on the name (e.g., "2023-2024")
    if (empty($startDate) || empty($endDate)) {
        file_put_contents(__DIR__ . '/academic_year_debug.log', "Generating dates from name: $name\n", FILE_APPEND);
        
        if (preg_match('/(\d{4})[^\d]*(\d{2,4})/', $name, $matches)) {
            $startYear = $matches[1];
            $endYear = $matches[2];
            
            // If end year is just 2 digits (e.g., "24" in "2023-24")
            if (strlen($endYear) == 2) {
                $endYear = substr($startYear, 0, 2) . $endYear;
            }
            
            $startDate = $startYear . '-06-01'; // June 1st of start year
            $endDate = $endYear . '-05-31';     // May 31st of end year
            
            file_put_contents(__DIR__ . '/academic_year_debug.log', "Generated start_date: $startDate, end_date: $endDate\n", FILE_APPEND);
        } else {
            file_put_contents(__DIR__ . '/academic_year_debug.log', "Error: Invalid academic year format\n", FILE_APPEND);
            echo json_encode(['success' => false, 'message' => 'Invalid academic year format. Expected format like 2023-2024 or 2023-24']);
            exit;
        }
    }
    
    // Check if academic year with same name already exists
    $checkStmt = $conn->prepare("SELECT id FROM academic_years WHERE name = ?");
    $checkStmt->bind_param("s", $name);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        file_put_contents(__DIR__ . '/academic_year_debug.log', "Error: Academic year with name '$name' already exists\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => "Academic year '$name' already exists"]);
        $checkStmt->close();
        exit;
    }
    $checkStmt->close();
    
    try {
        // Check for is_current column
        $checkColumn = $conn->query("SHOW COLUMNS FROM academic_years LIKE 'is_current'");
        $hasIsCurrentColumn = ($checkColumn && $checkColumn->num_rows > 0);
        
        // Add the column if it doesn't exist
        if (!$hasIsCurrentColumn) {
            file_put_contents(__DIR__ . '/academic_year_debug.log', "Adding missing is_current column\n", FILE_APPEND);
            $alterSql = "ALTER TABLE academic_years ADD COLUMN is_current TINYINT(1) NOT NULL DEFAULT 0";
            
            if (!$conn->query($alterSql)) {
                file_put_contents(__DIR__ . '/academic_year_debug.log', "Error adding is_current column: " . $conn->error . "\n", FILE_APPEND);
                // Continue anyway - we'll use simpler SQL without is_current
            } else {
                $hasIsCurrentColumn = true;
                file_put_contents(__DIR__ . '/academic_year_debug.log', "is_current column added successfully\n", FILE_APPEND);
            }
        }
        
        // If marking as current and the is_current column exists, update other years
        if ($isCurrent && $hasIsCurrentColumn) {
            file_put_contents(__DIR__ . '/academic_year_debug.log', "Setting other academic years as not current\n", FILE_APPEND);
            $conn->query("UPDATE academic_years SET is_current = 0");
        }
        
        // Insert the new academic year - different SQL based on column existence
        if ($hasIsCurrentColumn) {
            $sql = "INSERT INTO academic_years (name, start_date, end_date, is_current) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                file_put_contents(__DIR__ . '/academic_year_debug.log', "Error preparing full statement: " . $conn->error . "\n", FILE_APPEND);
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("sssi", $name, $startDate, $endDate, $isCurrent);
        } else {
            // Fallback without is_current
            $sql = "INSERT INTO academic_years (name, start_date, end_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                file_put_contents(__DIR__ . '/academic_year_debug.log', "Error preparing simplified statement: " . $conn->error . "\n", FILE_APPEND);
                throw new Exception("Failed to prepare statement: " . $conn->error);
            }
            
            $stmt->bind_param("sss", $name, $startDate, $endDate);
        }
        
        file_put_contents(__DIR__ . '/academic_year_debug.log', "Using SQL: $sql\n", FILE_APPEND);
        
        if ($stmt->execute()) {
            $newId = $conn->insert_id;
            $stmt->close();
            file_put_contents(__DIR__ . '/academic_year_debug.log', "Academic year added successfully with ID: $newId\n", FILE_APPEND);
            echo json_encode([
                'success' => true, 
                'message' => 'Academic year added successfully',
                'academic_year' => [
                    'id' => $newId,
                    'name' => $name,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'is_current' => $hasIsCurrentColumn ? $isCurrent : 0
                ]
            ]);
        } else {
            file_put_contents(__DIR__ . '/academic_year_debug.log', "Error executing statement: " . $stmt->error . "\n", FILE_APPEND);
            throw new Exception("Failed to add academic year: " . $stmt->error);
        }
    } catch (Exception $e) {
        file_put_contents(__DIR__ . '/academic_year_debug.log', "Exception: " . $e->getMessage() . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
    
    exit;
}

echo json_encode(["success" => false, "message" => "Invalid request."]);
?>
