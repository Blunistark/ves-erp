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
html {
    scroll-behavior: smooth;
}

body {
    margin: 0;
    padding: 0;
    background: #f8fafc;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.dashboard-container {
    min-height: 100vh;
}

.main-content {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
    min-height: 100vh;
    overflow-y: auto;
    max-height: 100vh;
    padding: 0;
}

/* Sidebar Overlay */
.sidebar-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 40;
    display: none;
}

/* Mobile Hamburger Button */
.hamburger-btn {
    position: fixed;
    top: 1rem;
    left: 1rem;
    z-index: 60;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.75rem;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: none;
}

.hamburger-icon {
    width: 20px;
    height: 20px;
    stroke: #374151;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .hamburger-btn {
        display: block;
    }

    .main-content {
        margin-left: 0;
    }

    .sidebar {
        transform: translateX(-100%);
    }

    .sidebar.show {
        transform: translateX(0);
    }

    .sidebar-overlay.active {
        display: block;
    }
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
    margin: 0;
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
    padding: 20px;
}

/* Content Styles */
.content {
    padding: 20px;
    background: #f5f5f5;
    min-height: calc(100vh - 40px);
    overflow-y: auto;
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

/* CRITICAL: Table container with FORCED horizontal scroll */
.table-container {
    width: 100%;
    overflow-x: scroll !important; /* Force horizontal scroll */
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
    position: relative;
    scroll-behavior: smooth;
}

/* CRITICAL: Force scrollbar to always show */
.table-container::-webkit-scrollbar {
    height: 14px !important;
    background: #f1f5f9;
}

.table-container::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 7px;
}

.table-container::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 7px;
    border: 2px solid #f1f5f9;
}

.table-container::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}

/* CRITICAL: Table with guaranteed minimum width */
.user-table {
    width: 100%;
    min-width: 1200px !important; /* FORCE horizontal scroll by making table wider than any container */
    border-collapse: collapse;
    background: white;
    table-layout: fixed;
}

/* CRITICAL: Fixed column widths that total more than mobile screen width */
.user-table th:nth-child(1), /* ID */
.user-table td:nth-child(1) {
    width: 100px;
    min-width: 100px;
}

.user-table th:nth-child(2), /* Name */
.user-table td:nth-child(2) {
    width: 200px;
    min-width: 200px;
}

.user-table th:nth-child(3), /* Email */
.user-table td:nth-child(3) {
    width: 250px;
    min-width: 250px;
}

.user-table th:nth-child(4), /* Role */
.user-table td:nth-child(4) {
    width: 120px;
    min-width: 120px;
}

.user-table th:nth-child(5), /* Identifier */
.user-table td:nth-child(5) {
    width: 150px;
    min-width: 150px;
}

.user-table th:nth-child(6), /* Status */
.user-table td:nth-child(6) {
    width: 120px;
    min-width: 120px;
}

.user-table th:nth-child(7), /* Last Login */
.user-table td:nth-child(7) {
    width: 180px;
    min-width: 180px;
}

.user-table th:nth-child(8), /* Actions */
.user-table td:nth-child(8) {
    width: 180px;
    min-width: 180px;
}

.user-table th {
    background: #f9fafb;
    padding: 12px 8px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
    position: sticky;
    top: 0;
    z-index: 5;
}

.user-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #6b7280;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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
    gap: 6px;
    justify-content: flex-start;
}

