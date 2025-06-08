<?php
session_start();
require_once '../../includes/config.php';

// Set admin session for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['full_name'] = 'Test Admin';

$conn = getDbConnection();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Teacher Assignment Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Class Teacher Assignment System Test</h1>
    
    <div class="test-section">
        <h2>1. Database Schema Check</h2>
        <?php
        // Check sections table structure
        $result = $conn->query("DESCRIBE sections");
        echo "<h3>Sections Table Structure:</h3>";
        echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr><td>{$row['Field']}</td><td>{$row['Type']}</td><td>{$row['Null']}</td><td>{$row['Key']}</td><td>{$row['Default']}</td></tr>";
        }
        echo "</table>";
        
        // Check if class_teacher_user_id exists
        $has_class_teacher = false;
        $result = $conn->query("DESCRIBE sections");
        while ($row = $result->fetch_assoc()) {
            if ($row['Field'] === 'class_teacher_user_id') {
                $has_class_teacher = true;
                break;
            }
        }
        echo $has_class_teacher ? "<p class='success'>✓ class_teacher_user_id field exists</p>" : "<p class='error'>✗ class_teacher_user_id field missing</p>";
        ?>
    </div>

    <div class="test-section">
        <h2>2. Available Teachers</h2>
        <?php
        $sql = "SELECT u.id, u.full_name, t.employee_number 
                FROM users u 
                JOIN teachers t ON u.id = t.user_id 
                WHERE u.role = 'teacher' AND u.status = 'active'
                ORDER BY u.full_name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<table><tr><th>ID</th><th>Name</th><th>Employee Number</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>{$row['id']}</td><td>{$row['full_name']}</td><td>{$row['employee_number']}</td></tr>";
            }
            echo "</table>";
            echo "<p class='success'>✓ Found {$result->num_rows} active teachers</p>";
        } else {
            echo "<p class='error'>✗ No active teachers found</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>3. Available Classes and Sections</h2>
        <?php
        $sql = "SELECT c.id as class_id, c.name as class_name, s.id as section_id, s.name as section_name,
                       u.full_name as current_teacher, COUNT(st.user_id) as student_count
                FROM classes c
                JOIN sections s ON c.id = s.class_id
                LEFT JOIN users u ON s.class_teacher_user_id = u.id
                LEFT JOIN students st ON s.id = st.section_id
                GROUP BY c.id, c.name, s.id, s.name, u.full_name
                ORDER BY c.name, s.name";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            echo "<table><tr><th>Class</th><th>Section</th><th>Current Teacher</th><th>Students</th></tr>";
            while ($row = $result->fetch_assoc()) {
                $teacher = $row['current_teacher'] ?: 'Not assigned';
                echo "<tr><td>{$row['class_name']}</td><td>{$row['section_name']}</td><td>{$teacher}</td><td>{$row['student_count']}</td></tr>";
            }
            echo "</table>";
            echo "<p class='success'>✓ Found {$result->num_rows} class sections</p>";
        } else {
            echo "<p class='error'>✗ No class sections found</p>";
        }
        ?>
    </div>

    <div class="test-section">
        <h2>4. API Endpoint Tests</h2>
        <div id="api-tests">
            <p class="info">Testing API endpoints...</p>
        </div>
    </div>

    <div class="test-section">
        <h2>5. Test Assignment Form</h2>
        <form id="testForm">
            <div style="margin: 10px 0;">
                <label>Teacher: </label>
                <select id="teacher" name="teacher" required>
                    <option value="">Select Teacher</option>
                </select>
            </div>
            <div style="margin: 10px 0;">
                <label>Class: </label>
                <select id="class" name="class" required>
                    <option value="">Select Class</option>
                </select>
            </div>
            <div style="margin: 10px 0;">
                <label>Section: </label>
                <select id="section" name="section" required>
                    <option value="">Select Section</option>
                </select>
            </div>
            <button type="submit">Assign Class Teacher</button>
        </form>
        <div id="form-result"></div>
    </div>

    <script>
        // Test API endpoints
        async function testAPIs() {
            const apiTests = document.getElementById('api-tests');
            
            try {
                // Test teachers endpoint
                const teachersResponse = await fetch('teachersassign_class_action.php?type=teachers');
                const teachersData = await teachersResponse.json();
                
                if (teachersData.status === 'success') {
                    apiTests.innerHTML += '<p class="success">✓ Teachers API working</p>';
                    populateSelect('teacher', teachersData.data);
                } else {
                    apiTests.innerHTML += '<p class="error">✗ Teachers API failed: ' + teachersData.message + '</p>';
                }

                // Test classes endpoint
                const classesResponse = await fetch('teachersassign_class_action.php?type=classes');
                const classesData = await classesResponse.json();
                
                if (classesData.status === 'success') {
                    apiTests.innerHTML += '<p class="success">✓ Classes API working</p>';
                    populateSelect('class', classesData.data);
                } else {
                    apiTests.innerHTML += '<p class="error">✗ Classes API failed: ' + classesData.message + '</p>';
                }

                // Test assignments endpoint
                const assignmentsResponse = await fetch('teachersassign_class_action.php?type=assignments');
                const assignmentsData = await assignmentsResponse.json();
                
                if (assignmentsData.status === 'success') {
                    apiTests.innerHTML += '<p class="success">✓ Assignments API working</p>';
                } else {
                    apiTests.innerHTML += '<p class="error">✗ Assignments API failed: ' + assignmentsData.message + '</p>';
                }

            } catch (error) {
                apiTests.innerHTML += '<p class="error">✗ API test failed: ' + error.message + '</p>';
            }
        }

        function populateSelect(selectId, data) {
            const select = document.getElementById(selectId);
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });
        }

        // Handle class change to load sections
        document.getElementById('class').addEventListener('change', async function() {
            const classId = this.value;
            const sectionSelect = document.getElementById('section');
            
            sectionSelect.innerHTML = '<option value="">Select Section</option>';
            
            if (classId) {
                try {
                    const response = await fetch(`teachersassign_class_action.php?type=sections&class_id=${classId}`);
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        populateSelect('section', data.data);
                    }
                } catch (error) {
                    console.error('Error loading sections:', error);
                }
            }
        });

        // Handle form submission
        document.getElementById('testForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const resultDiv = document.getElementById('form-result');
            
            try {
                const response = await fetch('teachersassign_class_action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.status === 'success') {
                    resultDiv.innerHTML = '<p class="success">✓ ' + data.message + '</p>';
                    this.reset();
                } else {
                    resultDiv.innerHTML = '<p class="error">✗ ' + data.message + '</p>';
                    if (data.debug) {
                        resultDiv.innerHTML += '<pre>' + JSON.stringify(data.debug, null, 2) + '</pre>';
                    }
                }
            } catch (error) {
                resultDiv.innerHTML = '<p class="error">✗ Error: ' + error.message + '</p>';
            }
        });

        // Run tests when page loads
        testAPIs();
    </script>
</body>
</html> 