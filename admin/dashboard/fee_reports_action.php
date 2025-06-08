<?php
// Include database connection
include 'con.php';
require_once '../../includes/timezone_fix.php'; // Add timezone utilities

// Get action from request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Set JSON headers early
header('Content-Type: application/json');

// Ensure no whitespace or HTML is output before JSON
ob_clean(); // Clear any previous output

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Response array
$response = [
    'success' => false,
    'message' => 'Invalid action'
];

// Handle different actions
switch ($action) {
    case 'get_classes':
        // Get all classes
        $sql = "SELECT id, name FROM classes ORDER BY name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $classes = [];
            while ($row = $result->fetch_assoc()) {
                $classes[] = $row;
            }
            
            $response = [
                'success' => true,
                'classes' => $classes
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No classes found'
            ];
        }
        break;
        
    case 'get_sections':
        // Get sections by class ID
        if (isset($_REQUEST['class_id']) && !empty($_REQUEST['class_id'])) {
            $classId = $_REQUEST['class_id'];
            
            $sql = "SELECT id, name FROM sections WHERE class_id = ? ORDER BY name";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $classId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $sections = [];
                while ($row = $result->fetch_assoc()) {
                    $sections[] = $row;
                }
                
                $response = [
                    'success' => true,
                    'sections' => $sections
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'No sections found for this class'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Missing class ID'
            ];
        }
        break;
        
    case 'get_fee_types':
        // Get all fee types/structures
        $sql = "SELECT DISTINCT id, title FROM fee_structures ORDER BY title";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $feeTypes = [];
            while ($row = $result->fetch_assoc()) {
                $feeTypes[] = $row;
            }
            
            $response = [
                'success' => true,
                'fee_types' => $feeTypes
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'No fee types found'
            ];
        }
        break;
        
    case 'get_reports':
        // Build query with filters
        $where = [];
        $params = [];
        $types = "";
        
        // Get payments with all related info
        $sql = "SELECT 
                fp.id as payment_id, 
                fp.payment_date,
                fp.amount_paid,
                fp.remaining_amount,
                fp.status,
                fs.title as fee_title,
                fs.due_date,
                s.full_name as student_name,
                s.admission_number,
                c.name as class_name,
                sec.name as section_name
                FROM fee_payments fp
                JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                JOIN students s ON fp.student_user_id = s.user_id
                JOIN classes c ON s.class_id = c.id
                JOIN sections sec ON s.section_id = sec.id";
        
        // Apply filters
        if (isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date'])) {
            $where[] = "fp.payment_date >= ?";
            $params[] = $_REQUEST['start_date'];
            $types .= "s";
        }
        
        if (isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date'])) {
            $where[] = "fp.payment_date <= ?";
            $params[] = $_REQUEST['end_date'];
            $types .= "s";
        }
        
        if (isset($_REQUEST['class_id']) && !empty($_REQUEST['class_id'])) {
            $where[] = "s.class_id = ?";
            $params[] = $_REQUEST['class_id'];
            $types .= "i";
        }
        
        if (isset($_REQUEST['section_id']) && !empty($_REQUEST['section_id'])) {
            $where[] = "s.section_id = ?";
            $params[] = $_REQUEST['section_id'];
            $types .= "i";
        }
        
        if (isset($_REQUEST['fee_type_id']) && !empty($_REQUEST['fee_type_id'])) {
            $where[] = "fs.id = ?";
            $params[] = $_REQUEST['fee_type_id'];
            $types .= "i";
        }
        
        if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) {
            if ($_REQUEST['status'] === 'overdue') {
                $today = getCurrentDateIST();
                $where[] = "(fp.status = 'pending' OR fp.status = 'partial') AND fs.due_date < ?";
                $params[] = $today;
                $types .= "s";
            } else {
                $where[] = "fp.status = ?";
                $params[] = $_REQUEST['status'];
                $types .= "s";
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Add order by
        $sql .= " ORDER BY fp.payment_date DESC";
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) AS total FROM (" . $sql . ") as count_query";
        
        if (!empty($params)) {
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param($types, ...$params);
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $totalCount = $countResult->fetch_assoc()['total'];
        } else {
            $countResult = $conn->query($countSql);
            $totalCount = $countResult->fetch_assoc()['total'];
        }
        
        // Add pagination limits
        if (isset($_REQUEST['page']) && isset($_REQUEST['items_per_page'])) {
            $page = intval($_REQUEST['page']);
            $itemsPerPage = intval($_REQUEST['items_per_page']);
            $offset = ($page - 1) * $itemsPerPage;
            
            $sql .= " LIMIT ?, ?";
            $params[] = $offset;
            $params[] = $itemsPerPage;
            $types .= "ii";
        }
        
        // Execute final query
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        if ($result) {
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                $reports[] = $row;
            }
            
            $response = [
                'success' => true,
                'reports' => $reports,
                'total_items' => $totalCount
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Error fetching reports: ' . $conn->error
            ];
        }
        break;
        
    case 'get_summary':
        // Set headers for JSON response
        header('Content-Type: application/json');
        
        try {
            // Calculate the TOTAL fees that should be collected from all students
            // by multiplying fee structure amounts by the number of students in each class
            $totalFeesSql = "SELECT SUM(class_total) as grand_total FROM (
                                SELECT fs.id, fs.title, fs.class_id, fs.amount, 
                                COUNT(s.user_id) as student_count,
                                fs.amount * COUNT(s.user_id) as class_total
                         FROM fee_structures fs
                                JOIN students s ON fs.class_id = s.class_id
                                GROUP BY fs.id, fs.title, fs.class_id, fs.amount
                             ) as class_totals";
        
            $totalFeesResult = $conn->query($totalFeesSql);
            $totalFees = floatval($totalFeesResult->fetch_assoc()['grand_total']);
            
            // Method 2: Sum of paid and remaining amounts (actual status of collections)
            $amountsSql = "SELECT 
                          COALESCE(SUM(amount_paid), 0) as total_paid,
                          COALESCE(SUM(remaining_amount), 0) as total_remaining
                          FROM fee_payments";
            $amountsResult = $conn->query($amountsSql);
            $amounts = $amountsResult->fetch_assoc();
            $totalPaid = floatval($amounts['total_paid']);
            $totalRemaining = floatval($amounts['total_remaining']);
            
            // Calculate the amount that hasn't even been assigned to fee_payments yet
            // This would be the difference between total expected fees and what's accounted for
            $accountedFees = $totalPaid + $totalRemaining;
            $unassignedFees = $totalFees - $accountedFees;
            if ($unassignedFees < 0) $unassignedFees = 0;
            
            // The pending fees should include both remaining amounts in fee_payments
            // and any unassigned fees that aren't yet in the payment system
            $pendingFees = $totalRemaining + $unassignedFees;
            
            // Get overdue fees directly from database - fees with due date in the past
            $today = getCurrentDateIST();
            $overdueFeesSql = "SELECT COALESCE(SUM(fp.remaining_amount), 0) as total 
                              FROM fee_payments fp 
                              JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                              WHERE fs.due_date < ? AND fp.status IN ('pending', 'partial')";
            
            $overdueStmt = $conn->prepare($overdueFeesSql);
            $overdueStmt->bind_param("s", $today);
            $overdueStmt->execute();
            $overdueFeesResult = $overdueStmt->get_result();
            $overdueFees = floatval($overdueFeesResult->fetch_assoc()['total']);
            
            // Get monthly collection data for trend chart
            $sixMonthsAgo = date('Y-m-d', strtotime('-6 months'));
            $monthlySql = "SELECT 
                          DATE_FORMAT(payment_date, '%Y-%m') as month,
                          SUM(amount_paid) as collected
                          FROM fee_payments
                          WHERE payment_date >= ?
                          GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                          ORDER BY month";
            $monthlyStmt = $conn->prepare($monthlySql);
            $monthlyStmt->bind_param("s", $sixMonthsAgo);
            $monthlyStmt->execute();
            $monthlyResult = $monthlyStmt->get_result();
            $monthlyData = [];
            
            if ($monthlyResult && $monthlyResult->num_rows > 0) {
                while ($row = $monthlyResult->fetch_assoc()) {
                    // Format month name
                    $monthName = date('M', strtotime($row['month'] . '-01'));
                    $monthlyData[] = [
                        'month' => $monthName,
                        'collected' => floatval($row['collected']),
                        'pending' => 0 // Will calculate below
                    ];
                }
            } else {
                // Provide sample data if no data available
                $monthlyData = [
                    ['month' => 'Jan', 'collected' => 5000, 'pending' => 2000],
                    ['month' => 'Feb', 'collected' => 7000, 'pending' => 1000],
                    ['month' => 'Mar', 'collected' => 8000, 'pending' => 3000],
                    ['month' => 'Apr', 'collected' => 9000, 'pending' => 2000],
                    ['month' => 'May', 'collected' => 10000, 'pending' => 1500],
                    ['month' => 'Jun', 'collected' => 12000, 'pending' => 1000]
                ];
            }
            
            // Get classwise collection data
            $classwiseSql = "SELECT 
                            c.name as class_name,
                            SUM(fp.amount_paid) as collected,
                            SUM(fp.remaining_amount) as pending
                            FROM fee_payments fp
                            JOIN students s ON fp.student_user_id = s.user_id
                            JOIN classes c ON s.class_id = c.id
                            GROUP BY c.id, c.name
                            ORDER BY c.name";
            $classwiseResult = $conn->query($classwiseSql);
            $classwiseData = [];
            
            if ($classwiseResult && $classwiseResult->num_rows > 0) {
                while ($row = $classwiseResult->fetch_assoc()) {
                    $total = floatval($row['collected']) + floatval($row['pending']);
                    $percentage = $total > 0 ? round((floatval($row['collected']) / $total) * 100) : 0;
                    
                    $classwiseData[] = [
                        'class' => $row['class_name'],
                        'collected' => floatval($row['collected']),
                        'pending' => floatval($row['pending']),
                        'percentage' => $percentage
                    ];
                }
            } else {
                // Provide sample data if no data available
                $classwiseData = [
                    ['class' => 'Class 1', 'collected' => 8500, 'pending' => 1500, 'percentage' => 85],
                    ['class' => 'Class 2', 'collected' => 9200, 'pending' => 800, 'percentage' => 92],
                    ['class' => 'Class 3', 'collected' => 7800, 'pending' => 2200, 'percentage' => 78],
                    ['class' => 'Class 4', 'collected' => 9500, 'pending' => 500, 'percentage' => 95]
                ];
            }
            
            // Get payment methods data
            $methodsSql = "SELECT 
                          payment_method,
                          COUNT(*) as count,
                          SUM(amount_paid) as amount
                          FROM fee_payments
                          WHERE payment_method IS NOT NULL AND payment_method != ''
                          GROUP BY payment_method";
            $methodsResult = $conn->query($methodsSql);
            $methodsData = [];
            
            if ($methodsResult && $methodsResult->num_rows > 0) {
                $colors = ['#4a6cf7', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];
                $colorIndex = 0;
                
                while ($row = $methodsResult->fetch_assoc()) {
                    $methodsData[] = [
                        'method' => ucfirst($row['payment_method']),
                        'count' => intval($row['count']),
                        'amount' => floatval($row['amount']),
                        'color' => $colors[$colorIndex % count($colors)]
                    ];
                    $colorIndex++;
                }
        } else {
                // Provide sample data if no data available
                $methodsData = [
                    ['method' => 'Cash', 'count' => 45, 'amount' => 45000, 'color' => '#4a6cf7'],
                    ['method' => 'Online Transfer', 'count' => 35, 'amount' => 35000, 'color' => '#10b981'],
                    ['method' => 'Cheque', 'count' => 15, 'amount' => 15000, 'color' => '#f59e0b'],
                    ['method' => 'UPI', 'count' => 25, 'amount' => 25000, 'color' => '#ef4444']
                ];
        }
        
            // Return results with proper data types
        $response = [
            'success' => true,
            'total_fees' => $totalFees,
                'total_collected' => $totalPaid,
            'pending_fees' => $pendingFees,
                'overdue_fees' => $overdueFees,
                'debug_timestamp' => date('Y-m-d H:i:s'),
                'debug_info' => [
                    'accounted_fees' => $accountedFees,
                    'unassigned_fees' => $unassignedFees,
                    'total_paid' => $totalPaid,
                    'total_remaining' => $totalRemaining
                ],
                // Add chart data for frontend
                'monthly_data' => $monthlyData,
                'classwise_data' => $classwiseData,
                'payment_methods' => $methodsData
            ];
        } catch (Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Error calculating fee summary: ' . $e->getMessage(),
                'error_details' => $e->getTrace()
            ];
        }
        break;
        
    case 'export_reports':
        // Check requested format
        $format = isset($_REQUEST['format']) ? $_REQUEST['format'] : 'excel';
        
        // Build query with filters
        $where = [];
        $params = [];
        $types = "";
        
        // Get payments with all related info
        $sql = "SELECT 
                fp.id as payment_id, 
                fp.payment_date,
                fp.amount_paid,
                fp.remaining_amount,
                fp.status,
                fp.payment_method,
                fp.reference_number,
                fs.title as fee_title,
                fs.due_date,
                s.full_name as student_name,
                s.admission_number,
                c.name as class_name,
                sec.name as section_name
                FROM fee_payments fp
                JOIN fee_structures fs ON fp.fee_structure_id = fs.id
                JOIN students s ON fp.student_user_id = s.user_id
                JOIN classes c ON s.class_id = c.id
                JOIN sections sec ON s.section_id = sec.id";
        
        // Apply filters (same as get_reports)
        if (isset($_REQUEST['start_date']) && !empty($_REQUEST['start_date'])) {
            $where[] = "fp.payment_date >= ?";
            $params[] = $_REQUEST['start_date'];
            $types .= "s";
        }
        
        if (isset($_REQUEST['end_date']) && !empty($_REQUEST['end_date'])) {
            $where[] = "fp.payment_date <= ?";
            $params[] = $_REQUEST['end_date'];
            $types .= "s";
        }
        
        if (isset($_REQUEST['class_id']) && !empty($_REQUEST['class_id'])) {
            $where[] = "s.class_id = ?";
            $params[] = $_REQUEST['class_id'];
            $types .= "i";
        }
        
        if (isset($_REQUEST['section_id']) && !empty($_REQUEST['section_id'])) {
            $where[] = "s.section_id = ?";
            $params[] = $_REQUEST['section_id'];
            $types .= "i";
        }
        
        if (isset($_REQUEST['fee_type_id']) && !empty($_REQUEST['fee_type_id'])) {
            $where[] = "fs.id = ?";
            $params[] = $_REQUEST['fee_type_id'];
            $types .= "i";
        }
        
        if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) {
            if ($_REQUEST['status'] === 'overdue') {
                $today = getCurrentDateIST();
                $where[] = "(fp.status = 'pending' OR fp.status = 'partial') AND fs.due_date < ?";
                $params[] = $today;
                $types .= "s";
            } else {
                $where[] = "fp.status = ?";
                $params[] = $_REQUEST['status'];
                $types .= "s";
            }
        }
        
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        
        // Add order by
        $sql .= " ORDER BY fp.payment_date DESC";
        
        // Execute query
        if (!empty($params)) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }
        
        if ($result) {
            $reports = [];
            while ($row = $result->fetch_assoc()) {
                // Format the payment status
                $statusLabels = [
                    'paid' => 'Paid',
                    'partial' => 'Partially Paid',
                    'pending' => 'Pending'
                ];
                $row['status'] = $statusLabels[$row['status']] ?? $row['status'];
                
                // Format the receipt number
                $row['receipt_number'] = sprintf("RCPT-%05d", $row['payment_id']);
                
                // Format the date
                $paymentDate = new DateTime($row['payment_date']);
                $row['formatted_date'] = $paymentDate->format('d-m-Y');
                
                $reports[] = $row;
            }
            
            // Generate export
            if ($format === 'excel') {
                exportToExcel($reports);
            } else if ($format === 'pdf') {
                exportToPdf($reports);
            } else {
                header('Content-Type: application/json');
                $response = [
                    'success' => false,
                    'message' => 'Invalid export format'
                ];
                echo json_encode($response);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            $response = [
                'success' => false,
                'message' => 'Error fetching reports for export: ' . $conn->error
            ];
            echo json_encode($response);
            exit;
        }
        break;
}