.user-actions a {
    padding: 6px 8px;
    border-radius: 4px;
    text-decoration: none;
    font-size: 14px;
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 32px;
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

/* Pagination */
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

/* MOBILE AND TABLET RESPONSIVE - GUARANTEED HORIZONTAL SCROLL */
@media (max-width: 1024px) {
    .menu-toggle {
        display: flex;
    }

    .content {
        padding: 15px;
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
        padding: 15px;
    }

    /* TABLET: Maintain table width for horizontal scroll */
    .user-table {
        min-width: 1100px !important;
    }
}

@media (max-width: 768px) {
    .content {
        padding: 10px;
    }

    .card-header {
        padding: 15px;
    }

    .card-body {
        padding: 10px;
    }

    .filter-bar {
        padding: 12px;
        margin-bottom: 15px;
    }

    /* MOBILE: Force table to extend beyond screen for scroll */
    .table-container {
        margin: 0 -10px;
        border-left: none;
        border-right: none;
        border-radius: 0;
        width: calc(100% + 20px);
        overflow-x: scroll !important;
    }

    .user-table {
        min-width: 1000px !important; /* More than any mobile screen width */
    }

    .user-table th,
    .user-table td {
        padding: 8px 6px;
        font-size: 12px;
    }

    /* Make scrollbar more visible on mobile */
    .table-container::-webkit-scrollbar {
        height: 16px !important;
    }

    .table-container::-webkit-scrollbar-thumb {
        background: #64748b !important;
        border-radius: 8px;
        border: 3px solid #f1f5f9;
    }

    .user-actions {
        gap: 4px;
    }

    .user-actions a {
        padding: 4px 6px;
        min-width: 28px;
        height: 28px;
        font-size: 12px;
    }

    .pagination {
        gap: 3px;
        overflow-x: auto;
        justify-content: flex-start;
        padding: 10px 5px;
    }

    .pagination a, .pagination span {
        padding: 6px 8px;
        font-size: 12px;
        min-width: 32px;
        flex-shrink: 0;
    }
}

@media (max-width: 480px) {
    .content {
        padding: 5px;
    }

    .card-header {
        padding: 12px;
    }

    .card-body {
        padding: 8px;
    }

    .filter-bar {
        padding: 10px;
    }

    /* SMALL MOBILE: Even more aggressive horizontal scroll */
    .table-container {
        margin: 0 -8px;
        width: calc(100% + 16px);
        overflow-x: scroll !important;
    }

    .user-table {
        min-width: 900px !important; /* Still wider than small screens */
    }

    .user-table th,
    .user-table td {
        padding: 6px 4px;
        font-size: 11px;
    }

    .user-actions a {
        padding: 3px 4px;
        min-width: 24px;
        height: 24px;
        font-size: 10px;
    }

    .pagination a, .pagination span {
        padding: 4px 6px;
        font-size: 11px;
        min-width: 28px;
    }
}

/* Force horizontal scroll on ALL devices */
@media (max-width: 1200px) {
    .table-container {
        overflow-x: scroll !important;
    }
    
    .user-table {
        width: max-content !important;
    }
}

/* SIMPLE AND GUARANTEED TABLE SCROLL */
.table-wrapper {
    width: 100%;
    position: relative;
}

.table-scroll {
    width: 100%;
    overflow-x: auto;
    overflow-y: hidden;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    background: white;
}

.user-table {
   /* Fixed width - always wider than screens */
    border-collapse: collapse;
    background: white;
    margin: 0;
}

.user-table th {
    background: #f9fafb;
    padding: 12px 8px;
    text-align: left;
    font-weight: 600;
    font-size: 14px;
    color: #374151;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.user-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 14px;
    color: #6b7280;
    white-space: nowrap;
}

.user-table tbody tr:hover {
    background: #f9fafb;
}

/* Scrollbar styling */
.table-scroll::-webkit-scrollbar {
    height: 8px;
}

.table-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.table-scroll::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-scroll::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Mobile adjustments */
@media (max-width: 768px) {
    .table-scroll {
        margin: 0 -10px;
        border-left: none;
        border-right: none;
        border-radius: 0;
    }
    
    .user-table th,
    .user-table td {
        padding: 8px 6px;
        font-size: 12px;
    }
    
    .table-scroll::-webkit-scrollbar {
        height: 12px;
    }
}

@media (max-width: 480px) {
    .table-scroll {
        margin: 0 -8px;
    }
    
    .user-table th,
    .user-table td {
        padding: 6px 4px;
        font-size: 11px;
    }
}
</style>
</head>
<body>
    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <main class="main-content">
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
                  <!-- Replace your current table structure with this -->
<div class="table-wrapper">
    <div class="table-scroll">
        <table class="user-table">
            <thead>
                <tr>
                    <th style="min-width: 80px;">ID</th>
                    <th style="min-width: 200px;">Name</th>
                    <th style="min-width: 250px;">Email</th>
                    <th style="min-width: 120px;">Role</th>
                    <th style="min-width: 150px;">Identifier</th>
                    <th style="min-width: 100px;">Status</th>
                    <th style="min-width: 180px;">Last Login</th>
                    <th style="min-width: 140px;">Actions</th>
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
    </div>
</div>
                        
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
                        </div>                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>    <script>
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

        // Smooth scroll to top when pagination changes
        $('.pagination a').click(function() {
            // Small delay to allow page transition, then scroll to top of content
            setTimeout(function() {
                $('.main-content').animate({
                    scrollTop: 0
                }, 300);
            }, 100);
        });

        // Auto-scroll to content area after page load
        if (window.location.hash) {
            setTimeout(function() {
                const target = $(window.location.hash);
                if (target.length) {
                    $('.main-content').animate({
                        scrollTop: target.offset().top - $('.main-content').offset().top + $('.main-content').scrollTop() - 20
                    }, 500);
                }
            }, 300);
        }
    });

    // Hamburger menu toggle function
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        
        if (sidebar) {
            sidebar.classList.toggle('show');
            if (overlay) {
                overlay.classList.toggle('active');
            }
        }
    }
    </script>
</body>
</html> 
