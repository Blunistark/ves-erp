<?php
// Web-based test for teacher timetable API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session and include functions
require_once __DIR__ . '/../../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in as teacher
if (!isLoggedIn() || !hasRole(['teacher', 'headmaster'])) {
    echo "<h3>Please log in as a teacher first</h3>";
    echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
    echo "<p>Role: " . ($_SESSION['role'] ?? 'Not set') . "</p>";
    exit;
}

$teacher_id = $_SESSION['user_id'];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Timetable API Test for Teacher <?php echo $_SESSION['full_name']; ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        .info { background-color: #d1ecf1; border-color: #bee5eb; }
        pre { white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>Timetable API Test for Teacher: <?php echo $_SESSION['full_name']; ?></h1>
    <p><strong>Teacher ID:</strong> <?php echo $teacher_id; ?></p>

    <?php
    // Include database connection
    require_once __DIR__ . '/../../includes/config.php';
    
    // Test 1: Check if teacher exists
    echo '<div class="test-section info">';
    echo '<h3>Test 1: Teacher Information</h3>';
    try {
        $sql = "SELECT id, full_name, email, role, status FROM users WHERE id = ? AND role = 'teacher'";
        $result = executeQuery($sql, "i", [$teacher_id]);
        
        if (!empty($result)) {
            echo "<p class='success'>✓ Teacher found in database</p>";
            echo "<pre>" . json_encode($result[0], JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p class='error'>✗ Teacher not found in database</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
    }
    echo '</div>';

    // Test 2: Check timetable periods for this teacher
    echo '<div class="test-section info">';
    echo '<h3>Test 2: Teacher\'s Timetable Periods</h3>';
    try {
        $sql = "SELECT COUNT(*) as total_periods FROM timetable_periods WHERE teacher_id = ?";
        $result = executeQuery($sql, "i", [$teacher_id]);
        $period_count = $result[0]['total_periods'];
        
        echo "<p><strong>Total periods assigned:</strong> " . $period_count . "</p>";
        
        if ($period_count > 0) {
            // Get sample periods
            $sql = "SELECT tp.*, t.name as timetable_name, t.status as timetable_status, 
                           s.name as subject_name, c.name as class_name
                    FROM timetable_periods tp
                    LEFT JOIN timetables t ON tp.timetable_id = t.id
                    LEFT JOIN subjects s ON tp.subject_id = s.id
                    LEFT JOIN classes c ON t.class_id = c.id
                    WHERE tp.teacher_id = ?
                    LIMIT 5";
            $periods = executeQuery($sql, "i", [$teacher_id]);
            
            echo "<p class='success'>✓ Teacher has periods assigned</p>";
            echo "<h4>Sample periods:</h4>";
            echo "<pre>" . json_encode($periods, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p class='error'>✗ No periods found for this teacher</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error checking periods: " . $e->getMessage() . "</p>";
    }
    echo '</div>';

    // Test 3: Check published timetables for this teacher
    echo '<div class="test-section info">';
    echo '<h3>Test 3: Published Timetables for Teacher</h3>';
    try {
        $sql = "SELECT DISTINCT t.*, 
                       ay.name as academic_year_name, 
                       c.name as class_name, 
                       s.name as section_name 
                FROM timetables t
                INNER JOIN timetable_periods tp ON t.id = tp.timetable_id
                LEFT JOIN academic_years ay ON t.academic_year_id = ay.id
                LEFT JOIN classes c ON t.class_id = c.id
                LEFT JOIN sections s ON t.section_id = s.id
                WHERE tp.teacher_id = ? AND t.status = 'published'";
        
        $timetables = executeQuery($sql, "i", [$teacher_id]);
        
        echo "<p><strong>Published timetables count:</strong> " . count($timetables) . "</p>";
        
        if (!empty($timetables)) {
            echo "<p class='success'>✓ Teacher has published timetables</p>";
            echo "<pre>" . json_encode($timetables, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p class='error'>✗ No published timetables found for this teacher</p>";
            
            // Check if there are unpublished timetables
            $sql_unpublished = "SELECT DISTINCT t.status, COUNT(*) as count
                               FROM timetables t
                               INNER JOIN timetable_periods tp ON t.id = tp.timetable_id
                               WHERE tp.teacher_id = ?
                               GROUP BY t.status";
            $status_counts = executeQuery($sql_unpublished, "i", [$teacher_id]);
            echo "<h4>Timetable status breakdown:</h4>";
            echo "<pre>" . json_encode($status_counts, JSON_PRETTY_PRINT) . "</pre>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error checking timetables: " . $e->getMessage() . "</p>";
    }
    echo '</div>';

    // Test 4: Test the API call via AJAX
    echo '<div class="test-section info">';
    echo '<h3>Test 4: Live API Call</h3>';
    echo '<button onclick="testAPI()">Test Timetable API</button>';
    echo '<div id="api-result"></div>';
    echo '</div>';
    ?>

    <script>
    function testAPI() {
        document.getElementById('api-result').innerHTML = '<p>Loading...</p>';
        
        fetch('/erp/backend/api/timetables')
            .then(response => response.json())
            .then(data => {
                document.getElementById('api-result').innerHTML = 
                    '<h4>API Response:</h4><pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('api-result').innerHTML = 
                    '<p class="error">API Error: ' + error.message + '</p>';
            });
    }
    </script>
</body>
</html>
