<?php
// Simple debug script to enable error reporting and test SA timetable
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h3>Testing SA Timetable Debug</h3>\n";

echo "<p>1. Testing con.php include...</p>\n";
try {
    require_once 'con.php';
    echo "<p style='color: green'>✓ con.php included successfully</p>\n";
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Error including con.php: " . $e->getMessage() . "</p>\n";
    exit;
}

echo "<p>2. Testing sa_timetable.php include...</p>\n";
try {
    ob_start();
    include 'sa_timetable.php';
    $output = ob_get_clean();
    
    if (trim($output)) {
        echo "<p style='color: green'>✓ sa_timetable.php included successfully</p>\n";
        echo "<h4>Page Output:</h4>\n";
        echo $output;
    } else {
        echo "<p style='color: orange'>⚠ sa_timetable.php included but produced no output</p>\n";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>✗ Error including sa_timetable.php: " . $e->getMessage() . "</p>\n";
} catch (Error $e) {
    echo "<p style='color: red'>✗ Fatal error in sa_timetable.php: " . $e->getMessage() . "</p>\n";
}

echo "<p>3. Checking for any PHP errors...</p>\n";
$errors = error_get_last();
if ($errors) {
    echo "<p style='color: red'>Last PHP error: " . print_r($errors, true) . "</p>\n";
} else {
    echo "<p style='color: green'>✓ No PHP errors detected</p>\n";
}
?>
