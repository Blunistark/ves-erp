<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Content</title>
    <link rel="stylesheet" href="css/sidebar.css">
    <style>
        .test-content {
            margin-left: 280px; /* Adjust based on sidebar width */
            padding: 20px;
            background-color: #f0f0f0;
            min-height: 100vh;
            border: 5px solid red; /* Very visible border for debugging */
        }
        
        h1 {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="sidebar-overlay"></div>
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="test-content">
        <h1>Test Content</h1>
        <p>This is a test to see if content appears correctly on the page.</p>
        <p>If you can see this text with a red border around it, the issue is with the CSS in the other pages.</p>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
    </script>
</body>
</html> 