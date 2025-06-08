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

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10; // Records per page
$offset = ($page - 1) * $limit;

// Search and filter parameters
$search = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$role_filter = isset($_GET['role']) ? sanitizeInput($_GET['role']) : '';
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';

// Base query
$query = "SELECT u.id, u.email, u.full_name, u.role, u.status, u.last_login, 
          CASE 
            WHEN u.role = 'teacher' THEN (SELECT employee_number FROM teachers WHERE user_id = u.id)
            WHEN u.role = 'student' THEN (SELECT admission_number FROM students WHERE user_id = u.id)
            ELSE NULL
          END AS identifier
          FROM users u";

// Build where clause
$where_clauses = [];
$params = [];
$types = "";

if (!empty($search)) {
    $where_clauses[] = "(u.email LIKE ? OR u.full_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($role_filter)) {
    $where_clauses[] = "u.role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $where_clauses[] = "u.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

// Combine where clauses if there are any
if (!empty($where_clauses)) {
    $query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Add order and pagination
$query .= " ORDER BY u.id DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Execute query
$users = executeQuery($query, $types, $params);

// Count total users (for pagination)
$count_query = "SELECT COUNT(*) AS total FROM users u";
if (!empty($where_clauses)) {
    $count_query .= " WHERE " . implode(" AND ", $where_clauses);
}

// Remove limit and offset from params for count query
array_pop($params); // Remove offset
array_pop($params); // Remove limit

// Get total count
$count_result = executeQuery($count_query, substr($types, 0, -2), $params);
$total_users = $count_result[0]['total'];
$total_pages = ceil($total_users / $limit);

// Generate CSRF token for forms
$csrf_token = generateCSRFToken();
include 'sidebar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - School ERP</title>
    
    <!-- Include CSS files -->
    <link rel="stylesheet" href="css/users.css">
    <link rel="stylesheet" href="css/sidebar.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
 <style>
        /* Main Layout Styles */
        body {
            margin: 0;
            padding: 0;
            background: #f8fafc;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .main-content {
            flex: 1;
            overflow-y: auto;
            position: relative;
        }

        /* Header Styles */
        .header {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 24px;
            height: 18px;
            cursor: pointer;
        }

        .bar {
            width: 100%;
            height: 2px;
            background-color: #333;
            border-radius: 1px;
        }

        /* User Dropdown Styles */
        .user-dropdown {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
            color: #333;
            padding: 8px 12px;
            border-radius: 4px;
        }

        .user-dropdown-toggle:hover {
            background-color: #f2f2f2;
        }

        .user-dropdown-menu {
            position: absolute;
            right: 0;
            top: 100%;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 150px;
            display: none;
            z-index: 20;
        }

        .user-dropdown-menu.active {
            display: block;
        }

        .user-dropdown-menu a {
            display: block;
            padding: 10px 16px;
            color: #333;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .user-dropdown-menu a:hover {
            background-color: #f2f2f2;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: #2563eb;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .card-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }

        .card-body {
            padding: 20px 0;
        }

        /* Content Styles */
        .content {
            padding: 20px 0;
            background: #f5f5f5;
            min-height: 100vh;
        }

        /* Button Styles */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        /* Form Control Styles */
        .form-control {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            font-size: 0.95rem;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* User Management Enhanced Styles */
        .add-button {
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-button:hover {
            background: #059669;
            color: white;
        }

        /* Filter bar */
        .filter-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .search-box {
            flex: 1;
            min-width: 200px;
        }

        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        .search-box input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        .filter-select {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            min-width: 120px;
        }

        .filter-select:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }

        /* Table container with horizontal scroll */
        .table-container {
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            width: 100%;
            max-width: 100%;
            position: relative;
        }

        .user-table {
            width: 100%;
            min-width: 800px; /* Ensures horizontal scroll on small screens */
            border-collapse: collapse;
            background: white;
        }

        .user-table th {
            background: #f9fafb;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .user-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #6b7280;
            white-space: nowrap;
        }

        .user-table tbody tr:hover {
            background: #f9fafb;
        }

        /* Status badges */
        .status-active {
            color: #10b981;
            font-weight: 500;
        }

        .status-inactive {
            color: #ef4444;
            font-weight: 500;
        }

        /* Action buttons */
        .user-actions {
            display: flex;
            gap: 8px;
        }

        .user-actions a {
            padding: 6px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            color: #6b7280;
        }

        .user-actions a:hover {
            background: #f3f4f6;
        }

        .user-actions a[title="Edit"] {
            color: #2563eb;
        }

        .user-actions a[title="Deactivate"] {
            color: #ef4444;
        }

        .user-actions a[title="Activate"] {
            color: #10b981;
        }

        .user-actions a[title="Reset Password"] {
            color: #f59e0b;
        }

        /* Pagination - Single row layout */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            gap: 5px;
            flex-wrap: nowrap;
            overflow-x: auto;
            padding: 10px 0;
        }

        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            background: white;
            color: #374151;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            min-width: 40px;
            text-align: center;
        }

        .pagination a:hover {
            background: #f3f4f6;
        }

        .pagination .active {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }

        .pagination .disabled {
            color: #9ca3af;
            cursor: not-allowed;
        }

        .pagination .disabled:hover {
            background: white;
        }

        /* Empty state */
        .text-center {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        /* Form Styles for User Management */
        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-size: 0.95rem;
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }

        .form-input, 
        .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 0.95rem;
            color: #1a1a1a;
            background: white;
            transition: all 0.3s ease;
        }

        .form-input:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        .required::after {
            content: "*";
            color: #ef4444;
            margin-left: 4px;
        }

        .help-text {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.375rem;
        }

        /* Password Input Wrapper */
        .password-input-wrapper {
            position: relative;
            width: 100%;
        }

        .password-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            z-index: 5;
        }

        .password-toggle:hover {
            color: #4b5563;
        }

        .password-toggle-icon {
            width: 20px;
            height: 20px;
        }

        /* Checkbox Styles */
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .checkbox-input {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            accent-color: #667eea;
        }

        .checkbox-label {
            font-size: 0.95rem;
            color: #4b5563;
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background-color: #ffffff;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 20;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar.collapsed {
            transform: translateX(-100%);
        }

        /* Two-Factor Section */
        .two-factor-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .two-factor-content {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .two-factor-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1a1a1a;
            margin: 0;
        }

        .two-factor-desc {
            color: #6b7280;
            font-size: 0.95rem;
            margin: 0;
        }

        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }

        .toggle-input {
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
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .toggle-input:checked + .toggle-slider {
            background-color: #667eea;
        }

        .toggle-input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }

        /* File Input Styles */
        .file-input-wrapper {
            display: flex;
            flex-direction: column;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: #f9fafb;
            border: 1px dashed #d1d5db;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input-label:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }

        .file-input {
            display: none;
        }

        .file-input-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .file-input-icon {
            width: 36px;
            height: 36px;
            color: #6b7280;
        }

        /* Profile Card Styles */
        .profile-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            height: fit-content;
        }

        .profile-avatar {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
        }

        .avatar-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 1rem;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-placeholder {
            font-size: 48px;
            font-weight: 600;
            color: #6b7280;
        }

        .avatar-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            cursor: pointer;
        }

        .avatar-container:hover .avatar-overlay {
            opacity: 1;
        }

        .avatar-edit-icon {
            width: 30px;
            height: 30px;
            color: white;
        }

        /* Scrollbar styling */
        .table-container::-webkit-scrollbar {
            height: 8px;
        }

        .table-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .menu-toggle {
                display: flex;
            }

            .content {
                padding: 15px 0;
            }

            .card-header {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
            }

            .card-header h2 {
                margin-bottom: 10px;
            }

            .add-button {
                width: 100%;
                justify-content: center;
            }

            .filter-bar {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                min-width: auto;
                margin-bottom: 10px;
            }

            .filter-select {
                min-width: auto;
                margin-bottom: 10px;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .card-body {
                padding: 15px 10px;
            }

            /* Table keeps horizontal scroll */
            .table-container {
                margin: 0 -10px; /* Extend to edges on mobile */
                border-left: none;
                border-right: none;
                border-radius: 0;
            }

            .user-table {
                min-width: 700px; /* Smaller min-width for mobile */
            }

            .user-table th,
            .user-table td {
                padding: 10px 12px;
                font-size: 13px;
            }

            /* Pagination adjustments */
            .pagination {
                gap: 3px;
            }

            .pagination a, .pagination span {
                padding: 6px 10px;
                font-size: 13px;
                min-width: 35px;
            }

            /* Hide some pagination items on very small screens */
            .pagination a:not(.active):not(:first-child):not(:last-child):not(:nth-child(2)):not(:nth-last-child(2)) {
                display: none;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .content {
                padding: 10px 0;
            }

            .card-header {
                padding: 15px;
            }

            .card-body {
                padding: 15px 10px;
            }

            .filter-bar {
                padding: 10px;
            }

            .user-table {
                min-width: 600px;
            }

            .user-table th,
            .user-table td {
                padding: 8px 10px;
                font-size: 12px;
            }

            .user-actions a {
                padding: 4px 6px;
                font-size: 12px;
            }

            .pagination a, .pagination span {
                padding: 5px 8px;
                font-size: 12px;
                min-width: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        
        <div class="main-content">
            <div class="header">
                <div class="menu-toggle">
                    <div class="bar"></div>
                    <div class="bar"></div>
                    <div class="bar"></div>
                </div>
                <h1>User Management</h1>
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
                        <h2>Manage Users</h2>
                        <a href="user-add.php" class="add-button"><i class="fas fa-plus"></i> Add User</a>
                    </div>
                    <div class="card-body">
                        <!-- Filter and Search -->
                        <form method="GET" action="" class="filter-bar">
                            <div class="search-box">
                                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                                       placeholder="Search by name or email" class="form-control">
                            </div>
                            
                            <select name="role" class="form-control filter-select">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="teacher" <?php echo $role_filter === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                <option value="student" <?php echo $role_filter === 'student' ? 'selected' : ''; ?>>Student</option>
                                <option value="parent" <?php echo $role_filter === 'parent' ? 'selected' : ''; ?>>Parent</option>
                            </select>
                            
                            <select name="status" class="form-control filter-select">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                            <a href="users.php" class="btn btn-secondary">Reset</a>
                        </form>
                        
                        <!-- User Table -->
                        <table class="user-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Identifier</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No users found</td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['id']); ?></td>
                                        <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                                        <td><?php echo htmlspecialchars($user['identifier'] ?? 'N/A'); ?></td>
                                        <td class="status-<?php echo htmlspecialchars($user['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                        </td>
                                        <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                        <td class="user-actions">
                                            <a href="user-edit.php?id=<?php echo $user['id']; ?>" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($user['status'] === 'active'): ?>
                                            <a href="#" class="deactivate-user" data-id="<?php echo $user['id']; ?>" 
                                               data-name="<?php echo htmlspecialchars($user['full_name']); ?>" title="Deactivate">
                                                <i class="fas fa-user-slash"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="#" class="activate-user" data-id="<?php echo $user['id']; ?>" 
                                               data-name="<?php echo htmlspecialchars($user['full_name']); ?>" title="Activate">
                                                <i class="fas fa-user-check"></i>
                                            </a>
                                            <?php endif; ?>
                                            <a href="#" class="reset-password" data-id="<?php echo $user['id']; ?>" 
                                               data-name="<?php echo htmlspecialchars($user['full_name']); ?>" title="Reset Password">
                                                <i class="fas fa-key"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                            <a href="?page=1&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">&laquo; First</a>
                            <a href="?page=<?php echo $page - 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">&lsaquo; Prev</a>
                            <?php else: ?>
                            <span class="disabled">&laquo; First</span>
                            <span class="disabled">&lsaquo; Prev</span>
                            <?php endif; ?>
                            
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <?php if ($i == $page): ?>
                                <span class="active"><?php echo $i; ?></span>
                                <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Next &rsaquo;</a>
                            <a href="?page=<?php echo $total_pages; ?>&limit=<?php echo $limit; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Last &raquo;</a>
                            <?php else: ?>
                            <span class="disabled">Next &rsaquo;</span>
                            <span class="disabled">Last &raquo;</span>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Hidden forms for status changes and password reset -->
    <form id="deactivateForm" method="POST" action="user_actions.php" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="deactivate">
        <input type="hidden" name="user_id" id="deactivateUserId">
    </form>
    
    <form id="activateForm" method="POST" action="user_actions.php" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="activate">
        <input type="hidden" name="user_id" id="activateUserId">
    </form>
    
    <form id="resetPasswordForm" method="POST" action="user_actions.php" style="display: none;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <input type="hidden" name="action" value="reset_password">
        <input type="hidden" name="user_id" id="resetPasswordUserId">
    </form>
    
    <!-- Include JavaScript files -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
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
        
        // Handle deactivate user
        $('.deactivate-user').click(function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            const userName = $(this).data('name');
            
            if (confirm(`Are you sure you want to deactivate user "${userName}"?`)) {
                $('#deactivateUserId').val(userId);
                $('#deactivateForm').submit();
            }
        });
        
        // Handle activate user
        $('.activate-user').click(function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            const userName = $(this).data('name');
            
            if (confirm(`Are you sure you want to activate user "${userName}"?`)) {
                $('#activateUserId').val(userId);
                $('#activateForm').submit();
            }
        });
        
        // Handle password reset
        $('.reset-password').click(function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            const userName = $(this).data('name');
            
            if (confirm(`Are you sure you want to reset the password for "${userName}"?`)) {
                $('#resetPasswordUserId').val(userId);
                $('#resetPasswordForm').submit();
            }
        });
    });
    </script>
</body>
</html> 