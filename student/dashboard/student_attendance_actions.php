<?php
// student/dashboard/student_attendance_actions.php
// Backend endpoint for student attendance summary and calendar

require_once 'con.php'; // Database connection
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$student_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'get_summary':
        $month = $_POST['month'] ?? date('m');
        $year = $_POST['year'] ?? date('Y');
        $conn = getDbConnection();
        $sql = "SELECT status, COUNT(*) as count FROM attendance WHERE student_user_id = ? AND MONTH(date) = ? AND YEAR(date) = ? GROUP BY status";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $student_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'holiday' => 0];
        $total = 0;
        while ($row = $result->fetch_assoc()) {
            $summary[$row['status']] = (int)$row['count'];
            $total += (int)$row['count'];
        }
        $percentage = $total ? round(($summary['present'] / $total) * 100) : 0;
        $stmt->close();
        $conn->close();
        echo json_encode([
            'status' => 'success',
            'summary' => $summary,
            'total' => $total,
            'percentage' => $percentage
        ]);
        break;
    case 'get_monthly':
        $month = $_POST['month'] ?? date('m');
        $year = $_POST['year'] ?? date('Y');
        $conn = getDbConnection();
        $sql = "SELECT date, status FROM attendance WHERE student_user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('iii', $student_id, $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        $days = [];
        while ($row = $result->fetch_assoc()) {
            $days[$row['date']] = $row['status'];
        }
        $stmt->close();
        $conn->close();
        echo json_encode([
            'status' => 'success',
            'month' => $month,
            'year' => $year,
            'days' => $days
        ]);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
} 