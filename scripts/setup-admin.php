<?php
// Web interface to create a new admin user

// Messages array
$messages = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include config file
    require_once __DIR__ . '/includes/config.php';
    
    // Function to connect to database
    function getDbConnection() {
        global $db_host, $db_user, $db_pass, $db_name;
        
        $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    }
    
    // Get form data
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $full_name = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $role = 'admin'; // Always admin in this script
    $status = 'active'; // Always active
    
    // Validate data
    if (empty($email) || empty($password) || empty($full_name)) {
        $messages[] = ['type' => 'error', 'text' => 'All fields are required.'];
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messages[] = ['type' => 'error', 'text' => 'Please enter a valid email address.'];
    } elseif (strlen($password) < 8) {
        $messages[] = ['type' => 'error', 'text' => 'Password must be at least 8 characters long.'];
    } else {
        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        try {
            // Connect to database
            $conn = getDbConnection();
            
            // Check if user already exists
            $check_user = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check_user->bind_param("s", $email);
            $check_user->execute();
            $result = $check_user->get_result();
            
            if ($result->num_rows > 0) {
                // User exists
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
                
                // Update the password
                $update_stmt = $conn->prepare("UPDATE users SET password_hash = ?, full_name = ?, updated_at = NOW() WHERE id = ?");
                $update_stmt->bind_param("ssi", $password_hash, $full_name, $user_id);
                
                if ($update_stmt->execute()) {
                    $messages[] = ['type' => 'success', 'text' => "Admin user updated successfully. User ID: $user_id"];
                } else {
                    $messages[] = ['type' => 'error', 'text' => "Error updating user: " . $conn->error];
                }
                
                $update_stmt->close();
            } else {
                // Insert new user
                $insert_stmt = $conn->prepare(
                    "INSERT INTO users (email, password_hash, full_name, role, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, NOW())"
                );
                $insert_stmt->bind_param("sssss", $email, $password_hash, $full_name, $role, $status);
                
                if ($insert_stmt->execute()) {
                    $new_id = $conn->insert_id;
                    $messages[] = ['type' => 'success', 'text' => "Admin user created successfully. User ID: $new_id"];
                } else {
                    $messages[] = ['type' => 'error', 'text' => "Error creating user: " . $conn->error];
                }
                
                $insert_stmt->close();
            }
            
            // Close connection
            $check_user->close();
            $conn->close();
            
        } catch (Exception $e) {
            $messages[] = ['type' => 'error', 'text' => "Database error: " . $e->getMessage()];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin User - School ERP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0069d9;
        }
        .note {
            background-color: #fff3cd;
            padding: 10px;
            border-radius: 4px;
            margin-top: 20px;
            border: 1px solid #ffeeba;
        }
    </style>
</head>
<body>
    <h1>Setup Admin User</h1>
    
    <?php if (!empty($messages)): ?>
        <?php foreach ($messages as $message): ?>
            <div class="message <?php echo $message['type']; ?>">
                <?php echo htmlspecialchars($message['text']); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? 'admin@example.com'); ?>">
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required minlength="8">
            <small>Minimum 8 characters</small>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name:</label>
            <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? 'Admin User'); ?>">
        </div>
        
        <button type="submit">Create Admin User</button>
    </form>
    
    <?php if (isset($messages) && !empty($messages) && $messages[0]['type'] === 'success'): ?>
        <div class="note">
            <strong>Next Steps:</strong>
            <ol>
                <li>You can now log in to the admin panel using the email and password you just created.</li>
                <li>Once logged in, you can create additional users and configure the system.</li>
                <li>For security reasons, consider removing this setup file (setup-admin.php) after successful user creation.</li>
            </ol>
            <p><a href="admin/index.php">Go to Admin Login</a></p>
        </div>
    <?php endif; ?>
</body>
</html> 