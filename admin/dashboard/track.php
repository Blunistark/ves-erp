<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fees Tracking</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/track.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Fees Tracking</h1>
            <span class="header-path">Dashboard > Admin > Fees > Track</span>
        </header>

        <main class="dashboard-content">
            <div class="action-bar">
                <div class="search-bar">
                    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input type="text" id="studentSearch" class="search-input" placeholder="Search by student name, ID, class...">
                </div>
                <div class="action-buttons">
                    <button class="btn btn-outline" id="filterToggleBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                        </svg>
                        Filter
                    </button>
                    <button class="btn btn-outline">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Export
                    </button>
                    <button class="btn btn-primary" id="recordPaymentBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Record Payment
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div class="filter-panel" id="filterPanel" style="display: none;">
                <h3 class="filter-title">Filter Students</h3>
                <form class="filter-form" method="get">
                    <div class="filter-group">
                        <label class="filter-label">Class/Grade</label>
                        <select class="filter-select" id="classFilter" name="classFilter">
                            <option value="">All Classes</option>
                            <option value="7a" <?php if($classFilter=='7a') echo 'selected'; ?>>Grade 7A</option>
                            <option value="7b" <?php if($classFilter=='7b') echo 'selected'; ?>>Grade 7B</option>
                            <option value="8a" <?php if($classFilter=='8a') echo 'selected'; ?>>Grade 8A</option>
                            <option value="8b" <?php if($classFilter=='8b') echo 'selected'; ?>>Grade 8B</option>
                            <option value="9a" <?php if($classFilter=='9a') echo 'selected'; ?>>Grade 9A</option>
                            <option value="9b" <?php if($classFilter=='9b') echo 'selected'; ?>>Grade 9B</option>
                            <option value="10a" <?php if($classFilter=='10a') echo 'selected'; ?>>Grade 10A</option>
                            <option value="10b" <?php if($classFilter=='10b') echo 'selected'; ?>>Grade 10B</option>
                            <option value="11a" <?php if($classFilter=='11a') echo 'selected'; ?>>Grade 11A</option>
                            <option value="11b" <?php if($classFilter=='11b') echo 'selected'; ?>>Grade 11B</option>
                            <option value="12a" <?php if($classFilter=='12a') echo 'selected'; ?>>Grade 12A</option>
                            <option value="12b" <?php if($classFilter=='12b') echo 'selected'; ?>>Grade 12B</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="filter-btn filter-btn-reset" type="reset">Reset</button>
                        <button class="filter-btn filter-btn-apply" type="submit">Apply Filters</button>
                    </div>
                </form>
            </div>

            <!-- Tab System -->
            <div class="fees-tabs">
                <div class="fees-tab active" data-tab="overview">Overview</div>
                <div class="fees-tab" data-tab="all-students">All Students</div>
                <div class="fees-tab" data-tab="due-fees">Due Fees</div>
                <div class="fees-tab" data-tab="fee-components">Fee Components</div>
                <div class="fees-tab" data-tab="statistics">Statistics</div>
            </div>

            <!-- Payment Form -->
            <div class="fees-form-container" id="paymentForm" style="display: none;">
                <h2 class="form-title">Record Fee Payment</h2>
                <form id="recordPaymentForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="studentSelect">Select Student</label>
                            <select class="form-select" id="studentSelect" name="studentSelect" required>
                                <option value="">Select Student</option>
                                <option value="1">Alex Brown (ST001) - Grade 10A</option>
                                <option value="2">Emma Smith (ST002) - Grade 10A</option>
                                <option value="3">Michael Johnson (ST003) - Grade 10A</option>
                                <option value="4">Sophia Davis (ST004) - Grade 10A</option>
                                <option value="5">William Miller (ST005) - Grade 10A</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="feeTypeSelect">Fee Type</label>
                            <select class="form-select" id="feeTypeSelect" name="feeTypeSelect" required>
                                <option value="">Select Fee Type</option>
                                <option value="tuition">Tuition Fee</option>
                                <option value="admission">Admission Fee</option>
                                <option value="exam">Examination Fee</option>
                                <option value="library">Library Fee</option>
                                <option value="transportation">Transportation Fee</option>
                                <option value="laboratory">Laboratory Fee</option>
                                <option value="sports">Sports Fee</option>
                                <option value="other">Other Fees</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="paymentAmount">Payment Amount</label>
                            <input type="number" class="form-input" id="paymentAmount" name="paymentAmount" min="0" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="paymentDate">Payment Date</label>
                            <input type="date" class="form-input" id="paymentDate" name="paymentDate" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="paymentMethodSelect">Payment Method</label>
                            <select class="form-select" id="paymentMethodSelect" name="paymentMethodSelect" required>
                                <option value="">Select Payment Method</option>
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="debit_card">Debit Card</option>
                                <option value="online">Online Payment</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="transactionId">Transaction ID/Reference</label>
                            <input type="text" class="form-input" id="transactionId" name="transactionId" placeholder="Enter transaction reference number">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="receivedBy">Received By</label>
                            <input type="text" class="form-input" id="receivedBy" name="receivedBy" placeholder="Enter name of receiver">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label class="form-label" for="paymentNotes">Notes</label>
                            <textarea class="form-textarea" id="paymentNotes" name="paymentNotes" placeholder="Enter any additional information about this payment..."></textarea>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelPaymentBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Record Payment</button>
                    </div>
                </form>
            </div>

            <!-- Fee Components Form -->
            <div class="fees-form-container" id="feeComponentForm" style="display: none;">
                <h2 class="form-title">Manage Fee Components</h2>
                <form id="manageFeeComponentForm">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="componentStudentSelect">Select Student</label>
                            <select class="form-select" id="componentStudentSelect" name="componentStudentSelect" required>
                                <option value="">Select Student</option>
                                <option value="1">Alex Brown (ST001) - Grade 10A</option>
                                <option value="2">Emma Smith (ST002) - Grade 10A</option>
                                <option value="3">Michael Johnson (ST003) - Grade 10A</option>
                                <option value="4">Sophia Davis (ST004) - Grade 10A</option>
                                <option value="5">William Miller (ST005) - Grade 10A</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="academicYearSelect">Academic Year</label>
                            <select class="form-select" id="academicYearSelect" name="academicYearSelect" required>
                                <option value="2024-25">2024-2025</option>
                                <option value="2023-24">2023-2024</option>
                                <option value="2022-23">2022-2023</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="fee-components-section">
                        <div class="fee-components-header">
                            <h3 class="fee-components-title">Current Fee Components</h3>
                        </div>
                        
                        <div class="fee-components-list">
                            <div class="fee-component-item">
                                <span class="component-name">Tuition Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$8,500.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fee-component-item">
                                <span class="component-name">Library Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$500.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fee-component-item">
                                <span class="component-name">Laboratory Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$750.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="fee-component-item">
                                <span class="component-name">Examination Fee</span>
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <span class="component-amount">$300.00</span>
                                    <div class="component-actions">
                                        <button type="button" class="action-btn" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </button>
                                        <button type="button" class="action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h4 style="margin-top: 1.5rem; font-size: 1rem; font-weight: 600;">Add New Fee Component</h4>
                        <div class="add-component-form">
                            <div class="form-group">
                                <label class="form-label" for="componentName">Component Name</label>
                                <input type="text" class="form-input" id="componentName" placeholder="Enter fee component name">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="componentAmount">Amount</label>
                                <input type="number" class="form-input" id="componentAmount" placeholder="Enter amount" min="0" step="0.01">
                            </div>
                            <button type="button" class="btn btn-primary" style="height: 42px;">Add Component</button>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" id="cancelComponentBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <!-- Overview Tab Content -->
            <div class="tab-content active" id="overview-tab">
                <div class="performance-metrics">
                    <?php
                    require_once 'con.php';


                    $year = '2024-25';
                    // Calculate metrics
                    $total_needed = 0;
                    $total_collected = 0;
                    $total_pending = 0;
                    $total_overdue = 0;
                    $total_exempted = 0;

                    $res = $conn->query("SELECT total_fee, status, due_date, id FROM student_fees WHERE academic_year = '$year'");
                    while ($row = $res->fetch_assoc()) {
                        $total_needed += $row['total_fee'];
                        if ($row['status'] === 'paid') {
                            $total_collected += $row['total_fee'];
                        } elseif ($row['status'] === 'exempted') {
                            $total_exempted += $row['total_fee'];
                        } elseif ($row['status'] === 'overdue') {
                            $total_overdue += $row['total_fee'];
                        } else {
                            $total_pending += $row['total_fee'];
                        }
                    }
                    ?>
                    <div class="metric-card metric-total-needed">
                        <h3 class="metric-title">Total To Be Collected</h3>
                        <div class="metric-value">₹<?php echo number_format($total_needed); ?></div>
                        <div class="metric-indicator">
                            <span class="indicator-neutral">100% of target</span>
                        </div>
                    </div>
                    <div class="metric-card metric-collected">
                        <h3 class="metric-title">Total Collected</h3>
                        <div class="metric-value">₹<?php echo number_format($total_collected); ?></div>
                        <div class="metric-indicator">
                            <span class="indicator-positive">
                                <?php echo $total_needed > 0 ? round($total_collected / $total_needed * 100, 1) : 0; ?>% collected
                            </span>
                        </div>
                    </div>
                    <div class="metric-card metric-pending">
                        <h3 class="metric-title">Pending Fees</h3>
                        <div class="metric-value">₹<?php echo number_format($total_pending); ?></div>
                        <div class="metric-indicator">
                            <span class="indicator-warning">
                                <?php echo $total_needed > 0 ? round($total_pending / $total_needed * 100, 1) : 0; ?>% pending
                            </span>
                        </div>
                    </div>
                    <div class="metric-card metric-overdue">
                        <h3 class="metric-title">Overdue Fees</h3>
                        <div class="metric-value">₹<?php echo number_format($total_overdue); ?></div>
                        <div class="metric-indicator">
                            <span class="indicator-negative">
                                <?php echo $total_needed > 0 ? round($total_overdue / $total_needed * 100, 1) : 0; ?>% overdue
                            </span>
                        </div>
                    </div>
                </div>

                <div class="student-fee-cards">
