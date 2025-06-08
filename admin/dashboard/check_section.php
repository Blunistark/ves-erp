<?php
// Simple script to check if students exist in section 3
require_once '../../includes/config.php';

// Function to log messages
function logMessage($message) {
    $logFile = __DIR__ . '/section_check.log';
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $message . "\n", FILE_APPEND);
    echo $message . "<br>";
}

logMessage("Starting section check");

try {
    // Get database connection
    $conn = getDbConnection();
    
    if (!$conn) {
        logMessage("Failed to connect to database");
        exit;
    }
    
    logMessage("Connected to database successfully");
    
    // Check if section 3 exists
    $sectionSql = "SELECT id, name, class_id FROM sections WHERE id = 3";
    $sectionResult = $conn->query($sectionSql);
    
    if ($sectionResult && $sectionResult->num_rows > 0) {
        $section = $sectionResult->fetch_assoc();
        logMessage("Section exists: ID = {$section['id']}, Name = {$section['name']}, Class ID = {$section['class_id']}");
    } else {
        logMessage("Section with ID 3 does not exist");
    }
    
    // Check if class 3 exists
    $classSql = "SELECT id, name FROM classes WHERE id = 3";
    $classResult = $conn->query($classSql);
    
    if ($classResult && $classResult->num_rows > 0) {
        $class = $classResult->fetch_assoc();
        logMessage("Class exists: ID = {$class['id']}, Name = {$class['name']}");
    } else {
        logMessage("Class with ID 3 does not exist");
    }
    
    // Count students in section 3
    $countSql = "SELECT COUNT(*) as total FROM students WHERE section_id = 3";
    $countResult = $conn->query($countSql);
    
    if ($countResult) {
        $countRow = $countResult->fetch_assoc();
        logMessage("Total students in section 3: {$countRow['total']}");
    } else {
        logMessage("Error counting students: " . $conn->error);
    }
    
    // Get list of students in section 3
    $studentSql = "SELECT user_id, full_name, roll_number, class_id, section_id 
                   FROM students 
                   WHERE section_id = 3 
                   ORDER BY roll_number, full_name
                   LIMIT 10";
    $studentResult = $conn->query($studentSql);
    
    if ($studentResult && $studentResult->num_rows > 0) {
        logMessage("Found {$studentResult->num_rows} students (showing first 10):");
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>User ID</th><th>Name</th><th>Roll Number</th><th>Class ID</th><th>Section ID</th></tr>";
        
        while ($student = $studentResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$student['user_id']}</td>";
            echo "<td>{$student['full_name']}</td>";
            echo "<td>{$student['roll_number']}</td>";
            echo "<td>{$student['class_id']}</td>";
            echo "<td>{$student['section_id']}</td>";
            echo "</tr>";
            
            logMessage("Student: {$student['full_name']} (ID: {$student['user_id']}, Roll: {$student['roll_number']})");
        }
        
        echo "</table>";
    } else {
        logMessage("No students found in section 3");
    }
    
    // Check students with mismatched section_id and class_id
    $mismatchSql = "SELECT user_id, full_name, class_id, section_id FROM students 
                   WHERE section_id = 3 AND class_id != 3";
    $mismatchResult = $conn->query($mismatchSql);
    
    if ($mismatchResult && $mismatchResult->num_rows > 0) {
        logMessage("Found {$mismatchResult->num_rows} students with mismatched section and class IDs:");
        
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-top: 20px;'>";
        echo "<tr><th>User ID</th><th>Name</th><th>Class ID</th><th>Section ID</th></tr>";
        
        while ($mismatch = $mismatchResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$mismatch['user_id']}</td>";
            echo "<td>{$mismatch['full_name']}</td>";
            echo "<td>{$mismatch['class_id']}</td>";
            echo "<td>{$mismatch['section_id']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    } else {
        logMessage("No students with mismatched section and class IDs");
    }
    
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
}

logMessage("Section check completed");
?> 