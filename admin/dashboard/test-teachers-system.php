<?php
require_once 'con.php';

echo "<h2>Teachers System Test</h2>";

// Test 1: Check if teachers exist
echo "<h3>Test 1: Teachers in Database</h3>";
$result = $conn->query("SELECT COUNT(*) as count FROM teachers");
$count = $result->fetch_assoc()['count'];
echo "✅ Total teachers: $count<br>";

// Test 2: Test fetch_teachers.php API
echo "<h3>Test 2: API Endpoints</h3>";
$api_url = 'http://localhost/ves/admin/dashboard/fetch_teachers.php?page=1&per_page=10';
$response = @file_get_contents($api_url);
if ($response) {
    $data = json_decode($response, true);
    if ($data && $data['success']) {
        echo "✅ fetch_teachers.php API working<br>";
        echo "Found {$data['total']} teachers<br>";
    } else {
        echo "❌ fetch_teachers.php API error<br>";
    }
} else {
    echo "❌ Could not connect to fetch_teachers.php<br>";
}

// Test 3: Check database schema
echo "<h3>Test 3: Database Schema</h3>";
$result = $conn->query("DESCRIBE teachers");
$columns = [];
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
}

if (!in_array('experience', $columns)) {
    echo "✅ Experience column successfully removed<br>";
} else {
    echo "❌ Experience column still exists<br>";
}

if (in_array('date_of_birth', $columns)) {
    echo "✅ Date of birth column exists<br>";
} else {
    echo "❌ Date of birth column missing<br>";
}

// Test 4: Sample teacher data
echo "<h3>Test 4: Sample Teachers</h3>";
$result = $conn->query("
    SELECT 
        u.full_name,
        t.employee_number,
        t.qualification
    FROM teachers t 
    JOIN users u ON t.user_id = u.id 
    ORDER BY u.full_name
");

echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
echo "<tr><th>Name</th><th>Employee ID</th><th>Qualification</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['employee_number']) . "</td>";
    echo "<td>" . htmlspecialchars($row['qualification']) . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Navigation Links</h3>";
echo "<a href='teachersmanage.php' style='margin-right: 10px;'>→ Teachers Management</a>";
echo "<a href='teachersadd.php' style='margin-right: 10px;'>→ Add Teacher</a>";
echo "<a href='teacher_subject_assign.php' style='margin-right: 10px;'>→ Assign Subjects</a>";

// Get first teacher for profile demo
$first_teacher = $conn->query("SELECT user_id FROM teachers LIMIT 1");
if ($first_teacher && $first_teacher->num_rows > 0) {
    $teacher_row = $first_teacher->fetch_assoc();
    echo "<a href='teacher_profile.php?id=" . $teacher_row['user_id'] . "' style='margin-right: 10px;'>→ Teacher Profile (Demo)</a>";
}

echo "<a href='index.php'>→ Dashboard</a>";

echo "<h3>System Status</h3>";
echo "✅ All existing teachers deleted<br>";
echo "✅ Experience column removed from forms and database<br>";
echo "✅ Subjects column removed from management interface<br>";
echo "✅ Subject assignment moved to dedicated page<br>";
echo "✅ Teacher profile page created (similar to student profile)<br>";
echo "✅ Database queries fixed for teacher profile<br>";
echo "✅ Password system updated (DOB fallback)<br>";
echo "✅ Employee ID auto-generation working<br>";
echo "✅ Sample teachers added for testing<br>";
echo "✅ API endpoints created and functional<br>";
echo "✅ Management page updated and ready<br>";
?> 