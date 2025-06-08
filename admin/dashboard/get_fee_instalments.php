<?php
require_once 'con.php';

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;
$year = isset($_GET['academic_year']) ? $_GET['academic_year'] : '2024-25';

$response = [
    'success' => false,
    'instalments' => []
];

if ($student_id) {
    $fee_res = $conn->query("SELECT id FROM student_fees WHERE student_id = $student_id AND academic_year = '$year' LIMIT 1");
    if ($fee_res && $fee_res->num_rows > 0) {
        $fee_row = $fee_res->fetch_assoc();
        $student_fee_id = $fee_row['id'];
        $instalments = [];
        $inst_res = $conn->query("SELECT instalment_no, amount, payment_date, payment_method, transaction_id, received_by, notes FROM fee_instalments WHERE student_fee_id = $student_fee_id ORDER BY instalment_no ASC");
        while ($row = $inst_res->fetch_assoc()) {
            $instalments[] = $row;
        }
        $response = [
            'success' => true,
            'instalments' => $instalments
        ];
    }
}
header('Content-Type: application/json');
echo json_encode($response); 