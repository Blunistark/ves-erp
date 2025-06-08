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

// Set headers for HTML display
header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($type); ?> Export - Vinodh English School</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .page-break { page-break-before: always; }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #f4c653;
            padding-bottom: 20px;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 10px;
        }
        
        .school-logo {
            width: 60px;
            height: 60px;
            flex-shrink: 0;
        }
        
        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #1c170d;
            margin: 0;
        }
        
        .report-title {
            font-size: 18px;
            color: #8f7f56;
            margin-bottom: 5px;
        }
        
        .report-info {
            font-size: 12px;
            color: #666;
            margin-top: 10px;
        }
        
        .print-button {
            background-color: #f4c653;
            color: #1c170d;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .print-button:hover {
            background-color: #f2c240;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f4c653;
            font-weight: bold;
            color: #1c170d;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-button" onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button class="print-button" onclick="window.close()" style="background-color: #ccc;">❌ Close</button>
    </div>

    <div class="header">
        <div class="header-content">
            <img src="./assets/images/school-logo.png" alt="Vinodh English School Logo" class="school-logo">
            <div class="school-name">VINODH ENGLISH SCHOOL</div>
        </div>
        <div class="report-title"><?php echo ucfirst($type); ?> Report</div>
        <div class="report-info">
            Generated on: <?php echo date('F j, Y g:i A'); ?>
            <?php if ($dateFilter === 'all'): ?>
                <br>Date Range: All Time
            <?php elseif ($dateFilter === 'custom' && $selectedDate): ?>
                <br>Date Range: <?php echo date('F j, Y', strtotime($selectedDate)); ?>
            <?php elseif ($dateFilter === 'range' && $startDate && $endDate): ?>
                <?php if ($startDate === $endDate): ?>
                    <br>Date Range: <?php echo date('F j, Y', strtotime($startDate)); ?>
                <?php else: ?>
                    <br>Date Range: <?php echo date('F j, Y', strtotime($startDate)) . ' - ' . date('F j, Y', strtotime($endDate)); ?>
                <?php endif; ?>
            <?php else: ?>
                <br>Date Range: Today (<?php echo date('F j, Y'); ?>)
            <?php endif; ?>
            <?php if ($search): ?>
                <br>Search Filter: "<?php echo htmlspecialchars($search); ?>"
            <?php endif; ?>
        </div>
    </div>

    <?php
    if ($type === 'admissions') {
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
        
        echo '<div class="summary">';
        echo '<strong>Summary:</strong> ' . count($admissions) . ' admission inquiries found';
        if ($dateFilter === 'all') {
            echo ' (all time)';
        } elseif ($dateFilter === 'custom' && $selectedDate) {
            echo ' for ' . date('F j, Y', strtotime($selectedDate));
        } elseif ($dateFilter === 'range' && $startDate && $endDate) {
            if ($startDate === $endDate) {
                echo ' for ' . date('F j, Y', strtotime($startDate));
            } else {
                echo ' from ' . date('F j, Y', strtotime($startDate)) . ' to ' . date('F j, Y', strtotime($endDate));
            }
        } else {
            echo ' for today';
        }
        if ($search) {
            echo ' matching search criteria';
        }
        echo '</div>';
        
        // Main table
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Sl. No.</th>';
        echo '<th>Child Name</th>';
        echo '<th>Parent Name</th>';
        echo '<th>Email</th>';
        echo '<th>Phone</th>';
        echo '<th>Class</th>';
        echo '<th>Date</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        $serialNumber = 1;
        foreach ($admissions as $admission) {
            echo '<tr>';
            echo '<td>' . $serialNumber++ . '</td>';
            echo '<td>' . htmlspecialchars($admission['child_name']) . '</td>';
            echo '<td>' . htmlspecialchars($admission['parent_name']) . '</td>';
            echo '<td>' . htmlspecialchars($admission['parent_email']) . '</td>';
            echo '<td>' . htmlspecialchars($admission['phone_number']) . '</td>';
            echo '<td>' . htmlspecialchars($admission['desired_class']) . '</td>';
            echo '<td>' . date('M j, Y', strtotime($admission['inquiry_date'])) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
    } else if ($type === 'visitors') {
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
        
        echo '<div class="summary">';
        echo '<strong>Summary:</strong> ' . count($visitors) . ' visitor records found';
        if ($dateFilter === 'all') {
            echo ' (all time)';
        } elseif ($dateFilter === 'custom' && $selectedDate) {
            echo ' for ' . date('F j, Y', strtotime($selectedDate));
        } elseif ($dateFilter === 'range' && $startDate && $endDate) {
            if ($startDate === $endDate) {
                echo ' for ' . date('F j, Y', strtotime($startDate));
            } else {
                echo ' from ' . date('F j, Y', strtotime($startDate)) . ' to ' . date('F j, Y', strtotime($endDate));
            }
        } else {
            echo ' for today';
        }
        if ($search) {
            echo ' matching search criteria';
        }
        echo '</div>';
        
        // Main table
        echo '<table>';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Sl. No.</th>';
        echo '<th>Visitor Name</th>';
        echo '<th>Email</th>';
        echo '<th>Phone</th>';
        echo '<th>Purpose</th>';
        echo '<th>Reporting Person</th>';
        echo '<th>Visit Date</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        $serialNumber = 1;
        foreach ($visitors as $visitor) {
            echo '<tr>';
            echo '<td>' . $serialNumber++ . '</td>';
            echo '<td>' . htmlspecialchars($visitor['visitor_name']) . '</td>';
            echo '<td>' . htmlspecialchars($visitor['email'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($visitor['phone_number']) . '</td>';
            echo '<td>' . htmlspecialchars($visitor['purpose']) . '</td>';
            echo '<td>' . htmlspecialchars($visitor['reporting_person'] ?? 'N/A') . '</td>';
            echo '<td>' . date('M j, Y g:i A', strtotime($visitor['visit_date'])) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    }
    
    // Log the export activity
    $logDetails = "Type: $type, Search: $search, Date Filter: $dateFilter";
    if ($dateFilter === 'custom') {
        $logDetails .= ", Selected Date: $selectedDate";
    } elseif ($dateFilter === 'range') {
        $logDetails .= ", Date Range: $startDate to $endDate";
    }
    logActivity('PDF Export', $logDetails);
    ?>

    <div class="footer">
        Generated by Vinodh English School Admin System<br>
        Date: <?php echo date('Y-m-d H:i:s'); ?>
    </div>

    <script>
        // Auto-focus print dialog if requested
        if (window.location.hash === '#print') {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 500);
            };
        }
    </script>
</body>
</html> 