<?php
session_start();
include '../../includes/config.php';
include 'con.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(['error' => 'Unauthorized access']);
    } else {
        header('Location: ../index.php');
    }
    exit();
}

// Get student user ID
$user_id = $_SESSION['user_id'];
$student_id = null;

// Get the student ID from the users table
$query = "SELECT student_id FROM students WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $student_id = $row['student_id'];
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Get notifications 
    if ($action === 'get_notifications') {
        $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
        
        $notifications = [];
        $unread_count = 0;
        
        // First get school announcements relevant to students
        $announcement_query = "SELECT a.*, u.name as sender_name, 
                              CASE WHEN rs.id IS NOT NULL THEN 1 ELSE 0 END as is_read 
                              FROM announcements a 
                              JOIN users u ON a.created_by = u.id 
                              LEFT JOIN notification_read_status rs ON rs.notification_type = 'announcement' 
                                  AND rs.notification_id = a.id 
                                  AND rs.user_id = ?
                              WHERE a.is_active = 1 
                                AND (a.target_audience = 'all' OR a.target_audience = 'students')
                                AND (a.expiry_date IS NULL OR a.expiry_date >= CURDATE())";
        
        // Add filters
        if ($filter === 'unread') {
            $announcement_query .= " AND rs.id IS NULL";
        } else if ($filter === 'important') {
            $announcement_query .= " AND a.priority IN ('important', 'urgent')";
        } else if ($filter === 'urgent') {
            $announcement_query .= " AND a.priority = 'urgent'";
        }
        
        $announcement_query .= " ORDER BY 
                                CASE WHEN a.priority = 'urgent' THEN 0
                                     WHEN a.priority = 'important' THEN 1
                                     ELSE 2 END, 
                                a.created_at DESC 
                                LIMIT 50";
        
        $stmt = $conn->prepare($announcement_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Add type field to identify this as an announcement
            $row['type'] = 'announcement';
            $notifications[] = $row;
            
            // Count unread announcements
            if ($row['is_read'] == 0) {
                $unread_count++;
            }
        }
        
        // Then get class notices for the student's class/section
        if ($student_id) {
            // Get the student's class and section
            $class_query = "SELECT c.id as class_id, s.id as section_id 
                            FROM student_classes sc 
                            JOIN classes c ON sc.class_id = c.id 
                            JOIN sections s ON sc.section_id = s.id
                            WHERE sc.student_id = ? AND sc.is_active = 1";
            $stmt = $conn->prepare($class_query);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $class_id = $row['class_id'];
                $section_id = $row['section_id'];
                
                $notice_query = "SELECT n.*, u.name as sender_name, 
                                CASE WHEN rs.id IS NOT NULL THEN 1 ELSE 0 END as is_read,
                                t.name as teacher_name
                                FROM class_notices n 
                                JOIN teachers t ON n.teacher_id = t.id
                                JOIN users u ON t.user_id = u.id
                                LEFT JOIN notification_read_status rs ON rs.notification_type = 'notice' 
                                    AND rs.notification_id = n.id 
                                    AND rs.user_id = ?
                                WHERE n.is_active = 1 
                                  AND n.class_id = ?
                                  AND (n.section_id = ? OR n.section_id IS NULL)
                                  AND (n.expiry_date IS NULL OR n.expiry_date >= CURDATE())";
                
                // Add filters
                if ($filter === 'unread') {
                    $notice_query .= " AND rs.id IS NULL";
                }
                
                $notice_query .= " ORDER BY n.created_at DESC LIMIT 50";
                
                $stmt = $conn->prepare($notice_query);
                $stmt->bind_param("iii", $user_id, $class_id, $section_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                while ($row = $result->fetch_assoc()) {
                    // Add type field to identify this as a class notice
                    $row['type'] = 'notice';
                    // Set a default priority (notices don't have priority in the DB)
                    $row['priority'] = 'normal';
                    $notifications[] = $row;
                    
                    // Count unread notices
                    if ($row['is_read'] == 0) {
                        $unread_count++;
                    }
                }
            }
        }
        
        // TODO: In the future, add personal messages here
        
        // Sort all notifications by priority and date
        usort($notifications, function($a, $b) {
            // First sort by priority
            $priority_order = ['urgent' => 0, 'important' => 1, 'normal' => 2];
            $a_priority = $priority_order[$a['priority']];
            $b_priority = $priority_order[$b['priority']];
            
            if ($a_priority !== $b_priority) {
                return $a_priority - $b_priority;
            }
            
            // Then sort by date (most recent first)
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Return the notifications as JSON
        echo json_encode([
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
        exit();
    }
    
    // If action is not recognized
    else {
        echo json_encode(['error' => 'Invalid action']);
        exit();
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Since most actions are handled by mark_read.php, this is reserved for future use
    echo json_encode(['error' => 'Invalid request method']);
    exit();
}

// If neither GET nor POST with valid action
header('Location: notifications.php');
exit();
?> 