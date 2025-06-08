<?php
require_once 'con.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Database connection successful<br>";

// Check if academic_years table exists
$tableCheckResult = $conn->query("SHOW TABLES LIKE 'academic_years'");
if ($tableCheckResult->num_rows > 0) {
    echo "Table academic_years exists<br>";
    
    // Describe the table structure
    $result = $conn->query("DESCRIBE academic_years");
    if ($result) {
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        
        // Keep track of existing columns
        $existingColumns = [];
        
        while($row = $result->fetch_assoc()) {
            print_r($row);
            $existingColumns[] = $row['Field'];
        }
        echo "</pre>";
        
        // Check for missing columns and add them
        $requiredColumns = [
            'id' => "ALTER TABLE academic_years ADD COLUMN id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST",
            'name' => "ALTER TABLE academic_years ADD COLUMN name VARCHAR(20) NOT NULL, ADD UNIQUE KEY (name)",
            'start_date' => "ALTER TABLE academic_years ADD COLUMN start_date DATE NOT NULL",
            'end_date' => "ALTER TABLE academic_years ADD COLUMN end_date DATE NOT NULL",
            'is_current' => "ALTER TABLE academic_years ADD COLUMN is_current TINYINT(1) NOT NULL DEFAULT 0",
            'created_at' => "ALTER TABLE academic_years ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
            'updated_at' => "ALTER TABLE academic_years ADD COLUMN updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP"
        ];
        
        echo "<h3>Checking for missing columns:</h3>";
        
        foreach ($requiredColumns as $column => $alterSql) {
            if (!in_array($column, $existingColumns)) {
                echo "Missing column: $column - Adding it now...<br>";
                if ($conn->query($alterSql)) {
                    echo "Added column $column successfully<br>";
                } else {
                    echo "Error adding column $column: " . $conn->error . "<br>";
                }
            }
        }
        
        // Check if there are any records
        $countResult = $conn->query("SELECT COUNT(*) as count FROM academic_years");
        $count = $countResult->fetch_assoc()['count'];
        echo "Number of records: $count<br>";
        
        if ($count > 0) {
            echo "<h3>Existing academic years:</h3>";
            $years = $conn->query("SELECT * FROM academic_years");
            echo "<pre>";
            while($year = $years->fetch_assoc()) {
                print_r($year);
            }
            echo "</pre>";
        }
    } else {
        echo "Error describing table: " . $conn->error;
    }
} else {
    echo "Table academic_years does not exist<br>";
    
    // Create the table
    echo "Creating the academic_years table...<br>";
    $createTableSql = "CREATE TABLE academic_years (
        id INT(11) NOT NULL AUTO_INCREMENT,
        name VARCHAR(20) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        is_current TINYINT(1) NOT NULL DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY (name)
    )";
    
    if ($conn->query($createTableSql)) {
        echo "Table created successfully<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Now try to add a test academic year
$name = "2023-2024";
$startDate = "2023-06-01";
$endDate = "2024-05-31";
$isCurrent = 1;

// Update the existing test record if already exists
$updateSql = "UPDATE academic_years SET start_date = ?, end_date = ?, is_current = ? WHERE name = ?";
$updateStmt = $conn->prepare($updateSql);

if ($updateStmt) {
    $updateStmt->bind_param("ssis", $startDate, $endDate, $isCurrent, $name);
    $updateStmt->execute();
    
    if ($updateStmt->affected_rows > 0) {
        echo "Updated existing academic year '$name'<br>";
    } else {
        // Insert a new record
        $insertSql = "INSERT INTO academic_years (name, start_date, end_date, is_current) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        
        if (!$insertStmt) {
            echo "Error preparing insert statement: " . $conn->error . "<br>";
        } else {
            $insertStmt->bind_param("sssi", $name, $startDate, $endDate, $isCurrent);
            
            if ($insertStmt->execute()) {
                echo "Test academic year added successfully with ID: " . $conn->insert_id . "<br>";
            } else {
                echo "Error adding test academic year: " . $insertStmt->error . "<br>";
            }
            
            $insertStmt->close();
        }
    }
    
    $updateStmt->close();
} else {
    echo "Error preparing update statement: " . $conn->error . "<br>";
}

// Update the existing record to set is_current = 1 if it exists
if (in_array('is_current', $existingColumns)) {
    $sql = "UPDATE academic_years SET is_current = 1 WHERE name = '2025-2025'";
    if ($conn->query($sql)) {
        echo "Updated existing record with is_current = 1<br>";
    } else {
        echo "Error updating existing record: " . $conn->error . "<br>";
    }
}

echo "<p>Done! Now <a href='import_student.php'>go back to the import page</a> and try again.</p>";

$conn->close();
?> 