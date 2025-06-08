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
    header("Location: ../login.php");
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
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-control {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .alerts {
            margin-bottom: 20px;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .role-fields {
            display: none;
            border: 1px solid #eee;
            padding: 15px;
            margin-top: 15px;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 0;
        }
        
        .form-row .form-group {
            flex: 1;
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