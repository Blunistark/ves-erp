<?php
// Include database connection
include 'con.php';

// Check if payment ID is provided
if (!isset($_GET['payment_id']) || empty($_GET['payment_id'])) {
    die("Error: Payment ID is required");
}

$paymentId = intval($_GET['payment_id']);

// Get payment details
$sql = "SELECT fp.*, 
         fs.title as fee_title, fs.due_date,
         s.full_name as student_name, s.admission_number, s.user_id as student_id,
         c.name as class_name, sec.name as section_name
         FROM fee_payments fp
         JOIN fee_structures fs ON fp.fee_structure_id = fs.id
         JOIN students s ON fp.student_user_id = s.user_id
         JOIN classes c ON s.class_id = c.id
         JOIN sections sec ON s.section_id = sec.id
         WHERE fp.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $paymentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Error: Payment not found");
}

$payment = $result->fetch_assoc();

// Get school information
// In a production environment, this would be fetched from a settings table
$schoolName = "Vinodh English School";
$schoolAddress = "123 Education Street, Knowledge City";
$schoolPhone = "+91 1234567890";
$schoolEmail = "info@vinodh.edu";
$schoolLogo = "../assets/img/school-logo.png"; // Change to your actual logo path

// Format date
$paymentDate = new DateTime($payment['payment_date']);
$formattedPaymentDate = $paymentDate->format('d M, Y');

// Format due date
$dueDate = new DateTime($payment['due_date']);
$formattedDueDate = $dueDate->format('d M, Y');

// Format receipt number with leading zeros
$receiptNumber = sprintf("RCPT-%05d", $payment['id']);

// Get previous payments for this fee structure
$previousPaymentsSql = "SELECT SUM(amount_paid) as previous_paid
                        FROM fee_payments
                        WHERE student_user_id = ? 
                        AND fee_structure_id = ? 
                        AND id < ?";
$previousPaymentsStmt = $conn->prepare($previousPaymentsSql);
$previousPaymentsStmt->bind_param('iii', $payment['student_id'], $payment['fee_structure_id'], $paymentId);
$previousPaymentsStmt->execute();
$previousPaymentsResult = $previousPaymentsStmt->get_result();
$previousPayments = $previousPaymentsResult->fetch_assoc();
$previousPaid = $previousPayments['previous_paid'] ?: 0;

// Get fee structure details
$feeStructureSql = "SELECT * FROM fee_structures WHERE id = ?";
$feeStructureStmt = $conn->prepare($feeStructureSql);
$feeStructureStmt->bind_param('i', $payment['fee_structure_id']);
$feeStructureStmt->execute();
$feeStructureResult = $feeStructureStmt->get_result();
$feeStructure = $feeStructureResult->fetch_assoc();

// Get fee components
$componentsSql = "SELECT * FROM fee_components WHERE fee_structure_id = ?";
$componentsStmt = $conn->prepare($componentsSql);
$componentsStmt->bind_param('i', $payment['fee_structure_id']);
$componentsStmt->execute();
$componentsResult = $componentsStmt->get_result();
$components = [];
while ($component = $componentsResult->fetch_assoc()) {
    $components[] = $component;
}

// Get student parent details (if available)
$parentDetailsSql = "SELECT p.user_id, p.relationship, p.phone, p.email, u.full_name as parent_name
                     FROM parent_accounts p
                     JOIN users u ON p.user_id = u.id
                     WHERE p.student_user_id = ?";
$parentDetailsStmt = $conn->prepare($parentDetailsSql);
$parentDetailsStmt->bind_param('i', $payment['student_id']);
$parentDetailsStmt->execute();
$parentDetailsResult = $parentDetailsStmt->get_result();
$parentDetails = $parentDetailsResult->fetch_assoc();

// Generate QR code content (would be a URL or identifier in production)
$qrCodeContent = "VES|RCPT|" . $payment['id'] . "|" . $payment['student_user_id'] . "|" . $payment['amount_paid'];

