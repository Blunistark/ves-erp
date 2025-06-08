<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// if (session_status() === PHP_SESSION_NONE) { // This line will be removed
//     session_start(); // This line will be removed
// }

require_once 'includes/config.php';
require_once 'includes/connection.php';
require_once 'includes/functions.php';

$error = '';
$success = '';

ob_start(); // Ensure headers can be sent later

if (isset($_SESSION['user_id'])) {
    header('Location: pages/dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    try {
        if (empty($username) || empty($password)) {
            throw new Exception('Please enter both username and password.');
        }

        $user = $db->fetchOne(
            'SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1',
            [$username, $username]
        );

        if (!$user || !password_verify($password, $user['password'])) {
            logActivity('Failed Login Attempt', "Username: $username, IP: " . getClientIP());
            throw new Exception('Invalid username or password.');
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['authenticated'] = true;

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
        }

        $db->query('UPDATE users SET updated_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $user['id']]);
        logActivity('Successful Login', "User: {$user['username']}, Role: {$user['role']}");

        session_write_close();
        echo "<script>window.location.href = 'pages/dashboard.php';</script>";
        exit;

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - School Admin System</title>
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

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #374151;
        }

        .remember-me input {
            margin-right: 0.5rem;
            width: auto;
        }

        .forgot-password {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #e64545;
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

        .back-btn {
            width: 100%;
            padding: 1rem;
            background: #F3F4F6;
            color: #374151;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: #E5E7EB;
            transform: translateY(-1px);
        }

        .error-alert {
            background: #FEF2F2;
            border: 1px solid #FECACA;
            color: #DC2626;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            animation: shake 0.5s ease-in-out;
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
                <img src="assets/images/school-logo.png" alt="School Logo">
            </div>
            <div class="welcome-text">Welcome Back</div>
            <div class="subtitle">Access your admin dashboard</div>
        </div>

        <!-- Login form section -->
        <div class="login-section">
            <div class="login-form">
                <h1 class="form-title">Sign In</h1>
                <p class="form-subtitle">Enter your credentials to continue</p>

                <?php if ($error): ?>
                    <div class="error-alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <div class="input-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" required autocomplete="current-password">
                            <button type="button" class="password-toggle" onclick="togglePassword()">
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

                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" <?php if (!empty($_POST['remember'])) echo 'checked'; ?> id="remember">
                            Remember Me
                        </label>
                        <a href="#" class="forgot-password">Forgot password?</a>
                    </div>

                    <button type="submit" class="login-btn">Sign In</button>
                    <button type="button" onclick="window.location.href='index.php'" class="back-btn">← Go Back</button>
                </form>
            </div>
        </div>
        
        <!-- Animated shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
        }

        // Add form submission animation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.login-btn');
            submitBtn.style.transform = 'scale(0.95)';
            submitBtn.innerHTML = 'Signing In...';
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
    </script>
</body>
</html>