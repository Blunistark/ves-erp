<?php
// This is a standalone file to check and fix your database table
// Save this as check_db.php and run it in your browser to verify and fix your database

// Include your database connection
require_once 'con.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Table Check & Fix</h1>";

// Check if the students table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'students'");
if ($tableCheck->num_rows == 0) {
    echo "<p>The 'students' table does not exist. Creating it now...</p>";
    
    // Create the table with the simplified schema
    $createTable = "CREATE TABLE students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        last_name VARCHAR(100) NOT NULL,
        gender ENUM('Male','Female','Other'),
        aadhar_card_number VARCHAR(20),
        dob DATE,
        blood_group VARCHAR(5),
        email VARCHAR(100),
        medical_conditions TEXT,
        sats_number VARCHAR(50),
        father_full_name VARCHAR(100),
        father_mobile_number VARCHAR(15),
        father_email VARCHAR(100),
        father_aadhar_card_number VARCHAR(20),
        mother_full_name VARCHAR(100),
        mother_mobile_number VARCHAR(15),
        mother_email VARCHAR(100),
        mother_aadhar_card_number VARCHAR(20),
        emergency_contact_number VARCHAR(15),
        residential_address TEXT,
        standard VARCHAR(50),
        section VARCHAR(10),
        religion VARCHAR(50),
        caste VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTable) === TRUE) {
        echo "<p style='color:green'>Successfully created the 'students' table!</p>";
    } else {
        echo "<p style='color:red'>Error creating table: " . $conn->error . "</p>";
    }
} else {
    echo "<p>The 'students' table exists. Checking structure...</p>";
    
    // Get the table structure
    $columns = $conn->query("SHOW COLUMNS FROM students");
    $foundColumns = [];
    
    while ($column = $columns->fetch_assoc()) {
        $foundColumns[] = $column['Field'];
    }
    
    echo "<p>Found columns: " . implode(", ", $foundColumns) . "</p>";
    
    // Check for essential columns
    $essentialColumns = ['id','first_name','middle_name','last_name','gender','aadhar_card_number','dob','blood_group','email','medical_conditions','sats_number','father_full_name','father_mobile_number','father_email','father_aadhar_card_number','mother_full_name','mother_mobile_number','mother_email','mother_aadhar_card_number','emergency_contact_number','residential_address','standard','section','religion','caste'];
    $missingColumns = [];
    
    foreach ($essentialColumns as $col) {
        if (!in_array($col, $foundColumns)) {
            $missingColumns[] = $col;
        }
    }
    
    if (!empty($missingColumns)) {
        echo "<p>Adding missing columns: " . implode(", ", $missingColumns) . "</p>";
        $typeMap = [
            'first_name' => 'VARCHAR(100) NOT NULL',
            'middle_name' => 'VARCHAR(100)',
            'last_name' => 'VARCHAR(100) NOT NULL',
            'gender' => "ENUM('Male','Female','Other')",
            'aadhar_card_number' => 'VARCHAR(20)',
            'dob' => 'DATE',
            'blood_group' => 'VARCHAR(5)',
            'email' => 'VARCHAR(100)',
            'medical_conditions' => 'TEXT',
            'sats_number' => 'VARCHAR(50)',
            'father_full_name' => 'VARCHAR(100)',
            'father_mobile_number' => 'VARCHAR(15)',
            'father_email' => 'VARCHAR(100)',
            'father_aadhar_card_number' => 'VARCHAR(20)',
            'mother_full_name' => 'VARCHAR(100)',
            'mother_mobile_number' => 'VARCHAR(15)',
            'mother_email' => 'VARCHAR(100)',
            'mother_aadhar_card_number' => 'VARCHAR(20)',
            'emergency_contact_number' => 'VARCHAR(15)',
            'residential_address' => 'TEXT',
            'standard' => 'VARCHAR(50)',
            'section' => 'VARCHAR(10)',
            'religion' => 'VARCHAR(50)',
            'caste' => 'VARCHAR(50)'
        ];
        foreach ($missingColumns as $col) {
            $sql = "ALTER TABLE students ADD COLUMN `$col` " . $typeMap[$col];
            if ($conn->query($sql) === TRUE) {
                echo "<p>Added column $col</p>";
            } else {
                echo "<p style='color:red'>Error adding $col: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p>All student columns are present.</p>";
    }
}

// Test insert
echo "<h2>Testing DB Connection & Insert</h2>";
try {
    $testName = "Test Student";
    $testRoll = "TEST123";
    $testClass = "10";
    $testSection = "A";
    $testGender = "Male";
    $testDob = "2000-01-01";
    $testAddress = "Test Address";
    
    $stmt = $conn->prepare("INSERT INTO students (name, roll_number, class, section, gender, dob, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $testName, $testRoll, $testClass, $testSection, $testGender, $testDob, $testAddress);
    
    if ($stmt->execute()) {
        $insertId = $conn->insert_id;
        echo "<p style='color:green'>Test insert successful with ID: $insertId</p>";
        
        // Clean up the test data
        $conn->query("DELETE FROM students WHERE id = $insertId");
        echo "<p>Test data removed.</p>";
    } else {
        echo "<p style='color:red'>Test insert failed: " . $stmt->error . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
}

echo "<p>Check complete!</p>";