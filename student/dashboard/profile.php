<?php include 'sidebar.php';
include 'con.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: ../index.php');
    exit();
}

// Get student details
$student_id = $_SESSION['user_id'];
$query = "SELECT s.*, u.email, u.full_name as user_full_name, u.created_at as join_date, 
            c.name as class_name, sec.name as section_name, 
            g.name as gender_name, b.name as blood_group_name,
            ay.name as academic_year
          FROM students s
          JOIN users u ON s.user_id = u.id
          LEFT JOIN classes c ON s.class_id = c.id
          LEFT JOIN sections sec ON s.section_id = sec.id
          LEFT JOIN genders g ON s.gender_code = g.code
          LEFT JOIN blood_groups b ON s.blood_group_code = b.code
          LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
          WHERE s.user_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
} else {
    // Handle case where student data is not found
    $student = [];
}

// Format date of birth if it exists
$formatted_dob = isset($student['dob']) && $student['dob'] != '0000-00-00' ? date('F j, Y', strtotime($student['dob'])) : 'Not set';

// Format join date if it exists
$join_date = isset($student['join_date']) ? date('F j, Y', strtotime($student['join_date'])) : 'Not available';

// Default photo if none exists
$photo_url = !empty($student['photo']) ? '../uploads/student_photos/' . $student['photo'] : 'https://randomuser.me/api/portraits/lego/1.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>My Profile</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
<div class="sidebar-overlay"></div>
<button class="hamburger-btn" type="button" onclick="toggleSidebar()">
    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
    </svg>
</button>

