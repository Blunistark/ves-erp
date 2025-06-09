<?php
require_once 'includes/grading_functions.php';

echo "<h2>Dual Grading System Validation</h2>\n";

// Test SA Grading (Percentage-based)
echo "<h3>SA (Summative Assessment) Grading Tests</h3>\n";
$sa_tests = [
    ['marks' => 95, 'total' => 100, 'expected' => 'A+'],
    ['marks' => 82, 'total' => 100, 'expected' => 'A'],
    ['marks' => 67, 'total' => 100, 'expected' => 'B'],
    ['marks' => 55, 'total' => 100, 'expected' => 'C'],
    ['marks' => 42, 'total' => 100, 'expected' => 'D'],
];

foreach ($sa_tests as $test) {
    $grade_info = calculateGrade($test['marks'], $test['total'], 'SA');
    $result = $grade_info['code'] === $test['expected'] ? '✓ PASS' : '✗ FAIL';
    echo "SA: {$test['marks']}/{$test['total']} -> {$grade_info['code']} (Expected: {$test['expected']}) $result\n";
}

// Test FA Grading (Marks-based out of 25)
echo "\n<h3>FA (Formative Assessment) Grading Tests</h3>\n";
$fa_tests = [
    ['marks' => 23, 'total' => 25, 'expected' => 'A+'],
    ['marks' => 18, 'total' => 25, 'expected' => 'A'],
    ['marks' => 14, 'total' => 25, 'expected' => 'B'],
    ['marks' => 11, 'total' => 25, 'expected' => 'C'],
    ['marks' => 8, 'total' => 25, 'expected' => 'D'],
];

foreach ($fa_tests as $test) {
    $grade_info = calculateGrade($test['marks'], $test['total'], 'FA');
    $result = $grade_info['code'] === $test['expected'] ? '✓ PASS' : '✗ FAIL';
    echo "FA: {$test['marks']}/{$test['total']} -> {$grade_info['code']} (Expected: {$test['expected']}) $result\n";
}

// Test Grade Scale Information
echo "\n<h3>Grade Scale Information</h3>\n";
echo "SA Grades:\n";
foreach (getSAGrades() as $grade) {
    echo "- {$grade['code']}: {$grade['description']}\n";
}

echo "\nFA Grades:\n";
foreach (getFAGrades() as $grade) {
    echo "- {$grade['code']}: {$grade['description']}\n";
}

echo "\n<h3>✅ Dual Grading System Validation Complete</h3>\n";
?>
