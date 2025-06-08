<?php
require_once 'con.php';

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '2024-25';

$response = [
    'success' => false,
    'total_fee' => 0,
    'total_paid' => 0,
    'balance' => 0,
    'status' => 'not_found'
];

if ($student_id) {
    $fee_res = $conn->query("SELECT id, total_fee, status FROM student_fees WHERE student_id = $student_id AND academic_year = '$year' LIMIT 1");
    if ($fee_res && $fee_res->num_rows > 0) {
        $fee_row = $fee_res->fetch_assoc();
        $student_fee_id = $fee_row['id'];
        $total_fee = floatval($fee_row['total_fee']);
        $status = $fee_row['status'];
        $paid_res = $conn->query("SELECT SUM(amount) AS total_paid FROM fee_instalments WHERE student_fee_id = $student_fee_id");
        $total_paid = 0;
        if ($paid_res && $paid_row = $paid_res->fetch_assoc()) {
            $total_paid = floatval($paid_row['total_paid']);
        }
        $balance = $total_fee - $total_paid;
        $response = [
            'success' => true,
            'total_fee' => $total_fee,
            'total_paid' => $total_paid,
            'balance' => $balance,
            'status' => $status
        ];
    }
}
header('Content-Type: application/json');
echo json_encode($response); 