<?php 
include 'sidebar.php'; 
require_once 'con.php';

// Get current user's profile data
$user_id = $_SESSION['user_id'];
$query = "SELECT u.id, u.email, u.full_name, u.created_at,
                 t.employee_number, t.joined_date, t.qualification, t.date_of_birth,
                 t.profile_photo, t.address, t.city, t.phone, t.alt_email,
                 t.emergency_contact, t.gender, t.state, t.zip_code, t.country,
                 t.department, t.position, t.experience_years, t.bio
          FROM users u 
          LEFT JOIN teachers t ON u.id = t.user_id 
          WHERE u.id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

// Split full name
$nameParts = explode(' ', $profile['full_name'] ?? '', 2);
$firstName = $nameParts[0] ?? '';
$lastName = $nameParts[1] ?? '';

// Default profile photo
$profilePhoto = !empty($profile['profile_photo']) ? '../../' . $profile['profile_photo'] : 'https://randomuser.me/api/portraits/men/32.jpg';
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
    
    <!-- Enhanced Dialog Styles -->
    <style>
        /* Modern Modal Styling */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        .modal-dialog {
            background: white;
            border-radius: 12px;
            padding: 0;
            min-width: 400px;
            max-width: 500px;
            max-height: 90vh;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            transform: scale(0.8) translateY(20px);
            transition: all 0.3s ease;
        }
        
        .modal-overlay.show .modal-dialog {
            transform: scale(1) translateY(0);
        }
        
        .modal-header {
            padding: 20px 24px 16px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .modal-icon {
            width: 24px;
            height: 24px;
        }
        
        .modal-icon.success {
            color: #10b981;
        }
        
        .modal-icon.error {
            color: #ef4444;
        }
        
        .modal-icon.warning {
            color: #f59e0b;
        }
        
        .modal-icon.info {
            color: #3b82f6;
        }
        
        .modal-close {
            background: none;
            border: none;
            color: #6b7280;
            font-size: 24px;
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            transition: all 0.2s ease;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-close:hover {
            background: #f3f4f6;
            color: #374151;
        }
        
        .modal-body {
            padding: 20px 24px;
        }
        
        .modal-message {
            color: #6b7280;
            line-height: 1.6;
            margin: 0;
        }
        
        .modal-footer {
            padding: 16px 24px 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 12px;
            justify-content: flex-end;
        }
        
        .modal-btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
            font-size: 0.875rem;
        }
        
        .modal-btn-primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .modal-btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }
        
        .modal-btn-secondary {
            background: #f9fafb;
            color: #374151;
            border-color: #d1d5db;
        }
        
        .modal-btn-secondary:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .modal-btn-danger {
            background: #ef4444;
            color: white;
            border-color: #ef4444;
        }
        
        .modal-btn-danger:hover {
            background: #dc2626;
            border-color: #dc2626;
        }
        
        /* Progress Modal */
        .progress-container {
            margin: 16px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            border-radius: 4px;
            transition: width 0.3s ease;
            width: 0%;
        }
        
        .progress-text {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 8px;
            text-align: center;
        }
    </style>
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
                            <img src="<?php echo htmlspecialchars($profilePhoto); ?>" alt="Teacher Profile" id="profile-image">
                            <div class="change-photo" onclick="showAvatarModal()">Change Photo</div>
                        </div>
                        <h2 class="profile-name"><?php echo htmlspecialchars($profile['full_name'] ?? 'No Name'); ?></h2>
                        <p class="profile-role"><?php echo htmlspecialchars($profile['department'] ?? 'Teacher'); ?> <?php echo $profile['position'] ? '- ' . htmlspecialchars($profile['position']) : ''; ?></p>
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
                                <div class="profile-info-text"><?php echo htmlspecialchars($profile['employee_number'] ?? 'Not Set'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Phone</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($profile['phone'] ?? 'Not Set'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Email</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($profile['email'] ?? 'Not Set'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Department</div>
                                <div class="profile-info-text"><?php echo htmlspecialchars($profile['department'] ?? 'Not Set'); ?></div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 2h8a2 2 0 0 1 2 2v14.5a3.5 3.5 0 0 0-7 0V18H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                                <path d="M15 22v-3.5a3.5 3.5 0 0 0-7 0V22"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Joined</div>
                                <div class="profile-info-text"><?php echo $profile['joined_date'] ? date('F j, Y', strtotime($profile['joined_date'])) : 'Not Set'; ?></div>
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
                        <div class="profile-tab" onclick="changeTab(this, 'qualifications')">Qualifications</div>
                    </div>
                    
                    <!-- Personal Information Tab -->
                    <div id="personal-info" class="profile-tab-content">
                        <div class="profile-body">
                            <form id="personal-info-form" onsubmit="updatePersonalInfo(event)">
                                <div class="form-section">
                                    <h3 class="form-section-title">Basic Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="firstName">First Name</label>
                                            <input type="text" id="firstName" name="first_name" class="form-input" value="<?php echo htmlspecialchars($firstName); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="lastName">Last Name</label>
                                            <input type="text" id="lastName" name="last_name" class="form-input" value="<?php echo htmlspecialchars($lastName); ?>" required>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="dob">Date of Birth</label>
                                            <input type="date" id="dob" name="date_of_birth" class="form-input" value="<?php echo $profile['date_of_birth'] ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="gender">Gender</label>
                                            <select id="gender" name="gender" class="form-select">
                                                <option value="">Select Gender</option>
                                                <option value="male" <?php echo ($profile['gender'] ?? '') === 'male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="female" <?php echo ($profile['gender'] ?? '') === 'female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="other" <?php echo ($profile['gender'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                                <option value="prefer-not" <?php echo ($profile['gender'] ?? '') === 'prefer-not' ? 'selected' : ''; ?>>Prefer not to say</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Contact Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($profile['email'] ?? ''); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($profile['phone'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="altEmail">Alternative Email (Optional)</label>
                                            <input type="email" id="altEmail" name="alt_email" class="form-input" value="<?php echo htmlspecialchars($profile['alt_email'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="emergencyPhone">Emergency Contact Number</label>
                                            <input type="tel" id="emergencyPhone" name="emergency_contact" class="form-input" value="<?php echo htmlspecialchars($profile['emergency_contact'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Address Information</h3>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="address">Street Address</label>
                                            <input type="text" id="address" name="address" class="form-input" value="<?php echo htmlspecialchars($profile['address'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="city">City</label>
                                            <input type="text" id="city" name="city" class="form-input" value="<?php echo htmlspecialchars($profile['city'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="state">State/Province</label>
                                            <input type="text" id="state" name="state" class="form-input" value="<?php echo htmlspecialchars($profile['state'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="zipCode">ZIP/Postal Code</label>
                                            <input type="text" id="zipCode" name="zip_code" class="form-input" value="<?php echo htmlspecialchars($profile['zip_code'] ?? ''); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="country">Country</label>
                                            <select id="country" name="country" class="form-select">
                                                <option value="">Select Country</option>
                                                <option value="us" <?php echo ($profile['country'] ?? '') === 'us' ? 'selected' : ''; ?>>United States</option>
                                                <option value="ca" <?php echo ($profile['country'] ?? '') === 'ca' ? 'selected' : ''; ?>>Canada</option>
                                                <option value="uk" <?php echo ($profile['country'] ?? '') === 'uk' ? 'selected' : ''; ?>>United Kingdom</option>
                                                <option value="au" <?php echo ($profile['country'] ?? '') === 'au' ? 'selected' : ''; ?>>Australia</option>
                                                <option value="in" <?php echo ($profile['country'] ?? '') === 'in' ? 'selected' : ''; ?>>India</option>
                                                <option value="other" <?php echo ($profile['country'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Professional Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="department">Department</label>
                                            <select id="department" name="department" class="form-select">
                                                <option value="">Select Department</option>
                                                <option value="mathematics" <?php echo ($profile['department'] ?? '') === 'mathematics' ? 'selected' : ''; ?>>Mathematics</option>
                                                <option value="science" <?php echo ($profile['department'] ?? '') === 'science' ? 'selected' : ''; ?>>Science</option>
                                                <option value="english" <?php echo ($profile['department'] ?? '') === 'english' ? 'selected' : ''; ?>>English</option>
                                                <option value="history" <?php echo ($profile['department'] ?? '') === 'history' ? 'selected' : ''; ?>>History</option>
                                                <option value="computer-science" <?php echo ($profile['department'] ?? '') === 'computer-science' ? 'selected' : ''; ?>>Computer Science</option>
                                                <option value="physics" <?php echo ($profile['department'] ?? '') === 'physics' ? 'selected' : ''; ?>>Physics</option>
                                                <option value="chemistry" <?php echo ($profile['department'] ?? '') === 'chemistry' ? 'selected' : ''; ?>>Chemistry</option>
                                                <option value="biology" <?php echo ($profile['department'] ?? '') === 'biology' ? 'selected' : ''; ?>>Biology</option>
                                                <option value="geography" <?php echo ($profile['department'] ?? '') === 'geography' ? 'selected' : ''; ?>>Geography</option>
                                                <option value="physical-education" <?php echo ($profile['department'] ?? '') === 'physical-education' ? 'selected' : ''; ?>>Physical Education</option>
                                                <option value="arts" <?php echo ($profile['department'] ?? '') === 'arts' ? 'selected' : ''; ?>>Arts</option>
                                                <option value="music" <?php echo ($profile['department'] ?? '') === 'music' ? 'selected' : ''; ?>>Music</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="position">Position</label>
                                            <input type="text" id="position" name="position" class="form-input" value="<?php echo htmlspecialchars($profile['position'] ?? ''); ?>" placeholder="e.g., Senior Teacher, Head of Department">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="employmentDate">Employment Date</label>
                                            <input type="date" id="employmentDate" name="joined_date" class="form-input" value="<?php echo $profile['joined_date'] ?? ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="employeeID">Employee ID (Read Only)</label>
                                            <input type="text" id="employeeID" class="form-input" value="<?php echo htmlspecialchars($profile['employee_number'] ?? 'Not Assigned'); ?>" readonly style="background-color: #f9fafb;">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="bio">Short Bio (displayed on teacher profile)</label>
                                            <textarea id="bio" name="bio" class="form-textarea" maxlength="500"><?php echo htmlspecialchars($profile['bio'] ?? ''); ?></textarea>
                                            <div class="form-hint">Max 500 characters</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="resetPersonalInfoForm()">Cancel Changes</button>
                                    <button type="submit" class="btn btn-primary">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Documents Tab -->
                    <div id="documents" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">Upload and manage your personal and professional documents. All documents are securely stored and only accessible to authorized personnel.</p>
                            
                            <label class="file-upload" for="document-upload">
                                <input type="file" id="document-upload" class="file-input" onchange="uploadDocument(this.files[0])">
                                <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <div class="file-upload-text">Drag and drop files here, or click to browse</div>
                                <div class="file-upload-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</div>
                            </label>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin-bottom: 1rem;">Your Documents</h3>
                            
                            <!-- Document List - Will be populated dynamically -->
                            <div id="documents-container">
                                <p style="color: #9ca3af; text-align: center; padding: 2rem;">Loading documents...</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Qualifications Tab -->
                    <div id="qualifications" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">Add and manage your educational qualifications, certifications, and professional development courses.</p>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Education</h3>
                                
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="degree1">Degree/Qualification</label>
                                            <input type="text" id="degree1" class="form-input" value="Master of Science in Mathematics">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="institution1">Institution</label>
                                            <input type="text" id="institution1" class="form-input" value="University of Illinois">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="startDate1">Start Date</label>
                                            <input type="month" id="startDate1" class="form-input" value="2016-09">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="endDate1">End Date</label>
                                            <input type="month" id="endDate1" class="form-input" value="2018-05">
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end;">
                                        <button class="btn btn-sm btn-danger" onclick="removeEducation(1)">Remove</button>
                                    </div>
                                </div>
                                
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="degree2">Degree/Qualification</label>
                                            <input type="text" id="degree2" class="form-input" value="Bachelor of Science in Mathematics">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="institution2">Institution</label>
                                            <input type="text" id="institution2" class="form-input" value="State University of New York">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="startDate2">Start Date</label>
                                            <input type="month" id="startDate2" class="form-input" value="2012-09">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="endDate2">End Date</label>
                                            <input type="month" id="endDate2" class="form-input" value="2016-05">
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end;">
                                        <button class="btn btn-sm btn-danger" onclick="removeEducation(2)">Remove</button>
                                    </div>
                                </div>
                                
                                <button class="btn btn-secondary btn-sm" onclick="addEducation()">
                                    <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    Add Education
                                </button>
                            </div>
                            
                            <div class="form-section">
                                <h3 class="form-section-title">Certifications</h3>
                                
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="cert1">Certification Name</label>
                                            <input type="text" id="cert1" class="form-input" value="State Teaching License">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="certIssuer1">Issuing Organization</label>
                                            <input type="text" id="certIssuer1" class="form-input" value="Illinois State Board of Education">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="certDate1">Issue Date</label>
                                            <input type="month" id="certDate1" class="form-input" value="2019-06">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="certExpiry1">Expiry Date (if applicable)</label>
                                            <input type="month" id="certExpiry1" class="form-input" value="2024-06">
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end;">
                                        <button class="btn btn-sm btn-danger" onclick="removeCertification(1)">Remove</button>
                                    </div>
                                </div>
                                
                                <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="cert2">Certification Name</label>
                                            <input type="text" id="cert2" class="form-input" value="Advanced Math Teaching Certificate">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="certIssuer2">Issuing Organization</label>
                                            <input type="text" id="certIssuer2" class="form-input" value="National Council of Teachers of Mathematics">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="certDate2">Issue Date</label>
                                            <input type="month" id="certDate2" class="form-input" value="2021-03">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="certExpiry2">Expiry Date (if applicable)</label>
                                            <input type="month" id="certExpiry2" class="form-input" value="">
                                        </div>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end;">
                                        <button class="btn btn-sm btn-danger" onclick="removeCertification(2)">Remove</button>
                                    </div>
                                </div>
                                
                                <button class="btn btn-secondary btn-sm" onclick="addCertification()">
                                    <svg style="width: 16px; height: 16px; margin-right: 0.5rem;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    Add Certification
                                </button>
                            </div>
                            
                            <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="saveQualifications()">Save Changes</button>
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
                <!-- QR code with dynamic teacher data -->
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode(($profile['employee_number'] ?? 'TCH-' . date('Y') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT)) . '|' . ($profile['full_name'] ?? 'Teacher') . '|' . ($profile['department'] ?? 'General')); ?>" alt="Teacher ID QR Code" style="max-width: 180px; max-height: 180px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($profile['full_name'] ?? 'Teacher Name'); ?></div>
                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars($profile['employee_number'] ?? 'TCH-' . date('Y') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT)); ?></div>
                <div style="font-size: 0.875rem; color: #6b7280;"><?php echo htmlspecialchars(ucfirst($profile['department'] ?? 'General')); ?> <?php echo $profile['position'] ? htmlspecialchars($profile['position']) : 'Teacher'; ?></div>
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
    // Enhanced Dialog System
    function showDialog(options) {
        const { 
            title, 
            message, 
            type = 'info', 
            confirmText = 'OK', 
            cancelText = 'Cancel', 
            showCancel = false,
            onConfirm = null,
            onCancel = null 
        } = options;
        
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        
        // Icon based on type
        const icons = {
            success: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>',
            error: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>',
            warning: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>',
            info: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
        };
        
        overlay.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <svg class="modal-icon ${type}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            ${icons[type]}
                        </svg>
                        ${title}
                    </h3>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
                </div>
                <div class="modal-body">
                    <p class="modal-message">${message}</p>
                </div>
                <div class="modal-footer">
                    ${showCancel ? `<button class="modal-btn modal-btn-secondary" onclick="this.closest('.modal-overlay').remove(); ${onCancel ? 'window.' + onCancel.name + '()' : ''}">${cancelText}</button>` : ''}
                    <button class="modal-btn modal-btn-primary" onclick="this.closest('.modal-overlay').remove(); ${onConfirm ? 'window.' + onConfirm.name + '()' : ''}">${confirmText}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        setTimeout(() => overlay.classList.add('show'), 10);
        
        return overlay;
    }
    
    function showSuccessDialog(title, message, callback = null) {
        return showDialog({
            title,
            message,
            type: 'success',
            confirmText: 'OK',
            onConfirm: callback
        });
    }
    
    function showErrorDialog(title, message, callback = null) {
        return showDialog({
            title,
            message,
            type: 'error',
            confirmText: 'OK',
            onConfirm: callback
        });
    }
    
    function showWarningDialog(title, message, callback = null) {
        return showDialog({
            title,
            message,
            type: 'warning',
            confirmText: 'OK',
            onConfirm: callback
        });
    }
    
    function showConfirmDialog(title, message, onConfirm, onCancel = null) {
        return showDialog({
            title,
            message,
            type: 'warning',
            confirmText: 'Confirm',
            cancelText: 'Cancel',
            showCancel: true,
            onConfirm,
            onCancel
        });
    }

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
        showSuccessDialog(
            'QR Code Download',
            'Your QR code has been generated and is ready for download.',
            () => {
                // In a real application, this would download the QR code image
                console.log('Downloading QR code...');
            }
        );
    }
    
    // Form management functions
    function updatePersonalInfo(event) {
        event.preventDefault();
        
        const formData = new FormData(event.target);
        formData.append('action', 'update_personal_info');
        
        // Show loading state
        const submitBtn = event.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Saving...';
        submitBtn.disabled = true;
        
        fetch('profile_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Personal information updated successfully!', 'success');
                // Update sidebar info
                location.reload();
            } else {
                showNotification(data.message || 'Failed to update profile', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while updating profile', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function resetPersonalInfoForm() {
        showConfirmDialog(
            'Discard Changes',
            'Are you sure you want to discard all unsaved changes?',
            () => location.reload()
        );
    }
    
    function savePersonalInfo() {
        // Trigger form submission
        document.getElementById('personal-info-form').dispatchEvent(new Event('submit'));
    }
    
    function resetForm() {
        resetPersonalInfoForm();
    }
    
    // Profile photo upload
    function showAvatarModal() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.onchange = function(e) {
            uploadProfilePhoto(e.target.files[0]);
        };
        input.click();
    }
    
    function uploadProfilePhoto(file) {
        if (!file) return;
        
        const formData = new FormData();
        formData.append('profile_photo', file);
        formData.append('action', 'upload_profile_photo');
        
        fetch('profile_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('profile-image').src = '../../' + data.photo_url;
                showNotification('Profile photo updated successfully!', 'success');
            } else {
                showNotification(data.message || 'Failed to upload photo', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while uploading photo', 'error');
        });
    }
    
    // Password change functionality
    function changePassword() {
        const formData = new FormData();
        formData.append('action', 'change_password');
        formData.append('current_password', document.getElementById('currentPassword').value);
        formData.append('new_password', document.getElementById('newPassword').value);
        formData.append('confirm_password', document.getElementById('confirmPassword').value);
        
        fetch('profile_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Password changed successfully!', 'success');
                hidePasswordModal();
                // Clear form
                document.getElementById('currentPassword').value = '';
                document.getElementById('newPassword').value = '';
                document.getElementById('confirmPassword').value = '';
            } else {
                showNotification(data.message || 'Failed to change password', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('An error occurred while changing password', 'error');
        });
    }
    
    // Document management functions
    function loadDocuments() {
        fetch('profile_actions.php?action=get_documents')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayDocuments(data.documents);
            }
        })
        .catch(error => console.error('Error loading documents:', error));
    }
    
    function displayDocuments(documents) {
        const container = document.getElementById('documents-container');
        let documentsHtml = '';
        
        if (documents.length === 0) {
            documentsHtml = '<p style="color: #9ca3af; text-align: center; padding: 2rem;">No documents uploaded yet.</p>';
        } else {
            documents.forEach(doc => {
                documentsHtml += `
                    <div class="document-card">
                        <div class="document-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14,2 14,8 20,8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10,9 9,9 8,9"/>
                            </svg>
                        </div>
                        <div class="document-info">
                            <div class="document-name">${doc.document_name}</div>
                            <div class="document-meta">${doc.document_type} • ${formatFileSize(doc.file_size)} • ${formatDate(doc.uploaded_at)}</div>
                        </div>
                        <div class="document-actions">
                            <button class="btn-icon" onclick="window.open('../../${doc.file_path}', '_blank')" title="View">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                            <button class="btn-icon" onclick="deleteDocument(${doc.id})" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3,6 5,6 21,6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                `;
            });
        }
        
        container.innerHTML = documentsHtml;
    }
    
    function uploadDocument(file) {
        if (!file) return;
        
        // Create a custom dialog for document details
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <svg class="modal-icon info" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Upload Document
                    </h3>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
                </div>
                <div class="modal-body">
                    <p class="modal-message">Please provide details for the document: <strong>${file.name}</strong></p>
                    <div style="margin-top: 16px;">
                        <label class="form-label" style="display: block; margin-bottom: 8px;">Document Name</label>
                        <input type="text" id="docName" class="form-input" value="${file.name.split('.')[0]}" style="width: 100%; margin-bottom: 16px;">
                        
                        <label class="form-label" style="display: block; margin-bottom: 8px;">Document Type</label>
                        <select id="docType" class="form-select" style="width: 100%;">
                            <option value="resume">Resume/CV</option>
                            <option value="certificate">Certificate</option>
                            <option value="id_proof">ID Proof</option>
                            <option value="address_proof">Address Proof</option>
                            <option value="other" selected>Other</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="modal-btn modal-btn-secondary" onclick="this.closest('.modal-overlay').remove()">Cancel</button>
                    <button class="modal-btn modal-btn-primary" onclick="processDocumentUpload()">Upload</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        setTimeout(() => overlay.classList.add('show'), 10);
        
        // Store file reference for processing
        window.pendingFile = file;
    }
    
    function processDocumentUpload() {
        const file = window.pendingFile;
        const documentName = document.getElementById('docName').value;
        const documentType = document.getElementById('docType').value;
        
        if (!documentName.trim()) {
            showErrorDialog('Error', 'Please enter a document name');
            return;
        }
        
        // Close the dialog
        document.querySelector('.modal-overlay').remove();
        
        const formData = new FormData();
        formData.append('document', file);
        formData.append('document_name', documentName);
        formData.append('document_type', documentType);
        formData.append('action', 'upload_document');
        
        fetch('profile_actions.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessDialog('Success', 'Document uploaded successfully!');
                loadDocuments();
            } else {
                showErrorDialog('Error', data.message || 'Failed to upload document');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorDialog('Error', 'An error occurred while uploading document');
        });
        
        // Clean up
        delete window.pendingFile;
    }
    
    function deleteDocument(id) {
        showConfirmDialog(
            'Delete Document',
            'Are you sure you want to delete this document? This action cannot be undone.',
            () => {
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
                        showSuccessDialog('Success', 'Document deleted successfully!');
                        loadDocuments();
                    } else {
                        showErrorDialog('Error', data.message || 'Failed to delete document');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorDialog('Error', 'An error occurred while deleting document');
                });
            }
        );
    }
    
    // Notification function
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Hide notification
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
    // Utility functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString();
    }
    
    // Load data when tab changes
    function changeTab(tabElement, tabId) {
        // Remove active class from all tabs
        document.querySelectorAll('.profile-tab').forEach(tab => tab.classList.remove('active'));
        document.querySelectorAll('.profile-tab-content').forEach(content => content.style.display = 'none');
        
        // Add active class to clicked tab
        tabElement.classList.add('active');
        document.getElementById(tabId).style.display = 'block';
        
        // Load data based on tab
        if (tabId === 'documents') {
            loadDocuments();
        } else if (tabId === 'qualifications') {
            loadEducation();
        } else if (tabId === 'notification-settings') {
            loadNotificationSettings();
        }
    }
    
    // Load education data
    function loadEducation() {
        fetch('profile_actions.php?action=get_education')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayEducation(data.education);
            }
        })
        .catch(error => console.error('Error loading education:', error));
    }
    
    function displayEducation(education) {
        // This would display education records - implementation depends on the HTML structure
        console.log('Education data:', education);
    }
    
    // Load notification settings
    function loadNotificationSettings() {
        fetch('profile_actions.php?action=get_notification_settings')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationSettings(data.settings);
            }
        })
        .catch(error => console.error('Error loading notification settings:', error));
    }
    
    function updateNotificationSettings(settings) {
        // Update checkbox states based on settings
        Object.keys(settings).forEach(key => {
            const checkbox = document.querySelector(`input[name="${key}"]`);
            if (checkbox) {
                checkbox.checked = Boolean(settings[key]);
            }
        });
    }
    
    function downloadInformation() {
        showSuccessDialog(
            'Download Information',
            'Your teacher information has been prepared for download. The file contains your profile data, qualifications, and contact details.',
            () => {
                // In a real application, this would generate and download a PDF
                // For now, we'll just show a success message
                console.log('Downloading teacher information...');
            }
        );
    }
    
    // Qualifications management functions
    function addEducation() {
        // In a real application, this would add a new education form
        alert('Education field added');
    }
    
    function removeEducation(id) {
        // In a real application, this would remove the education field
        if (confirm('Are you sure you want to remove this education entry?')) {
            alert('Education entry removed');
        }
    }
    
    function addCertification() {
        // In a real application, this would add a new certification form
        alert('Certification field added');
    }
    
    function removeCertification(id) {
        // In a real application, this would remove the certification field
        if (confirm('Are you sure you want to remove this certification entry?')) {
            alert('Certification entry removed');
        }
    }
    
    function saveQualifications() {
        // In a real application, this would save the updated qualifications
        alert('Qualifications updated successfully!');
    }
    
    function resetQualifications() {
        // In a real application, this would reset the qualifications form
        if (confirm('Discard all changes?')) {
            alert('Qualifications reset to original values');
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