<?php
/**
 * Script to update teacher passwords based on their date of birth
 * Password format: DDMMYYYY (e.g., 15051980 for May 15, 1980)
 */

require_once '../../includes/functions.php';
require_once 'con.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check if user is logged in and is an admin
if (!isLoggedIn() || !hasRole('admin')) {
    die("Access denied. Admin privileges required.");
}

// Set execution time limit for potentially long-running script
set_time_limit(300); // 5 minutes

echo "<h2>Teacher Password Update Script</h2>\n";
echo "<p>This script will update all teacher passwords based on their date of birth in DDMMYYYY format.</p>\n";

// Get all teachers with their current data
$query = "SELECT u.id, u.email, u.full_name, t.date_of_birth 
          FROM users u 
          JOIN teachers t ON u.id = t.user_id 
          WHERE u.role = 'teacher' AND u.status = 'active'
          ORDER BY u.full_name";

$result = $conn->query($query);
if (!$result) {
    die("Error fetching teachers: " . $conn->error);
}

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}
$result->free();

echo "<h3>Found " . count($teachers) . " teachers to update:</h3>\n";
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; margin: 20px 0;'>\n";
echo "<tr style='background-color: #f0f0f0;'><th>Teacher Name</th><th>Email</th><th>DOB</th><th>New Password</th><th>Status</th></tr>\n";

$updated = 0;
$errors = 0;

foreach ($teachers as $teacher) {
    $teacherId = $teacher['id'];
    $teacherName = htmlspecialchars($teacher['full_name']);
    $email = htmlspecialchars($teacher['email']);
    $dob = $teacher['date_of_birth'];
    
    echo "<tr>";
    echo "<td>{$teacherName}</td>";
    echo "<td>{$email}</td>";
    echo "<td>{$dob}</td>";
    
    if (empty($dob) || $dob === '0000-00-00') {
        echo "<td>-</td>";
        echo "<td style='color: red;'>❌ No DOB available</td>";
        $errors++;
    } else {
        // Convert DOB from YYYY-MM-DD to DDMMYYYY format
        $dobParts = explode('-', $dob);
        if (count($dobParts) === 3) {
            $year = $dobParts[0];
            $month = $dobParts[1];
            $day = $dobParts[2];
            
            // Create password in DDMMYYYY format
            $passwordPlain = $day . $month . $year;
            
            // Hash the password
            $passwordHash = password_hash($passwordPlain, PASSWORD_DEFAULT);
            
            echo "<td>{$passwordPlain}</td>";
            
            // Update the user's password
            $updateQuery = "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $conn->prepare($updateQuery);
            
            if ($stmt) {
                $stmt->bind_param("si", $passwordHash, $teacherId);
                
                if ($stmt->execute()) {
                    echo "<td style='color: green;'>✅ Updated successfully</td>";
                    $updated++;
                    
                    // Log the audit event
                    logAudit('users', $teacherId, 'UPDATE');
                } else {
                    echo "<td style='color: red;'>❌ Update failed: " . htmlspecialchars($stmt->error) . "</td>";
                    $errors++;
                }
                $stmt->close();
            } else {
                echo "<td style='color: red;'>❌ Query preparation failed: " . htmlspecialchars($conn->error) . "</td>";
                $errors++;
            }
        } else {
            echo "<td>-</td>";
            echo "<td style='color: red;'>❌ Invalid DOB format</td>";
            $errors++;
        }
    }
    echo "</tr>\n";
    
    // Flush output for real-time display
    if (ob_get_level()) {
        ob_flush();
    }
    flush();
}

echo "</table>\n";

echo "<h3>Summary:</h3>\n";
echo "<ul>\n";
echo "<li><strong>Total teachers processed:</strong> " . count($teachers) . "</li>\n";
echo "<li><strong>Successfully updated:</strong> <span style='color: green;'>{$updated}</span></li>\n";
echo "<li><strong>Errors:</strong> <span style='color: red;'>{$errors}</span></li>\n";
echo "</ul>\n";

if ($updated > 0) {
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; margin: 20px 0; border-radius: 5px;'>\n";
    echo "<strong>✅ Password update completed!</strong><br>\n";
    echo "All teachers can now log in using their date of birth in DDMMYYYY format as their password.\n";
    echo "</div>\n";
}

if ($errors > 0) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 20px 0; border-radius: 5px;'>\n";
    echo "<strong>⚠️ Some errors occurred during the update process.</strong><br>\n";
    echo "Please review the errors above and fix any issues manually if needed.\n";
    echo "</div>\n";
}

echo "<hr>\n";
echo "<p><strong>Example login credentials:</strong></p>\n";
echo "<ul>\n";
foreach (array_slice($teachers, 0, 3) as $teacher) {
    if (!empty($teacher['date_of_birth']) && $teacher['date_of_birth'] !== '0000-00-00') {
        $dobParts = explode('-', $teacher['date_of_birth']);
        if (count($dobParts) === 3) {
            $passwordPlain = $dobParts[2] . $dobParts[1] . $dobParts[0];
            echo "<li><strong>" . htmlspecialchars($teacher['full_name']) . "</strong>: Email: " . htmlspecialchars($teacher['email']) . ", Password: {$passwordPlain}</li>\n";
        }
    }
}
echo "</ul>\n";

echo "<p><a href='users.php'>← Back to Users Management</a></p>\n";

$conn->close();
?>
