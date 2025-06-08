<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Language Settings</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/language.css">
</head>
<body>
    <div class="sidebar-overlay"></div>
    <button class="hamburger-btn" type="button" onclick="toggleSidebar()">
        <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="dashboard-container">
        <header class="dashboard-header">
            <h1 class="header-title">Language Settings</h1>
            <span class="header-date"><?php echo date('F j, Y'); ?></span>
        </header>

        <main class="dashboard-content">
            <div class="alert alert-info">
                <svg xmlns="http://www.w3.org/2000/svg" class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="alert-content">
                    <p style="margin: 0;">Changing the language will affect the entire user interface. Your data and system settings will remain unchanged.</p>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Select Language</h2>
                        <p class="card-subtitle">Choose your preferred language for the system interface</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="language-grid">
                        <!-- English -->
                        <div class="language-option active" data-lang="en">
                            <img src="../../assets/img/flags/en.png" alt="English" class="language-flag">
                            <div class="language-name">English</div>
                            <div class="language-native">English</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 100%;"></div>
                            </div>
                            <div class="translation-percentage">100% translated</div>
                        </div>
                        
                        <!-- Spanish -->
                        <div class="language-option" data-lang="es">
                            <img src="../../assets/img/flags/es.png" alt="Spanish" class="language-flag">
                            <div class="language-name">Spanish</div>
                            <div class="language-native">Español</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 92%;"></div>
                            </div>
                            <div class="translation-percentage">92% translated</div>
                        </div>
                        
                        <!-- French -->
                        <div class="language-option" data-lang="fr">
                            <img src="../../assets/img/flags/fr.png" alt="French" class="language-flag">
                            <div class="language-name">French</div>
                            <div class="language-native">Français</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 85%;"></div>
                            </div>
                            <div class="translation-percentage">85% translated</div>
                        </div>
                        
                        <!-- German -->
                        <div class="language-option" data-lang="de">
                            <img src="../../assets/img/flags/de.png" alt="German" class="language-flag">
                            <div class="language-name">German</div>
                            <div class="language-native">Deutsch</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 78%;"></div>
                            </div>
                            <div class="translation-percentage">78% translated</div>
                        </div>
                        
                        <!-- Arabic -->
                        <div class="language-option" data-lang="ar">
                            <img src="../../assets/img/flags/ar.png" alt="Arabic" class="language-flag">
                            <div class="language-name">Arabic</div>
                            <div class="language-native">العربية</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 70%;"></div>
                            </div>
                            <div class="translation-percentage">70% translated</div>
                        </div>
                        
                        <!-- Chinese -->
                        <div class="language-option" data-lang="zh">
                            <img src="../../assets/img/flags/zh.png" alt="Chinese" class="language-flag">
                            <div class="language-name">Chinese</div>
                            <div class="language-native">中文</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 65%;"></div>
                            </div>
                            <div class="translation-percentage">65% translated</div>
                        </div>
                        
                        <!-- Hindi -->
                        <div class="language-option" data-lang="hi">
                            <img src="../../assets/img/flags/in.png" alt="Hindi" class="language-flag">
                            <div class="language-name">Hindi</div>
                            <div class="language-native">हिन्दी</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 60%;"></div>
                            </div>
                            <div class="translation-percentage">60% translated</div>
                        </div>
                        
                        <!-- Portuguese -->
                        <div class="language-option" data-lang="pt">
                            <img src="../../assets/img/flags/pt.png" alt="Portuguese" class="language-flag">
                            <div class="language-name">Portuguese</div>
                            <div class="language-native">Português</div>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 75%;"></div>
                            </div>
                            <div class="translation-percentage">75% translated</div>
                        </div>
                    </div>
                    
                    <div class="settings-section">
                        <h3 class="settings-title">Additional Language Settings</h3>
                        
                        <div class="form-group">
                            <label for="date-format" class="form-label">Date Format</label>
                            <select id="date-format" class="form-select">
                                <option value="mm/dd/yyyy">MM/DD/YYYY (US)</option>
                                <option value="dd/mm/yyyy">DD/MM/YYYY (UK/EU)</option>
                                <option value="yyyy/mm/dd">YYYY/MM/DD (ISO)</option>
                                <option value="dd.mm.yyyy">DD.MM.YYYY (German)</option>
                                <option value="dd-mm-yyyy">DD-MM-YYYY (French)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="time-format" class="form-label">Time Format</label>
                            <select id="time-format" class="form-select">
                                <option value="12">12-hour (AM/PM)</option>
                                <option value="24">24-hour</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="first-day" class="form-label">First Day of Week</label>
                            <select id="first-day" class="form-select">
                                <option value="0">Sunday</option>
                                <option value="1">Monday</option>
                                <option value="6">Saturday</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select id="timezone" class="form-select">
                                <option value="UTC">UTC (Coordinated Universal Time)</option>
                                <option value="EST">EST (Eastern Standard Time)</option>
                                <option value="CST">CST (Central Standard Time)</option>
                                <option value="MST">MST (Mountain Standard Time)</option>
                                <option value="PST">PST (Pacific Standard Time)</option>
                                <option value="IST">IST (Indian Standard Time)</option>
                                <option value="GMT">GMT (Greenwich Mean Time)</option>
                                <option value="CET">CET (Central European Time)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="auto-detect" class="form-check-input" checked>
                                <label for="auto-detect" class="form-check-label">Auto-detect language based on browser settings</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="fallback-english" class="form-check-input" checked>
                                <label for="fallback-english" class="form-check-label">Fallback to English for untranslated content</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="numbers-localized" class="form-check-input" checked>
                                <label for="numbers-localized" class="form-check-label">Use localized number formats</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="button button-outline" onclick="resetToDefaults()">Reset to Defaults</button>
                    <button type="button" class="button" onclick="saveLanguageSettings()">Save Settings</button>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Toast -->
    <div id="success-toast" class="toast">
        <svg xmlns="http://www.w3.org/2000/svg" class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <div class="toast-message">Language settings saved successfully!</div>
    </div>

    <script>
        // DOM ready function
        document.addEventListener('DOMContentLoaded', function() {
            initializeLanguageOptions();
        });

        // Toggle sidebar visibility
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
            
            const hamburgerBtn = document.querySelector('.hamburger-btn');
            if (sidebar.classList.contains('show')) {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                `;
            } else {
                hamburgerBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="hamburger-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" pointer-events="none">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                `;
            }
        }

        // Initialize Language Options
        function initializeLanguageOptions() {
            const languageOptions = document.querySelectorAll('.language-option');
            
            languageOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove active class from all options
                    languageOptions.forEach(opt => opt.classList.remove('active'));
                    // Add active class to clicked option
                    this.classList.add('active');
                });
            });
        }

        // Reset to Defaults
        function resetToDefaults() {
            // Reset language selection
            const languageOptions = document.querySelectorAll('.language-option');
            languageOptions.forEach(opt => opt.classList.remove('active'));
            document.querySelector('[data-lang="en"]').classList.add('active');
            
            // Reset select inputs
            document.getElementById('date-format').value = 'mm/dd/yyyy';
            document.getElementById('time-format').value = '12';
            document.getElementById('first-day').value = '0';
            document.getElementById('timezone').value = 'UTC';
            
            // Reset checkboxes
            document.getElementById('auto-detect').checked = true;
            document.getElementById('fallback-english').checked = true;
            document.getElementById('numbers-localized').checked = true;
            
            // Show success message
            showToast('Settings reset to defaults');
        }

        // Save Language Settings
        function saveLanguageSettings() {
            // Get selected language
            const selectedLang = document.querySelector('.language-option.active').getAttribute('data-lang');
            
            // Get form values
            const dateFormat = document.getElementById('date-format').value;
            const timeFormat = document.getElementById('time-format').value;
            const firstDay = document.getElementById('first-day').value;
            const timezone = document.getElementById('timezone').value;
            const autoDetect = document.getElementById('auto-detect').checked;
            const fallbackEnglish = document.getElementById('fallback-english').checked;
            const numbersLocalized = document.getElementById('numbers-localized').checked;
            
            // In a real application, you would send these settings to the server
            // For this demo, we'll just log them and show a success message
            console.log('Language Settings:', {
                language: selectedLang,
                dateFormat,
                timeFormat,
                firstDay,
                timezone,
                autoDetect,
                fallbackEnglish,
                numbersLocalized
            });
            
            // Show success toast
            showToast('Language settings saved successfully!');
        }

        // Show Toast Notification
        function showToast(message) {
            const toast = document.getElementById('success-toast');
            toast.querySelector('.toast-message').textContent = message;
            
            toast.classList.add('show');
            
            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Page transitions
        document.addEventListener('DOMContentLoaded', function() {
            const links = document.querySelectorAll('a:not([href^="#"])');
            
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!this.getAttribute('href').startsWith('javascript:')) {
                        e.preventDefault();
                        document.body.classList.add('fade-out');
                        
                        setTimeout(() => {
                            window.location.href = this.getAttribute('href');
                        }, 500);
                    }
                });
            });
        });
    </script>
</body>
</html>