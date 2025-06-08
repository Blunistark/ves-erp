<?php
// Include the variables file and functions
include '../setup.php';
include '../includes/functions.php';

// Start secure session
startSecureSession();

// Check if already logged in
if (isLoggedIn()) {
    header("Location: dashboard/index.php");
    exit;
}

// Process reset password
$message = '';
$messageType = '';
$validToken = false;
$token = sanitizeInput($_GET['token'] ?? '');

// Validate token
if (!empty($token)) {
    // Check if token exists and is not expired
    $sql = "SELECT pr.user_id, pr.token, pr.expires_at, u.email, u.full_name 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? AND pr.used = 0 AND u.role = 'student' AND u.status = 'active'";
    $result = executeQuery($sql, "s", [$token]);
    
    if (!empty($result)) {
        $resetInfo = $result[0];
        
        // Check if token is expired
        if (strtotime($resetInfo['expires_at']) > time()) {
            $validToken = true;
        } else {
            $message = "This password reset link has expired. Please request a new one.";
            $messageType = "error";
        }
    } else {
        $message = "Invalid or expired reset link. Please request a new one.";
        $messageType = "error";
    }
} else {
    $message = "Invalid request. Please use the reset link sent to your email.";
    $messageType = "error";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $message = "Invalid form submission. Please try again.";
        $messageType = "error";
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate password
        if (strlen($password) < 8) {
            $message = "Password must be at least 8 characters long.";
            $messageType = "error";
        } elseif ($password !== $confirmPassword) {
            $message = "Passwords do not match.";
            $messageType = "error";
        } else {
            // Hash new password
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user password
            $sql = "UPDATE users SET password_hash = ? WHERE id = ?";
            $updateResult = executeQuery($sql, "si", [$passwordHash, $resetInfo['user_id']]);
            
            if ($updateResult !== false) {
                // Mark token as used
                $sql = "UPDATE password_resets SET used = 1, used_at = CURRENT_TIMESTAMP WHERE token = ?";
                executeQuery($sql, "s", [$token]);
                
                $message = "Password has been reset successfully. You can now <a href='index.php'>login</a> with your new password.";
                $messageType = "success";
                $validToken = false; // Hide the form
            } else {
                $message = "An error occurred. Please try again.";
                $messageType = "error";
            }
        }
    }
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Vinodh English School</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <style>
        /* Root variables for consistent colors */
        :root {
            --primary: #fd5d5d; /* Bright red */
            --secondary: #5856d6; /* Purple */
            --accent: #26e7a6; /* Green */
            --background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        /* Base styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        }
        
        input, textarea, button, select, a {
            -webkit-tap-highlight-color: rgba(0, 0, 0, 0);
        }
        
        a:focus, a:visited, a:active {
            outline: none;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 2rem;
            overflow: hidden;
        }

        .main-container {
            width: 100%;
            max-width: 1200px;
            min-height: 80vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Logo container */
        .logo-container {
            position: absolute;
            left: 0;
            width: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            z-index: 1;
            padding: 2.5rem;
            opacity: 0;
            animation: slideInLeft 1s ease-out 0.5s forwards;
        }

        .logo {
            width: 200px;
            height: 200px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 1.5rem;
            animation: floatAnimation 3s ease-in-out infinite, 
                      pulseAnimation 5s ease-in-out infinite;
        }
        
        .logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .welcome-text {
            font-size: 2.2rem;
            color: #18181B;
            text-align: center;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-family: 'Montserrat', sans-serif;
            opacity: 0;
            animation: fadeInUp 1s ease-out 1s forwards;
        }

        .subtitle {
            font-size: 1.1rem;
            color: #71717A;
            text-align: center;
            opacity: 0;
            animation: fadeInUp 1s ease-out 1.2s forwards;
        }

        /* Login form section */
        .login-section {
            position: absolute;
            right: 0;
            width: 50%;
            padding: 2rem;
            opacity: 0;
            animation: slideInRight 1s ease-out 0.8s forwards;
        }

        .login-form {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid #E4E4E7;
            position: relative;
            overflow: hidden;
        }

        .login-form::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        }

        .form-title {
            font-size: 1.8rem;
            color: #18181B;
            margin-bottom: 0.5rem;
            font-weight: 600;
            text-align: center;
        }

        .form-subtitle {
            color: #71717A;
            text-align: center;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .back-button {
            margin-bottom: 1.5rem;
        }

        .back-button a {
            display: flex;
            align-items: center;
            color: #71717A;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .back-button a:hover {
            color: var(--primary);
        }

        .back-button svg {
            width: 16px;
            height: 16px;
            margin-right: 0.5rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .input-group input {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #F9FAFB;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(253, 93, 93, 0.1);
            transform: translateY(-2px);
        }

        .password-input-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9CA3AF;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary);
        }

        .password-toggle svg {
            width: 20px;
            height: 20px;
        }

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            border-radius: 2px;
            background: #E5E7EB;
            transition: all 0.3s ease;
        }

        .password-strength-weak {
            background: linear-gradient(90deg, #ef5350 30%, #E5E7EB 30%);
        }

        .password-strength-medium {
            background: linear-gradient(90deg, #ffb74d 60%, #E5E7EB 60%);
        }

        .password-strength-strong {
            background: linear-gradient(90deg, #66bb6a 100%, #E5E7EB 100%);
        }

        .password-feedback {
            font-size: 0.8rem;
            margin-top: 0.5rem;
            color: #71717A;
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, var(--primary), #e64545);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(253, 93, 93, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .login-btn::after {
            content: '';
            position: absolute;
            width: 30px;
            height: 100%;
            top: 0;
            left: -100px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: 0.5s;
            animation: shine 3s infinite;
        }

        .message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .error {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #DC2626;
            animation: shake 0.5s ease-in-out;
        }

        .success {
            background: #F0FDF4;
            border: 1px solid #BBF7D0;
            color: #166534;
        }

        .success a {
            color: #166534;
            font-weight: 600;
            text-decoration: underline;
        }

        .copyright {
            text-align: center;
            margin-top: 2rem;
            color: #71717A;
            font-size: 0.85rem;
        }

        /* Background shapes */
        .shape {
            position: absolute;
            z-index: -1;
            opacity: 0.7;
            animation: floatAnimation 4s ease-in-out infinite;
        }

        .shape-1 {
            top: 10%;
            right: 10%;
            width: 100px;
            height: 100px;
            background: var(--accent);
            border-radius: 50%;
            animation-delay: 0s;
        }

        .shape-2 {
            bottom: 15%;
            left: 5%;
            width: 150px;
            height: 150px;
            background: var(--secondary);
            clip-path: polygon(50% 0%, 100% 50%, 50% 100%, 0% 50%);
            transform: rotate(15deg);
            animation-delay: 1s;
        }

        .shape-3 {
            top: 20%;
            left: 15%;
            width: 60px;
            height: 60px;
            background: var(--primary);
            clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
            animation-delay: 0.5s;
        }
        
        .shape-4 {
            bottom: 25%;
            right: 15%;
            width: 120px;
            height: 120px;
            background: rgba(253, 93, 93, 0.2);
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: morphAnimation 10s ease-in-out infinite alternate;
        }

        /* Animations */
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes floatAnimation {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-15px) rotate(5deg);
            }
        }

        @keyframes pulseAnimation {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        @keyframes morphAnimation {
            0% {
                border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            }
            25% {
                border-radius: 58% 42% 75% 25% / 76% 46% 54% 24%;
            }
            50% {
                border-radius: 50% 50% 33% 67% / 55% 27% 73% 45%;
            }
            75% {
                border-radius: 33% 67% 58% 42% / 63% 68% 32% 37%;
            }
            100% {
                border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            }
        }

        @keyframes shine {
            0% {
                left: -100px;
            }
            20% {
                left: 100%;
            }
            100% {
                left: 100%;
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-5px);
            }
            75% {
                transform: translateX(5px);
            }
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .main-container {
                flex-direction: column;
            }

            .logo-container {
                position: relative;
                width: 100%;
                margin-bottom: 2rem;
            }

            .login-section {
                position: relative;
                width: 100%;
            }

            .logo {
                width: 120px;
                height: 120px;
            }

            .welcome-text {
                font-size: 1.8rem;
            }

            .login-form {
                padding: 2rem;
            }
            
            .shape {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Logo section -->
        <div class="logo-container">
            <div class="logo">
                <img src="../assets/images/school-logo.png" alt="School Logo">
            </div>
            <div class="welcome-text">Set New Password</div>
            <div class="subtitle">Student Portal</div>
        </div>

        <!-- Login form section -->
        <div class="login-section">
            <div class="login-form">
                <div class="back-button">
                    <a href="index.php">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back to Login
                    </a>
                </div>
                
                <h1 class="form-title">Set New Password</h1>
                <p class="form-subtitle">Create a new secure password for your account</p>
                
                <?php if (!empty($message)): ?>
                <div class="message <?php echo $messageType; ?>">
                    <?php echo $message; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($validToken): ?>
                <form id="resetPasswordForm" method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?token=' . $token); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="input-group">
                        <label for="password">New Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" placeholder="Enter new password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <div class="password-feedback" id="passwordFeedback"></div>
                    </div>
                    
                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="login-btn">Reset Password</button>
                </form>
                <?php endif; ?>
                
                <div class="copyright">
                    &copy; 2025 Vinodh English School & Digitar App LLP
                </div>
            </div>
        </div>
        
        <!-- Animated shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }

        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            const feedback = document.getElementById('passwordFeedback');
            
            let strength = 0;
            let feedbackText = '';
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            strengthBar.className = 'password-strength';
            
            if (strength < 3) {
                strengthBar.classList.add('password-strength-weak');
                feedbackText = 'Weak password';
            } else if (strength < 5) {
                strengthBar.classList.add('password-strength-medium');
                feedbackText = 'Medium strength';
            } else {
                strengthBar.classList.add('password-strength-strong');
                feedbackText = 'Strong password';
            }
            
            feedback.textContent = feedbackText;
        }

        // Add password strength checking
        document.getElementById('password')?.addEventListener('input', function() {
            checkPasswordStrength(this.value);
        });

        // Focus animation for inputs
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });

        // Form submission animation
        document.getElementById('resetPasswordForm')?.addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.login-btn');
            submitBtn.style.transform = 'scale(0.95)';
            submitBtn.innerHTML = 'Resetting...';
        });
    </script>
</body>
</html> 