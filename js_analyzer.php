<?php
// Extract JavaScript content from teacher management file
$file_content = file_get_contents('c:/Program Files/Ampps/www/erp/admin/dashboard/teacher_management_unified.php');

// Find the script tag content
$start = strpos($file_content, '<script>');
$end = strrpos($file_content, '</script>');

if ($start !== false && $end !== false) {
    $start += 8; // Length of '<script>'
    $js_content = substr($file_content, $start, $end - $start);
    
    echo "<h2>JavaScript Content Analysis</h2>";
    echo "<pre>";
    
    // Split into lines and analyze
    $lines = explode("\n", $js_content);
    $line_num = 1;
    $brace_count = 0;
    $paren_count = 0;
    $issues = [];
    
    foreach ($lines as $line) {
        $trimmed = trim($line);
        
        // Count braces and parentheses
        $brace_count += substr_count($line, '{') - substr_count($line, '}');
        $paren_count += substr_count($line, '(') - substr_count($line, ')');
        
        // Check for potential issues
        if (preg_match('/^\s*return\s*;?\s*$/', $trimmed) && $brace_count <= 0) {
            $issues[] = "Line $line_num: Potential orphaned return statement: '$trimmed'";
        }
        
        if (preg_match('/^\s*}\s*$/', $trimmed) && $brace_count < 0) {
            $issues[] = "Line $line_num: Extra closing brace";
        }
        
        if (preg_match('/function\s+\w+\s*\([^)]*\)\s*{?\s*$/', $trimmed) && !strpos($trimmed, '{')) {
            $issues[] = "Line $line_num: Function definition missing opening brace";
        }
        
        $line_num++;
    }
    
    echo "Total lines: " . count($lines) . "\n";
    echo "Final brace count: $brace_count\n";
    echo "Final paren count: $paren_count\n\n";
    
    if (!empty($issues)) {
        echo "POTENTIAL ISSUES FOUND:\n";
        foreach ($issues as $issue) {
            echo $issue . "\n";
        }
    } else {
        echo "No obvious syntax issues detected.\n";
    }
    
    echo "</pre>";
    
    // Show first 50 lines for detailed inspection
    echo "<h3>First 50 lines of JavaScript:</h3>";
    echo "<pre>";
    for ($i = 0; $i < min(50, count($lines)); $i++) {
        printf("%3d: %s\n", $i + 1, htmlspecialchars($lines[$i]));
    }
    echo "</pre>";
    
} else {
    echo "Could not find JavaScript content in the file.";
}
?>
