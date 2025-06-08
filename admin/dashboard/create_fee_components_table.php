<?php
// Include database connection
include 'con.php';

// SQL to create fee_components table if it doesn't exist
$sql = "
CREATE TABLE IF NOT EXISTS fee_components (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fee_structure_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE CASCADE
) ENGINE=InnoDB;
";

// Check if there was an error
if ($conn->query($sql) === TRUE) {
    echo "Fee components table created successfully.";
} else {
    echo "Error creating fee components table: " . $conn->error;
}

// Close connection
$conn->close();
?> 