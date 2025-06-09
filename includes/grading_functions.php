<?php
/**
 * Dual Grading System Functions
 * Handles both SA (Summative Assessment) and FA (Formative Assessment) grading
 */

require_once __DIR__ . '/config.php';

/**
 * Calculate grade for SA (Summative Assessment) based on percentage
 * @param float $percentage The percentage score
 * @return string Grade code (A+, A, B, C, D)
 */
function calculateSAGrade($percentage) {
    if ($percentage >= 92) return 'A+';
    if ($percentage >= 75) return 'A';
    if ($percentage >= 60) return 'B';
    if ($percentage >= 50) return 'C';
    return 'D';
}

/**
 * Calculate grade for FA (Formative Assessment) based on marks out of 25
 * @param int $marks The marks obtained
 * @return string Grade code (A+, A, B, C, D)
 */
function calculateFAGrade($marks) {
    if ($marks >= 19) return 'A+';
    if ($marks >= 16) return 'A';
    if ($marks >= 13) return 'B';
    if ($marks >= 10) return 'C';
    return 'D';
}

/**
 * Get grade information based on assessment type
 * @param string $assessment_type 'SA' or 'FA'
 * @param float $score The score (percentage for SA, marks for FA)
 * @return array Grade information
 */
function getGradeInfo($assessment_type, $score) {
    if ($assessment_type === 'SA') {
        $grade_code = calculateSAGrade($score);
        $grade_desc = getSAGradeDescription($grade_code);
    } else {
        $grade_code = calculateFAGrade($score);
        $grade_desc = getFAGradeDescription($grade_code);
    }
    
    return [
        'code' => $grade_code,
        'description' => $grade_desc
    ];
}

/**
 * Get SA grade description
 */
function getSAGradeDescription($grade_code) {
    $descriptions = [
        'A+' => 'Excellent (92-100%)',
        'A' => 'Very Good (75-91%)',
        'B' => 'Good (60-74%)',
        'C' => 'Average (50-59%)',
        'D' => 'Below Average (0-49%)'
    ];
    return $descriptions[$grade_code] ?? 'Unknown';
}

/**
 * Get FA grade description
 */
function getFAGradeDescription($grade_code) {
    $descriptions = [
        'A+' => 'Excellent (19-25)',
        'A' => 'Very Good (16-18)',
        'B' => 'Good (13-15)',
        'C' => 'Average (10-12)',
        'D' => 'Below Average (0-9)'
    ];
    return $descriptions[$grade_code] ?? 'Unknown';
}

/**
 * Calculate grade for any assessment based on its type
 * @param int $marks_obtained Marks obtained
 * @param int $total_marks Total marks
 * @param string $assessment_type 'SA' or 'FA'
 * @return array Grade information
 */
function calculateGrade($marks_obtained, $total_marks, $assessment_type) {
    if ($assessment_type === 'SA') {
        $percentage = ($total_marks > 0) ? ($marks_obtained / $total_marks) * 100 : 0;
        return getGradeInfo('SA', $percentage);
    } else {
        // For FA, assume marks are out of 25
        $normalized_marks = ($total_marks > 0) ? ($marks_obtained / $total_marks) * 25 : 0;
        return getGradeInfo('FA', $normalized_marks);
    }
}

/**
 * Get all SA grades
 */
function getSAGrades() {
    return [
        ['code' => 'A+', 'min_percentage' => 92, 'max_percentage' => 100, 'description' => 'Excellent (92-100%)'],
        ['code' => 'A', 'min_percentage' => 75, 'max_percentage' => 91, 'description' => 'Very Good (75-91%)'],
        ['code' => 'B', 'min_percentage' => 60, 'max_percentage' => 74, 'description' => 'Good (60-74%)'],
        ['code' => 'C', 'min_percentage' => 50, 'max_percentage' => 59, 'description' => 'Average (50-59%)'],
        ['code' => 'D', 'min_percentage' => 0, 'max_percentage' => 49, 'description' => 'Below Average (0-49%)']
    ];
}

/**
 * Get all FA grades
 */
function getFAGrades() {
    return [
        ['code' => 'A+', 'min_marks' => 19, 'max_marks' => 25, 'description' => 'Excellent (19-25)'],
        ['code' => 'A', 'min_marks' => 16, 'max_marks' => 18, 'description' => 'Very Good (16-18)'],
        ['code' => 'B', 'min_marks' => 13, 'max_marks' => 15, 'description' => 'Good (13-15)'],
        ['code' => 'C', 'min_marks' => 10, 'max_marks' => 12, 'description' => 'Average (10-12)'],
        ['code' => 'D', 'min_marks' => 0, 'max_marks' => 9, 'description' => 'Below Average (0-9)']
    ];
}

/**
 * Check if a student passed based on grade
 */
function isPassing($grade_code) {
    return !in_array(strtoupper($grade_code), ['D', 'F']);
}

/**
 * Get grade color class for UI
 */
function getGradeColorClass($grade_code) {
    switch (strtoupper($grade_code)) {
        case 'A+':
        case 'A':
            return 'grade-excellent';
        case 'B':
            return 'grade-good';
        case 'C':
            return 'grade-average';
        case 'D':
        case 'F':
            return 'grade-poor';
        default:
            return 'grade-default';
    }
}

/**
 * Get grade points for a grade code
 * @param string $grade_code The grade code (A+, A, B, C, D)
 * @return float Grade points
 */
function getGradePoints($grade_code) {
    $grade_points = [
        'A+' => 4.0,
        'A' => 3.5,
        'B' => 3.0,
        'C' => 2.5,
        'D' => 2.0,
        'F' => 0.0
    ];
    return $grade_points[$grade_code] ?? 0.0;
}
