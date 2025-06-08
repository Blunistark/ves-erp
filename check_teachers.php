<?php
include 'setup.php';

// First check if we have any users at all
$userCount = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
echo "Total users in database: $userCount\n";

$teacherCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")->fetch_assoc()['count'];
echo "Total teachers in users table: $teacherCount\n";

$teacherTableCount = $conn->query("SELECT COUNT(*) as count FROM teachers")->fetch_assoc()['count'];
echo "Total records in teachers table: $teacherTableCount\n";

// Now try to get teacher data
$sql = "SELECT u.id, u.email, u.full_name, t.employee_number, u.status 
        FROM users u 
        INNER JOIN teachers t ON u.id = t.user_id 
        WHERE u.role = 'teacher' 
        LIMIT 5";
        
$result = $conn->query($sql);

if ($result === false) {
    echo "SQL Error: " . $conn->error . "\n";
} elseif ($result->num_rows > 0) {
    echo "\nFound " . $result->num_rows . " teachers:\n";
    while ($row = $result->fetch_assoc()) {
        echo "ID: " . $row['id'] . 
             ", Email: " . $row['email'] . 
             ", Name: " . $row['full_name'] . 
             ", Employee ID: " . ($row['employee_number'] ?? 'NULL') . 
             ", Status: " . $row['status'] . "\n";
    }
} else {
    echo "\nNo teachers found with the JOIN query.\n";
    
    // Let's check users table separately
    echo "\nChecking users table directly:\n";
    $users = $conn->query("SELECT id, email, full_name, role, status FROM users WHERE role = 'teacher' LIMIT 3");
    if ($users && $users->num_rows > 0) {
        while ($user = $users->fetch_assoc()) {
            echo "User ID: " . $user['id'] . ", Email: " . $user['email'] . ", Name: " . $user['full_name'] . ", Role: " . $user['role'] . ", Status: " . $user['status'] . "\n";
        }
    }
    
    // Check teachers table separately
    echo "\nChecking teachers table directly:\n";
    $teachers = $conn->query("SELECT id, user_id, employee_number FROM teachers LIMIT 3");
    if ($teachers && $teachers->num_rows > 0) {
        while ($teacher = $teachers->fetch_assoc()) {
            echo "Teacher ID: " . $teacher['id'] . ", User ID: " . $teacher['user_id'] . ", Employee Number: " . ($teacher['employee_number'] ?? 'NULL') . "\n";
        }
    }
}
?>