<?php
require_once 'con.php';

function renderPagination($page, $totalPages) {
    $range = 2;
    $output = '';
    // Prev button
    $output .= '<button class="page-btn" ' . ($page <= 1 ? 'disabled' : '') . ' onclick="window.location.href=\'?page=' . ($page - 1) . '\'">';
    $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>';
    $output .= '</button>';
    // Always show first page
    if ($page > $range + 2) {
        $output .= '<button class="page-btn" onclick="window.location.href=\'?page=1\'">1</button>';
        $output .= '<span class="page-ellipsis">...</span>';
    } else {
        for ($i = 1; $i < max(2, $page - $range); $i++) {
            $output .= '<button class="page-btn" onclick="window.location.href=\'?page=' . $i . '\'">' . $i . '</button>';
        }
    }
    // Pages around current
    for ($i = max(1, $page - $range); $i <= min($totalPages, $page + $range); $i++) {
        if ($i == $page) {
            $output .= '<button class="page-btn active" disabled>' . $i . '</button>';
        } else {
            $output .= '<button class="page-btn" onclick="window.location.href=\'?page=' . $i . '\'">' . $i . '</button>';
        }
    }
    // Always show last page
    if ($page < $totalPages - $range - 1) {
        $output .= '<span class="page-ellipsis">...</span>';
        $output .= '<button class="page-btn" onclick="window.location.href=\'?page=' . $totalPages . '\'">' . $totalPages . '</button>';
    } else {
        for ($i = max($page + $range + 1, $totalPages - 1); $i <= $totalPages; $i++) {
            $output .= '<button class="page-btn" onclick="window.location.href=\'?page=' . $i . '\'">' . $i . '</button>';
        }
    }
    // Next button
    $output .= '<button class="page-btn" ' . ($page >= $totalPages ? 'disabled' : '') . ' onclick="window.location.href=\'?page=' . ($page + 1) . '\'">';
    $output .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>';
    $output .= '</button>';
    return $output;
}

