<?php
// Include required files and start session
require_once __DIR__ . '/../../includes/functions.php';

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../../login.php");
    exit;
}

// Include database connection
require_once 'con.php';

// Get parameters
$class_id = isset($_GET['class']) ? intval($_GET['class']) : 0;
$section = isset($_GET['section']) ? $_GET['section'] : '';

// Build filename
$filename = 'students_export_' . date('Y-m-d_H-i-s');
if ($class_id) {
    $filename .= '_class_' . $class_id;
    if ($section) {
        $filename .= '_section_' . strtolower($section);
    }
}
$filename .= '.csv';

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Pragma: no-cache');
header('Expires: 0');

// Create output stream
$output = fopen('php://output', 'w');

// CSV Headers
$headers = [
    'Admission Number',
    'Full Name',
    'Class',
    'Section',
    'Roll Number',
    'Gender',
    'Date of Birth',
    'Father Name',
    'Father Aadhar',
    'Mother Name', 
    'Mother Aadhar',
    'Address',
    'Pincode',
    'Mobile',
    'Alt Mobile',
    'Email',
    'Blood Group',
    'Nationality',
    'Academic Year',
    'Student Aadhar',
    'Medical Conditions',
    'Admission Date',
    'Attendance %',
    'Present Days',
    'Total Days',
    'Fee Status',
    'Total Fees',
    'Paid Fees',
    'Pending Fees'
];

// Write headers
fputcsv($output, $headers);

// Build query based on filters
$where_conditions = [];
$params = [];
$types = '';

if ($class_id) {
    $where_conditions[] = "s.class_id = ?";
    $params[] = $class_id;
    $types .= 'i';
    
    if ($section) {
        $where_conditions[] = "sec.name = ?";
        $params[] = $section;
        $types .= 's';
    }
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Query to get student data with all details
$sql = "SELECT 
            s.admission_number,
            s.full_name,
            c.name as class_name,
            sec.name as section_name,
            s.roll_number,
            s.gender_code,
            s.dob,
            s.father_name,
            s.father_aadhar_number,
            s.mother_name,
            s.mother_aadhar_number,
            s.address,
            s.pincode,
            s.mobile,
            s.alt_mobile,
            s.contact_email,
            s.blood_group_code,
            s.nationality,
            ay.name as academic_year,
            s.aadhar_card_number,
            s.medical_conditions,
            s.admission_date,
            COALESCE(att.present_count, 0) as present_days,
            COALESCE(att.total_days, 0) as total_days,
            COALESCE(ROUND((att.present_count / NULLIF(att.total_days, 0)) * 100, 1), 0) as attendance_percentage,
            COALESCE(f.total_fees, 0) as total_fees,
            COALESCE(f.paid_amount, 0) as paid_fees,
            COALESCE(f.total_fees - f.paid_amount, 0) as pending_fees,
            CASE 
                WHEN COALESCE(f.paid_amount, 0) >= COALESCE(f.total_fees, 0) THEN 'Paid'
                WHEN COALESCE(f.paid_amount, 0) > 0 THEN 'Partial'
                ELSE 'Pending'
            END as fee_status
        FROM students s
        LEFT JOIN classes c ON s.class_id = c.id
        LEFT JOIN sections sec ON s.section_id = sec.id
        LEFT JOIN academic_years ay ON s.academic_year_id = ay.id
        LEFT JOIN (
            SELECT 
                student_user_id,
                COUNT(CASE WHEN status = 'present' THEN 1 END) as present_count,
                COUNT(*) as total_days
            FROM attendance 
            WHERE date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY student_user_id
        ) att ON s.user_id = att.student_user_id
        LEFT JOIN (
            SELECT 
                fp.student_user_id,
                SUM(fs.amount) as total_fees,
                SUM(fp.amount_paid) as paid_amount
            FROM fee_payments fp
            JOIN fee_structures fs ON fp.fee_structure_id = fs.id
            WHERE fs.academic_year_id = (SELECT id FROM academic_years WHERE is_current = 1)
            GROUP BY fp.student_user_id
        ) f ON s.user_id = f.student_user_id
        $where_clause
        ORDER BY s.class_id, sec.name, s.roll_number, s.full_name";

try {
    if (!empty($params)) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($sql);
    }

    // Write data rows
    while ($row = $result->fetch_assoc()) {
        $csv_row = [
            $row['admission_number'],
            $row['full_name'],
            $row['class_name'],
            strtoupper($row['section_name']),
            $row['roll_number'],
            $row['gender_code'],
            $row['dob'],
            $row['father_name'],
            $row['father_aadhar_number'],
            $row['mother_name'],
            $row['mother_aadhar_number'],
            $row['address'],
            $row['pincode'],
            $row['mobile'],
            $row['alt_mobile'],
            $row['contact_email'],
            $row['blood_group_code'],
            $row['nationality'],
            $row['academic_year'],
            $row['aadhar_card_number'],
            $row['medical_conditions'],
            $row['admission_date'],
            $row['attendance_percentage'] . '%',
            $row['present_days'],
            $row['total_days'],
            $row['fee_status'],
            '₹' . number_format($row['total_fees']),
            '₹' . number_format($row['paid_fees']),
            '₹' . number_format($row['pending_fees'])
        ];
        
        fputcsv($output, $csv_row);
    }

    if (isset($stmt)) {
        $stmt->close();
    }

} catch (Exception $e) {
    // Write error to CSV
    fputcsv($output, ['Error: ' . $e->getMessage()]);
}

// Close output stream
fclose($output);
$conn->close();
exit;
?> 