// Format status badge
$statusBadgeClass = '';
switch ($payment['status']) {
    case 'paid':
        $statusBadgeClass = 'paid';
        break;
    case 'partial':
        $statusBadgeClass = 'partial';
        break;
    case 'pending':
        $statusBadgeClass = 'pending';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Fee Receipt - <?php echo $receiptNumber; ?></title>
    <link rel="stylesheet" href="css/fee_receipt.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="receipt-container">
        <!-- Watermark -->
        <div class="watermark"><?php echo strtoupper($payment['status']); ?></div>
        
        <!-- Header -->
        <div class="receipt-header">
            <?php if (file_exists($schoolLogo)): ?>
                <img src="<?php echo $schoolLogo; ?>" alt="School Logo" class="school-logo">
            <?php endif; ?>
            
            <h1 class="school-name"><?php echo $schoolName; ?></h1>
            <p class="school-address"><?php echo $schoolAddress; ?></p>
            <p class="school-contact">Phone: <?php echo $schoolPhone; ?> | Email: <?php echo $schoolEmail; ?></p>
            
            <!-- Status Badge -->
            <div class="receipt-badge <?php echo $statusBadgeClass; ?>">
                <?php echo ucfirst($payment['status']); ?>
            </div>
        </div>
        
        <!-- Title -->
        <div class="receipt-title-container">
            <div class="receipt-title">FEE RECEIPT</div>
        </div>
        
        <!-- Receipt Metadata -->
        <div class="receipt-metadata">
            <div class="receipt-number">
                <strong>Receipt No:</strong> <?php echo $receiptNumber; ?>
            </div>
            <div class="receipt-date">
                <strong>Date:</strong> <?php echo $formattedPaymentDate; ?>
            </div>
        </div>
        
        <!-- Receipt Body -->
        <div class="receipt-body">
            <!-- Student Information Card -->
            <div class="info-card">
                <div class="student-info">
                    <div>
                        <div class="info-group">
                            <div class="info-label">Student Name</div>
                            <div class="info-value"><?php echo $payment['student_name']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Admission Number</div>
                            <div class="info-value"><?php echo $payment['admission_number']; ?></div>
                        </div>
                        
                        <?php if ($parentDetails): ?>
                        <div class="info-group">
                            <div class="info-label">Parent/Guardian</div>
                            <div class="info-value"><?php echo $parentDetails['parent_name']; ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div>
                        <div class="info-group">
                            <div class="info-label">Class</div>
                            <div class="info-value"><?php echo $payment['class_name'] . ' ' . $payment['section_name']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Fee Structure</div>
                            <div class="info-value"><?php echo $payment['fee_title']; ?></div>
                        </div>
                        
                        <div class="info-group">
                            <div class="info-label">Due Date</div>
                            <div class="info-value"><?php echo $formattedDueDate; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Fee Breakdown Table -->
            <table class="fee-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="width: 25%;">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($components) > 0): ?>
                        <?php foreach ($components as $component): ?>
                            <tr>
                                <td><?php echo $component['name']; ?></td>
                                <td><?php echo number_format($component['amount'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td><?php echo $payment['fee_title']; ?></td>
                            <td><?php echo number_format($feeStructure['amount'], 2); ?></td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr class="total-row">
                        <td>Total Fee Amount</td>
                        <td><?php echo number_format($feeStructure['amount'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Payment Details -->
            <div class="payment-summary">
                <div class="payment-detail">
                    <div class="payment-detail-header">
                        <span>Payment Details</span>
                        <i class="fas fa-money-check payment-detail-icon"></i>
                    </div>
                    
                    <div class="payment-info">
                        <div class="payment-label">Payment Method</div>
                        <div class="payment-value">
                            <?php 
                                $method = ucfirst($payment['payment_method'] ?: 'Cash');
                                echo $method;
                            ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($payment['reference_number'])): ?>
                        <div class="payment-info">
                            <div class="payment-label">Reference Number</div>
                            <div class="payment-value"><?php echo $payment['reference_number']; ?></div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($payment['remarks'])): ?>
                        <div class="payment-info">
                            <div class="payment-label">Remarks</div>
                            <div class="payment-value"><?php echo $payment['remarks']; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="payment-detail">
                    <div class="payment-detail-header">
                        <span>Transaction Summary</span>
                        <i class="fas fa-receipt payment-detail-icon"></i>
                    </div>
                    
                    <div class="payment-info">
                        <div class="payment-label">Previous Paid Amount</div>
                        <div class="payment-value positive">₹<?php echo number_format($previousPaid, 2); ?></div>
                    </div>
                    
                    <div class="payment-info">
                        <div class="payment-label">Current Payment</div>
                        <div class="payment-value positive">₹<?php echo number_format($payment['amount_paid'], 2); ?></div>
                    </div>
                    
                    <div class="payment-info">
                        <div class="payment-label">Remaining Balance</div>
                        <div class="payment-value <?php echo $payment['remaining_amount'] > 0 ? 'negative' : 'positive'; ?>">
                            ₹<?php echo number_format($payment['remaining_amount'], 2); ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Signatures -->
            <div class="signature-section">
                <div class="signature-box">
                    <?php if (isset($payment['cashier_signature']) && !empty($payment['cashier_signature'])): ?>
                        <img src="<?php echo $payment['cashier_signature']; ?>" alt="Cashier Signature" class="signature-image">
                    <?php endif; ?>
                    <div class="signature-line">Authorized Signature</div>
                </div>
                
                <div class="signature-box">
                    <div class="signature-line">Receiver's Signature</div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="receipt-footer">
            <div class="footer-note">
                <p>This is a computer-generated receipt and does not require a physical signature.</p>
                <p>For any queries regarding this receipt, please contact the school office.</p>
            </div>
        </div>
        
        <!-- QR Code (Would be actual QR in production) -->
        <?php if (isset($qrCodeContent)): ?>
        <div class="qr-code">
            <!-- Placeholder QR code. In production, use a QR code library -->
            <svg viewBox="0 0 100 100" style="width: 100%; height: 100%;">
                <rect x="10" y="10" width="80" height="80" fill="#fff" stroke="#000" stroke-width="2"></rect>
                <text x="50" y="50" font-size="8" text-anchor="middle">QR Code</text>
                <text x="50" y="60" font-size="6" text-anchor="middle">Scan to verify</text>
            </svg>
        </div>
        <?php endif; ?>
    </div>
    
    <button class="print-button" onclick="window.print();">
        <i class="fas fa-print"></i> Print Receipt
    </button>
    
    <!-- Mobile Orientation Message -->
    <div class="orientation-message">
        <p>For best printing results, please use landscape orientation on mobile devices.</p>
    </div>
    
    <script>
        // Add class to body for small screens
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 768) {
                document.body.classList.add('mobile-view');
            }
            
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768) {
                    document.body.classList.add('mobile-view');
                } else {
                    document.body.classList.remove('mobile-view');
                }
            });
        });
    </script>
</body>
</html> 