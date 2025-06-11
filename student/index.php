<?php
// Include the variables file and functions
include '../setup.php';
include '../includes/functions.php';

// Start secure session
startSecureSession();

// Check if already logged in
if (isLoggedIn() && hasRole('student')) {
    header("Location: dashboard/index.php");
    exit;
}

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid form submission. Please try again.";
    } else {
        $sats_number = sanitizeInput($_POST['sats_number'] ?? '');
        $dob = sanitizeInput($_POST['dob'] ?? '');
        
        // Convert date from DD/MM/YYYY to YYYY-MM-DD format
        if ($dob && preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $dob, $matches)) {
            $dob = $matches[3] . '-' . $matches[2] . '-' . $matches[1]; // Convert to YYYY-MM-DD
        }
        
        // Authenticate student using SATS number and DOB
        $student = authenticateStudent($sats_number, $dob);
        
        if ($student) {
            // Set session variables
            $_SESSION['user_id'] = $student['id'];
            $_SESSION['email'] = $student['email'];
            $_SESSION['full_name'] = $student['full_name'];
            $_SESSION['role'] = $student['role'];
            $_SESSION['student_state_code'] = $student['student_state_code'];
            $_SESSION['class_id'] = $student['class_id'];
            $_SESSION['section_id'] = $student['section_id'];
            
            // Redirect to dashboard
            header("Location: dashboard/index.php");
            exit;
        } else {
            $error = "Invalid SATS number or date of birth. Please try again.";
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
    <title>Student Login - Vinodh English School</title>
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
            padding: 1rem;
            overflow-x: hidden;
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
            z-index: 2;
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
        }        .remember-forgot {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }        .remember-me {
            display: flex;
            align-items: center;
            cursor: pointer;
            color: #374151;
        }

        .remember-me input {
            margin-right: 0.5rem;
            width: auto;
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

        /* RESPONSIVE DESIGN - COMPLETELY REWRITTEN */
        /* Tablet Styles */
        @media (max-width: 1024px) {
            .main-container {
                padding: 1rem;
            }
            
            .logo-container {
                width: 45%;
                padding: 2rem;
            }
            
            .login-section {
                width: 55%;
                padding: 1.5rem;
            }
            
            .welcome-text {
                font-size: 2rem;
            }
            
            .logo {
                width: 180px;
                height: 180px;
            }
            
            .login-form {
                padding: 2.5rem;
            }
        }

        /* Mobile Landscape */
        @media (max-width: 768px) and (orientation: landscape) {
            body {
                padding: 0.5rem;
            }
            
            .main-container {
                flex-direction: row;
                min-height: 100vh;
                max-width: none;
            }
            
            .logo-container {
                position: relative;
                width: 40%;
                padding: 1rem;
                animation: none;
                opacity: 1;
            }
            
            .logo {
                width: 100px;
                height: 100px;
                margin-bottom: 1rem;
            }
            
            .welcome-text {
                font-size: 1.4rem;
                animation: none;
                opacity: 1;
            }
            
            .subtitle {
                font-size: 0.9rem;
                animation: none;
                opacity: 1;
            }
            
            .login-section {
                position: relative;
                width: 60%;
                padding: 1rem;
                animation: none;
                opacity: 1;
            }
            
            .login-form {
                padding: 1.5rem;
            }
            
            .form-title {
                font-size: 1.5rem;
            }
            
            .shape {
                display: none;
            }
        }

        /* Mobile Portrait - MAJOR FIXES */
        @media (max-width: 768px) and (orientation: portrait) {
            body {
                padding: 1rem;
                align-items: flex-start;
                min-height: 100vh;
            }

            .main-container {
                flex-direction: column;
                min-height: auto;
                width: 100%;
                max-width: 100%;
                align-items: stretch;
                justify-content: flex-start;
            }

            .logo-container {
                position: relative;
                width: 100%;
                padding: 2rem 1rem;
                margin-bottom: 2rem;
                animation: none;
                opacity: 1;
            }

            .logo {
                width: 120px;
                height: 120px;
                margin-bottom: 1rem;
            }

            .welcome-text {
                font-size: 1.8rem;
                animation: none;
                opacity: 1;
            }

            .subtitle {
                font-size: 1rem;
                animation: none;
                opacity: 1;
            }

            .login-section {
                position: relative;
                width: 100%;
                padding: 0 1rem;
                margin-top: 0;
                animation: none;
                opacity: 1;
            }

            .login-form {
                padding: 2rem;
                border-radius: 20px;
            }

            .form-title {
                font-size: 1.6rem;
                margin-bottom: 0.5rem;
            }

            .form-subtitle {
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
            }

            .input-group {
                margin-bottom: 1.25rem;
            }

            .input-group input {
                padding: 0.875rem 1rem;
                font-size: 1rem;
            }            .remember-forgot {
                margin-bottom: 1.5rem;
            }

            .login-btn, .back-btn {
                padding: 0.875rem;
                font-size: 1rem;
            }

            .copyright {
                font-size: 0.8rem;
                margin-top: 1.5rem;
            }

            .shape {
                display: none;
            }
        }

        /* Small Mobile */
        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }

            .logo-container {
                padding: 1.5rem 0.5rem;
            }

            .logo {
                width: 100px;
                height: 100px;
            }

            .welcome-text {
                font-size: 1.6rem;
            }

            .subtitle {
                font-size: 0.9rem;
            }

            .login-section {
                padding: 0 0.5rem;
            }

            .login-form {
                padding: 1.5rem;
            }

            .form-title {
                font-size: 1.4rem;
            }

            .form-subtitle {
                font-size: 0.85rem;
            }

            .input-group input {
                padding: 0.75rem;
                font-size: 0.95rem;
            }

            .remember-forgot {
                font-size: 0.85rem;
            }

            .login-btn, .back-btn {
                padding: 0.75rem;
                font-size: 0.95rem;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 320px) {
            .welcome-text {
                font-size: 1.4rem;
            }

            .logo {
                width: 80px;
                height: 80px;
            }

            .form-title {
                font-size: 1.3rem;
            }

            .login-form {
                padding: 1.25rem;
            }

            .input-group input {
                padding: 0.625rem;
                font-size: 0.9rem;
            }
        }

        /* High DPI Screens */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .logo img {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Accessibility and Motion Preferences */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
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
            <div class="welcome-text">Student Portal</div>
            <div class="subtitle">Vinodh English School</div>
        </div>

        <!-- Login form section -->
        <div class="login-section">
            <div class="login-form">
                <h1 class="form-title">Student Sign In</h1>
                <p class="form-subtitle">Enter your SATS number and date of birth</p>

                <?php if ($error): ?>
                    <div class="error-alert"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="input-group">
                        <label for="sats_number">SATS Number</label>
                        <input type="text" id="sats_number" name="sats_number" required 
                               placeholder="Enter your SATS number" 
                               pattern="[0-9]+" 
                               title="Please enter only numbers"
                               autocomplete="username">
                    </div>

                    <div class="input-group">
                        <label for="dob">Date of Birth</label>
                        <input type="text" id="dob" name="dob" required 
                               placeholder="DD/MM/YYYY"
                               pattern="^(0[1-9]|[12][0-9]|3[01])/(0[1-9]|1[012])/[0-9]{4}$"
                               title="Please enter date in DD/MM/YYYY format"
                               maxlength="10"
                               autocomplete="bday">
                    </div>                    <div class="remember-forgot">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            Remember Me
                        </label>
                    </div>

                    <button type="submit" class="login-btn">Sign In</button>
                    <button type="button" onclick="window.location.href='../'" class="back-btn">← Go Back</button>
                </form>
                
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

        // SATS number input validation
        document.getElementById('sats_number').addEventListener('input', function(e) {
            // Remove any non-numeric characters
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Date of birth input formatting
        document.getElementById('dob').addEventListener('input', function(e) {
            let value = this.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters
            
            // Add slashes at appropriate positions
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '/' + value.substring(5, 9);
            }
            
            this.value = value;
        });

        // Prevent calendar from opening on date input
        document.getElementById('dob').addEventListener('focus', function(e) {
            this.setAttribute('readonly', true);
            setTimeout(() => {
                this.removeAttribute('readonly');
            }, 100);
        });

        // Handle window resize for responsive behavior
        window.addEventListener('resize', function() {
            // On mobile, disable animations to prevent layout issues
            if (window.innerWidth <= 768) {
                document.querySelectorAll('.logo-container, .login-section').forEach(el => {
                    el.style.animation = 'none';
                    el.style.opacity = '1';
                });
            }
        });

        // Initialize responsive behavior on load
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 768) {
                document.querySelectorAll('.logo-container, .login-section').forEach(el => {
                    el.style.animation = 'none';
                    el.style.opacity = '1';
                });
            }
        });
    </script>
</body>
</html>