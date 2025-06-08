<?php include 'sidebar.php'; ?>

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
                            <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Teacher Profile" id="profile-image">
                            <div class="change-photo" onclick="showAvatarModal()">Change Photo</div>
                        </div>
                        <h2 class="profile-name">John Smith</h2>
                        <p class="profile-role">Mathematics Teacher</p>
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
                                <div class="profile-info-text">TCH-2023-1025</div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Phone</div>
                                <div class="profile-info-text">+1 (123) 456-7890</div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                <polyline points="22,6 12,13 2,6"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Email</div>
                                <div class="profile-info-text">john.smith@schooldomain.edu</div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Department</div>
                                <div class="profile-info-text">Mathematics</div>
                            </div>
                        </div>
                        <div class="profile-info-item">
                            <svg class="profile-info-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M8 2h8a2 2 0 0 1 2 2v14.5a3.5 3.5 0 0 0-7 0V18H8a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z"/>
                                <path d="M15 22v-3.5a3.5 3.5 0 0 0-7 0V22"/>
                            </svg>
                            <div>
                                <div class="profile-info-label">Joined</div>
                                <div class="profile-info-text">August 12, 2023</div>
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
                                            <label class="form-label" for="firstName">First Name</label>
                                            <input type="text" id="firstName" class="form-input" value="John">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="lastName">Last Name</label>
                                            <input type="text" id="lastName" class="form-input" value="Smith">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="dob">Date of Birth</label>
                                            <input type="date" id="dob" class="form-input" value="1985-06-15">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="gender">Gender</label>
                                            <select id="gender" class="form-select">
                                                <option value="male" selected>Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                                <option value="prefer-not">Prefer not to say</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Contact Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="email">Email Address</label>
                                            <input type="email" id="email" class="form-input" value="john.smith@schooldomain.edu">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Phone Number</label>
                                            <input type="tel" id="phone" class="form-input" value="+1 (123) 456-7890">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="altEmail">Alternative Email (Optional)</label>
                                            <input type="email" id="altEmail" class="form-input" value="johnsmith@personal.com">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="emergencyPhone">Emergency Contact Number</label>
                                            <input type="tel" id="emergencyPhone" class="form-input" value="+1 (987) 654-3210">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Address Information</h3>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="address">Street Address</label>
                                            <input type="text" id="address" class="form-input" value="123 Education Street, Apt 4B">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="city">City</label>
                                            <input type="text" id="city" class="form-input" value="Springfield">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="state">State/Province</label>
                                            <input type="text" id="state" class="form-input" value="IL">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="zipCode">ZIP/Postal Code</label>
                                            <input type="text" id="zipCode" class="form-input" value="62704">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="country">Country</label>
                                            <select id="country" class="form-select">
                                                <option value="us" selected>United States</option>
                                                <option value="ca">Canada</option>
                                                <option value="uk">United Kingdom</option>
                                                <option value="au">Australia</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h3 class="form-section-title">Professional Information</h3>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="department">Department</label>
                                            <select id="department" class="form-select">
                                                <option value="mathematics" selected>Mathematics</option>
                                                <option value="science">Science</option>
                                                <option value="english">English</option>
                                                <option value="history">History</option>
                                                <option value="computer-science">Computer Science</option>
                                                <option value="physics">Physics</option>
                                                <option value="chemistry">Chemistry</option>
                                                <option value="biology">Biology</option>
                                                <option value="geography">Geography</option>
                                                <option value="physical-education">Physical Education</option>
                                                <option value="arts">Arts</option>
                                                <option value="music">Music</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="position">Position</label>
                                            <select id="position" class="form-select">
                                                <option value="teacher" selected>Teacher</option>
                                                <option value="senior-teacher">Senior Teacher</option>
                                                <option value="head-of-department">Head of Department</option>
                                                <option value="assistant-principal">Assistant Principal</option>
                                                <option value="principal">Principal</option>
                                                <option value="coordinator">Coordinator</option>
                                                <option value="counselor">Counselor</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label" for="employmentDate">Employment Date</label>
                                            <input type="date" id="employmentDate" class="form-input" value="2023-08-12">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label" for="employeeID">Employee ID (Read Only)</label>
                                            <input type="text" id="employeeID" class="form-input" value="TCH-2023-1025" readonly style="background-color: #f9fafb;">
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="flex: 0 0 100%;">
                                            <label class="form-label" for="bio">Short Bio (displayed on teacher profile)</label>
                                            <textarea id="bio" class="form-textarea">Mathematics teacher with 8+ years of experience teaching at secondary level. Specializing in Algebra and Calculus. Passionate about making math accessible and engaging for all students through innovative teaching methods and real-world applications.</textarea>
                                            <div class="form-hint">Max 500 characters</div>
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
                            
                            <label class="file-upload" for="document-upload">
                                <input type="file" id="document-upload" class="file-input">
                                <svg class="file-upload-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <div class="file-upload-text">Drag and drop files here, or click to browse</div>
                                <div class="file-upload-hint">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</div>
                            </label>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin-bottom: 1rem;">Your Documents</h3>
                            
                            <!-- Document List -->
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
                                    <h4 class="document-title">Teaching_Certificate.pdf</h4>
                                    <p class="document-info">Uploaded on: Feb 15, 2024 • 1.2 MB</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="viewDocument(1)">View</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteDocument(1)">Delete</button>
                                </div>
                            </div>
                            
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
                                    <h4 class="document-title">Masters_Degree_Certificate.pdf</h4>
                                    <p class="document-info">Uploaded on: Aug 18, 2023 • 2.5 MB</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="viewDocument(2)">View</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteDocument(2)">Delete</button>
                                </div>
                            </div>
                            
                            <div class="document-card">
                                <div class="document-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                </div>
                                <div class="document-details">
                                    <h4 class="document-title">Profile_Photo.jpg</h4>
                                    <p class="document-info">Uploaded on: Aug 12, 2023 • 245 KB</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="viewDocument(3)">View</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteDocument(3)">Delete</button>
                                </div>
                            </div>
                            
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
                                    <h4 class="document-title">ID_Proof.pdf</h4>
                                    <p class="document-info">Uploaded on: Aug 12, 2023 • 1.8 MB</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-sm btn-secondary" onclick="viewDocument(4)">View</button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteDocument(4)">Delete</button>
                                </div>
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
                    
                    <!-- Notification Settings Tab -->
                    <div id="notification-settings" class="profile-tab-content" style="display: none;">
                        <div class="profile-body">
                            <p style="color: #6b7280; font-size: 0.9375rem; margin-bottom: 1.5rem;">Configure how you receive notifications and alerts from the system. You can customize settings for different types of notifications.</p>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin-bottom: 1rem;">Email Notifications</h3>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Attendance Updates</h4>
                                    <p>Receive email updates for student attendance records</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>School Announcements</h4>
                                    <p>Receive important announcements from school administration</p>
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
                                    <h4>Homework Submissions</h4>
                                    <p>Get notified when students submit homework assignments</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Parent Messages</h4>
                                    <p>Receive notifications when parents send you messages</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin: 1.5rem 0 1rem;">Push Notifications</h3>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Attendance Updates</h4>
                                    <p>Receive push notifications for student attendance records</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>School Announcements</h4>
                                    <p>Receive important announcements from school administration</p>
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
                                    <h4>Homework Submissions</h4>
                                    <p>Get notified when students submit homework assignments</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Parent Messages</h4>
                                    <p>Receive notifications when parents send you messages</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                            
                            <h3 style="font-size: 1rem; color: #4b5563; margin: 1.5rem 0 1rem;">SMS Notifications</h3>
                            
                            <div class="notification-option">
                                <div class="notification-details">
                                    <h4>Emergency Alerts Only</h4>
                                    <p>Receive SMS notifications only for emergency situations</p>
                                </div>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
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
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=TCH-2023-1025" alt="Teacher ID QR Code" style="max-width: 180px; max-height: 180px;">
            </div>
            
            <div style="margin-bottom: 1rem;">
                <div style="font-size: 1rem; font-weight: 600; color: #1f2937; margin-bottom: 0.25rem;">John Smith</div>
                <div style="font-size: 0.875rem; color: #6b7280;">TCH-2023-1025</div>
                <div style="font-size: 0.875rem; color: #6b7280;">Mathematics Teacher</div>
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
        // In a real application, this would generate and download a PDF with teacher information
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