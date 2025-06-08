<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug</title>
    <link rel="stylesheet" href="css/sidebar.css">
</head>
<body>
    <h1>Debug Page</h1>
    <div id="debug-output"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Output document structure
            var debugOutput = document.getElementById('debug-output');
            debugOutput.innerHTML = '<h2>Document Structure</h2>';
            
            // Show HTML structure
            var htmlStructure = document.documentElement.outerHTML
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            
            debugOutput.innerHTML += '<pre>' + htmlStructure + '</pre>';
            
            // Check if sidebar exists and is visible
            var sidebar = document.getElementById('sidebar');
            debugOutput.innerHTML += '<h2>Sidebar Status</h2>';
            if (sidebar) {
                debugOutput.innerHTML += '<p>Sidebar exists: YES</p>';
                debugOutput.innerHTML += '<p>Sidebar classes: ' + sidebar.className + '</p>';
                debugOutput.innerHTML += '<p>Sidebar visibility: ' + 
                    (window.getComputedStyle(sidebar).display !== 'none' ? 'Visible' : 'Hidden') + '</p>';
                debugOutput.innerHTML += '<p>Sidebar HTML: </p><pre>' + 
                    sidebar.outerHTML.replace(/</g, '&lt;').replace(/>/g, '&gt;') + '</pre>';
            } else {
                debugOutput.innerHTML += '<p>Sidebar exists: NO</p>';
            }
        });
    </script>
</body>
</html> 