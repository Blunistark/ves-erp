<?php
/**
 * Unified Academic Management System
 * Comprehensive academic structure management interface
 * Role-based access control: Admin can do everything, others have limited access
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
$subjects_raw = executeQuery("SELECT id, name, code FROM subjects ORDER BY name");
$academic_years_raw = executeQuery("SELECT id, name FROM academic_years ORDER BY start_date DESC");

// Ensure data is array for json_encode to prevent JS errors
$classes = is_array($classes_raw) ? $classes_raw : [];
$subjects = is_array($subjects_raw) ? $subjects_raw : [];
$academic_years = is_array($academic_years_raw) ? $academic_years_raw : [];

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Academic Management - Unified System</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/academic_management_unified.css">
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

        .search-container{     {/** for bot class and subject */}

            padding: 20px;
            
        }

        .search-input{
              width: 70%;
            padding: 12px 16px 12px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius);
            font-size: 1rem;

        }

        .unified-container {
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .header-section {
            background: var(--bg-white);
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            margin-bottom: 30px;
            border-left: 4px solid var(--primary-color);
        }

        .header-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 8px;
        }

        .header-subtitle {
            color: var(--text-secondary);
            font-size: 1.1rem;
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
            border-bottom: 1px solid var(--border-color);
            overflow-x: auto;
        }

        .tab-button {
            padding: 16px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            white-space: nowrap;
            transition: all 0.3s ease;
            position: relative;
            border-bottom: 3px solid transparent;
        }

        .tab-button:hover {
            color: var(--primary-color);
            background: rgba(253, 93, 93, 0.05);
        }

        .tab-button.active {
            color: var(--primary-color);
            background: var(--bg-white);
            border-bottom-color: var(--primary-color);
        }

        .tab-content {
            padding: 30px;
            display: none;
            min-height: 500px;
        }

        .tab-content.active {
            display: block;
        }

        .btn {
            padding: 10px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: #e54e4e;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: var(--bg-light);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
        }

        .btn-outline {
            background: transparent;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-outline:hover {
            background: var(--primary-color);
            color: white;
        }

        .notification {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 20px;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            min-width: 300px;
            box-shadow: var(--shadow-md);
        }

        .notification.success {
            background: #48bb78;
        }

        .notification.error {
            background: #f56565;
        }

        .notification.info {
            background: #4299e1;
        }

        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid var(--border-color);
            border-top: 2px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal-container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 500px;
            box-shadow: var(--shadow-md);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 20px;
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
            color: var(--text-primary);
        }

        .modal-body {
            padding: 20px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 4px;
            color: var(--text-primary);
        }

        .form-label.required::after {
            content: '*';
            color: #f56565;
            margin-left: 4px;
        }

        .form-input, .form-select, .form-textarea {
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-color);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }

        /* Classes & Sections Specific Styles */
        .classes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .class-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
        }

        .class-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .class-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .class-name {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .class-actions {
            display: flex;
            gap: 8px;
        }

        .sections-list {
            margin-bottom: 16px;
        }

        .sections-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 8px;
        }

        .sections-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 8px;
            margin-bottom: 12px;
        }

        .section-badge {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-primary);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .section-badge:hover {
            background: var(--accent-color);
            color: white;
            border-color: var(--accent-color);
        }

        .section-badge.has-teacher {
            background: #e6fffa;
            border-color: var(--accent-color);
            color: #065f46;
        }

        .add-section-btn {
            background: #f0f9ff;
            border: 2px dashed var(--secondary-color);
            border-radius: 6px;
            padding: 8px 12px;
            text-align: center;
            font-size: 0.875rem;
            color: var(--secondary-color);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .add-section-btn:hover {
            background: var(--secondary-color);
            color: white;
        }

        .class-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
            border-top: 1px solid var(--border-color);
            padding-top: 12px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .stat-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-actions {
            display: flex;
            gap: 10px;
        }

        /* Subjects Management Specific Styles */
        .subjects-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .subjects-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-white);
            padding: 16px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .subjects-search {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            max-width: 400px;
        }

        .subjects-table-container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .subjects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .subjects-table th {
            background: var(--bg-light);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            border-bottom: 2px solid var(--border-color);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .subjects-table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .subjects-table tr:hover {
            background: #f8fafc;
        }

        .subjects-table tr:last-child td {
            border-bottom: none;
        }

        .subject-code {
            background: var(--secondary-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            min-width: 40px;
            text-align: center;
        }

        .subject-name {
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }

        .subject-meta {
            font-size: 0.875rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .subjects-actions {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .subjects-empty {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-secondary);
        }

        .subjects-empty i {
            font-size: 3rem;
            margin-bottom: 16px;
            opacity: 0.3;
            color: var(--primary-color);
        }

        .subjects-empty h3 {
            margin-bottom: 8px;
            color: var(--text-primary);
        }

        .subject-usage-badge {
            background: var(--accent-color);
            color: white;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .subject-usage-badge.no-usage {
            background: var(--text-secondary);
        }

        .bulk-actions-bar {
            background: var(--bg-white);
            padding: 12px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            display: none;
            align-items: center;
            gap: 16px;
            border-left: 4px solid var(--accent-color);
        }

        .bulk-actions-bar.show {
            display: flex;
        }

        .selected-count {
            font-weight: 600;
            color: var(--text-primary);
        }

        .subjects-filters {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: white;
            color: var(--text-primary);
            font-size: 0.875rem;
            min-width: 120px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(253, 93, 93, 0.1);
        }

        .subject-checkbox {
            margin-right: 12px;
            transform: scale(1.1);
        }

        /* Curriculum Mapping Specific Styles */
        .curriculum-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .curriculum-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-white);
            padding: 16px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .curriculum-filters {
            display: flex;
            gap: 12px;
            align-items: center;
        }        .curriculum-matrix-container {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: auto;
            max-height: 600px;
            position: relative;
        }

        .curriculum-matrix {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            min-width: 800px; /* Ensure horizontal scrolling for many subjects */
        }

       /* Ensure table cells maintain proper alignment */
        .curriculum-matrix td {
            padding: 0; /* Remove default padding since assignment-cell has its own */
            border: 1px solid var(--border-color);
            text-align: center;
            vertical-align: middle;
            position: relative;
            width: auto; /* Allow natural width */
            min-width: 45px; /* Minimum width for assignment cells */
        }

        /* Specific styling for assignment cells within table */
        .curriculum-matrix td .assignment-cell {
            width: 100%;
            height: 100%;
            margin: 0;
            border: none; /* Remove border since td already has it */
        }

        /* Fix for sticky headers alignment */
        .curriculum-matrix th.subject-header {
            background: var(--secondary-color);
            color: white;
            min-width: 120px;
            max-width: 120px;
            padding: 8px 4px; /* Reduced padding for better alignment */
            font-weight: 600;
            font-size: 0.8rem;
            line-height: 1.2;
            word-wrap: break-word;
            white-space: normal;
            text-align: center;
            vertical-align: middle;
        }

        .curriculum-matrix th.class-header {
            background: var(--primary-color);
            color: white;
            min-width: 120px;
            font-weight: 600;
            padding: 8px;
            white-space: nowrap;
            text-align: center;
            vertical-align: middle;
        }
        .assignment-cell {
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 45px;
            min-width: 45px;
            display: table-cell; /* Changed from flex to table-cell for proper table alignment */
            vertical-align: middle; /* Center vertically */
            text-align: center; /* Center horizontally */
            position: relative;
            padding: 8px; /* Add padding for better spacing */
        }

        .assignment-cell:hover {
            background: #f0f9ff;
            transform: scale(1.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .assignment-cell.assigned {
            background: var(--accent-color);
            color: white;
        }

        .assignment-cell.assigned:hover {
            background: #22c55e;
        }

        .assignment-cell.unassigned {
            background: #f8fafc;
            color: var(--text-secondary);
            border: 2px dashed #e2e8f0;
        }

        .assignment-cell.unassigned:hover {
            background: var(--accent-color);
            color: white;
            border: 2px dashed white;
        }

        .assignment-icon {
            font-size: 16px;
            font-weight: bold;
            display: inline-block; /* Ensure proper horizontal alignment */
            line-height: 1; /* Prevent vertical spacing issues */
        }

        .assignment-cell.assigned .assignment-icon {
            color: white;
        }

        .assignment-cell.unassigned .assignment-icon {
            color: #64748b;
        }

        .assignment-cell.unassigned:hover .assignment-icon {
            color: white;
        }

        .assignment-cell-flex {
            cursor: pointer;
            transition: all 0.2s ease;
            min-height: 45px;
            min-width: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 8px;
        }
        .curriculum-legend {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 16px 20px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
        }

        .legend-icon {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
        }

        .legend-assigned {
            background: var(--accent-color);
            color: white;
        }

        .legend-unassigned {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
        }

        .curriculum-summary {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 16px;
        }

        .summary-item {
            text-align: center;
            padding: 16px;
            background: var(--bg-light);
            border-radius: 6px;
        }

        .summary-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .summary-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .bulk-assignment-bar {
            background: var(--bg-white);
            padding: 16px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--secondary-color);
        }

        .bulk-assignment-form {
            display: flex;
            gap: 16px;
            align-items: end;
            flex-wrap: wrap;
        }

        .matrix-controls {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .matrix-scroll-container {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 70vh;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
        }

        .quick-actions {
            display: flex;
            gap: 8px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--border-color);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }

        .progress-fill {
            height: 100%;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        .class-summary {
            margin-top: 8px;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        /* Reports Specific Styles */
        .reports-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .reports-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-white);
            padding: 16px 20px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--accent-color);
        }

        .report-cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .report-card {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--secondary-color);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .report-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .report-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .report-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .report-icon.overview {
            background: var(--primary-color);
        }

        .report-icon.structure {
            background: var(--secondary-color);
        }

        .report-icon.curriculum {
            background: var(--accent-color);
        }

        .report-icon.analytics {
            background: #f59e0b;
        }

        .report-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .report-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 16px;
            line-height: 1.5;
        }

        .report-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }

        .report-actions {
            display: flex;
            gap: 8px;
        }

        .reports-viewer {
            background: var(--bg-white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            display: none;
        }

        .reports-viewer.active {
            display: block;
        }

        .report-viewer-header {
            background: var(--bg-light);
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-viewer-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .report-viewer-actions {
            display: flex;
            gap: 8px;
        }

        .report-content {
            padding: 20px;
            max-height: 70vh;
            overflow-y: auto;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .analytics-card {
            background: var(--bg-light);
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }

        .analytics-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 4px;
        }

        .analytics-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .analytics-trend {
            font-size: 0.75rem;
            margin-top: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .analytics-trend.positive {
            color: var(--accent-color);
        }

        .analytics-trend.negative {
            color: #ef4444;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        .report-table th,
        .report-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .report-table th {
            background: var(--bg-light);
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.875rem;
        }

        .report-table tr:hover {
            background: #f8fafc;
        }

        .report-filters {
            background: var(--bg-light);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            align-items: end;
        }

        .chart-container {
            background: var(--bg-white);
            border-radius: 8px;
            padding: 20px;
            margin: 16px 0;
            border: 1px solid var(--border-color);
        }

        .chart-placeholder {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-light);
            border-radius: 6px;
            color: var(--text-secondary);
            border: 2px dashed var(--border-color);
        }

        .export-options {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .overview-summary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .overview-summary h4 {
            margin-bottom: 8px;
            font-size: 1.1rem;
        }

        .overview-summary p {
            opacity: 0.9;
            margin: 0;
            font-size: 0.875rem;
        }

        .structure-tree {
            font-family: monospace;
            background: var(--bg-light);
            padding: 16px;
            border-radius: 6px;
            overflow-x: auto;
            white-space: pre-line;
            font-size: 0.875rem;
            line-height: 1.4;
        }

        .progress-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }

        .progress-item:last-child {
            border-bottom: none;
        }

        .progress-label {
            font-weight: 500;
            color: var(--text-primary);
        }

        .progress-bar-small {
            width: 100px;
            height: 6px;
            background: var(--border-color);
            border-radius: 3px;
            overflow: hidden;
            margin: 0 12px;
        }

        .progress-fill-small {
            height: 100%;
            background: var(--accent-color);
            transition: width 0.3s ease;
        }

        .progress-value {
            font-size: 0.875rem;
            color: var(--text-secondary);
            min-width: 40px;
            text-align: right;
        }

        @media (max-width: 768px) {
            .reports-toolbar {
                flex-direction: column;
                gap: 12px;
                align-items: stretch;
            }
            
            .report-cards-grid {
                grid-template-columns: 1fr;
            }
            
            .analytics-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
            
            .filter-row {
                grid-template-columns: 1fr;
            }
        }

        /* Bulk Operations Styles */
        .bulk-operations-header {
            margin-bottom: 2rem;
        }
        
        .bulk-operations-header h2 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        
        .bulk-operations-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        
        .bulk-sections {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .bulk-section {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .section-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
        }
        
        .section-header h3 {
            margin: 0 0 0.5rem 0;
            font-size: 1.3rem;
        }
        
        .section-header p {
            margin: 0;
            opacity: 0.9;
        }
        
        .section-content {
            padding: 2rem;
        }
        
        .operation-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .operation-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .operation-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .operation-card h4 {
            color: #2c3e50;
            margin: 0 0 0.5rem 0;
            font-size: 1.1rem;
        }
        
        .operation-card p {
            color: #6c757d;
            margin: 0 0 1rem 0;
            font-size: 0.9rem;
        }
        
        .file-upload {
            margin-bottom: 1rem;
        }
        
        .file-upload button {
            margin-right: 1rem;
        }
        
        .file-name {
            color: #28a745;
            font-weight: 500;
            font-size: 0.9rem;
        }
        
        .export-options {
            margin-bottom: 1rem;
        }
        
        .export-options label {
            display: block;
            margin-bottom: 0.5rem;
            color: #495057;
        }
        
        .export-options input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        
        .batch-form .form-group {
            margin-bottom: 1rem;
        }
        
        .batch-form label {
            display: block;
            margin-bottom: 0.5rem;
            color: #495057;
            font-weight: 500;
        }
        
        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.5rem;
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 1rem;
            background: white;
        }
        
        .checkbox-grid label {
            display: flex;
            align-items: center;
            margin-bottom: 0.25rem;
            font-weight: normal;
        }
        
        .checkbox-grid input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        
        .validation-results,
        .cleanup-results {
            margin-top: 1rem;
            padding: 1rem;
            border-radius: 4px;
            background: #f8f9fa;
            border-left: 4px solid #17a2b8;
        }
        
        .validation-item {
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            border-radius: 4px;
        }
        
        .validation-item.success {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }
        
        .validation-item.warning {
            background: #fff3cd;
            color: #856404;
            border-left: 3px solid #ffc107;
        }
        
        .validation-item.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
        }
        
        .history-table-container {
            overflow-x: auto;
        }
        
        .history-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        .history-table th,
        .history-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        
        .history-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .status-badge.success {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-badge.processing {
            background: #fff3cd;
            color: #856404;
        }
        
        /* Progress indicators */
        .progress-container {
            margin: 1rem 0;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            width: 0%;
            transition: width 0.3s ease;
        }
        
        .progress-text {
            margin-top: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
            text-align: center;
        }
    </style>
    
</head>
<body>
    <!-- Notification Element -->
    <div id="notification" class="notification"></div>

    <div class="unified-container">
        <!-- Header Section -->
        <div class="header-section">
            <h1 class="header-title">
                <i class="fas fa-graduation-cap" style="color: var(--primary-color); margin-right: 12px;"></i>
                Academic Management
            </h1>
            <p class="header-subtitle">
                Comprehensive management of academic structure, classes, sections, and subjects
            </p>
        </div>

        <!-- Main Tabs Container -->
        <div class="tabs-container">
            <!-- Tabs Navigation -->
            <div class="tabs-nav">
                <button class="tab-button active" data-tab="academic-years" onclick="showTab('academic-years')">
                    <i class="fas fa-calendar-alt"></i>
                    Academic Years
                </button>
                <button class="tab-button" data-tab="classes-sections" onclick="showTab('classes-sections')">
                    <i class="fas fa-school"></i>
                    Classes & Sections
                </button>
                <button class="tab-button" data-tab="subjects" onclick="showTab('subjects')">
                    <i class="fas fa-book"></i>
                    Subjects
                </button>
                <button class="tab-button" data-tab="curriculum" onclick="showTab('curriculum')">
                    <i class="fas fa-link"></i>
                    Curriculum Mapping
                </button>
                <button class="tab-button" data-tab="reports" onclick="showTab('reports')">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </button>
                <button class="tab-button" data-tab="bulk-operations" onclick="showTab('bulk-operations')">
                    <i class="fas fa-tasks"></i>
                    Bulk Operations
                </button>
            </div>

            <!-- Tab Content: Academic Years -->
            <div id="academic-years" class="tab-content active">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: var(--text-primary);">
                        <i class="fas fa-calendar-alt" style="color: var(--primary-color); margin-right: 8px;"></i>
                        Academic Years Management
                    </h3>
                    <button class="btn btn-primary" onclick="openAddAcademicYearModal()">
                        <i class="fas fa-plus"></i>
                        Add Academic Year
                    </button>
                </div>

                <!-- Academic Years Loading -->
                <div id="academicYearsLoading" class="loading" style="display: none;">
                    <div class="spinner"></div>
                    <span>Loading academic years...</span>
                </div>

                <!-- Academic Years Grid -->
                <div id="academicYearsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                    <!-- Academic years will be loaded here -->
                </div>
            </div>            <!-- Tab Content: Classes & Sections -->
            <div id="classes-sections" class="tab-content">
                <!-- Classes & Sections Header -->
                <div class="section-header">
                    <h3 class="section-title">
                        <i class="fas fa-school"></i>
                        Classes & Sections Management
                    </h3>
                    <div class="section-actions">
                        <button class="btn btn-outline btn-sm" onclick="refreshClassesData()">
                            <i class="fas fa-sync-alt"></i>
                            Refresh
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="showAddClassModal()">
                            <i class="fas fa-plus"></i>
                            Add Class
                        </button>
                    </div>
                </div>

                <!-- Classes & Sections Statistics -->
                <div class="stats-grid" style="margin-bottom: 24px;">
                    <div class="stat-card">
                        <div class="stat-number" id="totalClassesCount">0</div>
                        <div class="stat-label">Total Classes</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="totalSectionsCount">0</div>
                        <div class="stat-label">Total Sections</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="avgSectionsPerClass">0</div>
                        <div class="stat-label">Avg Sections/Class</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="sectionsWithoutTeachers">0</div>
                        <div class="stat-label">Sections w/o Teachers</div>
                    </div>
                </div>

                <!-- Classes List -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Classes Overview</h4>
                        <div class="search-container">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="classesSearch" class="search-input" placeholder="Search classes...">
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Loading State -->
                        <div id="classesLoading" class="loading" style="display: none;">
                            <div class="spinner"></div>
                            <p>Loading classes...</p>
                        </div>

                        <!-- Classes Grid -->
                        <div id="classesGrid" class="classes-grid">
                            <!-- Classes will be populated here -->
                        </div>

                        <!-- Empty State -->
                        <div id="classesEmpty" class="empty-state" style="display: none;">
                            <i class="fas fa-school" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                            <h4>No Classes Found</h4>
                            <p>Start by adding your first class to organize students.</p>
                            <button class="btn btn-primary" onclick="showAddClassModal()">
                                <i class="fas fa-plus"></i>
                                Add First Class
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Subjects -->
            <div id="subjects" class="tab-content">
                <div class="subjects-container">
                    <!-- Subjects Header -->
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-book"></i>
                            Subjects Management
                        </h3>
                        <div class="section-actions">
                            <button class="btn btn-outline btn-sm" onclick="refreshSubjectsData()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="showBulkImportModal()">
                                <i class="fas fa-upload"></i>
                                Bulk Import
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="showAddSubjectModal()">
                                <i class="fas fa-plus"></i>
                                Add Subject
                            </button>
                        </div>
                    </div>

                    <!-- Subjects Statistics -->
                    <div class="stats-grid" style="margin-bottom: 24px;">
                        <div class="stat-card">
                            <div class="stat-number" id="totalSubjectsCount">0</div>
                            <div class="stat-label">Total Subjects</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="assignedSubjectsCount">0</div>
                            <div class="stat-label">Assigned to Classes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="unassignedSubjectsCount">0</div>
                            <div class="stat-label">Unassigned</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number" id="avgSubjectsPerClass">0</div>
                            <div class="stat-label">Avg per Class</div>
                        </div>
                    </div>

                    <!-- Subjects Toolbar -->
                    <div class="subjects-toolbar">
                        <div class="subjects-search">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" id="subjectsSearch" class="search-input" placeholder="Search subjects by name or code...">
                        </div>
                        <div class="subjects-filters">
                            <select id="subjectUsageFilter" class="filter-select">
                                <option value="">All Subjects</option>
                                <option value="assigned">Assigned to Classes</option>
                                <option value="unassigned">Unassigned</option>
                            </select>
                            <button class="btn btn-outline btn-sm" onclick="clearSubjectsFilters()">
                                <i class="fas fa-times"></i>
                                Clear
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Actions Bar -->
                    <div class="bulk-actions-bar" id="subjectsBulkActions">
                        <span class="selected-count" id="selectedSubjectsCount">0 subjects selected</span>
                        <div style="margin-left: auto; display: flex; gap: 8px;">
                            <button class="btn btn-outline btn-sm" onclick="clearSubjectSelection()">
                                <i class="fas fa-times"></i>
                                Clear Selection
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="bulkAssignSubjects()">
                                <i class="fas fa-link"></i>
                                Assign to Classes
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="bulkDeleteSubjects()" style="color: #e53e3e; border-color: #e53e3e;">
                                <i class="fas fa-trash"></i>
                                Delete Selected
                            </button>
                        </div>
                    </div>

                    <!-- Subjects Table -->
                    <div class="subjects-table-container">
                        <!-- Loading State -->
                        <div id="subjectsLoading" class="loading" style="display: none;">
                            <div class="spinner"></div>
                            <p>Loading subjects...</p>
                        </div>

                        <!-- Subjects Table -->
                        <table class="subjects-table" id="subjectsTable" style="display: none;">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">
                                        <input type="checkbox" id="selectAllSubjects" onchange="toggleAllSubjects(this)">
                                    </th>
                                    <th style="width: 80px;">Code</th>
                                    <th>Subject Name</th>
                                    <th style="width: 120px;">Classes</th>
                                    <th style="width: 100px;">Usage</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="subjectsTableBody">
                                <!-- Subjects will be populated here -->
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div id="subjectsEmpty" class="subjects-empty" style="display: none;">
                            <i class="fas fa-book"></i>
                            <h3>No Subjects Found</h3>
                            <p>Start by adding subjects to your academic curriculum.</p>
                            <button class="btn btn-primary" onclick="showAddSubjectModal()" style="margin-top: 16px;">
                                <i class="fas fa-plus"></i> Add First Subject
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Curriculum Mapping -->
            <div id="curriculum" class="tab-content">
                <div class="curriculum-container">
                    <!-- Curriculum Header -->
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-link"></i>
                            Curriculum Mapping
                        </h3>
                        <div class="section-actions">
                            <button class="btn btn-outline btn-sm" onclick="refreshCurriculumData()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="exportCurriculumMatrix()">
                                <i class="fas fa-download"></i>
                                Export Matrix
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="showBulkAssignmentModal()">
                                <i class="fas fa-magic"></i>
                                Bulk Assignment
                            </button>
                        </div>
                    </div>

                    <!-- Curriculum Summary -->
                    <div class="curriculum-summary">
                        <h4 style="margin-bottom: 16px; color: var(--text-primary);">
                            <i class="fas fa-chart-pie"></i>
                            Curriculum Overview
                        </h4>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <div class="summary-number" id="totalAssignments">0</div>
                                <div class="summary-label">Total Assignments</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="completionPercentage">0%</div>
                                <div class="summary-label">Coverage</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="classesWithSubjects">0</div>
                                <div class="summary-label">Classes with Subjects</div>
                            </div>
                            <div class="summary-item">
                                <div class="summary-number" id="subjectsAssigned">0</div>
                                <div class="summary-label">Subjects in Use</div>
                            </div>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" id="curriculumProgress" style="width: 0%;"></div>
                        </div>
                    </div>

                    <!-- Curriculum Toolbar -->
                    <div class="curriculum-toolbar">
                        <div class="curriculum-filters">
                            <select id="classFilterCurriculum" class="filter-select" onchange="filterCurriculumMatrix()">
                                <option value="">All Classes</option>
                            </select>
                            <select id="subjectFilterCurriculum" class="filter-select" onchange="filterCurriculumMatrix()">
                                <option value="">All Subjects</option>
                            </select>
                            <select id="assignmentStatusFilter" class="filter-select" onchange="filterCurriculumMatrix()">
                                <option value="">All Assignments</option>
                                <option value="assigned">Assigned Only</option>
                                <option value="unassigned">Unassigned Only</option>
                            </select>
                        </div>
                        <div class="matrix-controls">
                            <button class="btn btn-outline btn-sm" onclick="clearCurriculumFilters()">
                                <i class="fas fa-times"></i>
                                Clear Filters
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="toggleMatrixView()">
                                <i class="fas fa-expand-arrows-alt"></i>
                                <span id="matrixViewToggleText">Expand</span>
                            </button>
                        </div>
                    </div>

                    <!-- Bulk Assignment Bar -->
                    <div class="bulk-assignment-bar">
                        <h5 style="margin-bottom: 12px; color: var(--text-primary);">
                            <i class="fas fa-magic"></i>
                            Quick Assignment Tools
                        </h5>
                        <div class="bulk-assignment-form">
                            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                                <label class="form-label">Select Class:</label>
                                <select id="quickAssignClass" class="form-select">
                                    <option value="">Choose class...</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
                                <label class="form-label">Select Subject:</label>
                                <select id="quickAssignSubject" class="form-select">
                                    <option value="">Choose subject...</option>
                                </select>
                            </div>
                            <div class="quick-actions">
                                <button class="btn btn-primary btn-sm" onclick="quickAssignSubject()">
                                    <i class="fas fa-link"></i>
                                    Assign
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="quickUnassignSubject()">
                                    <i class="fas fa-unlink"></i>
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Curriculum Matrix -->
                    <div class="curriculum-matrix-container">
                        <!-- Loading State -->
                        <div id="curriculumLoading" class="loading" style="display: none;">
                            <div class="spinner"></div>
                            <p>Loading curriculum matrix...</p>
                        </div>

                        <!-- Matrix -->
                        <div class="matrix-scroll-container" id="matrixScrollContainer">
                            <table class="curriculum-matrix" id="curriculumMatrix" style="display: none;">
                                <thead id="matrixHeaders">
                                    <!-- Headers will be populated here -->
                                </thead>
                                <tbody id="matrixBody">
                                    <!-- Matrix content will be populated here -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div id="curriculumEmpty" class="subjects-empty" style="display: none;">
                            <i class="fas fa-link"></i>
                            <h3>No Curriculum Data Found</h3>
                            <p>Add classes and subjects first to create curriculum mappings.</p>
                            <div style="margin-top: 16px; display: flex; gap: 12px; justify-content: center;">
                                <button class="btn btn-outline" onclick="showTab('classes-sections')">
                                    <i class="fas fa-school"></i> Manage Classes
                                </button>
                                <button class="btn btn-outline" onclick="showTab('subjects')">
                                    <i class="fas fa-book"></i> Manage Subjects
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="curriculum-legend">
                        <div class="legend-item">
                            <div class="legend-icon legend-assigned">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Subject Assigned to Class</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon legend-unassigned">
                                <i class="fas fa-plus"></i>
                            </div>
                            <span>Click to Assign Subject</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-icon" style="background: #f0f9ff; color: var(--secondary-color); border: 1px solid var(--secondary-color);">
                                <i class="fas fa-mouse-pointer"></i>
                            </div>
                            <span>Hover for Details</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Reports -->
            <div id="reports" class="tab-content">
                <div class="reports-container">
                    <!-- Reports Header -->
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-chart-bar"></i>
                            Academic Reports & Analytics
                        </h3>
                        <div class="section-actions">
                            <button class="btn btn-outline btn-sm" onclick="refreshReportsData()">
                                <i class="fas fa-sync-alt"></i>
                                Refresh Data
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="scheduleReport()">
                                <i class="fas fa-clock"></i>
                                Schedule Report
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="exportAllReports()">
                                <i class="fas fa-download"></i>
                                Export All
                            </button>
                        </div>
                    </div>
            
                    <!-- Quick Stats Overview -->
                    <div class="overview-summary">
                        <h4><i class="fas fa-tachometer-alt"></i> Quick Overview</h4>
                        <p id="quickOverviewText">Loading academic structure statistics...</p>
                    </div>
            
                    <!-- Report Cards Grid -->
                    <div class="report-cards-grid">
                        <!-- Academic Structure Overview Report -->
                        <div class="report-card" onclick="showReport('structure-overview')">
                            <div class="report-card-header">
                                <div class="report-icon overview">
                                    <i class="fas fa-sitemap"></i>
                                </div>
                                <h4 class="report-title">Academic Structure Overview</h4>
                            </div>
                            <p class="report-description">
                                Comprehensive overview of all classes, sections, and organizational structure with detailed statistics.
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-clock"></i> Last updated: <span id="structureLastUpdate">-</span></span>
                                <div class="report-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); exportReport('structure-overview')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
            
                        <!-- Curriculum Coverage Report -->
                        <div class="report-card" onclick="showReport('curriculum-coverage')">
                            <div class="report-card-header">
                                <div class="report-icon curriculum">
                                    <i class="fas fa-graduation-cap"></i>
                                </div>
                                <h4 class="report-title">Curriculum Coverage</h4>
                            </div>
                            <p class="report-description">
                                Analysis of subject assignments across classes, curriculum completeness, and coverage gaps.
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-percentage"></i> Coverage: <span id="curriculumCoverage">-</span>%</span>
                                <div class="report-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); exportReport('curriculum-coverage')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
            
                        <!-- Subject Usage Analytics -->
                        <div class="report-card" onclick="showReport('subject-analytics')">
                            <div class="report-card-header">
                                <div class="report-icon analytics">
                                    <i class="fas fa-chart-pie"></i>
                                </div>
                                <h4 class="report-title">Subject Analytics</h4>
                            </div>
                            <p class="report-description">
                                Detailed analysis of subject distribution, usage patterns, and optimization recommendations.
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-book"></i> <span id="totalSubjectsReport">-</span> Subjects</span>
                                <div class="report-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); exportReport('subject-analytics')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
            
                        <!-- Class Utilization Report -->
                        <div class="report-card" onclick="showReport('class-utilization')">
                            <div class="report-card-header">
                                <div class="report-icon structure">
                                    <i class="fas fa-school"></i>
                                </div>
                                <h4 class="report-title">Class & Section Utilization</h4>
                            </div>
                            <p class="report-description">
                                Analysis of class capacity, section distribution, and resource utilization across the institution.
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-users"></i> <span id="totalClassesReport">-</span> Classes</span>
                                <div class="report-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); exportReport('class-utilization')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
            
                        <!-- Academic Year Progress -->
                        <div class="report-card" onclick="showReport('year-progress')">
                            <div class="report-card-header">
                                <div class="report-icon overview">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <h4 class="report-title">Academic Year Progress</h4>
                            </div>
                            <p class="report-description">
                                Timeline view of academic year milestones, progress tracking, and historical comparisons.
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-calendar"></i> Current Year: <span id="currentAcademicYear">-</span></span>
                                <div class="report-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); exportReport('year-progress')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
            
                        <!-- Data Validation Report -->
                        <div class="report-card" onclick="showReport('data-validation')">
                            <div class="report-card-header">
                                <div class="report-icon analytics">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h4 class="report-title">Data Validation & Integrity</h4>
                            </div>
                            <p class="report-description">
                                System validation report identifying data inconsistencies, missing assignments, and integrity issues.
                            </p>
                            <div class="report-meta">
                                <span><i class="fas fa-exclamation-triangle"></i> Issues: <span id="validationIssues">-</span></span>
                                <div class="report-actions">
                                    <button class="btn btn-outline btn-sm" onclick="event.stopPropagation(); exportReport('data-validation')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <!-- Report Viewer -->
                    <div class="reports-viewer" id="reportViewer">
                        <div class="report-viewer-header">
                            <h3 class="report-viewer-title" id="reportViewerTitle">
                                <i class="fas fa-chart-bar"></i>
                                Report Title
                            </h3>
                            <div class="report-viewer-actions">
                                <button class="btn btn-outline btn-sm" onclick="printReport()">
                                    <i class="fas fa-print"></i>
                                    Print
                                </button>
                                <button class="btn btn-outline btn-sm" onclick="exportCurrentReport()">
                                    <i class="fas fa-download"></i>
                                    Export
                                </button>
                                <button class="btn btn-secondary btn-sm" onclick="closeReportViewer()">
                                    <i class="fas fa-times"></i>
                                    Close
                                </button>
                            </div>
                        </div>
                        <div class="report-content" id="reportContent">
                            <!-- Report content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 6: Bulk Operations -->
            <div id="bulk-operations" class="tab-content">
                <div class="bulk-operations-header">
                    <h2>Bulk Operations</h2>
                    <p>Mass data operations, imports, exports, and batch assignments</p>
                </div>
            
                <!-- Bulk Operations Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">📊</div>
                        <div class="stat-info">
                            <h3 id="total-records">0</h3>
                            <p>Total Records</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⬆️</div>
                        <div class="stat-info">
                            <h3 id="pending-imports">0</h3>
                            <p>Pending Imports</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3 id="completed-operations">0</h3>
                            <p>Completed Today</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">⚠️</div>
                        <div class="stat-info">
                            <h3 id="failed-operations">0</h3>
                            <p>Failed Operations</p>
                        </div>
                    </div>
                </div>
            
                <!-- Bulk Operations Sections -->
                <div class="bulk-sections">
                    <!-- Import/Export Section -->
                    <div class="bulk-section">
                        <div class="section-header">
                            <h3>📥 Data Import/Export</h3>
                            <p>Import or export academic data in bulk</p>
                        </div>
                        <div class="section-content">
                            <div class="operation-grid">
                                <div class="operation-card">
                                    <h4>Import Academic Years</h4>
                                    <p>Import multiple academic years from CSV</p>
                                    <div class="file-upload">
                                        <input type="file" id="import-years" accept=".csv" style="display: none;">
                                        <button onclick="document.getElementById('import-years').click()" class="btn btn-primary">
                                            Choose CSV File
                                        </button>
                                        <span class="file-name" id="years-file-name"></span>
                                    </div>
                                    <button onclick="importData('academic_years')" class="btn btn-success" id="import-years-btn" disabled>
                                        Import Years
                                    </button>
                                </div>
            
                                <div class="operation-card">
                                    <h4>Import Classes & Sections</h4>
                                    <p>Import class and section data from CSV</p>
                                    <div class="file-upload">
                                        <input type="file" id="import-classes" accept=".csv" style="display: none;">
                                        <button onclick="document.getElementById('import-classes').click()" class="btn btn-primary">
                                            Choose CSV File
                                        </button>
                                        <span class="file-name" id="classes-file-name"></span>
                                    </div>
                                    <button onclick="importData('classes')" class="btn btn-success" id="import-classes-btn" disabled>
                                        Import Classes
                                    </button>
                                </div>
            
                                <div class="operation-card">
                                    <h4>Import Subjects</h4>
                                    <p>Import subject data from CSV</p>
                                    <div class="file-upload">
                                        <input type="file" id="import-subjects" accept=".csv" style="display: none;">
                                        <button onclick="document.getElementById('import-subjects').click()" class="btn btn-primary">
                                            Choose CSV File
                                        </button>
                                        <span class="file-name" id="subjects-file-name"></span>
                                    </div>
                                    <button onclick="importData('subjects')" class="btn btn-success" id="import-subjects-btn" disabled>
                                        Import Subjects
                                    </button>
                                </div>
            
                                <div class="operation-card">
                                    <h4>Export All Data</h4>
                                    <p>Export complete academic structure</p>
                                    <div class="export-options">
                                        <label>
                                            <input type="checkbox" id="export-years" checked> Academic Years
                                        </label>
                                        <label>
                                            <input type="checkbox" id="export-classes" checked> Classes & Sections
                                        </label>
                                        <label>
                                            <input type="checkbox" id="export-subjects" checked> Subjects
                                        </label>
                                        <label>
                                            <input type="checkbox" id="export-curriculum" checked> Curriculum Mapping
                                        </label>
                                    </div>
                                    <button onclick="exportData()" class="btn btn-primary">
                                        Export Selected Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <!-- Batch Operations Section -->
                    <div class="bulk-section">
                        <div class="section-header">
                            <h3>⚡ Batch Operations</h3>
                            <p>Perform batch operations on existing data</p>
                        </div>
                        <div class="section-content">
                            <div class="operation-grid">
                                <div class="operation-card">
                                    <h4>Batch Subject Assignment</h4>
                                    <p>Assign subjects to multiple classes at once</p>
                                    <div class="batch-form">
                                        <div class="form-group">
                                            <label>Select Academic Year:</label>
                                            <select id="batch-year" class="form-control">
                                                <option value="">Select Year...</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Classes:</label>
                                            <div id="batch-classes" class="checkbox-grid">
                                                <!-- Classes will be loaded here -->
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>Select Subjects:</label>
                                            <div id="batch-subjects" class="checkbox-grid">
                                                <!-- Subjects will be loaded here -->
                                            </div>
                                        </div>
                                        <button onclick="performBatchAssignment()" class="btn btn-success">
                                            Assign Subjects
                                        </button>
                                    </div>
                                </div>
            
                                <div class="operation-card">
                                    <h4>Duplicate Year Structure</h4>
                                    <p>Copy complete structure from one year to another</p>
                                    <div class="batch-form">
                                        <div class="form-group">
                                            <label>Source Academic Year:</label>
                                            <select id="source-year" class="form-control">
                                                <option value="">Select Source Year...</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Target Academic Year:</label>
                                            <select id="target-year" class="form-control">
                                                <option value="">Select Target Year...</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>What to Copy:</label>
                                            <div class="checkbox-grid">
                                                <label>
                                                    <input type="checkbox" id="copy-classes" checked> Classes & Sections
                                                </label>
                                                <label>
                                                    <input type="checkbox" id="copy-curriculum" checked> Subject Assignments
                                                </label>
                                            </div>
                                        </div>
                                        <button onclick="duplicateYearStructure()" class="btn btn-warning">
                                            Duplicate Structure
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <!-- Data Validation Section -->
                    <div class="bulk-section">
                        <div class="section-header">
                            <h3>🔍 Data Validation & Cleanup</h3>
                            <p>Validate and clean up academic data</p>
                        </div>
                        <div class="section-content">
                            <div class="operation-grid">
                                <div class="operation-card">
                                    <h4>Validate Data Integrity</h4>
                                    <p>Check for inconsistencies and orphaned records</p>
                                    <div id="validation-results" class="validation-results" style="display: none;">
                                        <!-- Validation results will appear here -->
                                    </div>
                                    <button onclick="validateDataIntegrity()" class="btn btn-info">
                                        Run Validation
                                    </button>
                                </div>
            
                                <div class="operation-card">
                                    <h4>Cleanup Orphaned Records</h4>
                                    <p>Remove records with missing dependencies</p>
                                    <div id="cleanup-results" class="cleanup-results" style="display: none;">
                                        <!-- Cleanup results will appear here -->
                                    </div>
                                    <button onclick="cleanupOrphanedRecords()" class="btn btn-danger">
                                        Cleanup Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
            
                    <!-- Operation History Section -->
                    <div class="bulk-section">
                        <div class="section-header">
                            <h3>📋 Operation History</h3>
                            <p>View recent bulk operations and their status</p>
                        </div>
                        <div class="section-content">
                            <div class="history-table-container">
                                <table class="history-table">
                                    <thead>
                                        <tr>
                                            <th>Date/Time</th>
                                            <th>Operation</th>
                                            <th>Type</th>
                                            <th>Records</th>
                                            <th>Status</th>
                                            <th>Duration</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="operation-history">
                                        <!-- Operation history will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Academic Year Modal -->
    <div class="modal-overlay" id="addAcademicYearModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3 id="academicYearModalTitle">Add Academic Year</h3>
                <button class="modal-close" onclick="closeModal('addAcademicYearModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="academicYearForm">
                    <input type="hidden" id="academicYearId" name="id">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="academicYearName" class="form-label required">Academic Year Name</label>
                            <input type="text" id="academicYearName" name="name" class="form-input" 
                                   placeholder="e.g., 2024-2025" required>
                        </div>
                        <div class="form-group">
                            <label for="startDate" class="form-label required">Start Date</label>
                            <input type="date" id="startDate" name="start_date" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate" class="form-label required">End Date</label>
                            <input type="date" id="endDate" name="end_date" class="form-input" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal('addAcademicYearModal')">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i>
                            <span id="saveButtonText">Save Academic Year</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        const userRole = <?php echo json_encode($user_role); ?>;
        const classes = <?php echo json_encode($classes); ?>;
        const subjects = <?php echo json_encode($subjects); ?>;
        const academic_years = <?php echo json_encode($academic_years); ?>;

        // Initialize the application
        document.addEventListener('DOMContentLoaded', function() {
            initializeApp();
        });        function initializeApp() {
            // Check for URL parameter to determine which tab to show
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                // Show the specified tab
                showTab(tabParam);
            } else {
                // Default behavior - load academic years tab
                loadAcademicYears();
            }
            
            // Always load all data
            loadClassesData(); 
            loadSubjectsData();
            loadCurriculumData();
            loadReportsData();
            setupEventListeners();
        }

        function setupEventListeners() {
            // Academic Year form submission
            document.getElementById('academicYearForm').addEventListener('submit', handleAcademicYearSubmit);
        }

        // ========== TAB MANAGEMENT ==========

        function showTab(tabName) {
            // Remove active class from all tabs and contents
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Add active class to the specified tab
            const targetButton = document.querySelector(`[data-tab="${tabName}"]`);
            const targetContent = document.getElementById(tabName);
            
            if (targetButton && targetContent) {
                targetButton.classList.add('active');
                targetContent.classList.add('active');
                
                // Load data based on tab
                switch(tabName) {
                    case 'academic-years':
                        loadAcademicYears();
                        break;
                    case 'classes-sections':
                        loadClassesData();
                        break;
                    case 'subjects':
                        loadSubjectsData();
                        break;
                    case 'curriculum':
                        loadCurriculumData();
                        break;
                    case 'reports':
                        loadReportsData();
                        break;
                    case 'bulk-operations':
                        initializeBulkOperationsTab();
                        break;
                    default:
                        console.warn('Unknown tab:', tabName);
                }
            }
        }

        // ========== ACADEMIC YEARS MANAGEMENT ==========

        function loadAcademicYears() {
            showLoading('academicYearsLoading', 'academicYearsGrid');
            
            fetch('academic_management_api.php?action=get_academic_years')
                .then(response => response.json())
                .then(data => {
                    hideLoading('academicYearsLoading', 'academicYearsGrid');
                    
                    if (data.success) {
                        displayAcademicYears(data.data);
                    } else {
                        showNotification('Failed to load academic years: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideLoading('academicYearsLoading', 'academicYearsGrid');
                    showNotification('Error loading academic years: ' + error.message, 'error');
                });
        }

        function displayAcademicYears(academicYears) {
            const grid = document.getElementById('academicYearsGrid');
            
            if (!academicYears || academicYears.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                        <i class="fas fa-calendar-alt" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 16px;"></i>
                        <h4 style="color: var(--text-secondary); margin-bottom: 8px;">No Academic Years Found</h4>
                        <p style="color: var(--text-secondary); margin-bottom: 20px;">Get started by adding your first academic year.</p>
                        <button class="btn btn-primary" onclick="openAddAcademicYearModal()">
                            <i class="fas fa-plus"></i>
                            Add Academic Year
                        </button>
                    </div>
                `;
                return;
            }

            grid.innerHTML = academicYears.map(year => {
                const today = new Date();
                const startDate = new Date(year.start_date);
                const endDate = new Date(year.end_date);
                const isCurrent = year.is_current == 1;
                const isActive = today >= startDate && today <= endDate;
                
                const statusClass = isCurrent ? 'current' : (isActive ? 'active' : 'inactive');
                const statusText = isCurrent ? 'Current' : (isActive ? 'Active' : 'Inactive');
                const statusColor = isCurrent ? '#26e7a6' : (isActive ? '#4299e1' : '#a0aec0');

                return `
                    <div class="academic-year-card" style="
                        background: var(--bg-white);
                        border-radius: var(--border-radius);
                        box-shadow: var(--shadow-sm);
                        overflow: hidden;
                        transition: transform 0.2s, box-shadow 0.2s;
                        border-left: 4px solid ${statusColor};
                    ">
                        <div style="padding: 20px;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                <h4 style="margin: 0; color: var(--text-primary); font-size: 1.2rem;">${year.name}</h4>
                                <span style="
                                    background: ${statusColor}20;
                                    color: ${statusColor};
                                    padding: 4px 8px;
                                    border-radius: 12px;
                                    font-size: 12px;
                                    font-weight: 500;
                                ">${statusText}</span>
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                <p style="margin: 0; color: var(--text-secondary); font-size: 14px;">
                                    <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i>
                                    ${formatDate(year.start_date)} - ${formatDate(year.end_date)}
                                </p>
                                <p style="margin: 4px 0 0 0; color: var(--text-secondary); font-size: 14px;">
                                    <i class="fas fa-clock" style="margin-right: 6px;"></i>
                                    ${year.duration_days} days
                                </p>
                            </div>
                            
                            <div style="display: flex; gap: 8px;">
                                <button class="btn btn-outline" onclick="editAcademicYear(${year.id})" style="flex: 1; font-size: 12px;">
                                    <i class="fas fa-edit"></i>
                                    Edit
                                </button>
                                ${!isCurrent ? `
                                    <button class="btn btn-secondary" onclick="setCurrentAcademicYear(${year.id})" style="flex: 1; font-size: 12px;">
                                        <i class="fas fa-check"></i>
                                        Set Current
                                    </button>
                                ` : ''}
                                <button class="btn" onclick="deleteAcademicYear(${year.id})" style="
                                    background: #f56565;
                                    color: white;
                                    font-size: 12px;
                                    padding: 8px 12px;
                                ">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        function openAddAcademicYearModal() {
            document.getElementById('academicYearModalTitle').textContent = 'Add Academic Year';
            document.getElementById('saveButtonText').textContent = 'Save Academic Year';
            document.getElementById('academicYearForm').reset();
            document.getElementById('academicYearId').value = '';
            openModal('addAcademicYearModal');
        }

        function editAcademicYear(id) {
            // Find the academic year data
            fetch(`academic_management_api.php?action=get_academic_years`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const year = data.data.find(y => y.id == id);
                        if (year) {
                            document.getElementById('academicYearModalTitle').textContent = 'Edit Academic Year';
                            document.getElementById('saveButtonText').textContent = 'Update Academic Year';
                            document.getElementById('academicYearId').value = year.id;
                            document.getElementById('academicYearName').value = year.name;
                            document.getElementById('startDate').value = year.start_date;
                            document.getElementById('endDate').value = year.end_date;
                            openModal('addAcademicYearModal');
                        }
                    }
                });
        }

        function handleAcademicYearSubmit(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const isEdit = formData.get('id');
            
            formData.append('action', isEdit ? 'update_academic_year' : 'add_academic_year');
            
            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('addAcademicYearModal');
                    loadAcademicYears();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function setCurrentAcademicYear(id) {
            if (!confirm('Are you sure you want to set this as the current academic year?')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'set_current_academic_year');
            formData.append('id', id);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadAcademicYears();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function deleteAcademicYear(id) {
            if (!confirm('Are you sure you want to delete this academic year? This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_academic_year');
            formData.append('id', id);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadAcademicYears();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        // ========== CLASSES & SECTIONS MANAGEMENT ==========

        function loadClassesData() {
            showLoading('classesLoading', 'classesGrid');
            
            fetch('academic_management_api.php?action=get_classes')
                .then(response => response.json())
                .then(data => {
                    hideLoading('classesLoading', 'classesGrid');
                    if (data.success) {
                        displayClasses(data.data);
                        updateClassesStatistics(data.data);
                    } else {
                        showNotification('Error loading classes: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideLoading('classesLoading', 'classesGrid');
                    showNotification('Error: ' + error.message, 'error');
                });
        }

        function displayClasses(classes) {
            const grid = document.getElementById('classesGrid');
            
            if (!classes || classes.length === 0) {
                grid.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-secondary);">
                        <i class="fas fa-school" style="font-size: 3rem; margin-bottom: 16px; opacity: 0.3;"></i>
                        <h3 style="margin-bottom: 8px;">No Classes Found</h3>
                        <p>Start by adding your first class to the system.</p>
                        <button class="btn btn-primary" onclick="showAddClassModal()" style="margin-top: 16px;">
                            <i class="fas fa-plus"></i> Add First Class
                        </button>
                    </div>
                `;
                return;
            }

            grid.innerHTML = classes.map(classItem => `
                <div class="class-card" data-class-id="${classItem.id}">
                    <div class="class-header">
                        <h3 class="class-name">Class ${classItem.name}</h3>
                        <div class="class-actions">
                            <button class="btn btn-outline btn-sm" onclick="editClass(${classItem.id}, '${classItem.name}')" title="Edit Class">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="deleteClass(${classItem.id})" title="Delete Class">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="sections-list">
                        <div class="sections-title">Sections (${classItem.section_count})</div>
                        <div class="sections-grid">
                            ${classItem.sections ? classItem.sections.split(',').map(section => `
                                <div class="section-badge" onclick="viewSectionDetails(${classItem.id}, '${section}')">
                                    ${section}
                                </div>
                            `).join('') : ''}
                            <div class="add-section-btn" onclick="showAddSectionModal(${classItem.id})">
                                <i class="fas fa-plus"></i> Add Section
                            </div>
                        </div>
                    </div>
                    
                    <div class="class-stats">
                        <span><i class="fas fa-users"></i> ${classItem.section_count} Section${classItem.section_count !== '1' ? 's' : ''}</span>
                        <span><i class="fas fa-book"></i> Subjects: TBD</span>
                    </div>
                </div>
            `).join('');
        }

        function updateClassesStatistics(classes) {
            const totalClasses = classes.length;
            const totalSections = classes.reduce((sum, cls) => sum + parseInt(cls.section_count || 0), 0);
            const avgSections = totalClasses > 0 ? (totalSections / totalClasses).toFixed(1) : 0;
            
            document.getElementById('totalClassesCount').textContent = totalClasses;
            document.getElementById('totalSectionsCount').textContent = totalSections;
            document.getElementById('avgSectionsPerClass').textContent = avgSections;
            document.getElementById('sectionsWithoutTeachers').textContent = '0'; // Will be updated when sections data is loaded
        }

        function refreshClassesData() {
            loadClassesData();
            showNotification('Classes data refreshed', 'info');
        }

        function showAddClassModal() {
            const modalHtml = `
                <div class="modal-overlay" id="addClassModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="fas fa-school"></i> Add New Class</h3>
                            <button class="modal-close" onclick="closeModal('addClassModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <form id="addClassForm" onsubmit="submitAddClass(event)">
                                <div class="form-group">
                                    <label class="form-label required">Class Name</label>
                                    <input type="text" name="name" class="form-input" placeholder="e.g., I, II, III, IV..." required>
                                    <small class="help-text">Enter the class level (Roman numerals recommended)</small>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('addClassModal')">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Class
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('addClassModal');
        }

        function submitAddClass(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'add_class');

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('addClassModal');
                    document.getElementById('addClassModal').remove();
                    loadClassesData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function editClass(id, currentName) {
            const modalHtml = `
                <div class="modal-overlay" id="editClassModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="fas fa-edit"></i> Edit Class</h3>
                            <button class="modal-close" onclick="closeModal('editClassModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <form id="editClassForm" onsubmit="submitEditClass(event, ${id})">
                                <div class="form-group">
                                    <label class="form-label required">Class Name</label>
                                    <input type="text" name="name" class="form-input" value="${currentName}" required>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('editClassModal')">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Class
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('editClassModal');
        }

        function submitEditClass(event, id) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'update_class');
            formData.append('id', id);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('editClassModal');
                    document.getElementById('editClassModal').remove();
                    loadClassesData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function deleteClass(id) {
            if (!confirm('Are you sure you want to delete this class? This will also delete all its sections. This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_class');
            formData.append('id', id);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadClassesData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function showAddSectionModal(classId) {
            // Get class name for display
            const classCard = document.querySelector(`[data-class-id="${classId}"]`);
            const className = classCard.querySelector('.class-name').textContent;
            
            const modalHtml = `
                <div class="modal-overlay" id="addSectionModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="fas fa-plus"></i> Add Section to ${className}</h3>
                            <button class="modal-close" onclick="closeModal('addSectionModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <form id="addSectionForm" onsubmit="submitAddSection(event, ${classId})">
                                <div class="form-group">
                                    <label class="form-label required">Section Name</label>
                                    <input type="text" name="name" class="form-input" placeholder="e.g., A, B, C..." maxlength="1" required>
                                    <small class="help-text">Enter a single letter for the section</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Capacity</label>
                                    <input type="number" name="capacity" class="form-input" placeholder="e.g., 30" min="1" max="100">
                                    <small class="help-text">Maximum number of students (optional)</small>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('addSectionModal')">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Section
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('addSectionModal');
        }

        function submitAddSection(event, classId) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'add_section');
            formData.append('class_id', classId);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('addSectionModal');
                    document.getElementById('addSectionModal').remove();
                    loadClassesData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function viewSectionDetails(classId, sectionName) {
            // This will be implemented when we add detailed section management
            showNotification(`Section details for ${sectionName} - Coming soon!`, 'info');
        }

        // ========== SUBJECTS MANAGEMENT ==========

        let selectedSubjects = new Set();

        function loadSubjectsData() {
            showSubjectsLoading();
            
            fetch('academic_management_api.php?action=get_subjects')
                .then(response => response.json())
                .then(data => {
                    hideSubjectsLoading();
                    if (data.success) {
                        displaySubjects(data.data);
                        updateSubjectsStatistics(data.data);
                    } else {
                        showNotification('Error loading subjects: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideSubjectsLoading();
                    showNotification('Error: ' + error.message, 'error');
                });
        }

        function displaySubjects(subjects) {
            const tableBody = document.getElementById('subjectsTableBody');
            const table = document.getElementById('subjectsTable');
            const emptyState = document.getElementById('subjectsEmpty');
            
            if (!subjects || subjects.length === 0) {
                table.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            table.style.display = 'table';
            emptyState.style.display = 'none';

            tableBody.innerHTML = subjects.map(subject => `
                <tr data-subject-id="${subject.id}">
                    <td>
                        <input type="checkbox" class="subject-checkbox" value="${subject.id}" 
                            onchange="toggleSubjectSelection(${subject.id}, this.checked)">
                    </td>
                    <td>
                        <span class="subject-code">${subject.code}</span>
                    </td>
                    <td>
                        <div class="subject-name">${subject.name}</div>
                        <div class="subject-meta">
                            <span><i class="fas fa-hashtag"></i> ID: ${subject.id}</span>
                        </div>
                    </td>
                    <td>
                        <span class="subject-usage-badge ${parseInt(subject.class_count) === 0 ? 'no-usage' : ''}">
                            ${subject.class_count} class${subject.class_count !== '1' ? 'es' : ''}
                        </span>
                    </td>
                    <td>
                        ${subject.classes ? `
                            <small style="color: var(--text-secondary);">${subject.classes.substring(0, 30)}${subject.classes.length > 30 ? '...' : ''}</small>
                        ` : '<small style="color: var(--text-secondary);">Not assigned</small>'}
                    </td>
                    <td>
                        <div class="subjects-actions">
                            <button class="btn btn-outline btn-sm" onclick="editSubject(${subject.id}, '${subject.name.replace(/'/g, "\\'")}', '${subject.code}')" title="Edit Subject">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="viewSubjectDetails(${subject.id})" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-outline btn-sm" onclick="deleteSubject(${subject.id})" title="Delete Subject" style="color: #e53e3e;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `).join('');

            // Clear selection
            selectedSubjects.clear();
            updateBulkActionsVisibility();
        }

        function updateSubjectsStatistics(subjects) {
            const totalSubjects = subjects.length;
            const assignedSubjects = subjects.filter(s => parseInt(s.class_count) > 0).length;
            const unassignedSubjects = totalSubjects - assignedSubjects;
            
            // Calculate average subjects per class
            const totalAssignments = subjects.reduce((sum, s) => sum + parseInt(s.class_count || 0), 0);
            const avgSubjectsPerClass = totalAssignments > 0 ? (totalAssignments / totalSubjects).toFixed(1) : 0;
            
            document.getElementById('totalSubjectsCount').textContent = totalSubjects;
            document.getElementById('assignedSubjectsCount').textContent = assignedSubjects;
            document.getElementById('unassignedSubjectsCount').textContent = unassignedSubjects;
            document.getElementById('avgSubjectsPerClass').textContent = avgSubjectsPerClass;
        }

        function showSubjectsLoading() {
            document.getElementById('subjectsLoading').style.display = 'flex';
            document.getElementById('subjectsTable').style.display = 'none';
            document.getElementById('subjectsEmpty').style.display = 'none';
        }

        function hideSubjectsLoading() {
            document.getElementById('subjectsLoading').style.display = 'none';
        }

        function refreshSubjectsData() {
            loadSubjectsData();
            showNotification('Subjects data refreshed', 'info');
        }

        function showAddSubjectModal() {
            const modalHtml = `
                <div class="modal-overlay" id="addSubjectModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="fas fa-book"></i> Add New Subject</h3>
                            <button class="modal-close" onclick="closeModal('addSubjectModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <form id="addSubjectForm" onsubmit="submitAddSubject(event)">
                                <div class="form-group">
                                    <label class="form-label required">Subject Name</label>
                                    <input type="text" name="name" class="form-input" placeholder="e.g., Mathematics, English Literature" required>
                                    <small class="help-text">Enter the full subject name</small>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Subject Code</label>
                                    <input type="text" name="code" class="form-input" placeholder="e.g., MATH, ENG" maxlength="10" required>
                                    <small class="help-text">Short code for the subject (2-10 characters)</small>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('addSubjectModal')">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Add Subject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('addSubjectModal');
        }

        function submitAddSubject(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'add_subject');

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('addSubjectModal');
                    document.getElementById('addSubjectModal').remove();
                    loadSubjectsData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function editSubject(id, currentName, currentCode) {
            const modalHtml = `
                <div class="modal-overlay" id="editSubjectModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="fas fa-edit"></i> Edit Subject</h3>
                            <button class="modal-close" onclick="closeModal('editSubjectModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <form id="editSubjectForm" onsubmit="submitEditSubject(event, ${id})">
                                <div class="form-group">
                                    <label class="form-label required">Subject Name</label>
                                    <input type="text" name="name" class="form-input" value="${currentName}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label required">Subject Code</label>
                                    <input type="text" name="code" class="form-input" value="${currentCode}" maxlength="10" required>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('editSubjectModal')">Cancel</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Subject
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('editSubjectModal');
        }

        function submitEditSubject(event, id) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'update_subject');
            formData.append('id', id);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('editSubjectModal');
                    document.getElementById('editSubjectModal').remove();
                    loadSubjectsData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function deleteSubject(id) {
            if (!confirm('Are you sure you want to delete this subject? This will remove it from all classes. This action cannot be undone.')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'delete_subject');
            formData.append('id', id);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    loadSubjectsData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function viewSubjectDetails(id) {
            // This will show detailed information about the subject
            showNotification('Subject details view - Coming soon!', 'info');
        }

        // ========== BULK OPERATIONS FOR SUBJECTS ==========

        function toggleSubjectSelection(id, checked) {
            if (checked) {
                selectedSubjects.add(id);
            } else {
                selectedSubjects.delete(id);
            }
            updateBulkActionsVisibility();
            updateSelectAllState();
        }

        function toggleAllSubjects(selectAllCheckbox) {
            const checkboxes = document.querySelectorAll('.subject-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
                const id = parseInt(checkbox.value);
                
                if (selectAllCheckbox.checked) {
                    selectedSubjects.add(id);
                } else {
                    selectedSubjects.delete(id);
                }
            });
            
            updateBulkActionsVisibility();
        }

        function updateBulkActionsVisibility() {
            const bulkActionsBar = document.getElementById('subjectsBulkActions');
            const selectedCount = document.getElementById('selectedSubjectsCount');
            
            if (selectedSubjects.size > 0) {
                bulkActionsBar.classList.add('show');
                selectedCount.textContent = `${selectedSubjects.size} subject${selectedSubjects.size !== 1 ? 's' : ''} selected`;
            } else {
                bulkActionsBar.classList.remove('show');
            }
        }

        function updateSelectAllState() {
            const selectAllCheckbox = document.getElementById('selectAllSubjects');
            const totalCheckboxes = document.querySelectorAll('.subject-checkbox').length;
            
            if (selectedSubjects.size === 0) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = false;
            } else if (selectedSubjects.size === totalCheckboxes) {
                selectAllCheckbox.indeterminate = false;
                selectAllCheckbox.checked = true;
            } else {
                selectAllCheckbox.indeterminate = true;
            }
        }

        function clearSubjectSelection() {
            selectedSubjects.clear();
            document.querySelectorAll('.subject-checkbox').forEach(cb => cb.checked = false);
            document.getElementById('selectAllSubjects').checked = false;
            document.getElementById('selectAllSubjects').indeterminate = false;
            updateBulkActionsVisibility();
        }

        function bulkAssignSubjects() {
            if (selectedSubjects.size === 0) {
                showNotification('Please select subjects to assign', 'error');
                return;
            }
            
            // This will be implemented in the next tab (Curriculum Mapping)
            showNotification('Bulk subject assignment - Coming in Curriculum Mapping tab!', 'info');
        }

        function bulkDeleteSubjects() {
            if (selectedSubjects.size === 0) {
                showNotification('Please select subjects to delete', 'error');
                return;
            }
            
            if (!confirm(`Are you sure you want to delete ${selectedSubjects.size} selected subject${selectedSubjects.size !== 1 ? 's' : ''}? This action cannot be undone.`)) {
                return;
            }
            
            // Implementation for bulk delete would go here
            showNotification('Bulk delete functionality - Coming soon!', 'info');
        }

        function clearSubjectsFilters() {
            document.getElementById('subjectsSearch').value = '';
            document.getElementById('subjectUsageFilter').value = '';
            loadSubjectsData();
        }

        function showBulkImportModal() {
            const modalHtml = `
                <div class="modal-overlay" id="bulkImportModal">
                    <div class="modal-container">
                        <div class="modal-header">
                            <h3><i class="fas fa-upload"></i> Bulk Import Subjects</h3>
                            <button class="modal-close" onclick="closeModal('bulkImportModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <div style="margin-bottom: 20px; padding: 16px; background: #f0f9ff; border-radius: 6px; border-left: 4px solid var(--secondary-color);">
                                <h4 style="margin-bottom: 8px; color: var(--text-primary);">CSV Format Requirements:</h4>
                                <p style="margin-bottom: 8px; font-size: 0.875rem;">Your CSV file should have the following columns:</p>
                                <code style="background: white; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">name,code</code>
                                <p style="margin-top: 8px; font-size: 0.875rem;">Example: <code>Mathematics,MATH</code></p>
                            </div>
                            
                            <form id="bulkImportForm" onsubmit="submitBulkImport(event)">
                                <div class="form-group">
                                    <label class="form-label required">CSV File</label>
                                    <input type="file" name="csv_file" class="form-input" accept=".csv" required>
                                    <small class="help-text">Select a CSV file with subject data</small>
                                </div>
                                <div class="form-actions">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('bulkImportModal')">Cancel</button>
                                    <button type="button" class="btn btn-outline" onclick="downloadSampleCSV()">
                                        <i class="fas fa-download"></i> Download Sample
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload"></i> Import Subjects
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('bulkImportModal');
        }

        function submitBulkImport(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            formData.append('action', 'bulk_import_subjects');

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('bulkImportModal');
                    document.getElementById('bulkImportModal').remove();
                    loadSubjectsData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function downloadSampleCSV() {
            const csvContent = `name,code
        Mathematics,MATH
        English Literature,ENG
        Science,SCI
        Social Studies,SS
        Physical Education,PE
        Computer Science,CS
        Art,ART
        Music,MUS`;
            
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'subjects_sample.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        }

        // ========== CURRICULUM MAPPING ==========

        let curriculumData = [];
        let classesData = [];
        let subjectsData = [];

        function loadCurriculumData() {
            showCurriculumLoading();
            
            // Load curriculum overview data
            fetch('academic_management_api.php?action=get_curriculum_overview')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        curriculumData = data.data;
                        loadSupportingData();
                    } else {
                        hideCurriculumLoading();
                        showNotification('Error loading curriculum data: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideCurriculumLoading();
                    showNotification('Error: ' + error.message, 'error');
                });
        }

        function loadSupportingData() {
            Promise.all([
                fetch('academic_management_api.php?action=get_classes').then(r => r.json()),
                fetch('academic_management_api.php?action=get_subjects').then(r => r.json())
            ])
            .then(([classesResponse, subjectsResponse]) => {
                hideCurriculumLoading();
                
                if (classesResponse.success && subjectsResponse.success) {
                    classesData = classesResponse.data;
                    subjectsData = subjectsResponse.data;
                    
                    populateCurriculumFilters();
                    displayCurriculumMatrix();
                    updateCurriculumSummary();
                } else {
                    showNotification('Error loading supporting data', 'error');
                }
            })
            .catch(error => {
                hideCurriculumLoading();
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function displayCurriculumMatrix() {
            const matrix = document.getElementById('curriculumMatrix');
            const emptyState = document.getElementById('curriculumEmpty');
            
            if (!classesData || classesData.length === 0 || !subjectsData || subjectsData.length === 0) {
                matrix.style.display = 'none';
                emptyState.style.display = 'block';
                return;
            }

            matrix.style.display = 'table';
            emptyState.style.display = 'none';

            // Create headers
            const headers = document.getElementById('matrixHeaders');
            headers.innerHTML = `
                <tr>
                    <th class="class-header" style="position: sticky; left: 0; z-index: 15;">Class</th>
                    ${subjectsData.map(subject => `
                        <th class="subject-header" title="${subject.name}">
                            ${subject.code}
                        </th>
                    `).join('')}
                </tr>
            `;

            // Create matrix body
            const matrixBody = document.getElementById('matrixBody');
            matrixBody.innerHTML = classesData.map(classItem => {
                return `
                    <tr data-class-id="${classItem.id}">
                        <td class="class-name">
                            <strong>Class ${classItem.name}</strong>
                            <div class="class-summary">${classItem.section_count || 0} sections</div>
                        </td>
                        ${subjectsData.map(subject => {
                            const isAssigned = curriculumData.some(item => 
                                parseInt(item.class_id) === parseInt(classItem.id) && 
                                parseInt(item.subject_id) === parseInt(subject.id) && 
                                parseInt(item.is_assigned) === 1
                            );
                            
                            return `
                                <td class="assignment-cell ${isAssigned ? 'assigned' : 'unassigned'}" 
                                    data-class-id="${classItem.id}" 
                                    data-subject-id="${subject.id}"
                                    onclick="toggleAssignment(${classItem.id}, ${subject.id})"
                                    title="${isAssigned ? 'Click to remove' : 'Click to assign'} ${subject.name} ${isAssigned ? 'from' : 'to'} Class ${classItem.name}">
                                    <i class="fas ${isAssigned ? 'fa-check' : 'fa-plus'} assignment-icon"></i>
                                </td>
                            `;
                        }).join('')}
                    </tr>
                `;
            }).join('');
        }

        function toggleAssignment(classId, subjectId) {
            const cell = document.querySelector(`[data-class-id="${classId}"][data-subject-id="${subjectId}"]`);
            const isCurrentlyAssigned = cell.classList.contains('assigned');
            const action = isCurrentlyAssigned ? 'remove_subject_from_class' : 'assign_subject_to_class';
            
            // Optimistic UI update
            cell.style.opacity = '0.5';
            cell.style.pointerEvents = 'none';
            
            const formData = new FormData();
            formData.append('action', action);
            formData.append('class_id', classId);
            formData.append('subject_id', subjectId);

            fetch('academic_management_api.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    if (isCurrentlyAssigned) {
                        cell.classList.remove('assigned');
                        cell.classList.add('unassigned');
                        cell.innerHTML = '<i class="fas fa-plus assignment-icon"></i>';
                        cell.title = cell.title.replace('Click to remove', 'Click to assign').replace('from', 'to');
                    } else {
                        cell.classList.remove('unassigned');
                        cell.classList.add('assigned');
                        cell.innerHTML = '<i class="fas fa-check assignment-icon"></i>';
                        cell.title = cell.title.replace('Click to assign', 'Click to remove').replace('to', 'from');
                    }
                    
                    // Update curriculum data
                    const dataIndex = curriculumData.findIndex(item => 
                        parseInt(item.class_id) === parseInt(classId) && 
                        parseInt(item.subject_id) === parseInt(subjectId)
                    );
                    
                    if (dataIndex !== -1) {
                        curriculumData[dataIndex].is_assigned = isCurrentlyAssigned ? 0 : 1;
                    } else {
                        curriculumData.push({
                            class_id: classId,
                            subject_id: subjectId,
                            is_assigned: 1
                        });
                    }
                    
                    updateCurriculumSummary();
                    showNotification(data.message, 'success');
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
                
                // Restore cell state
                cell.style.opacity = '1';
                cell.style.pointerEvents = 'auto';
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
                cell.style.opacity = '1';
                cell.style.pointerEvents = 'auto';
            });
        }

        function updateCurriculumSummary() {
            if (!curriculumData || !classesData || !subjectsData) return;
            
            const totalPossibleAssignments = classesData.length * subjectsData.length;
            const totalAssignments = curriculumData.filter(item => parseInt(item.is_assigned) === 1).length;
            const completionPercentage = totalPossibleAssignments > 0 ? Math.round((totalAssignments / totalPossibleAssignments) * 100) : 0;
            
            // Count classes with at least one subject
            const classesWithSubjects = new Set(
                curriculumData.filter(item => parseInt(item.is_assigned) === 1).map(item => item.class_id)
            ).size;
            
            // Count subjects that are assigned to at least one class
            const subjectsAssigned = new Set(
                curriculumData.filter(item => parseInt(item.is_assigned) === 1).map(item => item.subject_id)
            ).size;
            
            document.getElementById('totalAssignments').textContent = totalAssignments;
            document.getElementById('completionPercentage').textContent = completionPercentage + '%';
            document.getElementById('classesWithSubjects').textContent = classesWithSubjects;
            document.getElementById('subjectsAssigned').textContent = subjectsAssigned;
            document.getElementById('curriculumProgress').style.width = completionPercentage + '%';
        }

        function populateCurriculumFilters() {
            // Populate class filter
            const classFilter = document.getElementById('classFilterCurriculum');
            const quickAssignClass = document.getElementById('quickAssignClass');
            
            const classOptions = classesData.map(cls => 
                `<option value="${cls.id}">Class ${cls.name}</option>`
            ).join('');
            
            classFilter.innerHTML = '<option value="">All Classes</option>' + classOptions;
            quickAssignClass.innerHTML = '<option value="">Choose class...</option>' + classOptions;
            
            // Populate subject filter
            const subjectFilter = document.getElementById('subjectFilterCurriculum');
            const quickAssignSubject = document.getElementById('quickAssignSubject');
            
            const subjectOptions = subjectsData.map(subject => 
                `<option value="${subject.id}">${subject.name} (${subject.code})</option>`
            ).join('');
            
            subjectFilter.innerHTML = '<option value="">All Subjects</option>' + subjectOptions;
            quickAssignSubject.innerHTML = '<option value="">Choose subject...</option>' + subjectOptions;
        }

        function quickAssignSubject() {
            const classId = document.getElementById('quickAssignClass').value;
            const subjectId = document.getElementById('quickAssignSubject').value;
            
            if (!classId || !subjectId) {
                showNotification('Please select both class and subject', 'error');
                return;
            }
            
            toggleAssignment(classId, subjectId);
        }

        function quickUnassignSubject() {
            const classId = document.getElementById('quickAssignClass').value;
            const subjectId = document.getElementById('quickAssignSubject').value;
            
            if (!classId || !subjectId) {
                showNotification('Please select both class and subject', 'error');
                return;
            }
            
            const cell = document.querySelector(`[data-class-id="${classId}"][data-subject-id="${subjectId}"]`);
            if (cell && cell.classList.contains('assigned')) {
                toggleAssignment(classId, subjectId);
            } else {
                showNotification('This subject is not assigned to the selected class', 'info');
            }
        }

        function showCurriculumLoading() {
            document.getElementById('curriculumLoading').style.display = 'flex';
            document.getElementById('curriculumMatrix').style.display = 'none';
            document.getElementById('curriculumEmpty').style.display = 'none';
        }

        function hideCurriculumLoading() {
            document.getElementById('curriculumLoading').style.display = 'none';
        }

        function refreshCurriculumData() {
            loadCurriculumData();
            showNotification('Curriculum data refreshed', 'info');
        }

        function filterCurriculumMatrix() {
            // Implementation for filtering the matrix view
            showNotification('Matrix filtering - Coming soon!', 'info');
        }

        function clearCurriculumFilters() {
            document.getElementById('classFilterCurriculum').value = '';
            document.getElementById('subjectFilterCurriculum').value = '';
            document.getElementById('assignmentStatusFilter').value = '';
            filterCurriculumMatrix();
        }

        function toggleMatrixView() {
            const container = document.getElementById('matrixScrollContainer');
            const toggleText = document.getElementById('matrixViewToggleText');
            
            if (container.style.maxHeight === 'none') {
                container.style.maxHeight = '70vh';
                toggleText.textContent = 'Expand';
            } else {
                container.style.maxHeight = 'none';
                toggleText.textContent = 'Collapse';
            }
        }

        function showBulkAssignmentModal() {
            const modalHtml = `
                <div class="modal-overlay" id="bulkAssignmentModal">
                    <div class="modal-container" style="max-width: 600px;">
                        <div class="modal-header">
                            <h3><i class="fas fa-magic"></i> Bulk Assignment Templates</h3>
                            <button class="modal-close" onclick="closeModal('bulkAssignmentModal')">&times;</button>
                        </div>
                        <div class="modal-body" style="padding: 20px;">
                            <div style="margin-bottom: 20px;">
                                <h4 style="margin-bottom: 12px;">Quick Templates</h4>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <button class="btn btn-outline" onclick="applyTemplate('core_subjects')">
                                        <i class="fas fa-book"></i> Core Subjects to All Classes
                                    </button>
                                    <button class="btn btn-outline" onclick="applyTemplate('all_subjects')">
                                        <i class="fas fa-layer-group"></i> All Subjects to Selected Class
                                    </button>
                                    <button class="btn btn-outline" onclick="applyTemplate('clear_all')">
                                        <i class="fas fa-eraser"></i> Clear All Assignments
                                    </button>
                                    <button class="btn btn-outline" onclick="applyTemplate('copy_class')">
                                        <i class="fas fa-copy"></i> Copy from Another Class
                                    </button>
                                </div>
                            </div>
                            
                            <div style="padding-top: 16px; border-top: 1px solid var(--border-color);">
                                <h4 style="margin-bottom: 12px;">Custom Bulk Assignment</h4>
                                <form id="bulkAssignmentForm" onsubmit="submitBulkAssignment(event)">
                                    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                                        <div class="form-group">
                                            <label class="form-label">Target Classes</label>
                                            <select name="target_classes" class="form-select" multiple style="height: 120px;">
                                                ${classesData.map(cls => `<option value="${cls.id}">Class ${cls.name}</option>`).join('')}
                                            </select>
                                            <small class="help-text">Hold Ctrl to select multiple</small>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Subjects to Assign</label>
                                            <select name="target_subjects" class="form-select" multiple style="height: 120px;">
                                                ${subjectsData.map(subject => `<option value="${subject.id}">${subject.name}</option>`).join('')}
                                            </select>
                                            <small class="help-text">Hold Ctrl to select multiple</small>
                                        </div>
                                    </div>
                                    <div class="form-actions" style="margin-top: 20px;">
                                        <button type="button" class="btn btn-secondary" onclick="closeModal('bulkAssignmentModal')">Cancel</button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-magic"></i> Apply Assignments
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            openModal('bulkAssignmentModal');
        }

        function applyTemplate(templateType) {
            // Implementation for template-based bulk assignments
            showNotification(`Applying ${templateType} template - Feature coming soon!`, 'info');
        }

        function submitBulkAssignment(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const targetClasses = Array.from(formData.getAll('target_classes'));
            const targetSubjects = Array.from(formData.getAll('target_subjects'));
            
            if (targetClasses.length === 0 || targetSubjects.length === 0) {
                showNotification('Please select at least one class and one subject', 'error');
                return;
            }
            
            // Create assignments array
            const assignments = [];
            targetClasses.forEach(classId => {
                targetSubjects.forEach(subjectId => {
                    assignments.push({ class_id: classId, subject_id: subjectId });
                });
            });
            
            const bulkData = new FormData();
            bulkData.append('action', 'bulk_assign_subjects');
            bulkData.append('assignments', JSON.stringify(assignments));

            fetch('academic_management_api.php', {
                method: 'POST',
                body: bulkData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    closeModal('bulkAssignmentModal');
                    document.getElementById('bulkAssignmentModal').remove();
                    loadCurriculumData();
                } else {
                    showNotification('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                showNotification('Error: ' + error.message, 'error');
            });
        }

        function exportCurriculumMatrix() {
            // Implementation for exporting the curriculum matrix
            showNotification('Curriculum matrix export - Coming soon!', 'info');
        }


        // ========== REPORTS MANAGEMENT ==========
        
        let reportsData = {};
        let currentReport = null;
        
        function loadReportsData() {
            // Load overview statistics
            fetch('academic_management_api.php?action=get_academic_statistics')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        reportsData.statistics = data.data;
                        updateReportsOverview();
                    }
                })
                .catch(error => {
                    console.error('Error loading reports data:', error);
                });
            
            // Update last update timestamps
            updateReportTimestamps();
        }
        
        function updateReportsOverview() {
            if (!reportsData.statistics) return;
            
            const stats = reportsData.statistics;
            const overview = `Academic structure includes ${stats.classes} classes with ${stats.sections} sections, 
                             ${stats.subjects} subjects with ${stats.subject_assignments} curriculum assignments. 
                             Current academic year: ${stats.current_academic_year}. 
                             Average ${stats.avg_sections_per_class} sections per class and 
                             ${stats.avg_subjects_per_class} subjects per class.`;
            
            document.getElementById('quickOverviewText').textContent = overview;
            document.getElementById('totalSubjectsReport').textContent = stats.subjects;
            document.getElementById('totalClassesReport').textContent = stats.classes;
            document.getElementById('currentAcademicYear').textContent = stats.current_academic_year;
            
            // Calculate curriculum coverage
            const totalPossible = stats.classes * stats.subjects;
            const coverage = totalPossible > 0 ? Math.round((stats.subject_assignments / totalPossible) * 100) : 0;
            document.getElementById('curriculumCoverage').textContent = coverage;
        }
        
        function updateReportTimestamps() {
            const now = new Date().toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            document.getElementById('structureLastUpdate').textContent = now;
        }
        
        function showReport(reportType) {
            currentReport = reportType;
            const viewer = document.getElementById('reportViewer');
            const title = document.getElementById('reportViewerTitle');
            const content = document.getElementById('reportContent');
            
            // Show loading state
            viewer.classList.add('active');
            content.innerHTML = `
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Generating report...</p>
                </div>
            `;
            
            // Set title and generate content based on report type
            switch (reportType) {
                case 'structure-overview':
                    title.innerHTML = '<i class="fas fa-sitemap"></i> Academic Structure Overview';
                    generateStructureOverviewReport();
                    break;
                case 'curriculum-coverage':
                    title.innerHTML = '<i class="fas fa-graduation-cap"></i> Curriculum Coverage Report';
                    generateCurriculumCoverageReport();
                    break;
                case 'subject-analytics':
                    title.innerHTML = '<i class="fas fa-chart-pie"></i> Subject Analytics Report';
                    generateSubjectAnalyticsReport();
                    break;
                case 'class-utilization':
                    title.innerHTML = '<i class="fas fa-school"></i> Class & Section Utilization';
                    generateClassUtilizationReport();
                    break;
                case 'year-progress':
                    title.innerHTML = '<i class="fas fa-calendar-check"></i> Academic Year Progress';
                    generateYearProgressReport();
                    break;
                case 'data-validation':
                    title.innerHTML = '<i class="fas fa-shield-alt"></i> Data Validation Report';
                    generateDataValidationReport();
                    break;
                default:
                    content.innerHTML = '<p>Report not found.</p>';
            }
        }
        
        function generateStructureOverviewReport() {
            fetch('academic_management_api.php?action=get_structure_overview')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayStructureOverviewReport(data.data);
                    } else {
                        document.getElementById('reportContent').innerHTML = '<p>Error loading report data.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('reportContent').innerHTML = '<p>Error generating report.</p>';
                });
        }
        
        function displayStructureOverviewReport(data) {
            const content = document.getElementById('reportContent');
            
            if (!data || data.length === 0) {
                content.innerHTML = '<p>No academic structure data available.</p>';
                return;
            }
            
            const totalSections = data.reduce((sum, cls) => sum + parseInt(cls.section_count || 0), 0);
            const totalSubjectAssignments = data.reduce((sum, cls) => sum + parseInt(cls.subject_count || 0), 0);
            
            content.innerHTML = `
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-number">${data.length}</div>
                        <div class="analytics-label">Total Classes</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${totalSections}</div>
                        <div class="analytics-label">Total Sections</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${totalSubjectAssignments}</div>
                        <div class="analytics-label">Subject Assignments</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${(totalSections / data.length).toFixed(1)}</div>
                        <div class="analytics-label">Avg Sections/Class</div>
                    </div>
                </div>
                
                <h4>Detailed Structure Breakdown</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Sections</th>
                            <th>Section Names</th>
                            <th>Subjects Assigned</th>
                            <th>Subject Codes</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(cls => `
                            <tr>
                                <td><strong>Class ${cls.class_name}</strong></td>
                                <td>${cls.section_count}</td>
                                <td>${cls.sections || 'None'}</td>
                                <td>${cls.subject_count}</td>
                                <td>${cls.subject_codes || 'None assigned'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="chart-container">
                    <h5>Academic Structure Visualization</h5>
                    <div class="structure-tree">
        ${data.map(cls => `
        📚 Class ${cls.class_name}
        ${cls.sections ? cls.sections.split(',').map(section => `   📂 Section ${section.trim()}`).join('\n') : '   ⚠️  No sections'}
        ${cls.subject_codes ? cls.subject_codes.split(',').map(code => `   📖 ${code.trim()}`).join('\n') : '   ⚠️  No subjects assigned'}
        `).join('\n')}
                    </div>
                </div>
            `;
        }
        
        function generateCurriculumCoverageReport() {
            fetch('academic_management_api.php?action=get_curriculum_overview')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayCurriculumCoverageReport(data.data);
                    } else {
                        document.getElementById('reportContent').innerHTML = '<p>Error loading curriculum data.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('reportContent').innerHTML = '<p>Error generating report.</p>';
                });
        }
        
        function displayCurriculumCoverageReport(data) {
            const content = document.getElementById('reportContent');
            
            if (!data || data.length === 0) {
                content.innerHTML = '<p>No curriculum data available.</p>';
                return;
            }
            
            // Group data by class
            const classesCoverage = {};
            const subjectsCoverage = {};
            let totalAssignments = 0;
            
            data.forEach(item => {
                const className = item.class_name;
                const subjectName = item.subject_name;
                const isAssigned = parseInt(item.is_assigned) === 1;
                
                if (!classesCoverage[className]) {
                    classesCoverage[className] = { total: 0, assigned: 0 };
                }
                if (!subjectsCoverage[subjectName]) {
                    subjectsCoverage[subjectName] = { total: 0, assigned: 0 };
                }
                
                classesCoverage[className].total++;
                subjectsCoverage[subjectName].total++;
                
                if (isAssigned) {
                    classesCoverage[className].assigned++;
                    subjectsCoverage[subjectName].assigned++;
                    totalAssignments++;
                }
            });
            
            const totalPossible = data.length;
            const overallCoverage = Math.round((totalAssignments / totalPossible) * 100);
            
            content.innerHTML = `
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-number">${overallCoverage}%</div>
                        <div class="analytics-label">Overall Coverage</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${totalAssignments}</div>
                        <div class="analytics-label">Active Assignments</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${totalPossible - totalAssignments}</div>
                        <div class="analytics-label">Missing Assignments</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${Object.keys(classesCoverage).length}</div>
                        <div class="analytics-label">Classes Analyzed</div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div>
                        <h4>Coverage by Class</h4>
                        ${Object.entries(classesCoverage).map(([className, stats]) => {
                            const percentage = Math.round((stats.assigned / stats.total) * 100);
                            return `
                                <div class="progress-item">
                                    <span class="progress-label">Class ${className}</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill-small" style="width: ${percentage}%;"></div>
                                    </div>
                                    <span class="progress-value">${percentage}%</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                    
                    <div>
                        <h4>Coverage by Subject</h4>
                        ${Object.entries(subjectsCoverage).map(([subjectName, stats]) => {
                            const percentage = Math.round((stats.assigned / stats.total) * 100);
                            return `
                                <div class="progress-item">
                                    <span class="progress-label">${subjectName}</span>
                                    <div class="progress-bar-small">
                                        <div class="progress-fill-small" style="width: ${percentage}%;"></div>
                                    </div>
                                    <span class="progress-value">${percentage}%</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
                
                <div class="chart-container">
                    <h5>Curriculum Coverage Visualization</h5>
                    <div class="chart-placeholder">
                        <div style="text-align: center;">
                            <i class="fas fa-chart-bar" style="font-size: 2rem; margin-bottom: 8px; opacity: 0.3;"></i>
                            <p>Interactive charts will be available in future updates</p>
                            <small>Current coverage: ${overallCoverage}% (${totalAssignments}/${totalPossible} assignments)</small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateSubjectAnalyticsReport() {
            fetch('academic_management_api.php?action=get_subjects')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySubjectAnalyticsReport(data.data);
                    } else {
                        document.getElementById('reportContent').innerHTML = '<p>Error loading subjects data.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('reportContent').innerHTML = '<p>Error generating report.</p>';
                });
        }
        
        function displaySubjectAnalyticsReport(data) {
            const content = document.getElementById('reportContent');
            
            if (!data || data.length === 0) {
                content.innerHTML = '<p>No subjects data available.</p>';
                return;
            }
            
            const totalSubjects = data.length;
            const assignedSubjects = data.filter(s => parseInt(s.class_count) > 0).length;
            const unassignedSubjects = totalSubjects - assignedSubjects;
            const avgClassesPerSubject = data.reduce((sum, s) => sum + parseInt(s.class_count || 0), 0) / totalSubjects;
            
            content.innerHTML = `
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-number">${totalSubjects}</div>
                        <div class="analytics-label">Total Subjects</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${assignedSubjects}</div>
                        <div class="analytics-label">Assigned Subjects</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${unassignedSubjects}</div>
                        <div class="analytics-label">Unassigned Subjects</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${avgClassesPerSubject.toFixed(1)}</div>
                        <div class="analytics-label">Avg Classes/Subject</div>
                    </div>
                </div>
                
                <h4>Subject Usage Details</h4>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Classes Assigned</th>
                            <th>Usage Status</th>
                            <th>Classes</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(subject => {
                            const classCount = parseInt(subject.class_count || 0);
                            const status = classCount === 0 ? 'Unused' : classCount === 1 ? 'Limited' : 'Active';
                            const statusColor = classCount === 0 ? '#ef4444' : classCount === 1 ? '#f59e0b' : '#10b981';
                            
                            return `
                                <tr>
                                    <td><span style="background: ${statusColor}; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">${subject.code}</span></td>
                                    <td><strong>${subject.name}</strong></td>
                                    <td>${classCount}</td>
                                    <td><span style="color: ${statusColor}; font-weight: 500;">${status}</span></td>
                                    <td style="font-size: 0.875rem;">${subject.classes || 'None'}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
                
                <div class="chart-container">
                    <h5>Subject Usage Distribution</h5>
                    <div class="chart-placeholder">
                        <div style="text-align: center;">
                            <i class="fas fa-chart-pie" style="font-size: 2rem; margin-bottom: 8px; opacity: 0.3;"></i>
                            <p>Subject usage pie chart will be available in future updates</p>
                            <small>Assigned: ${assignedSubjects} | Unassigned: ${unassignedSubjects}</small>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateClassUtilizationReport() {
            fetch('academic_management_api.php?action=get_classes')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayClassUtilizationReport(data.data);
                    } else {
                        document.getElementById('reportContent').innerHTML = '<p>Error loading classes data.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('reportContent').innerHTML = '<p>Error generating report.</p>';
                });
        }
        
        function displayClassUtilizationReport(data) {
            const content = document.getElementById('reportContent');
            
            const reportContent = `
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-number">${data.length}</div>
                        <div class="analytics-label">Total Classes</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${data.reduce((sum, cls) => sum + parseInt(cls.section_count || 0), 0)}</div>
                        <div class="analytics-label">Total Sections</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${(data.reduce((sum, cls) => sum + parseInt(cls.section_count || 0), 0) / data.length).toFixed(1)}</div>
                        <div class="analytics-label">Avg Sections/Class</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${data.filter(cls => parseInt(cls.section_count || 0) === 0).length}</div>
                        <div class="analytics-label">Classes w/o Sections</div>
                    </div>
                </div>
                
                <h4>Class Utilization Analysis</h4>
                <p style="margin-bottom: 16px; color: var(--text-secondary);">
                    This report provides insights into class and section distribution across the institution.
                </p>
            `;
            
            content.innerHTML = reportContent;
        }
        
        function generateYearProgressReport() {
            const content = document.getElementById('reportContent');
            content.innerHTML = `
                <div class="analytics-card" style="margin-bottom: 20px;">
                    <div class="analytics-number">2024-2025</div>
                    <div class="analytics-label">Current Academic Year</div>
                </div>
                
                <h4>Academic Year Progress</h4>
                <p style="margin-bottom: 16px; color: var(--text-secondary);">
                    Academic year progress tracking will be available in future updates with term-based milestones.
                </p>
                
                <div class="chart-container">
                    <h5>Year Timeline</h5>
                    <div class="chart-placeholder">
                        <div style="text-align: center;">
                            <i class="fas fa-calendar-alt" style="font-size: 2rem; margin-bottom: 8px; opacity: 0.3;"></i>
                            <p>Academic year timeline visualization coming soon</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function generateDataValidationReport() {
            fetch('academic_management_api.php?action=validate_academic_data')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayDataValidationReport(data.data);
                        // Update validation issues count
                        const totalIssues = Object.keys(data.data).length;
                        document.getElementById('validationIssues').textContent = totalIssues;
                    } else {
                        document.getElementById('reportContent').innerHTML = '<p>Error loading validation data.</p>';
                    }
                })
                .catch(error => {
                    document.getElementById('reportContent').innerHTML = '<p>Error generating validation report.</p>';
                });
        }
        
        function displayDataValidationReport(issues) {
            const content = document.getElementById('reportContent');
            const totalIssues = Object.keys(issues).length;
            
            if (totalIssues === 0) {
                content.innerHTML = `
                    <div style="text-align: center; padding: 40px; background: #f0fdf4; border-radius: 8px; border: 1px solid #22c55e;">
                        <i class="fas fa-check-circle" style="font-size: 3rem; color: #22c55e; margin-bottom: 16px;"></i>
                        <h3 style="color: #22c55e; margin-bottom: 8px;">All Good! ✅</h3>
                        <p style="color: #166534;">No data validation issues found. Your academic structure is consistent and complete.</p>
                    </div>
                `;
                return;
            }
            
            let issuesHtml = '';
            
            if (issues.classes_without_sections) {
                issuesHtml += `
                    <div style="background: #fef2f2; border: 1px solid #f87171; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
                        <h5 style="color: #dc2626; margin-bottom: 8px;">
                            <i class="fas fa-exclamation-triangle"></i> Classes Without Sections (${issues.classes_without_sections.length})
                        </h5>
                        <ul style="margin: 0; padding-left: 20px; color: #7f1d1d;">
                            ${issues.classes_without_sections.map(cls => `<li>Class ${cls.name}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            if (issues.classes_without_subjects) {
                issuesHtml += `
                    <div style="background: #fffbeb; border: 1px solid #fbbf24; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
                        <h5 style="color: #d97706; margin-bottom: 8px;">
                            <i class="fas fa-exclamation-triangle"></i> Classes Without Subjects (${issues.classes_without_subjects.length})
                        </h5>
                        <ul style="margin: 0; padding-left: 20px; color: #92400e;">
                            ${issues.classes_without_subjects.map(cls => `<li>Class ${cls.name}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            if (issues.unassigned_subjects) {
                issuesHtml += `
                    <div style="background: #f0f9ff; border: 1px solid #60a5fa; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
                        <h5 style="color: #2563eb; margin-bottom: 8px;">
                            <i class="fas fa-info-circle"></i> Unassigned Subjects (${issues.unassigned_subjects.length})
                        </h5>
                        <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                            ${issues.unassigned_subjects.map(subject => `<li>${subject.name} (${subject.code})</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            if (issues.sections_without_teachers) {
                issuesHtml += `
                    <div style="background: #f3f4f6; border: 1px solid #9ca3af; border-radius: 6px; padding: 16px; margin-bottom: 16px;">
                        <h5 style="color: #4b5563; margin-bottom: 8px;">
                            <i class="fas fa-user-slash"></i> Sections Without Class Teachers (${issues.sections_without_teachers.length})
                        </h5>
                        <ul style="margin: 0; padding-left: 20px; color: #374151;">
                            ${issues.sections_without_teachers.map(section => `<li>${section.class_name} - Section ${section.name}</li>`).join('')}
                        </ul>
                    </div>
                `;
            }
            
            content.innerHTML = `
                <div class="analytics-grid">
                    <div class="analytics-card">
                        <div class="analytics-number" style="color: ${totalIssues === 0 ? '#10b981' : '#ef4444'};">${totalIssues}</div>
                        <div class="analytics-label">Total Issues Found</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${issues.classes_without_sections ? issues.classes_without_sections.length : 0}</div>
                        <div class="analytics-label">Classes w/o Sections</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${issues.unassigned_subjects ? issues.unassigned_subjects.length : 0}</div>
                        <div class="analytics-label">Unassigned Subjects</div>
                    </div>
                    <div class="analytics-card">
                        <div class="analytics-number">${issues.sections_without_teachers ? issues.sections_without_teachers.length : 0}</div>
                        <div class="analytics-label">Sections w/o Teachers</div>
                    </div>
                </div>
                
                <h4>Validation Issues</h4>
                ${issuesHtml}
                
                ${totalIssues > 0 ? `
                    <div style="background: #f0f9ff; border-radius: 6px; padding: 16px; margin-top: 20px;">
                        <h5 style="color: #1d4ed8; margin-bottom: 8px;">
                            <i class="fas fa-lightbulb"></i> Recommendations
                        </h5>
                        <ul style="margin: 0; padding-left: 20px; color: #1e40af;">
                            ${issues.classes_without_sections ? '<li>Add sections to classes that don\'t have any</li>' : ''}
                            ${issues.classes_without_subjects ? '<li>Assign subjects to classes in the Curriculum Mapping tab</li>' : ''}
                            ${issues.unassigned_subjects ? '<li>Consider assigning unused subjects to appropriate classes</li>' : ''}
                            ${issues.sections_without_teachers ? '<li>Assign class teachers to sections in Teacher Management</li>' : ''}
                        </ul>
                    </div>
                ` : ''}
            `;
        }
        
        function closeReportViewer() {
            document.getElementById('reportViewer').classList.remove('active');
            currentReport = null;
        }
        
        function refreshReportsData() {
            loadReportsData();
            showNotification('Reports data refreshed', 'info');
        }
          function exportReport(reportType) {
            if (reportType === 'structure-overview') {
                // Export structure overview using the new API endpoint
                window.open('academic_management_api.php?action=export_structure_overview', '_blank');
                showNotification('Exporting structure overview report...', 'success');
            } else {
                showNotification(`Exporting ${reportType} report - Feature coming soon!`, 'info');
            }
        }
        
        function exportCurrentReport() {
            if (currentReport) {
                exportReport(currentReport);
            }
        }
        
        function exportAllReports() {
            showNotification('Bulk export of all reports - Feature coming soon!', 'info');
        }
        
        function printReport() {
            if (currentReport) {
                window.print();
            }
        }
        
        function scheduleReport() {
            showNotification('Report scheduling - Feature coming soon!', 'info');
        }

        // Bulk Operations Tab Functions
        function initializeBulkOperationsTab() {
            loadBulkOperationsStats();
            loadAcademicYearsForBatch();
            loadOperationHistory();
            setupFileHandlers();
        }
        
        function setupFileHandlers() {
            // Academic Years file handler
            document.getElementById('import-years').addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || '';
                document.getElementById('years-file-name').textContent = fileName;
                document.getElementById('import-years-btn').disabled = !fileName;
            });
        
            // Classes file handler
            document.getElementById('import-classes').addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || '';
                document.getElementById('classes-file-name').textContent = fileName;
                document.getElementById('import-classes-btn').disabled = !fileName;
            });
        
            // Subjects file handler
            document.getElementById('import-subjects').addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name || '';
                document.getElementById('subjects-file-name').textContent = fileName;
                document.getElementById('import-subjects-btn').disabled = !fileName;
            });
        
            // Batch year change handler
            document.getElementById('batch-year').addEventListener('change', function() {
                if (this.value) {
                    loadClassesForBatch(this.value);
                    loadSubjectsForBatch();
                }
            });
        }
        
        function loadBulkOperationsStats() {
            fetch('academic_management_api.php?action=get_bulk_stats')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('total-records').textContent = data.stats.total_records || 0;
                        document.getElementById('pending-imports').textContent = data.stats.pending_imports || 0;
                        document.getElementById('completed-operations').textContent = data.stats.completed_today || 0;
                        document.getElementById('failed-operations').textContent = data.stats.failed_operations || 0;
                    }
                })
                .catch(error => console.error('Error loading bulk stats:', error));
        }
        
        function loadAcademicYearsForBatch() {
            fetch('academic_management_api.php?action=get_academic_years')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const batchYear = document.getElementById('batch-year');
                        const sourceYear = document.getElementById('source-year');
                        const targetYear = document.getElementById('target-year');
                        
                        // Clear existing options
                        [batchYear, sourceYear, targetYear].forEach(select => {
                            select.innerHTML = '<option value="">Select Year...</option>';
                        });
        
                        data.academic_years.forEach(year => {
                            const option = `<option value="${year.id}">${year.year_name}</option>`;
                            batchYear.innerHTML += option;
                            sourceYear.innerHTML += option;
                            targetYear.innerHTML += option;
                        });
                    }
                })
                .catch(error => console.error('Error loading academic years:', error));
        }
        
        function loadClassesForBatch(yearId) {
            fetch(`academic_management_api.php?action=get_classes&year_id=${yearId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('batch-classes');
                        container.innerHTML = '';
                        
                        data.classes.forEach(cls => {
                            const checkbox = document.createElement('label');
                            checkbox.innerHTML = `
                                <input type="checkbox" value="${cls.id}"> 
                                ${cls.class_name}
                            `;
                            container.appendChild(checkbox);
                        });
                    }
                })
                .catch(error => console.error('Error loading classes:', error));
        }
        
        function loadSubjectsForBatch() {
            fetch('academic_management_api.php?action=get_subjects')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('batch-subjects');
                        container.innerHTML = '';
                        
                        data.subjects.forEach(subject => {
                            const checkbox = document.createElement('label');
                            checkbox.innerHTML = `
                                <input type="checkbox" value="${subject.id}"> 
                                ${subject.subject_name} (${subject.subject_code})
                            `;
                            container.appendChild(checkbox);
                        });
                    }
                })
                .catch(error => console.error('Error loading subjects:', error));
        }
        
        function importData(type) {
            const fileInput = document.getElementById(`import-${type}`);
            const file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a CSV file first.');
                return;
            }
        
            const formData = new FormData();
            formData.append('file', file);
            formData.append('type', type);
        
            const progressContainer = document.createElement('div');
            progressContainer.className = 'progress-container';
            progressContainer.style.display = 'block';
            progressContainer.innerHTML = `
                <div class="progress-bar">
                    <div class="progress-fill"></div>
                </div>
                <div class="progress-text">Importing data...</div>
            `;
            
            fileInput.closest('.operation-card').appendChild(progressContainer);
        
            fetch('academic_management_api.php?action=bulk_import', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                progressContainer.remove();
                
                if (data.success) {
                    alert(`Successfully imported ${data.imported_count} records.`);
                    loadBulkOperationsStats();
                    loadOperationHistory();
                    
                    // Reset file input
                    fileInput.value = '';
                    document.getElementById(`${type}-file-name`).textContent = '';
                    document.getElementById(`import-${type}-btn`).disabled = true;
                } else {
                    alert(`Import failed: ${data.message}`);
                }
            })
            .catch(error => {
                progressContainer.remove();
                console.error('Import error:', error);
                alert('Import failed. Please try again.');
            });
        }
        
        function exportData() {
            const exportOptions = {
                academic_years: document.getElementById('export-years').checked,
                classes: document.getElementById('export-classes').checked,
                subjects: document.getElementById('export-subjects').checked,
                curriculum: document.getElementById('export-curriculum').checked
            };
        
            const params = new URLSearchParams();
            params.append('action', 'bulk_export');
            Object.keys(exportOptions).forEach(key => {
                if (exportOptions[key]) {
                    params.append('tables[]', key);
                }
            });
        
            window.open(`academic_management_api.php?${params.toString()}`, '_blank');
        }
        
        function performBatchAssignment() {
            const yearId = document.getElementById('batch-year').value;
            const selectedClasses = Array.from(document.querySelectorAll('#batch-classes input:checked'))
                .map(cb => cb.value);
            const selectedSubjects = Array.from(document.querySelectorAll('#batch-subjects input:checked'))
                .map(cb => cb.value);
        
            if (!yearId || selectedClasses.length === 0 || selectedSubjects.length === 0) {
                alert('Please select academic year, classes, and subjects.');
                return;
            }
        
            const data = {
                action: 'batch_assign_subjects',
                year_id: yearId,
                class_ids: selectedClasses,
                subject_ids: selectedSubjects
            };
        
            fetch('academic_management_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Successfully assigned subjects to ${data.assignments_created} class-subject combinations.`);
                    loadBulkOperationsStats();
                    loadOperationHistory();
                } else {
                    alert(`Batch assignment failed: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Batch assignment error:', error);
                alert('Batch assignment failed. Please try again.');
            });
        }
        
        function duplicateYearStructure() {
            const sourceYearId = document.getElementById('source-year').value;
            const targetYearId = document.getElementById('target-year').value;
            const copyClasses = document.getElementById('copy-classes').checked;
            const copyCurriculum = document.getElementById('copy-curriculum').checked;
        
            if (!sourceYearId || !targetYearId) {
                alert('Please select both source and target academic years.');
                return;
            }
        
            if (sourceYearId === targetYearId) {
                alert('Source and target years cannot be the same.');
                return;
            }
        
            if (!copyClasses && !copyCurriculum) {
                alert('Please select what to copy.');
                return;
            }
        
            const data = {
                action: 'duplicate_year_structure',
                source_year_id: sourceYearId,
                target_year_id: targetYearId,
                copy_classes: copyClasses,
                copy_curriculum: copyCurriculum
            };
        
            fetch('academic_management_api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Successfully duplicated structure. ${data.classes_copied || 0} classes and ${data.assignments_copied || 0} subject assignments copied.`);
                    loadBulkOperationsStats();
                    loadOperationHistory();
                } else {
                    alert(`Duplication failed: ${data.message}`);
                }
            })
            .catch(error => {
                console.error('Duplication error:', error);
                alert('Duplication failed. Please try again.');
            });
        }
        
        function validateDataIntegrity() {
            const resultsContainer = document.getElementById('validation-results');
            resultsContainer.style.display = 'block';
            resultsContainer.innerHTML = '<div class="text-center">Running validation...</div>';
        
            fetch('academic_management_api.php?action=validate_data_integrity')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let html = '<h5>Validation Results:</h5>';
                        
                        data.validation_results.forEach(result => {
                            const className = result.status === 'success' ? 'success' : 
                                            result.status === 'warning' ? 'warning' : 'error';
                            html += `<div class="validation-item ${className}">
                                <strong>${result.check}:</strong> ${result.message}
                                ${result.count ? ` (${result.count} items)` : ''}
                            </div>`;
                        });
                        
                        resultsContainer.innerHTML = html;
                    } else {
                        resultsContainer.innerHTML = `<div class="validation-item error">Validation failed: ${data.message}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Validation error:', error);
                    resultsContainer.innerHTML = '<div class="validation-item error">Validation failed. Please try again.</div>';
                });
        }
        
        function cleanupOrphanedRecords() {
            if (!confirm('This will permanently delete orphaned records. Are you sure?')) {
                return;
            }
        
            const resultsContainer = document.getElementById('cleanup-results');
            resultsContainer.style.display = 'block';
            resultsContainer.innerHTML = '<div class="text-center">Cleaning up data...</div>';
        
            fetch('academic_management_api.php?action=cleanup_orphaned_records', {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = '<h5>Cleanup Results:</h5>';
                    
                    data.cleanup_results.forEach(result => {
                        html += `<div class="validation-item success">
                            <strong>${result.table}:</strong> ${result.deleted_count} orphaned records deleted
                        </div>`;
                    });
                    
                    resultsContainer.innerHTML = html;
                    loadBulkOperationsStats();
                } else {
                    resultsContainer.innerHTML = `<div class="validation-item error">Cleanup failed: ${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Cleanup error:', error);
                resultsContainer.innerHTML = '<div class="validation-item error">Cleanup failed. Please try again.</div>';
            });
        }
        
        function loadOperationHistory() {
            fetch('academic_management_api.php?action=get_operation_history')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('operation-history');
                        tbody.innerHTML = '';
                        
                        if (data.operations.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="7" class="text-center">No operations found</td></tr>';
                            return;
                        }
                        
                        data.operations.forEach(operation => {
                            const statusClass = operation.status === 'completed' ? 'success' : 
                                              operation.status === 'failed' ? 'error' : 'processing';
                            
                            tbody.innerHTML += `
                                <tr>
                                    <td>${new Date(operation.created_at).toLocaleString()}</td>
                                    <td>${operation.operation_type}</td>
                                    <td>${operation.data_type}</td>
                                    <td>${operation.records_count || '-'}</td>
                                    <td><span class="status-badge ${statusClass}">${operation.status}</span></td>
                                    <td>${operation.duration || '-'}</td>
                                    <td>
                                        ${operation.log_file ? `<a href="${operation.log_file}" target="_blank" class="btn btn-sm btn-outline-primary">View Log</a>` : '-'}
                                    </td>
                                </tr>
                            `;
                        });
                    }
                })
                .catch(error => console.error('Error loading operation history:', error));
        }



        // ========== UTILITY FUNCTIONS ==========

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

        function showLoading(loadingId, contentId) {
            document.getElementById(loadingId).style.display = 'flex';
            document.getElementById(contentId).style.display = 'none';
        }

        function hideLoading(loadingId, contentId) {
            document.getElementById(loadingId).style.display = 'none';
            document.getElementById(contentId).style.display = 'grid';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
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