<div class="dashboard-container">
    <header class="dashboard-header">
        <h1 class="header-title">Student Profile</h1>
        <span class="header-subtitle">View and update your profile information</span>
    </header>

    <main class="dashboard-content">
        <div class="profile-container">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-pic">
                            <img src="<?php echo $photo_url; ?>" alt="Student Profile" id="profile-image">
                            <div class="change-photo" onclick="showAvatarModal()">Change Photo</div>
                        </div>
                        <h2 class="profile-name"><?php echo htmlspecialchars($student['full_name'] ?? 'Student Name'); ?></h2>
                        <p class="profile-role">Student</p>
                    </div>
                    <div class="profile-body">
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="12" cy="10" r="3"/>
                                <path d="M8 16a4 4 0 0 1 8 0"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Admission Number</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($student['admission_number'] ?? 'Not available'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Mobile</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($student['mobile'] ?? 'Not available'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Email</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($student['email'] ?? 'Not available'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Class</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars(($student['class_name'] ?? 'Not assigned') . ' - ' . ($student['section_name'] ?? 'Not assigned')); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 2h8a2 2 0 0 1 2 2v14.5a3.5 3.5 0 0 0-7 0V18H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                                <path d="M15 22v-3.5a3.5 3.5 0 0 0-7 0V22"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Joined</div>
                                <div class="profile-info-text"><?php echo $join_date; ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <button class="btn btn-primary" onclick="showQRModal()">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                                <path d="M9 9h3v3H9z"/>
                                <path d="M9 15h3v3H9z"/>
                                <path d="M15 9h3v3h-3z"/>
                                <path d="M12 12h3v3h-3z"/>
                            </svg>
                            View ID Card QR
                        </button>
                    </div>
                </div>
                
                <div class="profile-card">
                    <div class="profile-body">
                        <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1rem; color: #1f2937;">Quick Actions</h3>
                        <button class="btn btn-secondary" style="width: 100%; margin-bottom: 0.75rem;" onclick="showPasswordModal()">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            Change Password
                        </button>
                        <button class="btn btn-secondary" style="width: 100%; margin-bottom: 0.75rem;" onclick="window.location.href='id-card_student.php'">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="12" cy="10" r="3"/>
                                <path d="M8 16a4 4 0 0 1 8 0"/>
                            </svg>
                            View ID Card
                        </button>
                        <button class="btn btn-secondary" style="width: 100%;" onclick="downloadInformation()">
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Download My Information
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Profile Main Content -->
            <div class="profile-main">
                <div class="profile-card">
                    <div class="profile-tabs">
                        <div class="profile-tab active" onclick="changeTab(this, 'personal-info')">Personal Information</div>
                        <div class="profile-tab" onclick="changeTab(this, 'academics')">Academic Details</div>
                        <div class="profile-tab" onclick="changeTab(this, 'documents')">Documents</div>
                        <div class="profile-tab" onclick="changeTab(this, 'notification-settings')">Notification Settings</div>
                    </div>
                    
                    <!-- Personal Information Tab -->
                    <div id="personal-info" class="profile-tab-content">
                        <div class="profile-body">
                            <form id="personal-info-form">
                                <div class="form-section">
                                    <h3 class="form-section-title">Basic Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="firstName">Full Name</label>
                                            <input type="text" id="firstName" class="form-input" value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="admissionNumber">Admission Number</label>
                                            <input type="text" id="admissionNumber" class="form-input" value="<?php echo htmlspecialchars($student['admission_number'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="dob">Date of Birth</label>
                                            <input type="text" id="dob" class="form-input" value="<?php echo $formatted_dob; ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="gender">Gender</label>
                                            <input type="text" id="gender" class="form-input" value="<?php echo htmlspecialchars($student['gender_name'] ?? 'Not specified'); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="bloodGroup">Blood Group</label>
                                            <input type="text" id="bloodGroup" class="form-input" value="<?php echo htmlspecialchars($student['blood_group_name'] ?? 'Not specified'); ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="nationality">Nationality</label>
                                            <input type="text" id="nationality" class="form-input" value="<?php echo htmlspecialchars($student['nationality'] ?? 'Not specified'); ?>" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Contact Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" id="email" class="form-input" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone Number</label>
                                            <input type="tel" id="phone" class="form-input" value="<?php echo htmlspecialchars($student['mobile'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="altEmail">Alternative Email (Optional)</label>
                                            <input type="email" id="altEmail" class="form-input" value="<?php echo htmlspecialchars($student['contact_email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="altPhone">Alternative Contact Number</label>
                                            <input type="tel" id="altPhone" class="form-input" value="<?php echo htmlspecialchars($student['alt_mobile'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Address Information</h3>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="address">Street Address</label>
                                            <input type="text" id="address" class="form-input" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="pincode">PIN/Postal Code</label>
                                            <input type="text" id="pincode" class="form-input" value="<?php echo htmlspecialchars($student['pincode'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="state">State</label>
                                            <input type="text" id="state" class="form-input" value="<?php echo htmlspecialchars($student['student_state_code'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Family Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="fatherName">Father's Name</label>
                                            <input type="text" id="fatherName" class="form-input" value="<?php echo htmlspecialchars($student['father_name'] ?? ''); ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="motherName">Mother's Name</label>
                                            <input type="text" id="motherName" class="form-input" value="<?php echo htmlspecialchars($student['mother_name'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>
                                    <?php
                                    // Check if parent account exists
                                    $parent_query = "SELECT pa.*, u.email, u.full_name as parent_name
                                                    FROM parent_accounts pa
                                                    JOIN users u ON pa.user_id = u.id
                                                    WHERE pa.student_id = ?";
                                    $parent_stmt = $conn->prepare($parent_query);
                                    $parent_stmt->bind_param("i", $student_id);
                                    $parent_stmt->execute();
                                    $parent_result = $parent_stmt->get_result();
                                    $has_parent_account = $parent_result->num_rows > 0;
                                    $parent_info = $has_parent_account ? $parent_result->fetch_assoc() : null;
                                    ?>
                                    
                                    <?php if ($has_parent_account): ?>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="parentName">Parent Account</label>
                                            <input type="text" id="parentName" class="form-input" value="<?php echo htmlspecialchars($parent_info['parent_name'] ?? 'Not available'); ?>" readonly>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="parentEmail">Parent Email</label>
                                            <input type="email" id="parentEmail" class="form-input" value="<?php echo htmlspecialchars($parent_info['email'] ?? 'Not available'); ?>" readonly>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <div class="info-message">No parent account is linked to your profile. Please contact school administration if this information is incorrect.</div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Other Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="motherTongue">Mother Tongue</label>
                                            <input type="text" id="motherTongue" class="form-input" value="<?php echo htmlspecialchars($student['mother_tongue'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="aadhar">Aadhar Card Number</label>
                                            <input type="text" id="aadhar" class="form-input" value="<?php echo htmlspecialchars($student['aadhar_card_number'] ?? ''); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="medicalConditions">Medical Conditions (if any)</label>
                                            <textarea id="medicalConditions" class="form-textarea"><?php echo htmlspecialchars($student['medical_conditions'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm()">Cancel Changes</button>
                                    <button type="button" class="btn btn-primary" onclick="savePersonalInfo()">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Academics Tab -->
                    <div id="academics" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <div class="form-section">
                                <h3 class="form-section-title">Current Academic Information</h3>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Academic Year</label>
                                        <div class="form-value"><?php echo htmlspecialchars($student['academic_year'] ?? 'Not available'); ?></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Class</label>
                                        <div class="form-value"><?php echo htmlspecialchars($student['class_name'] ?? 'Not assigned'); ?></div>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Section</label>
                                        <div class="form-value"><?php echo htmlspecialchars($student['section_name'] ?? 'Not assigned'); ?></div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Roll Number</label>
                                        <div class="form-value"><?php echo htmlspecialchars($student['roll_number'] ?? 'Not assigned'); ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Subjects</h3>
                                <?php
                                // Get subjects for the student's class
                                $subjects_query = "SELECT s.name, s.code, s.description
                                                FROM subjects s
                                                JOIN class_subjects cs ON s.id = cs.subject_id
                                                WHERE cs.class_id = ?
                                                ORDER BY s.name";
                                $subjects_stmt = $conn->prepare($subjects_query);
                                $subjects_stmt->bind_param("i", $student['class_id']);
                                $subjects_stmt->execute();
                                $subjects_result = $subjects_stmt->get_result();
                                $has_subjects = $subjects_result->num_rows > 0;
                                ?>
                                
                                <?php if ($has_subjects): ?>
                                <div class="subjects-grid">
                                    <?php while ($subject = $subjects_result->fetch_assoc()): ?>
                                    <div class="subject-card">
                                        <div class="subject-name"><?php echo htmlspecialchars($subject['name']); ?></div>
                                        <div class="subject-code"><?php echo htmlspecialchars($subject['code']); ?></div>
                                        <?php if (!empty($subject['description'])): ?>
                                        <div class="subject-description"><?php echo htmlspecialchars($subject['description']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                                <?php else: ?>
                                <div class="info-message">No subjects are assigned to your class.</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Teachers</h3>
                                <?php
                                // Get teachers for the student's class and subjects
                                $teachers_query = "SELECT t.employee_number, u.full_name as teacher_name, s.name as subject_name
                                                FROM teachers t
                                                JOIN users u ON t.user_id = u.id
                                                JOIN teacher_class_subjects tcs ON t.user_id = tcs.teacher_id
                                                JOIN subjects s ON tcs.subject_id = s.id
                                                WHERE tcs.class_id = ? AND tcs.section_id = ?
                                                ORDER BY s.name";
                                $teachers_stmt = $conn->prepare($teachers_query);
                                $teachers_stmt->bind_param("ii", $student['class_id'], $student['section_id']);
                                $teachers_stmt->execute();
                                $teachers_result = $teachers_stmt->get_result();
                                $has_teachers = $teachers_result->num_rows > 0;
                                ?>
                                
                                <?php if ($has_teachers): ?>
                                <div class="teacher-list">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Subject</th>
                                                <th>Teacher</th>
                                                <th>Employee ID</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($teacher = $teachers_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($teacher['subject_name']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['teacher_name']); ?></td>
                                                <td><?php echo htmlspecialchars($teacher['employee_number']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="info-message">No teachers are assigned to your class yet.</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Academic History</h3>
                                <?php
                                // Get academic history (previous classes/years)
                                $history_query = "SELECT e.*, c.name as class_name, s.name as section_name, ay.name as academic_year
                                                FROM enrollments e
                                                JOIN classes c ON e.class_id = c.id
                                                JOIN sections s ON e.section_id = s.id
                                                JOIN academic_years ay ON e.academic_year_id = ay.id
                                                WHERE e.student_id = ? AND e.academic_year_id != ?
                                                ORDER BY ay.start_date DESC";
                                $history_stmt = $conn->prepare($history_query);
                                $history_stmt->bind_param("ii", $student_id, $student['academic_year_id']);
                                $history_stmt->execute();
                                $history_result = $history_stmt->get_result();
                                $has_history = $history_result->num_rows > 0;
                                ?>
                                
                                <?php if ($has_history): ?>
                                <div class="history-list">
                                    <table class="data-table">
                                        <thead>
                                            <tr>
                                                <th>Academic Year</th>
                                                <th>Class</th>
                                                <th>Section</th>
                                                <th>Roll Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($enrollment = $history_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($enrollment['academic_year']); ?></td>
                                                <td><?php echo htmlspecialchars($enrollment['class_name']); ?></td>
                                                <td><?php echo htmlspecialchars($enrollment['section_name']); ?></td>
                                                <td><?php echo htmlspecialchars($enrollment['roll_number']); ?></td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="info-message">No previous academic records found.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Documents Tab -->
                    <div id="documents" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">Upload and manage your personal and academic documents. All documents are securely stored and only accessible to authorized personnel.</p>
                            
                            <label class="file-upload" for="document-upload">
                                <input type="file" id="document-upload" class="file-input">
                                <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <div class="file-upload-text">Drag and drop files here, or click to browse</div>
                                <div class="file-upload-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</div>
                            </label>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin: 1.5rem 0 1rem;">Your Documents</h3>
                            
                            <?php
                            // In a real implementation, you would fetch student documents from the database
                            // For now, we'll simulate with sample data
                            $student_documents = [
                                [
                                    'id' => 1,
                                    'name' => 'Birth Certificate.pdf',
                                    'uploaded_on' => 'Aug 12, 2023',
                                    'size' => '1.2 MB',
                                    'type' => 'pdf'
                                ],
                                [
                                    'id' => 2,
                                    'name' => 'Previous School TC.pdf',
                                    'uploaded_on' => 'Aug 12, 2023',
                                    'size' => '2.5 MB',
                                    'type' => 'pdf'
                                ],
                                [
                                    'id' => 3,
                                    'name' => 'Student ID Photo.jpg',
                                    'uploaded_on' => 'Sep 5, 2023',
                                    'size' => '245 KB',
                                    'type' => 'jpg'
                                ],
                            ];
                            ?>
                            
                            <!-- Document List -->
                            <?php if (!empty($student_documents)): ?>
                                <?php foreach ($student_documents as $document): ?>
                                <div class="document-card">
                                    <div class="document-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="16" y1="13" x2="8" y2="13" />
                                            <line x1="16" y1="17" x2="8" y2="17" />
                                            <polyline points="10 9 9 9 8 9" />
                                        </svg>
                                    </div>
                                    <div class="document-details">
                                        <h4 class="document-title"><?php echo htmlspecialchars($document['name']); ?></h4>
                                        <p class="document-info">Uploaded on: <?php echo htmlspecialchars($document['uploaded_on']); ?> • <?php echo htmlspecialchars($document['size']); ?></p>
                                    </div>
                                    <div class="document-actions">
                                        <button class="btn btn-sm btn-secondary" onclick="viewDocument(<?php echo $document['id']; ?>)">View</button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteDocument(<?php echo $document['id']; ?>)">Delete</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-documents">
                                    <p>You haven't uploaded any documents yet.</p>
                                </div>
                            <?php endif; ?>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin: 1.5rem 0 1rem;">Required Documents</h3>
                            
                            <div class="required-documents">
                                <div class="document-requirement">
                                    <div class="requirement-name">Birth Certificate</div>
                                    <div class="requirement-status">
                                        <span class="status-badge status-uploaded">Uploaded</span>
                                    </div>
                                </div>
                                
                                <div class="document-requirement">
                                    <div class="requirement-name">Previous School Transfer Certificate</div>
                                    <div class="requirement-status">
                                        <span class="status-badge status-uploaded">Uploaded</span>
                                    </div>
                                </div>
                                
                                <div class="document-requirement">
                                    <div class="requirement-name">Medical Certificate</div>
                                    <div class="requirement-status">
                                        <span class="status-badge status-pending">Pending</span>
                                    </div>
                                </div>
                                
                                <div class="document-requirement">
                                    <div class="requirement-name">Aadhar Card</div>
                                    <div class="requirement-status">
                                        <span class="status-badge status-pending">Pending</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Notification Settings Tab -->
                    <div id="notification-settings" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">Configure how you receive notifications and alerts from the system. You can customize settings for different types of notifications.</p>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin-bottom: 1rem;">Email Notifications</h3>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Class Announcements</h4>
                                    <p>Receive email updates for class announcements</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Homework Assignments</h4>
                                    <p>Receive email notifications for new homework assignments</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Exam & Test Schedules</h4>
                                    <p>Get notified about upcoming exams and test schedules</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Results</h4>
                                    <p>Get notified when exam results are published</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Fee Reminders</h4>
                                    <p>Receive fee payment reminders via email</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin: 1.5rem 0 1rem;">Push Notifications</h3>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Class Announcements</h4>
                                    <p>Receive push notifications for class announcements</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Homework Assignments</h4>
                                    <p>Receive push notifications for new homework assignments</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Exam & Test Schedules</h4>
                                    <p>Get notified about upcoming exams and test schedules</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Results</h4>
                                    <p>Get notified when exam results are published</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Fee Reminders</h4>
                                    <p>Receive fee payment reminders via push notifications</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin: 1.5rem 0 1rem;">SMS Notifications</h3>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Important Announcements Only</h4>
                                    <p>Receive SMS notifications only for important announcements</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Exam & Test Schedules</h4>
                                    <p>Get SMS notifications about upcoming exams and test schedules</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Results</h4>
                                    <p>Get SMS notifications when exam results are published</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="resetNotificationSettings()">Reset to Default</button>
                                <button type="button" class="btn btn-primary" onclick="saveNotificationSettings()">Save Changes</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Avatar Selection Modal -->
<div class="modal-overlay" id="avatarModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Choose Profile Picture</h3>
            <button class="modal-close" onclick="hideAvatarModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p style="margin-top: 0; margin-bottom: 1rem; color: #6b7280; font-size: 0.9375rem;">Select a profile picture from our collection or upload your own.</p>
            
            <h4 style="font-size: 0.9375rem; color: #4b5563; margin-bottom: 0.5rem;">Upload Image</h4>
            <label class="file-upload" for="avatar-upload" style="padding: 1rem;">
                <input type="file" id="avatar-upload" class="file-input" accept="image/*">
                <svg style="width: 24px; height: 24px; color: #6b7280; margin-bottom: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                <div style="font-size: 0.875rem; color: #4b5563;">Click to upload image</div>
                <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem;">JPG, PNG (Max 2MB)</div>
            </label>
            
            <h4 style="font-size: 0.9375rem; color: #4b5563; margin: 1rem 0 0.5rem;">Available Avatars</h4>
            <div class="avatar-grid">
                <div class="avatar-option" onclick="selectAvatar(1)">
                    <img src="https://randomuser.me/api/portraits/lego/1.jpg" alt="Avatar 1">
                </div>
                <div class="avatar-option" onclick="selectAvatar(2)">
                    <img src="https://randomuser.me/api/portraits/lego/2.jpg" alt="Avatar 2">
                </div>
                <div class="avatar-option" onclick="selectAvatar(3)">
                    <img src="https://randomuser.me/api/portraits/lego/3.jpg" alt="Avatar 3">
                </div>
                <div class="avatar-option" onclick="selectAvatar(4)">
                    <img src="https://randomuser.me/api/portraits/lego/4.jpg" alt="Avatar 4">
                </div>
                <div class="avatar-option" onclick="selectAvatar(5)">
                    <img src="https://randomuser.me/api/portraits/lego/5.jpg" alt="Avatar 5">
                </div>
                <div class="avatar-option" onclick="selectAvatar(6)">
                    <img src="https://randomuser.me/api/portraits/lego/6.jpg" alt="Avatar 6">
                </div>
                <div class="avatar-option" onclick="selectAvatar(7)">
                    <img src="https://randomuser.me/api/portraits/lego/7.jpg" alt="Avatar 7">
                </div>
                <div class="avatar-option" onclick="selectAvatar(8)">
                    <img src="https://randomuser.me/api/portraits/lego/8.jpg" alt="Avatar 8">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hideAvatarModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveAvatar()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal-overlay" id="passwordModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Change Password</h3>
            <button class="modal-close" onclick="hidePasswordModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="password-tips">
                <h4 class="password-tips-title">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    Password Requirements
                </h4>
                <ul class="password-tips-list">
                    <li>At least 8 characters long</li>
                    <li>Include at least one uppercase letter</li>
                    <li>Include at least one lowercase letter</li>
                    <li>Include at least one number</li>
                    <li>Include at least one special character (!@#$%^&*)</li>
                </ul>
            </div>
            
            <form id="change-password-form">
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" class="form-input" placeholder="Enter your current password">
                </div>
                
                <div class="form-group" style="margin-bottom: 1.25rem;">
                    <label class="form-label" for="newPassword">New Password</label>
                    <input type="password" id="newPassword" class="form-input" placeholder="Enter your new password" onkeyup="checkPasswordStrength()">
                    <div class="password-strength">
                        <div class="password-strength-fill" id="passwordStrength"></div>
                    </div>
                    <div class="password-strength-text">
                        <span id="passwordStrengthText">Password strength</span>
                        <span id="passwordStrengthRating">Weak</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" class="form-input" placeholder="Confirm your new password" onkeyup="checkPasswordMatch()">
                    <div class="form-hint" id="passwordMatchText"></div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="hidePasswordModal()">Cancel</button>
            <button class="btn btn-primary" onclick="changePassword()">Update Password</button>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal-overlay" id="qrModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">Student ID QR Code</h3>
            <button class="modal-close" onclick="hideQRModal()">
                <svg class="modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="modal-body" style="text-align: center;">
            <p style="margin-top: 0; margin-bottom: 1rem; color: #6b7280; font-size: 0.9375rem;">Scan this QR code for quick identification within the school system.</p>
            
            <div style="width: 200px; height: 200px; margin: 0 auto 1.5rem; background: #f3f4f6; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                <!-- Placeholder for QR code - in a real application, this would be a generated QR code -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo htmlspecialchars($student['admission_number'] ?? 'student'); ?>" alt="Student ID QR Code" style="max-width: 180px; max-height: 180px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($student['full_name'] ?? 'Student Name'); ?></div>
                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($student['admission_number'] ?? 'Admission Number'); ?></div>
                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars(($student['class_name'] ?? 'Class') . ' - ' . ($student['section_name'] ?? 'Section')); ?></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="downloadQRCode()">
                <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download QR
            </button>
            <button class="btn btn-primary" onclick="hideQRModal()">Close</button>
        </div>
    </div>
</div>

<style>
/* Additional styles for profile page */
.form-value {
    background-color: #f3f4f6;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    color: #4b5563;
    font-size: 0.9375rem;
}

.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-top: 0.75rem;
}

.subject-card {
    background-color: #f3f4f6;
    border-radius: 0.5rem;
    padding: 1rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.subject-name {
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.subject-code {
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
}

.subject-description {
    color: #4b5563;
    font-size: 0.875rem;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.75rem;
}

.data-table th,
.data-table td {
    padding: 0.75rem 1rem;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}

.data-table th {
    font-weight: 600;
    color: #4b5563;
    background-color: #f9fafb;
}

.data-table tr:hover {
    background-color: #f9fafb;
}

.info-message {
    background-color: #f3f8ff;
    border-left: 4px solid #3b82f6;
    padding: 0.75rem 1rem;
    color: #1e40af;
    font-size: 0.9375rem;
    border-radius: 0.25rem;
}

.avatar-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 0.75rem;
    margin-top: 0.75rem;
}

.avatar-option {
    border-radius: 0.5rem;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid transparent;
    transition: border-color 0.2s ease;
}

.avatar-option:hover {
    border-color: #3b82f6;
}

.avatar-option.selected {
    border-color: #3b82f6;
}

.avatar-option img {
    width: 100%;
    height: auto;
    display: block;
}

.document-card {
    display: flex;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.75rem;
}

.document-icon {
    flex: 0 0 2.5rem;
    height: 2.5rem;
    margin-right: 1rem;
    color: #4b5563;
}

.document-details {
    flex: 1;
}

.document-title {
    margin: 0 0 0.25rem;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #1f2937;
}

.document-info {
    margin: 0;
    font-size: 0.8125rem;
    color: #6b7280;
}

.document-actions {
    display: flex;
    gap: 0.5rem;
}

.required-documents {
    margin-top: 0.75rem;
}

.document-requirement {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.requirement-name {
    font-size: 0.9375rem;
    color: #1f2937;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-uploaded {
    background-color: #ecfdf5;
    color: #065f46;
}

.status-pending {
    background-color: #fff7ed;
    color: #9a3412;
}

.notification-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.notification-details h4 {
    margin: 0 0 0.25rem;
    font-size: 0.9375rem;
    font-weight: 500;
    color: #1f2937;
}

.notification-details p {
    margin: 0;
    font-size: 0.8125rem;
    color: #6b7280;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 46px;
    height: 24px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #e5e7eb;
    border-radius: 34px;
    transition: 0.4s;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    border-radius: 50%;
    transition: 0.4s;
}

input:checked + .toggle-slider {
    background-color: #3b82f6;
}

input:focus + .toggle-slider {
    box-shadow: 0 0 1px #3b82f6;
}

input:checked + .toggle-slider:before {
    transform: translateX(22px);
}

.password-tips {
    background-color: #f3f4f6;
    border-radius: 0.5rem;
    padding: 1rem;
    margin-bottom: 1.25rem;
}

.password-tips-title {
    display: flex;
    align-items: center;
    font-size: 0.9375rem;
    color: #1f2937;
    margin-top: 0;
    margin-bottom: 0.75rem;
}

.password-tips-title svg {
    width: 1.25rem;
    height: 1.25rem;
    margin-right: 0.5rem;
    color: #4b5563;
}

.password-tips-list {
    margin: 0;
    padding-left: 1.5rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.password-tips-list li {
    margin-bottom: 0.25rem;
}

.password-strength {
    margin-top: 0.5rem;
    height: 4px;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}

.password-strength-fill {
    height: 100%;
    width: 0;
    border-radius: 9999px;
    transition: width 0.3s ease, background-color 0.3s ease;
}

.password-strength-text {
    display: flex;
    justify-content: space-between;
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 0.25rem;
}

.password-strength-weak {
    background-color: #ef4444;
    width: 25%;
}

.password-strength-fair {
    background-color: #f59e0b;
    width: 50%;
}

.password-strength-good {
    background-color: #10b981;
    width: 75%;
}

.password-strength-strong {
    background-color: #059669;
    width: 100%;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 50;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

.modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.modal {
    background-color: white;
    border-radius: 0.5rem;
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.modal-title {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6b7280;
}

.modal-close-icon {
    width: 1.25rem;
    height: 1.25rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
}

@media (max-width: 768px) {
    .profile-container {
        flex-direction: column;
    }
    
    .profile-sidebar,
    .profile-main {
        width: 100%;
    }
    
    .profile-sidebar {
        margin-bottom: 1.5rem;
    }
    
    .form-row {
        flex-direction: column;
    }
    
    .form-group {
        width: 100%;
    }
    
    .subjects-grid {
        grid-template-columns: 1fr;
    }
    
    .avatar-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}
</style>

<script>
    // Function to toggle sidebar
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const dashboardContainer = document.querySelector('.dashboard-container');
        const body = document.querySelector('body');
        const overlay = document.querySelector('.sidebar-overlay');
        
        sidebar.classList.toggle('show');
        body.classList.toggle('sidebar-open');
        dashboardContainer.classList.toggle('sidebar-open');
    }
    
    // Function to handle tab changes
    function changeTab(tab, tabId) {
        // Remove active class from all tabs
        const tabs = document.querySelectorAll('.profile-tab');
        tabs.forEach(t => t.classList.remove('active'));
        
        // Add active class to selected tab
        tab.classList.add('active');
        
        // Hide all sections
        document.querySelectorAll('.profile-tab-content').forEach(content => {
            content.style.display = 'none';
        });
        
        // Show selected section
        document.getElementById(tabId).style.display = 'block';
    }
    
    // Avatar modal functions
    function showAvatarModal() {
        document.getElementById('avatarModal').classList.add('show');
    }
    
    function hideAvatarModal() {
        document.getElementById('avatarModal').classList.remove('show');
    }
    
    function selectAvatar(id) {
        // Remove selected class from all options
        document.querySelectorAll('.avatar-option').forEach(option => {
            option.classList.remove('selected');
        });
        
        // Add selected class to clicked option
        event.currentTarget.classList.add('selected');
    }
    
    function saveAvatar() {
        // In a real application, this would upload and save the selected avatar
        const selectedAvatar = document.querySelector('.avatar-option.selected img');
        
        if (selectedAvatar) {
            document.getElementById('profile-image').src = selectedAvatar.src;
        }
        
        hideAvatarModal();
    }
    
    // Password modal functions
    function showPasswordModal() {
        document.getElementById('passwordModal').classList.add('show');
    }
    
    function hidePasswordModal() {
        document.getElementById('passwordModal').classList.remove('show');
    }
    
    function checkPasswordStrength() {
        const password = document.getElementById('newPassword').value;
        const strengthBar = document.getElementById('passwordStrength');
        const strengthText = document.getElementById('passwordStrengthRating');
        
        // Simple password strength check logic
        let strength = 0;
        
        if (password.length >= 8) strength += 1;
        if (password.match(/[A-Z]/)) strength += 1;
        if (password.match(/[a-z]/)) strength += 1;
        if (password.match(/[0-9]/)) strength += 1;
        if (password.match(/[^A-Za-z0-9]/)) strength += 1;
        
        switch(strength) {
            case 0:
            case 1:
                strengthBar.className = 'password-strength-fill password-strength-weak';
                strengthText.textContent = 'Weak';
                break;
            case 2:
                strengthBar.className = 'password-strength-fill password-strength-fair';
                strengthText.textContent = 'Fair';
                break;
            case 3:
                strengthBar.className = 'password-strength-fill password-strength-good';
                strengthText.textContent = 'Good';
                break;
            case 4:
            case 5:
                strengthBar.className = 'password-strength-fill password-strength-strong';
                strengthText.textContent = 'Strong';
                break;
        }
    }
    
    function checkPasswordMatch() {
        const password = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        const matchText = document.getElementById('passwordMatchText');
        
        if (confirmPassword === '') {
            matchText.textContent = '';
            return;
        }
        
        if (password === confirmPassword) {
            matchText.textContent = 'Passwords match';
            matchText.style.color = '#10b981';
        } else {
            matchText.textContent = 'Passwords do not match';
            matchText.style.color = '#ef4444';
        }
    }
    
    function changePassword() {
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        // Validate inputs
        if (!currentPassword || !newPassword || !confirmPassword) {
            alert('Please fill in all password fields');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            alert('New passwords do not match');
            return;
        }
        
        // In a real application, this would send a request to change the password
        alert('Password changed successfully!');
        hidePasswordModal();
    }
    
    // QR Code modal functions
    function showQRModal() {
        document.getElementById('qrModal').classList.add('show');
    }
    
    function hideQRModal() {
        document.getElementById('qrModal').classList.remove('show');
    }
    
    function downloadQRCode() {
        // In a real application, this would download the QR code image
        alert('QR Code downloaded');
    }
    
    // Form management functions
    function savePersonalInfo() {
        // In a real application, this would save the updated personal information
        alert('Personal information updated successfully!');
    }
    
    function resetForm() {
        // In a real application, this would reset the form to its original values
        if (confirm('Discard all changes?')) {
            document.getElementById('personal-info-form').reset();
        }
    }
    
    function downloadInformation() {
        // In a real application, this would generate and download a PDF with student information
        alert('Your information has been downloaded');
    }
    
    // Document management functions
    function viewDocument(id) {
        // In a real application, this would open the document for viewing
        alert('Viewing document with ID: ' + id);
    }
    
    function deleteDocument(id) {
        // In a real application, this would delete the document
        if (confirm('Are you sure you want to delete this document?')) {
            alert('Document with ID: ' + id + ' has been deleted');
        }
    }
    
    // Notification settings functions
    function saveNotificationSettings() {
        // In a real application, this would save the notification settings
        alert('Notification settings updated successfully!');
    }
    
    function resetNotificationSettings() {
        // In a real application, this would reset notification settings to defaults
        if (confirm('Reset all notification settings to default values?')) {
            // Reset all toggles to their default state
            alert('Notification settings reset to defaults');
        }
    }
    
    // Add page transition animations
    document.addEventListener('DOMContentLoaded', function() {
        const links = document.querySelectorAll('a[href]:not([target="_blank"])');
        
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                
                // Skip for anchors, javascript: links, etc.
                if (href.startsWith('#') || href.startsWith('javascript:') || href.startsWith('mailto:') || href.startsWith('tel:')) {
                    return;
                }
                
                e.preventDefault();
                document.body.classList.add('fade-out');
                
                setTimeout(function() {
                    window.location.href = href;
                }, 500); // Match this to the CSS animation duration
            });
        });
    });
</script>
</body>
</html> 