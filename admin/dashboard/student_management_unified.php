<?php
/**
 * Unified Student Management System
 * Comprehensive student management interface
 * Role-based access control: Admin can do everything, others have limited access
 */
// Include database connection
require_once 'con.php';
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role
if (!isLoggedIn() || !hasRole(['admin', 'headmaster'])) {
    header("Location: ../../index.php");
    exit;
}



// Get current user role for permission checks
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch required data for dropdowns
$classes_raw = executeQuery("SELECT id, name FROM classes ORDER BY name");
$sections_raw = executeQuery("SELECT id, name, class_id FROM sections ORDER BY class_id, name");
$academic_years_raw = executeQuery("SELECT id, name FROM academic_years ORDER BY start_date DESC");
$parents_raw = executeQuery("
    SELECT u.id, u.full_name, u.email
    FROM users u 
    WHERE u.role = 'parent'
    ORDER BY u.full_name
");

// Ensure data is array for json_encode to prevent JS errors
$classes = is_array($classes_raw) ? $classes_raw : [];
$sections = is_array($sections_raw) ? $sections_raw : [];
$academic_years = is_array($academic_years_raw) ? $academic_years_raw : [];
$parents = is_array($parents_raw) ? $parents_raw : [];

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Unified System</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #fd5d5d;
            --secondary-color: #5856d6;
            --accent-color: #26e7a6;
            --text-primary: #2d3748;
            --text-secondary: #718096;
            --border-color: #e2e8f0;
            --bg-light: #f7fafc;
            --bg-white: #ffffff;
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.1);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --border-radius: 8px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: var(--bg-light);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
        }

        .unified-container {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .header-section {
            background: var(--bg-white);
            padding: 24px;
            border-radius: var(--border-radius);
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }

        .header-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .header-subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .tabs-container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .tabs-nav {
            display: flex;
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
        }

        .tab-button {
            flex: 1;
            padding: 16px 24px;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .tab-button:hover {
            background: rgba(253, 93, 93, 0.05);
            color: var(--primary-color);
        }

        .tab-button.active {
            background: var(--bg-white);
            color: var(--primary-color);
        }

        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary-color);
        }

        .tab-content {
            display: none;
            padding: 24px;
        }

        .tab-content.active {
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .form-label.required::after {
            content: ' *';
            color: #e53e3e;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(253, 93, 93, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .help-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 4px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #e53e3e;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--text-secondary);
            color: white;
        }

        .btn-secondary:hover {
            background: #4a5568;
        }

        .btn-outline {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
        }

        .card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .card-header {
            padding: 20px 24px;
            background: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .card-body {
            padding: 24px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .data-table th, .data-table td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-primary);
        }

        .data-table tr:hover {
            background: rgba(253, 93, 93, 0.02);
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-active {
            background: rgba(38, 231, 166, 0.1);
            color: #059669;
        }

        .status-inactive {
            background: rgba(229, 62, 62, 0.1);
            color: #dc2626;
        }

        .status-suspended {
            background: rgba(234, 179, 8, 0.1);
            color: #a16207;
        }

        .status-graduated {
            background: rgba(88, 86, 214, 0.1);
            color: var(--secondary-color);
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .search-container {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px 12px 48px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .notification {
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            display: none;
        }        .notification.success {
            background: rgba(38, 231, 166, 0.1);
            color: #059669;
            border: 1px solid rgba(38, 231, 166, 0.3);
        }

        .notification.error {
            background: rgba(229, 62, 62, 0.1);
            color: #dc2626;
            border: 1px solid rgba(229, 62, 62, 0.3);
        }

        .notification.info {
            background: rgba(88, 86, 214, 0.1);
            color: var(--secondary-color);
            border: 1px solid rgba(88, 86, 214, 0.3);
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .spinner {
            display: inline-block;
            width: 32px;
            height: 32px;
            border: 3px solid var(--border-color);
            border-radius: 50%;
            border-top-color: var(--primary-color);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Role-based styling */
        .admin-only {
            <?php if ($user_role !== 'admin'): ?>
            display: none !important;
            <?php endif; ?>
        }

        .headmaster-restricted {
            <?php if ($user_role === 'headmaster'): ?>
            opacity: 0.5;
            pointer-events: none;
            <?php endif; ?>
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: none;
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--text-primary);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-secondary);
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: var(--primary-color);
        }

        .modal-body {
            padding: 24px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stats-grid .stat-card {
            background: var(--bg-white);
            padding: 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            text-align: center;
            border-left: 4px solid var(--primary-color);
        }

        .stats-grid .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
            margin-bottom: 8px;
        }

        .stats-grid .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .student-info-cell {
            display: flex;
            flex-direction: column;
        }

        .student-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .student-admission {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
        }

        .pagination-btn {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-primary);
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .pagination-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .unified-container {
                margin-left: 0;
                padding: 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .tabs-nav {
                flex-direction: column;
            }

            .tab-button {
                flex: none;
            }

            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
    <div class="unified-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="header-title">Student Management System</h1>
            <p class="header-subtitle">Comprehensive student information and management interface</p>
        </div>

        <!-- Main Tabs Container -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active" data-tab="list">
                    <i class="fas fa-list"></i> Student List
                </button>
                <button class="tab-button admin-only" data-tab="add">
                    <i class="fas fa-user-plus"></i> Add Student
                </button>
                <button class="tab-button admin-only" data-tab="bulk">
                    <i class="fas fa-users-cog"></i> Bulk Operations
                </button>
                <button class="tab-button" data-tab="reports">
                    <i class="fas fa-chart-bar"></i> Reports
                </button>
            </div>

            <!-- Student List Tab -->
            <div class="tab-content active" id="list-tab">
                <!-- Filters -->
                <div class="filter-grid">
                    <div class="form-group">
                        <label class="form-label">Filter by Class</label>
                        <select id="filterClass" class="form-select">
                            <option value="">All Classes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Filter by Section</label>
                        <select id="filterSection" class="form-select">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Filter by Status</label>
                        <select id="filterStatus" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                            <option value="graduated">Graduated</option>
                            <option value="transferred_out">Transferred Out</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="searchTerm" class="search-input" placeholder="Name, Admission No, Email...">
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <span class="stat-number" id="totalStudentsCount">0</span>
                        <span class="stat-label">Total Students</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number" id="activeStudentsCount">0</span>
                        <span class="stat-label">Active Students</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number" id="classesCount">0</span>
                        <span class="stat-label">Classes</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number" id="sectionsCount">0</span>
                        <span class="stat-label">Sections</span>
                    </div>
                </div>

                <!-- Notifications -->
                <div id="notification" class="notification"></div>

                <!-- Students Table -->
                <div class="card">
                    <div class="card-header">
                        <div class="section-header">
                            <h3 class="card-title">Students List</h3>
                            <div class="actions">
                                <button class="btn btn-outline btn-sm" onclick="refreshStudentList()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="loading" id="loadingIndicator">
                            <div class="spinner"></div>
                            <p>Loading students...</p>
                        </div>
                        
                        <table class="data-table" id="studentsTable" style="display: none;">
                            <thead>
                                <tr>
                                    <th>Student Info</th>
                                    <th>Class & Section</th>
                                    <th>Roll Number</th>
                                    <th>Status</th>
                                    <th>Contact</th>
                                    <th class="admin-only">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <div class="pagination" id="studentPagination" style="display: none;">
                        </div>
                    </div>
                </div>
            </div>            <!-- Add Student Tab -->
            <div class="tab-content admin-only" id="add-tab">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" id="addStudentTitle">Add New Student</h3>
                        <div id="editModeNotice" style="display: none; background: #e6f3ff; padding: 8px 12px; border-radius: 4px; color: #0066cc; margin-top: 8px;">
                            <i class="fas fa-info-circle"></i> You are currently editing a student. Click "Clear Form" to add a new student instead.
                        </div>
                    </div>
                    <div class="card-body">
                        <form id="addStudentForm" enctype="multipart/form-data">
                            <input type="hidden" name="student_id" id="editStudentId" value="">                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label required">Full Name</label>
                                    <input type="text" name="full_name" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Admission Date</label>
                                    <input type="date" name="admission_date" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Date of Birth</label>
                                    <input type="date" name="dob" class="form-input" required>
                                </div>                                <div class="form-group">
                                    <label class="form-label required">Gender</label>
                                    <select name="gender_code" class="form-select" required>
                                        <option value="">Select Gender</option>
                                        <option value="M">Male</option>
                                        <option value="MALE">Male</option>
                                        <option value="F">Female</option>
                                        <option value="FEMALE">Female</option>
                                        <option value="O">Other</option>
                                        <option value="OTHER">Other</option>
                                    </select>
                                </div>                                <div class="form-group">
                                    <label class="form-label">Blood Group</label>
                                    <select name="blood_group_code" class="form-select">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="UNKNO">Unknown</option>
                                        <option value="NA">Not Available</option>
                                    </select>
                                </div>                                <div class="form-group">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Password</label>
                                    <input type="password" name="password" class="form-input" required>
                                    <div class="help-text">For new students only. Leave blank when editing.</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Mobile</label>
                                    <input type="tel" name="mobile" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Class</label>
                                    <select name="class_id" id="addStudentClass" class="form-select" required>
                                        <option value="">Select Class</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Section</label>
                                    <select name="section_id" id="addStudentSection" class="form-select" required>
                                        <option value="">Select Section</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Academic Year</label>
                                    <select name="academic_year_id" id="addStudentAcademicYear" class="form-select">
                                        <option value="">Select Academic Year</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Roll Number</label>
                                    <input type="text" name="roll_number" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Father's Name</label>
                                    <input type="text" name="father_name" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mother's Name</label>
                                    <input type="text" name="mother_name" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Nationality</label>
                                    <input type="text" name="nationality" class="form-input" value="Indian">
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">Address</label>
                                    <textarea name="address" class="form-textarea" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Pincode</label>
                                    <input type="text" name="pincode" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Alternative Mobile</label>
                                    <input type="tel" name="alt_mobile" class="form-input">
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">Photo</label>
                                    <input type="file" name="photo" class="form-input" accept="image/*">
                                    <div class="help-text">Optional. Max file size: 2MB</div>
                                </div>
                            </div><div class="actions">
                                <button type="submit" class="btn btn-primary" id="addStudentSubmitBtn">
                                    <i class="fas fa-save"></i> <span id="submitButtonText">Add Student</span>
                                </button>
                                <button type="reset" class="btn btn-secondary" onclick="clearStudentForm()">
                                    <i class="fas fa-undo"></i> <span id="resetButtonText">Reset Form</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Bulk Operations Tab -->
            <div class="tab-content admin-only" id="bulk-tab">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bulk Operations</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <h4>Bulk Promote Students</h4>
                                <p class="help-text">Promote all students from one class to the next</p>
                                <form id="bulkPromoteForm">
                                    <div class="form-group">
                                        <label class="form-label required">From Class</label>
                                        <select name="from_class_id" id="bulkFromClass" class="form-select" required>
                                            <option value="">Select Class</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label required">To Class</label>
                                        <select name="to_class_id" id="bulkToClass" class="form-select" required>
                                            <option value="">Select Class</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Academic Year</label>
                                        <input type="text" name="academic_year" class="form-input" value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-level-up-alt"></i> Promote Students
                                    </button>
                                </form>
                            </div>
                            <div class="form-group">
                                <h4>Bulk Import Students</h4>
                                <p class="help-text">Import students from CSV file</p>
                                <form id="bulkImportForm" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label class="form-label required">CSV File</label>
                                        <input type="file" name="csv_file" class="form-input" accept=".csv" required>
                                        <div class="help-text">CSV format: first_name, last_name, admission_number, class_id, section_id, etc.</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Import Students
                                    </button>
                                    <a href="#" class="btn btn-outline" onclick="downloadSampleCSV()">
                                        <i class="fas fa-download"></i> Download Sample CSV
                                    </a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reports Tab -->
            <div class="tab-content" id="reports-tab">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Student Reports</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <h4>Class-wise Report</h4>
                                <form id="classReportForm">
                                    <div class="form-group">
                                        <label class="form-label">Select Class</label>
                                        <select name="class_id" id="reportClass" class="form-select">
                                            <option value="">All Classes</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Select Section</label>
                                        <select name="section_id" id="reportSection" class="form-select">
                                            <option value="">All Sections</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-file-pdf"></i> Generate Report
                                    </button>
                                </form>
                            </div>
                            <div class="form-group">
                                <h4>Student Statistics</h4>
                                <div class="stats-grid">
                                    <div class="stat-card">
                                        <span class="stat-number" id="reportTotalStudents">0</span>
                                        <span class="stat-label">Total Students</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-number" id="reportActiveStudents">0</span>
                                        <span class="stat-label">Active Students</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-number" id="reportMaleStudents">0</span>
                                        <span class="stat-label">Male Students</span>
                                    </div>
                                    <div class="stat-card">
                                        <span class="stat-number" id="reportFemaleStudents">0</span>
                                        <span class="stat-label">Female Students</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Student Details Modal -->
    <div class="modal-overlay" id="studentDetailsModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Student Details</h3>
                <button class="modal-close" onclick="closeModal('studentDetailsModal')">&times;</button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <!-- Student details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal-overlay" id="editStudentModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Edit Student</h3>
                <button class="modal-close" onclick="closeModal('editStudentModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editStudentForm" enctype="multipart/form-data">
                    <input type="hidden" name="student_id" id="editStudentId">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label required">First Name</label>
                            <input type="text" name="first_name" id="editFirstName" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Last Name</label>
                            <input type="text" name="last_name" id="editLastName" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Date of Birth</label>
                            <input type="date" name="date_of_birth" id="editDateOfBirth" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Gender</label>
                            <select name="gender" id="editGender" class="form-select" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="editEmail" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" id="editPhone" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Class</label>
                            <select name="class_id" id="editStudentClassSelect" class="form-select" required>
                                <option value="">Select Class</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label required">Section</label>
                            <select name="section_id" id="editStudentSectionSelect" class="form-select" required>
                                <option value="">Select Section</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Roll Number</label>
                            <input type="number" name="roll_number" id="editRollNumber" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" id="editStatus" class="form-select">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="suspended">Suspended</option>
                                <option value="graduated">Graduated</option>
                                <option value="transferred_out">Transferred Out</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Address</label>
                            <textarea name="address" id="editAddress" class="form-textarea" rows="3"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Photo</label>
                            <input type="file" name="photo" class="form-input" accept="image/*">
                            <div class="help-text">Leave empty to keep current photo</div>
                        </div>
                    </div>
                    <div class="actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Student
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('editStudentModal')">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>        // Initialize data
        const classes = <?php echo json_encode($classes); ?>;
        const sections = <?php echo json_encode($sections); ?>;
        const academic_years = <?php echo json_encode($academic_years); ?>;
        const parents = <?php echo json_encode($parents); ?>;
        
        let currentPage = 1;
        let totalPages = 1;
        let currentFilters = {};

        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
        });

        function initializePage() {
            setupTabs();
            populateDropdowns();
            setupEventListeners();
            refreshStudentList();
            loadReportStats();
        }

        function setupTabs() {
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const targetTab = this.getAttribute('data-tab');
                    
                    // Remove active class from all tabs and contents
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    tabContents.forEach(content => content.classList.remove('active'));
                    
                    // Add active class to clicked tab and corresponding content
                    this.classList.add('active');
                    document.getElementById(targetTab + '-tab').classList.add('active');
                });
            });
        }

        function populateDropdowns() {            // Populate class dropdowns
            const classSelects = document.querySelectorAll('#filterClass, #addStudentClass, #bulkFromClass, #bulkToClass, #reportClass, #editStudentClassSelect');
            classSelects.forEach(select => {
                select.innerHTML = '<option value="">Select Class</option>';
                classes.forEach(cls => {
                    select.innerHTML += `<option value="${cls.id}">${cls.name}</option>`;
                });
            });

            // Populate academic years dropdown
            const academicYearSelect = document.querySelector('#addStudentAcademicYear');
            if (academicYearSelect) {                academicYearSelect.innerHTML = '<option value="">Select Academic Year</option>';
                academic_years.forEach(year => {
                    academicYearSelect.innerHTML += `<option value="${year.id}">${year.name}</option>`;
                });
                
                // Auto-select current academic year if available
                if (academic_years.length > 0) {
                    academicYearSelect.value = academic_years[0].id;
                }
            }

            // Populate parent dropdown (if it exists)
            const parentSelect = document.querySelector('select[name="parent_id"]');
            if (parentSelect) {
                parents.forEach(parent => {
                    parentSelect.innerHTML += `<option value="${parent.id}">${parent.full_name} (${parent.email})</option>`;
                });
            }

            // Setup section dropdowns based on class selection
            setupSectionDependency('#addStudentClass', '#addStudentSection');
            setupSectionDependency('#editStudentClassSelect', '#editStudentSectionSelect');
            setupSectionDependency('#filterClass', '#filterSection');
            setupSectionDependency('#reportClass', '#reportSection');
        }

        function setupSectionDependency(classSelectId, sectionSelectId) {
            const classSelect = document.querySelector(classSelectId);
            const sectionSelect = document.querySelector(sectionSelectId);
            
            if (classSelect && sectionSelect) {
                classSelect.addEventListener('change', function() {
                    const classId = this.value;
                    sectionSelect.innerHTML = '<option value="">Select Section</option>';
                    
                    if (classId) {
                        const classSections = sections.filter(section => section.class_id == classId);
                        classSections.forEach(section => {
                            sectionSelect.innerHTML += `<option value="${section.id}">${section.name}</option>`;
                        });
                    }
                });
            }
        }        function setupEventListeners() {
            // Search and filter listeners
            document.getElementById('searchTerm').addEventListener('input', debounce(handleFilterChange, 500));
            document.getElementById('filterClass').addEventListener('change', handleFilterChange);
            document.getElementById('filterSection').addEventListener('change', handleFilterChange);
            document.getElementById('filterStatus').addEventListener('change', handleFilterChange);

            // Form submissions
            document.getElementById('addStudentForm').addEventListener('submit', handleAddStudent);
            document.getElementById('editStudentForm').addEventListener('submit', handleEditStudent);
            document.getElementById('bulkPromoteForm').addEventListener('submit', handleBulkPromote);
            document.getElementById('bulkImportForm').addEventListener('submit', handleBulkImport);
            document.getElementById('classReportForm').addEventListener('submit', handleGenerateReport);

            // Auto-fill admission date with today's date for new students
            const admissionDateField = document.querySelector('input[name="admission_date"]');
            if (admissionDateField && !admissionDateField.value) {
                const today = new Date().toISOString().split('T')[0];
                admissionDateField.value = today;
            }            // Auto-fill password with DOB for new students
            const dobField = document.querySelector('input[name="dob"]');
            const passwordField = document.querySelector('input[name="password"]');
            
            if (dobField && passwordField) {
                dobField.addEventListener('change', function() {
                    // Only auto-fill password if we're adding a new student (not editing)
                    const isEditing = document.getElementById('editStudentId').value.trim() !== '';
                    if (!isEditing && this.value) {
                        // Format DOB as DD/MM/YYYY for password
                        const date = new Date(this.value);
                        const day = String(date.getDate()).padStart(2, '0');
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const year = date.getFullYear();
                        passwordField.value = `${day}/${month}/${year}`;
                    }
                });
            }
        }

        function handleFilterChange() {
            currentPage = 1;
            refreshStudentList();
        }        
        function refreshStudentList() {
            const filters = {
                search: document.getElementById('searchTerm').value,
                class_id: document.getElementById('filterClass').value,
                section_id: document.getElementById('filterSection').value,
                status: document.getElementById('filterStatus').value,
                page: currentPage,
                action: 'get_students' // Make sure action is included
            };

            currentFilters = filters;
            
            showLoading();
            
            fetch('student_management_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(filters)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                hideLoading();
                console.log('API Response:', data); // Debug log
                if (data.success) {
                    displayStudents(data.students);
                    updatePagination(data.pagination);
                    updateStats(data.stats);
                } else {
                    showNotification(data.message || 'Failed to load students', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Error loading students: ' + error.message, 'error');
            });
        }

        function displayStudents(students) {
            const tbody = document.getElementById('studentsTableBody');
            const table = document.getElementById('studentsTable');
            
            if (!tbody || !table) {
                console.error('Table elements not found');
                return;
            }
            
            if (!students || students.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No students found</td></tr>';
            } else {
                tbody.innerHTML = students.map(student => `
                    <tr>
                        <td>
                            <div class="student-info-cell">
                                <span class="student-name">${student.full_name || 'N/A'}</span>
                                <span class="student-admission">Adm: ${student.admission_number || 'N/A'}</span>
                            </div>
                        </td>
                        <td>${(student.class_name || 'N/A') + ' - ' + (student.section_name || 'N/A')}</td>
                        <td>${student.roll_number || 'N/A'}</td>
                        <td><span class="status-badge status-${student.status || 'active'}">${student.status || 'active'}</span></td>
                        <td>${student.email || 'N/A'}<br><small>${student.phone || 'N/A'}</small></td>
                        <td class="admin-only">
                            <div class="actions">
                                <button class="btn btn-outline btn-sm" onclick="viewStudent(${student.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="editStudent(${student.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            }
            
            table.style.display = 'table';
        }

        function updatePagination(pagination) {
            totalPages = pagination.totalPages;
            const paginationContainer = document.getElementById('studentPagination');
            
            if (totalPages <= 1) {
                paginationContainer.style.display = 'none';
                return;
            }

            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <button class="pagination-btn" onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
                    <i class="fas fa-chevron-left"></i>
                </button>
            `;

            // Page numbers
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage || i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    paginationHTML += `
                        <button class="pagination-btn ${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                            ${i}
                        </button>
                    `;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    paginationHTML += '<span class="pagination-btn">...</span>';
                }
            }

            // Next button
            paginationHTML += `
                <button class="pagination-btn" onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
                    <i class="fas fa-chevron-right"></i>
                </button>
            `;

            paginationContainer.innerHTML = paginationHTML;
            paginationContainer.style.display = 'flex';
        }

        function changePage(page) {
            if (page >= 1 && page <= totalPages && page !== currentPage) {
                currentPage = page;
                refreshStudentList();
            }
        }

        function updateStats(stats) {
            document.getElementById('totalStudentsCount').textContent = stats.total || 0;
            document.getElementById('activeStudentsCount').textContent = stats.active || 0;
            document.getElementById('classesCount').textContent = classes.length;
            document.getElementById('sectionsCount').textContent = sections.length;
        }        function handleAddStudent(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            
            // Check if we're editing (student_id is present) or adding
            const studentId = formData.get('student_id');
            const isEditing = studentId && studentId.trim() !== '';
            
            const action = isEditing ? 'update_student' : 'add_student';
            formData.append('action', action);
            
            if (isEditing) {
                formData.append('student_user_id', studentId);
            }

            fetch('student_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const message = isEditing ? 'Student updated successfully!' : 'Student added successfully!';
                    showNotification(message, 'success');
                    
                    if (!isEditing) {
                        event.target.reset();
                    } else {
                        clearStudentForm(); // Clear form after successful edit
                    }
                    
                    refreshStudentList();
                    
                    // Switch back to list tab after successful operation
                    showTab('list');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                const errorMessage = isEditing ? 'Error updating student: ' : 'Error adding student: ';
                showNotification(errorMessage + error.message, 'error');
            });
        }function handleEditStudent(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'update_student');

            fetch('student_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Student updated successfully!', 'success');
                    closeModal('editStudentModal');
                    refreshStudentList();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error updating student: ' + error.message, 'error');
            });
        }        function handleBulkPromote(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            data.action = 'bulk_promote_students';

            if (confirm('Are you sure you want to promote all students from the selected class?')) {
                fetch('student_management_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(`Successfully promoted ${data.promoted_count} students!`, 'success');
                        refreshStudentList();
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('Error promoting students: ' + error.message, 'error');
                });
            }
        }        function handleBulkImport(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            formData.append('action', 'bulk_import_students');

            fetch('student_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`Successfully imported ${data.imported_count} students!`, 'success');
                    refreshStudentList();
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error importing students: ' + error.message, 'error');
            });
        }

        function handleGenerateReport(event) {
            event.preventDefault();
            const formData = new FormData(event.target);
            const params = new URLSearchParams(formData);
            
            window.open(`student_report.php?${params.toString()}`, '_blank');
        }        function viewStudent(studentId) {
            fetch('student_management_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_student_details',
                    student_id: studentId
                })
            })
            .then(response => response.json())            .then(data => {
                if (data.success) {
                    displayStudentDetails(data.data);
                    openModal('studentDetailsModal');
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading student details: ' + error.message, 'error');
            });
        }        function editStudent(studentId) {
            fetch('student_management_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_student_details',
                    student_id: studentId
                })
            })
            .then(response => response.json())            .then(data => {
                if (data.success) {
                    populateAddFormForEdit(data.data);
                    // Switch to Add Student tab and show it's in edit mode
                    showTab('add');
                    showNotification('Editing student: ' + data.data.full_name, 'info');
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error loading student details: ' + error.message, 'error');
            });
        }

        function displayStudentDetails(student) {
            const content = document.getElementById('studentDetailsContent');
            content.innerHTML = `
                <div class="form-grid">
                    <div class="form-group">
                        <strong>Name:</strong> ${student.full_name}
                    </div>
                    <div class="form-group">
                        <strong>Admission Number:</strong> ${student.admission_number}
                    </div>
                    <div class="form-group">
                        <strong>Class:</strong> ${student.class_name || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Section:</strong> ${student.section_name || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Roll Number:</strong> ${student.roll_number || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Date of Birth:</strong> ${student.date_of_birth || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Gender:</strong> ${student.gender || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Blood Group:</strong> ${student.blood_group || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Email:</strong> ${student.email || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Phone:</strong> ${student.phone || 'N/A'}
                    </div>
                    <div class="form-group">
                        <strong>Status:</strong> <span class="status-badge status-${student.status}">${student.status}</span>
                    </div>
                    <div class="form-group">
                        <strong>Parent:</strong> ${student.parent_name || 'N/A'}
                    </div>
                    <div class="form-group full-width">
                        <strong>Address:</strong> ${student.address || 'N/A'}
                    </div>
                    ${student.photo ? `<div class="form-group full-width"><strong>Photo:</strong><br><img src="${student.photo}" style="max-width: 200px; border-radius: 8px;"></div>` : ''}
                </div>
            `;
        }

        function populateEditForm(student) {
            document.getElementById('editStudentId').value = student.id;
            document.getElementById('editFirstName').value = student.first_name || '';
            document.getElementById('editLastName').value = student.last_name || '';
            document.getElementById('editDateOfBirth').value = student.date_of_birth || '';
            document.getElementById('editGender').value = student.gender || '';
            document.getElementById('editEmail').value = student.email || '';
            document.getElementById('editPhone').value = student.phone || '';
            document.getElementById('editRollNumber').value = student.roll_number || '';
            document.getElementById('editStatus').value = student.status || 'active';
            document.getElementById('editAddress').value = student.address || '';
            
            // Set class and trigger section population
            const classSelect = document.getElementById('editStudentClassSelect');
            classSelect.value = student.class_id || '';
            classSelect.dispatchEvent(new Event('change'));
            
            // Set section after a brief delay to allow section population
            setTimeout(() => {
                document.getElementById('editStudentSectionSelect').value = student.section_id || '';
            }, 100);
        }        function loadReportStats() {
            fetch('student_management_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'get_students'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.stats) {
                    document.getElementById('reportTotalStudents').textContent = data.stats.total || 0;
                    document.getElementById('reportActiveStudents').textContent = data.stats.active || 0;
                    document.getElementById('reportMaleStudents').textContent = data.stats.male || 0;
                    document.getElementById('reportFemaleStudents').textContent = data.stats.female || 0;
                }
            })
            .catch(error => {
                console.error('Error loading report stats:', error);
            });
        }

        function downloadSampleCSV() {
            const csvContent = `first_name,last_name,admission_number,date_of_birth,gender,blood_group,email,phone,class_id,section_id,roll_number,address
John,Doe,ADM001,2010-01-15,male,A+,john.doe@email.com,1234567890,1,1,1,123 Main Street
Jane,Smith,ADM002,2010-02-20,female,B+,jane.smith@email.com,0987654321,1,1,2,456 Oak Avenue`;
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'student_import_sample.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function showNotification(message, type) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.className = `notification ${type}`;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.display = 'none';
            }, 5000);
        }

        function showLoading() {
            document.getElementById('loadingIndicator').style.display = 'block';
            document.getElementById('studentsTable').style.display = 'none';
            document.getElementById('studentPagination').style.display = 'none';
        }

        function hideLoading() {
            document.getElementById('loadingIndicator').style.display = 'none';
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        function showTab(tabName) {
            // Remove active class from all tabs and contents
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to the specified tab
            const targetButton = document.querySelector(`[data-tab="${tabName}"]`);
            const targetContent = document.getElementById(`${tabName}-tab`);
            
            if (targetButton && targetContent) {
                targetButton.classList.add('active');
                targetContent.classList.add('active');
            }
        }        function populateAddFormForEdit(student) {
            // Set the hidden student ID
            document.getElementById('editStudentId').value = student.user_id || student.id;
            
            // Update UI to show edit mode
            document.getElementById('addStudentTitle').textContent = 'Edit Student';
            document.getElementById('editModeNotice').style.display = 'block';
            document.getElementById('submitButtonText').textContent = 'Update Student';
            document.getElementById('resetButtonText').textContent = 'Clear Form';
            
            // Populate form fields with actual API field names
            const form = document.getElementById('addStudentForm');
            
            // Basic fields
            form.querySelector('input[name="full_name"]').value = student.full_name || '';
            form.querySelector('input[name="admission_date"]').value = student.admission_date || '';
            form.querySelector('input[name="dob"]').value = student.dob || '';
            form.querySelector('select[name="gender_code"]').value = student.gender_code || '';
            form.querySelector('select[name="blood_group_code"]').value = student.blood_group_code || '';
            form.querySelector('input[name="email"]').value = student.user_email || student.contact_email || '';
            form.querySelector('input[name="mobile"]').value = student.mobile || '';
            form.querySelector('input[name="roll_number"]').value = student.roll_number || '';
            form.querySelector('input[name="father_name"]').value = student.father_name || '';
            form.querySelector('input[name="mother_name"]').value = student.mother_name || '';
            form.querySelector('input[name="nationality"]').value = student.nationality || '';
            form.querySelector('textarea[name="address"]').value = student.address || '';
            form.querySelector('input[name="pincode"]').value = student.pincode || '';
            form.querySelector('input[name="alt_mobile"]').value = student.alt_mobile || '';            // Don't populate password field when editing
            form.querySelector('input[name="password"]').value = '';
            form.querySelector('input[name="password"]').removeAttribute('required');
            
            // Set class and trigger section population
            const classSelect = form.querySelector('select[name="class_id"]');
            classSelect.value = student.class_id || '';
            classSelect.dispatchEvent(new Event('change'));
            
            // Set section and academic year after a brief delay to allow section population
            setTimeout(() => {
                form.querySelector('select[name="section_id"]').value = student.section_id || '';
                form.querySelector('select[name="academic_year_id"]').value = student.academic_year_id || '';
            }, 100);
        }        function clearStudentForm() {
            // Reset form
            document.getElementById('addStudentForm').reset();
            
            // Clear hidden student ID
            document.getElementById('editStudentId').value = '';
            
            // Reset UI to add mode
            document.getElementById('addStudentTitle').textContent = 'Add New Student';
            document.getElementById('editModeNotice').style.display = 'none';
            document.getElementById('submitButtonText').textContent = 'Add Student';
            document.getElementById('resetButtonText').textContent = 'Reset Form';            // Restore password field as required for new students
            const passwordField = document.querySelector('input[name="password"]');
            passwordField.setAttribute('required', 'required');
            
            // Set admission date to today
            const admissionDateField = document.querySelector('input[name="admission_date"]');
            if (admissionDateField) {
                const today = new Date().toISOString().split('T')[0];
                admissionDateField.value = today;
            }
            
            showNotification('Form cleared. You can now add a new student.', 'info');
        }

        // Close modals when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target.classList.contains('modal-overlay')) {
                event.target.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
    </script>
</body>
</html>
