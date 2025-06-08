<?php
require_once '../includes/config.php';
require_once '../includes/connection.php';
require_once '../includes/functions.php';

// Require authentication and view_dashboard permission
requirePermission('view_dashboard');

// Handle date filter
$dateFilter = isset($_GET['date_filter']) ? trim($_GET['date_filter']) : 'today';
$selectedDate = isset($_GET['selected_date']) ? trim($_GET['selected_date']) : date('Y-m-d');
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : date('Y-m-d');

// Get statistics based on date filter
$today = date('Y-m-d');
if ($dateFilter === 'all') {
    $todayAdmissions = $db->count('admission_inquiries', '1=1', []);
    $todayVisitors = $db->count('visitors', '1=1', []);
    $statsLabel = 'Total';
} elseif ($dateFilter === 'custom' && $selectedDate) {
    $todayAdmissions = $db->count('admission_inquiries', 'DATE(inquiry_date) = ?', [$selectedDate]);
    $todayVisitors = $db->count('visitors', 'DATE(visit_date) = ?', [$selectedDate]);
    $statsLabel = date('M j, Y', strtotime($selectedDate));
} elseif ($dateFilter === 'range' && $startDate && $endDate) {
    $todayAdmissions = $db->count('admission_inquiries', 'DATE(inquiry_date) BETWEEN ? AND ?', [$startDate, $endDate]);
    $todayVisitors = $db->count('visitors', 'DATE(visit_date) BETWEEN ? AND ?', [$startDate, $endDate]);
    if ($startDate === $endDate) {
        $statsLabel = date('M j, Y', strtotime($startDate));
    } else {
        $statsLabel = date('M j', strtotime($startDate)) . ' - ' . date('M j, Y', strtotime($endDate));
    }
} else {
    // Default to today
    $todayAdmissions = $db->count('admission_inquiries', 'DATE(inquiry_date) = ?', [$today]);
    $todayVisitors = $db->count('visitors', 'DATE(visit_date) = ?', [$today]);
    $statsLabel = "Today's";
}

// Handle search for admissions
$admissionSearch = isset($_GET['admission_search']) ? trim($_GET['admission_search']) : '';
$admissionWhere = '1=1';
$admissionParams = [];

// Add date filter to admission search
if ($dateFilter === 'today') {
    $admissionWhere .= ' AND DATE(inquiry_date) = ?';
    $admissionParams[] = $today;
} elseif ($dateFilter === 'custom' && $selectedDate) {
    $admissionWhere .= ' AND DATE(inquiry_date) = ?';
    $admissionParams[] = $selectedDate;
} elseif ($dateFilter === 'range' && $startDate && $endDate) {
    $admissionWhere .= ' AND DATE(inquiry_date) BETWEEN ? AND ?';
    $admissionParams[] = $startDate;
    $admissionParams[] = $endDate;
}

