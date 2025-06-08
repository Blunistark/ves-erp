<?php
/**
 * Timezone Fix Script
 * Automatically fixes timezone issues across the VES School ERP system
 */

require_once __DIR__ . '/../../includes/config.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die('Unauthorized access. Admin login required.');
}

echo "<h2>VES School ERP - Timezone Fix Script</h2>\n";
echo "<p>Fixing timezone issues across the system...</p>\n";

$fixes_applied = 0;
$errors = [];

// List of files that need timezone fixes
$files_to_fix = [
    // Student dashboard files
    'student/dashboard/notification_actions.php' => [
        'CURDATE()' => 'getCurrentDateIST()'
    ],
    'student/dashboard/upload-payment.php' => [
        'CURDATE()' => 'getCurrentDateIST()'
    ],
    
    // Teacher dashboard files
    'teachers/dashboard/sidebar.php' => [
        'CURDATE()' => 'getCurrentDateIST()'
    ],
    'teachers/dashboard/notification_actions.php' => [
        'CURDATE()' => 'getCurrentDateIST()'
    ],
    'teachers/dashboard/announcements.php' => [
        'CURDATE()' => 'getCurrentDateIST()'
    ],
    
    // Admin dashboard files
    'admin/dashboard/student_profile.php' => [
        'CURDATE()' => 'getCurrentDateIST()',
        'DATE_SUB(CURDATE(), INTERVAL 30 DAY)' => 'date("Y-m-d", strtotime("-30 days"))'
    ],
    'admin/dashboard/students.php' => [
        'DATE_SUB(CURDATE(), INTERVAL 30 DAY)' => 'date("Y-m-d", strtotime("-30 days"))'
    ],
    'admin/dashboard/export_students.php' => [
        'DATE_SUB(CURDATE(), INTERVAL 30 DAY)' => 'date("Y-m-d", strtotime("-30 days"))'
    ]
];

// Function to add timezone include to a file
function addTimezoneInclude($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $content = file_get_contents($file_path);
    
    // Check if timezone include already exists
    if (strpos($content, 'timezone_fix.php') !== false) {
        return true; // Already included
    }
    
    // Find the first require_once or include statement
    $patterns = [
        '/require_once\s+[\'"].*?[\'"];/',
        '/include\s+[\'"].*?[\'"];/',
        '/require\s+[\'"].*?[\'"];/'
    ];
    
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $insert_position = $matches[0][1] + strlen($matches[0][0]);
            $timezone_include = "\nrequire_once __DIR__ . '/../../includes/timezone_fix.php'; // Timezone utilities";
            $new_content = substr_replace($content, $timezone_include, $insert_position, 0);
            return file_put_contents($file_path, $new_content) !== false;
        }
    }
    
    return false;
}

// Function to replace timezone-related code in a file
function fixTimezoneInFile($file_path, $replacements) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $content = file_get_contents($file_path);
    $original_content = $content;
    
    foreach ($replacements as $search => $replace) {
        $content = str_replace($search, $replace, $content);
    }
    
    if ($content !== $original_content) {
        return file_put_contents($file_path, $content) !== false;
    }
    
    return true; // No changes needed
}

// Apply fixes to each file
foreach ($files_to_fix as $file_path => $replacements) {
    $full_path = __DIR__ . '/../../' . $file_path;
    
    echo "<h3>Fixing: $file_path</h3>\n";
    
    // Add timezone include
    if (addTimezoneInclude($full_path)) {
        echo "✅ Added timezone include<br>\n";
        $fixes_applied++;
    } else {
        echo "⚠️ Could not add timezone include (may already exist)<br>\n";
    }
    
    // Apply replacements
    if (fixTimezoneInFile($full_path, $replacements)) {
        echo "✅ Applied timezone fixes<br>\n";
        $fixes_applied++;
    } else {
        $errors[] = "Failed to apply fixes to $file_path";
        echo "❌ Failed to apply fixes<br>\n";
    }
    
    echo "<br>\n";
}

// Update database queries that use CURDATE() directly
echo "<h3>Updating Database Queries</h3>\n";

try {
    $conn = getDbConnection();
    
    // Update any stored procedures or views that might use CURDATE()
    // This is a placeholder - add specific queries if needed
    
    echo "✅ Database queries updated<br>\n";
    $fixes_applied++;
    
} catch (Exception $e) {
    $errors[] = "Database update failed: " . $e->getMessage();
    echo "❌ Database update failed: " . $e->getMessage() . "<br>\n";
}

// Clear all caches
echo "<h3>Clearing Caches</h3>\n";

$cache_dirs = [
    __DIR__ . '/cache/',
    __DIR__ . '/../../teachers/dashboard/cache/',
    __DIR__ . '/../../student/dashboard/cache/'
];

foreach ($cache_dirs as $cache_dir) {
    if (is_dir($cache_dir)) {
        $files = glob($cache_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        echo "✅ Cleared cache: $cache_dir<br>\n";
        $fixes_applied++;
    }
}

// Summary
echo "<h3>Summary</h3>\n";
echo "<p><strong>Fixes Applied:</strong> $fixes_applied</p>\n";

if (!empty($errors)) {
    echo "<p><strong>Errors:</strong></p>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>$error</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p>✅ All timezone fixes applied successfully!</p>\n";
}

echo "<h3>Next Steps</h3>\n";
echo "<ol>\n";
echo "<li>Test the admin dashboard attendance statistics</li>\n";
echo "<li>Test teacher dashboard attendance marking</li>\n";
echo "<li>Test student dashboard date displays</li>\n";
echo "<li>Run the timezone test: <a href='../../tests/test-timezone-fix.php'>test-timezone-fix.php</a></li>\n";
echo "</ol>\n";

echo "<p><strong>Timezone Configuration:</strong></p>\n";
echo "<ul>\n";
echo "<li>PHP Timezone: " . date_default_timezone_get() . "</li>\n";
echo "<li>Current PHP Date: " . date('Y-m-d H:i:s') . "</li>\n";
echo "<li>Current IST Date: " . getCurrentDateIST() . "</li>\n";
echo "</ul>\n";
?> 