// Read filter values from GET
$classFilter = isset($_GET['classFilter']) ? $_GET['classFilter'] : '';
$statusFilter = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : '';
$feeTypeFilter = isset($_GET['feeTypeFilter']) ? $_GET['feeTypeFilter'] : '';
$yearFilter = isset($_GET['yearFilter']) ? $_GET['yearFilter'] : '2024-25';
$minAmountFilter = isset($_GET['minAmountFilter']) && $_GET['minAmountFilter'] !== '' ? $_GET['minAmountFilter'] : '';
$maxAmountFilter = isset($_GET['maxAmountFilter']) && $_GET['maxAmountFilter'] !== '' ? $_GET['maxAmountFilter'] : '';

// Build WHERE clause for filters
$where = ["1=1"];
if ($classFilter) {
    $where[] = "s.class_id = '" . $conn->real_escape_string($classFilter) . "'";
}
if ($statusFilter) {
    $where[] = "sf.status = '" . $conn->real_escape_string($statusFilter) . "'";
}
if ($yearFilter) {
    $where[] = "sf.academic_year = '" . $conn->real_escape_string($yearFilter) . "'";
}
if ($minAmountFilter !== '') {
    $where[] = "sf.total_fee >= " . floatval($minAmountFilter);
}
if ($maxAmountFilter !== '') {
    $where[] = "sf.total_fee <= " . floatval($maxAmountFilter);
}
$whereSql = implode(' AND ', $where);

