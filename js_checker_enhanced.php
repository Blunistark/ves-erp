<?php
// Enhanced JavaScript syntax checker with actual rendering
require_once __DIR__ . '/includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Mock the required variables that would be set in the main file
$user_role = $_SESSION['role'] ?? 'admin';
$sections = [
    ['id' => 1, 'name' => 'A', 'class_id' => 1],
    ['id' => 2, 'name' => 'B', 'class_id' => 1]
];

echo "<h2>Enhanced JavaScript Analysis</h2>";

// Read the actual file content
$file_content = file_get_contents('admin/dashboard/teacher_management_unified.php');

// Extract just the JavaScript content between <script> tags
preg_match('/<script[^>]*>(.*?)<\/script>/s', $file_content, $matches);

if (isset($matches[1])) {
    $js_content = $matches[1];
    
    // Process the PHP variables as they would be rendered
    $js_content = str_replace('<?php echo json_encode($user_role); ?>', json_encode($user_role), $js_content);
    $js_content = str_replace('<?php echo json_encode($sections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>', json_encode($sections, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), $js_content);
    
    echo "<h3>Rendered JavaScript (first 100 lines):</h3>";
    echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 400px;'>";
    
    $lines = explode("\n", $js_content);
    $line_count = 0;
    $brace_count = 0;
    $issues = [];
    
    for ($i = 0; $i < min(100, count($lines)); $i++) {
        $line = $lines[$i];
        $trimmed = trim($line);
        
        // Track brace balance
        $brace_count += substr_count($line, '{') - substr_count($line, '}');
        
        // Check for return statements outside functions
        if (preg_match('/^\s*return\s*;?\s*$/', $trimmed)) {
            // Check if we're at document level (brace_count should be > 0 if inside functions)
            if ($brace_count <= 0) {
                $issues[] = "Line " . ($i + 1) . ": ILLEGAL RETURN - return statement at document level";
            }
        }
        
        // Check for unmatched braces
        if ($brace_count < 0) {
            $issues[] = "Line " . ($i + 1) . ": EXTRA CLOSING BRACE - more } than {";
        }
        
        printf("%3d: %s\n", $i + 1, htmlspecialchars($line));
        $line_count++;
    }
    
    echo "</pre>";
    
    echo "<h3>Analysis Results:</h3>";
    echo "<pre>";
    echo "Total lines analyzed: $line_count of " . count($lines) . "\n";
    echo "Final brace count: $brace_count\n";
    
    if (!empty($issues)) {
        echo "\nSYNTAX ISSUES FOUND:\n";
        foreach ($issues as $issue) {
            echo "❌ $issue\n";
        }
    } else {
        echo "\n✅ No syntax issues detected in analyzed portion\n";
    }
    
    // Check for incomplete functions
    echo "\nFunction Analysis:\n";
    $in_function = false;
    $function_brace_count = 0;
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = trim($lines[$i]);
        
        if (preg_match('/function\s+\w+\s*\(/', $line)) {
            echo "Function found at line " . ($i + 1) . ": " . substr($line, 0, 60) . "...\n";
        }
    }
    
    echo "</pre>";
    
} else {
    echo "<p style='color: red;'>❌ Could not extract JavaScript content from the file</p>";
}
?>
