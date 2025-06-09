<?php
/**
 * Database Migration Script - Update assessment_type field
 * Run this script to migrate from 'type' to 'assessment_type' in assessments table
 */

require_once __DIR__ . '/../includes/config.php';

// Get database connection
$conn = getDbConnection();

if (!$conn) {
    die("Database connection failed.\n");
}

echo "Starting assessment_type migration...\n";

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if assessment_type column exists
    $result = $conn->query("SHOW COLUMNS FROM assessments LIKE 'assessment_type'");
    
    if ($result->num_rows == 0) {
        // Add assessment_type column
        echo "Adding assessment_type column...\n";
        $conn->query("ALTER TABLE assessments ADD COLUMN assessment_type VARCHAR(10) DEFAULT 'SA'");
    } else {
        echo "assessment_type column already exists.\n";
    }
    
    // Check if type column exists and copy data
    $result = $conn->query("SHOW COLUMNS FROM assessments LIKE 'type'");
    
    if ($result->num_rows > 0) {
        echo "Copying data from 'type' to 'assessment_type'...\n";
        $conn->query("UPDATE assessments SET assessment_type = type WHERE type IS NOT NULL AND type != ''");
        
        echo "Setting default assessment_type for empty values...\n";
        $conn->query("UPDATE assessments SET assessment_type = 'SA' WHERE assessment_type IS NULL OR assessment_type = ''");
    }
    
    // Ensure all assessment_type values are valid (SA or FA)
    echo "Ensuring assessment_type values are valid...\n";
    $conn->query("UPDATE assessments SET assessment_type = 'SA' WHERE assessment_type NOT IN ('SA', 'FA')");
    
    // Add index for better performance
    echo "Adding index for assessment_type...\n";
    $conn->query("CREATE INDEX IF NOT EXISTS idx_assessments_type ON assessments(assessment_type)");
    
    // Commit transaction
    $conn->commit();
    
    echo "Migration completed successfully!\n";
    
    // Show current state
    $result = $conn->query("SELECT assessment_type, COUNT(*) as count FROM assessments GROUP BY assessment_type");
    echo "\nCurrent assessment distribution:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- {$row['assessment_type']}: {$row['count']} assessments\n";
    }
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo "Migration failed: " . $e->getMessage() . "\n";
}

$conn->close();
?>
