<?php
/**
 * Timetable Backup and Recovery System
 * Provides functionality to backup and restore timetable data
 */

require_once __DIR__ . '/../../includes/functions.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    startSecureSession();
}

// Check authentication and role
if (!isLoggedIn() || !hasRole(['admin', 'headmaster'])) {
    header("Location: ../../index.php");
    exit;
}

require_once 'con.php';

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

header('Content-Type: application/json');

try {
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create_backup':
            handleCreateBackup();
            break;
        case 'list_backups':
            handleListBackups();
            break;
        case 'restore_backup':
            handleRestoreBackup();
            break;
        case 'delete_backup':
            handleDeleteBackup();
            break;
        case 'download_backup':
            handleDownloadBackup();
            break;
        default:
            throw new Exception('Invalid action specified');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Create a new timetable backup
 */
function handleCreateBackup() {
    global $conn, $user_id;
    
    $backup_name = $_POST['backup_name'] ?? 'Backup_' . date('Y-m-d_H-i-s');
    $include_periods = isset($_POST['include_periods']) && $_POST['include_periods'] === 'true';
    
    $conn->beginTransaction();
    
    try {
        // Create backup record
        $backup_sql = "
            INSERT INTO timetable_backups (name, created_by, created_at, include_periods, status)
            VALUES (?, ?, NOW(), ?, 'creating')
        ";
        $stmt = $conn->prepare($backup_sql);
        $stmt->execute([$backup_name, $user_id, $include_periods ? 1 : 0]);
        
        $backup_id = $conn->lastInsertId();
        
        // Backup timetable metadata
        $timetables_sql = "
            INSERT INTO timetable_backup_data (backup_id, table_name, record_id, data)
            SELECT ?, 'timetables', id, JSON_OBJECT(
                'id', id,
                'class_id', class_id,
                'section_id', section_id,
                'academic_year', academic_year,
                'term', term,
                'status', status,
                'created_by', created_by,
                'created_at', created_at,
                'updated_at', updated_at
            )
            FROM timetables
        ";
        $stmt = $conn->prepare($timetables_sql);
        $stmt->execute([$backup_id]);
        
        if ($include_periods) {
            // Backup timetable periods
            $periods_sql = "
                INSERT INTO timetable_backup_data (backup_id, table_name, record_id, data)
                SELECT ?, 'timetable_periods', tp.id, JSON_OBJECT(
                    'id', tp.id,
                    'timetable_id', tp.timetable_id,
                    'day_of_week', tp.day_of_week,
                    'period_number', tp.period_number,
                    'subject_id', tp.subject_id,
                    'teacher_id', tp.teacher_id,
                    'room', tp.room,
                    'notes', tp.notes
                )
                FROM timetable_periods tp
                JOIN timetables t ON tp.timetable_id = t.id
            ";
            $stmt = $conn->prepare($periods_sql);
            $stmt->execute([$backup_id]);
        }
        
        // Update backup status
        $update_sql = "UPDATE timetable_backups SET status = 'completed' WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->execute([$backup_id]);
        
        $conn->commit();
        
        // Log activity
        logActivity($user_id, 'timetable_backup', 'Created timetable backup: ' . $backup_name);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup created successfully',
            'backup_id' => $backup_id
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * List all available backups
 */
function handleListBackups() {
    global $conn;
    
    $sql = "
        SELECT 
            tb.id,
            tb.name,
            tb.created_by,
            u.full_name as created_by_name,
            tb.created_at,
            tb.include_periods,
            tb.status,
            COUNT(tbd.id) as record_count
        FROM timetable_backups tb
        LEFT JOIN users u ON tb.created_by = u.id
        LEFT JOIN timetable_backup_data tbd ON tb.id = tbd.backup_id
        WHERE tb.status = 'completed'
        GROUP BY tb.id
        ORDER BY tb.created_at DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $backups = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $backups
    ]);
}

/**
 * Restore a backup
 */
function handleRestoreBackup() {
    global $conn, $user_id, $user_role;
    
    // Only admin can restore backups
    if ($user_role !== 'admin') {
        throw new Exception('Only administrators can restore backups');
    }
    
    $backup_id = (int)($_POST['backup_id'] ?? 0);
    $restore_mode = $_POST['restore_mode'] ?? 'replace'; // replace, merge
    
    if (!$backup_id) {
        throw new Exception('Backup ID is required');
    }
    
    $conn->beginTransaction();
    
    try {
        // Get backup info
        $backup_sql = "SELECT * FROM timetable_backups WHERE id = ? AND status = 'completed'";
        $stmt = $conn->prepare($backup_sql);
        $stmt->execute([$backup_id]);
        $backup = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$backup) {
            throw new Exception('Backup not found or incomplete');
        }
        
        if ($restore_mode === 'replace') {
            // Clear existing timetables
            $conn->exec("DELETE FROM timetable_periods");
            $conn->exec("DELETE FROM timetables");
        }
        
        // Restore timetables
        $timetables_sql = "
            SELECT data FROM timetable_backup_data 
            WHERE backup_id = ? AND table_name = 'timetables'
        ";
        $stmt = $conn->prepare($timetables_sql);
        $stmt->execute([$backup_id]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $data = json_decode($row['data'], true);
            
            $insert_sql = "
                INSERT INTO timetables (class_id, section_id, academic_year, term, status, created_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    status = VALUES(status),
                    updated_at = NOW()
            ";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->execute([
                $data['class_id'],
                $data['section_id'],
                $data['academic_year'],
                $data['term'],
                $data['status'],
                $data['created_by'],
                $data['created_at']
            ]);
        }
        
        if ($backup['include_periods']) {
            // Restore periods
            $periods_sql = "
                SELECT data FROM timetable_backup_data 
                WHERE backup_id = ? AND table_name = 'timetable_periods'
            ";
            $stmt = $conn->prepare($periods_sql);
            $stmt->execute([$backup_id]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data = json_decode($row['data'], true);
                
                // Find the restored timetable ID
                $find_timetable_sql = "
                    SELECT id FROM timetables 
                    WHERE class_id = ? AND section_id = ? AND academic_year = ? AND term = ?
                ";
                $find_stmt = $conn->prepare($find_timetable_sql);
                // We need to get the original timetable info first
                $orig_timetable_sql = "
                    SELECT data FROM timetable_backup_data 
                    WHERE backup_id = ? AND table_name = 'timetables' AND record_id = ?
                ";
                $orig_stmt = $conn->prepare($orig_timetable_sql);
                $orig_stmt->execute([$backup_id, $data['timetable_id']]);
                $orig_timetable = json_decode($orig_stmt->fetch(PDO::FETCH_ASSOC)['data'], true);
                
                $find_stmt->execute([
                    $orig_timetable['class_id'],
                    $orig_timetable['section_id'],
                    $orig_timetable['academic_year'],
                    $orig_timetable['term']
                ]);
                $new_timetable_id = $find_stmt->fetchColumn();
                
                if ($new_timetable_id) {
                    $insert_sql = "
                        INSERT INTO timetable_periods (timetable_id, day_of_week, period_number, subject_id, teacher_id, room, notes)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                            subject_id = VALUES(subject_id),
                            teacher_id = VALUES(teacher_id),
                            room = VALUES(room),
                            notes = VALUES(notes)
                    ";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->execute([
                        $new_timetable_id,
                        $data['day_of_week'],
                        $data['period_number'],
                        $data['subject_id'],
                        $data['teacher_id'],
                        $data['room'],
                        $data['notes']
                    ]);
                }
            }
        }
        
        $conn->commit();
        
        // Log activity
        logActivity($user_id, 'timetable_restore', 'Restored timetable backup: ' . $backup['name']);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup restored successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Delete a backup
 */