if ($admissionSearch) {
    $admissionWhere .= ' AND (child_name LIKE ? OR parent_name LIKE ? OR parent_email LIKE ? OR phone_number LIKE ?)';
    $searchTerm = "%$admissionSearch%";
    $admissionParams = array_merge($admissionParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Get admission inquiries
$admissions = $db->fetchAll(
    "SELECT * FROM admission_inquiries WHERE $admissionWhere ORDER BY inquiry_date DESC LIMIT 10",
    $admissionParams
);

// Handle search for visitors
$visitorSearch = isset($_GET['visitor_search']) ? trim($_GET['visitor_search']) : '';
$visitorWhere = '1=1';
$visitorParams = [];

// Add date filter to visitor search
if ($dateFilter === 'today') {
    $visitorWhere .= ' AND DATE(visit_date) = ?';
    $visitorParams[] = $today;
} elseif ($dateFilter === 'custom' && $selectedDate) {
    $visitorWhere .= ' AND DATE(visit_date) = ?';
    $visitorParams[] = $selectedDate;
} elseif ($dateFilter === 'range' && $startDate && $endDate) {
    $visitorWhere .= ' AND DATE(visit_date) BETWEEN ? AND ?';
    $visitorParams[] = $startDate;
    $visitorParams[] = $endDate;
}

if ($visitorSearch) {
    $visitorWhere .= ' AND (visitor_name LIKE ? OR email LIKE ? OR phone_number LIKE ? OR purpose LIKE ?)';
    $searchTerm = "%$visitorSearch%";
    $visitorParams = array_merge($visitorParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
}

// Get visitors
$visitors = $db->fetchAll(
    "SELECT * FROM visitors WHERE $visitorWhere ORDER BY visit_date DESC LIMIT 10",
    $visitorParams
);

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Vinodh English School</title>
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin="">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?display=swap&family=Noto+Sans:wght@400;500;700;900&family=Public+Sans:wght@400;500;700;900">
  <style>
/* Bootstrap-Inspired Premium Dashboard with Modern Gradients */

/* CSS Variables - Premium Color Palette */
:root {
    --primary-bg: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    --secondary-bg: linear-gradient(135deg, #ffffff 0%, #f1f5f9 50%, #e2e8f0 100%);
    --accent-bg: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    --card-bg: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(248,250,252,0.8) 100%);
    --border-color: rgba(226, 232, 240, 0.6);
    --text-primary: #1e293b;
    --text-secondary: #64748b;
    --text-muted: #94a3b8;
    --button-primary: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
    --button-hover: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
    --success-bg: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    --success-text: #065f46;
    --success-border: rgba(167, 243, 208, 0.5);
    --error-bg: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    --error-text: #991b1b;
    --error-border: rgba(252, 165, 165, 0.5);
    --warning-bg: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
    --warning-text: #92400e;
    --warning-border: rgba(252, 211, 77, 0.5);
    --required-color: #dc2626;
    --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Reset and Base Styles */
* {
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    margin: 0;
    padding: 0;
    background: var(--primary-bg);
    color: var(--text-primary);
    line-height: 1.6;
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

/* Layout Components */
.main-container {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: var(--primary-bg);
}

.header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 0.5rem;
    background: var(--secondary-bg);
    border-bottom: 1px solid var(--border-color);
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(10px);
    position: sticky;
    top: 0;
    z-index: 1030;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.logo-section {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    color: var(--text-primary);
}

.logo-text {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: -0.02em;
    margin: 0;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-section {
    display: flex;
    flex: 1;
    justify-content: flex-end;
    gap: 2rem;
    align-items: center;
}

.nav-links {
    display: flex;
    align-items: center;
    gap: 2rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.nav-link {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.nav-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s;
}

.nav-link:hover::before {
    left: 100%;
}

.nav-link:hover {
    color: var(--text-primary);
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-1px);
}

.nav-link.active {
    font-weight: 600;
    color: #3b82f6;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
}

/* Export Dropdown */
.export-dropdown {
    position: relative;
    display: inline-block;
}

.export-dropdown-btn {
    color: var(--text-secondary);
    font-size: 0.9rem;
    font-weight: 500;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    position: relative;
    overflow: hidden;
}

.export-dropdown-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s;
}

.export-dropdown-btn:hover::before {
    left: 100%;
}

.export-dropdown-btn:hover {
    color: var(--text-primary);
    background: rgba(59, 130, 246, 0.05);
    transform: translateY(-1px);
}

.export-dropdown-content {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: var(--card-bg);
    min-width: 220px;
    box-shadow: var(--shadow-lg);
    z-index: 1000;
    border-radius: 0.75rem;
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
    animation: dropdownFadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    margin-top: 0.5rem;
}

.export-dropdown:hover .export-dropdown-content {
    display: block;
}

.export-dropdown-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--text-primary);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 0.5rem;
    margin: 0.25rem;
}

.export-dropdown-link:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    color: #3b82f6;
    transform: translateX(4px);
}

.export-dropdown-link .export-icon {
    margin-right: 0.75rem;
    font-size: 1rem;
}

/* Mobile Hamburger Menu */
.mobile-menu-btn {
    display: none;
    flex-direction: column;
    justify-content: space-between;
    width: 24px;
    height: 18px;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hamburger-line {
    width: 100%;
    height: 2px;
    background: var(--text-secondary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-radius: 2px;
}

.mobile-menu-btn.active .hamburger-line:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
    background: #3b82f6;
}

.mobile-menu-btn.active .hamburger-line:nth-child(2) {
    opacity: 0;
}

.mobile-menu-btn.active .hamburger-line:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
    background: #3b82f6;
}

.mobile-nav-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    backdrop-filter: blur(5px);
    opacity: 0;
    transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.mobile-nav-overlay.active {
    opacity: 1;
}

.mobile-nav-menu {
    display: none;
    position: fixed;
    top: 0;
    right: -100%;
    width: 300px;
    height: 100vh;
    background: var(--secondary-bg);
    z-index: 1050;
    transition: right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    padding: 2rem 1rem;
    box-shadow: var(--shadow-xl);
    backdrop-filter: blur(10px);
    overflow-y: auto;
}

.mobile-nav-menu.active {
    right: 0;
}

.mobile-nav-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-secondary);
    padding: 0.5rem;
    border-radius: 0.5rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.mobile-nav-close:hover {
    background: rgba(59, 130, 246, 0.1);
    color: var(--text-primary);
    transform: rotate(90deg);
}

.mobile-nav-links {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 2rem;
}

.mobile-nav-link {
    color: var(--text-secondary);
    font-size: 1rem;
    font-weight: 500;
    text-decoration: none;
    padding: 1rem;
    border-radius: 0.75rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
    display: block;
}

.mobile-nav-link:hover,
.mobile-nav-link.active {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.05) 100%);
    color: #3b82f6;
    border-color: rgba(59, 130, 246, 0.2);
    transform: translateX(4px);
}

.mobile-export-section {
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.mobile-export-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 1rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0 1rem;
}

.user-avatar {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 50%;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    border: 2px solid rgba(59, 130, 246, 0.2);
    box-shadow: var(--shadow-md);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.user-avatar:hover {
    transform: scale(1.05);
    border-color: #3b82f6;
    box-shadow: var(--shadow-lg);
}

/* Content Layout */
.content-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 2rem;
    background: var(--primary-bg);
}

