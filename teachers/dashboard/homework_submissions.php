<?php
require_once 'con.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: ../index.php');
    exit;
}

$homework_id = $_GET['id'] ?? 0;
if (!$homework_id) {
    header('Location: homework.php');
    exit;
}

$teacher_id = $_SESSION['user_id'];

// Get homework details
$conn = getDbConnection();
$sql = "SELECT h.*, c.name AS class_name, s.name AS section_name, sub.name AS subject_name
        FROM homework h
        JOIN classes c ON h.class_id = c.id
        JOIN sections s ON h.section_id = s.id
        JOIN subjects sub ON h.subject_id = sub.id
        WHERE h.id = ? AND h.teacher_user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $homework_id, $teacher_id);
$stmt->execute();
$homework = $stmt->get_result()->fetch_assoc();

if (!$homework) {
    header('Location: homework.php');
    exit;
}

// Get submissions
$sql = "SELECT hs.*, s.full_name AS student_name, s.roll_number
        FROM homework_submissions hs
        JOIN students s ON hs.student_user_id = s.user_id
        WHERE hs.homework_id = ?
        ORDER BY hs.submitted_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $homework_id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total students in class/section
$sql = "SELECT COUNT(*) as total_students
        FROM students
        WHERE class_id = ? AND section_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $homework['class_id'], $homework['section_id']);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['total_students'];

$stmt->close();
$conn->close();
?>

