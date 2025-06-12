<?php
/**
 * Unified Teacher Management System
 * Combines all teacher-related operations in a single interface
 * Role-based access control: Admin/Headmaster can do everything, HM can only assign/reassign
 */

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

// Include database connection
require_once 'con.php';

// Get current user role for permission checks
$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch required data for dropdowns
$classes_raw = executeQuery("SELECT id, name FROM classes ORDER BY name");
$sections_raw = executeQuery("SELECT id, name, class_id FROM sections ORDER BY class_id, name");
$subjects_raw = executeQuery("SELECT id, name, code FROM subjects ORDER BY name");
$teachers_raw = executeQuery("
    SELECT u.id, u.full_name, u.email, t.employee_number, u.status
    FROM users u 
    JOIN teachers t ON u.id = t.user_id 
    WHERE u.role IN ('teacher', 'headmaster')
    ORDER BY u.full_name
");

// Ensure data is array for json_encode to prevent JS errors
$classes = is_array($classes_raw) ? $classes_raw : [];
$sections = is_array($sections_raw) ? $sections_raw : [];
$subjects = is_array($subjects_raw) ? $subjects_raw : [];
$teachers = is_array($teachers_raw) ? $teachers_raw : [];

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Management - Unified System</title>
    
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

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 0.875rem;
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
        }

        .notification.success {
            background: rgba(38, 231, 166, 0.1);
            color: #059669;
            border: 1px solid rgba(38, 231, 166, 0.3);
        }

        .notification.error {
            background: rgba(229, 62, 62, 0.1);
            color: #dc2626;
            border: 1px solid rgba(229, 62, 62, 0.3);
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
            max-width: 600px;
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

        /* Conflict Dialog Styles */
        .conflict-dialog {
            max-width: 500px;
        }

        .conflict-item {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 12px;
            margin-bottom: 12px;
        }

        .workload-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 4px;
            padding: 12px;
            margin: 16px 0;
        }

        .workload-info h5 {
            margin: 0 0 8px 0;
            color: var(--text-primary);
        }

        .workload-info p {
            margin: 4px 0;
            color: var(--text-secondary);
        }

        .conflict-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        /* Enhanced Table Styles */
        .section-badge {
            background: var(--accent-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .teacher-info {
            display: flex;
            flex-direction: column;
        }

        .teacher-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .teacher-email {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        /* Workload Display */
        .workload-details {
            max-width: 400px;
        }

        .workload-stats {
            display: grid;
            gap: 12px;
            margin: 16px 0;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .stat-label {
            font-weight: 600;
            color: var(--text-primary);
        }

        .stat-value {
            color: var(--primary-color);
            font-weight: 600;
        }

        /* Reassignment Form */
        .reassign-form {
            max-width: 400px;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }

        /* Enhanced Status Badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
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

        .assignment-scope-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            background: var(--bg-light);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
        }

        .assignment-scope-badge.all-classes {
            background: rgba(88, 86, 214, 0.1);
            color: var(--secondary-color);
            border-color: rgba(88, 86, 214, 0.3);
        }

        .assignment-scope-badge.specific-class {
            background: rgba(38, 231, 166, 0.1);
            color: var(--accent-color);
            border-color: rgba(38, 231, 166, 0.3);
        }

        .teacher-info-cell {
            display: flex;
            flex-direction: column;
        }

        .teacher-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .teacher-email {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        .subject-info-cell {
            display: flex;
            flex-direction: column;
        }

        .subject-name {
            font-weight: 500;
            color: var(--text-primary);
        }

        .subject-code {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 2px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
            
            .section-actions {
                justify-content: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }
        }

        /* Table hover effects */
        .data-table tbody tr:hover {
            background: rgba(253, 93, 93, 0.02);
        }

        .data-table tbody tr:hover .btn {
            opacity: 1;
            transform: translateY(-1px);
        }

        /* Button hover animations */
        .btn-sm {
            transition: all 0.2s ease;
            opacity: 0.8;
        }

        .btn-sm:hover {
            opacity: 1;
            transform: translateY(-1px);
        }

        .section-divider {
            position: relative;
        }

        .section-divider::after {
            content: '';
            position: absolute;
            top: -1px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 2px;
            background: var(--primary-color);
        }

        /* Responsive Modal */
        @media (max-width: 768px) {
            .modal-container {
                width: 95%;
                margin: 20px;
            }

            .modal-body {
                padding: 16px;
            }

            .conflict-actions {
                flex-direction: column;
            }

            .form-actions {
                flex-direction: column;
            }
        }

        /* ...existing styles... */
    </style>
</head>
<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <i class="fas fa-bars hamburger-icon"></i>
    </button>

    <div class="unified-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="header-title">
                <i class="fas fa-users-cog"></i>
                Teacher Management System
            </h1>
            <p class="header-subtitle">
                Unified interface for all teacher-related operations
                <?php if ($user_role === 'headmaster'): ?>
                    (Assignment/Reassignment Only)
                <?php endif; ?>
            </p>
        </div>

        <!-- Notification Area -->
        <div id="notification" class="notification"></div>

        <!-- Main Content Tabs -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-button active admin-only" data-tab="add-teacher">
                    <i class="fas fa-user-plus"></i>
                    Add Teacher
                </button>
                <button class="tab-button <?php echo ($user_role === 'headmaster') ? 'active' : ''; ?>" data-tab="manage-teachers">
                    <i class="fas fa-users"></i>
                    Manage Teachers
                </button>
                <button class="tab-button" data-tab="class-assignments">
                    <i class="fas fa-chalkboard-teacher"></i>
                    Class Teacher Assignment
                </button>
                <button class="tab-button" data-tab="subject-assignments">
                    <i class="fas fa-book-open"></i>
                    Subject Assignments
                </button>
                <button class="tab-button" data-tab="timetable-management">
                    <i class="fas fa-calendar-alt"></i>
                    Timetable Management
                </button>
                <button class="tab-button" data-tab="bulk-operations">
                    <i class="fas fa-tasks"></i>
                    Bulk Operations
                </button>
            </div>

            <!-- Tab Content: Add Teacher -->
            <div id="add-teacher" class="tab-content <?php echo ($user_role === 'admin') ? 'active' : ''; ?> admin-only">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Add New Teacher</h3>
                    </div>
                    <div class="card-body">
                        <form id="addTeacherForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="fullName" class="form-label required">Full Name</label>
                                    <input type="text" id="fullName" name="fullName" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="email" class="form-label required">Email</label>
                                    <input type="email" id="email" name="email" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="employeeNumber" class="form-label">Employee Number</label>
                                    <input type="text" id="employeeNumber" name="employeeNumber" class="form-input" readonly>
                                    <div class="help-text">Will be auto-generated (e.g., VES2025T001)</div>
                                </div>
                                <div class="form-group">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="qualification" class="form-label">Qualification</label>
                                    <input type="text" id="qualification" name="qualification" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label for="joinedDate" class="form-label required">Joined Date</label>
                                    <input type="date" id="joinedDate" name="joinedDate" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label for="role" class="form-label required">Role</label>
                                    <select id="role" name="role" class="form-select" required>
                                        <option value="">Select Role</option>
                                        <option value="teacher">Teacher</option>
                                        <option value="headmaster">Head Master</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="status" class="form-label required">Status</label>
                                    <select id="status" name="status" class="form-select" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group full-width">
                                    <label for="address" class="form-label">Address</label>
                                    <textarea id="address" name="address" class="form-textarea" rows="3"></textarea>
                                </div>
                            </div>
                            <div class="actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Add Teacher
                                </button>
                                <button type="reset" class="btn btn-outline">
                                    <i class="fas fa-undo"></i>
                                    Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Manage Teachers -->
            <div id="manage-teachers" class="tab-content <?php echo ($user_role === 'headmaster') ? 'active' : ''; ?>">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Teacher Directory</h3>
                    </div>
                    <div class="card-body">
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="teacherSearch" class="search-input" placeholder="Search by name, employee ID, or email...">
                        </div>
                        
                        <div id="teachersTable">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Employee ID</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="teachersTableBody">
                                    <!-- Dynamic content will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div id="teachersLoading" class="loading">
                            <div class="spinner"></div>
                            <p>Loading teachers...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Class Teacher Assignment -->
            <div id="class-assignments" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Class Teacher Assignments</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="assignClass" class="form-label required">Class</label>
                                <select id="assignClass" name="assignClass" class="form-select" required>
                                    <option value="">Select Class</option>
                                    <?php foreach ($classes as $class): ?>
                                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignSection" class="form-label required">Section</label>
                                <select id="assignSection" name="assignSection" class="form-select" required disabled>
                                    <option value="">Select Section</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignTeacher" class="form-label required">Teacher</label>
                                <select id="assignTeacher" name="assignTeacher" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['full_name']); ?> (<?php echo htmlspecialchars($teacher['employee_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="actions">
                            <button type="button" id="assignClassTeacher" class="btn btn-primary">
                                <i class="fas fa-user-check"></i>
                                Assign Class Teacher
                            </button>
                        </div>

                        <!-- Current Assignments Table -->
                        <h4 style="margin-top: 32px; margin-bottom: 16px;">Current Class Teacher Assignments</h4>
                        <div id="classAssignmentsTable">
                            <!-- Dynamic content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Subject Assignments -->
            <div id="subject-assignments" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Subject Assignments</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="subjectTeacher" class="form-label required">Teacher</label>
                                <select id="subjectTeacher" name="subjectTeacher" class="form-select" required>
                                    <option value="">Select Teacher</option>
                                    <?php foreach ($teachers as $teacher): ?>
                                        <option value="<?php echo $teacher['id']; ?>">
                                            <?php echo htmlspecialchars($teacher['full_name']); ?> (<?php echo htmlspecialchars($teacher['employee_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Assign Subjects</label>
                            <div id="subjectsCheckboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 12px; margin-top: 12px;">
                                <?php foreach ($subjects as $subject): ?>
                                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid var(--border-color); border-radius: 6px; cursor: pointer;">
                                        <input type="checkbox" name="subjects[]" value="<?php echo $subject['id']; ?>" style="margin: 0;">
                                        <span><?php echo htmlspecialchars($subject['name']); ?> (<?php echo htmlspecialchars($subject['code']); ?>)</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="button" id="updateSubjectAssignments" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Update Assignments
                            </button>
                            <button type="button" id="clearSubjectAssignments" class="btn btn-outline">
                                <i class="fas fa-times"></i>
                                Clear All
                            </button>
                        </div>

                        <!-- Current Subject Assignments -->
                        <h4 style="margin-top: 32px; margin-bottom: 16px;">Current Subject Assignments</h4>
                        <div id="subjectAssignmentsDisplay">
                            <p class="help-text">Select a teacher to view their subject assignments</p>
                        </div>

                        <!-- All Subject Assignments Display Section -->
<div class="section-divider" style="margin: 40px 0; border-top: 2px solid var(--border-color);"></div>

<div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h4 class="section-title" style="font-size: 1.25rem; font-weight: 600; margin: 0; color: var(--text-primary);">
        <i class="fas fa-list-ul"></i>
        All Teachers Subject Assignments
    </h4>
    <div class="section-actions" style="display: flex; gap: 12px;">
        <button class="btn btn-outline btn-sm" id="refreshAllAssignments">
            <i class="fas fa-sync"></i>
            Refresh
        </button>
        <button class="btn btn-outline btn-sm" id="exportAssignments">
            <i class="fas fa-download"></i>
            Export
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div id="assignmentStatistics" class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px;">
    <!-- Statistics will be populated here -->
</div>

<!-- Filters and Search -->
<div class="filters-container" style="background: var(--bg-white); padding: 20px; border-radius: var(--border-radius); margin-bottom: 20px; box-shadow: var(--shadow-sm);">
    <div class="filter-grid" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label for="allAssignmentsSearch" class="form-label">Search Teachers</label>
            <div class="search-container" style="position: relative;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="allAssignmentsSearch" class="search-input" placeholder="Search by name, employee ID, or email...">
            </div>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label for="filterTeacher" class="form-label">Filter by Teacher</label>
            <select id="filterTeacher" class="form-select">
                <option value="">All Teachers</option>
                <!-- Will be populated dynamically -->
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <label for="filterSubject" class="form-label">Filter by Subject</label>
            <select id="filterSubject" class="form-select">
                <option value="">All Subjects</option>
                <!-- Will be populated dynamically -->
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0;">
            <button type="button" id="clearAllFilters" class="btn btn-outline">
                <i class="fas fa-times"></i>
                Clear
            </button>
        </div>
    </div>
</div>

<!-- Main Assignments Table -->
<div id="allAssignmentsContainer" class="card">
    <div class="card-header">
        <h5 class="card-title" style="margin: 0;">Subject Assignments Overview</h5>
    </div>
    <div class="card-body" style="padding: 0;">
        
        <!-- Loading State -->
        <div id="allAssignmentsLoading" class="loading" style="display: none; padding: 40px;">
            <div class="spinner"></div>
            <p>Loading all subject assignments...</p>
        </div>
        
        <!-- Assignments Table -->
        <div id="allAssignmentsTable" style="overflow-x: auto;">
            <table class="data-table" style="margin: 0;">                <thead>
                    <tr>
                        <th style="min-width: 120px;">Employee ID</th>
                        <th style="min-width: 180px;">Teacher Name</th>
                        <th style="min-width: 150px;">Subject</th>
                        <th style="min-width: 100px;">Subject Code</th>
                        <th style="min-width: 120px;">Assignment Scope</th>
                        <th style="min-width: 80px;">Status</th>
                        <th style="min-width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="allAssignmentsTableBody">
                    <!-- Dynamic content will be loaded here -->
                </tbody>
            </table>
        </div>
        
        <!-- Empty State -->
        <div id="allAssignmentsEmpty" class="empty-state" style="display: none; text-align: center; padding: 60px 20px;">
            <i class="fas fa-users" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
            <h5 style="color: var(--text-primary); margin-bottom: 8px;">No Subject Assignments Found</h5>
            <p class="help-text">No teachers have been assigned to subjects yet, or your search didn't match any assignments.</p>
        </div>
        
    </div>
</div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Bulk Operations -->
            <div id="bulk-operations" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bulk Operations</h3>
                    </div>
                    <div class="card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
                            <!-- Bulk Class Assignment -->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Bulk Class Assignment</h4>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="bulkAssignClass" class="form-label">Class</label>
                                        <select id="bulkAssignClass" class="form-select">
                                            <option value="">Select Class</option>
                                            <?php foreach ($classes as $class): ?>
                                                <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="bulkAssignSubject" class="form-label">Subject</label>
                                        <select id="bulkAssignSubject" class="form-select">
                                            <option value="">Select Subject</option>
                                            <?php foreach ($subjects as $subject): ?>
                                                <option value="<?php echo $subject['id']; ?>"><?php echo htmlspecialchars($subject['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Teachers</label>
                                        <div id="bulkTeachersCheckboxes">
                                            <?php foreach ($teachers as $teacher): ?>
                                                <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                                    <input type="checkbox" name="bulkTeachers[]" value="<?php echo $teacher['id']; ?>">
                                                    <span><?php echo htmlspecialchars($teacher['full_name']); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <button type="button" id="executeBulkAssignment" class="btn btn-primary">
                                        <i class="fas fa-bolt"></i>
                                        Execute Bulk Assignment
                                    </button>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">System Statistics</h4>
                                </div>
                                <div class="card-body">
                                    <div id="statisticsContent">
                                        <!-- Will be loaded dynamically -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Timetable Management -->
            <div id="timetable-management" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Timetable & Schedule Management</h3>
                    </div>
                    <div class="card-body">
                        <!-- Timetable Conflicts Section -->
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                Schedule Conflicts
                            </h4>
                            <button class="btn btn-outline btn-sm" id="adminRefreshConflictsBtn">
                                <i class="fas fa-sync"></i>
                                Refresh Conflicts
                            </button>
                        </div>
                        <div id="adminTimetableConflicts" style="margin-bottom: 32px;">
                            <p class="help-text">Loading conflicts...</p>
                        </div>
                        <div id="adminTimetableConflictsLoading" class="loading" style="display:none;"><div class="spinner"></div><p>Loading Conflicts...</p></div>

                        <!-- Teacher Timetable Overview -->
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; margin-top: 32px;">
                            <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                                <i class="fas fa-users"></i>
                                Teacher Schedule Overview
                            </h4>
                        </div>
                        <div id="adminTeacherTimetableOverview" style="margin-bottom: 32px;">
                             <p class="help-text">Loading teacher schedules...</p>
                        </div>
                        <div id="adminTeacherTimetableOverviewLoading" class="loading" style="display:none;"><div class="spinner"></div><p>Loading Teacher Schedules...</p></div>

                        <!-- Class Timetable Status -->
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; margin-top: 32px;">
                            <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                                <i class="fas fa-chalkboard"></i>
                                Class Timetable Status
                            </h4>
                        </div>
                        <div id="adminClassTimetableStatus">
                            <p class="help-text">Loading class timetable statuses...</p>
                        </div>
                        <div id="adminClassTimetableStatusLoading" class="loading" style="display:none;"><div class="spinner"></div><p>Loading Class Timetable Status...</p></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>        // Global variables
        // Global variables
        const userRole = <?php echo json_encode($user_role); ?>;
        const sectionsData = <?php echo json_encode($sections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;

        // Utility functions - defined early to avoid reference errors
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

        function escapeHtml(unsafe) {
            if (unsafe == null) return '';
            return unsafe.toString()
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        function generateEmployeeNumber() {
            // Simple employee number generator - you may want to implement this via AJAX call
            const year = new Date().getFullYear();
            const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
            const empNumber = `VES${year}T${randomNum}`;
            const empNumberField = document.getElementById('employeeNumber');
            if (empNumberField) {
                empNumberField.value = empNumber;
            }
        }

        function showNotification(message, type = 'info') {
            const notification = document.getElementById('notification');
            if (notification) {
                notification.textContent = message;
                notification.className = `notification ${type}`;
                notification.style.display = 'block';
                setTimeout(() => {
                    notification.style.display = 'none';
                }, 5000);
            } else {
                // Fallback to console if notification element doesn't exist
                console.log(`${type.toUpperCase()}: ${message}`);
            }
        }

        function loadStatisticsForBulk() {
            // Placeholder function for bulk operations statistics
            console.log('Loading bulk operations statistics...');
        }

        // Search functionality for teachers
        function searchTeachers() {
            const searchTerm = $('#teacherSearch').val().toLowerCase();
            
            if (!searchTerm) {
                // If no search term, reload all teachers
                loadTeachers();
                return;
            }
            
            // Filter displayed teachers based on search term
            $('#teachersTable tbody tr').each(function() {
                const row = $(this);
                const text = row.text().toLowerCase();
                
                if (text.includes(searchTerm)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }

        // Modal functions
        function showModal(title, content) {
            // Create a simple modal overlay
            const modalHtml = `
                <div id="modalOverlay" class="modal-overlay" style="
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0,0,0,0.5); z-index: 9999; display: flex;
                    justify-content: center; align-items: center;
                ">
                    <div class="modal-content" style="
                        background: white; padding: 20px; border-radius: 8px;
                        max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;
                        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                    ">
                        <div class="modal-header" style="
                            display: flex; justify-content: space-between; align-items: center;
                            margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;
                        ">
                            <h3 style="margin: 0;">${title}</h3>
                            <button onclick="closeModal()" style="
                                background: none; border: none; font-size: 20px; cursor: pointer;
                                padding: 0; width: 30px; height: 30px; display: flex;
                                justify-content: center; align-items: center;
                            ">&times;</button>
                        </div>
                        <div class="modal-body">
                            ${content}
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing modal if any
            closeModal();
            
            // Add to body
            $('body').append(modalHtml);
            
            // Close on overlay click
            $('#modalOverlay').click(function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
        }

        function closeModal() {
            $('#modalOverlay').remove();
        }

        // Initialize the application
        $(document).ready(function() {
            // --- DEBUGGING CONSOLE LOGS ---
            console.log("Document ready. Initializing Headmaster Portal scripts.");
            
            if (typeof $ === 'undefined') {
                console.error("CRITICAL: jQuery is not loaded!");
                alert("Critical error: jQuery is not loaded. Page functionality will be severely impaired.");
                throw new Error("jQuery is not loaded");
            } else {
                // ✅ ALL INITIALIZATION CODE GOES INSIDE THIS ELSE BLOCK
                console.log("jQuery is loaded successfully. Proceeding with initialization...");
                
                if (typeof sectionsData === 'undefined') {
                    console.error("CRITICAL: sectionsData is undefined. This usually means an issue with PHP generating the data for JavaScript.");
                    $('#notification').text("Error: Page data (sectionsData) could not be loaded. Check browser console.").addClass('error').show();
                } else {
                    console.log("sectionsData loaded:", sectionsData);
                }
                console.log("userRole loaded:", userRole);

                if ($('.tabs-nav .tab-button').length === 0) {
                    console.warn("No tab buttons found. Check HTML structure.");
                }
                if ($('.tab-content').length === 0) {
                    console.warn("No tab content areas found. Check HTML structure.");
                }
                // --- END DEBUGGING CONSOLE LOGS ---

                initializeTabs();
                initializeFormHandlers();

                // Determine the initially active tab from HTML, or default to 'manage-teachers'
                // .first() is added in case multiple buttons somehow get the active class
                const initialTabId = $('.tabs-nav .tab-button.active').first().data('tab') || (userRole === 'admin' ? 'add-teacher' : 'manage-teachers');
                console.log("Attempting to activate initial tab ID:", initialTabId);
                
                if ($(`#${initialTabId}`).length === 0) {
                    console.error("CRITICAL: Initial tab content area #" + initialTabId + " not found in the DOM!");
                    $('#notification').text("Error: Initial tab content area not found. Page may not display correctly.").addClass('error').show();
                    // Fallback to the first available tab if the intended one is missing
                    const firstAvailableTab = $('.tabs-nav .tab-button').first().data('tab');
                    if (firstAvailableTab && $(`#${firstAvailableTab}`).length > 0) {
                        console.log("Falling back to first available tab:", firstAvailableTab);
                        switchTab(firstAvailableTab);
                    } else {
                        console.error("CRITICAL: No fallback tab content area found either.");
                    }
                } else {
                    switchTab(initialTabId); // This will also trigger data loading for the active tab
                }
                
                console.log("JavaScript initialization sequence complete. UI should be interactive if no errors occurred.");
            }
        });

        // Tab management
        function initializeTabs() {
            $('.tab-button').click(function() {
                const tabId = $(this).data('tab');
                switchTab(tabId);
            });
        }

        function switchTab(tabId) {
            console.log("Switching to tab:", tabId);
            // Update active states
            $('.tab-button').removeClass('active');
            $('.tab-content').removeClass('active');
            
            $(`[data-tab="${tabId}"]`).addClass('active');
            $(`#${tabId}`).addClass('active');
              // Load tab-specific data
            switch(tabId) {
                case 'add-teacher':
                    // Form is static, no specific data to load on switch
                    generateEmployeeNumber(); // Ensure emp number is fresh if admin switches back
                    break;
                case 'manage-teachers':
                    loadTeachers();
                    break;
                case 'class-assignments':
                    loadClassAssignments();
                    break;
                case 'subject-assignments':
                    // Load both individual teacher subjects and all assignments data
                    if ($('#subjectTeacher').val()) {
                        loadTeacherSubjects();
                    }
                    loadSubjectAssignmentsTabData();
                    break;
                case 'timetable-management':
                    loadAdminTimetableManagementData();
                    break;
                case 'bulk-operations':
                    loadStatisticsForBulk(); // Example: load stats if needed here
                    break;
                default:
                    console.warn("No specific data loading logic for tab:", tabId);
            }
        }

        // Alias for compatibility - some parts of the code expect showTab function
        function showTab(tabId) {
            switchTab(tabId);
        }

        // Form handlers
        function initializeFormHandlers() {
            // Add teacher form
            $('#addTeacherForm').submit(handleAddTeacher);
            
            // Class assignment
            $('#assignClass').change(updateSections);
            $('#assignClassTeacher').click(handleClassAssignment);
            
            // Subject assignment
            $('#subjectTeacher').change(loadTeacherSubjects);
            $('#updateSubjectAssignments').click(handleSubjectAssignment);
            $('#clearSubjectAssignments').click(clearSubjectSelections);
            
            // Search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Bulk operations
            $('#executeBulkAssignment').click(handleBulkAssignment);
            
            // Timetable specific handlers
            $('#adminRefreshConflictsBtn').off('click').on('click', loadAdminTimetableConflicts);
        }

        // Add teacher functionality
        function handleAddTeacher(e) {
            e.preventDefault();
            
            if (userRole !== 'admin') {
                showNotification('Only administrators can add new teachers', 'error');
                return;
            }
            
            const formData = new FormData(this);
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers();
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred while adding the teacher', 'error');
                }
            });
        }

        // Load teachers
        function loadTeachers() {
            $('#teachersLoading').show();
            $('#teachersTableBody').empty();
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teachers' },
                success: function(response) {
                    $('#teachersLoading').hide();
                    
                    if (response.success) {
                        displayTeachers(response.data);
                    } else {
                        showNotification('Failed to load teachers', 'error');
                    }
                },
                error: function() {
                    $('#teachersLoading').hide();
                    showNotification('An error occurred while loading teachers', 'error');
                }
            });
        }

        function displayTeachers(teachers) {
            const tbody = $('#teachersTableBody');
            tbody.empty();
            teachers.forEach(teacher => {
                const row = `
                    <tr>
                        <td>${escapeHtml(teacher.full_name)}</td>
                        <td>${escapeHtml(teacher.employee_number)}</td>
                        <td>${escapeHtml(teacher.email)}</td>
                        <td><span class="status-badge status-${teacher.status}">${teacher.status}</span></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="viewTeacher(${teacher.id})">
                                <i class="fas fa-eye"></i> View
                            </button>
                            ${userRole === 'admin' ? `
                                <button class="btn btn-primary btn-sm" onclick="editTeacher(${teacher.id})">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Class assignment functionality with enhanced section handling
        function updateSections() {
            const classId = $('#assignClass').val();
            const sectionSelect = $('#assignSection');
            
            sectionSelect.empty().append('<option value="">Select Section</option>');
            
            if (classId) {
                // Load sections with current teacher info via AJAX
                $.ajax({
                    url: 'teacher_management_api.php',
                    method: 'GET',
                    data: { action: 'get_sections', class_id: classId },
                    success: function(response) {
                        if (response.success) {
                            response.data.forEach(section => {
                                let optionText = escapeHtml(section.name);
                                if (section.current_teacher_name) {
                                    optionText += ` (Currently: ${section.current_teacher_name})`;
                                }
                                sectionSelect.append(`<option value="${section.id}">${optionText}</option>`);
                            });
                            sectionSelect.prop('disabled', false);
                        }
                    }
                });
            } else {
                sectionSelect.prop('disabled', true);
            }
        }

        function handleClassAssignment() {
            const data = {
                action: 'assign_class_teacher',
                teacher_id: $('#assignTeacher').val(),
                class_id: $('#assignClass').val(),
                section_id: $('#assignSection').val()
            };
            
            if (!data.teacher_id || !data.class_id || !data.section_id) {
                showNotification('Please select teacher, class, and section', 'error');
                return;
            }
            
            // First check for conflicts
            checkAssignmentConflicts(data);
        }

        function checkAssignmentConflicts(assignmentData) {
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: {
                    action: 'check_conflicts',
                    teacher_id: assignmentData.teacher_id,
                    section_id: assignmentData.section_id
                },
                success: function(response) {
                    if (response.success && response.conflicts.length > 0) {
                        showConflictDialog(response.conflicts, response.workload, assignmentData);
                    } else {
                        // No conflicts, proceed with assignment
                        executeClassAssignment(assignmentData);
                    }
                },
                error: function() {
                    showNotification('Error checking conflicts', 'error');
                }
            });
        }

        function showConflictDialog(conflicts, workload, assignmentData) {
            let conflictHtml = '<div class="conflict-dialog"><h4>Assignment Conflicts Detected</h4>';
            
            conflicts.forEach(conflict => {
                conflictHtml += `<div class="conflict-item"><strong>${conflict.type.replace(/_/g, ' ').toUpperCase()}:</strong> ${conflict.message}</div>`;
            });
            
            if (workload.class_teacher_count > 0) {
                conflictHtml += `<div class="workload-info"><h5>Current Workload:</h5>`;
                conflictHtml += `<p>Class Teacher: ${workload.class_teacher_count} class(es)</p>`;
                conflictHtml += `<p>Subject Assignments: ${workload.subject_count} subject(s)</p></div>`;
            }
            
            conflictHtml += '<div class="conflict-actions">';
            conflictHtml += '<button class="btn btn-primary" onclick="forceAssignment()">Force Assignment</button>';
            conflictHtml += '<button class="btn btn-secondary" onclick="closeConflictDialog()">Cancel</button>';
            conflictHtml += '</div></div>';
            
            // Store assignment data for force assignment
            window.pendingAssignment = assignmentData;
            
            // Show modal or inline conflict display
            showModal('Assignment Conflicts', conflictHtml);
        }

        function forceAssignment() {
            if (window.pendingAssignment) {
                window.pendingAssignment.force_assign = true;
                executeClassAssignment(window.pendingAssignment);
                closeConflictDialog();
            }
        }

        function closeConflictDialog() {
            window.pendingAssignment = null;
            closeModal();
        }

        function executeClassAssignment(data) {
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        showNotification(response.message || 'Class teacher assigned successfully!', 'success');
                        if (response.conflicts_resolved > 0) {
                            showNotification(`${response.conflicts_resolved} conflicts were resolved`, 'info');
                        }
                        loadClassAssignments();
                        // Reset form
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                    } else if (response.require_confirmation) {
                        showConflictDialog(response.conflicts, {}, data);
                    } else {
                        showNotification(response.message || 'Failed to assign class teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred while assigning class teacher', 'error');
                }
            });
        }

        function loadClassAssignments() {
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_class_assignments' },
                success: function(response) {
                    if (response.success) {
                        displayClassAssignments(response.data);
                    }
                },
                error: function() {
                    showNotification('An error occurred while loading class assignments', 'error');
                }
            });
        }

        function displayClassAssignments(assignments) {
            const container = $('#classAssignmentsTable');
            
            if (assignments.length === 0) {
                container.html('<p class="help-text">No class teacher assignments found</p>');
                return;
            }
            
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Teacher</th>
                            <th>Employee ID</th>
                            <th>Workload</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            assignments.forEach(assignment => {
                html += `
                    <tr>
                        <td><strong>${escapeHtml(assignment.class_name)}</strong></td>
                        <td><span class="section-badge">${escapeHtml(assignment.section_name)}</span></td>
                        <td>
                            <div class="teacher-info">
                                <div class="teacher-name">${escapeHtml(assignment.teacher_name)}</div>
                                <div class="teacher-email">${escapeHtml(assignment.employee_number)}</div>
                            </div>
                        </td>
                        <td><code>${escapeHtml(assignment.employee_number)}</code></td>
                        <td>
                            <button class="btn btn-outline btn-sm" onclick="showTeacherWorkload(${assignment.teacher_id})">
                                <i class="fas fa-chart-bar"></i> View
                            </button>
                        </td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="reassignClassTeacher(${assignment.section_id}, '${escapeHtml(assignment.class_name)} - ${escapeHtml(assignment.section_name)}')">
                                <i class="fas fa-exchange-alt"></i> Reassign
                            </button>
                            ${userRole === 'admin' ? `
                                <button class="btn btn-secondary btn-sm" onclick="removeClassTeacher(${assignment.section_id}, '${escapeHtml(assignment.teacher_name)}', '${escapeHtml(assignment.class_name)} - ${escapeHtml(assignment.section_name)}')">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.html(html);
        }

        function showTeacherWorkload(teacherId) {
            console.log('Showing teacher workload for teacher ID:', teacherId);
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teacher_workload', teacher_id: teacherId },
                dataType: 'json',
                success: function(response) {
                    console.log('Workload API response:', response);
                    if (response.success) {
                        displayWorkloadModal(response.data);
                    } else {
                        showModal('Error', '<p>Failed to load teacher workload data.</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading teacher workload:', error);
                    showModal('Error', '<p>Error loading teacher workload data. Please try again.</p>');
                }
            });
        }
        

        function displayWorkloadModal(workload) {
            console.log('Displaying workload modal with data:', workload);
            
            let html = '<div class="workload-details">';
            html += `<h4>Teacher Workload Summary</h4>`;
            html += `<div class="workload-stats">`;
            html += `<div class="stat-item"><span class="stat-label">Class Teacher Assignments:</span> <span class="stat-value">${workload.class_assignments || 0} class(es)</span></div>`;
            html += `<div class="stat-item"><span class="stat-label">Subject Assignments:</span> <span class="stat-value">${workload.subject_assignments || 0} subject(s)</span></div>`;
            html += `<div class="stat-item"><span class="stat-label">Timetable Periods:</span> <span class="stat-value">${workload.timetable_periods || 0} period(s)</span></div>`;
            html += `</div>`;
            
            // Note: The current API only returns counts, not detailed lists
            // We would need to modify the API to get detailed lists if needed
            if (workload.class_assignments > 0) {
                html += `<p><small><em>Note: This teacher is assigned as class teacher to ${workload.class_assignments} class(es).</em></small></p>`;
            }
            
            if (workload.subject_assignments > 0) {
                html += `<p><small><em>Note: This teacher has ${workload.subject_assignments} subject assignment(s).</em></small></p>`;
            }
              if (workload.timetable_periods > 0) {
                html += `<p><small><em>Note: This teacher has ${workload.timetable_periods} period(s) in published timetables.</em></small></p>`;
            }
            
            html += '</div>';
            showModal('Teacher Workload', html);
        }
        

        // Reassignment functionality
        function reassignClassTeacher(sectionId, sectionInfo) {
            // First load available teachers, then show the modal
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teachers' },
                success: function(response) {
                    if (response.success) {
                        showReassignmentModal(sectionId, sectionInfo, response.data);
                    } else {
                        showNotification('Failed to load teachers', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teachers for reassignment', 'error');
                }
            });
        }

        function showReassignmentModal(sectionId, sectionInfo, teachers) {
            let html = `
                <div class="reassign-form">
                    <h4 class="reassign-title">Reassign Class Teacher for ${sectionInfo}</h4>
                    <div class="form-group">
                        <label>Select New Teacher:</label>
                        <select id="newTeacherSelect" class="form-select">
                            <option value="">Select Teacher</option>`;
            
            // Populate with teachers from API response
            teachers.forEach(teacher => {
                html += `<option value="${teacher.id}">${escapeHtml(teacher.full_name)} (${escapeHtml(teacher.employee_number || 'N/A')})</option>`;
            });
            
            html += `
                        </select>
                    </div>
                    <div class="form-actions">
                        <button class="btn btn-primary" onclick="executeReassignment(${sectionId})">Reassign</button>
                        <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    </div>
                </div>
            `;
            
            showModal('Reassign Class Teacher', html);
        }

        function executeReassignment(sectionId) {
            const newTeacherId = $('#newTeacherSelect').val();
            if (!newTeacherId) {
                showNotification('Please select a teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: {
                    action: 'reassign_class_teacher',
                    section_id: sectionId,
                    new_teacher_id: newTeacherId
                },
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher reassigned successfully!', 'success');
                        loadClassAssignments();
                        closeModal();
                    } else {
                        showNotification(response.message || 'Failed to reassign teacher', 'error');
                    }
                }
            });
        }        // Subject assignment functionality
        function loadTeacherSubjects() {
            const teacherId = $('#subjectTeacher').val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                $('input[name="subjects[]"]').prop('checked', false);
                return;
            }
            
            // Show loading state
            $('#subjectAssignmentsDisplay').html('<p class="help-text">Loading current subject assignments...</p>');
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teacher_subjects', teacher_id: teacherId },
                dataType: 'json',
                success: function(response) {
                    console.log('Teacher subjects response:', response); // Debug log
                    if (response.success) {
                        // Update checkboxes
                        $('input[name="subjects[]"]').prop('checked', false);
                        if (response.data && response.data.length > 0) {
                            response.data.forEach(subject => {
                                $(`input[name="subjects[]"][value="${subject.subject_id}"]`).prop('checked', true);
                            });
                        }
                        
                        // Display current assignments
                        displayTeacherSubjects(response.data || []);
                    } else {
                        console.error('API returned error:', response.message);
                        $('#subjectAssignmentsDisplay').html('<p class="help-text text-danger">Failed to load subject assignments: ' + (response.message || 'Unknown error') + '</p>');
                        showNotification(response.message || 'Failed to load teacher subjects', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error loading teacher subjects:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    $('#subjectAssignmentsDisplay').html('<p class="help-text text-danger">Error loading subject assignments. Please try again.</p>');
                    showNotification('Failed to load teacher subjects: ' + error, 'error');
                }
            });
        }        function displayTeacherSubjects(subjects) {
            const container = $('#subjectAssignmentsDisplay');
            
            if (!subjects || subjects.length === 0) {
                container.html('<p class="help-text">No subjects assigned to this teacher</p>');
                return;
            }
            
            let html = '<div style="display: flex; flex-wrap: wrap; gap: 8px;">';
            subjects.forEach(subject => {
                // Handle class and section names safely
                const className = subject.class_name ? ` - ${escapeHtml(subject.class_name)}` : '';
                const sectionName = subject.section_name ? ` (${escapeHtml(subject.section_name)})` : '';
                const subjectName = subject.subject_name ? escapeHtml(subject.subject_name) : 'Unknown Subject';
                
                html += `<span class="status-badge status-active">${subjectName}${className}${sectionName}</span>`;
            });
            html += '</div>';
            
            container.html(html);
        }        function handleSubjectAssignment() {
            const teacherId = $('#subjectTeacher').val();
            const selectedSubjects = $('input[name="subjects[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            if (!teacherId) {
                showNotification('Please select a teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadTeacherSubjects(); // Refresh display
                    } else {
                        showNotification(response.message || 'Failed to update subject assignments', 'error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error updating subject assignments:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    showNotification('An error occurred while updating subject assignments', 'error');
                }
            });
        }

        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }

        // Bulk operations
        function handleBulkAssignment() {
            const classId = $('#bulkAssignClass').val();
            const subjectId = $('#bulkAssignSubject').val();
            const teacherIds = $('input[name="bulkTeachers[]"]:checked').map(function() {
                return this.value;
            }).get();
            
            const data = {
                action: 'bulk_assignment',
                class_id: classId,
                subject_id: subjectId,
                teacher_ids: teacherIds
            };
            
            if (!classId || !subjectId || teacherIds.length === 0) {
                showNotification('Please select class, subject, and at least one teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        showNotification('Bulk assignment completed successfully!', 'success');
                        // Reset form
                        $('#bulkAssignClass, #bulkAssignSubject').val('');
                        $('input[name="bulkTeachers[]"]').prop('checked', false);
                    } else {
                        showNotification(response.message || 'Failed to execute bulk assignment', 'error');
                    }
                },
                error: function() {
                    showNotification('An error occurred during bulk assignment', 'error');
                }
            });
        }

        // TIMETABLE MANAGEMENT FUNCTIONS (Admin version)
        function loadAdminTimetableManagementData() {
            console.log("Loading all admin timetable management data...");
            loadAdminTimetableConflicts();
            loadAdminTeacherSchedules();
            loadAdminClassTimetableStatus();
        }

        function loadAdminTimetableConflicts() {
            $('#adminTimetableConflictsLoading').show();
            $('#adminTimetableConflicts').html('<p class="help-text">Loading conflicts...</p>');
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_timetable_conflicts' }, // Reusing API action
                success: function(response) {
                    $('#adminTimetableConflictsLoading').hide();
                    if (response.success && response.data) {
                        displayAdminTimetableConflicts(response.data);
                    } else {
                        $('#adminTimetableConflicts').html(`<p class="help-text text-danger">Failed to load conflicts: ${response.message || 'Unknown error'}</p>`);
                        showNotification(response.message || 'Failed to load timetable conflicts', 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#adminTimetableConflictsLoading').hide();
                    $('#adminTimetableConflicts').html('<p class="help-text text-danger">Error loading conflicts. Please try again.</p>');
                    showNotification('Error loading timetable conflicts: ' + errorThrown, 'error');
                    console.error("AJAX error get_timetable_conflicts (admin):", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        }

        function displayAdminTimetableConflicts(conflicts) {
            const container = $('#adminTimetableConflicts');
            container.empty();
            if (conflicts.length === 0) {
                container.html('<p class="help-text">No schedule conflicts found.</p>');
                return;
            }
            let html = '<ul class="list-group">';
            conflicts.forEach(conflict => {
                html += `<li class="list-group-item list-group-item-warning">
                            <strong>Conflict:</strong> ${escapeHtml(conflict.description || 'N/A')} <br>
                            <strong>Teacher:</strong> ${escapeHtml(conflict.teacher_name || 'N/A')} <br>
                            <strong>Details:</strong> Class ${escapeHtml(conflict.class_name || 'N/A')} - ${escapeHtml(conflict.subject_name || 'N/A')} at ${escapeHtml(conflict.time_slot || 'N/A')} on ${escapeHtml(conflict.day_of_week || 'N/A')}
                        </li>`;
            });
            html += '</ul>';
            container.html(html);
        }

        function loadAdminTeacherSchedules() {
            $('#adminTeacherTimetableOverviewLoading').show();
            $('#adminTeacherTimetableOverview').html('<p class="help-text">Loading teacher schedules...</p>');
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teacher_schedules' }, // Reusing API action
                success: function(response) {
                    $('#adminTeacherTimetableOverviewLoading').hide();
                    if (response.success && response.data) {
                        displayAdminTeacherSchedules(response.data);
                    } else {
                        $('#adminTeacherTimetableOverview').html(`<p class="help-text text-danger">Failed to load teacher schedules: ${response.message || 'Unknown error'}</p>`);
                        showNotification(response.message || 'Failed to load teacher schedules', 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#adminTeacherTimetableOverviewLoading').hide();
                    $('#adminTeacherTimetableOverview').html('<p class="help-text text-danger">Error loading teacher schedules. Please try again.</p>');
                    showNotification('Error loading teacher schedules: ' + errorThrown, 'error');
                    console.error("AJAX error get_teacher_schedules (admin):", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        }

        function displayAdminTeacherSchedules(schedules) {
            const container = $('#adminTeacherTimetableOverview');
            container.empty();
            if (schedules.length === 0) {
                container.html('<p class="help-text">No teacher schedules available.</p>');
                return;
            }
            let html = '<div class="accordion" id="adminTeacherScheduleAccordion">';
            schedules.forEach((teacherSchedule, index) => {
                html += `
                    <div class="card">
                        <div class="card-header" id="headingAdminTeacher${index}">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseAdminTeacher${index}" aria-expanded="true" aria-controls="collapseAdminTeacher${index}">
                                    ${escapeHtml(teacherSchedule.teacher_name)} (${escapeHtml(teacherSchedule.employee_number || 'N/A')})
                                </button>
                            </h5>
                        </div>
                        <div id="collapseAdminTeacher${index}" class="collapse" aria-labelledby="headingAdminTeacher${index}" data-parent="#adminTeacherScheduleAccordion">
                            <div class="card-body">
                                <table class="data-table table-sm">
                                    <thead><tr><th>Day</th><th>Time</th><th>Class</th><th>Section</th><th>Subject</th></tr></thead>
                                    <tbody>`;
                if (teacherSchedule.schedule && teacherSchedule.schedule.length > 0) {
                    teacherSchedule.schedule.forEach(entry => {
                        html += `<tr>
                                    <td>${escapeHtml(entry.day_of_week)}</td>
                                    <td>${escapeHtml(entry.time_slot)}</td>
                                    <td>${escapeHtml(entry.class_name)}</td>
                                    <td>${escapeHtml(entry.section_name)}</td>
                                    <td>${escapeHtml(entry.subject_name)}</td>
                                </tr>`;
                    });
                } else {
                    html += '<tr><td colspan="5" class="text-center">No schedule entries found for this teacher.</td></tr>';
                }
                html +=       `</tbody></table>
                            </div>
                        </div>
                    </div>`;
            });
            html += '</div>';
            container.html(html);
            // Initialize Bootstrap collapse if you're using it and it's available
            // if (typeof $.fn.collapse === 'function') {
            //    $('#adminTeacherScheduleAccordion .collapse').collapse(); 
            // }
        }

        function loadAdminClassTimetableStatus() {
            $('#adminClassTimetableStatusLoading').show();
            $('#adminClassTimetableStatus').html('<p class="help-text">Loading class timetable statuses...</p>');
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_class_timetable_status' }, // Reusing API action
                success: function(response) {
                    $('#adminClassTimetableStatusLoading').hide();
                    if (response.success && response.data) {
                        displayAdminClassTimetableStatus(response.data);
                    } else {
                        $('#adminClassTimetableStatus').html(`<p class="help-text text-danger">Failed to load class timetable status: ${response.message || 'Unknown error'}</p>`);
                        showNotification(response.message || 'Failed to load class timetable status', 'error');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#adminClassTimetableStatusLoading').hide();
                    $('#adminClassTimetableStatus').html('<p class="help-text text-danger">Error loading class timetable status. Please try again.</p>');
                    showNotification('Error loading class timetable status: ' + errorThrown, 'error');
                    console.error("AJAX error get_class_timetable_status (admin):", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        }

        function displayAdminClassTimetableStatus(statuses) {
            const container = $('#adminClassTimetableStatus');
            container.empty();
            if (statuses.length === 0) {
                container.html('<p class="help-text">No class timetable statuses available.</p>');
                return;
            }
            let html = '<table class="data-table"><thead><tr><th>Class</th><th>Section</th><th>Status</th><th>Coverage</th><th>Issues</th></tr></thead><tbody>';
            statuses.forEach(status => {
                html += `<tr>
                            <td>${escapeHtml(status.class_name)}</td>
                            <td>${escapeHtml(status.section_name)}</td>
                            <td><span class="status-badge status-${status.is_complete ? 'active' : 'inactive'}">${status.is_complete ? 'Complete' : 'Incomplete'}</span></td>
                            <td>${escapeHtml(status.coverage_percentage || '0')}%</td>
                            <td>${escapeHtml(status.issues_count || '0')}</td>
                        </tr>`;
            });
            html += '</tbody></table>';
            container.html(html);
        }

        // Bulk operations helper function
        function loadStatisticsForBulk() {
            // Load basic statistics for bulk operations display
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_statistics' },
                success: function(response) {
                    if (response.success) {
                        // Update any statistics displays in bulk operations tab
                        console.log('Bulk statistics loaded:', response.data);
                    }
                },
                error: function() {
                    console.warn('Failed to load bulk statistics');
                }
            });
        }

        // Additional helper functions that might be called from HTML

        function viewTeacher(teacherId) {
            // Implementation for viewing teacher details
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teacher_details', teacher_id: teacherId },
                success: function(response) {
                    if (response.success) {
                        showTeacherDetailsModal(response.data);
                    } else {
                        showNotification('Failed to load teacher details', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher details', 'error');
                }
            });
        }

        function editTeacher(teacherId) {
            // Implementation for editing teacher
            console.log('Edit teacher:', teacherId);
            showNotification('Edit teacher functionality to be implemented', 'info');
        }

        function removeClassTeacher(sectionId, teacherName, sectionInfo) {
            if (confirm(`Are you sure you want to remove ${teacherName} as class teacher for ${sectionInfo}?`)) {
                $.ajax({
                    url: 'teacher_management_api.php',
                    method: 'POST',
                    data: {
                        action: 'remove_class_teacher',
                        section_id: sectionId
                    },
                    success: function(response) {
                        if (response.success) {
                            showNotification('Class teacher removed successfully!', 'success');
                            loadClassAssignments();
                        } else {
                            showNotification(response.message || 'Failed to remove class teacher', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error removing class teacher', 'error');
                    }
                });
            }
        }

        function showTeacherDetailsModal(teacher) {
            let html = `
                <div class="teacher-details">
                    <h4>Teacher Details</h4>
                    <div class="details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div><strong>Name:</strong> ${escapeHtml(teacher.full_name)}</div>
                        <div><strong>Employee ID:</strong> ${escapeHtml(teacher.employee_number || 'N/A')}</div>
                        <div><strong>Email:</strong> ${escapeHtml(teacher.email)}</div>
                        <div><strong>Phone:</strong> ${escapeHtml(teacher.phone || 'N/A')}</div>
                        <div><strong>Status:</strong> <span class="status-badge status-${teacher.status}">${teacher.status}</span></div>
                        <div><strong>Created:</strong> ${escapeHtml(teacher.created_at || 'N/A')}</div>
                    </div>
                    <div style="margin-top: 16px;">
                        <strong>Class Assignments:</strong> ${teacher.classes || 'None'}
                    </div>
                </div>
            `;
            
            showModal('Teacher Details', html);
        }
        
        // Load all subject assignments
        function loadAllSubjectAssignments() {
            $('#allAssignmentsLoading').show();
            $('#allAssignmentsTable').hide();
            $('#allAssignmentsEmpty').hide();
            
            const searchTerm = $('#allAssignmentsSearch').val();
            const teacherId = $('#filterTeacher').val();
            const subjectId = $('#filterSubject').val();
            
            const params = {
                action: 'get_all_subject_assignments'
            };
            
            if (searchTerm) params.search = searchTerm;
            if (teacherId) params.teacher_id = teacherId;
            if (subjectId) params.subject_id = subjectId;
            
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: params,
                dataType: 'json',
                success: function(response) {
                    $('#allAssignmentsLoading').hide();
                    
                    if (response.success) {
                        displayAllAssignments(response.data);
                        updateAssignmentStatistics(response.data.statistics);
                    } else {
                        showNotification('Failed to load subject assignments: ' + (response.message || 'Unknown error'), 'error');
                        $('#allAssignmentsEmpty').show();
                    }
                },
                error: function(xhr, status, error) {
                    $('#allAssignmentsLoading').hide();
                    $('#allAssignmentsEmpty').show();
                    console.error('Error loading all subject assignments:', error);
                    showNotification('Error loading subject assignments: ' + error, 'error');
                }
            });
        }

        // Display all assignments in the table
        function displayAllAssignments(data) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            if (!data.assignments || data.assignments.length === 0) {
                $('#allAssignmentsTable').hide();
                $('#allAssignmentsEmpty').show();
                return;
            }
            
            $('#allAssignmentsEmpty').hide();
            $('#allAssignmentsTable').show();
            
            data.assignments.forEach(teacher => {
                if (teacher.assignments && teacher.assignments.length > 0) {
                    // First row for teacher info
                    let isFirstRow = true;
                    
                    teacher.assignments.forEach(assignment => {                        const row = $(`
                            <tr>
                                <td><code>${escapeHtml(teacher.teacher_info.employee_number || 'N/A')}</code></td>
                                <td>
                                    <div class="teacher-info-cell">
                                        <div class="teacher-name">${escapeHtml(teacher.teacher_info.name)}</div>
                                        <div class="teacher-email">${escapeHtml(teacher.teacher_info.email)}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="subject-info-cell">
                                        <div class="subject-name">${escapeHtml(assignment.subject_name)}</div>
                                        ${assignment.subject_code ? `<div class="subject-code">${escapeHtml(assignment.subject_code)}</div>` : ''}
                                    </div>
                                </td>
                                <td><code>${escapeHtml(assignment.subject_code || 'N/A')}</code></td>
                                <td>
                                    <span class="assignment-scope-badge ${assignment.assignment_scope === 'All Classes' ? 'all-classes' : 'specific-class'}">
                                        ${escapeHtml(assignment.assignment_scope)}
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-${teacher.teacher_info.status}">
                                        ${teacher.teacher_info.status}
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="btn btn-outline btn-sm" onclick="viewAssignmentDetails(${assignment.assignment_id})" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    ${userRole === 'admin' ? `
                                        <button class="btn btn-secondary btn-sm" onclick="removeSpecificAssignment(${assignment.assignment_id}, '${escapeHtml(teacher.teacher_info.name)}', '${escapeHtml(assignment.subject_name)}')" title="Remove Assignment">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    ` : ''}
                                </td>
                            </tr>
                        `);
                        
                        tbody.append(row);
                        isFirstRow = false;
                    });
                } else {                    // Teacher with no assignments
                    const row = $(`
                        <tr>
                            <td><code>${escapeHtml(teacher.teacher_info.employee_number || 'N/A')}</code></td>
                            <td>
                                <div class="teacher-info-cell">
                                    <div class="teacher-name">${escapeHtml(teacher.teacher_info.name)}</div>
                                    <div class="teacher-email">${escapeHtml(teacher.teacher_info.email)}</div>
                                </div>
                            </td>
                            <td colspan="3" class="text-center" style="color: var(--text-secondary); font-style: italic;">
                                No subject assignments
                            </td>
                            <td>
                                <span class="status-badge status-${teacher.teacher_info.status}">
                                    ${teacher.teacher_info.status}
                                </span>
                            </td>
                            <td class="actions">
                                <button class="btn btn-primary btn-sm" onclick="assignSubjectToTeacher(${teacher.teacher_info.id}, '${escapeHtml(teacher.teacher_info.name)}')" title="Assign Subject">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    `);
                    
                    tbody.append(row);
                }
            });
        }

        // Update statistics display
        function updateAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            container.empty();
            
            const statisticsCards = [
                { label: 'Total Teachers', value: stats.total_teachers, icon: 'fas fa-users' },
                { label: 'With Assignments', value: stats.teachers_with_assignments, icon: 'fas fa-user-check' },
                { label: 'Total Assignments', value: stats.total_assignments, icon: 'fas fa-list' },
                { label: 'Unique Subjects', value: stats.unique_subjects, icon: 'fas fa-book' }
            ];
            
            statisticsCards.forEach(stat => {
                const card = $(`
                    <div class="stat-card">
                        <i class="${stat.icon}" style="font-size: 1.5rem; color: var(--primary-color); margin-bottom: 8px;"></i>
                        <span class="stat-number">${stat.value}</span>
                        <span class="stat-label">${stat.label}</span>
                    </div>
                `);
                container.append(card);
            });
        }

        // Remove specific assignment
        function removeSpecificAssignment(assignmentId, teacherName, subjectName) {
            if (confirm(`Are you sure you want to remove ${subjectName} assignment from ${teacherName}?`)) {
                $.ajax({
                    url: 'teacher_management_api.php',
                    method: 'POST',
                    data: {
                        action: 'remove_subject_assignment',
                        assignment_id: assignmentId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showNotification('Subject assignment removed successfully!', 'success');
                            loadAllSubjectAssignments();
                            // Also refresh the current teacher's assignments if they're selected
                            if ($('#subjectTeacher').val()) {
                                loadTeacherSubjects();
                            }
                        } else {
                            showNotification(response.message || 'Failed to remove assignment', 'error');
                        }
                    },
                    error: function() {
                        showNotification('Error removing assignment', 'error');
                    }
                });
            }
        }

        // View assignment details
        function viewAssignmentDetails(assignmentId) {
            // Implementation for viewing detailed assignment info
            showNotification('Assignment details view - to be implemented', 'info');
        }

        // Assign subject to teacher (quick assign)
        function assignSubjectToTeacher(teacherId, teacherName) {
            // Set the teacher in the subject assignment form and scroll to it
            $('#subjectTeacher').val(teacherId).trigger('change');
            
            // Scroll to the assignment form
            $('html, body').animate({
                scrollTop: $('#subjectTeacher').offset().top - 100
            }, 500);
            
            showNotification(`Quick assign: ${teacherName} selected in assignment form`, 'info');
        }

        // Load filter options
        function loadAssignmentFilters() {
            // Load teachers for filter
            $.ajax({
                url: 'teacher_management_api.php',
                method: 'GET',
                data: { action: 'get_teachers' },
                success: function(response) {
                    if (response.success) {
                        const teacherSelect = $('#filterTeacher');
                        teacherSelect.find('option[value!=""]').remove();
                        
                        response.data.forEach(teacher => {
                            teacherSelect.append(`
                                <option value="${teacher.id}">
                                    ${escapeHtml(teacher.full_name)} (${escapeHtml(teacher.employee_number || 'N/A')})
                                </option>
                            `);
                        });
                    }
                }
            });
            
            // Load subjects for filter
            const subjectSelect = $('#filterSubject');
            subjectSelect.find('option[value!=""]').remove();
            
            // Populate with subjects from the existing subjects data
            if (typeof subjects !== 'undefined' && subjects.length > 0) {
                subjects.forEach(subject => {
                    subjectSelect.append(`
                        <option value="${subject.id}">
                            ${escapeHtml(subject.name)} (${escapeHtml(subject.code)})
                        </option>
                    `);
                });
            }
        }

        // Clear all filters
        function clearAllFilters() {
            $('#allAssignmentsSearch').val('');
            $('#filterTeacher').val('');
            $('#filterSubject').val('');
            loadAllSubjectAssignments();
        }

        // Export assignments (placeholder)
        function exportAssignments() {
            showNotification('Export functionality - to be implemented', 'info');
        }

        // Event handlers for the new section
        $(document).ready(function() {
            // Add event handlers
            $('#refreshAllAssignments').click(loadAllSubjectAssignments);
            $('#exportAssignments').click(exportAssignments);
            $('#clearAllFilters').click(clearAllFilters);
            
            // Search with debounce
            $('#allAssignmentsSearch').on('input', debounce(loadAllSubjectAssignments, 500));
            
            // Filter changes
            $('#filterTeacher, #filterSubject').change(loadAllSubjectAssignments);
            
            // Load initial data when the subject assignments tab is shown
            // This will be called when the tab is switched
        });

        // Update the existing switchTab function to load the new data
        function loadSubjectAssignmentsTabData() {
            // Load existing teacher subjects if teacher is selected
            if ($('#subjectTeacher').val()) {
                loadTeacherSubjects();
            }
            
            // Load all assignments table
            loadAllSubjectAssignments();
            
            // Load filter options
            loadAssignmentFilters();
        }
    </script>
</body>
</html>