.content-container {
    display: flex;
    flex-direction: column;
    
    width: 100%;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.page-header {
    margin-bottom: 2rem;
    padding: 1.5rem 0;
}

.page-title {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin: 0 0 0.5rem 0;
    background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    letter-spacing: -0.02em;
}

.page-subtitle {
    color: var(--text-secondary);
    font-size: 1.1rem;
    margin: 0;
    font-weight: 400;
    opacity: 0.8;
}

/* Filter Components */
.filter-container {
    background: var(--card-bg);
    border-radius: 1rem;
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: var(--shadow-md);
    backdrop-filter: blur(10px);
    animation: slideInDown 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.filter-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6, #06b6d4);
    border-radius: 1rem 1rem 0 0;
}

.date-filter-form {
    display: flex;
    flex-wrap: wrap;
    gap: 1.5rem;
    align-items: end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 200px;
}

.filter-label {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.filter-select, 
.filter-input {
    padding: 0.75rem 1rem;
    border: 2px solid var(--border-color);
    border-radius: 0.75rem;
    background: rgba(255, 255, 255, 0.8);
    color: var(--text-primary);
    font-size: 0.9rem;
    min-height: 3rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-sm);
    backdrop-filter: blur(5px);
}

.filter-select:focus, 
.filter-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.filter-btn {
    padding: 0.75rem 2rem;
    background: var(--button-primary);
    color: white;
    border: none;
    border-radius: 0.75rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-md);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    min-height: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.filter-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.filter-btn:hover::before {
    left: 100%;
}

.filter-btn:hover {
    background: var(--button-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.filter-btn:active {
    transform: translateY(0);
    box-shadow: var(--shadow-md);
}

.date-range-inputs {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    min-width: 180px;
}

/* Stats Cards */
.stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.1s both;
}

.stat-card {
    background: var(--card-bg);
    border-radius: 1.25rem;
    border: 1px solid var(--border-color);
    padding: 2rem;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    box-shadow: var(--shadow-md);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(10px);
    opacity: 0;
    transform: translateY(30px);
}

.stat-card.animate-in {
    opacity: 1;
    transform: translateY(0);
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.stat-card.animate-out {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeOutDown 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    opacity: 0;
    transition: opacity 0.3s;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-xl);
    border-color: rgba(59, 130, 246, 0.3);
}

.stat-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
    margin: 0 0 1rem 0;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.stat-value {
    font-size: 2.5rem;
    font-weight: 800;
    margin: 0 0 1rem 0;
    background: linear-gradient(135deg, #1e293b 0%, #3b82f6 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
}

.card-spacer {
    flex-grow: 1;
}

/* Fixed Table Layout - Side by Side */
.tables-container {
    display: flex;
    gap: 2rem;
    margin-top: 1.5rem;
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) 0.2s both;
}

.table-section {
    flex: 1;
    background: var(--card-bg);
    border-radius: 1.25rem;
    padding: 2rem;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    opacity: 0;
    transform: translateY(30px);
    min-width: 0;
}

.table-section.animate-in {
    opacity: 1;
    transform: translateY(0);
    animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.table-section.animate-out {
    opacity: 0;
    transform: translateY(30px);
    animation: fadeOutDown 0.6s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}

.table-section:hover {
    box-shadow: var(--shadow-lg);
}

.section-title {
    color: var(--text-primary);
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    margin-top: 0;
    position: relative;
    padding-bottom: 0.75rem;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 3rem;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    border-radius: 2px;
}

.search-container {
    margin-bottom: 1.5rem;
}

.search-field {
    display: flex;
    flex-direction: column;
    height: 3rem;
    width: 100%;
}

.search-wrapper {
    display: flex;
    width: 100%;
    flex: 1;
    align-items: stretch;
    border-radius: 0.75rem;
    height: 100%;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-wrapper:focus-within {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.search-icon {
    color: var(--text-secondary);
    display: flex;
    border: none;
    background: rgba(59, 130, 246, 0.05);
    align-items: center;
    justify-content: center;
    padding-left: 1rem;
    border-radius: 0.75rem 0 0 0.75rem;
    border-right: 0;
    transition: all 0.3s;
}

.search-input {
    flex: 1;
    min-width: 0;
    border: 2px solid var(--border-color);
    background: rgba(255, 255, 255, 0.8);
    height: 100%;
    padding: 0 1rem 0 0.5rem;
    border-radius: 0 0.75rem 0.75rem 0;
    border-left: 0;
    font-size: 0.9rem;
    color: var(--text-primary);
    outline: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-input:focus {
    border-color: #3b82f6;
}

.search-input::placeholder {
    color: var(--text-muted);
}

.table-container {
    overflow: hidden;
    border-radius: 1rem;
    border: 1px solid var(--border-color);
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(5px);
    box-shadow: var(--shadow-sm);
}

.table-wrapper {
    position: relative;
    overflow-x: auto;
    overflow-y: auto;
    max-height: 500px;
}

.data-table {
    width: 100%;
    margin-bottom: 0;
    font-size: 0.875rem;
    border-collapse: collapse;
    background: transparent;
    min-width: 600px;
}

.data-table th,
.data-table td {
    padding: 1rem 0.75rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.table-header {
    position: sticky;
    top: 0;
    background: linear-gradient(135deg, rgba(248, 250, 252, 0.95) 0%, rgba(241, 245, 249, 0.95) 100%);
    z-index: 10;
    backdrop-filter: blur(10px);
}

.table-header th {
    border-bottom: 2px solid var(--border-color);
    text-align: left;
    color: var(--text-primary);
    font-size: 0.875rem;
    font-weight: 600;
    line-height: normal;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.table-body tr {
    border-bottom: 1px solid var(--border-color);
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.table-body tr:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.02) 0%, rgba(139, 92, 246, 0.02) 100%);
    transform: scale(1.002);
}

.table-body td {
    color: var(--text-primary);
    font-size: 0.875rem;
    font-weight: normal;
    line-height: normal;
    vertical-align: middle;
}

.secondary-text {
    color: var(--text-secondary);
}

.table-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.375rem 0.75rem;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(139, 92, 246, 0.1) 100%);
    color: #3b82f6;
    font-size: 0.75rem;
    font-weight: 600;
    border-radius: 1rem;
    border: 1px solid rgba(59, 130, 246, 0.2);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
}

.table-badge:hover {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.table-link {
   color: #3b82f6;
   text-decoration: none;
   font-size: 0.875rem;
   font-weight: 500;
   padding: 0.375rem 0.75rem;
   border-radius: 0.375rem;
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   display: inline-block;
}

.table-link:hover {
   color: #1d4ed8;
   background: rgba(59, 130, 246, 0.1);
   transform: translateY(-1px);
}

.empty-state {
   padding: 3rem 1rem;
   text-align: center;
   color: var(--text-muted);
   font-size: 1rem;
   font-style: italic;
}

.table-info {
   margin-top: 1rem;
   padding: 1rem 1.5rem;
   background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
   border: 1px solid rgba(59, 130, 246, 0.2);
   border-radius: 0.75rem;
   backdrop-filter: blur(5px);
}

.info-text {
   margin: 0;
   font-size: 0.875rem;
   color: var(--text-secondary);
   font-weight: 500;
}

/* Messages */
.message {
   padding: 1rem 1.5rem;
   margin: 1rem 0;
   border-radius: 0.75rem;
   font-weight: 500;
   transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
   box-shadow: var(--shadow-sm);
   animation: slideInDown 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.message.success {
   background: var(--success-bg);
   color: var(--success-text);
   border: 1px solid var(--success-border);
}

.message.error {
   background: var(--error-bg);
   color: var(--error-text);
   border: 1px solid var(--error-border);
}

.message.warning {
   background: var(--warning-bg);
   color: var(--warning-text);
   border: 1px solid var(--warning-border);
}

/* Responsive Design */
@media (max-width: 1200px) {
    .content-wrapper {
        padding: 1.5rem;
    }
}

/* Tablet Mode - Keep tables side by side */
@media (max-width: 1024px) and (min-width: 481px) {
    .tables-container {
        display: flex;
        gap: 1.5rem;
    }
    
    .table-section {
        flex: 1;
        padding: 1.5rem;
        min-width: 0;
    }
    
    .data-table {
        min-width: 450px;
        font-size: 0.8rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.75rem 0.5rem;
    }
    
    .table-header th {
        font-size: 0.75rem;
    }
    
    .table-body td {
        font-size: 0.8rem;
    }
    
    .stats-container {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }
}

/* 768px and above - Tables side by side */
@media (max-width: 768px) and (min-width: 481px) {
    .content-wrapper {
        padding: 1rem;
    }
    
    .header {
        padding: 1rem;
    }
    
    .nav-links,
    .export-dropdown {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
    
    .mobile-nav-overlay.active,
    .mobile-nav-menu {
        display: block;
    }
    
    .page-title {
        font-size: 2rem;
    }
    
    .date-filter-form {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .date-range-inputs {
        flex-direction: column;
        gap: 1rem;
    }
    
    .date-input-group {
        min-width: auto;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    /* Keep tables side by side at 768px */
    .tables-container {
        display: flex;
        gap: 1rem;
    }
    
    .table-section {
        flex: 1;
        padding: 1rem;
        min-width: 0;
    }
    
    .data-table {
        min-width: 400px;
        font-size: 0.75rem;
    }
    
    .section-title {
        font-size: 1.25rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.6rem 0.4rem;
    }
    
    .table-wrapper {
        max-height: 350px;
    }
}

/* 480px and below - Stack tables vertically */
@media (max-width: 480px) {
    .content-wrapper {
        padding: 1rem;
    }
    
    .header {
        padding: 1rem;
    }
    
    .nav-links,
    .export-dropdown {
        display: none;
    }
    
    .mobile-menu-btn {
        display: flex;
    }
    
    .mobile-nav-overlay.active,
    .mobile-nav-menu {
        display: block;
    }
    
    .filter-container {
        padding: 1rem;
    }
    
    .stat-card {
        padding: 1.5rem;
        min-height: 150px;
    }
    
    .stat-value {
        font-size: 2rem;
    }
    
    .page-title {
        font-size: 1.75rem;
    }
    
    .mobile-nav-menu {
        width: 280px;
    }
    
    .date-filter-form {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .filter-group {
        min-width: auto;
    }
    
    .date-range-inputs {
        flex-direction: column;
        gap: 1rem;
    }
    
    .date-input-group {
        min-width: auto;
    }
    
    .stats-container {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    /* Stack tables vertically on small mobile */
    .tables-container {
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .table-section {
        flex: none;
        padding: 1rem;
    }
    
    .data-table {
        min-width: 350px;
        font-size: 0.75rem;
    }
    
    .section-title {
        font-size: 1.125rem;
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem 0.25rem;
    }
    
    .table-wrapper {
        max-height: 300px;
    }
}
@media (max-width: 480px) {
   .filter-container {
       padding: 1rem;
   }
   
   .stat-card {
       padding: 1.5rem;
       min-height: 150px;
   }
   
   .stat-value {
       font-size: 2rem;
   }
   
   .page-title {
       font-size: 1.75rem;
   }
   
   .mobile-nav-menu {
       width: 280px;
   }
   
   .data-table {
       min-width: 400px;
   }
   
   .data-table th,
   .data-table td {
       padding: 0.5rem 0.25rem;
   }
}

/* Animations */
@keyframes fadeInUp {
   from {
       opacity: 0;
       transform: translateY(30px);
   }
   to {
       opacity: 1;
       transform: translateY(0);
   }
}

@keyframes fadeOutDown {
   from {
       opacity: 1;
       transform: translateY(0);
   }
   to {
       opacity: 0;
       transform: translateY(30px);
   }
}

@keyframes slideInDown {
   from {
       opacity: 0;
       transform: translateY(-20px);
   }
   to {
       opacity: 1;
       transform: translateY(0);
   }
}

@keyframes dropdownFadeIn {
   from {
       opacity: 0;
       transform: translateY(-10px);
   }
   to {
       opacity: 1;
       transform: translateY(0);
   }
}

@keyframes pulse {
   0%, 100% {
       opacity: 1;
   }
   50% {
       opacity: 0.5;
   }
}

.fade-in {
   animation: fadeInUp 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Loading States */
.loading {
   animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Utility Classes */
.hidden {
   display: none !important;
}

.text-center {
   text-align: center;
}

.cursor-pointer {
   cursor: pointer;
}

/* Custom Scrollbar */
::-webkit-scrollbar {
   width: 8px;
   height: 8px;
}

::-webkit-scrollbar-track {
   background: rgba(241, 245, 249, 0.5);
   border-radius: 4px;
}

::-webkit-scrollbar-thumb {
   background: linear-gradient(135deg, rgba(59, 130, 246, 0.3), rgba(139, 92, 246, 0.3));
   border-radius: 4px;
   transition: all 0.3s;
}

::-webkit-scrollbar-thumb:hover {
   background: linear-gradient(135deg, rgba(59, 130, 246, 0.5), rgba(139, 92, 246, 0.5));
}

/* Focus Styles for Accessibility */
*:focus {
   outline: 2px solid rgba(59, 130, 246, 0.5);
   outline-offset: 2px;
}

button:focus,
input:focus,
select:focus {
   outline: 2px solid rgba(59, 130, 246, 0.5);
   outline-offset: 2px;
}

/* Enhanced Mobile Navigation */
.mobile-nav-overlay.active {
   display: block;
}

.mobile-nav-menu.active {
   display: block;
}

/* Enhanced Hover Effects */
.nav-link:hover,
.export-dropdown-btn:hover {
   box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.stat-card:hover {
   animation: none;
}

.stat-card.animate-in:hover {
   transform: translateY(-8px);
   box-shadow: var(--shadow-xl);
}

.table-section.animate-in:hover {
   transform: translateY(-2px);
   box-shadow: var(--shadow-lg);
}

/* Performance Optimizations */
.stat-card,
.table-section {
   will-change: transform, opacity;
}

.stat-card.animate-in,
.table-section.animate-in {
   will-change: auto;
}

/* Fix for broken layout */
.tables-container {
   width: 100%;
   overflow-x: auto;
}

.table-section {
   max-width: 100%;
   overflow: hidden;
}

/* Ensure proper initialization */
.stat-card,
.table-section {
   opacity: 1;
   transform: translateY(0);
}
</style>


    <link rel="icon" type="image/x-icon" href="data:image/x-icon;base64,">
</head>
<body>
    <div class="main-container">
        <header class="header">
            <div class="logo-section">
                <h2 class="logo-text">Vinodh English School</h2>
            </div>
            <div class="nav-section">
                <nav class="nav-links">
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    <?php if (hasPermission('manage_admissions')): ?>
                    <a class="nav-link" href="admission_inquiry.php">Admissions</a>
                    <?php endif; ?>
                    <?php if (hasPermission('manage_visitors')): ?>
                    <a class="nav-link" href="log_visitor.php">Visitors</a>
                    <?php endif; ?>
                    
                    <?php if (hasPermission('export_data')): ?>
                    <div class="export-dropdown">
                        <button class="export-dropdown-btn">
                            Export
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10L12 15L17 10H7Z"/>
                            </svg>
                        </button>
                        <div class="export-dropdown-content">
                            <a href="#" onclick="exportAdmissions('csv')" class="export-dropdown-link">
                                <span class="export-icon">📄</span> Admissions CSV
                            </a>
                            <a href="#" onclick="exportAdmissions('pdf')" class="export-dropdown-link">
                                <span class="export-icon">📋</span> Admissions PDF
                            </a>
                            <a href="#" onclick="exportVisitors('csv')" class="export-dropdown-link">
                                <span class="export-icon">📄</span> Visitors CSV
                            </a>
                            <a href="#" onclick="exportVisitors('pdf')" class="export-dropdown-link">
                                <span class="export-icon">📋</span> Visitors PDF
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <a class="nav-link" href="logout.php">Logout</a>
                </nav>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn" onclick="toggleMobileMenu()">
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                    <span class="hamburger-line"></span>
                </button>
                
                <div class="user-avatar" style="background-image: url('https://ui-avatars.com/api/?name=<?php echo urlencode($currentUser['full_name']); ?>&background=f4c653&color=1c170d');"></div>
            </div>
        </header>
        
        <!-- Mobile Navigation Overlay -->
        <div class="mobile-nav-overlay" onclick="closeMobileMenu()"></div>
        
        <!-- Mobile Navigation Menu -->
        <div class="mobile-nav-menu">
            <button class="mobile-nav-close" onclick="closeMobileMenu()">×</button>
            <div class="mobile-nav-links">
                <a class="mobile-nav-link active" href="dashboard.php">Dashboard</a>
                <?php if (hasPermission('manage_admissions')): ?>
                <a class="mobile-nav-link" href="admission_inquiry.php">Admissions</a>
                <?php endif; ?>
                <?php if (hasPermission('manage_visitors')): ?>
                <a class="mobile-nav-link" href="log_visitor.php">Visitors</a>
                <?php endif; ?>
                <a class="mobile-nav-link" href="logout.php">Logout</a>
                
                <?php if (hasPermission('export_data')): ?>
                <div class="mobile-export-section">
                    <div class="mobile-export-title">Export Options</div>
                    <a href="#" onclick="exportAdmissions('csv')" class="mobile-nav-link">
                        📄 Export Admissions CSV
                    </a>
                    <a href="#" onclick="exportAdmissions('pdf')" class="mobile-nav-link">
                        📋 Export Admissions PDF
                    </a>
                    <a href="#" onclick="exportVisitors('csv')" class="mobile-nav-link">
                        📄 Export Visitors CSV
                    </a>
                    <a href="#" onclick="exportVisitors('pdf')" class="mobile-nav-link">
                        📋 Export Visitors PDF
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="content-container">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Visitors & Admissions Dashboard</h1>
                        <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($currentUser['full_name']); ?>! Manage all visitor and admission inquiries</p>
                    </div>
                </div>
                
                <!-- Date Filter -->
                <div class="filter-container">
                    <form method="GET" action="" class="date-filter-form">
                        <div class="filter-group">
                            <label for="date_filter" class="filter-label">View Data For:</label>
                            <select name="date_filter" id="date_filter" class="filter-select" onchange="toggleDatePicker()">
                                <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                                <option value="custom" <?php echo $dateFilter === 'custom' ? 'selected' : ''; ?>>Custom Date</option>
                                <option value="range" <?php echo $dateFilter === 'range' ? 'selected' : ''; ?>>Date Range</option>
                                <option value="all" <?php echo $dateFilter === 'all' ? 'selected' : ''; ?>>All Time</option>
                            </select>
                        </div>
                        <div class="filter-group" id="date-picker-group" style="<?php echo $dateFilter === 'custom' ? 'display: block;' : 'display: none;'; ?>">
                            <label for="selected_date" class="filter-label">Select Date:</label>
                            <input type="date" name="selected_date" id="selected_date" class="filter-input" value="<?php echo htmlspecialchars($selectedDate); ?>">
                        </div>
                        <div class="filter-group" id="date-range-group" style="<?php echo $dateFilter === 'range' ? 'display: flex;' : 'display: none;'; ?>">
                            <div class="date-range-inputs">
                                <div class="date-input-group">
                                    <label for="start_date" class="filter-label">From Date:</label>
                                    <input type="date" name="start_date" id="start_date" class="filter-input" value="<?php echo htmlspecialchars($startDate); ?>">
                                </div>
                                <div class="date-input-group">
                                    <label for="end_date" class="filter-label">To Date:</label>
                                    <input type="date" name="end_date" id="end_date" class="filter-input" value="<?php echo htmlspecialchars($endDate); ?>">
                                </div>
                            </div>
                        </div>
                        <div class="filter-group">
                            <button type="submit" class="filter-btn">Apply Filter</button>
                        </div>
                        <!-- Preserve search terms -->
                        <input type="hidden" name="admission_search" value="<?php echo htmlspecialchars($admissionSearch); ?>">
                        <input type="hidden" name="visitor_search" value="<?php echo htmlspecialchars($visitorSearch); ?>">
                    </form>
                </div>

                <div class="stats-container">
                    <div class="stat-card" data-animate="true">
                        <p class="stat-label"><?php echo $statsLabel; ?> Admission Inquiries</p>
                        <p class="stat-value"><?php echo $todayAdmissions; ?></p>
                        <div class="card-spacer"></div>
                    </div>
                    <div class="stat-card" data-animate="true">
                        <p class="stat-label"><?php echo $statsLabel; ?> Visitors</p>
                        <p class="stat-value"><?php echo $todayVisitors; ?></p>
                        <div class="card-spacer"></div>
                    </div>
                </div>
                
                <div class="tables-container">
                    <div class="table-section" data-animate="true">
                        <?php if (hasPermission('manage_admissions')): ?>
                        <h2 class="section-title">Admission Inquiries</h2>
                        <div class="search-container">
                            <form method="GET" action="">
                                <div class="search-field">
                                    <div class="search-wrapper">
                                        <div class="search-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                                                <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
                                            </svg>
                                        </div>
                                        <input name="admission_search" placeholder="Search admission inquiries" class="search-input" value="<?php echo htmlspecialchars($admissionSearch); ?>">
                                        <input type="hidden" name="visitor_search" value="<?php echo htmlspecialchars($visitorSearch); ?>">
                                        <input type="hidden" name="date_filter" value="<?php echo htmlspecialchars($dateFilter); ?>">
                                        <input type="hidden" name="selected_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                                        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="table-container">
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead class="table-header">
                                        <tr>
                                            <th>Sl. No.</th>
                                            <th>Child Name</th>
                                            <th>Parent</th>
                                            <th>Phone</th>
                                            <th>Class</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-body">
                                        <?php 
                                        $serialNumber = 1;
                                        foreach ($admissions as $admission): ?>
                                        <tr>
                                            <td><?php echo $serialNumber++; ?></td>
                                            <td><?php echo htmlspecialchars($admission['child_name']); ?></td>
                                            <td class="secondary-text">
                                                <?php echo htmlspecialchars($admission['parent_name']); ?>
                                                <?php if ($admission['parent_email']): ?>
                                                <br><small><?php echo htmlspecialchars($admission['parent_email']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="secondary-text"><?php echo htmlspecialchars($admission['phone_number']); ?></td>
                                            <td>
                                                <button class="table-badge">
                                                    <?php echo htmlspecialchars($admission['desired_class']); ?>
                                                </button>
                                            </td>
                                            <td class="secondary-text"><?php echo date('M j, Y', strtotime($admission['inquiry_date'])); ?></td>
                                            <td>
                                                <a href="#" onclick="viewAdmission(<?php echo $admission['id']; ?>)" class="table-link">View</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($admissions)): ?>
                                        <tr>
                                            <td colspan="7" class="empty-state">
                                                <?php echo $admissionSearch ? 'No admission inquiries found matching your search.' : 'No admission inquiries found.'; ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <?php 
                        // Check if there are more admissions
                        if (hasPermission('manage_admissions')) {
                            $totalAdmissions = $db->count('admission_inquiries', $admissionWhere, $admissionParams);
                            if ($totalAdmissions > 10): ?>
                            <div class="table-info">
                                <p class="info-text">📋 Showing latest 10 of <?php echo $totalAdmissions; ?> admission inquiries. Use search or date filter to find specific records.</p>
                            </div>
                            <?php endif;
                        } ?>
                        
                        <?php endif; ?>
                    </div>
                
                    <div class="table-section" data-animate="true">
                        <?php if (hasPermission('manage_visitors')): ?>
                        <h2 class="section-title">Visitors</h2>
                        <div class="search-container">
                            <form method="GET" action="">
                                <div class="search-field">
                                    <div class="search-wrapper">
                                        <div class="search-icon">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" fill="currentColor" viewBox="0 0 256 256">
                                                <path d="M229.66,218.34l-50.07-50.06a88.11,88.11,0,1,0-11.31,11.31l50.06,50.07a8,8,0,0,0,11.32-11.32ZM40,112a72,72,0,1,1,72,72A72.08,72.08,0,0,1,40,112Z"></path>
                                            </svg>
                                        </div>
                                        <input name="visitor_search" placeholder="Search visitor inquiries" class="search-input" value="<?php echo htmlspecialchars($visitorSearch); ?>">
                                        <input type="hidden" name="admission_search" value="<?php echo htmlspecialchars($admissionSearch); ?>">
                                        <input type="hidden" name="date_filter" value="<?php echo htmlspecialchars($dateFilter); ?>">
                                        <input type="hidden" name="selected_date" value="<?php echo htmlspecialchars($selectedDate); ?>">
                                        <input type="hidden" name="start_date" value="<?php echo htmlspecialchars($startDate); ?>">
                                        <input type="hidden" name="end_date" value="<?php echo htmlspecialchars($endDate); ?>">
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <div class="table-container">
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead class="table-header">
                                        <tr>
                                            <th>Sl. No.</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Purpose</th>
                                            <th>Reporting Person</th>
                                            <th>Visit Date & Time</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-body">
                                        <?php 
                                        $serialNumber = 1;
                                        foreach ($visitors as $visitor): ?>
                                        <tr>
                                            <td><?php echo $serialNumber++; ?></td>
                                            <td><?php echo htmlspecialchars($visitor['visitor_name']); ?></td>
                                            <td class="secondary-text"><?php echo htmlspecialchars($visitor['phone_number']); ?></td>
                                            <td>
                                                <button class="table-badge">
                                                    <?php echo htmlspecialchars($visitor['purpose']); ?>
                                                </button>
                                            </td>
                                            <td class="secondary-text"><?php echo htmlspecialchars($visitor['reporting_person'] ?? 'N/A'); ?></td>
                                            <td class="secondary-text">
                                                <?php echo date('M j, Y', strtotime($visitor['visit_date'])); ?>
                                                <br><small><?php echo date('g:i A', strtotime($visitor['visit_date'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="#" onclick="viewVisitor(<?php echo $visitor['id']; ?>)" class="table-link">View</a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($visitors)): ?>
                                        <tr>
                                            <td colspan="7" class="empty-state">
                                                <?php echo $visitorSearch ? 'No visitors found matching your search.' : 'No visitors found.'; ?>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <?php 
                        // Check if there are more visitors
                        if (hasPermission('manage_visitors')) {
                            $totalVisitors = $db->count('visitors', $visitorWhere, $visitorParams);
                            if ($totalVisitors > 10): ?>
                            <div class="table-info">
                                <p class="info-text">👥 Showing latest 10 of <?php echo $totalVisitors; ?> visitors. Use search or date filter to find specific records.</p>
                            </div>
                            <?php endif;
                        } ?>
                        
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Mobile menu functions
        function toggleMobileMenu() {
            const btn = document.querySelector('.mobile-menu-btn');
            const overlay = document.querySelector('.mobile-nav-overlay');
            const menu = document.querySelector('.mobile-nav-menu');
            
            btn.classList.toggle('active');
            overlay.classList.toggle('active');
            menu.classList.toggle('active');
            
            if (menu.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        function closeMobileMenu() {
            const btn = document.querySelector('.mobile-menu-btn');
            const overlay = document.querySelector('.mobile-nav-overlay');
            const menu = document.querySelector('.mobile-nav-menu');
            
            btn.classList.remove('active');
            overlay.classList.remove('active');
            menu.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        // Intersection Observer for scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                    entry.target.classList.remove('animate-out');
                } else {
                    entry.target.classList.add('animate-out');
                    entry.target.classList.remove('animate-in');
                }
            });
        }, observerOptions);
        
        // Observe all cards and table sections
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('[data-animate="true"]');
            animatedElements.forEach(element => {
                observer.observe(element);
            });
        });
        
        // Export functions
        function exportAdmissions(format = 'csv') {
            const search = '<?php echo urlencode($admissionSearch); ?>';
            const dateFilter = '<?php echo urlencode($dateFilter); ?>';
            const selectedDate = '<?php echo urlencode($selectedDate); ?>';
            const startDate = '<?php echo urlencode($startDate); ?>';
            const endDate = '<?php echo urlencode($endDate); ?>';
            
            let url = '';
            if (format === 'pdf') {
                url = '../export_pdf.php?type=admissions&search=' + search + '&date_filter=' + dateFilter + '&selected_date=' + selectedDate + '&start_date=' + startDate + '&end_date=' + endDate + '#print';
            } else {
                url = '../export.php?type=admissions&format=' + format + '&search=' + search + '&date_filter=' + dateFilter + '&selected_date=' + selectedDate + '&start_date=' + startDate + '&end_date=' + endDate;
            }
            window.open(url, '_blank');
            closeMobileMenu(); // Close mobile menu if open
        }

        function exportVisitors(format = 'csv') {
            const search = '<?php echo urlencode($visitorSearch); ?>';
            const dateFilter = '<?php echo urlencode($dateFilter); ?>';
            const selectedDate = '<?php echo urlencode($selectedDate); ?>';
            const startDate = '<?php echo urlencode($startDate); ?>';
            const endDate = '<?php echo urlencode($endDate); ?>';
            
            let url = '';
            if (format === 'pdf') {
                url = '../export_pdf.php?type=visitors&search=' + search + '&date_filter=' + dateFilter + '&selected_date=' + selectedDate + '&start_date=' + startDate + '&end_date=' + endDate + '#print';
            } else {
                url = '../export.php?type=visitors&format=' + format + '&search=' + search + '&date_filter=' + dateFilter + '&selected_date=' + selectedDate + '&start_date=' + startDate + '&end_date=' + endDate;
            }
            window.open(url, '_blank');
            closeMobileMenu(); // Close mobile menu if open
        }

        function viewAdmission(id) {
            showAdmissionDetails(id);
        }

        function viewVisitor(id) {
            showVisitorDetails(id);
        }

        // Toggle date picker visibility
        function toggleDatePicker() {
            const dateFilter = document.getElementById('date_filter').value;
            const datePickerGroup = document.getElementById('date-picker-group');
            const dateRangeGroup = document.getElementById('date-range-group');
            
            datePickerGroup.style.display = 'none';
            dateRangeGroup.style.display = 'none';
            
            if (dateFilter === 'custom') {
                datePickerGroup.style.display = 'block';
            } else if (dateFilter === 'range') {
                dateRangeGroup.style.display = 'flex';
            }
        }

        // Auto-submit search forms
        document.querySelectorAll('input[name="admission_search"], input[name="visitor_search"]').forEach(input => {
            let timeout;
            input.addEventListener('input', function() {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    this.form.submit();
                }, 500);
            });
        });
    </script>
</body>
</html>