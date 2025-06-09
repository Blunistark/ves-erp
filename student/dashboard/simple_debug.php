<?php
echo "=== SA Timetable Debug Script ===\n";

// 1. Test basic PHP execution
echo "1. PHP is working\n";

// 2. Test error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
echo "2. Error reporting enabled\n";

// 3. Test file includes
echo "3. Testing includes...\n";

// Test sidebar.php
if (file_exists('sidebar.php')) {
    echo "   - sidebar.php exists\n";
} else {
    echo "   - ERROR: sidebar.php not found\n";
}

// Test con.php
if (file_exists('con.php')) {
    echo "   - con.php exists\n";
    try {
        include 'con.php';
        echo "   - con.php loaded successfully\n";
        if (isset($conn)) {
            echo "   - Database connection variable exists\n";
        } else {
            echo "   - ERROR: \$conn variable not set\n";
        }
    } catch (Exception $e) {
        echo "   - ERROR loading con.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "   - ERROR: con.php not found\n";
}

// Test grading functions
$grading_file = '../../includes/grading_functions.php';
if (file_exists($grading_file)) {
    echo "   - grading_functions.php exists\n";
    try {
        require_once $grading_file;
        echo "   - grading_functions.php loaded successfully\n";
        
        if (function_exists('getSAGrades')) {
            echo "   - getSAGrades function exists\n";
        } else {
            echo "   - ERROR: getSAGrades function not found\n";
        }
        
        if (function_exists('getGradeColorClass')) {
            echo "   - getGradeColorClass function exists\n";
        } else {
            echo "   - ERROR: getGradeColorClass function not found\n";
        }
    } catch (Exception $e) {
        echo "   - ERROR loading grading_functions.php: " . $e->getMessage() . "\n";
    }
} else {
    echo "   - ERROR: grading_functions.php not found at: $grading_file\n";
}

// 4. Test session
echo "4. Testing session...\n";
session_start();
if (isset($_SESSION)) {
    echo "   - Session is active\n";
    if (isset($_SESSION['user_id'])) {
        echo "   - User ID in session: " . $_SESSION['user_id'] . "\n";
    } else {
        echo "   - WARNING: No user_id in session\n";
    }
} else {
    echo "   - ERROR: Session not available\n";
}

// 5. Test database query
echo "5. Testing database queries...\n";
if (isset($conn)) {
    try {
        $test_query = "SELECT COUNT(*) as count FROM assessments";
        $result = $conn->query($test_query);
        if ($result) {
            $row = $result->fetch_assoc();
            echo "   - Total assessments in database: " . $row['count'] . "\n";
        } else {
            echo "   - ERROR: Query failed\n";
        }
        
        // Test SA assessments specifically
        $sa_query = "SELECT COUNT(*) as count FROM assessments WHERE assessment_type = 'SA'";
        $sa_result = $conn->query($sa_query);
        if ($sa_result) {
            $sa_row = $sa_result->fetch_assoc();
            echo "   - SA assessments in database: " . $sa_row['count'] . "\n";
        }
        
    } catch (Exception $e) {
        echo "   - ERROR with database query: " . $e->getMessage() . "\n";
    }
} else {
    echo "   - Skipping database tests (no connection)\n";
}

echo "\n=== Debug Complete ===\n";
?>
