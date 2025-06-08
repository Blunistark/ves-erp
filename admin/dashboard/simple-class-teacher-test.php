<?php
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Class Teacher Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .form-group { margin: 10px 0; }
        label { display: block; margin-bottom: 5px; }
        select, button { padding: 8px; margin: 5px 0; }
        .result { margin: 20px 0; padding: 10px; border: 1px solid #ddd; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>Simple Class Teacher Assignment Test</h1>
    
    <form id="testForm">
        <div class="form-group">
            <label>Teacher:</label>
            <select name="teacher" id="teacher" required>
                <option value="">Select Teacher</option>
                <option value="522">John Doe (ID: 522)</option>
                <option value="523">Jane Smith (ID: 523)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Class:</label>
            <select name="class" id="class" required>
                <option value="">Select Class</option>
                <option value="3">Class II (ID: 3)</option>
                <option value="4">Class III (ID: 4)</option>
                <option value="5">Class IV (ID: 5)</option>
            </select>
        </div>
        
        <div class="form-group">
            <label>Section:</label>
            <select name="section" id="section" required>
                <option value="">Select Section</option>
                <option value="3">Section A (ID: 3)</option>
                <option value="4">Section A (ID: 4)</option>
                <option value="5">Section A (ID: 5)</option>
            </select>
        </div>
        
        <button type="submit">Assign Class Teacher</button>
    </form>
    
    <div id="result"></div>
    
    <h2>Debug Information</h2>
    <div id="debug"></div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('result');
            const debugDiv = document.getElementById('debug');
            
            // Show what we're sending
            debugDiv.innerHTML = '<h3>Sending:</h3><pre>' + 
                'teacher: ' + formData.get('teacher') + '\n' +
                'class: ' + formData.get('class') + '\n' +
                'section: ' + formData.get('section') + '</pre>';
            
            try {
                const response = await fetch('teachersassign_class_action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                debugDiv.innerHTML += '<h3>Raw Response:</h3><pre>' + responseText + '</pre>';
                
                try {
                    const data = JSON.parse(responseText);
                    
                    if (data.status === 'success') {
                        resultDiv.innerHTML = '<div class="result success">✓ ' + data.message + '</div>';
                    } else {
                        resultDiv.innerHTML = '<div class="result error">✗ ' + data.message + '</div>';
                        if (data.debug) {
                            debugDiv.innerHTML += '<h3>Debug Data:</h3><pre>' + JSON.stringify(data.debug, null, 2) + '</pre>';
                        }
                    }
                } catch (parseError) {
                    resultDiv.innerHTML = '<div class="result error">✗ Invalid JSON response</div>';
                    debugDiv.innerHTML += '<h3>Parse Error:</h3><pre>' + parseError.message + '</pre>';
                }
            } catch (error) {
                resultDiv.innerHTML = '<div class="result error">✗ Network Error: ' + error.message + '</div>';
            }
        });
    </script>
</body>
</html> 