<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework Submissions</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/homework.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    /* Modern font and base styles */
    * {
        box-sizing: border-box;
    }

    html, body {
        margin: 0;
        padding: 0;
        height: 100%;
        overflow: hidden;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background: #fafbfc;
        color: #1a202c;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Fix sidebar positioning to prevent gap */
    .sidebar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        height: 100vh !important;
        width: 260px !important;
        z-index: 999 !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        background: white;
        border-right: 1px solid #e2e8f0;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
    }

    /* Modern hamburger button */
    .hamburger-btn {
        position: fixed !important;
        top: 20px;
        left: 20px;
        z-index: 1000 !important;
        background: white;
        border: none;
        border-radius: 12px;
        padding: 12px;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(10px);
    }

    .hamburger-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .hamburger-btn:active {
        transform: translateY(0);
    }

    .hamburger-icon {
        width: 24px;
        height: 24px;
        color: #4a5568;
        transition: color 0.2s;
    }

    .hamburger-btn:hover .hamburger-icon {
        color: #2d3748;
    }

    /* Dashboard container with full screen usage */
    .dashboard-container {
        margin-left: 260px;
        width: calc(100vw - 260px);
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden;
        position: relative;
       
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Modern content area with full width usage */
    .dashboard-content {
        padding: 0;
        max-width: none;
        width: 100%;
        min-height: 100vh;
        background: #ffff;
        border-radius: 0;
        margin-top: 0;
        position: relative;
        padding-top: 140px;
    }

    /* Modern header with gradient - fixed at top */
    .dashboard-header {
        position: fixed;
        top: 0;
        left: 260px;
        right: 0;
        width: calc(100vw - 260px);
        padding: 32px 40px;
         background: #ffff;
        border-radius: 0;
        box-shadow: none;
        border: none;
        margin: 0;
        z-index: 100;
    }

    .header-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: black;
        margin: 0 0 8px 0;
        letter-spacing: -0.02em;
    }

    .header-subtitle {
        color: rgba(0, 0, 0, 0.8);
        font-size: 1.1rem;
        margin: 0;
        font-weight: 400;
    }

    /* Content with proper padding and full width */
    .dashboard-content > * {
        margin-left: 40px;
        margin-right: 40px;
    }

    /* Modern stats grid with full width */
    .quick-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 40px;
        width: calc(100% - 80px);
        margin-left: 40px;
        margin-right: 40px;
    }

    .stat-card {
        background: white;
        border-radius: 20px;
        padding: 32px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 20px 20px 0 0;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .stat-title {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 12px;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .stat-value {
        font-size: 3rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 16px;
        line-height: 1;
    }

    .stat-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 12px;
        font-weight: 400;
    }

    .stat-trend {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 12px;
    }

    .trend-up {
        color: #10b981;
    }

    .trend-down {
        color: #ef4444;
    }

    .progress-container {
        width: 100%;
        height: 8px;
        background-color: #f1f5f9;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #667eea, #764ba2);
        border-radius: 4px;
        transition: width 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Modern card styles with full width */
    .card {
        width: calc(100% - 80px);
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        margin-bottom: 24px;
        margin-left: 40px;
        margin-right: 40px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        padding: 32px 40px;
        border-bottom: 1px solid #f1f5f9;
        background: #fafbfc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .card-body {
        padding: 32px 40px;
    }

    /* Modern tabs */
    .tabs {
        display: flex;
        border-bottom: 1px solid #f1f5f9;
        background: white;
        padding: 0 40px;
        margin: 0 -40px 32px -40px;
    }

    .tab {
        padding: 16px 24px;
        cursor: pointer;
        color: #64748b;
        font-weight: 500;
        border-bottom: 3px solid transparent;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        white-space: nowrap;
        font-size: 0.875rem;
        position: relative;
    }

    .tab:hover {
        color: #475569;
        background: #f8fafc;
    }

    .tab.active {
        color: #667eea;
        border-bottom-color: #667eea;
        background: white;
    }

    /* Search and filters */
    .search-container {
        position: relative;
        margin-bottom: 24px;
    }

    .search-input {
        width: 100%;
        padding: 16px 20px 16px 48px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: white;
    }

    .search-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        width: 20px;
        height: 20px;
        color: #9ca3af;
    }

    .filters-container {
        display: flex;
        gap: 20px;
        align-items: flex-end;
        margin-bottom: 32px;
        flex-wrap: wrap;
    }

    .filter {
        flex: 1;
        min-width: 200px;
    }

    .form-select {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        background: white;
        cursor: pointer;
        transition: all 0.2s;
    }

    .form-select:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Modern buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 500;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.875rem;
        white-space: nowrap;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        min-width: 140px;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-secondary {
        background: white;
        color: #475569;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .btn-secondary:hover {
        background: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn-sm {
        padding: 8px 16px;
        font-size: 0.8rem;
    }

    .btn-icon {
        width: 18px;
        height: 18px;
    }

    /* Homework cards */
    .card-grid {
        display: grid;
        gap: 24px;
    }

    .homework-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        border: 1px solid #f1f5f9;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }

    .homework-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.12);
    }

    .card-header-with-menu {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
    }

    .homework-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 8px 0;
        letter-spacing: -0.01em;
    }

    .homework-meta {
        display: flex;
        gap: 12px;
        font-size: 0.8rem;
        color: #64748b;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .homework-meta span {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        background: #f8fafc;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-weight: 500;
    }

    .homework-description {
        color: #475569;
        line-height: 1.6;
        margin-bottom: 20px;
        font-size: 0.9rem;
    }

    .homework-status {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .status-pending {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .status-grading {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
    }

    .status-completed {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .status-overdue {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    /* Three-dot menu */
    .three-dot-menu {
        position: relative;
        display: inline-block;
    }

    .three-dot-btn {
        background: none;
        border: none;
        cursor: pointer;
        padding: 8px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        color: #64748b;
    }

    .three-dot-btn:hover {
        background-color: #f1f5f9;
        color: #374151;
    }

    .dots-icon {
        width: 20px;
        height: 20px;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        min-width: 160px;
        z-index: 1000;
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s ease;
        overflow: hidden;
    }

    .dropdown-menu.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 12px 16px;
        text-decoration: none;
        color: #374151;
        font-size: 14px;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
        background: none;
        width: 100%;
        text-align: left;
    }

    .dropdown-item:hover {
        background-color: #f8fafc;
    }

    .dropdown-item.danger {
        color: #dc2626;
    }

    .dropdown-item.danger:hover {
        background-color: #fef2f2;
    }

    .dropdown-icon {
        width: 16px;
        height: 16px;
        margin-right: 8px;
    }

    /* Tab content */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Table styles for submissions */
    .table-responsive {
        overflow-x: auto;
        margin: -32px -40px;
        padding: 32px 40px;
        border-radius: 20px;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.875rem;
    }

    .table th {
        padding: 16px 20px;
        text-align: left;
        background: #f8fafc;
        font-weight: 600;
        color: #475569;
        border-bottom: 1px solid #e2e8f0;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .table th:first-child {
        border-radius: 12px 0 0 0;
    }

    .table th:last-child {
        border-radius: 0 12px 0 0;
    }

    .table td {
        padding: 16px 20px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
        vertical-align: middle;
    }

    .table tr:hover {
        background-color: #f8fafc;
    }

    .table tr:last-child td {
        border-bottom: none;
    }

    /* Status badges in tables */
    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: capitalize;
        letter-spacing: 0.02em;
    }

    .status-badge.submitted {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        color: white;
    }

    .status-badge.graded {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .status-badge.not_submitted {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    .no-data {
        text-align: center;
        padding: 80px 20px;
        color: #64748b;
        font-style: italic;
        font-size: 1rem;
    }

    /* Assignment form */
    .hidden-form {
        display: none;
        margin-top: 24px;
    }

    .hidden-form.show {
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 500;
        color: #374151;
        margin-bottom: 8px;
        font-size: 0.875rem;
    }

    .form-input, .form-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 0.875rem;
        transition: all 0.2s;
        background: white;
        font-family: inherit;
    }

    .form-input:focus, .form-textarea:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 120px;
    }

    .two-col {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }

    .file-upload {
        border: 2px dashed #e2e8f0;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        transition: all 0.2s;
        background: #fafbfc;
    }

    .file-upload:hover {
        border-color: #667eea;
        background: #f8fafc;
    }

    .file-upload-label {
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 12px;
        color: #64748b;
    }

    .file-upload-input {
        display: none;
    }

    .upload-icon {
        width: 32px;
        height: 32px;
        color: #94a3b8;
    }

    .upload-text {
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* Mobile responsive design */
    @media (max-width: 768px) {
        .sidebar {
            position: fixed !important;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100vh !important;
            width: 260px !important;
            z-index: 999 !important;
        }
        
        .sidebar.show {
            transform: translateX(0) !important;
        }

        .dashboard-container {
            margin-left: 0;
            width: 100vw;
            height: 100vh;
        }

        .dashboard-header {
            left: 0;
            width: 100vw;
            padding: 24px 16px;
        }
        
        .dashboard-content {
            padding-top: 120px;
        }

        .dashboard-content > *,
        .quick-stats,
        .card {
            margin-left: 16px;
            margin-right: 16px;
            width: calc(100% - 32px);
        }

        .header-title {
            font-size: 2rem;
        }

        .header-subtitle {
            font-size: 1rem;
        }

        .hamburger-btn {
            top: 16px;
            left: 16px;
            padding: 10px;
        }

        .quick-stats {
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .stat-card {
            padding: 20px;
        }

        .stat-value {
            font-size: 2.2rem;
        }

        .card-header, .card-body {
            padding: 20px;
        }

        .card-header {
            flex-direction: column;
            gap: 16px;
            align-items: flex-start;
        }

        .card-header .btn {
            width: 100%;
            justify-content: center;
        }

        /* Mobile tabs */
        .tabs {
            display: flex;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 0 20px;
            margin: 0 -20px 20px -20px;
        }

        .tabs::-webkit-scrollbar {
            display: none;
        }

        .tab {
            padding: 12px 16px;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* Mobile filters */
        .filters-container {
            flex-direction: column !important;
            gap: 12px !important;
            align-items: stretch !important;
        }

        .filter {
            width: 100% !important;
            min-width: auto !important;
        }

        .btn {
            width: 100% !important;
            justify-content: center !important;
        }

        .homework-card {
            padding: 20px;
        }

        .homework-meta {
            gap: 8px;
        }

        .homework-meta span {
            padding: 3px 8px;
            font-size: 0.75rem;
        }

        .two-col {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .table-responsive {
            margin: -20px;
            padding: 20px;
        }

        .table th,
        .table td {
            padding: 12px 8px;
            font-size: 0.8rem;
        }
    }

    /* Tablet adjustments */
    @media (min-width: 769px) and (max-width: 1024px) {
        .dashboard-container {
            margin-left: 260px;
            width: calc(100vw - 260px);
        }

        .dashboard-header {
            left: 260px;
            width: calc(100vw - 260px);
        }

        .quick-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .filters-container {
            flex-wrap: wrap !important;
        }

        .filter {
            min-width: 180px;
            flex: 1;
        }
    }

    /* Desktop optimizations */
    @media (min-width: 1025px) {
        .dashboard-content {
            padding-top: 160px;
        }

        .dashboard-header {
            padding: 40px 40px;
        }

        .quick-stats {
            grid-template-columns: repeat(4, 1fr);
            gap: 32px;
        }

        .filters-container {
            flex-wrap: nowrap !important;
            gap: 24px;
        }
    }

    /* Sidebar collapsed state */
    .sidebar.collapsed + .dashboard-container {
        margin-left: 80px;
        width: calc(100vw - 80px);
    }

    .sidebar.collapsed ~ .dashboard-header {
        left: 80px;
        width: calc(100vw - 80px);
    }

    @media (max-width: 768px) {
        .sidebar.collapsed + .dashboard-container {
            margin-left: 0;
            width: 100vw;
        }

        .sidebar.collapsed ~ .dashboard-header {
            left: 0;
            width: 100vw;
        }
    }

    /* Sidebar overlay */
    .sidebar-overlay {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh !important;
        background: rgba(15, 23, 42, 0.5);
        backdrop-filter: blur(4px);
        z-index: 998;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    /* Large desktop screens */
    @media (min-width: 1400px) {
        .dashboard-content {
            padding-top: 180px;
        }

        .dashboard-header {
            padding: 50px 60px;
        }

        .dashboard-content > *,
        .quick-stats,
        .card {
            margin-left: 60px;
            margin-right: 60px;
            width: calc(100% - 120px);
        }

        .quick-stats {
            gap: 40px;
        }

        .stat-card {
            padding: 40px;
        }

        .header-title {
            font-size: 3rem;
        }
    }

    /* Enhanced focus states for accessibility */
    .btn:focus-visible,
    .form-input:focus-visible,
    .form-textarea:focus-visible,
    .form-select:focus-visible {
        outline: 2px solid #667eea;
        outline-offset: 2px;
    }

    /* Smooth scrolling */
    .dashboard-container {
        scroll-behavior: smooth;
    }

    /* Loading animation for buttons */
    .btn:active {
        transform: scale(0.98);
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
            <h1 class="header-title">Homework Submissions</h1>
            <p class="header-subtitle">View and grade student submissions</p>
        </header>

        <main class="dashboard-content">
            <!-- Homework Details -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title"><?= htmlspecialchars($homework['title']) ?></h2>
                    <div class="homework-meta">
                        <span class="class-info">
                            <?= htmlspecialchars($homework['class_name']) ?>
                        </span>
                        <span class="section-info">
                            <?= htmlspecialchars($homework['section_name']) ?>
                        </span>
                        <span class="subject-info">
                            <?= htmlspecialchars($homework['subject_name']) ?>
                        </span>
                        <span class="due-date">Due: <?= date('M j, Y', strtotime($homework['due_date'])) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <p class="description"><?= nl2br(htmlspecialchars($homework['description'])) ?></p>
                    <?php if ($homework['attachment']): ?>
                        <div class="attachment">
                            <a href="<?= htmlspecialchars($homework['attachment']) ?>" target="_blank" class="btn btn-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                </svg>
                                View Attachment
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Submission Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-title">Total Submissions</div>
                    <div class="stat-value"><?= count($submissions) ?></div>
                    <div class="progress-container">
                        <div class="progress-bar" style="width: <?= ($total_students > 0 ? (count($submissions) / $total_students * 100) : 0) ?>%"></div>
                    </div>
                    <div class="stat-subtitle"><?= count($submissions) ?> of <?= $total_students ?> students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Graded</div>
                    <div class="stat-value"><?= count(array_filter($submissions, fn($s) => $s['status'] === 'graded')) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Pending</div>
                    <div class="stat-value"><?= count(array_filter($submissions, fn($s) => $s['status'] !== 'graded')) ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-title">Average Grade</div>
                    <?php
                    $graded = array_filter($submissions, fn($s) => $s['status'] === 'graded');
                    $avg_grade = count($graded) > 0 ? array_sum(array_column($graded, 'grade_code')) / count($graded) : 0;
                    ?>
                    <div class="stat-value"><?= number_format($avg_grade, 1) ?></div>
                </div>
            </div>

            <!-- Submissions List -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Student Submissions</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($submissions)): ?>
                        <div class="no-data">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="margin: 0 auto 16px; color: #cbd5e1;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <br>No submissions yet.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Roll Number</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($submissions as $submission): ?>
                                        <tr data-submission-id="<?= $submission['id'] ?>">
                                            <td><strong><?= htmlspecialchars($submission['student_name']) ?></strong></td>
                                            <td><?= htmlspecialchars($submission['roll_number']) ?></td>
                                            <td><?= date('M j, Y g:i A', strtotime($submission['submitted_at'])) ?></td>
                                            <td>
                                                <span class="status-badge <?= $submission['status'] ?>">
                                                    <?= ucfirst($submission['status']) ?>
                                                </span>
                                            </td>
                                            <td><strong><?= $submission['grade_code'] ?? '-' ?></strong></td>
                                            <td>
                                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                                    <?php if ($submission['file_path']): ?>
                                                        <a href="<?= htmlspecialchars($submission['file_path']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                           </svg>
                                                           View
                                                       </a>
                                                   <?php endif; ?>
                                                   <button class="btn btn-primary btn-sm" onclick="gradeSubmission(<?= $submission['id'] ?>)">
                                                       <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                       </svg>
                                                       Grade
                                                   </button>
                                               </div>
                                           </td>
                                       </tr>
                                   <?php endforeach; ?>
                               </tbody>
                           </table>
                       </div>
                   <?php endif; ?>
               </div>
           </div>
       </main>
   </div>

   <!-- Grade Submission Modal -->
   <div id="gradeModal" class="modal" style="display: none;">
       <div class="modal-content">
           <div class="modal-header">
               <h3>Grade Submission</h3>
               <button class="close-btn" onclick="closeGradeModal()">&times;</button>
           </div>
           <div class="modal-body">
               <form id="gradeForm">
                   <input type="hidden" id="submissionId" name="submission_id">
                   <div class="form-group">
                       <label for="totalMarks" class="form-label">Total Marks:</label>
                       <span id="totalMarks" class="form-output"></span>
                   </div>
                   <div class="form-group">
                       <label for="marksObtained" class="form-label">Marks Obtained</label>
                       <input type="number" id="marksObtained" name="marks_obtained" class="form-input" min="0" required>
                   </div>
                   <div class="form-group">
                       <label for="feedback" class="form-label">Feedback</label>
                       <textarea id="feedback" name="feedback" class="form-textarea" rows="4" placeholder="Enter your feedback for the student..."></textarea>
                   </div>
                   <div class="form-actions">
                       <button type="button" class="btn btn-secondary" onclick="closeGradeModal()">Cancel</button>
                       <button type="submit" class="btn btn-primary">
                           <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                           </svg>
                           Save Grade
                       </button>
                   </div>
               </form>
           </div>
       </div>
   </div>

   <script>
       // Sidebar toggle function
       function toggleSidebar() {
           const sidebar = document.querySelector('.sidebar');
           const overlay = document.querySelector('.sidebar-overlay');
           
           if (sidebar) {
               sidebar.classList.toggle('show');
           }
           if (overlay) {
               overlay.classList.toggle('active');
           }
       }

       // Close sidebar when clicking overlay
       document.querySelector('.sidebar-overlay')?.addEventListener('click', function() {
           const sidebar = document.querySelector('.sidebar');
           if (sidebar) {
               sidebar.classList.remove('show');
           }
           this.classList.remove('active');
       });

       // Grade submission modal
       function gradeSubmission(submissionId) {
           // Fetch submission details including total marks
           fetch('homework_actions.php?action=get_submissions&submission_id=' + submissionId)
               .then(response => response.json())
               .then(data => {
                   if (data.status === 'success' && data.submissions.length > 0) {
                       const submission = data.submissions[0]; // Assuming only one submission per student per homework
                       document.getElementById('submissionId').value = submission.id;
                       document.getElementById('totalMarks').textContent = submission.total_marks;
                       document.getElementById('marksObtained').value = submission.marks_obtained ?? ''; // Populate if already graded
                       document.getElementById('feedback').value = submission.feedback ?? ''; // Populate feedback
                       document.getElementById('gradeModal').style.display = 'block';
                   } else {
                       alert('Error fetching submission details: ' + (data.message || 'Submission not found.'));
                   }
               })
               .catch(error => {
                   console.error('Error:', error);
                   alert('Error fetching submission details. Please try again.');
               });
       }

       function closeGradeModal() {
           document.getElementById('gradeModal').style.display = 'none';
           document.getElementById('gradeForm').reset();
           // Clear total marks display
           document.getElementById('totalMarks').textContent = '';
       }

       // Handle grade form submission
       document.getElementById('gradeForm').addEventListener('submit', function(e) {
           e.preventDefault();
           const formData = {
               action: 'grade_homework',
               submission_id: document.getElementById('submissionId').value,
               marks_obtained: document.getElementById('marksObtained').value, // Send marks obtained
               feedback: document.getElementById('feedback').value
           };

           fetch('homework_actions.php', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/json',
               },
               body: JSON.stringify(formData)
           })
           .then(response => response.json())
           .then(data => {
               if (data.status === 'success') {
                   // Update the specific row in the table instead of reloading the whole page
                   const submissionId = data.submission_id; // Get submission ID from response if needed, or use the one we have
                   const marksObtained = data.marks_obtained; // Get updated marks from response
                   const gradeCode = data.grade_code; // Get calculated grade from response

                   // Find the row in the table for this submission using the data-submission-id attribute
                   const row = document.querySelector(`tr[data-submission-id='${submissionId}']`);
                   if (row) {
                       // Update Grade and Marks Obtained columns
                       const cells = row.querySelectorAll('td');
                       // Assuming Grade is the 5th column (index 4) and Status is the 4th (index 3)
                       cells[4].innerHTML = '<strong>' + gradeCode + '</strong>'; // Update Grade column
                       
                        // If you want to display marks obtained in the table, you'll need to add a column for it
                        // and update the corresponding cell here.

                       const statusBadge = cells[3].querySelector('.status-badge'); 
                        if (statusBadge) {
                            statusBadge.textContent = 'Graded';
                            statusBadge.className = 'status-badge graded';
                        }
                   }

                   closeGradeModal();
                   // No need to reload: window.location.reload();
               } else {
                   alert('Error saving grade: ' + data.message);
               }
           })
           .catch(error => {
               console.error('Error:', error);
               alert('Error saving grade. Please try again.');
           });
       });

       // Close modal when clicking outside
       window.onclick = function(event) {
           const modal = document.getElementById('gradeModal');
           if (event.target === modal) {
               closeGradeModal();
           }
       }

       // Add smooth scrolling behavior
       document.querySelectorAll('a[href^="#"]').forEach(anchor => {
           anchor.addEventListener('click', function (e) {
               e.preventDefault();
               document.querySelector(this.getAttribute('href')).scrollIntoView({
                   behavior: 'smooth'
               });
           });
       });
   </script>
</body>
</html>
