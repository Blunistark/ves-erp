<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'includes/connection.php';
require_once 'includes/functions.php';

// Require authentication and export permission
requirePermission('export_data');

// Check if export type is specified
if (!isset($_GET['type']) || !in_array($_GET['type'], ['admissions', 'visitors'])) {
    die('Invalid export type');
}

$type = $_GET['type'];
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$dateFilter = isset($_GET['date_filter']) ? trim($_GET['date_filter']) : 'today';
$selectedDate = isset($_GET['selected_date']) ? trim($_GET['selected_date']) : date('Y-m-d');
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : date('Y-m-d');
$endDate = isset($_GET['end_date']) ? trim($_GET['end_date']) : date('Y-m-d');

// Generate filename based on date filter
$filename = $type . '_';
if ($dateFilter === 'all') {
    $filename .= 'all_time_';
} elseif ($dateFilter === 'custom' && $selectedDate) {
    $filename .= $selectedDate . '_';
} elseif ($dateFilter === 'range' && $startDate && $endDate) {
    if ($startDate === $endDate) {
        $filename .= $startDate . '_';
    } else {
        $filename .= $startDate . '_to_' . $endDate . '_';
    }
} else {
    $filename .= date('Y-m-d') . '_';
}
$filename .= date('H-i-s') . '.csv';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Open output stream
$output = fopen('php://output', 'w');

if ($type === 'admissions') {
    // CSV headers for admissions
    fputcsv($output, [
        'Sl. No.',
        'Child Name',
        'Parent Name', 
        'Parent Email',
        'Phone Number',
        'Desired Class',
        'Address',
        'Specific Requirements',
        'Parent Notes',
        'Inquiry Date',
        'Status'
    ]);

    // Build query with search and date filter
    $where = '1=1';
    $params = [];
    
    // Add date filter
    $today = date('Y-m-d');
    if ($dateFilter === 'today') {
        $where .= ' AND DATE(inquiry_date) = ?';
        $params[] = $today;
    } elseif ($dateFilter === 'custom' && $selectedDate) {
        $where .= ' AND DATE(inquiry_date) = ?';
        $params[] = $selectedDate;
    } elseif ($dateFilter === 'range' && $startDate && $endDate) {
        $where .= ' AND DATE(inquiry_date) BETWEEN ? AND ?';
        $params[] = $startDate;
        $params[] = $endDate;
    }
    // For 'all' filter, no date restriction is added
    
    if ($search) {
        $where .= ' AND (child_name LIKE ? OR parent_name LIKE ? OR parent_email LIKE ? OR phone_number LIKE ?)';
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    // Get admission data
    $admissions = $db->fetchAll(
        "SELECT * FROM admission_inquiries WHERE $where ORDER BY inquiry_date DESC",
        $params
    );

    // Output data rows
    $serialNumber = 1;
    foreach ($admissions as $admission) {
        fputcsv($output, [
            $serialNumber++,
            $admission['child_name'],
            $admission['parent_name'],
            $admission['parent_email'],
            $admission['phone_number'],
            $admission['desired_class'],
            $admission['address'],
            $admission['specific_requirements'],
            $admission['parent_notes'],
            $admission['inquiry_date'],
            $admission['status']
        ]);
    }

} else if ($type === 'visitors') {
    // CSV headers for visitors
    fputcsv($output, [
        'Sl. No.',
        'Visitor Name',
        'Email',
        'Phone Number',
        'Purpose',
        'Reporting Person',
        'Visit Date',
        'Status'
    ]);

    // Build query with search and date filter
    $where = '1=1';
    $params = [];
    
    // Add date filter
    $today = date('Y-m-d');
    if ($dateFilter === 'today') {
        $where .= ' AND DATE(visit_date) = ?';
        $params[] = $today;
    } elseif ($dateFilter === 'custom' && $selectedDate) {
        $where .= ' AND DATE(visit_date) = ?';
        $params[] = $selectedDate;
    } elseif ($dateFilter === 'range' && $startDate && $endDate) {
        $where .= ' AND DATE(visit_date) BETWEEN ? AND ?';
        $params[] = $startDate;
        $params[] = $endDate;
    }
    // For 'all' filter, no date restriction is added
    
    if ($search) {
        $where .= ' AND (visitor_name LIKE ? OR email LIKE ? OR phone_number LIKE ? OR purpose LIKE ?)';
        $searchTerm = "%$search%";
        $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }

    // Get visitor data
    $visitors = $db->fetchAll(
        "SELECT * FROM visitors WHERE $where ORDER BY visit_date DESC",
        $params
    );

    // Output data rows
    $serialNumber = 1;
    foreach ($visitors as $visitor) {
        fputcsv($output, [
            $serialNumber++,
            $visitor['visitor_name'],
            $visitor['email'],
            $visitor['phone_number'],
            $visitor['purpose'],
            $visitor['reporting_person'] ?? 'N/A',
            $visitor['visit_date'],
            $visitor['status']
        ]);
    }
}

// Log the export activity
$logDetails = "Type: $type, Format: CSV, Search: $search, Date Filter: $dateFilter";
if ($dateFilter === 'custom') {
    $logDetails .= ", Selected Date: $selectedDate";
} elseif ($dateFilter === 'range') {
    $logDetails .= ", Date Range: $startDate to $endDate";
}
logActivity('Data Export', $logDetails);

fclose($output);
exit;
?>