<?php include 'sidebar.php'; ?>

<?php
// Get logged-in teacher information
$teacher_info = null;
$teacher_subjects = [];
$teacher_classes = [];

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get teacher information with user details
    $teacher_query = "SELECT t.*, u.email, u.full_name, u.created_at as join_date,
                             COUNT(DISTINCT tcs.class_id) as total_classes,
                             COUNT(DISTINCT tcs.subject_id) as total_subjects
                      FROM teachers t 
                      LEFT JOIN users u ON t.user_id = u.id
                      LEFT JOIN teacher_class_subjects tcs ON t.user_id = tcs.teacher_user_id
                      WHERE t.user_id = ?
                      GROUP BY t.user_id";
    $stmt = $conn->prepare($teacher_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $teacher_info = $result->fetch_assoc();
    
    if ($teacher_info) {
        // Get teacher's subjects
        $subjects_query = "SELECT DISTINCT s.name as subject_name, s.code as subject_code
                          FROM subjects s
                          INNER JOIN teacher_class_subjects tcs ON s.id = tcs.subject_id
                          WHERE tcs.teacher_user_id = ?
                          ORDER BY s.name";
        $stmt = $conn->prepare($subjects_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $teacher_subjects[] = $row;
        }
        
        // Get teacher's classes
        $classes_query = "SELECT DISTINCT c.name as class_name, sec.name as section_name,
                                 c.id as class_id, sec.id as section_id,
                                 COUNT(DISTINCT st.user_id) as student_count
                         FROM classes c
                         INNER JOIN teacher_class_subjects tcs ON c.id = tcs.class_id
                         LEFT JOIN sections sec ON tcs.section_id = sec.id
                         LEFT JOIN students st ON c.id = st.class_id AND sec.id = st.section_id
                         WHERE tcs.teacher_user_id = ?
                         GROUP BY c.id, sec.id
                         ORDER BY c.name, sec.name";
        $stmt = $conn->prepare($classes_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $teacher_classes[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Profile Management</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    
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
        <h1 class="header-title">Profile Management</h1>
        <span class="header-subtitle">View and update your profile information</span>
    </header>

    <main class="dashboard-content">
        <div class="profile-container">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-pic">
                            <img src="<?php echo !empty($teacher_info['profile_photo']) ? '../../' . htmlspecialchars($teacher_info['profile_photo']) : 'https://randomuser.me/api/portraits/men/32.jpg'; ?>" alt="Teacher Profile" id="profile-image">
                            <div class="change-photo" onclick="showAvatarModal()">Change Photo</div>
                        </div>
                        <h2 class="profile-name"><?php echo htmlspecialchars($teacher_info['full_name'] ?? 'Teacher Name'); ?></h2>
                        <p class="profile-role"><?php echo htmlspecialchars($teacher_info['position'] ?? 'Teacher'); ?></p>
                    </div>
                    <div class="profile-body">
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="16" rx="2"/>
                                <circle cx="12" cy="10" r="3"/>
                                <path d="M8 16a4 4 0 0 1 8 0"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Employee ID</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($teacher_info['employee_number'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Phone</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($teacher_info['phone'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Email</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($teacher_info['email'] ?? 'N/A'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Classes</div>
                                <div class="profile-info-text"><?php echo $teacher_info['total_classes'] ?? 0; ?> Classes</div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Subjects</div>
                                <div class="profile-info-text"><?php echo $teacher_info['total_subjects'] ?? 0; ?> Subjects</div>
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
                        <button class="btn btn-secondary" style="width: 100%; margin-bottom: 0.75rem;" onclick="window.location.href='id-card.php'">
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
                        <div class="profile-tab" onclick="changeTab(this, 'documents')">Documents</div>
                    </div>
                    
                    <!-- Personal Information Tab -->
                    <div id="personal-info" class="profile-tab-content">
                        <div class="profile-body">
                            <form id="personal-info-form" enctype="multipart/form-data">
                                <div class="form-section">
                                    <h3 class="form-section-title">Basic Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="first_name">First Name</label>
                                            <input type="text" id="first_name" name="first_name" class="form-input" value="<?php echo htmlspecialchars(explode(' ', $teacher_info['full_name'] ?? '')[0] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="last_name">Last Name</label>
                                            <input type="text" id="last_name" name="last_name" class="form-input" value="<?php echo htmlspecialchars(explode(' ', $teacher_info['full_name'] ?? '', 2)[1] ?? ''); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="employeeId">Employee Number</label>
                                            <input type="text" id="employeeId" class="form-input" value="<?php echo htmlspecialchars($teacher_info['employee_number'] ?? ''); ?>" readonly style="background-color: #f9fafb;">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="date_of_birth">Date of Birth</label>
                                            <input type="date" id="date_of_birth" name="date_of_birth" class="form-input" value="<?php echo htmlspecialchars($teacher_info['date_of_birth'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="gender">Gender</label>
                                            <select id="gender" name="gender" class="form-select">
                                                <option value="male" <?php echo ($teacher_info['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo ($teacher_info['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo ($teacher_info['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                                                <option value="prefer-not" <?php echo ($teacher_info['gender'] ?? '') == 'prefer-not' ? 'selected' : ''; ?>>Prefer not to say</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Contact Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($teacher_info['email'] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($teacher_info['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="alt_email">Alternative Email (Optional)</label>
                                            <input type="email" id="alt_email" name="alt_email" class="form-input" value="<?php echo htmlspecialchars($teacher_info['alt_email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="emergency_contact">Emergency Contact Number</label>
                                            <input type="tel" id="emergency_contact" name="emergency_contact" class="form-input" value="<?php echo htmlspecialchars($teacher_info['emergency_contact'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Address Information</h3>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="address">Street Address</label>
                                            <input type="text" id="address" name="address" class="form-input" value="<?php echo htmlspecialchars($teacher_info['address'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="city">City</label>
                                            <input type="text" id="city" name="city" class="form-input" value="<?php echo htmlspecialchars($teacher_info['city'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="state">State/Province</label>
                                            <input type="text" id="state" name="state" class="form-input" value="<?php echo htmlspecialchars($teacher_info['state'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="zip_code">ZIP/Postal Code</label>
                                            <input type="text" id="zip_code" name="zip_code" class="form-input" value="<?php echo htmlspecialchars($teacher_info['zip_code'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="country">Country</label>
                                            <input type="text" id="country" name="country" class="form-input" value="<?php echo htmlspecialchars($teacher_info['country'] ?? 'India'); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Professional Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="position">Position</label>
                                            <input type="text" id="position" name="position" class="form-input" value="<?php echo htmlspecialchars($teacher_info['position'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="qualification">Qualification</label>
                                            <input type="text" id="qualification" name="qualification" class="form-input" value="<?php echo htmlspecialchars($teacher_info['qualification'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="experience_years">Experience (Years)</label>
                                            <input type="number" id="experience_years" name="experience_years" class="form-input" value="<?php echo htmlspecialchars($teacher_info['experience_years'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="department">Department</label>
                                            <input type="text" id="department" name="department" class="form-input" value="<?php echo htmlspecialchars($teacher_info['department'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="bio">Bio/Description</label>
                                            <textarea id="bio" name="bio" class="form-textarea" rows="3"><?php echo htmlspecialchars($teacher_info['bio'] ?? ''); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="subjects">Teaching Subjects</label>
                                            <input type="text" id="subjects" class="form-input" 
                                                   value="<?php echo !empty($teacher_subjects) ? implode(', ', array_column($teacher_subjects, 'subject_name')) : 'No subjects assigned'; ?>" 
                                                   readonly style="background-color: #f9fafb;">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="classes">Teaching Classes</label>
                                            <input type="text" id="classes" class="form-input" 
                                                   value="<?php 
                                                   if (!empty($teacher_classes)) {
                                                       $class_list = array_map(function($class) {
                                                           return $class['class_name'] . '-' . $class['section_name'];
                                                       }, $teacher_classes);
                                                       echo implode(', ', $class_list);
                                                   } else {
                                                       echo 'No classes assigned';
                                                   }
                                                   ?>" 
                                                   readonly style="background-color: #f9fafb;">
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
                    
                    <!-- Documents Tab -->
                    <div id="documents" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">Upload and manage your personal and professional documents. All documents are securely stored and only accessible to authorized personnel.</p>
                            
                            <form id="document-upload-form" enctype="multipart/form-data">
                                <div class="form-row" style="margin-bottom: 1rem;">
                                    <div class="form-group">
                                        <label class="form-label" for="document-name">Document Name</label>
                                        <input type="text" id="document-name" name="document_name" class="form-input" placeholder="Enter document name" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label" for="document-type">Document Type</label>
                                        <select id="document-type" name="document_type" class="form-select" required>
                                            <option value="resume">Resume/CV</option>
                                            <option value="certificate">Certificate</option>
                                            <option value="degree">Degree</option>
                                            <option value="id_proof">ID Proof</option>
                                            <option value="address_proof">Address Proof</option>
                                            <option value="training">Training Certificate</option>
                                            <option value="license">License</option>
                                            <option value="qualification">Qualification Document</option>
                                            <option value="experience_letter">Experience Letter</option>
                                            <option value="recommendation">Recommendation Letter</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                </div>
                            </form>
                            
                            <label class="file-upload" for="document-upload">
                                <input type="file" id="document-upload" class="file-input" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <div class="file-upload-text">Drag and drop files here, or click to browse</div>
                                <div class="file-upload-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</div>
                            </label>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin-bottom: 1rem;">Your Documents</h3>
                            
                            <!-- Document List will be loaded dynamically -->
                            <div id="documents-list">
                                <div class="loading-message" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    Loading documents...
                                </div>
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
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Avatar 1">
                </div>
                <div class="avatar-option" onclick="selectAvatar(2)">
                    <img src="https://randomuser.me/api/portraits/men/40.jpg" alt="Avatar 2">
                </div>
                <div class="avatar-option" onclick="selectAvatar(3)">
                    <img src="https://randomuser.me/api/portraits/men/55.jpg" alt="Avatar 3">
                </div>
                <div class="avatar-option" onclick="selectAvatar(4)">
                    <img src="https://randomuser.me/api/portraits/men/65.jpg" alt="Avatar 4">
                </div>
                <div class="avatar-option" onclick="selectAvatar(5)">
                    <img src="https://randomuser.me/api/portraits/men/75.jpg" alt="Avatar 5">
                </div>
                <div class="avatar-option" onclick="selectAvatar(6)">
                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Avatar 6">
                </div>
                <div class="avatar-option" onclick="selectAvatar(7)">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Avatar 7">
                </div>
                <div class="avatar-option" onclick="selectAvatar(8)">
                    <img src="https://randomuser.me/api/portraits/women/55.jpg" alt="Avatar 8">
                </div>
                <div class="avatar-option" onclick="selectAvatar(9)">
                    <img src="https://randomuser.me/api/portraits/women/65.jpg" alt="Avatar 9">
                </div>
                <div class="avatar-option" onclick="selectAvatar(10)">
                    <img src="https://randomuser.me/api/portraits/women/75.jpg" alt="Avatar 10">
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
            <h3 class="modal-title">Teacher ID QR Code</h3>
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
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($teacher_info['employee_number'] ?? 'TEACHER'); ?>" alt="Teacher ID QR Code" style="max-width: 180px; max-height: 180px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($teacher_info['full_name'] ?? 'Teacher Name'); ?></div>
                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($teacher_info['employee_number'] ?? 'N/A'); ?></div>
                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($teacher_info['position'] ?? 'Teacher'); ?></div>
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
        const avatarUpload = document.getElementById('avatar-upload');
        const selectedAvatar = document.querySelector('.avatar-option.selected img');
        
        if (avatarUpload.files.length > 0) {
            // Handle file upload
            const formData = new FormData();
            formData.append('profile_photo', avatarUpload.files[0]);
            formData.append('action', 'upload_profile_photo');
            
            fetch('profile_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profile-image').src = '../../' + data.photo_url;
                    alert('Profile photo updated successfully!');
                    hideAvatarModal();
                } else {
                    alert('Error uploading photo: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while uploading the photo');
            });
        } else if (selectedAvatar) {
            // Handle predefined avatar selection
            document.getElementById('profile-image').src = selectedAvatar.src;
            hideAvatarModal();
            alert('Avatar updated successfully!');
        } else {
            alert('Please select an avatar or upload a photo');
        }
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
        
        if (newPassword.length < 8) {
            alert('New password must be at least 8 characters long');
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'change_password');
        formData.append('current_password', currentPassword);
        formData.append('new_password', newPassword);
        formData.append('confirm_password', confirmPassword);
        
        // Show loading state
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Changing...';
        button.disabled = true;
        
        fetch('profile_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Password changed successfully!');
                document.getElementById('change-password-form').reset();
                hidePasswordModal();
            } else {
                alert('Error changing password: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while changing the password');
        })
        .finally(() => {
            button.textContent = originalText;
            button.disabled = false;
        });
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
        const form = document.getElementById('personal-info-form');
        const formData = new FormData(form);
        formData.append('action', 'update_personal_info');

        // Show loading state
        const saveButton = event.target;
        const originalText = saveButton.textContent;
        saveButton.textContent = 'Saving...';
        saveButton.disabled = true;

        fetch('profile_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Profile updated successfully!');
                // Update profile sidebar with new information
                const fullName = formData.get('first_name') + ' ' + formData.get('last_name');
                document.querySelector('.profile-name').textContent = fullName;
                
                // Update other profile info displays
                const phoneElement = document.querySelector('.profile-info-item:nth-child(2) .profile-info-text');
                if (phoneElement) phoneElement.textContent = formData.get('phone') || 'N/A';
                
                const emailElement = document.querySelector('.profile-info-item:nth-child(3) .profile-info-text');
                if (emailElement) emailElement.textContent = formData.get('email') || 'N/A';
            } else {
                alert('Error updating profile: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the profile');
        })
        .finally(() => {
            saveButton.textContent = originalText;
            saveButton.disabled = false;
        });
    }
    
    function resetForm() {
        if (confirm('Discard all changes?')) {
            location.reload(); // Reload to get original values
        }
    }
    
    function downloadInformation() {
        // In a real application, this would generate and download a PDF with teacher information
        alert('Your information has been downloaded');
    }
    
    // Document management functions
    function loadDocuments() {
        fetch('profile_actions.php?action=get_documents')
        .then(response => response.json())
        .then(data => {
            const documentsList = document.getElementById('documents-list');
            
            if (data.success && data.documents && data.documents.length > 0) {
                documentsList.innerHTML = '';
                data.documents.forEach(doc => {
                    const docCard = createDocumentCard(doc);
                    documentsList.appendChild(docCard);
                });
            } else {
                documentsList.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6b7280;">No documents uploaded yet.</div>';
            }
        })
        .catch(error => {
            console.error('Error loading documents:', error);
            document.getElementById('documents-list').innerHTML = '<div style="text-align: center; padding: 2rem; color: #ef4444;">Error loading documents.</div>';
        });
    }
    
    function createDocumentCard(doc) {
        const card = document.createElement('div');
        card.className = 'document-card';
        
        const iconType = getDocumentIcon(doc.original_filename);
        const fileSize = formatFileSize(doc.file_size);
        const uploadDate = new Date(doc.uploaded_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
        
        card.innerHTML = `
            <div class="document-icon">
                ${iconType}
            </div>
            <div class="document-details">
                <h4 class="document-title">${doc.document_name}</h4>
                <p class="document-info">Uploaded on: ${uploadDate} • ${fileSize}</p>
            </div>
            <div class="document-actions">
                <button class="btn btn-sm btn-secondary" onclick="viewDocument(${doc.id})" title="View Document">View</button>
                <button class="btn btn-sm btn-outline" onclick="downloadDocument(${doc.id})" title="Download Document">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteDocument(${doc.id})" title="Delete Document">Delete</button>
            </div>
        `;
        
        return card;
    }
    
    function getDocumentIcon(filename) {
        const extension = filename.split('.').pop().toLowerCase();
        
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                <circle cx="8.5" cy="8.5" r="1.5" />
                <polyline points="21 15 16 10 5 21" />
            </svg>`;
        } else {
            return `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <polyline points="10 9 9 9 8 9" />
            </svg>`;
        }
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function viewDocument(id) {
        // Open document using the view_document.php endpoint
        window.open('view_document.php?id=' + id, '_blank');
    }
    
    function downloadDocument(id) {
        // Download document using the download_document.php endpoint
        window.location.href = 'download_document.php?id=' + id;
    }
    
    function deleteDocument(id) {
        if (confirm('Are you sure you want to delete this document?')) {
            const formData = new FormData();
            formData.append('action', 'delete_document');
            formData.append('document_id', id);
            
            fetch('profile_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Document deleted successfully');
                    loadDocuments(); // Reload the documents list
                } else {
                    alert('Error deleting document: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the document');
            });
        }
    }
    
    // Handle document upload
    document.addEventListener('DOMContentLoaded', function() {
        const documentUpload = document.getElementById('document-upload');
        const documentNameInput = document.getElementById('document-name');
        const documentTypeSelect = document.getElementById('document-type');
        
        if (documentUpload) {
            documentUpload.addEventListener('change', function() {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const documentName = documentNameInput.value.trim();
                    const documentType = documentTypeSelect.value;
                    
                    if (!documentName) {
                        alert('Please enter a document name');
                        documentNameInput.focus();
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('document', file);
                    formData.append('document_name', documentName);
                    formData.append('document_type', documentType);
                    formData.append('action', 'upload_document');
                    
                    // Show upload progress
                    const uploadArea = document.querySelector('.file-upload');
                    const originalContent = uploadArea.innerHTML;
                    uploadArea.innerHTML = '<div style="text-align: center; padding: 2rem; color: #6b7280;">Uploading...</div>';
                    
                    fetch('profile_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Document uploaded successfully!');
                            documentNameInput.value = '';
                            documentUpload.value = '';
                            loadDocuments(); // Reload the documents list
                        } else {
                            alert('Error uploading document: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while uploading the document');
                    })
                    .finally(() => {
                        uploadArea.innerHTML = originalContent;
                        // Re-add event listener to the new element
                        const newDocumentUpload = document.getElementById('document-upload');
                        if (newDocumentUpload) {
                            newDocumentUpload.addEventListener('change', arguments.callee);
                        }
                    });
                }
            });
        }
        
        // Load documents when documents tab is opened
        loadDocuments();
    });
    
    // Add page transition animations (same as the dashboard)
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