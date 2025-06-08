<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Include the variables file
include 'setup.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $companyname; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&display=swap" rel="stylesheet">
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

        /* Options section */
        .options-section {
            position: absolute;
            right: 0;
            width: 50%;
            opacity: 0;
            visibility: hidden;
            transition: opacity 1s ease-in-out, visibility 1s ease-in-out;
            padding: 1rem;
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
            transform: translateX(-45%);
        }

        .show-options {
            opacity: 1;
            visibility: visible;
        }

        /* Animations */
        /* Enhanced animation set */
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
                transition: all 1.5s ease-in-out;
            }

            .move-to-left {
                transform: translateY(-30%);
            }

            .options-section {
                position: relative;
                width: 100%;
                margin-top: 18rem;
            }

            .logo {
                width: 150px;
                height: 150px;
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
        <div class="logo-container" id="logoContainer">
            <div class="logo">
                <img src="assets/images/school-logo.png" alt="Educational Platform Logo">
            </div>
            <div class="slogan" id="slogan">Your child future as a new address</div>
        </div>

        <!-- Options section -->
        <div class="options-section" id="optionsSection">
            <h1>Choose Your Role</h1>
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
            <p class="signup-prompt" style="text-align: center; margin-top: 2rem; color: #71717A;">
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
        
        <!-- Animated shapes -->
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
    </div>
    
    <script>
        window.onload = function() {
            // Get elements
            const logoContainer = document.getElementById('logoContainer');
            const slogan = document.getElementById('slogan');
            const optionsSection = document.getElementById('optionsSection');
            const cards = document.querySelectorAll('.login-card');
            const successPopup = document.getElementById('successPopup');
            
            // Show slogan with letter-by-letter animation
            let sloganText = "Your child future as a new address";
            slogan.textContent = "";
            
            // Create individual spans for each letter
            Array.from(sloganText).forEach(char => {
                const span = document.createElement('span');
                span.textContent = char === " " ? "\u00A0" : char; // Use non-breaking space for spaces
                slogan.appendChild(span);
            });
            
            // Animate each letter with a staggered delay
            const letters = slogan.querySelectorAll('span');
            function animateLetters(index) {
                if (index < letters.length) {
                    letters[index].classList.add('visible');
                    setTimeout(() => animateLetters(index + 1), 80);
                } else {
                    // Move logo after all letters are visible
                    setTimeout(() => {
                        logoContainer.classList.add('move-to-left');
                        
                        // Show options
                        setTimeout(() => {
                            optionsSection.classList.add('show-options');
                            
                            // Animate each card
                            cards.forEach(card => {
                                const index = card.style.getPropertyValue('--index');
                                setTimeout(() => {
                                    card.style.opacity = '1';
                                    card.classList.add('card-animated');
                                }, index * 200);
                            });
                        }, 800);
                    }, 1000);
                }
            }
            
            // Start animating letters after a delay
            setTimeout(() => {
                animateLetters(0);
            }, 1000);

            // Add hover behavior for cards
            cards.forEach(card => {
                // Optional: Add hover sound effect (uncomment if you have the sound file)
                /*
                card.addEventListener('mouseenter', () => {
                    const hoverSound = new Audio('assets/sounds/hover.mp3');
                    hoverSound.volume = 0.2;
                    hoverSound.play().catch(e => console.log('Audio play failed: Browser requires user interaction first'));
                });
                */
                
                card.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent immediate navigation
                    
                    // Add active state
                    cards.forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show success message
                    setTimeout(() => {
                        successPopup.classList.add('show');
                    }, 300);
                    
                    // Get the href from the card
                    const href = this.getAttribute('href');
                    
                    // Redirect after animation
                    setTimeout(() => {
                        window.location.href = href;
                    }, 1500);
                });
            });
        };
    </script>
</body>
</html>