<?php
require_once 'con.php';
header('Content-Type: application/json');

function response($success, $message, $errors = [], $duplicates = [], $imported = 0) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'errors' => $errors,
        'duplicates' => $duplicates,
        'imported' => $imported
    ]);
    exit;
}

$expected = ['employee_id','first_name','last_name','dob','gender','email','alt_email','phone','emergency_phone','joined_date','profile_photo','address','city','qualification','experience','username','designation','status'];

if (isset($_POST['teachers'])) {
    // New: Handle JSON array of teachers from POST
    $teachers = json_decode($_POST['teachers'], true);
    if (!is_array($teachers)) {
        response(false, 'Invalid teachers data.');
    }
    $errors = [];
    $duplicates = [];
    $imported = 0;
    foreach ($teachers as $idx => $rowObj) {
        $rowNum = isset($rowObj['rowNum']) ? $rowObj['rowNum'] : ($idx + 2); // +2 for header and 0-index
        $row = isset($rowObj['data']) ? $rowObj['data'] : $rowObj;
        $data = [];
        foreach ($expected as $col) {
            $data[$col] = trim($row[$col] ?? '');
        }
        // Basic validation
        if (!$data['employee_id'] || !$data['email'] || !$data['first_name'] || !$data['last_name']) {
            $errors[] = "Row $rowNum: Missing required fields (employee_id, email, first_name, last_name)";
            continue;
        }
        // Duplicate check
        $stmt = $conn->prepare('SELECT id FROM teachers WHERE employee_id = ? OR email = ?');
        $stmt->bind_param('ss', $data['employee_id'], $data['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $duplicates[] = [
                'rowNum' => $rowNum,
                'employee_id' => $data['employee_id'],
                'email' => $data['email']
            ];
            $stmt->close();
            continue;
        }
        $stmt->close();
        // Insert
        $stmt = $conn->prepare('INSERT INTO teachers(employee_id, first_name, last_name, dob, gender, email, alt_email, phone, emergency_phone, joined_date, profile_photo, address, city, qualification, experience, username, designation, status, full_name, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $full_name = $data['first_name'] . ' ' . $data['last_name'];
        $default_password = password_hash('changeme', PASSWORD_DEFAULT);
        $stmt->bind_param('ssssssssssssssssssss', $data['employee_id'], $data['first_name'], $data['last_name'], $data['dob'], $data['gender'], $data['email'], $data['alt_email'], $data['phone'], $data['emergency_phone'], $data['joined_date'], $data['profile_photo'], $data['address'], $data['city'], $data['qualification'], $data['experience'], $data['username'], $data['designation'], $data['status'], $full_name, $default_password);
        if ($stmt->execute()) {
            $imported++;
        } else {
            $errors[] = "Row $rowNum: DB error: " . $stmt->error;
        }
        $stmt->close();
    }
    response(true, 'Import completed.', $errors, $duplicates, $imported);
}

// Fallback: legacy CSV file upload logic
if (!isset($_FILES['csv']) || $_FILES['csv']['error'] !== UPLOAD_ERR_OK) {
    response(false, 'No file uploaded or upload error.');
}

$csvFile = $_FILES['csv']['tmp_name'];
if (($handle = fopen($csvFile, 'r')) === false) {
    response(false, 'Failed to open uploaded CSV file.');
}

$headers = fgetcsv($handle);
$map = [];
foreach ($expected as $col) {
    $idx = array_search($col, $headers);
    if ($idx === false) {
        response(false, "Missing required column: $col");
    }
    $map[$col] = $idx;
}

$errors = [];
$duplicates = [];
$imported = 0;
$rowNum = 1;
while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;
    $data = [];
    foreach ($expected as $col) {
        $data[$col] = trim($row[$map[$col]] ?? '');
    }
    // Basic validation
    if (!$data['employee_id'] || !$data['email'] || !$data['first_name'] || !$data['last_name']) {
        $errors[] = [
            'row' => $rowNum,
            'reason' => 'Missing required fields (employee_id, email, first_name, last_name)'
        ];
        continue;
    }
    // Duplicate check
    $stmt = $conn->prepare('SELECT id FROM teachers WHERE employee_id = ? OR email = ?');
    $stmt->bind_param('ss', $data['employee_id'], $data['email']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $duplicates[] = [
            'row' => $rowNum,
            'employee_id' => $data['employee_id'],
            'email' => $data['email']
        ];
        $stmt->close();
        continue;
    }
    $stmt->close();
    // Insert
    $stmt = $conn->prepare('INSERT INTO teachers(employee_id, first_name, last_name, dob, gender, email, alt_email, phone, emergency_phone, joined_date, profile_photo, address, city, qualification, experience, username, designation, status, full_name, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $full_name = $data['first_name'] . ' ' . $data['last_name'];
    $default_password = password_hash('changeme', PASSWORD_DEFAULT);
    $stmt->bind_param('ssssssssssssssssssss', $data['employee_id'], $data['first_name'], $data['last_name'], $data['dob'], $data['gender'], $data['email'], $data['alt_email'], $data['phone'], $data['emergency_phone'], $data['joined_date'], $data['profile_photo'], $data['address'], $data['city'], $data['qualification'], $data['experience'], $data['username'], $data['designation'], $data['status'], $full_name, $default_password);
    if ($stmt->execute()) {
        $imported++;
    } else {
        $errors[] = [
            'row' => $rowNum,
            'reason' => 'DB error: ' . $stmt->error
        ];
    }
    $stmt->close();
}
fclose($handle);
response(true, 'Import completed.', $errors, $duplicates, $imported); 