$perPage = 20;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Count total students with filters
$totalRes = $conn->query("
    SELECT COUNT(DISTINCT s.id) as total
    FROM students s
    LEFT JOIN student_fees sf ON s.id = sf.student_id
    WHERE $whereSql
");
$totalRow = $totalRes->fetch_assoc();
$totalStudents = $totalRow['total'];
$totalPages = ceil($totalStudents / $perPage);

// Fetch students for this page with filters
$students = [];
$res = $conn->query("
    SELECT s.id, s.first_name, s.last_name, s.class_id, s.section_id, s.photo, s.admission_number, c.name AS class_name, sec.name AS section_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN sections sec ON s.section_id = sec.id
    LEFT JOIN student_fees sf ON s.id = sf.student_id
    WHERE $whereSql
    ORDER BY s.class_id, s.section_id, s.first_name, s.last_name
    LIMIT $perPage OFFSET $offset
");
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
?>
<?php foreach ($students as $student):
    $avatar = $student['photo'] ? htmlspecialchars($student['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($student['first_name'].' '.$student['last_name']) . '&background=ececec&color=555&size=128';
    $grade = 'Grade ' . htmlspecialchars($student['class_name']) . strtoupper($student['section_name'] ?? '');
?>
    <div class="student-fee-card" data-student-id="<?php echo $student['id']; ?>">
                        <div class="student-fee-header">
                            <div class="student-fee-profile">
                <img class="student-fee-avatar" src="<?php echo $avatar; ?>" alt="Avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                <div class="student-fee-info">
                    <div class="student-fee-name"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></div>
                    <?php if (!empty($student['sats_number'])): ?>
                        <div class="student-sats-number" style="font-size: 0.95rem; color: #888;">SATS: <?php echo htmlspecialchars($student['sats_number']); ?></div>
                    <?php endif; ?>
                    <div class="student-fee-details">Grade <?php echo htmlspecialchars($student['class_name']) . strtoupper($student['section_name'] ?? ''); ?></div>
                                </div>
                            </div>
            <span class="status-badge">&nbsp;</span>
                        </div>
                        <div class="fee-summary">
                            <div class="fee-summary-item">
                                <span class="fee-summary-label">Total Fee</span>
                <span class="fee-summary-value">&nbsp;</span>
                            </div>
                            <div class="fee-summary-item">
                                <span class="fee-summary-label">Paid Amount</span>
                <span class="fee-summary-value">&nbsp;</span>
                            </div>
                        </div>
                        <div class="fee-progress">
                            <div class="fee-progress-label">
                                <span>Payment Progress</span>
                <span class="progress-percent">&nbsp;</span>
                            </div>
                            <div class="fee-progress-bar">
                <div class="fee-progress-fill" style="width:0%"></div>
                            </div>
                        </div>
                        <div class="fee-transactions">
                            <div class="fee-transactions-header">
                                <h4 class="fee-transactions-title">Recent Transactions</h4>
                            </div>
            <!-- Transaction items will be filled by JS -->
                                </div>
                        <div class="student-fee-footer">
            <span class="due-date" style="color: #6b7280;">&nbsp;</span>
                            <div>
                                <button class="btn btn-outline" style="padding: 0.5rem 0.75rem; font-size: 0.875rem;">View Details</button>
                            </div>
                        </div>
                    </div>
<?php endforeach; ?>
                                </div>
                <div class="pagination">
                    <div class="pagination-info">
                        Showing <?php echo ($offset + 1) . '-' . min($offset + $perPage, $totalStudents); ?> of <?php echo $totalStudents; ?> students
                            </div>
                    <div class="pagination-buttons">
                        <?php echo renderPagination($page, $totalPages); ?>
                        </div>
                </div>
            </div>

            <!-- All Students Tab Content -->
            <div class="tab-content" id="all-students-tab">
                <!-- Bulk Action Panel -->
                <div class="bulk-action-panel">
                    <div class="bulk-action-left">
                        <input type="checkbox" id="selectAllStudents">
                        <span class="bulk-action-count">0 students selected</span>
                    </div>
                    <div class="bulk-action-right">
                        <button class="btn btn-outline" disabled>
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            Send Reminder
                        </button>
                        <button class="btn btn-outline" disabled>
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export Selected
                        </button>
                        <button class="btn btn-primary" disabled>
                            <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Apply Bulk Action
                        </button>
                    </div>
                </div>
                
                <div class="fees-table-container">
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAllInTable"></th>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Total Fee</th>
                                <th>Paid Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="checkbox" class="student-checkbox"></td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">AB</div>
                                        <div class="student-details">
                                            <span class="student-name">Alex Brown</span>
                                            <span class="student-id">ID: ST001</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$6,500</td>
                                <td>$3,550</td>
                                <td><span class="status-badge status-partial">Partially Paid</span></td>
                                <td>Apr 15, 2025</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn manage-components-btn" title="Manage Components">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Send Reminder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="student-checkbox"></td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">ES</div>
                                        <div class="student-details">
                                            <span class="student-name">Emma Smith</span>
                                            <span class="student-id">ID: ST002</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$10,050</td>
                                <td>$0</td>
                                <td><span class="status-badge status-paid">Fully Paid</span></td>
                                <td>Apr 15, 2025</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn manage-components-btn" title="Manage Components">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Print Receipt" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="student-checkbox"></td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">MJ</div>
                                        <div class="student-details">
                                            <span class="student-name">Michael Johnson</span>
                                            <span class="student-id">ID: ST003</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$3,200</td>
                                <td>$6,850</td>
                                <td><span class="status-badge status-overdue">Overdue</span></td>
                                <td>Jan 30, 2025</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn manage-components-btn" title="Manage Components">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Send Reminder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="student-checkbox"></td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">SD</div>
                                        <div class="student-details">
                                            <span class="student-name">Sophia Davis</span>
                                            <span class="student-id">ID: ST004</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$5,000</td>
                                <td>$5,050</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>Apr 15, 2025</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn manage-components-btn" title="Manage Components">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                         
                                         2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Send Reminder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="student-checkbox"></td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">WM</div>
                                        <div class="student-details">
                                            <span class="student-name">William Miller</span>
                                            <span class="student-id">ID: ST005</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$0</td>
                                <td>$10,050</td>
                                <td><span class="status-badge status-exempted">Exempted</span></td>
                                <td>N/A</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment" disabled>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn manage-components-btn" title="Manage Components">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="View Exemption" style="color: #8b5cf6;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <div class="pagination-info">
                        Showing <?php echo ($offset + 1) . '-' . min($offset + $perPage, $totalStudents); ?> of <?php echo $totalStudents; ?> students
                    </div>
                    <div class="pagination-buttons">
                        <?php echo renderPagination($page, $totalPages); ?>
                    </div>
                </div>
            </div>

            <!-- Due Fees Tab Content -->
            <div class="tab-content" id="due-fees-tab">
                <div class="fees-table-container">
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Class</th>
                                <th>Total Fee</th>
                                <th>Paid Amount</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Due Date</th>
                                <th>Days Left/Overdue</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">MJ</div>
                                        <div class="student-details">
                                            <span class="student-name">Michael Johnson</span>
                                            <span class="student-id">ID: ST003</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$3,200</td>
                                <td>$6,850</td>
                                <td><span class="status-badge status-overdue">Overdue</span></td>
                                <td>Jan 30, 2025</td>
                                <td style="color: #ef4444;">45 days overdue</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Send Reminder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Grant Extension">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">AB</div>
                                        <div class="student-details">
                                            <span class="student-name">Alex Brown</span>
                                            <span class="student-id">ID: ST001</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$6,500</td>
                                <td>$3,550</td>
                                <td><span class="status-badge status-partial">Partially Paid</span></td>
                                <td>Apr 15, 2025</td>
                                <td>30 days left</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Send Reminder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Schedule Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">SD</div>
                                        <div class="student-details">
                                            <span class="student-name">Sophia Davis</span>
                                            <span class="student-id">ID: ST004</span>
                                        </div>
                                    </div>
                                </td>
                                <td>Grade 10A</td>
                                <td>$10,050</td>
                                <td>$5,000</td>
                                <td>$5,050</td>
                                <td><span class="status-badge status-pending">Pending</span></td>
                                <td>Apr 15, 2025</td>
                                <td>30 days left</td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="View Details">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn record-payment-btn" title="Record Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Send Reminder">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Schedule Payment">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Fee Components Tab Content -->
            <div class="tab-content" id="fee-components-tab">
                <div class="fees-table-container">
                    <table class="fees-table">
                        <thead>
                            <tr>
                                <th>Component Name</th>
                                <th>Amount</th>
                                <th>Class/Grade</th>
                                <th>Description</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Tuition Fee</td>
                                <td>$8,500</td>
                                <td>All Grades</td>
                                <td>Annual tuition fee for the academic year 2024-2025</td>
                                <td>Varies by student</td>
                                <td><span class="status-badge status-partial">Active</span></td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="Edit Component">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Assign to Class">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="View Students">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Library Fee</td>
                                <td>$500</td>
                                <td>All Grades</td>
                                <td>Annual library facility and resources fee</td>
                                <td>Varies by student</td>
                                <td><span class="status-badge status-partial">Active</span></td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="Edit Component">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Assign to Class">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="View Students">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Laboratory Fee</td>
                                <td>$750</td>
                                <td>Grades 9-12</td>
                                <td>Fee for science laboratory facilities and materials</td>
                                <td>Varies by student</td>
                                <td><span class="status-badge status-partial">Active</span></td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="Edit Component">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Assign to Class">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="View Students">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Examination Fee</td>
                                <td>$300</td>
                                <td>All Grades</td>
                                <td>Fee covering all formal examinations for the academic year</td>
                                <td>Varies by student</td>
                                <td><span class="status-badge status-partial">Active</span></td>
                                <td class="fee-actions">
                                    <button class="action-btn" title="Edit Component">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Assign to Class">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="View Students">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </button>
                                    <button class="action-btn" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                    <button class="btn btn-primary" id="addFeeComponentBtn">
                        <svg class="btn-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Add New Fee Component
                    </button>
                </div>
            </div>

            <!-- Statistics Tab Content -->
            <div class="tab-content" id="statistics-tab">
                <div class="fee-stats-container">
                    <div class="fee-stats-header">
                        <h3 class="fee-stats-title">Fees Collection Statistics</h3>
                        <span class="fee-stats-period">Academic Year 2024-2025</span>
                    </div>
                    
                    <div class="fee-stats-grid">
                        <div class="fee-stat-card">
                            <div class="fee-stat-value">$258,750</div>
                            <div class="fee-stat-label">Total Collected</div>
                        </div>
                        <div class="fee-stat-card">
                            <div class="fee-stat-value">$122,770</div>
                            <div class="fee-stat-label">Pending & Overdue</div>
                        </div>
                        <div class="fee-stat-card">
                            <div class="fee-stat-value">67.8%</div>
                            <div class="fee-stat-label">Collection Rate</div>
                        </div>
                        <div class="fee-stat-card">
                            <div class="fee-stat-value">14.2%</div>
                            <div class="fee-stat-label">Overdue Rate</div>
                        </div>
                    </div>
                    
                    <div class="fee-chart-section">
                        <h4 class="fee-chart-title">Monthly Collection Trend</h4>
                        <div class="fee-chart-placeholder">Monthly Collection Chart</div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
                        <div class="fee-chart-section">
                            <h4 class="fee-chart-title">Fee Components Breakdown</h4>
                            <div class="fee-chart-placeholder">Fee Components Chart</div>
                        </div>
                        <div class="fee-chart-section">
                            <h4 class="fee-chart-title">Collection Status</h4>
                            <div class="fee-chart-placeholder">Collection Status Chart</div>
                        </div>
                    </div>
                </div>
                
                <div class="fee-stats-container">
                    <div class="fee-stats-header">
                        <h3 class="fee-stats-title">Payment Method Analysis</h3>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="fee-chart-section">
                            <h4 class="fee-chart-title">Payment Methods Used</h4>
                            <div class="fee-chart-placeholder">Payment Methods Chart</div>
                        </div>
                        <div class="fee-chart-section">
                            <h4 class="fee-chart-title">Payment Frequency</h4>
                            <div class="fee-chart-placeholder">Payment Frequency Chart</div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');

            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Tab Switching
            const tabs = document.querySelectorAll('.fees-tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and contents
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding content
                    const tabId = this.getAttribute('data-tab');
                    document.getElementById(`${tabId}-tab`).classList.add('active');
                });
            });
            
            // Filter Panel Toggle
            const filterToggleBtn = document.getElementById('filterToggleBtn');
            const filterPanel = document.getElementById('filterPanel');
            
            filterToggleBtn.addEventListener('click', function() {
                filterPanel.style.display = filterPanel.style.display === 'none' ? 'block' : 'none';
            });
            
            // Payment Form Toggle
            const recordPaymentBtn = document.getElementById('recordPaymentBtn');
            const recordPaymentBtns = document.querySelectorAll('.record-payment-btn');
            const paymentForm = document.getElementById('paymentForm');
            const cancelPaymentBtn = document.getElementById('cancelPaymentBtn');
            
            recordPaymentBtn.addEventListener('click', function() {
                // Reset form
                document.getElementById('recordPaymentForm').reset();
                
                // Set current date as default
                const today = new Date().toISOString().split('T')[0];
                document.getElementById('paymentDate').value = today;
                
                // Show form
                paymentForm.style.display = 'block';
                
                // Scroll to form
                paymentForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            recordPaymentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.hasAttribute('disabled')) return;
                    
                    // Get student information
                    const studentRow = this.closest('tr');
                    const studentName = studentRow.querySelector('.student-name').textContent;
                    const studentId = studentRow.querySelector('.student-id').textContent.split(':')[1].trim();
                    
                    // Reset form
                    document.getElementById('recordPaymentForm').reset();
                    
                    // Set current date as default
                    const today = new Date().toISOString().split('T')[0];
                    document.getElementById('paymentDate').value = today;
                    
                    // Pre-select the student in the dropdown
                    const studentSelect = document.getElementById('studentSelect');
                    // Find the option that contains both the name and ID
                    Array.from(studentSelect.options).forEach(option => {
                        if (option.text.includes(studentName) && option.text.includes(studentId)) {
                            option.selected = true;
                        }
                    });
                    
                    // Show form
                    paymentForm.style.display = 'block';
                    
                    // Scroll to form
                    paymentForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            cancelPaymentBtn.addEventListener('click', function() {
                paymentForm.style.display = 'none';
            });
            
            // Fee Component Form Toggle
            const addFeeComponentBtn = document.getElementById('addFeeComponentBtn');
            const manageComponentsBtns = document.querySelectorAll('.manage-components-btn');
            const feeComponentForm = document.getElementById('feeComponentForm');
            const cancelComponentBtn = document.getElementById('cancelComponentBtn');
            
            addFeeComponentBtn.addEventListener('click', function() {
                // Reset form
                document.getElementById('manageFeeComponentForm').reset();
                
                // Update form title
                document.querySelector('#feeComponentForm .form-title').textContent = 'Add New Fee Component';
                
                // Show form
                feeComponentForm.style.display = 'block';
                
                // Scroll to form
                feeComponentForm.scrollIntoView({ behavior: 'smooth' });
            });
            
            manageComponentsBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    if (this.hasAttribute('disabled')) return;
                    
                    // Get student information
                    const studentRow = this.closest('tr');
                    const studentName = studentRow.querySelector('.student-name').textContent;
                    const studentId = studentRow.querySelector('.student-id').textContent.split(':')[1].trim();
                    
                    // Reset form
                    document.getElementById('manageFeeComponentForm').reset();
                    
                    // Update form title
                    document.querySelector('#feeComponentForm .form-title').textContent = `Manage Fee Components: ${studentName}`;
                    
                    // Pre-select the student in the dropdown
                    const studentSelect = document.getElementById('componentStudentSelect');
                    // Find the option that contains both the name and ID
                    Array.from(studentSelect.options).forEach(option => {
                        if (option.text.includes(studentName) && option.text.includes(studentId)) {
                            option.selected = true;
                        }
                    });
                    
                    // Show form
                    feeComponentForm.style.display = 'block';
                    
                    // Scroll to form
                    feeComponentForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            cancelComponentBtn.addEventListener('click', function() {
                feeComponentForm.style.display = 'none';
            });
            
            // Form Submissions
            const recordPaymentForm = document.getElementById('recordPaymentForm');
            const manageFeeComponentForm = document.getElementById('manageFeeComponentForm');
            
            recordPaymentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const studentSelect = document.getElementById('studentSelect').value;
                const feeTypeSelect = document.getElementById('feeTypeSelect').value;
                const paymentAmount = document.getElementById('paymentAmount').value;
                
                // Validate form fields
                if (!studentSelect || !feeTypeSelect || !paymentAmount) {
                    alert('Please fill in all required fields');
                    return;
                }
                
                // Here you would typically submit the form via AJAX or redirect
                alert('Payment recorded successfully!');
                
                // Hide form
                paymentForm.style.display = 'none';
                
                // Reload the page to show the updated payment
                // window.location.reload();
            });
            
            manageFeeComponentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Get form values
                const studentSelect = document.getElementById('componentStudentSelect').value;
                
                // Validate form fields
                if (!studentSelect) {
                    alert('Please select a student');
                    return;
                }
                
                // Here you would typically submit the form via AJAX or redirect
                alert('Fee components updated successfully!');
                
                // Hide form
                feeComponentForm.style.display = 'none';
                
                // Reload the page to show the updated components
                // window.location.reload();
            });
            
            // Bulk Action Checkboxes
            const selectAllInTable = document.getElementById('selectAllInTable');
            const selectAllStudents = document.getElementById('selectAllStudents');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');
            const bulkActionCount = document.querySelector('.bulk-action-count');
            const bulkActionButtons = document.querySelectorAll('.bulk-action-right .btn');
            
            if (selectAllInTable) {
                selectAllInTable.addEventListener('change', function() {
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    
                    updateBulkActionUI();
                });
            }
            
            if (selectAllStudents) {
                selectAllStudents.addEventListener('change', function() {
                    studentCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    
                    if (selectAllInTable) {
                        selectAllInTable.checked = this.checked;
                    }
                    
                    updateBulkActionUI();
                });
            }
            
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateBulkActionUI();
                    
                    // Update "Select All" checkbox state
                    if (selectAllInTable) {
                        selectAllInTable.checked = Array.from(studentCheckboxes).every(c => c.checked);
                    }
                    
                    if (selectAllStudents) {
                        selectAllStudents.checked = Array.from(studentCheckboxes).every(c => c.checked);
                    }
                });
            });
            
            function updateBulkActionUI() {
                const selectedCount = Array.from(studentCheckboxes).filter(c => c.checked).length;
                
                if (bulkActionCount) {
                    bulkActionCount.textContent = `${selectedCount} students selected`;
                }
                
                bulkActionButtons.forEach(btn => {
                    btn.disabled = selectedCount === 0;
                });
            }
            
            // Search functionality
            const searchInput = document.getElementById('studentSearch');
            const tableRows = document.querySelectorAll('.fees-table tbody tr');
            const studentCards = document.querySelectorAll('.student-fee-card');
            
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    
                    // Search in table rows
                    if (tableRows.length > 0) {
                        tableRows.forEach(row => {
                            const studentName = row.querySelector('.student-name')?.textContent.toLowerCase() || '';
                            const studentId = row.querySelector('.student-id')?.textContent.toLowerCase() || '';
                            const classGrade = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                            
                            const matchFound = 
                                studentName.includes(searchTerm) || 
                                studentId.includes(searchTerm) || 
                                classGrade.includes(searchTerm);
                            
                            row.style.display = matchFound ? '' : 'none';
                        });
                    }
                    
                    // Search in student cards
                    if (studentCards.length > 0) {
                        studentCards.forEach(card => {
                            const studentName = card.querySelector('.student-fee-name')?.textContent.toLowerCase() || '';
                            const studentId = card.querySelector('.student-fee-id')?.textContent.toLowerCase() || '';
                            const studentDetails = card.querySelector('.student-fee-details')?.textContent.toLowerCase() || '';
                            
                            const matchFound = 
                                studentName.includes(searchTerm) || 
                                studentId.includes(searchTerm) || 
                                studentDetails.includes(searchTerm);
                            
                            card.style.display = matchFound ? '' : 'none';
                        });
                    }
                });
            }
            
            // Filter functionality
            const filterForm = document.querySelector('.filter-form');
            const filterApplyBtn = document.querySelector('.filter-btn-apply');
            const filterResetBtn = document.querySelector('.filter-btn-reset');
            
            if (filterApplyBtn) {
                filterApplyBtn.addEventListener('click', function() {
                    // Get filter values
                    const classFilter = document.getElementById('classFilter').value;
                    const statusFilter = document.getElementById('statusFilter').value;
                    const feeTypeFilter = document.getElementById('feeTypeFilter').value;
                    const yearFilter = document.getElementById('yearFilter').value;
                    const startDateFilter = document.getElementById('startDateFilter').value;
                    const endDateFilter = document.getElementById('endDateFilter').value;
                    const minAmountFilter = document.getElementById('minAmountFilter').value;
                    const maxAmountFilter = document.getElementById('maxAmountFilter').value;
                    
                    // In a real implementation, this would filter the data based on the filters
                    
                    // For this demo, just show an alert with the selected filters
                    let filterMessage = 'Applied filters:\n';
                    filterMessage += classFilter ? `Class: ${classFilter}\n` : 'Class: All\n';
                    filterMessage += statusFilter ? `Status: ${statusFilter}\n` : 'Status: All\n';
                    filterMessage += feeTypeFilter ? `Fee Type: ${feeTypeFilter}\n` : 'Fee Type: All\n';
                    filterMessage += yearFilter ? `Academic Year: ${yearFilter}\n` : 'Academic Year: All\n';
                    filterMessage += startDateFilter ? `Start Date: ${startDateFilter}\n` : 'Start Date: -\n';
                    filterMessage += endDateFilter ? `End Date: ${endDateFilter}\n` : 'End Date: -\n';
                    filterMessage += minAmountFilter ? `Min Amount: $${minAmountFilter}\n` : 'Min Amount: -\n';
                    filterMessage += maxAmountFilter ? `Max Amount: $${maxAmountFilter}\n` : 'Max Amount: -\n';
                    
                    alert(filterMessage);
                    
                    // Hide the filter panel
                    filterPanel.style.display = 'none';
                });
            }
            
            if (filterResetBtn) {
                filterResetBtn.addEventListener('click', function() {
                    if (filterForm) {
                        filterForm.reset();
                    }
                });
            }
            
            // Pagination
            const pageButtons = document.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
            
            pageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const parentPagination = this.closest('.pagination');
                    const pageButtons = parentPagination.querySelectorAll('.page-btn:not(:first-child):not(:last-child)');
                    
                    pageButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // In a real implementation, this would load the next page of data
                    if (this.textContent !== '1') {
                        alert(`Loading page ${this.textContent} of students...`);
                    }
                });
            });
            
            // Fee Component Management
            const addComponentBtn = document.querySelector('.add-component-form .btn-primary');
            
            if (addComponentBtn) {
                addComponentBtn.addEventListener('click', function() {
                    const componentName = document.getElementById('componentName').value;
                    const componentAmount = document.getElementById('componentAmount').value;
                    
                    if (!componentName || !componentAmount) {
                        alert('Please enter both component name and amount');
                        return;
                    }
                    
                    // In a real implementation, this would add the component to the list
                    alert(`Added new fee component: ${componentName} - $${componentAmount}`);
                    
                    // Clear the input fields
                    document.getElementById('componentName').value = '';
                    document.getElementById('componentAmount').value = '';
                });
            }
            
            // Edit Fee Component
            const editComponentBtns = document.querySelectorAll('.fee-component-item .action-btn[title="Edit"]');
            
            editComponentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const componentItem = this.closest('.fee-component-item');
                    const componentName = componentItem.querySelector('.component-name').textContent;
                    const componentAmount = componentItem.querySelector('.component-amount').textContent.replace('$', '');
                    
                    // In a real implementation, this would populate the edit form
                    const newAmount = prompt(`Edit amount for ${componentName}:`, componentAmount);
                    
                    if (newAmount !== null) {
                        // Update the displayed amount
                        componentItem.querySelector('.component-amount').textContent = `$${newAmount}`;
                        
                        alert(`Updated ${componentName} amount to $${newAmount}`);
                    }
                });
            });
            
            // Delete Fee Component
            const deleteComponentBtns = document.querySelectorAll('.fee-component-item .action-btn[title="Delete"]');
            
            deleteComponentBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const componentItem = this.closest('.fee-component-item');
                    const componentName = componentItem.querySelector('.component-name').textContent;
                    
                    if (confirm(`Are you sure you want to delete the ${componentName} component?`)) {
                        // Remove the component item
                        componentItem.remove();
                        
                        alert(`Deleted ${componentName} component`);
                    }
                });
            });
            
            // View Details buttons
            const viewDetailsBtns = document.querySelectorAll('.action-btn[title="View Details"]');
            
            viewDetailsBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    let studentName = '';
                    
                    if (this.closest('tr')) {
                        studentName = this.closest('tr').querySelector('.student-name').textContent;
                    } else if (this.closest('.student-fee-card')) {
                        studentName = this.closest('.student-fee-card').querySelector('.student-fee-name').textContent;
                    }
                    
                    alert(`Viewing detailed fee information for ${studentName}`);
                    // In a real implementation, this would open a detailed view or modal
                });
            });
            
            // Send Reminder buttons
            const sendReminderBtns = document.querySelectorAll('.action-btn[title="Send Reminder"]');
            
            sendReminderBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const studentName = this.closest('tr').querySelector('.student-name').textContent;
                    
                    if (confirm(`Are you sure you want to send a payment reminder to ${studentName}?`)) {
                        alert(`Payment reminder sent to ${studentName}`);
                    }
                });
            });

            // --- Fee Summary Fetch and Display ---
            document.querySelectorAll('.student-fee-card').forEach(card => {
                const studentId = card.getAttribute('data-student-id');
                fetch('get_fees_summary.php?student_id=' + studentId)
                    .then(response => response.json())
                    .then(data => {
                        // Fee summary fields
                        const totalFeeEl = card.querySelector('.fee-summary .fee-summary-item:nth-child(1) .fee-summary-value');
                        const paidAmountEl = card.querySelector('.fee-summary .fee-summary-item:nth-child(2) .fee-summary-value');
                        const progressPercentEl = card.querySelector('.fee-progress-label .progress-percent');
                        const progressFillEl = card.querySelector('.fee-progress-fill');
                        const statusBadgeEl = card.querySelector('.status-badge');
                        const dueDateEl = card.querySelector('.due-date');

                        if (data.success) {
                            totalFeeEl.textContent = '₹' + data.total_fee.toLocaleString();
                            paidAmountEl.textContent = '₹' + data.total_paid.toLocaleString();
                            let percent = data.total_fee > 0 ? Math.round((data.total_paid / data.total_fee) * 100) : 0;
                            progressPercentEl.textContent = percent + '%';
                            progressFillEl.style.width = percent + '%';
                            // Status badge
                            statusBadgeEl.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                            statusBadgeEl.className = 'status-badge status-' + data.status;
                            // Due date (if you want to show, you can fetch and display it here)
                        } else {
                            totalFeeEl.textContent = '-';
                            paidAmountEl.textContent = '-';
                            progressPercentEl.textContent = '-';
                            progressFillEl.style.width = '0%';
                            statusBadgeEl.textContent = 'No Data';
                            statusBadgeEl.className = 'status-badge';
                        }
                    })
                    .catch(() => {
                        // Handle fetch error
                        const totalFeeEl = card.querySelector('.fee-summary .fee-summary-item:nth-child(1) .fee-summary-value');
                        const paidAmountEl = card.querySelector('.fee-summary .fee-summary-item:nth-child(2) .fee-summary-value');
                        const progressPercentEl = card.querySelector('.fee-progress-label .progress-percent');
                        const progressFillEl = card.querySelector('.fee-progress-fill');
                        const statusBadgeEl = card.querySelector('.status-badge');
                        totalFeeEl.textContent = '-';
                        paidAmountEl.textContent = '-';
                        progressPercentEl.textContent = '-';
                        progressFillEl.style.width = '0%';
                        statusBadgeEl.textContent = 'Error';
                        statusBadgeEl.className = 'status-badge';
                });
            });
        });
    </script>
</body>
</html>
</html>