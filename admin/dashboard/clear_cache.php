<?php
/**
 * Cache clearing utility for teacher management system
 * Call this when teacher data is updated to ensure fresh data
 */

function clearTeacherCache($specific_cache = null) {
    $cache_dir = __DIR__ . '/cache';
    $cleared = [];
    
    if (!is_dir($cache_dir)) {
        return ['success' => true, 'message' => 'Cache directory does not exist', 'cleared' => []];
    }
    
    try {
        if ($specific_cache) {
            // Clear specific cache file
            $cache_file = $cache_dir . '/' . $specific_cache . '.json';
            if (file_exists($cache_file)) {
                unlink($cache_file);
                $cleared[] = $specific_cache;
            }
        } else {
            // Clear all teacher-related cache files
            $cache_patterns = [
                'teachers_*.json',
                'class_teacher_assignments.json',
                'teacher_assignments_*.json',
                'subject_teacher_assignments.json'
            ];
            
            foreach ($cache_patterns as $pattern) {
                $files = glob($cache_dir . '/' . $pattern);
                foreach ($files as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                        $cleared[] = basename($file);
                    }
                }
            }
        }
        
        return [
            'success' => true,
            'message' => 'Cache cleared successfully',
            'cleared' => $cleared,
            'count' => count($cleared)
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error clearing cache: ' . $e->getMessage(),
            'cleared' => $cleared
        ];
    }
}

// If called directly via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['clear'])) {
    header('Content-Type: application/json');
    
    $cache_type = $_POST['cache_type'] ?? $_GET['cache_type'] ?? null;
    $result = clearTeacherCache($cache_type);
    
    echo json_encode($result);
    exit;
}
?> 