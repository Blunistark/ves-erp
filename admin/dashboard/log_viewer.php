<?php
// This is a simple log viewer to help diagnose issues

// Security check - only allow local access
$remoteAddr = $_SERVER['REMOTE_ADDR'];
if ($remoteAddr !== '127.0.0.1' && $remoteAddr !== '::1' && $remoteAddr !== 'localhost') {
    die("This tool is only available for local development");
}

// Define the log files we want to check
$logFiles = [
    'academic_year_debug.log',
    'academic_years_debug.log',
    'db_connection_error.log',
    'student_debug.log'
];

// Function to get the last n lines of a file
function tail($filename, $lines = 50) {
    if (!file_exists($filename)) {
        return ["File does not exist: $filename"];
    }
    
    $file = file($filename);
    if (count($file) <= $lines) {
        return $file;
    } else {
        return array_slice($file, -$lines);
    }
}

// Clear a log file if requested
if (isset($_GET['clear']) && in_array($_GET['clear'], $logFiles)) {
    $filename = __DIR__ . '/' . $_GET['clear'];
    if (file_exists($filename)) {
        file_put_contents($filename, '');
        $message = "Log file {$_GET['clear']} has been cleared";
    } else {
        $message = "Log file {$_GET['clear']} does not exist";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Viewer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .log-container {
            margin-bottom: 30px;
        }
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .log-title {
            font-size: 1.2em;
            font-weight: bold;
        }
        .log-content {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            overflow-x: auto;
            white-space: pre-wrap;
            font-family: monospace;
            max-height: 400px;
            overflow-y: auto;
        }
        .clear-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .refresh-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Log Viewer</h1>
    
    <a href="import_student.php" class="back-link">← Back to Import Students</a>
    
    <?php if (isset($message)): ?>
    <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form method="get">
        <button type="submit" class="refresh-btn">Refresh Logs</button>
    </form>
    
    <?php foreach ($logFiles as $logFile): ?>
    <div class="log-container">
        <div class="log-header">
            <div class="log-title"><?php echo $logFile; ?></div>
            <form method="get">
                <input type="hidden" name="clear" value="<?php echo $logFile; ?>">
                <button type="submit" class="clear-btn">Clear Log</button>
            </form>
        </div>
        <div class="log-content">
<?php
$logPath = __DIR__ . '/' . $logFile;
$logLines = tail($logPath);
foreach ($logLines as $line) {
    echo htmlspecialchars($line);
}
if (count($logLines) === 0) {
    echo "Log file is empty or does not exist.";
}
?>
        </div>
    </div>
    <?php endforeach; ?>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html> 