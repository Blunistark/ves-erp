<?php
/**
 * API Router
 * Routes API requests to the appropriate handler based on the request URI
 */

// Get the request URI path
$requestUri = $_SERVER['REQUEST_URI'];

// Extract the API endpoint path
$pattern = '/\/backend\/api\/([^\/\?]+)(?:\/(.*))?/';
$matches = [];

// Debug output
file_put_contents(__DIR__ . '/router_debug.log', 
    date('Y-m-d H:i:s') . ' - ' . 
    'Request URI: ' . $requestUri . "\n",
    FILE_APPEND);

if (preg_match($pattern, $requestUri, $matches)) {
    $endpoint = $matches[1] ?? null;
    $pathInfo = $matches[2] ?? '';
    
    // Debug output
    file_put_contents(__DIR__ . '/router_debug.log', 
        date('Y-m-d H:i:s') . ' - ' . 
        'Endpoint: ' . $endpoint . ', PathInfo: ' . $pathInfo . "\n",
        FILE_APPEND);
    
    // Set PATH_INFO for handlers to parse
    $_SERVER['PATH_INFO'] = !empty($pathInfo) ? '/' . $pathInfo : '';
    
    // Route to appropriate handler based on endpoint
    switch ($endpoint) {
        case 'timetables':
            require_once __DIR__ . '/timetable.php';
            break;
            
        case 'subjects':
            require_once __DIR__ . '/subjects.php';
            break;
            
        case 'teachers':
            require_once __DIR__ . '/teachers.php';
            break;
            
        case 'academic-years':
            require_once __DIR__ . '/academic-years.php';
            break;
            
        case 'classes':
            require_once __DIR__ . '/classes.php';
            break;
            
        case 'sections':
            require_once __DIR__ . '/sections.php';
            break;
            
        case 'class-notes.php':
            require_once __DIR__ . '/class-notes.php';
            break;
            
        case 'students':
            require_once __DIR__ . '/students.php';
            break;
            
        case 'student-profile':
            require_once __DIR__ . '/student-profile.php';
            break;
            
        case 'student-timetable':
            require_once __DIR__ . '/student-timetable.php';
            break;
            
        // Add more endpoints as needed
            
        default:
            // Handle unknown endpoint
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['error' => 'Unknown API endpoint']);
            exit;
    }
} else {
    // Invalid API request format
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid API request format']);
    exit;
} 