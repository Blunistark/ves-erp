<?php
// Include necessary files
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/con.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../index.php");
    exit;
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: user-add.php");
        exit;
    }
    
    // Get form data
    $full_name = isset($_POST['full_name']) ? sanitizeInput($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $role = isset($_POST['role']) ? sanitizeInput($_POST['role']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    if (empty($role)) {
        $errors[] = "Role is required.";
    } elseif (!in_array($role, ['admin', 'teacher', 'student', 'parent'])) {
        $errors[] = "Invalid role selected.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if email already exists
    $email_check = executeQuery("SELECT id FROM users WHERE email = ?", "s", [$email]);
    if (!empty($email_check)) {
        $errors[] = "Email address is already in use.";
    }
    
    // Role-specific validations
    $role_specific_data = [];
    
    if ($role === 'teacher') {
        $employee_number = isset($_POST['employee_number']) ? sanitizeInput($_POST['employee_number']) : '';
        $joined_date = isset($_POST['joined_date']) ? sanitizeInput($_POST['joined_date']) : '';
        $qualification = isset($_POST['qualification']) ? sanitizeInput($_POST['qualification']) : '';
        
        if (empty($employee_number)) {
            $errors[] = "Employee number is required for teachers.";
        } else {
            // Check if employee number already exists
            $emp_check = executeQuery("SELECT user_id FROM teachers WHERE employee_number = ?", "s", [$employee_number]);
            if (!empty($emp_check)) {
                $errors[] = "Employee number is already in use.";
            }
        }
        
        if (empty($joined_date)) {
            $errors[] = "Joined date is required for teachers.";
        }
        
        $role_specific_data = [
            'employee_number' => $employee_number,
            'joined_date' => $joined_date,
            'qualification' => $qualification
        ];
    } elseif ($role === 'student') {
        $admission_number = isset($_POST['admission_number']) ? sanitizeInput($_POST['admission_number']) : '';
        $admission_date = isset($_POST['admission_date']) ? sanitizeInput($_POST['admission_date']) : '';
        $class_id = isset($_POST['class_id']) ? (int)$_POST['class_id'] : 0;
        $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : 0;
        $roll_number = isset($_POST['roll_number']) ? (int)$_POST['roll_number'] : 0;
        $gender = isset($_POST['gender']) ? sanitizeInput($_POST['gender']) : '';
        $dob = isset($_POST['dob']) ? sanitizeInput($_POST['dob']) : '';
        
        if (empty($admission_number)) {
            $errors[] = "Admission number is required for students.";
        } else {
            // Check if admission number already exists
            $adm_check = executeQuery("SELECT user_id FROM students WHERE admission_number = ?", "s", [$admission_number]);
            if (!empty($adm_check)) {
                $errors[] = "Admission number is already in use.";
            }
        }
        
        if (empty($admission_date)) {
            $errors[] = "Admission date is required for students.";
        }
        
        if ($class_id <= 0) {
            $errors[] = "Class is required for students.";
        }
        
        if ($section_id <= 0) {
            $errors[] = "Section is required for students.";
        }
        
        if ($roll_number <= 0) {
            $errors[] = "Roll number is required for students.";
        } else {
            // Check if roll number already exists in same class and section
            $roll_check = executeQuery(
                "SELECT user_id FROM students WHERE class_id = ? AND section_id = ? AND roll_number = ?", 
                "iii", 
                [$class_id, $section_id, $roll_number]
            );
            if (!empty($roll_check)) {
                $errors[] = "Roll number is already assigned in this class and section.";
            }
        }
        
        $role_specific_data = [
            'admission_number' => $admission_number,
            'admission_date' => $admission_date,
            'class_id' => $class_id,
            'section_id' => $section_id,
            'roll_number' => $roll_number,
            'gender_code' => $gender,
            'dob' => $dob
        ];
    } elseif ($role === 'parent') {
        $student_user_id = isset($_POST['student_user_id']) ? (int)$_POST['student_user_id'] : 0;
        $relationship = isset($_POST['relationship']) ? sanitizeInput($_POST['relationship']) : '';
        $phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
        
        if ($student_user_id <= 0) {
            $errors[] = "Student is required for parent accounts.";
        }
        
        if (empty($relationship)) {
            $errors[] = "Relationship is required for parents.";
        }
        
        if (empty($phone)) {
            $errors[] = "Phone number is required for parents.";
        }
        
        $role_specific_data = [
            'student_user_id' => $student_user_id,
            'relationship' => $relationship,
            'phone' => $phone
        ];
    }
    
    // If no errors, add user to database
    if (empty($errors)) {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Begin transaction
        $conn = getDbConnection();
        $conn->begin_transaction();
        
        try {
            // Insert into users table
            $result = executeQuery(
                "INSERT INTO users (email, password_hash, full_name, role, status, created_at) VALUES (?, ?, ?, ?, 'active', CURRENT_TIMESTAMP)",
                "ssss",
                [$email, $password_hash, $full_name, $role]
            );
            
            if (!$result || !isset($result['insert_id']) || $result['insert_id'] <= 0) {
                throw new Exception("Failed to create user account.");
            }
            
            $user_id = $result['insert_id'];
            
            // Insert role-specific data
            if ($role === 'teacher') {
                $teacher_result = executeQuery(
                    "INSERT INTO teachers (user_id, employee_number, joined_date, qualification) VALUES (?, ?, ?, ?)",
                    "isss",
                    [$user_id, $role_specific_data['employee_number'], $role_specific_data['joined_date'], $role_specific_data['qualification']]
                );
                
                if (!$teacher_result) {
                    throw new Exception("Failed to create teacher profile.");
                }
            } elseif ($role === 'student') {
                // Current academic year
                $academic_year = executeQuery("SELECT id FROM academic_years WHERE CURRENT_DATE BETWEEN start_date AND end_date LIMIT 1");
                $academic_year_id = !empty($academic_year) ? $academic_year[0]['id'] : null;
                
                $student_result = executeQuery(
                    "INSERT INTO students (user_id, admission_number, admission_date, class_id, section_id, roll_number, full_name, gender_code, dob, academic_year_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
                    "issiiiissi",
                    [
                        $user_id, 
                        $role_specific_data['admission_number'], 
                        $role_specific_data['admission_date'],
                        $role_specific_data['class_id'],
                        $role_specific_data['section_id'],
                        $role_specific_data['roll_number'],
                        $full_name,
                        $role_specific_data['gender_code'],
                        $role_specific_data['dob'],
                        $academic_year_id
                    ]
                );
                
                if (!$student_result) {
                    throw new Exception("Failed to create student profile.");
                }
            } elseif ($role === 'parent') {
                $parent_result = executeQuery(
                    "INSERT INTO parent_accounts (user_id, student_user_id, relationship, phone, email) VALUES (?, ?, ?, ?, ?)",
                    "iisss",
                    [
                        $user_id, 
                        $role_specific_data['student_user_id'], 
                        $role_specific_data['relationship'],
                        $role_specific_data['phone'],
                        $email
                    ]
                );
                
                if (!$parent_result) {
                    throw new Exception("Failed to create parent profile.");
                }
            }
            
            // Log audit
            logAudit('users', $user_id, 'INSERT');
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "User created successfully.";
            header("Location: users.php");
            exit;
            
        } catch (Exception $e) {
            // Roll back on error
            $conn->rollback();
            $errors[] = $e->getMessage();
        }
    }
}

// Get available classes
$classes = executeQuery("SELECT id, name FROM classes ORDER BY name");

// Get available sections
$sections = executeQuery("SELECT id, class_id, name FROM sections ORDER BY class_id, name");

// Get available students for parent linking
$students = executeQuery("SELECT u.id, s.full_name, s.admission_number FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student' ORDER BY s.full_name");

// Get gender options
$genders = executeQuery("SELECT code, label FROM genders ORDER BY label");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add User - School ERP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" href="css/users.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
   <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background-color: #f5f6fa;
        color: #2c3e50;
        line-height: 1.6;
    }
    
    .container {
        display: flex;
        min-height: 100vh;
    }
    
    .main-content {
        flex: 1;
        margin-left: 250px; /* Adjust based on sidebar width */
        transition: margin-left 0.3s ease;
        background-color: #f5f6fa;
    }
    
    .sidebar.collapsed ~ .main-content {
        margin-left: 80px; /* Adjust for collapsed sidebar */
    }
    
    .header {
        background: #ffffff;
        padding: 0 24px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #e1e8ed;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        position: sticky;
        top: 0;
        z-index: 100;
    }
    
    .header h1 {
        font-size: 24px;
        font-weight: 600;
        color: #2c3e50;
        margin-left: 16px;
    }
    
    .menu-toggle {
        display: flex;
        flex-direction: column;
        cursor: pointer;
        padding: 8px;
        border-radius: 6px;
        transition: background-color 0.2s;
    }
    
    .menu-toggle:hover {
        background-color: #f8f9fa;
    }
    
    .menu-toggle .bar {
        width: 20px;
        height: 2px;
        background-color: #6c757d;
        margin: 2px 0;
        transition: 0.3s;
        border-radius: 1px;
    }
    
    .user-dropdown {
        position: relative;
    }
    
    .user-dropdown-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
        color: #495057;
    }
    
    .user-dropdown-toggle:hover {
        background: #e9ecef;
        border-color: #adb5bd;
    }
    
    .user-dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        min-width: 150px;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s;
        z-index: 1000;
        margin-top: 4px;
    }
    
    .user-dropdown-menu.active {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .user-dropdown-menu a {
        display: block;
        padding: 12px 16px;
        color: #495057;
        text-decoration: none;
        transition: background-color 0.2s;
        font-size: 14px;
        border-bottom: 1px solid #f8f9fa;
    }
    
    .user-dropdown-menu a:last-child {
        border-bottom: none;
    }
    
    .user-dropdown-menu a:hover {
        background-color: #f8f9fa;
    }
    
    .content {
        padding: 24px;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e1e8ed;
        overflow: hidden;
    }
    
    .card-header {
        padding: 20px 24px;
        border-bottom: 1px solid #e1e8ed;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8f9fa;
    }
    
    .card-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .card-body {
        padding: 24px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group:last-child {
        margin-bottom: 0;
    }
    
    label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }
    
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s;
        background: #ffffff;
        color: #374151;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background: #ffffff;
    }
    
    .form-control:hover {
        border-color: #9ca3af;
    }
    
    select.form-control {
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 8px center;
        background-repeat: no-repeat;
        background-size: 16px 12px;
        padding-right: 40px;
        appearance: none;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 0;
    }
    
    .form-row .form-group {
        margin-bottom: 20px;
    }
    
    .role-fields {
        display: none;
        background: #f8f9fa;
        border: 1px solid #e1e8ed;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        animation: slideDown 0.3s ease-out;
    }
    
    .role-fields h3 {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
        padding-bottom: 8px;
        border-bottom: 2px solid #e1e8ed;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        text-align: center;
        font-family: inherit;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
    }
    
    .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        transform: translateY(-1px);
    }
    
    .btn-secondary {
        background: #ffffff;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }
    
    .btn-secondary:hover {
        background: #f8f9fa;
        border-color: #adb5bd;
        transform: translateY(-1px);
    }
    
    .alerts {
        margin-bottom: 20px;
    }
    
    .alert {
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    
    .alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
    }
    
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #166534;
    }
    
    small {
        display: block;
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
    }
    
    /* Animations */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
            max-height: 0;
        }
        to {
            opacity: 1;
            transform: translateY(0);
            max-height: 1000px;
        }
    }
    
    /* Loading states */
    .form-control:disabled {
        background-color: #f9fafb;
        color: #6b7280;
        cursor: not-allowed;
    }
    
    /* Focus styles for accessibility */
    .btn:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
    }
    
    /* Required field indicator */
    .required {
        color: #ef4444;
    }
    
    /* Responsive Design */
    @media (max-width: 1024px) {
        .main-content {
            margin-left: 0;
        }
        
        .content {
            padding: 16px;
        }
        
        .card-header,
        .card-body {
            padding: 16px;
        }
        
        .form-row {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }
    
    @media (max-width: 640px) {
        .header {
            padding: 0 16px;
        }
        
        .header h1 {
            font-size: 20px;
            margin-left: 8px;
        }
        
        .card-header {
            flex-direction: column;
            gap: 12px;
            align-items: flex-start;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
        
        .card-header .btn {
            width: auto;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <?php include 'sidebar.php'; ?>
        
        <div class="main-content">
            <div class="header">
                <div class="menu-toggle">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <h1>Add New User</h1>
                <div class="user-dropdown">
                    <button class="user-dropdown-toggle">
                        <span><?php echo $_SESSION['full_name'] ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="user-dropdown-menu">
                        <a href="adminprofile.php">Profile</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
            
            <div class="content">
                <div class="card">
                    <div class="card-header">
                        <h2>Add User</h2>
                        <a href="users.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Users</a>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alerts">
                                <?php foreach ($errors as $error): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" name="email" id="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="role">User Role *</label>
                                    <select name="role" id="role" class="form-control" required>
                                        <option value="">Select Role</option>
                                        <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                                        <option value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                                        <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'selected' : ''; ?>>Student</option>
                                        <option value="parent" <?php echo (isset($_POST['role']) && $_POST['role'] === 'parent') ? 'selected' : ''; ?>>Parent</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="password">Password *</label>
                                    <input type="password" name="password" id="password" class="form-control" required>
                                    <small>Minimum 8 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm Password *</label>
                                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                                </div>
                            </div>
                            
                            <!-- Teacher specific fields -->
                            <div id="teacher_fields" class="role-fields">
                                <h3>Teacher Information</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="employee_number">Employee Number *</label>
                                        <input type="text" name="employee_number" id="employee_number" class="form-control" value="<?php echo isset($_POST['employee_number']) ? htmlspecialchars($_POST['employee_number']) : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="joined_date">Joined Date *</label>
                                        <input type="date" name="joined_date" id="joined_date" class="form-control" value="<?php echo isset($_POST['joined_date']) ? htmlspecialchars($_POST['joined_date']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="qualification">Qualification</label>
                                    <input type="text" name="qualification" id="qualification" class="form-control" value="<?php echo isset($_POST['qualification']) ? htmlspecialchars($_POST['qualification']) : ''; ?>">
                                </div>
                            </div>
                            
                            <!-- Student specific fields -->
                            <div id="student_fields" class="role-fields">
                                <h3>Student Information</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="admission_number">Admission Number *</label>
                                        <input type="text" name="admission_number" id="admission_number" class="form-control" value="<?php echo isset($_POST['admission_number']) ? htmlspecialchars($_POST['admission_number']) : ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="admission_date">Admission Date *</label>
                                        <input type="date" name="admission_date" id="admission_date" class="form-control" value="<?php echo isset($_POST['admission_date']) ? htmlspecialchars($_POST['admission_date']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="class_id">Class *</label>
                                        <select name="class_id" id="class_id" class="form-control">
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>" <?php echo (isset($_POST['class_id']) && $_POST['class_id'] == $class['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($class['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="section_id">Section *</label>
                                        <select name="section_id" id="section_id" class="form-control">
                                            <option value="">Select Section</option>
                                            <!-- Will be populated via JavaScript -->
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="roll_number">Roll Number *</label>
                                        <input type="number" name="roll_number" id="roll_number" class="form-control" value="<?php echo isset($_POST['roll_number']) ? htmlspecialchars($_POST['roll_number']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select name="gender" id="gender" class="form-control">
                                            <option value="">Select Gender</option>
                                            <?php foreach ($genders as $gender): ?>
                                                <option value="<?php echo $gender['code']; ?>" <?php echo (isset($_POST['gender']) && $_POST['gender'] == $gender['code']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($gender['label']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="dob">Date of Birth</label>
                                        <input type="date" name="dob" id="dob" class="form-control" value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Parent specific fields -->
                            <div id="parent_fields" class="role-fields">
                                <h3>Parent Information</h3>
                                
                                <div class="form-group">
                                    <label for="student_user_id">Child/Student *</label>
                                    <select name="student_user_id" id="student_user_id" class="form-control">
                                        <option value="">Select Student</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>" <?php echo (isset($_POST['student_user_id']) && $_POST['student_user_id'] == $student['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['full_name']) . ' (' . htmlspecialchars($student['admission_number']) . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="relationship">Relationship *</label>
                                        <select name="relationship" id="relationship" class="form-control">
                                            <option value="">Select Relationship</option>
                                            <option value="father" <?php echo (isset($_POST['relationship']) && $_POST['relationship'] === 'father') ? 'selected' : ''; ?>>Father</option>
                                            <option value="mother" <?php echo (isset($_POST['relationship']) && $_POST['relationship'] === 'mother') ? 'selected' : ''; ?>>Mother</option>
                                            <option value="other" <?php echo (isset($_POST['relationship']) && $_POST['relationship'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Phone Number *</label>
                                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
                                <a href="users.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Include JavaScript files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    // All sections data from PHP
    const sectionsData = <?php echo json_encode($sections); ?>;
    
    $(document).ready(function() {
        // Toggle sidebar
        $('.menu-toggle').click(function() {
            $('.sidebar').toggleClass('collapsed');
            document.cookie = "sidebar_collapsed=" + $('.sidebar').hasClass('collapsed') + "; path=/";
        });
        
        // User dropdown
        $('.user-dropdown-toggle').click(function() {
            $('.user-dropdown-menu').toggleClass('active');
        });
        
        // Close dropdown when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('.user-dropdown').length) {
                $('.user-dropdown-menu').removeClass('active');
            }
        });
        
        // Toggle role-specific fields
        $('#role').change(function() {
            const role = $(this).val();
            $('.role-fields').hide();
            
            if (role === 'teacher') {
                $('#teacher_fields').show();
            } else if (role === 'student') {
                $('#student_fields').show();
                updateSections(); // Update sections dropdown when class changes
            } else if (role === 'parent') {
                $('#parent_fields').show();
            }
        });
        
        // Trigger role change to show fields if value is already selected
        $('#role').trigger('change');
        
        // Update sections when class changes
        $('#class_id').change(updateSections);
        
        function updateSections() {
            const classId = $('#class_id').val();
            const sectionSelect = $('#section_id');
            
            // Clear current options
            sectionSelect.empty().append('<option value="">Select Section</option>');
            
            if (!classId) return;
            
            // Filter sections for the selected class
            const filteredSections = sectionsData.filter(section => section.class_id == classId);
            
            // Add filtered sections to dropdown
            filteredSections.forEach(section => {
                const selected = <?php echo isset($_POST['section_id']) ? $_POST['section_id'] : 0; ?> == section.id ? 'selected' : '';
                sectionSelect.append(`<option value="${section.id}" ${selected}>${section.name}</option>`);
            });
        }
        
        // Initialize sections dropdown
        updateSections();
    });
    </script>
</body>
</html>