function handleDeleteBackup() {
    global $conn, $user_id, $user_role;
    
    // Only admin can delete backups
    if ($user_role !== 'admin') {
        throw new Exception('Only administrators can delete backups');
    }
    
    $backup_id = (int)($_POST['backup_id'] ?? 0);
    
    if (!$backup_id) {
        throw new Exception('Backup ID is required');
    }
    
    $conn->beginTransaction();
    
    try {
        // Get backup name for logging
        $name_sql = "SELECT name FROM timetable_backups WHERE id = ?";
        $stmt = $conn->prepare($name_sql);
        $stmt->execute([$backup_id]);
        $backup_name = $stmt->fetchColumn();
        
        // Delete backup data
        $delete_data_sql = "DELETE FROM timetable_backup_data WHERE backup_id = ?";
        $stmt = $conn->prepare($delete_data_sql);
        $stmt->execute([$backup_id]);
        
        // Delete backup record
        $delete_backup_sql = "DELETE FROM timetable_backups WHERE id = ?";
        $stmt = $conn->prepare($delete_backup_sql);
        $stmt->execute([$backup_id]);
        
        $conn->commit();
        
        // Log activity
        logActivity($user_id, 'timetable_backup_delete', 'Deleted timetable backup: ' . $backup_name);
        
        echo json_encode([
            'success' => true,
            'message' => 'Backup deleted successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        throw $e;
    }
}

/**
 * Download backup as JSON file
 */
function handleDownloadBackup() {
    global $conn;
    
    $backup_id = (int)($_GET['backup_id'] ?? 0);
    
    if (!$backup_id) {
        throw new Exception('Backup ID is required');
    }
    
    // Get backup info
    $backup_sql = "SELECT * FROM timetable_backups WHERE id = ? AND status = 'completed'";
    $stmt = $conn->prepare($backup_sql);
    $stmt->execute([$backup_id]);
    $backup = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$backup) {
        throw new Exception('Backup not found');
    }
    
    // Get backup data
    $data_sql = "SELECT table_name, record_id, data FROM timetable_backup_data WHERE backup_id = ?";
    $stmt = $conn->prepare($data_sql);
    $stmt->execute([$backup_id]);
    $backup_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $export_data = [
        'backup_info' => $backup,
        'data' => $backup_data,
        'exported_at' => date('Y-m-d H:i:s')
    ];
    
    $filename = 'timetable_backup_' . $backup['name'] . '_' . date('Y-m-d') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen(json_encode($export_data, JSON_PRETTY_PRINT)));
    
    echo json_encode($export_data, JSON_PRETTY_PRINT);
    exit;
}
?>