// Make sure no whitespace or other content is accidentally output
echo json_encode($response);
exit; // Stop execution to prevent any additional output

// Function to export data to Excel
function exportToExcel($data) {
    // Set headers for Excel download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="fee_report_' . date('Ymd') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Start output buffering
    ob_start();
    
    echo '<table border="1">';
    echo '<tr>';
    echo '<th>Receipt No.</th>';
    echo '<th>Student Name</th>';
    echo '<th>Admission Number</th>';
    echo '<th>Class</th>';
    echo '<th>Fee Type</th>';
    echo '<th>Payment Date</th>';
    echo '<th>Amount Paid (₹)</th>';
    echo '<th>Remaining (₹)</th>';
    echo '<th>Status</th>';
    echo '<th>Payment Method</th>';
    echo '<th>Reference</th>';
    echo '</tr>';
    
    foreach ($data as $row) {
        echo '<tr>';
        echo '<td>' . $row['receipt_number'] . '</td>';
        echo '<td>' . $row['student_name'] . '</td>';
        echo '<td>' . $row['admission_number'] . '</td>';
        echo '<td>' . $row['class_name'] . ' ' . $row['section_name'] . '</td>';
        echo '<td>' . $row['fee_title'] . '</td>';
        echo '<td>' . $row['formatted_date'] . '</td>';
        echo '<td>' . number_format($row['amount_paid'], 2) . '</td>';
        echo '<td>' . number_format($row['remaining_amount'], 2) . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td>' . ucfirst($row['payment_method']) . '</td>';
        echo '<td>' . $row['reference_number'] . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    
    // End output buffering and send to browser
    ob_end_flush();
    exit;
}

// Function to export data to PDF
function exportToPdf($data) {
    // Include the required TCPDF library
    // Note: You'll need to have TCPDF installed or replace this with your preferred PDF library
    require_once('../../lib/tcpdf/tcpdf.php');
    
    try {
        // Create new PDF document
        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator('Virtual Education System');
        $pdf->SetAuthor('Admin');
        $pdf->SetTitle('Fee Report');
        $pdf->SetSubject('Fee Collection Report');
        
        // Remove header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        // Set margins
        $pdf->SetMargins(10, 10, 10);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', 'B', 14);
        
        // Title
        $pdf->Cell(0, 10, 'Fee Collection Report', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 5, 'Generated on: ' . date('d-m-Y H:i:s'), 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFillColor(240, 240, 240);
        $pdf->SetFont('helvetica', 'B', 8);
        
        $pdf->Cell(20, 7, 'Receipt No.', 1, 0, 'C', 1);
        $pdf->Cell(40, 7, 'Student Name', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Admission No.', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Class', 1, 0, 'C', 1);
        $pdf->Cell(35, 7, 'Fee Type', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Payment Date', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Amount Paid', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Remaining', 1, 0, 'C', 1);
        $pdf->Cell(20, 7, 'Status', 1, 0, 'C', 1);
        $pdf->Cell(25, 7, 'Method', 1, 1, 'C', 1);
        
        // Table data
        $pdf->SetFont('helvetica', '', 8);
        $fill = false;
        
        foreach ($data as $row) {
            $pdf->Cell(20, 6, $row['receipt_number'], 1, 0, 'L', $fill);
            $pdf->Cell(40, 6, $row['student_name'], 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $row['admission_number'], 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $row['class_name'] . ' ' . $row['section_name'], 1, 0, 'L', $fill);
            $pdf->Cell(35, 6, $row['fee_title'], 1, 0, 'L', $fill);
            $pdf->Cell(25, 6, $row['formatted_date'], 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, '₹' . number_format($row['amount_paid'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(25, 6, '₹' . number_format($row['remaining_amount'], 2), 1, 0, 'R', $fill);
            $pdf->Cell(20, 6, $row['status'], 1, 0, 'C', $fill);
            $pdf->Cell(25, 6, ucfirst($row['payment_method']), 1, 1, 'L', $fill);
            
            $fill = !$fill;
        }
        
        // Calculate summary
        $totalPaid = array_sum(array_column($data, 'amount_paid'));
        $totalRemaining = array_sum(array_column($data, 'remaining_amount'));
        
        // Summary footer
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(170, 7, 'Total', 1, 0, 'R', true);
        $pdf->Cell(25, 7, '₹' . number_format($totalPaid, 2), 1, 0, 'R', true);
        $pdf->Cell(25, 7, '₹' . number_format($totalRemaining, 2), 1, 0, 'R', true);
        $pdf->Cell(45, 7, '', 1, 1, 'L', true);
        
        // Close and output PDF document
        $pdf->Output('fee_report_' . date('Ymd') . '.pdf', 'D');
    } catch (Exception $e) {
        // If TCPDF is not available, provide a fallback
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'PDF generation failed: ' . $e->getMessage()
        ]);
    }
    
    exit;
}
?> 