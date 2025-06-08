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

// Get user ID from URL
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate user ID
if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: users.php");
    exit;
}

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Get user data
$user_query = "SELECT id, email, full_name, role, status FROM users WHERE id = ?";
$user_result = executeQuery($user_query, "i", [$user_id]);

if (empty($user_result)) {
    $_SESSION['error'] = "User not found.";
    header("Location: users.php");
    exit;
}

$user = $user_result[0];

// Get role-specific data
$role_data = null;

if ($user['role'] === 'teacher') {
    $teacher_query = "SELECT * FROM teachers WHERE user_id = ?";
    $teacher_result = executeQuery($teacher_query, "i", [$user_id]);
    if (!empty($teacher_result)) {
        $role_data = $teacher_result[0];
    }
} elseif ($user['role'] === 'student') {
    $student_query = "SELECT * FROM students WHERE user_id = ?";
    $student_result = executeQuery($student_query, "i", [$user_id]);
    if (!empty($student_result)) {
        $role_data = $student_result[0];
    }
} elseif ($user['role'] === 'parent') {
    $parent_query = "SELECT * FROM parent_accounts WHERE user_id = ?";
    $parent_result = executeQuery($parent_query, "i", [$user_id]);
    if (!empty($parent_result)) {
        $role_data = $parent_result[0];
    }
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid request. Please try again.";
        header("Location: user-edit.php?id=$user_id");
        exit;
    }
    
    // Get form data
    $full_name = isset($_POST['full_name']) ? sanitizeInput($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
    $status = isset($_POST['status']) ? sanitizeInput($_POST['status']) : '';
    
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
    
    // Check if email already exists (excluding current user)
    $email_check = executeQuery("SELECT id FROM users WHERE email = ? AND id != ?", "si", [$email, $user_id]);
    if (!empty($email_check)) {
        $errors[] = "Email address is already in use by another user.";
    }
    
    // Role-specific validations
    if ($user['role'] === 'teacher') {
        $employee_number = isset($_POST['employee_number']) ? sanitizeInput($_POST['employee_number']) : '';
        $joined_date = isset($_POST['joined_date']) ? sanitizeInput($_POST['joined_date']) : '';
        $qualification = isset($_POST['qualification']) ? sanitizeInput($_POST['qualification']) : '';
        
        if (empty($employee_number)) {
            $errors[] = "Employee number is required for teachers.";
        } else {
            // Check if employee number already exists (excluding current user)
            $emp_check = executeQuery(
                "SELECT user_id FROM teachers WHERE employee_number = ? AND user_id != ?", 
                "si", 
                [$employee_number, $user_id]
            );
            if (!empty($emp_check)) {
                $errors[] = "Employee number is already in use.";
            }
        }
        
        if (empty($joined_date)) {
            $errors[] = "Joined date is required for teachers.";
        }
        
    } elseif ($user['role'] === 'student') {
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
            // Check if admission number already exists (excluding current user)
            $adm_check = executeQuery(
                "SELECT user_id FROM students WHERE admission_number = ? AND user_id != ?", 
                "si", 
                [$admission_number, $user_id]
            );
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
            // Check if roll number already exists in same class and section (excluding current user)
            $roll_check = executeQuery(
                "SELECT user_id FROM students WHERE class_id = ? AND section_id = ? AND roll_number = ? AND user_id != ?", 
                "iiii", 
                [$class_id, $section_id, $roll_number, $user_id]
            );
            if (!empty($roll_check)) {
                $errors[] = "Roll number is already assigned in this class and section.";
            }
        }
        
    } elseif ($user['role'] === 'parent') {
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
    }
    
    // If no errors, update user in database
    if (empty($errors)) {
        // Begin transaction
        $conn = getDbConnection();
        $conn->begin_transaction();
        
        try {
            // Update user table
            $result = executeQuery(
                "UPDATE users SET email = ?, full_name = ?, status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?",
                "sssi",
                [$email, $full_name, $status, $user_id]
            );
            
            // Update role-specific data
            if ($user['role'] === 'teacher' && $role_data) {
                $teacher_result = executeQuery(
                    "UPDATE teachers SET employee_number = ?, joined_date = ?, qualification = ? WHERE user_id = ?",
                    "sssi",
                    [$employee_number, $joined_date, $qualification, $user_id]
                );
                
                if (!$teacher_result) {
                    throw new Exception("Failed to update teacher profile.");
                }
                
            } elseif ($user['role'] === 'student' && $role_data) {
                $student_result = executeQuery(
                    "UPDATE students SET 
                        admission_number = ?, 
                        admission_date = ?,
                        class_id = ?,
                        section_id = ?,
                        roll_number = ?,
                        full_name = ?,
                        gender_code = ?,
                        dob = ?,
                        updated_at = CURRENT_TIMESTAMP
                     WHERE user_id = ?",
                    "ssiiiissi",
                    [
                        $admission_number, 
                        $admission_date,
                        $class_id,
                        $section_id,
                        $roll_number,
                        $full_name,
                        $gender,
                        $dob,
                        $user_id
                    ]
                );
                
                if (!$student_result) {
                    throw new Exception("Failed to update student profile.");
                }
                
            } elseif ($user['role'] === 'parent' && $role_data) {
                $parent_result = executeQuery(
                    "UPDATE parent_accounts SET 
                        student_user_id = ?, 
                        relationship = ?,
                        phone = ?,
                        email = ?
                     WHERE user_id = ?",
                    "isssi",
                    [
                        $student_user_id, 
                        $relationship,
                        $phone,
                        $email,
                        $user_id
                    ]
                );
                
                if (!$parent_result) {
                    throw new Exception("Failed to update parent profile.");
                }
            }
            
            // Log audit
            logAudit('users', $user_id, 'UPDATE');
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "User updated successfully.";
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
    <title>Edit User - School ERP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" href="css/styles.css">
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
                <h1>Edit User</h1>
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
                        <h2>Edit User: <?php echo htmlspecialchars($user['full_name']); ?></h2>
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
                        
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" name="full_name" id="full_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" name="email" id="email" class="form-control" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="role">User Role</label>
                                    <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select name="status" id="status" class="form-control" required>
                                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <?php if ($user['role'] === 'teacher' && $role_data): ?>
                            <!-- Teacher specific fields -->
                            <div class="role-fields">
                                <h3>Teacher Information</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="employee_number">Employee Number *</label>
                                        <input type="text" name="employee_number" id="employee_number" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['employee_number']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="joined_date">Joined Date *</label>
                                        <input type="date" name="joined_date" id="joined_date" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['joined_date']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="qualification">Qualification</label>
                                    <input type="text" name="qualification" id="qualification" class="form-control" 
                                           value="<?php echo htmlspecialchars($role_data['qualification'] ?? ''); ?>">
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user['role'] === 'student' && $role_data): ?>
                            <!-- Student specific fields -->
                            <div class="role-fields">
                                <h3>Student Information</h3>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="admission_number">Admission Number *</label>
                                        <input type="text" name="admission_number" id="admission_number" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['admission_number']); ?>" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="admission_date">Admission Date *</label>
                                        <input type="date" name="admission_date" id="admission_date" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['admission_date']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="class_id">Class *</label>
                                        <select name="class_id" id="class_id" class="form-control" required>
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>" <?php echo $role_data['class_id'] == $class['id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($class['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="section_id">Section *</label>
                                        <select name="section_id" id="section_id" class="form-control" required>
                                            <option value="">Select Section</option>
                                            <!-- Will be populated via JavaScript -->
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="roll_number">Roll Number *</label>
                                        <input type="number" name="roll_number" id="roll_number" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['roll_number']); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select name="gender" id="gender" class="form-control">
                                            <option value="">Select Gender</option>
                                            <?php foreach ($genders as $gender): ?>
                                                <option value="<?php echo $gender['code']; ?>" <?php echo $role_data['gender_code'] == $gender['code'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($gender['label']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="dob">Date of Birth</label>
                                        <input type="date" name="dob" id="dob" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['dob'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($user['role'] === 'parent' && $role_data): ?>
                            <!-- Parent specific fields -->
                            <div class="role-fields">
                                <h3>Parent Information</h3>
                                
                                <div class="form-group">
                                    <label for="student_user_id">Child/Student *</label>
                                    <select name="student_user_id" id="student_user_id" class="form-control" required>
                                        <option value="">Select Student</option>
                                        <?php foreach ($students as $student): ?>
                                            <option value="<?php echo $student['id']; ?>" <?php echo $role_data['student_user_id'] == $student['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($student['full_name']) . ' (' . htmlspecialchars($student['admission_number']) . ')'; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="relationship">Relationship *</label>
                                        <select name="relationship" id="relationship" class="form-control" required>
                                            <option value="">Select Relationship</option>
                                            <option value="father" <?php echo $role_data['relationship'] === 'father' ? 'selected' : ''; ?>>Father</option>
                                            <option value="mother" <?php echo $role_data['relationship'] === 'mother' ? 'selected' : ''; ?>>Mother</option>
                                            <option value="other" <?php echo $role_data['relationship'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="phone">Phone Number *</label>
                                        <input type="text" name="phone" id="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($role_data['phone']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update User</button>
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
    const currentSectionId = <?php echo ($user['role'] === 'student' && $role_data) ? $role_data['section_id'] : 0; ?>;
    
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
                const selected = currentSectionId == section.id ? 'selected' : '';
                sectionSelect.append(`<option value="${section.id}" ${selected}>${section.name}</option>`);
            });
        }
        
        // Initialize sections dropdown
        updateSections();
    });
    </script>
</body>
</html> 