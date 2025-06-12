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
        }        .tab-content {
            display: none;
            padding: 24px;
        }

        .tab-content.active {
            display: block !important;
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

        /* Teacher Schedule Editor Styles */
        .schedule-editor-section {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 20px;
        }

        .schedule-grid-container {
            overflow-x: auto;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .schedule-grid-table {
            min-width: 800px;
            font-size: 14px;
        }

        .schedule-grid-table th {
            background-color: #f8fafc;
            color: #374151;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .period-cell-empty {
            background-color: #f9fafb;
            transition: all 0.2s ease;
        }

        .period-cell-empty:hover {
            background-color: #f3f4f6;
            cursor: pointer;
        }

        .period-cell-filled {
            background-color: #dbeafe;
            border-color: #3b82f6;
            transition: all 0.2s ease;
        }

        .period-cell-filled:hover {
            background-color: #bfdbfe;
            cursor: pointer;
        }

        .period-cell-selected {
            box-shadow: 0 0 0 2px #4f46e5 !important;
        }

        .period-content {
            line-height: 1.4;
        }

        .schedule-actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .alert {
            padding: 12px 16px;
            border-radius: var(--border-radius);
            margin-bottom: 16px;
        }

        .alert-warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
        }

        .alert h6 {
            margin: 0 0 8px 0;
            font-weight: 600;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert li {
            margin-bottom: 4px;
        }

        /* Loading animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .fa-spinner.fa-spin {
            animation: spin 1s linear infinite;
        }

        /* Quick actions buttons */
        .quick-actions .btn {
            margin-right: 8px;
            margin-bottom: 8px;
        }

        /* Period editor form */
        #periodEditorForm {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
        }

        #periodEditorForm .form-row {
            display: flex;
            align-items: end;
            flex-wrap: wrap;
            gap: 15px;
        }

        #periodEditorForm .form-group {
            margin-bottom: 0;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .schedule-grid-table {
                font-size: 12px;
            }
            
            .schedule-grid-table th,
            .schedule-grid-table td {
                padding: 6px !important;
            }
            
            .period-content {
                font-size: 11px;
            }
            
            #periodEditorForm .form-row {
                flex-direction: column;
                align-items: stretch;
            }
            
            .quick-actions {
                flex-direction: column;
            }
            
            .quick-actions .btn {
                margin-right: 0;
                width: 100%;
            }
        }

        /* Additional improvements */
        .section-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 16px;
        }

        .schedule-header {
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 12px;
        }

        .form-row {
            display: flex;
            align-items: end;
            flex-wrap: wrap;
            gap: 15px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 12px 16px;
            border-radius: var(--border-radius);
            color: white;
            font-weight: 500;
            max-width: 400px;
            box-shadow: var(--shadow-md);
            display: none;
        }

        .notification.success {
            background-color: #10b981;
        }

        .notification.error {
            background-color: #ef4444;
        }

        .notification.warning {
            background-color: #f59e0b;
        }

        .notification.info {
            background-color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <i class="fas fa-bars hamburger-icon"></i>
    </button>

    <div class="unified-container">
        <!-- Notification Element -->
        <div id="notification" class="notification"></div>
        
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
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
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
                        <div id="adminTeacherTimetableOverviewLoading" class="loading" style="display:none;"><div class="spinner"></div><p>Loading Teacher Schedules...</p></div>                        <!-- Class Timetable Status -->
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

                        <!-- Individual Teacher Schedule Editor -->
                        <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; margin-top: 32px;">
                            <h4 class="section-title" style="font-size: 1.1rem; font-weight: 600; margin:0;">
                                <i class="fas fa-user-edit"></i>
                                Individual Teacher Schedule Editor
                            </h4>
                            <button class="btn btn-primary btn-sm" id="teacherScheduleEditorToggle">
                                <i class="fas fa-calendar-plus"></i>
                                Open Schedule Editor
                            </button>
                        </div>
                        <div id="teacherScheduleEditor" style="display: none;">                            <!-- Teacher Selection -->
                            <div class="form-row" style="margin-bottom: 20px;">
                                <div class="form-group" style="margin-right: 20px;">
                                    <label for="selectedTeacher" class="form-label">Select Teacher</label>
                                    <select id="selectedTeacher" class="form-select" style="min-width: 250px;">
                                        <option value="">Choose a teacher...</option>
                                    </select>
                                </div>
                                <div class="form-group" style="display: flex; align-items: end;">
                                    <button class="btn btn-outline" id="loadTeacherSchedule">
                                        <i class="fas fa-download"></i>
                                        Load Complete Schedule
                                    </button>
                                </div>
                            </div>

                            <!-- Teacher Schedule Grid -->
                            <div id="teacherScheduleGrid" style="display: none;">
                                <div class="schedule-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                                    <h5 id="currentTeacherName" style="margin: 0; color: #374151;">Teacher Schedule</h5>
                                    <div class="schedule-actions">
                                        <button class="btn btn-sm btn-outline" id="scheduleConflictCheck">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            Check Conflicts
                                        </button>
                                        <button class="btn btn-sm btn-success" id="saveScheduleChanges" style="display: none;">
                                            <i class="fas fa-save"></i>
                                            Save Changes
                                        </button>
                                    </div>
                                </div>

                                <!-- Schedule Grid Table -->
                                <div class="schedule-grid-container">
                                    <table class="schedule-grid-table" style="width: 100%; border-collapse: collapse; border: 1px solid #e5e7eb;">
                                        <thead>
                                            <tr style="background-color: #f9fafb;">
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Time</th>
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Monday</th>
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Tuesday</th>
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Wednesday</th>
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Thursday</th>
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Friday</th>
                                                <th style="border: 1px solid #e5e7eb; padding: 12px; text-align: center; font-weight: 600;">Saturday</th>
                                            </tr>
                                        </thead>
                                        <tbody id="scheduleGridBody">
                                            <!-- Schedule periods will be populated here -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Period Editor Form -->                                <div id="periodEditorForm" style="display: none; margin-top: 20px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px; background-color: #f9fafc;">
                                    <h6 style="margin-bottom: 16px; color: #374151;">Edit Period: <span id="editingPeriodInfo">Monday, Period 1</span></h6>
                                    <div class="form-row" style="margin-bottom: 15px;">
                                        <div class="form-group" style="margin-right: 15px;">
                                            <label for="periodClass" class="form-label">Class</label>
                                            <select id="periodClass" class="form-select" style="min-width: 150px;">
                                                <option value="">Select class...</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-right: 15px;">
                                            <label for="periodSection" class="form-label">Section</label>
                                            <select id="periodSection" class="form-select" style="min-width: 150px;">
                                                <option value="">Select section...</option>
                                            </select>
                                        </div>
                                        <div class="form-group" style="margin-right: 15px;">
                                            <label for="periodSubject" class="form-label">Subject</label>
                                            <select id="periodSubject" class="form-select" style="min-width: 200px;">
                                                <option value="">Select subject...</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group" style="margin-right: 15px;">
                                            <label for="periodNotes" class="form-label">Notes</label>
                                            <input type="text" id="periodNotes" class="form-input" placeholder="Optional notes" style="width: 300px;">
                                        </div>
                                        <div class="form-group" style="display: flex; align-items: end; gap: 10px;">
                                            <button class="btn btn-primary btn-sm" id="savePeriodChanges">
                                                <i class="fas fa-check"></i>
                                                Save
                                            </button>
                                            <button class="btn btn-outline btn-sm" id="clearPeriod">
                                                <i class="fas fa-trash"></i>
                                                Clear
                                            </button>
                                            <button class="btn btn-outline btn-sm" id="cancelPeriodEdit">
                                                <i class="fas fa-times"></i>
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <div class="quick-actions" style="margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
                                    <button class="btn btn-outline btn-sm" id="viewAvailableSlots">
                                        <i class="fas fa-clock"></i>
                                        View Available Slots
                                    </button>
                                    <button class="btn btn-outline btn-sm" id="bulkAssignPeriods">
                                        <i class="fas fa-layer-group"></i>
                                        Bulk Assign
                                    </button>
                                    <button class="btn btn-outline btn-sm" id="exportTeacherSchedule">
                                        <i class="fas fa-download"></i>
                                        Export Schedule
                                    </button>
                                    <button class="btn btn-outline btn-sm" id="resetScheduleChanges">
                                        <i class="fas fa-undo"></i>
                                        Reset Changes
                                    </button>
                                </div>                                <!-- Conflict Display -->
                                <div id="scheduleConflicts" style="display: none; margin-top: 20px;">
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-exclamation-triangle"></i> Schedule Conflicts Detected</h6>
                                        <ul id="conflictsList" style="margin-bottom: 0;">
                                            <!-- Conflicts will be listed here -->
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Teacher Schedule Placeholder -->
                            <div id="teacher-schedule-placeholder" style="text-align: center; padding: 40px; color: #6b7280;">
                                <p class="help-text">Select a teacher to view their schedule</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>    </div>
      <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>    <script>
        // Global variables
        const userRole = <?php echo json_encode($user_role); ?>;
        const sectionsData = <?php echo json_encode($sections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        let currentTab = null;
        
        function switchTab(tabId) {
            console.log(`Attempting to switch to tab: ${tabId}`);            const $targetContent = $('#' + tabId);
            const $targetButton = $('.tab-button[data-tab="' + tabId + '"]');

            if ($targetContent.length === 0) {
                console.error(`Tab content not found for ID: #${tabId}`);
                showNotification(`Error: Content for tab ${tabId} not found.`, 'error');
                // Do not proceed if content area doesn't exist
                // Attempt to switch to a default valid tab if the current one is problematic
                // This prevents getting stuck on a non-existent tab from localStorage
                if (localStorage.getItem('activeTeacherManagementTab') === tabId) {
                    localStorage.removeItem('activeTeacherManagementTab');
                    // Try to re-initialize with default if this was an attempt to load a bad stored tab
                    console.warn(`Invalid tab ${tabId} was stored. Clearing and attempting to re-initialize.`);
                    // Find a sensible default (first non-admin or first overall)
                    const defaultFallbackTab = $('<?php echo $user_role; ?>' === 'admin' ? '.tab-button' : '.tab-button:not(.admin-only)').first().data('tab');
                    if (defaultFallbackTab && defaultFallbackTab !== tabId) { // Avoid recursion if the default is also bad
                        return switchTab(defaultFallbackTab); // Recursive call, ensure base case
                    }
                }
                return false; 
            }
              // Role-based access check for tabs marked as 'admin-only'
            if ($targetButton.hasClass('admin-only') && userRole !== 'admin') {
                console.warn(`Access denied: Tab "${tabId}" is admin-only. Current role: "${userRole}".`);
                showNotification('Access denied to this section.', 'error');
                // Fallback to a default accessible tab
                const fallbackTab = $('<?php echo $user_role; ?>' === 'admin' ? '.tab-button' : '.tab-button:not(.admin-only)').first().data('tab');
                if (fallbackTab && fallbackTab !== tabId) { // Avoid recursion
                    return switchTab(fallbackTab);
                }
                return false; // Could not find a fallback
            }            // Hide all tab content and remove active class from buttons
            $('.tab-content').removeClass('active').hide();
            $('.tab-button').removeClass('active');
            
            // Show target content and add active classes
            $targetContent.addClass('active').show();
            $targetButton.addClass('active');

            // Store the successfully switched tabId
            try {
                localStorage.setItem('activeTeacherManagementTab', tabId);
            } catch (e) {
                console.warn("Could not save active tab to localStorage after switch:", e);
            }

            console.log(`Loading content for tab: ${tabId}`);
            try {
                switch (tabId) {
                    case 'add-teacher':
                        if (typeof generateEmployeeNumber === 'function') {
                            const addTeacherForm = $('#addTeacherForm');
                            if (addTeacherForm.length) addTeacherForm[0].reset();
                            generateEmployeeNumber();
                        }
                        break;
                    case 'manage-teachers':
                        if (typeof loadTeachers === 'function') loadTeachers();
                        break;
                    case 'class-assignments':
                        if (typeof loadClassAssignments === 'function') loadClassAssignments();
                        if (typeof loadTeachersForDropdowns === 'function') loadTeachersForDropdowns('assignTeacher', false); // For assign class teacher
                        if (typeof loadClassesForDropdowns === 'function') loadClassesForDropdowns('assignClass', false); // For assign class
                        // Initial call to updateSections if a class might be pre-selected or to set initial state
                        if (typeof updateSections === 'function') updateSections($('#assignClass').val(), 'assignSection');
                        break;
                    case 'subject-assignments':
                        if (typeof loadTeachersForDropdowns === 'function') {
                            loadTeachersForDropdowns('subjectTeacher', false); // For individual assignment
                            loadTeachersForDropdowns('filterTeacher'); // For 'All Assignments' filter
                        }
                        if (typeof loadSubjectsForDropdowns === 'function') {
                           // loadSubjectsForDropdowns('assignSubjectSubject'); // This was for a direct subject dropdown, now using checkboxes
                           loadSubjectsForDropdowns('filterSubject'); // For 'All Assignments' filter
                        }
                         // Populate checkboxes when tab is switched, not just on teacher selection
                        if (typeof populateSubjectCheckboxes === 'function') {
                            // Pass null or undefined if no teacher is initially selected, or the ID of a default/selected teacher
                            populateSubjectCheckboxes($('#subjectTeacher').val() || null);
                        }
                        if (typeof loadAssignmentFilters === 'function') loadAssignmentFilters(); // This might be redundant if dropdowns are loaded directly
                        if (typeof loadAllSubjectAssignments === 'function') loadAllSubjectAssignments();
                        break;
                    case 'timetable-management':
                        if (typeof loadAdminTimetableManagementData === 'function') loadAdminTimetableManagementData();
                        break;
                    case 'bulk-operations':
                        if (typeof loadBulkOperationsData === 'function') loadBulkOperationsData();
                        if (typeof loadStatisticsForBulk === 'function') loadStatisticsForBulk();
                        break;
                    default:
                        console.warn('Unknown tab ID in switchTab:', tabId);
                        return false; // Unknown tab
                }
                console.log(`Successfully processed logic for tab: ${tabId}`);
                return true; // Signal success
            } catch (error) {
                console.error(`Error loading content for tab ${tabId}:`, error);
                showNotification(`Error loading tab ${tabId}: ${error.message}`, 'error');
                return false; // Signal failure
            }
        }        // --- IMPLEMENTED FUNCTIONS ---
        
        /**
         * Load and display class teacher assignments
         */
        function loadClassAssignments() {
            console.log('Loading class assignments...');
            const container = $('#classAssignmentsTable');
            const loading = $('<div class="loading"><div class="spinner"></div><p>Loading assignments...</p></div>');
            
            container.html(loading);
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_class_assignments' },
                dataType: 'json',
                success: function(response) {
                    console.log('Class assignments loaded:', response);
                    
                    if (response.success && response.data) {
                        displayClassAssignments(response.data, container);
                    } else {
                        container.html('<p class="help-text">No class assignments found</p>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading class assignments:', error);
                    container.html('<p class="help-text text-danger">Error loading assignments</p>');
                    showNotification('Failed to load class assignments', 'error');
                }
            });
        }
        
        /**
         * Display class assignments in a table
         */
        function displayClassAssignments(assignments, container) {
            if (!assignments || assignments.length === 0) {
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
                            <th>Status</th>
                            <th class="admin-only">Actions</th>
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
                                <span class="teacher-name">${escapeHtml(assignment.teacher_name)}</span>
                            </div>
                        </td>
                        <td><code>${escapeHtml(assignment.employee_number)}</code></td>
                        <td><span class="status-badge status-${assignment.teacher_status}">${assignment.teacher_status}</span></td>
                        <td class="actions admin-only">
                            <button class="btn btn-sm btn-outline" onclick="reassignClassTeacher(${assignment.section_id})">
                                <i class="fas fa-exchange-alt"></i> Reassign
                            </button>
                            <button class="btn btn-sm btn-secondary" onclick="removeClassTeacher(${assignment.section_id})">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.html(html);
        }
        
        /**
         * Load admin timetable management data
         */
        function loadAdminTimetableManagementData() {
            console.log('Loading admin timetable management data...');
            
            // Load conflicts
            loadTimetableConflicts();
            
            // Load teacher schedules overview
            loadTeacherSchedulesOverview();
            
            // Load class timetable status
            loadClassTimetableStatus();
            
            // Load teacher dropdown for schedule editor
            loadTeachersForDropdowns('selectedTeacher', false);
        }
        
        /**
         * Load timetable conflicts
         */
        function loadTimetableConflicts() {
            const container = $('#adminTimetableConflicts');
            const loading = $('#adminTimetableConflictsLoading');
            
            container.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_timetable_conflicts' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    container.show();
                    
                    if (response.success && response.data && response.data.length > 0) {
                        displayTimetableConflicts(response.data, container);
                    } else {
                        container.html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> No timetable conflicts detected</div>');
                    }
                },
                error: function() {
                    loading.hide();
                    container.show().html('<div class="alert alert-error">Error loading conflicts</div>');
                }
            });
        }
        
        /**
         * Display timetable conflicts
         */
        function displayTimetableConflicts(conflicts, container) {
            let html = '<div class="alert alert-warning"><h6><i class="fas fa-exclamation-triangle"></i> Conflicts Detected</h6><ul>';
            
            conflicts.forEach(conflict => {
                html += `<li>${conflict.message} - ${conflict.details || ''}</li>`;
            });
            
            html += '</ul></div>';
            container.html(html);
        }
        
        /**
         * Load teacher schedules overview
         */
        function loadTeacherSchedulesOverview() {
            const container = $('#adminTeacherTimetableOverview');
            const loading = $('#adminTeacherTimetableOverviewLoading');
            
            container.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_teacher_schedules' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    container.show();
                    
                    if (response.success && response.data) {
                        displayTeacherSchedulesOverview(response.data, container);
                    } else {
                        container.html('<p class="help-text">No teacher schedules found</p>');
                    }
                },
                error: function() {
                    loading.hide();
                    container.show().html('<p class="help-text text-danger">Error loading teacher schedules</p>');
                }
            });
        }
        
        /**
         * Display teacher schedules overview
         */
        function displayTeacherSchedulesOverview(schedules, container) {
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Employee ID</th>
                            <th>Schedule Summary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            schedules.forEach(schedule => {
                const scheduleCount = schedule.schedule ? schedule.schedule.length : 0;
                html += `
                    <tr>
                        <td>${escapeHtml(schedule.teacher_name)}</td>
                        <td><code>${escapeHtml(schedule.employee_number)}</code></td>
                        <td>${scheduleCount} periods assigned</td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="viewTeacherSchedule(${schedule.teacher_id})">
                                <i class="fas fa-calendar-alt"></i> View Schedule
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.html(html);
        }
        
        /**
         * Load class timetable status
         */
        function loadClassTimetableStatus() {
            const container = $('#adminClassTimetableStatus');
            const loading = $('#adminClassTimetableStatusLoading');
            
            container.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_class_timetable_status' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    container.show();
                    
                    if (response.success && response.data) {
                        displayClassTimetableStatus(response.data, container);
                    } else {
                        container.html('<p class="help-text">No class timetable data found</p>');
                    }
                },
                error: function() {
                    loading.hide();
                    container.show().html('<p class="help-text text-danger">Error loading class timetable status</p>');
                }
            });
        }
        
        /**
         * Display class timetable status
         */
        function displayClassTimetableStatus(classes, container) {
            let html = `
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Section</th>
                            <th>Status</th>
                            <th>Completion</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            classes.forEach(cls => {
                const statusClass = cls.status === 'published' ? 'status-active' : 'status-inactive';
                const completionPercent = cls.completion_percentage || 0;
                
                html += `
                    <tr>
                        <td><strong>${escapeHtml(cls.class_name)}</strong></td>
                        <td><span class="section-badge">${escapeHtml(cls.section_name)}</span></td>
                        <td><span class="status-badge ${statusClass}">${cls.status || 'Draft'}</span></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; background: #f0f0f0; border-radius: 4px; height: 6px;">
                                    <div style="background: var(--primary-color); height: 100%; border-radius: 4px; width: ${completionPercent}%;"></div>
                                </div>
                                <span style="font-size: 0.875rem;">${completionPercent}%</span>
                            </div>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="editClassTimetable(${cls.class_id}, ${cls.section_id})">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.html(html);
        }
        
        /**
         * Load bulk operations data
         */
        function loadBulkOperationsData() {
            console.log('Loading bulk operations data...');
            loadStatisticsForBulk();
        }
        
        /**
         * Load statistics for bulk operations
         */
        function loadStatisticsForBulk() {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_statistics' },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        displayBulkStatistics(response.data);
                    }
                },
                error: function() {
                    console.error('Error loading bulk statistics');
                }
            });
        }
        
        /**
         * Display bulk statistics
         */
        function displayBulkStatistics(stats) {
            const container = $('#statisticsContent');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            console.log('Initializing form handlers...');
            
            // Add teacher form
            $('#addTeacherForm').on('submit', handleAddTeacher);
            $('#generateEmpNumber').on('click', generateEmployeeNumber);
            
            // Class assignment form
            $('#assignClass').on('change', function() {
                updateSections($(this).val(), 'assignSection');
            });
            $('#assignClassTeacher').on('click', handleClassTeacherAssignment);
            
            // Subject assignment form  
            $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
            $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);
            
            // Refresh buttons
            $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
            $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);
            
            // Teacher schedule editor
            $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
            $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);
            
            console.log('Form handlers initialized');
        }

        /**
         * Initialize search handlers
         */
        function initializeSearchHandlers() {
            console.log('Initializing search handlers...');
            
            // Teacher search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Assignment filters
            $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
            $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
            $('#clearAllFilters').on('click', clearAllAssignmentFilters);
            
            console.log('Search handlers initialized');
        }
        
        /**
         * Handle add teacher form submission
         */
        function handleAddTeacher(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_teacher',
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers(); // Refresh teacher list
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error adding teacher', 'error');
                }
            });
        }
        
        /**
         * Handle class teacher assignment
         */
        function handleClassTeacherAssignment() {
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            const teacherId = $('#assignTeacher').val();
            
            if (!classId || !sectionId || !teacherId) {
                showNotification('Please select class, section, and teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'assign_class_teacher',
                    section_id: sectionId,
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                        loadClassAssignments(); // Refresh assignments
                    } else {
                        showNotification(response.message || 'Failed to assign teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error assigning class teacher', 'error');
                }
            });
        }
        
        /**
         * Handle teacher selection for subject assignments
         */
        function handleTeacherSubjectSelection() {
            const teacherId = $(this).val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                return;
            }
            
            loadTeacherSubjects(teacherId);
        }
        
        /**
         * Load teacher subjects for assignment
         */
        function loadTeacherSubjects(teacherId) {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherSubjects(response.data, teacherId);
                    } else {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher subjects', 'error');
                }
            });
        }
        
        /**
         * Display teacher subjects with checkboxes
         */
        function displayTeacherSubjects(assignedSubjects, teacherId) {
            let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';
            
            // Get all subjects and mark assigned ones
            const allSubjects = <?php echo json_encode($subjects); ?>;
            
            allSubjects.forEach(subject => {
                const isAssigned = assignedSubjects.includes(subject.id);
                html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
            });
            
            html += '</div>';
            html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;
            
            $('#subjectAssignmentsDisplay').html(html);
            
            // Re-bind the update button
            $('#updateSubjectAssignments').off('click').on('click', function() {
                handleSubjectAssignmentUpdate(teacherId);
            });
        }
        
        /**
         * Handle subject assignment update
         */
        function handleSubjectAssignmentUpdate(teacherId) {
            const selectedSubjects = [];
            $('input[name="subjects[]"]:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadAllSubjectAssignments(); // Refresh all assignments view
                    } else {
                        showNotification(response.message || 'Failed to update assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating subject assignments', 'error');
                }
            });
        }
        
        /**
         * Clear subject selections
         */
        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }
        
        /**
         * Load all subject assignments
         */
        function loadAllSubjectAssignments() {
            const container = $('#allAssignmentsTable');
            const loading = $('#allAssignmentsLoading');
            const empty = $('#allAssignmentsEmpty');
            
            container.hide();
            empty.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_all_subject_assignments' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    
                    if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                        displayAllSubjectAssignments(response.data.assignments);
                        displayAssignmentStatistics(response.data.statistics);
                        container.show();
                    } else {
                        empty.show();
                    }
                },
                error: function() {
                    loading.hide();
                    container.show();
                    $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                }
            });
        }
        
        /**
         * Display all subject assignments
         */
        function displayAllSubjectAssignments(assignments) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            assignments.forEach(teacher => {
                const teacherInfo = teacher.teacher_info;
                const teacherAssignments = teacher.assignments;
                
                if (teacherAssignments.length === 0) {
                    // Show teacher with no assignments
                    tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                } else {
                    // Show each assignment
                    teacherAssignments.forEach((assignment, index) => {
                        tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        
        /**
         * Display assignment statistics
         */
        function displayAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            console.log('Initializing form handlers...');
            
            // Add teacher form
            $('#addTeacherForm').on('submit', handleAddTeacher);
            $('#generateEmpNumber').on('click', generateEmployeeNumber);
            
            // Class assignment form
            $('#assignClass').on('change', function() {
                updateSections($(this).val(), 'assignSection');
            });
            $('#assignClassTeacher').on('click', handleClassTeacherAssignment);
            
            // Subject assignment form  
            $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
            $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);
            
            // Refresh buttons
            $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
            $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);
            
            // Teacher schedule editor
            $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
            $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);
            
            console.log('Form handlers initialized');
        }

        /**
         * Initialize search handlers
         */
        function initializeSearchHandlers() {
            console.log('Initializing search handlers...');
            
            // Teacher search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Assignment filters
            $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
            $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
            $('#clearAllFilters').on('click', clearAllAssignmentFilters);
            
            console.log('Search handlers initialized');
        }
        
        /**
         * Handle add teacher form submission
         */
        function handleAddTeacher(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_teacher',
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers(); // Refresh teacher list
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error adding teacher', 'error');
                }
            });
        }
        
        /**
         * Handle class teacher assignment
         */
        function handleClassTeacherAssignment() {
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            const teacherId = $('#assignTeacher').val();
            
            if (!classId || !sectionId || !teacherId) {
                showNotification('Please select class, section, and teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'assign_class_teacher',
                    section_id: sectionId,
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                        loadClassAssignments(); // Refresh assignments
                    } else {
                        showNotification(response.message || 'Failed to assign teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error assigning class teacher', 'error');
                }
            });
        }
        
        /**
         * Handle teacher selection for subject assignments
         */
        function handleTeacherSubjectSelection() {
            const teacherId = $(this).val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                return;
            }
            
            loadTeacherSubjects(teacherId);
        }
        
        /**
         * Load teacher subjects for assignment
         */
        function loadTeacherSubjects(teacherId) {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherSubjects(response.data, teacherId);
                    } else {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher subjects', 'error');
                }
            });
        }
        
        /**
         * Display teacher subjects with checkboxes
         */
        function displayTeacherSubjects(assignedSubjects, teacherId) {
            let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';
            
            // Get all subjects and mark assigned ones
            const allSubjects = <?php echo json_encode($subjects); ?>;
            
            allSubjects.forEach(subject => {
                const isAssigned = assignedSubjects.includes(subject.id);
                html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
            });
            
            html += '</div>';
            html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;
            
            $('#subjectAssignmentsDisplay').html(html);
            
            // Re-bind the update button
            $('#updateSubjectAssignments').off('click').on('click', function() {
                handleSubjectAssignmentUpdate(teacherId);
            });
        }
        
        /**
         * Handle subject assignment update
         */
        function handleSubjectAssignmentUpdate(teacherId) {
            const selectedSubjects = [];
            $('input[name="subjects[]"]:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadAllSubjectAssignments(); // Refresh all assignments view
                    } else {
                        showNotification(response.message || 'Failed to update assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating subject assignments', 'error');
                }
            });
        }
        
        /**
         * Clear subject selections
         */
        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }
        
        /**
         * Load all subject assignments
         */
        function loadAllSubjectAssignments() {
            const container = $('#allAssignmentsTable');
            const loading = $('#allAssignmentsLoading');
            const empty = $('#allAssignmentsEmpty');
            
            container.hide();
            empty.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_all_subject_assignments' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    
                    if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                        displayAllSubjectAssignments(response.data.assignments);
                        displayAssignmentStatistics(response.data.statistics);
                        container.show();
                    } else {
                        empty.show();
                    }
                },
                error: function() {
                    loading.hide();
                    container.show();
                    $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                }
            });
        }
        
        /**
         * Display all subject assignments
         */
        function displayAllSubjectAssignments(assignments) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            assignments.forEach(teacher => {
                const teacherInfo = teacher.teacher_info;
                const teacherAssignments = teacher.assignments;
                
                if (teacherAssignments.length === 0) {
                    // Show teacher with no assignments
                    tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                } else {
                    // Show each assignment
                    teacherAssignments.forEach((assignment, index) => {
                        tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        
        /**
         * Display assignment statistics
         */
        function displayAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            console.log('Initializing form handlers...');
            
            // Add teacher form
            $('#addTeacherForm').on('submit', handleAddTeacher);
            $('#generateEmpNumber').on('click', generateEmployeeNumber);
            
            // Class assignment form
            $('#assignClass').on('change', function() {
                updateSections($(this).val(), 'assignSection');
            });
            $('#assignClassTeacher').on('click', handleClassTeacherAssignment);
            
            // Subject assignment form  
            $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
            $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);
            
            // Refresh buttons
            $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
            $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);
            
            // Teacher schedule editor
            $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
            $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);
            
            console.log('Form handlers initialized');
        }

        /**
         * Initialize search handlers
         */
        function initializeSearchHandlers() {
            console.log('Initializing search handlers...');
            
            // Teacher search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Assignment filters
            $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
            $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
            $('#clearAllFilters').on('click', clearAllAssignmentFilters);
            
            console.log('Search handlers initialized');
        }
        
        /**
         * Handle add teacher form submission
         */
        function handleAddTeacher(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_teacher',
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers(); // Refresh teacher list
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error adding teacher', 'error');
                }
            });
        }
        
        /**
         * Handle class teacher assignment
         */
        function handleClassTeacherAssignment() {
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            const teacherId = $('#assignTeacher').val();
            
            if (!classId || !sectionId || !teacherId) {
                showNotification('Please select class, section, and teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'assign_class_teacher',
                    section_id: sectionId,
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                        loadClassAssignments(); // Refresh assignments
                    } else {
                        showNotification(response.message || 'Failed to assign teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error assigning class teacher', 'error');
                }
            });
        }
        
        /**
         * Handle teacher selection for subject assignments
         */
        function handleTeacherSubjectSelection() {
            const teacherId = $(this).val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                return;
            }
            
            loadTeacherSubjects(teacherId);
        }
        
        /**
         * Load teacher subjects for assignment
         */
        function loadTeacherSubjects(teacherId) {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherSubjects(response.data, teacherId);
                    } else {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher subjects', 'error');
                }
            });
        }
        
        /**
         * Display teacher subjects with checkboxes
         */
        function displayTeacherSubjects(assignedSubjects, teacherId) {
            let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';
            
            // Get all subjects and mark assigned ones
            const allSubjects = <?php echo json_encode($subjects); ?>;
            
            allSubjects.forEach(subject => {
                const isAssigned = assignedSubjects.includes(subject.id);
                html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
            });
            
            html += '</div>';
            html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;
            
            $('#subjectAssignmentsDisplay').html(html);
            
            // Re-bind the update button
            $('#updateSubjectAssignments').off('click').on('click', function() {
                handleSubjectAssignmentUpdate(teacherId);
            });
        }
        
        /**
         * Handle subject assignment update
         */
        function handleSubjectAssignmentUpdate(teacherId) {
            const selectedSubjects = [];
            $('input[name="subjects[]"]:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadAllSubjectAssignments(); // Refresh all assignments view
                    } else {
                        showNotification(response.message || 'Failed to update assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating subject assignments', 'error');
                }
            });
        }
        
        /**
         * Clear subject selections
         */
        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }
        
        /**
         * Load all subject assignments
         */
        function loadAllSubjectAssignments() {
            const container = $('#allAssignmentsTable');
            const loading = $('#allAssignmentsLoading');
            const empty = $('#allAssignmentsEmpty');
            
            container.hide();
            empty.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_all_subject_assignments' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    
                    if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                        displayAllSubjectAssignments(response.data.assignments);
                        displayAssignmentStatistics(response.data.statistics);
                        container.show();
                    } else {
                        empty.show();
                    }
                },
                error: function() {
                    loading.hide();
                    container.show();
                    $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                }
            });
        }
        
        /**
         * Display all subject assignments
         */
        function displayAllSubjectAssignments(assignments) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            assignments.forEach(teacher => {
                const teacherInfo = teacher.teacher_info;
                const teacherAssignments = teacher.assignments;
                
                if (teacherAssignments.length === 0) {
                    // Show teacher with no assignments
                    tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                } else {
                    // Show each assignment
                    teacherAssignments.forEach((assignment, index) => {
                        tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        
        /**
         * Display assignment statistics
         */
        function displayAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            console.log('Initializing form handlers...');
            
            // Add teacher form
            $('#addTeacherForm').on('submit', handleAddTeacher);
            $('#generateEmpNumber').on('click', generateEmployeeNumber);
            
            // Class assignment form
            $('#assignClass').on('change', function() {
                updateSections($(this).val(), 'assignSection');
            });
            $('#assignClassTeacher').on('click', handleClassTeacherAssignment);
            
            // Subject assignment form  
            $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
            $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);
            
            // Refresh buttons
            $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
            $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);
            
            // Teacher schedule editor
            $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
            $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);
            
            console.log('Form handlers initialized');
        }

        /**
         * Initialize search handlers
         */
        function initializeSearchHandlers() {
            console.log('Initializing search handlers...');
            
            // Teacher search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Assignment filters
            $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
            $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
            $('#clearAllFilters').on('click', clearAllAssignmentFilters);
            
            console.log('Search handlers initialized');
        }
        
        /**
         * Handle add teacher form submission
         */
        function handleAddTeacher(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_teacher',
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers(); // Refresh teacher list
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error adding teacher', 'error');
                }
            });
        }
        
        /**
         * Handle class teacher assignment
         */
        function handleClassTeacherAssignment() {
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            const teacherId = $('#assignTeacher').val();
            
            if (!classId || !sectionId || !teacherId) {
                showNotification('Please select class, section, and teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'assign_class_teacher',
                    section_id: sectionId,
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                        loadClassAssignments(); // Refresh assignments
                    } else {
                        showNotification(response.message || 'Failed to assign teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error assigning class teacher', 'error');
                }
            });
        }
        
        /**
         * Handle teacher selection for subject assignments
         */
        function handleTeacherSubjectSelection() {
            const teacherId = $(this).val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                return;
            }
            
            loadTeacherSubjects(teacherId);
        }
        
        /**
         * Load teacher subjects for assignment
         */
        function loadTeacherSubjects(teacherId) {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherSubjects(response.data, teacherId);
                    } else {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher subjects', 'error');
                }
            });
        }
        
        /**
         * Display teacher subjects with checkboxes
         */
        function displayTeacherSubjects(assignedSubjects, teacherId) {
            let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';
            
            // Get all subjects and mark assigned ones
            const allSubjects = <?php echo json_encode($subjects); ?>;
            
            allSubjects.forEach(subject => {
                const isAssigned = assignedSubjects.includes(subject.id);
                html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
            });
            
            html += '</div>';
            html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;
            
            $('#subjectAssignmentsDisplay').html(html);
            
            // Re-bind the update button
            $('#updateSubjectAssignments').off('click').on('click', function() {
                handleSubjectAssignmentUpdate(teacherId);
            });
        }
        
        /**
         * Handle subject assignment update
         */
        function handleSubjectAssignmentUpdate(teacherId) {
            const selectedSubjects = [];
            $('input[name="subjects[]"]:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadAllSubjectAssignments(); // Refresh all assignments view
                    } else {
                        showNotification(response.message || 'Failed to update assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating subject assignments', 'error');
                }
            });
        }
        
        /**
         * Clear subject selections
         */
        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }
        
        /**
         * Load all subject assignments
         */
        function loadAllSubjectAssignments() {
            const container = $('#allAssignmentsTable');
            const loading = $('#allAssignmentsLoading');
            const empty = $('#allAssignmentsEmpty');
            
            container.hide();
            empty.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_all_subject_assignments' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    
                    if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                        displayAllSubjectAssignments(response.data.assignments);
                        displayAssignmentStatistics(response.data.statistics);
                        container.show();
                    } else {
                        empty.show();
                    }
                },
                error: function() {
                    loading.hide();
                    container.show();
                    $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                }
            });
        }
        
        /**
         * Display all subject assignments
         */
        function displayAllSubjectAssignments(assignments) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            assignments.forEach(teacher => {
                const teacherInfo = teacher.teacher_info;
                const teacherAssignments = teacher.assignments;
                
                if (teacherAssignments.length === 0) {
                    // Show teacher with no assignments
                    tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                } else {
                    // Show each assignment
                    teacherAssignments.forEach((assignment, index) => {
                        tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        
        /**
         * Display assignment statistics
         */
        function displayAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            console.log('Initializing form handlers...');
            
            // Add teacher form
            $('#addTeacherForm').on('submit', handleAddTeacher);
            $('#generateEmpNumber').on('click', generateEmployeeNumber);
            
            // Class assignment form
            $('#assignClass').on('change', function() {
                updateSections($(this).val(), 'assignSection');
            });
            $('#assignClassTeacher').on('click', handleClassTeacherAssignment);
            
            // Subject assignment form  
            $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
            $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);
            
            // Refresh buttons
            $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
            $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);
            
            // Teacher schedule editor
            $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
            $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);
            
            console.log('Form handlers initialized');
        }

        /**
         * Initialize search handlers
         */
        function initializeSearchHandlers() {
            console.log('Initializing search handlers...');
            
            // Teacher search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Assignment filters
            $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
            $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
            $('#clearAllFilters').on('click', clearAllAssignmentFilters);
            
            console.log('Search handlers initialized');
        }
        
        /**
         * Handle add teacher form submission
         */
        function handleAddTeacher(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_teacher',
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers(); // Refresh teacher list
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error adding teacher', 'error');
                }
            });
        }
        
        /**
         * Handle class teacher assignment
         */
        function handleClassTeacherAssignment() {
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            const teacherId = $('#assignTeacher').val();
            
            if (!classId || !sectionId || !teacherId) {
                showNotification('Please select class, section, and teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'assign_class_teacher',
                    section_id: sectionId,
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                        loadClassAssignments(); // Refresh assignments
                    } else {
                        showNotification(response.message || 'Failed to assign teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error assigning class teacher', 'error');
                }
            });
        }
        
        /**
         * Handle teacher selection for subject assignments
         */
        function handleTeacherSubjectSelection() {
            const teacherId = $(this).val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                return;
            }
            
            loadTeacherSubjects(teacherId);
        }
        
        /**
         * Load teacher subjects for assignment
         */
        function loadTeacherSubjects(teacherId) {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherSubjects(response.data, teacherId);
                    } else {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher subjects', 'error');
                }
            });
        }
        
        /**
         * Display teacher subjects with checkboxes
         */
        function displayTeacherSubjects(assignedSubjects, teacherId) {
            let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';
            
            // Get all subjects and mark assigned ones
            const allSubjects = <?php echo json_encode($subjects); ?>;
            
            allSubjects.forEach(subject => {
                const isAssigned = assignedSubjects.includes(subject.id);
                html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
            });
            
            html += '</div>';
            html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;
            
            $('#subjectAssignmentsDisplay').html(html);
            
            // Re-bind the update button
            $('#updateSubjectAssignments').off('click').on('click', function() {
                handleSubjectAssignmentUpdate(teacherId);
            });
        }
        
        /**
         * Handle subject assignment update
         */
        function handleSubjectAssignmentUpdate(teacherId) {
            const selectedSubjects = [];
            $('input[name="subjects[]"]:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadAllSubjectAssignments(); // Refresh all assignments view
                    } else {
                        showNotification(response.message || 'Failed to update assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating subject assignments', 'error');
                }
            });
        }
        
        /**
         * Clear subject selections
         */
        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }
        
        /**
         * Load all subject assignments
         */
        function loadAllSubjectAssignments() {
            const container = $('#allAssignmentsTable');
            const loading = $('#allAssignmentsLoading');
            const empty = $('#allAssignmentsEmpty');
            
            container.hide();
            empty.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_all_subject_assignments' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    
                    if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                        displayAllSubjectAssignments(response.data.assignments);
                        displayAssignmentStatistics(response.data.statistics);
                        container.show();
                    } else {
                        empty.show();
                    }
                },
                error: function() {
                    loading.hide();
                    container.show();
                    $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                }
            });
        }
        
        /**
         * Display all subject assignments
         */
        function displayAllSubjectAssignments(assignments) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            assignments.forEach(teacher => {
                const teacherInfo = teacher.teacher_info;
                const teacherAssignments = teacher.assignments;
                
                if (teacherAssignments.length === 0) {
                    // Show teacher with no assignments
                    tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                } else {
                    // Show each assignment
                    teacherAssignments.forEach((assignment, index) => {
                        tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        
        /**
         * Display assignment statistics
         */
        function displayAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Initialize form handlers
         */
        function initializeFormHandlers() {
            console.log('Initializing form handlers...');
            
            // Add teacher form
            $('#addTeacherForm').on('submit', handleAddTeacher);
            $('#generateEmpNumber').on('click', generateEmployeeNumber);
            
            // Class assignment form
            $('#assignClass').on('change', function() {
                updateSections($(this).val(), 'assignSection');
            });
            $('#assignClassTeacher').on('click', handleClassTeacherAssignment);
            
            // Subject assignment form  
            $('#subjectTeacher').on('change', handleTeacherSubjectSelection);
            $('#updateSubjectAssignments').on('click', handleSubjectAssignmentUpdate);
            
            // Refresh buttons
            $('#adminRefreshConflictsBtn').on('click', loadTimetableConflicts);
            $('#refreshAllAssignments').on('click', loadAllSubjectAssignments);
            
            // Teacher schedule editor
            $('#teacherScheduleEditorToggle').on('click', toggleScheduleEditor);
            $('#loadTeacherSchedule').on('click', loadSelectedTeacherSchedule);
            
            console.log('Form handlers initialized');
        }

        /**
         * Initialize search handlers
         */
        function initializeSearchHandlers() {
            console.log('Initializing search handlers...');
            
            // Teacher search
            $('#teacherSearch').on('input', debounce(searchTeachers, 300));
            
            // Assignment filters
            $('#filterTeacher, #filterSubject').on('change', filterAllAssignments);
            $('#assignmentSearchInput').on('input', debounce(searchAllAssignments, 300));
            $('#clearAllFilters').on('click', clearAllAssignmentFilters);
            
            console.log('Search handlers initialized');
        }
        
        /**
         * Handle add teacher form submission
         */
        function handleAddTeacher(e) {
            e.preventDefault();
            
            const formData = {
                action: 'add_teacher',
                full_name: $('#fullName').val(),
                email: $('#email').val(),
                phone: $('#phone').val(),
                password: $('#password').val()
            };
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Teacher added successfully!', 'success');
                        $('#addTeacherForm')[0].reset();
                        loadTeachers(); // Refresh teacher list
                    } else {
                        showNotification(response.message || 'Failed to add teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error adding teacher', 'error');
                }
            });
        }
        
        /**
         * Handle class teacher assignment
         */
        function handleClassTeacherAssignment() {
            const classId = $('#assignClass').val();
            const sectionId = $('#assignSection').val();
            const teacherId = $('#assignTeacher').val();
            
            if (!classId || !sectionId || !teacherId) {
                showNotification('Please select class, section, and teacher', 'error');
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'assign_class_teacher',
                    section_id: sectionId,
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Class teacher assigned successfully!', 'success');
                        $('#assignClass, #assignSection, #assignTeacher').val('');
                        $('#assignSection').prop('disabled', true);
                        loadClassAssignments(); // Refresh assignments
                    } else {
                        showNotification(response.message || 'Failed to assign teacher', 'error');
                    }
                },
                error: function() {
                    showNotification('Error assigning class teacher', 'error');
                }
            });
        }
        
        /**
         * Handle teacher selection for subject assignments
         */
        function handleTeacherSubjectSelection() {
            const teacherId = $(this).val();
            
            if (!teacherId) {
                $('#subjectAssignmentsDisplay').html('<p class="help-text">Select a teacher to view their subject assignments</p>');
                return;
            }
            
            loadTeacherSubjects(teacherId);
        }
        
        /**
         * Load teacher subjects for assignment
         */
        function loadTeacherSubjects(teacherId) {
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: {
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        displayTeacherSubjects(response.data, teacherId);
                    } else {
                        showNotification('Error loading teacher subjects', 'error');
                    }
                },
                error: function() {
                    showNotification('Error loading teacher subjects', 'error');
                }
            });
        }
        
        /**
         * Display teacher subjects with checkboxes
         */
        function displayTeacherSubjects(assignedSubjects, teacherId) {
            let html = '<div class="subject-checkboxes" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin: 16px 0;">';
            
            // Get all subjects and mark assigned ones
            const allSubjects = <?php echo json_encode($subjects); ?>;
            
            allSubjects.forEach(subject => {
                const isAssigned = assignedSubjects.includes(subject.id);
                html += `
                    <label style="display: flex; align-items: center; gap: 8px; padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px; cursor: pointer;">
                        <input type="checkbox" name="subjects[]" value="${subject.id}" ${isAssigned ? 'checked' : ''}>
                        <span>${escapeHtml(subject.name)} (${escapeHtml(subject.code)})</span>
                    </label>
                `;
            });
            
            html += '</div>';
            html += `
                <div class="actions" style="margin-top: 16px;">
                    <button class="btn btn-primary" id="updateSubjectAssignments">
                        <i class="fas fa-save"></i> Update Assignments
                    </button>
                    <button class="btn btn-outline" onclick="clearSubjectSelections()">
                        <i class="fas fa-times"></i> Clear All
                    </button>
                </div>
            `;
            
            $('#subjectAssignmentsDisplay').html(html);
            
            // Re-bind the update button
            $('#updateSubjectAssignments').off('click').on('click', function() {
                handleSubjectAssignmentUpdate(teacherId);
            });
        }
        
        /**
         * Handle subject assignment update
         */
        function handleSubjectAssignmentUpdate(teacherId) {
            const selectedSubjects = [];
            $('input[name="subjects[]"]:checked').each(function() {
                selectedSubjects.push($(this).val());
            });
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'POST',
                data: {
                    action: 'update_subject_assignments',
                    teacher_id: teacherId,
                    subject_ids: JSON.stringify(selectedSubjects)
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        showNotification('Subject assignments updated successfully!', 'success');
                        loadAllSubjectAssignments(); // Refresh all assignments view
                    } else {
                        showNotification(response.message || 'Failed to update assignments', 'error');
                    }
                },
                error: function() {
                    showNotification('Error updating subject assignments', 'error');
                }
            });
        }
        
        /**
         * Clear subject selections
         */
        function clearSubjectSelections() {
            $('input[name="subjects[]"]').prop('checked', false);
        }
        
        /**
         * Load all subject assignments
         */
        function loadAllSubjectAssignments() {
            const container = $('#allAssignmentsTable');
            const loading = $('#allAssignmentsLoading');
            const empty = $('#allAssignmentsEmpty');
            
            container.hide();
            empty.hide();
            loading.show();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_all_subject_assignments' },
                dataType: 'json',
                success: function(response) {
                    loading.hide();
                    
                    if (response.success && response.data && response.data.assignments && response.data.assignments.length > 0) {
                        displayAllSubjectAssignments(response.data.assignments);
                        displayAssignmentStatistics(response.data.statistics);
                        container.show();
                    } else {
                        empty.show();
                    }
                },
                error: function() {
                    loading.hide();
                    container.show();
                    $('#allAssignmentsTableBody').html('<tr><td colspan="7" class="text-center text-danger">Error loading assignments</td></tr>');
                }
            });
        }
        
        /**
         * Display all subject assignments
         */
        function displayAllSubjectAssignments(assignments) {
            const tbody = $('#allAssignmentsTableBody');
            tbody.empty();
            
            assignments.forEach(teacher => {
                const teacherInfo = teacher.teacher_info;
                const teacherAssignments = teacher.assignments;
                
                if (teacherAssignments.length === 0) {
                    // Show teacher with no assignments
                    tbody.append(`
                        <tr>
                            <td><code>${escapeHtml(teacherInfo.employee_number)}</code></td>
                            <td>${escapeHtml(teacherInfo.name)}</td>
                            <td colspan="3"><em>No subject assignments</em></td>
                            <td><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>
                            <td>-</td>
                        </tr>
                    `);
                } else {
                    // Show each assignment
                    teacherAssignments.forEach((assignment, index) => {
                        tbody.append(`
                            <tr>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><code>${escapeHtml(teacherInfo.employee_number)}</code></td>` : ''}
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}">${escapeHtml(teacherInfo.name)}</td>` : ''}
                                <td>${escapeHtml(assignment.subject_name)}</td>
                                <td><code>${escapeHtml(assignment.subject_code)}</code></td>
                                <td><span class="assignment-scope-badge ${assignment.assignment_scope}">${assignment.assignment_scope}</span></td>
                                ${index === 0 ? `<td rowspan="${teacherAssignments.length}"><span class="status-badge status-${teacherInfo.status}">${teacherInfo.status}</span></td>` : ''}
                                <td>
                                    <button class="btn btn-sm btn-outline" onclick="removeSubjectAssignment(${assignment.assignment_id})">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    });
                }
            });
        }
        
        /**
         * Display assignment statistics
         */
        function displayAssignmentStatistics(stats) {
            const container = $('#assignmentStatistics');
            const html = `
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px;">
                    <div class="stat-item">
                        <div class="stat-label">Total Teachers</div>
                        <div class="stat-value">${stats.total_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Active Teachers</div>
                        <div class="stat-value">${stats.active_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Assigned Teachers</div>
                        <div class="stat-value">${stats.assigned_teachers}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label">Unassigned Teachers</div>
                        <div class="stat-value">${stats.unassigned_teachers}</div>
                    </div>
                </div>
            `;
            container.html(html);
        }

        /**
         * Filter all assignments based on teacher and subject filters
         */
        function filterAllAssignments() {
            const teacherFilter = $('#filterTeacher').val();
            const subjectFilter = $('#filterSubject').val();
            
            console.log('Filtering assignments:', { teacherFilter, subjectFilter });
            
            // Reload assignments with filters
            loadAllSubjectAssignments();
        }
        
        /**
         * Search all assignments based on search input
         */
        function searchAllAssignments() {
            const searchTerm = $('#allAssignmentsSearch').val().toLowerCase();
            console.log('Searching assignments for:', searchTerm);
            
            // Filter visible rows based on search term
            $('#allAssignmentsTableBody tr').each(function() {
                const row = $(this);
                const text = row.text().toLowerCase();
                
                if (text.includes(searchTerm)) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        }
        
        /**
         * Clear all assignment filters
         */
        function clearAllAssignmentFilters() {
            $('#filterTeacher').val('');
            $('#filterSubject').val('');
            $('#allAssignmentsSearch').val('');
            
            // Show all rows
            $('#allAssignmentsTableBody tr').show();
            
            // Reload data
            loadAllSubjectAssignments();
        }

        // ...existing code...
        
        // --- TAB SWITCHING INITIALIZATION ---
        function initializeTabSwitching() {
            console.log('Initializing tab switching...');
            
            // Attach click handlers to tab buttons
            $('.tab-button').on('click', function(e) {
                e.preventDefault();
                const tabId = $(this).data('tab');
                console.log('Tab button clicked:', tabId);
                if (tabId) {
                    switchTab(tabId);
                } else {
                    console.error('No data-tab attribute found on button');
                }
            });

            // Initialize with default tab or from localStorage
            let initialTab = null;
            
            try {
                initialTab = localStorage.getItem('activeTeacherManagementTab');
            } catch(e) {
                console.warn('Could not read from localStorage:', e);
            }
            
            // Validate that the stored tab exists and is accessible
            if (initialTab) {
                const tabExists = $(`#${initialTab}-content`).length > 0;
                const buttonExists = $(`.tab-button[data-tab="${initialTab}"]`).length > 0;
                if (!tabExists || !buttonExists) {
                    console.warn(`Stored tab ${initialTab} does not exist, using default`);
                    initialTab = null;
                }
            }
            
            // If no valid stored tab, use the first available tab
            if (!initialTab) {
                const firstTab = $('.tab-button').first();
                if (firstTab.length > 0) {
                    initialTab = firstTab.data('tab');
                    console.log('Using first available tab:', initialTab);
                } else {
                    console.error('No tab buttons found');
                    return;
                }
            }
            
            // Activate the initial tab
            if (initialTab) {
                console.log('Activating initial tab:', initialTab);
                switchTab(initialTab);
            }
        }        // --- MISSING FUNCTIONS ---
        
        /**
         * Load teachers from API and display them
         */
        function loadTeachers() {
            console.log('Loading teachers...');
            const container = $('#teachersTableBody');
            const loading = $('#teachersLoading');
            
            // Show loading state
            loading.show();
            container.parent().hide();
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { action: 'get_teachers' },
                dataType: 'json',
                success: function(response) {
                    console.log('Teachers API response:', response);
                    loading.hide();
                    container.parent().show();
                    
                    if (response.success && response.data) {
                        displayTeachers(response.data);
                    } else {
                        container.html('<tr><td colspan="6" class="text-center">No teachers found</td></tr>');
                        console.warn('No teachers data in response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading teachers:', error);
                    loading.hide();
                    container.parent().show();
                    container.html('<tr><td colspan="6" class="text-center text-danger">Error loading teachers</td></tr>');
                    showNotification('Failed to load teachers', 'error');
                }
            });
        }
        
        /**
         * Display teachers in the table
         */
        function displayTeachers(teachers) {
            console.log('Displaying', teachers.length, 'teachers');
            const tbody = $('#teachersTableBody');
            tbody.empty();
            
            if (!teachers || teachers.length === 0) {
                tbody.html('<tr><td colspan="6" class="text-center">No teachers found</td></tr>');
                return;
            }
            
            teachers.forEach(teacher => {
                const row = `
                    <tr>
                        <td><code>${escapeHtml(teacher.employee_number || 'N/A')}</code></td>
                        <td>
                            <div class="teacher-info-cell">
                                <div class="teacher-name">${escapeHtml(teacher.full_name || 'N/A')}</div>
                                <div class="teacher-email">${escapeHtml(teacher.email || 'N/A')}</div>
                            </div>
                        </td>
                        <td>${escapeHtml(teacher.email || 'N/A')}</td>
                        <td>${escapeHtml(teacher.role || 'teacher')}</td>
                        <td><span class="status-badge status-${teacher.status || 'active'}">${teacher.status || 'active'}</span></td>
                        <td class="actions">
                            <button class="btn btn-outline btn-sm" onclick="editTeacher(${teacher.id})" title="Edit Teacher">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="viewTeacherDetails(${teacher.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `;
                tbody.append(row);
            });
            
            console.log('Teachers displayed successfully');
        }
        
        /**
         * Generate employee number
         */
        function generateEmployeeNumber() {
            const currentYear = new Date().getFullYear();
            const employeeInput = $('#employeeNumber');
            
            if (employeeInput.length === 0) {
                console.warn('Employee number input field not found');
                return;
            }
            
            // Simple client-side generation - this will be validated/corrected on server-side
            const randomNum = Math.floor(Math.random() * 900) + 100; // 3-digit random number
            const generated = `VES${currentYear}T${randomNum}`;
            
            employeeInput.val(generated);
            console.log('Generated employee number:', generated);
        }
        
        /**
         * Update sections dropdown based on selected class
         */
        function updateSections(classId, targetSectionId) {
            console.log('Updating sections for class:', classId);
            const sectionSelect = $('#' + targetSectionId);
            
            // Reset section dropdown
            sectionSelect.empty().append('<option value="">Select Section</option>');
            
            if (!classId) {
                sectionSelect.prop('disabled', true);
                return;
            }
            
            // Filter sections by class ID
            const classSections = sectionsData.filter(section => section.class_id == classId);
            
            if (classSections.length > 0) {
                classSections.forEach(section => {
                    sectionSelect.append(`<option value="${section.id}">${escapeHtml(section.name)}</option>`);
                });
                sectionSelect.prop('disabled', false);
                console.log('Found', classSections.length, 'sections for class', classId);
            } else {
                sectionSelect.append('<option value="">No sections found</option>');
                sectionSelect.prop('disabled', true);
                console.log('No sections found for class', classId);
            }
        }
        
        /**
         * Load teachers for dropdown population
         */
        function loadTeachersForDropdowns(selectId, includeEmpty = true) {
            console.log('Loading teachers for dropdown:', selectId);
            const select = $('#' + selectId);
            
            if (select.length === 0) {
                console.warn('Dropdown not found:', selectId);
                return;
            }
            
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET', 
                data: { action: 'get_teachers' },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        select.empty();
                        if (includeEmpty) {
                            select.append('<option value="">Select Teacher</option>');
                        }
                        
                        response.data.forEach(teacher => {
                            select.append(`<option value="${teacher.id}">${escapeHtml(teacher.full_name)} (${escapeHtml(teacher.employee_number)})</option>`);
                        });
                        
                        console.log('Teachers loaded for dropdown:', selectId);
                    }
                },
                error: function() {
                    console.error('Failed to load teachers for dropdown:', selectId);
                }
            });
        }
        
        /**
         * Load classes for dropdown population  
         */
        function loadClassesForDropdowns(selectId, includeEmpty = true) {
            console.log('Loading classes for dropdown:', selectId);
            const select = $('#' + selectId);
            
            if (select.length === 0) {
                console.warn('Dropdown not found:', selectId);
                return;
            }
            
            // Classes are already available in the PHP-generated dropdown
            console.log('Classes already populated in dropdown:', selectId);
        }
        
        /**
         * Load subjects for dropdown population
         */
        function loadSubjectsForDropdowns(selectId, includeEmpty = true) {
            console.log('Loading subjects for dropdown:', selectId);
            const select = $('#' + selectId);
            
            if (select.length === 0) {
                console.warn('Dropdown not found:', selectId);
                return;
            }
            
            // Subjects are already available in the PHP-generated dropdown
            console.log('Subjects already populated in dropdown:', selectId);
        }
        
        /**
         * Populate subject checkboxes based on teacher selection
         */
        function populateSubjectCheckboxes(teacherId) {
            console.log('Populating subject checkboxes for teacher:', teacherId);
            
            if (!teacherId) {
                $('input[name="subjects[]"]').prop('checked', false);
                return;
            }
            
            // Load teacher's current subject assignments
            $.ajax({
                url: 'teacher_management_api.php',
                type: 'GET',
                data: { 
                    action: 'get_teacher_subjects',
                    teacher_id: teacherId 
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        // Uncheck all first
                        $('input[name="subjects[]"]').prop('checked', false);
                        
                        // Check assigned subjects
                        response.data.forEach(subjectId => {
                            $(`input[name="subjects[]"][value="${subjectId}"]`).prop('checked', true);
                        });
                        
                        console.log('Subject checkboxes updated for teacher:', teacherId);
                    }
                },
                error: function() {
                    console.error('Failed to load teacher subjects for checkboxes');
                }
            });
        }
        
        /**
         * Load assignment filters
         */
        function loadAssignmentFilters() {
            console.log('Loading assignment filters...');
            // This is handled by the loadTeachersForDropdowns and loadSubjectsForDropdowns functions
        }
        
        /**
         * Search teachers
         */
        function searchTeachers() {
            const query = $('#teacherSearch').val().toLowerCase();
            console.log('Searching teachers with query:', query);
            
            $('#teachersTableBody tr').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes(query)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        /**
         * Debounce function for search
         */
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
        
        /**
         * Schedule editor functions (placeholders)
         */
        function toggleScheduleEditor() {
            const editor = $('#teacherScheduleEditor');
            if (editor.is(':visible')) {
                editor.hide();
                $('#teacherScheduleEditorToggle').html('<i class="fas fa-calendar-plus"></i> Open Schedule Editor');
            } else {
                editor.show();
                $('#teacherScheduleEditorToggle').html('<i class="fas fa-calendar-minus"></i> Close Schedule Editor');
            }
        }
        
        function loadSelectedTeacherSchedule() {
            const teacherId = $('#selectedTeacher').val();
            if (!teacherId) {
                showNotification('Please select a teacher first', 'error');
                return;
            }
            
            console.log('Loading schedule for teacher:', teacherId);
            showNotification('Schedule loading functionality will be implemented', 'info');
        }
        
        /**
         * Utility functions
         */
        function escapeHtml(text) {
            if (typeof text !== 'string') {
                return text;
            }
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }
        
        function showNotification(message, type = 'info') {
            console.log(`Notification [${type}]:`, message);
            
            const notification = $('#notification');
            if (notification.length === 0) {
                // Create notification element if it doesn't exist
                $('body').append(`<div id="notification" class="notification"></div>`);
            }
            
            const notificationEl = $('#notification');
            notificationEl
                .removeClass('success error warning info')
                .addClass(type)
                .text(message)
                .fadeIn();
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                notificationEl.fadeOut();
            }, 3000);
        }
        
        /**
         * Placeholder functions for teacher actions
         */
        function editTeacher(teacherId) {
            console.log('Edit teacher:', teacherId);
            showNotification('Teacher editing functionality will be implemented', 'info');
        }
        
        function viewTeacherDetails(teacherId) {
            console.log('View teacher details:', teacherId);
            showNotification('Teacher details view will be implemented', 'info');
        }
        
        function viewTeacherAssignments(teacherId) {
            console.log('View teacher assignments:', teacherId);
            showNotification('Teacher assignments view will be implemented', 'info');
        }
        
        function reassignClassTeacher(sectionId) {
            console.log('Reassign class teacher for section:', sectionId);
            showNotification('Use the Class Teacher Assignment tab to reassign teachers', 'info');
        }
        
        function removeClassTeacher(sectionId) {
            console.log('Remove class teacher for section:', sectionId);
            if (confirm('Are you sure you want to remove the class teacher for this section?')) {
                showNotification('Class teacher removal functionality will be implemented', 'info');
            }
        }
        
        function removeSubjectAssignment(assignmentId) {
            console.log('Remove subject assignment:', assignmentId);
            if (confirm('Are you sure you want to remove this subject assignment?')) {
                showNotification('Subject assignment removal functionality will be implemented', 'info');
            }
        }
        
        function viewTeacherSchedule(teacherId) {
            console.log('View teacher schedule:', teacherId);
            showNotification('Teacher schedule view will be implemented', 'info');
        }
        
        function editClassTimetable(classId, sectionId) {
            console.log('Edit class timetable:', classId, sectionId);
            showNotification('Class timetable editing will be implemented', 'info');
        }

        // --- DOCUMENT READY ---
        $(document).ready(function() {
            console.log('Document ready - initializing teacher management interface...');
            
            // Initialize tab switching
            initializeTabSwitching();
            
            // Initialize form handlers (when implemented)
            if (typeof initializeFormHandlers === 'function') {
                initializeFormHandlers();
            }
            
            // Initialize search handlers (when implemented)
            if (typeof initializeSearchHandlers === 'function') {
                initializeSearchHandlers();
            }
            
            console.log('Teacher management interface initialization complete.');
        });

    </script>
</body>
</html>