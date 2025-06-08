<?php
// Include the variables file
include 'setup.php';

// Handle VES Reception login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ves_login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Check if it's the VES receptionist email
    if ($email === 'ves-receptionist@gmail.com') {
        // Create a form to auto-submit to VES reception login
        echo '<!DOCTYPE html>
        <html>
        <head><title>Redirecting...</title></head>
        <body>
        <form id="vesForm" method="POST" action="ves-reception/login.php">
            <input type="hidden" name="username" value="' . htmlspecialchars($email) . '">
            <input type="hidden" name="password" value="' . htmlspecialchars($password) . '">
        </form>
        <script>
            document.getElementById("vesForm").submit();
        </script>
        </body>
        </html>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $companyname; ?></title>
    
    <!-- PWA Manifest and Meta Tags -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#2563eb">
    <meta name="background-color" content="#ffffff">
    <meta name="display" content="standalone">
    <meta name="orientation" content="portrait-primary">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="VES ERP">
    <meta name="msapplication-TileColor" content="#2563eb">
    <meta name="msapplication-tap-highlight" content="no">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" sizes="152x152" href="assets/images/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/images/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/images/icon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/images/icon-16x16.png">
    
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
    
    <!-- STYLE TAG PLACEHOLDER - TO BE REPLACED WITH BATCH 2 -->
 <style>
        /* Root variables for consistent colors */
        :root {
            --primary: #fd5d5d; /* Bright red from Get Started button */
            --secondary: #5856d6; /* Purple from notification */
            --accent: #26e7a6; /* Green from background elements */
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

        /* Optional gradient background */
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

        /* Logo container that moves */
        .logo-container {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: all 1.5s ease-in-out;
            z-index: 1;
            padding: 2.5rem;
            width: 50%;
            left: 0;
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

        .slogan {
            font-size: 1.8rem;
            color: #18181B;
            text-align: center;
            font-weight: 700;
            opacity: 1;
            position: relative;
            white-space: nowrap;
            overflow: hidden;
            margin: 0 auto;
            letter-spacing: 0.05em;
            font-family: 'Montserrat', sans-serif;
        }

        .slogan span {
            display: inline-block;
            opacity: 0;
            transform: translateY(20px);
            filter: blur(10px);
            transition: opacity 0.6s, transform 0.6s, filter 0.6s;
        }

        .slogan span.visible {
            opacity: 1;
            transform: translateY(0);
            filter: blur(0);
        }

        .slogan-line {
            display: block;
            line-height: 1.2;
        }

        /* Options section */
        .options-section {
            position: absolute;
            right: 0;
            width: 50%;
            opacity: 0;
            visibility: hidden;
            transition: opacity 1s ease-in-out, visibility 1s ease-in-out;
            padding: 1rem;
            z-index: 2;
        }

        h1 {
            font-size: 1.75rem;
            color: #18181B;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .cards-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            width: 100%;
        }

        /* Enhanced cards styling */
        .login-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid #E4E4E7;
            width: 100%;
            display: flex;
            align-items: center;
            cursor: pointer;
            animation: fadeIn 0.5s ease-out forwards;
            animation-delay: calc(var(--index) * 0.2s);
            opacity: 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.05);
            margin-bottom: 1rem;
            text-decoration: none;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--primary);
            transform: scaleY(0);
            transform-origin: bottom;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
            transform: translateX(8px) scale(1.02);
            border-color: var(--primary);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        }

        .login-card:hover::before {
            transform: scaleY(1);
        }

        .login-card:focus {
            outline: 2px solid var(--primary);
            transform: translateX(5px);
        }

        .login-card.active {
            transform: scale(1.03);
            border-color: var(--primary);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        /* Icon styling enhancements */
        .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: #F4F4F5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.25rem;
            transition: all 0.3s ease;
            flex-shrink: 0;
            transform: rotate(0deg);
            position: relative;
            overflow: hidden;
        }
        
        .icon-wrapper::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.3), rgba(255,255,255,0));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-card:hover .icon-wrapper {
            background: var(--primary);
            transform: rotate(-5deg) scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .login-card:hover .icon-wrapper::after {
            opacity: 1;
        }

        .icon-wrapper svg {
            width: 24px;
            height: 24px;
            color: var(--primary);
            transition: color 0.3s ease;
        }

        .login-card:hover .icon-wrapper svg {
            color: white;
        }

        .card-content {
            flex: 1;
        }

        .login-card h3 {
            font-size: 1rem;
            color: #18181B;
            margin-bottom: 0.25rem;
            text-align: left;
        }

        .login-card p {
            color: #71717A;
            font-size: 0.875rem;
            margin-bottom: 0;
            text-align: left;
        }

        /* Button styling enhancements */
        .action-btn {
            margin-top: 12px;
            padding: 8px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s ease;
            transform: translateY(10px);
            opacity: 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(253, 93, 93, 0.2);
        }

        .login-card:hover .action-btn {
            opacity: 1;
            transform: translateY(0);
        }

        .action-btn:hover {
            background: #e64545;
            box-shadow: 0 6px 16px rgba(253, 93, 93, 0.4);
            transform: translateY(-2px);
        }

        .action-btn::after {
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

        /* Success popup */
        .success-popup {
            position: fixed;
            bottom: -100px;
            right: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            padding: 16px;
            z-index: 10;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        }

        .success-popup.show {
            bottom: 30px;
            opacity: 1;
        }

        .success-icon {
            width: 40px;
            height: 40px;
            background: var(--accent);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
        }

        .success-icon svg {
            color: white;
            width: 24px;
            height: 24px;
        }

        .success-message h3 {
            margin: 0 0 4px 0;
            color: #18181B;
        }

        .success-message p {
            margin: 0;
            color: #71717A;
        }

        /* PWA Install Modal Styles */
        .install-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            padding: 1rem;
        }

        .install-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .install-modal {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            max-width: 420px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            transform: scale(0.9) translateY(20px);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .install-modal-overlay.show .install-modal {
            transform: scale(1) translateY(0);
        }

        .install-modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #f3f4f6;
            border: none;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #6b7280;
        }

        .install-modal-close:hover {
            background: #e5e7eb;
            color: #374151;
            transform: rotate(90deg);
        }

        .install-modal-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .install-modal-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary), #e64545);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px rgba(253, 93, 93, 0.3);
            animation: modalIconPulse 2s ease-in-out infinite;
        }

        .install-app-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .install-modal-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .install-modal-header p {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .install-modal-features {
            margin-bottom: 2rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-icon {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #f3f4f6, #e5e7eb);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .feature-item:hover .feature-icon {
            background: linear-gradient(135deg, var(--primary), #e64545);
            transform: scale(1.1);
        }

        .feature-icon svg {
            color: #6b7280;
            transition: color 0.3s ease;
        }

        .feature-item:hover .feature-icon svg {
            color: white;
        }

        .feature-text {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .feature-title {
            font-weight: 600;
            color: #1f2937;
            font-size: 0.9rem;
        }

        .feature-desc {
            color: #6b7280;
            font-size: 0.85rem;
            line-height: 1.4;
        }

        .install-modal-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .install-btn-primary {
            background: linear-gradient(135deg, var(--primary), #e64545);
            color: white;
            border: none;
            border-radius: 14px;
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 6px 20px rgba(253, 93, 93, 0.3);
            position: relative;
            overflow: hidden;
        }

        .install-btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .install-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(253, 93, 93, 0.4);
        }

        .install-btn-primary:hover::before {
            left: 100%;
        }

        .install-btn-secondary {
            background: transparent;
            color: #6b7280;
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            padding: 0.875rem 1.5rem;
            font-weight: 500;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .install-btn-secondary:hover {
            border-color: #d1d5db;
            color: #4b5563;
            background: #f9fafb;
        }

        /* Floating Install Button */
        .floating-install-btn {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: linear-gradient(135deg, var(--primary), #e64545);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 14px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(253, 93, 93, 0.4);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 100;
            display: none;
            align-items: center;
            gap: 0.5rem;
            opacity: 0;
            transform: translateY(100px) scale(0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .floating-install-btn.show {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .floating-install-btn:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: 0 12px 32px rgba(253, 93, 93, 0.5);
        }

        .floating-install-btn:active {
            transform: translateY(-2px) scale(1.02);
        }

        .floating-install-btn svg {
            animation: bounceY 2s ease-in-out infinite;
        }

        /* Install Success Notification */
        .install-success-notification {
            position: fixed;
            top: 24px;
            right: 24px;
            background: white;
            border-radius: 16px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
            border: 1px solid #e5e7eb;
            z-index: 1001;
            animation: slideInFromRight 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            max-width: 320px;
        }

        /* Background shapes with more animation */
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

        /* Animation classes */
        .show {
            opacity: 1;
        }

        .move-to-left {
            transform: translateX(-25%);
        }

        .show-options {
            opacity: 1;
            visibility: visible;
        }

        /* Quick Login Styles */
        .quick-login-section {
            background: white;
            border-radius: 16px;
            border: 1px solid #E4E4E7;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .quick-login-toggle {
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #F8F9FA;
            transition: background 0.3s ease;
            font-weight: 500;
            color: #374151;
        }

        .quick-login-toggle:hover {
            background: #E5E7EB;
        }

        .quick-login-toggle svg {
            transition: transform 0.3s ease;
        }

        .quick-login-toggle.active svg {
            transform: rotate(180deg);
        }

        .quick-login-form {
            padding: 1.5rem;
            background: white;
        }

        .quick-login-form .input-group {
            margin-bottom: 1rem;
        }

        .quick-login-form input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #E5E7EB;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .quick-login-form input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .quick-login-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .quick-login-btn:hover {
            background: #e64545;
        }

        /* COPYRIGHT SECTION */
        .signup-prompt {
            text-align: center;
            margin-top: 2rem;
            color: #71717A;
            font-size: 0.875rem;
        }

        /* Enhanced Animations */
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

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
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

        @keyframes modalIconPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }

        @keyframes bounceY {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-3px);
            }
        }

        @keyframes slideInFromRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
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
            }
            
            .options-section {
                width: 55%;
                padding: 0.5rem;
            }
            
            .move-to-left {
                transform: translateX(-20%);
            }
            
            .slogan {
                font-size: 1.6rem;
            }
            
            .logo {
                width: 180px;
                height: 180px;
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
            }
            
            .logo {
                width: 120px;
                height: 120px;
                margin-bottom: 1rem;
            }
            
            .slogan {
                font-size: 1.2rem;
                white-space: normal;
                line-height: 1.3;
            }
            
            .options-section {
                position: relative;
                width: 60%;
                padding: 1rem;
                margin-top: 0;
            }
            
            .move-to-left {
                transform: none;
            }
            
            h1 {
                font-size: 1.5rem;
                margin-bottom: 1rem;
            }
            
            .login-card {
                padding: 1rem;
                margin-bottom: 0.75rem;
            }
            
            .shape {
                display: none;
            }

            /* PWA Modal responsive for landscape */
            .floating-install-btn {
                bottom: 16px;
                right: 16px;
                padding: 10px 16px;
                font-size: 0.8rem;
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
            }

            .logo {
                width: 150px;
                height: 150px;
                margin-bottom: 1rem;
            }

            .slogan {
                font-size: 1.4rem;
                white-space: normal;
                line-height: 1.4;
                max-width: 100%;
            }

            .options-section {
                position: relative;
                width: 100%;
                padding: 0 1rem;
                margin-top: 0;
                opacity: 1;
                visibility: visible;
            }

            .move-to-left {
                transform: none;
            }

            h1 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
                text-align: center;
            }

            .cards-container {
                gap: 1rem;
            }

            .login-card {
                padding: 1.25rem;
                margin-bottom: 1rem;
                opacity: 1;
                transform: none;
                flex-direction: column;
                text-align: center;
            }

            .icon-wrapper {
                margin-right: 0;
                margin-bottom: 1rem;
                width: 64px;
                height: 64px;
            }

            .card-content {
                width: 100%;
            }

            .login-card h3 {
                font-size: 1.1rem;
                text-align: center;
                margin-bottom: 0.5rem;
            }

            .login-card p {
                text-align: center;
                font-size: 0.9rem;
            }

            .action-btn {
                opacity: 1;
                transform: none;
                margin-top: 1rem;
                width: 100%;
                padding: 12px 20px;
            }

            .quick-login-section {
                margin-top: 2rem;
                opacity: 1;
            }

            .signup-prompt {
                margin-top: 2rem;
                padding: 1rem;
                font-size: 0.8rem;
            }

            .shape {
                display: none;
            }

            .success-popup {
                right: 1rem;
                left: 1rem;
                width: auto;
            }

            /* PWA Mobile Portrait Styles */
            .install-modal-overlay {
                padding: 0.5rem;
            }
            
            .install-modal {
                padding: 1.5rem;
                border-radius: 20px;
                max-height: 95vh;
            }
            
            .install-modal-icon {
                width: 64px;
                height: 64px;
                margin-bottom: 1rem;
            }
            
            .install-app-logo {
                width: 36px;
                height: 36px;
            }
            
            .floating-install-btn {
                bottom: 16px;
                right: 16px;
                padding: 12px 16px;
                font-size: 0.85rem;
            }

            .install-success-notification {
                left: 1rem;
                right: 1rem;
                transform: none;
                max-width: none;
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
                width: 120px;
                height: 120px;
            }

            .slogan {
                font-size: 1.2rem;
                padding: 0 1rem;
            }

            .options-section {
                padding: 0 0.5rem;
            }

            h1 {
                font-size: 1.3rem;
                margin-bottom: 1rem;
            }

            .login-card {
                padding: 1rem;
            }

            .icon-wrapper {
                width: 56px;
                height: 56px;
            }

            .login-card h3 {
                font-size: 1rem;
            }

            .login-card p {
                font-size: 0.85rem;
            }

            .quick-login-toggle {
                padding: 0.75rem 1rem;
            }

            .quick-login-form {
                padding: 1rem;
            }

            /* PWA Small Mobile Styles */
            .floating-install-btn span {
                display: none;
            }

            .install-modal {
                padding: 1.25rem;
            }

            .install-modal-header h2 {
                font-size: 1.125rem;
            }

            .feature-title {
                font-size: 0.85rem;
            }

            .feature-desc {
                font-size: 0.8rem;
            }
        }

        /* Extra Small Mobile */
        @media (max-width: 320px) {
            .slogan {
                font-size: 1.1rem;
            }

            .logo {
                width: 100px;
                height: 100px;
            }

            h1 {
                font-size: 1.2rem;
            }

            .login-card {
                padding: 0.875rem;
            }

            .icon-wrapper {
                width: 48px;
                height: 48px;
            }

            .login-card h3 {
                font-size: 0.95rem;
            }

            .login-card p {
                font-size: 0.8rem;
            }

            .floating-install-btn {
                bottom: 12px;
                right: 12px;
                padding: 10px 14px;
                font-size: 0.8rem;
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
            *,
            *::before,
            *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Dark mode support for PWA elements */
        @media (prefers-color-scheme: dark) {
            .install-modal {
                background: #1f2937;
                color: white;
            }
            
            .install-modal-header h2 {
                color: white;
            }
            
            .install-modal-header p {
                color: #9ca3af;
            }
            
            .feature-title {
                color: white;
            }
            
            .feature-desc {
                color: #9ca3af;
            }
            
            .install-modal-close {
                background: #374151;
                color: #9ca3af;
            }
            
            .install-modal-close:hover {
                background: #4b5563;
                color: #f3f4f6;
            }
            
            .feature-item {
                border-bottom-color: #374151;
            }
            
            .install-success-notification {
                background: #1f2937;
                color: white;
                border-color: #374151;
            }
        }

        /* Touch device optimizations */
        @media (pointer: coarse) {
            .install-btn-primary,
            .install-btn-secondary {
                min-height: 48px;
            }
            
            .floating-install-btn {
                min-height: 48px;
                min-width: 48px;
            }
            
            .install-modal-close {
                min-height: 48px;
                min-width: 48px;
            }
        }

        /* Print styles - hide PWA elements */
        @media print {
            .install-modal-overlay,
            .floating-install-btn,
            .install-success-notification {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Logo section -->
        <div class="logo-container" id="logoContainer">
            <div class="logo">
                <img src="assets/images/school-logo.png" alt="Educational Platform Logo">
            </div>
            <div class="slogan" id="slogan">
                <div class="slogan-line">Your child future as</div>
                <div class="slogan-line">a new address</div>
            </div>
        </div>

        <!-- Options section -->
        <div class="options-section" id="optionsSection">
            <div class="cards-container">
                <a href="student/" class="login-card" id="studentCard" style="--index: 1" tabindex="0" role="button" aria-label="Login as Student">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3>Student & Parent</h3>
                        <p>Access your courses, assignments, and grades</p>
                        <button class="action-btn">Get Started</button>
                    </div>
                </a>

                <a href="teachers/" class="login-card" id="teacherCard" style="--index: 2" tabindex="0" role="button" aria-label="Login as Teacher">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3>Teacher</h3>
                        <p>Manage your classes, create assignments, and grade work</p>
                        <button class="action-btn">Get Started</button>
                    </div>
                </a>

                <a href="admin/" class="login-card" id="adminCard" style="--index: 3" tabindex="0" role="button" aria-label="Login as Admin">
                    <div class="icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div class="card-content">
                        <h3>Admin</h3>
                        <p>Manage users, configure system settings, and monitor activity</p>
                        <button class="action-btn">Get Started</button>
                    </div>
                </a>
            </div>

            <!-- VES Reception Quick Login -->
            <div class="quick-login-section" id="quickLoginSection" style="margin-top: 2rem; opacity: 0;">
                <div class="quick-login-toggle" onclick="toggleQuickLogin()">
                    <span>VES Reception Login</span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z"/>
                    </svg>
                </div>
                <div class="quick-login-form" id="quickLoginForm" style="display: none;">
                    <form method="POST" action="">
                        <input type="hidden" name="ves_login" value="1">
                        <div class="input-group">
                            <input type="email" name="email" placeholder="Enter email" required>
                        </div>
                        <div class="input-group">
                            <input type="password" name="password" placeholder="Enter password" required>
                        </div>
                        <button type="submit" class="quick-login-btn">Login to VES Reception</button>
                    </form>
                </div>
            </div>
            <p class="signup-prompt">
                &copy; 2025 <?php echo $name; ?> & Digitar App LLP
            </p>
        </div>
        
        <!-- Success notification -->
        <div class="success-popup" id="successPopup">
            <div class="success-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div class="success-message">
                <h3>Redirecting</h3>
                <p>Taking you to login page</p>
            </div>
        </div>

        <!-- PWA Install Modal (Only shows in browser mode) -->
        <div id="pwaInstallContainer" style="display: none;">
            <!-- Modal Overlay -->
            <div class="install-modal-overlay" id="installModalOverlay">
                <div class="install-modal">
                    <button class="install-modal-close" onclick="closeInstallModal()" aria-label="Close modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                    
                    <div class="install-modal-header">
                        <div class="install-modal-icon">
                            <img src="assets/images/school-logo.png" alt="VES ERP Logo" class="install-app-logo">
                        </div>
                        <h2>Install VES ERP App</h2>
                        <p>Get the best experience with our mobile app</p>
                    </div>
                    
                    <div class="install-modal-features">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <span class="feature-title">Lightning Fast</span>
                                <span class="feature-desc">Quick access without browser overhead</span>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <span class="feature-title">Push Notifications</span>
                                <span class="feature-desc">Never miss important updates</span>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                    <polyline points="3.27,6.96 12,12.01 20.73,6.96"/>
                                    <line x1="12" y1="22.08" x2="12" y2="12"/>
                                </svg>
                            </div>
                            <div class="feature-text">
                                <span class="feature-title">Works Offline</span>
                                <span class="feature-desc">Access your data without internet</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="install-modal-actions">
                        <button class="install-btn-primary" onclick="installAppFromModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7,10 12,15 17,10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Install VES App
                        </button>
                        <button class="install-btn-secondary" onclick="closeInstallModal()">
                            Maybe Later
                        </button>
                    </div>
                </div>
            </div>

            <!-- Floating Install Button -->
            <button class="floating-install-btn" id="floatingInstallBtn" onclick="showInstallModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7,10 12,15 17,10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Install VES App</span>
            </button>
        </div>
        
        <!-- Animated shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    
    <script>
        // PWA Install Variables
        let deferredPrompt = null;
        let isInPWAMode = false;
        let installModalShown = false;

        // Check if app is running in PWA mode
       // Check if app is running in PWA mode
        function checkPWAMode() {
            isInPWAMode = window.matchMedia('(display-mode: standalone)').matches || 
                          window.navigator.standalone === true ||
                          document.referrer.includes('android-app://');
            
            console.log('PWA Mode:', isInPWAMode);
            
            // Show/hide PWA install container based on mode
            const pwaContainer = document.getElementById('pwaInstallContainer');
            if (pwaContainer) {
                if (isInPWAMode) {
                    pwaContainer.style.display = 'none';
                } else {
                    pwaContainer.style.display = 'block';
                    // Always show in browser mode - force show floating button
                    setTimeout(() => {
                        showFloatingInstallBtn();
                        // Always show modal in Chrome
                        if (!installModalShown) {
                            showInstallModal();
                        }
                    }, 2000);
                }
            }
            
            return isInPWAMode;
        }

       // PWA Install Modal Functions
        function showInstallModal() {
            if (isInPWAMode) return;
            
            const modal = document.getElementById('installModalOverlay');
            modal.classList.add('show');
            installModalShown = true;
            
            // Add body scroll lock
            document.body.style.overflow = 'hidden';
            
            // Show modal even without deferredPrompt in Chrome
            if (!deferredPrompt) {
                console.log('Showing modal without install prompt - user can see app benefits');
            }
        }

        function closeInstallModal() {
            const modal = document.getElementById('installModalOverlay');
            modal.classList.remove('show');
            installModalShown = false;
            
            // Remove body scroll lock
            document.body.style.overflow = '';
            
            // Store that user dismissed the modal
            localStorage.setItem('installModalDismissed', Date.now().toString());
        }

        async function installAppFromModal() {
            if (!deferredPrompt) return;
            
            try {
                // Show the install prompt
                deferredPrompt.prompt();
                
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to the install prompt: ${outcome}`);
                
                if (outcome === 'accepted') {
                    showInstallSuccessMessage();
                }
                
                // Clear the deferredPrompt variable
                deferredPrompt = null;
                closeInstallModal();
                hideFloatingInstallBtn();
                
            } catch (error) {
                console.error('Install failed:', error);
            }
        }

       // Floating install button functions
        function showFloatingInstallBtn() {
            if (isInPWAMode) return;
            
            const btn = document.getElementById('floatingInstallBtn');
            if (btn) {
                // Always show the button in browser mode, even without deferredPrompt
                btn.style.display = 'flex';
                btn.classList.add('show');
                
                // If no deferredPrompt, clicking will just show modal
                if (!deferredPrompt) {
                    console.log('No install prompt available, button will show modal only');
                }
            }
        }

        function hideFloatingInstallBtn() {
            const btn = document.getElementById('floatingInstallBtn');
            if (btn) {
                btn.classList.remove('show');
                setTimeout(() => {
                    btn.style.display = 'none';
                }, 300);
            }
        }

        // Check if user previously dismissed modal (within last 7 days)
        function shouldShowInstallPrompt() {
            const dismissed = localStorage.getItem('installModalDismissed');
            if (!dismissed) return true;
            
            const dismissedTime = parseInt(dismissed);
            const weekInMs = 7 * 24 * 60 * 60 * 1000;
            const now = Date.now();
            
            return (now - dismissedTime) > weekInMs;
        }

        // Auto-show install modal after user interaction
        function scheduleInstallPrompt() {
            if (isInPWAMode || !deferredPrompt || !shouldShowInstallPrompt()) return;
            
            // Show modal after user has been active for 30 seconds
            setTimeout(() => {
                if (!installModalShown && deferredPrompt && !isInPWAMode) {
                    showInstallModal();
                }
            }, 3000);
        }

        function showInstallSuccessMessage() {
            const successMsg = document.createElement('div');
            successMsg.innerHTML = `
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 24px; height: 24px; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3">
                            <path d="M20 6L9 17l-5-5"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-weight: 600; margin-bottom: 2px;">VES App Installed!</div>
                        <div style="font-size: 13px; opacity: 0.8;">App is now available on your home screen</div>
                    </div>
                </div>
            `;
            successMsg.className = 'install-success-notification';
            document.body.appendChild(successMsg);
            
            // Remove after 5 seconds
            setTimeout(() => {
                successMsg.remove();
            }, 5000);
        }

        window.onload = function() {
            // Initialize PWA functionality first
            setTimeout(() => {
                checkPWAMode();
            }, 1000);

            // Get elements
            const logoContainer = document.getElementById('logoContainer');
            const slogan = document.getElementById('slogan');
            const optionsSection = document.getElementById('optionsSection');
            const cards = document.querySelectorAll('.login-card');
            const successPopup = document.getElementById('successPopup');
            
            // Check if we're on mobile
            const isMobile = window.innerWidth <= 768;
            
            if (isMobile) {
                // On mobile, show everything immediately without animations
                optionsSection.classList.add('show-options');
                cards.forEach(card => {
                    card.style.opacity = '1';
                });
                const quickLoginSection = document.getElementById('quickLoginSection');
                if (quickLoginSection) {
                    quickLoginSection.style.opacity = '1';
                }
                
                // Still do the slogan animation
                let sloganLines = ["Your child future as", "a new address"];
                slogan.textContent = "";
                
                sloganLines.forEach((line, lineIndex) => {
                    const lineDiv = document.createElement('div');
                    lineDiv.className = 'slogan-line';
                    Array.from(line).forEach(char => {
                        const span = document.createElement('span');
                        span.textContent = char === " " ? "\u00A0" : char;
                        lineDiv.appendChild(span);
                    });
                    slogan.appendChild(lineDiv);
                });
                
                const letters = slogan.querySelectorAll('span');
                function animateLetters(index) {
                    if (index < letters.length) {
                        letters[index].classList.add('visible');
                        setTimeout(() => animateLetters(index + 1), 40);
                    }
                }
                animateLetters(0);
            } else {
                // Desktop animation sequence
                let sloganLines = ["Your child future as", "a new address"];
                slogan.textContent = "";
                
                sloganLines.forEach((line, lineIndex) => {
                    const lineDiv = document.createElement('div');
                    lineDiv.className = 'slogan-line';
                    Array.from(line).forEach(char => {
                        const span = document.createElement('span');
                        span.textContent = char === " " ? "\u00A0" : char;
                        lineDiv.appendChild(span);
                    });
                    slogan.appendChild(lineDiv);
                });
                
                const letters = slogan.querySelectorAll('span');
                function animateLetters(index) {
                    if (index < letters.length) {
                        letters[index].classList.add('visible');
                        setTimeout(() => animateLetters(index + 1), 80);
                    } else {
                        setTimeout(() => {
                            logoContainer.classList.add('move-to-left');
                            
                            setTimeout(() => {
                                optionsSection.classList.add('show-options');
                                
                                cards.forEach(card => {
                                    const index = card.style.getPropertyValue('--index');
                                    setTimeout(() => {
                                        card.style.opacity = '1';
                                        card.classList.add('card-animated');
                                    }, index * 200);
                                });

                                setTimeout(() => {
                                    const quickLoginSection = document.getElementById('quickLoginSection');
                                    if (quickLoginSection) {
                                        quickLoginSection.style.opacity = '1';
                                    }
                                }, 1000);
                            }, 800);
                        }, 1000);
                    }
                }
                
                setTimeout(() => {
                    animateLetters(0);
                }, 1000);
            }

            // Add click behavior for cards
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    cards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    setTimeout(() => {
                        successPopup.classList.add('show');
                    }, 300);
                    
                    const href = this.getAttribute('href');
                    
                    setTimeout(() => {
                        window.location.href = href;
                    }, 1500);
                });
            });

            // Schedule install prompt after animations complete
            setTimeout(() => {
                scheduleInstallPrompt();
            }, 3000);
        };

        // Toggle quick login form
        function toggleQuickLogin() {
            const toggle = document.querySelector('.quick-login-toggle');
            const form = document.getElementById('quickLoginForm');
            
            toggle.classList.toggle('active');
            
            if (form.style.display === 'none' || form.style.display === '') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            // Reset animations on resize to prevent layout issues
            const logoContainer = document.getElementById('logoContainer');
            const optionsSection = document.getElementById('optionsSection');
            
            if (window.innerWidth <= 768) {
                logoContainer.classList.remove('move-to-left');
                optionsSection.classList.add('show-options');
            }
        });

        // PWA Install Prompt Handler
       // PWA Install Prompt Handler
        window.addEventListener('beforeinstallprompt', (e) => {
            console.log('beforeinstallprompt event fired');
            
            // Only handle if not in PWA mode
            if (checkPWAMode()) {
                console.log('Already in PWA mode, ignoring install prompt');
                return;
            }
            
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            
            // Store the event for later use
            deferredPrompt = e;
            
            // Always show install UI immediately in Chrome
            showFloatingInstallBtn();
            setTimeout(() => {
                if (!installModalShown) {
                    showInstallModal();
                }
            }, 1000);
            
            console.log('Install prompt available - showing UI immediately');
        });

        // Handle successful app installation
        window.addEventListener('appinstalled', (evt) => {
            console.log('VES ERP app was installed successfully');
            
            // Clear the prompt
            deferredPrompt = null;
            
            // Hide install UI
            closeInstallModal();
            hideFloatingInstallBtn();
            
            // Show success message
            showInstallSuccessMessage();
            
            // Update PWA mode status
            setTimeout(() => {
                checkPWAMode();
            }, 1000);
        });

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            const modal = document.getElementById('installModalOverlay');
            if (e.target === modal) {
                closeInstallModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeInstallModal();
            }
        });

        // Handle display mode changes
        window.matchMedia('(display-mode: standalone)').addEventListener('change', (e) => {
            isInPWAMode = e.matches;
            console.log('Display mode changed. PWA Mode:', isInPWAMode);
            
            if (isInPWAMode) {
                closeInstallModal();
                hideFloatingInstallBtn();
            }
        });
    </script>
</body>
</html>