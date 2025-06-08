<?php include 'sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Parent ID Card</title>
    
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/id-card_parent.css">
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
        <h1 class="header-title">Parent ID Card</h1>
        <span class="header-date"><?php echo date('F j, Y'); ?></span>
    </header>

    <main class="dashboard-content">
        <div class="card">
            <h2 class="card-title">Your Parent ID Card</h2>
            
            <div class="id-card-container">
                <div class="id-card">
                    <div class="id-card-header">
                        <div class="school-logo">
                            <img src="https://lh3.googleusercontent.com/-aDvI5uiAJ4pvFRPUoTjzNYt5LH4UvFiSM6OGAL5dQMlzNrzWqVhSFRTRXZ3UWrzDHvR9az0dkffr9t0P39bHbAJb0pbfG-sahO2oKU" alt="School Logo">
                        </div>
                        <div class="school-name">Vinodh English School</div>
                        <div class="school-address">13, Navarathna Road, 5, 2nd Cross Rd, Goraguntepalya, Yeswanthpur, Bengaluru, Karnataka 560022</div>
                    </div>
                    
                    <div class="id-card-content">
                        <div class="parent-photo">
                            <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        
                        <div class="parent-info">
                            <div class="info-row">
                                <div class="info-label">Name:</div>
                                <div class="info-value">Robert Smith</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Parent ID:</div>
                                <div class="info-value">VES-P2024001</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Relation:</div>
                                <div class="info-value">Father</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Contact:</div>
                                <div class="info-value">+1 (555) 789-1234</div>
                            </div>
                            
                            <div class="info-row">
                                <div class="info-label">Email:</div>
                                <div class="info-value">robert.smith@example.com</div>
                            </div>
                        </div>
                        
                        <div class="student-links">
                            <div class="student-link-title">Linked Students</div>
                            
                            <div class="student-link">
                                <div class="student-link-photo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div>
                                    <div class="student-link-name">John Smith</div>
                                    <div class="student-link-class">Class X-A</div>
                                </div>
                            </div>
                            
                            <div class="student-link">
                                <div class="student-link-photo">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div>
                                    <div class="student-link-name">Emma Smith</div>
                                    <div class="student-link-class">Class VII-B</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="id-card-footer">
                        <div class="id-card-validity">Valid till: March 31, 2025</div>
                        <div class="id-card-barcode">
                            <svg width="200" height="30" viewBox="0 0 200 30" xmlns="http://www.w3.org/2000/svg">
                                <rect x="10" y="5" width="2" height="20" fill="#333" />
                                <rect x="15" y="5" width="1" height="20" fill="#333" />
                                <rect x="20" y="5" width="3" height="20" fill="#333" />
                                <rect x="25" y="5" width="2" height="20" fill="#333" />
                                <rect x="30" y="5" width="1" height="20" fill="#333" />
                                <rect x="35" y="5" width="3" height="20" fill="#333" />
                                <rect x="42" y="5" width="2" height="20" fill="#333" />
                                <rect x="48" y="5" width="1" height="20" fill="#333" />
                                <rect x="52" y="5" width="3" height="20" fill="#333" />
                                <rect x="58" y="5" width="2" height="20" fill="#333" />
                                <rect x="65" y="5" width="1" height="20" fill="#333" />
                                <rect x="70" y="5" width="3" height="20" fill="#333" />
                                <rect x="78" y="5" width="2" height="20" fill="#333" />
                                <rect x="82" y="5" width="1" height="20" fill="#333" />
                                <rect x="88" y="5" width="3" height="20" fill="#333" />
                                <rect x="95" y="5" width="2" height="20" fill="#333" />
                                <rect x="100" y="5" width="1" height="20" fill="#333" />
                                <rect x="105" y="5" width="3" height="20" fill="#333" />
                                <rect x="112" y="5" width="2" height="20" fill="#333" />
                                <rect x="118" y="5" width="1" height="20" fill="#333" />
                                <rect x="125" y="5" width="3" height="20" fill="#333" />
                                <rect x="132" y="5" width="2" height="20" fill="#333" />
                                <rect x="138" y="5" width="1" height="20" fill="#333" />
                                <rect x="145" y="5" width="3" height="20" fill="#333" />
                                <rect x="152" y="5" width="2" height="20" fill="#333" />
                                <rect x="158" y="5" width="1" height="20" fill="#333" />
                                <rect x="165" y="5" width="3" height="20" fill="#333" />
                                <rect x="172" y="5" width="2" height="20" fill="#333" />
                                <rect x="178" y="5" width="1" height="20" fill="#333" />
                                <rect x="185" y="5" width="3" height="20" fill="#333" />
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="id-card-actions">
                    <button class="action-button primary-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download ID Card
                    </button>
                    
                    <button class="action-button secondary-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Share ID Card
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Digital Verification</h2>
            
            <div class="qr-card">
                <div class="qr-title">Scan for Verification</div>
                
                <div class="qr-code">
                    <svg width="180" height="180" viewBox="0 0 180 180" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- QR Code for Parent ID -->
                        <rect width="180" height="180" fill="white"/>
                        <rect x="20" y="20" width="10" height="10" fill="black"/>
                        <rect x="30" y="20" width="10" height="10" fill="black"/>
                        <rect x="40" y="20" width="10" height="10" fill="black"/>
                        <rect x="50" y="20" width="10" height="10" fill="black"/>
                        <rect x="60" y="20" width="10" height="10" fill="black"/>
                        <rect x="70" y="20" width="10" height="10" fill="black"/>
                        <rect x="80" y="20" width="10" height="10" fill="black"/>
                        <rect x="100" y="20" width="10" height="10" fill="black"/>
                        <rect x="110" y="20" width="10" height="10" fill="black"/>
                        <rect x="150" y="20" width="10" height="10" fill="black"/>
                        <rect x="20" y="30" width="10" height="10" fill="black"/>
                        <rect x="80" y="30" width="10" height="10" fill="black"/>
                        <rect x="100" y="30" width="10" height="10" fill="black"/>
                        <rect x="130" y="30" width="10" height="10" fill="black"/>
                        <rect x="150" y="30" width="10" height="10" fill="black"/>
                        <rect x="20" y="40" width="10" height="10" fill="black"/>
                        <rect x="40" y="40" width="10" height="10" fill="black"/>
                        <rect x="50" y="40" width="10" height="10" fill="black"/>
                        <rect x="60" y="40" width="10" height="10" fill="black"/>
                        <rect x="80" y="40" width="10" height="10" fill="black"/>
                        <rect x="110" y="40" width="10" height="10" fill="black"/>
                        <rect x="150" y="40" width="10" height="10" fill="black"/>
                        <rect x="20" y="50" width="10" height="10" fill="black"/>
                        <rect x="40" y="50" width="10" height="10" fill="black"/>
                        <rect x="50" y="50" width="10" height="10" fill="black"/>
                        <rect x="60" y="50" width="10" height="10" fill="black"/>
                        <rect x="80" y="50" width="10" height="10" fill="black"/>
                        <rect x="100" y="50" width="10" height="10" fill="black"/>
                        <rect x="110" y="50" width="10" height="10" fill="black"/>
                        <rect x="120" y="50" width="10" height="10" fill="black"/>
                        <rect x="150" y="50" width="10" height="10" fill="black"/>
                        <rect x="20" y="60" width="10" height="10" fill="black"/>
                        <rect x="40" y="60" width="10" height="10" fill="black"/>
                        <rect x="50" y="60" width="10" height="10" fill="black"/>
                        <rect x="60" y="60" width="10" height="10" fill="black"/>
                        <rect x="80" y="60" width="10" height="10" fill="black"/>
                        <rect x="100" y="60" width="10" height="10" fill="black"/>
                        <rect x="130" y="60" width="10" height="10" fill="black"/>
                        <rect x="150" y="60" width="10" height="10" fill="black"/>
                        <rect x="20" y="70" width="10" height="10" fill="black"/>
                        <rect x="80" y="70" width="10" height="10" fill="black"/>
                        <rect x="100" y="70" width="10" height="10" fill="black"/>
                        <rect x="110" y="70" width="10" height="10" fill="black"/>
                        <rect x="150" y="70" width="10" height="10" fill="black"/>
                        <rect x="20" y="80" width="10" height="10" fill="black"/>
                        <rect x="30" y="80" width="10" height="10" fill="black"/>
                        <rect x="40" y="80" width="10" height="10" fill="black"/>
                        <rect x="50" y="80" width="10" height="10" fill="black"/>
                        <rect x="60" y="80" width="10" height="10" fill="black"/>
                        <rect x="70" y="80" width="10" height="10" fill="black"/>
                        <rect x="80" y="80" width="10" height="10" fill="black"/>
                        <rect x="100" y="80" width="10" height="10" fill="black"/>
                        <rect x="120" y="80" width="10" height="10" fill="black"/>
                        <rect x="140" y="80" width="10" height="10" fill="black"/>
                        <rect x="150" y="80" width="10" height="10" fill="black"/>
                        <rect x="100" y="90" width="10" height="10" fill="black"/>
                        <rect x="120" y="90" width="10" height="10" fill="black"/>
                        <rect x="20" y="100" width="10" height="10" fill="black"/>
                        <rect x="30" y="100" width="10" height="10" fill="black"/>
                        <rect x="40" y="100" width="10" height="10" fill="black"/>
                        <rect x="70" y="100" width="10" height="10" fill="black"/>
                        <rect x="90" y="100" width="10" height="10" fill="black"/>
                        <rect x="140" y="100" width="10" height="10" fill="black"/>
                        <rect x="30" y="110" width="10" height="10" fill="black"/>
                        <rect x="50" y="110" width="10" height="10" fill="black"/>
                        <rect x="60" y="110" width="10" height="10" fill="black"/>
                        </svg>
                        </div>
                    </div>
                </div>
                
                <div class="id-card-actions">
                    <button class="action-button primary-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Download ID Card
                    </button>
                    
                    <button class="action-button secondary-button">
                        <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                        </svg>
                        Share ID Card
                    </button>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Update ID Card Photo</h2>
            
            <div class="upload-section">
                <label for="photo-upload" class="upload-label">
                    <svg xmlns="http://www.w3.org/2000/svg" class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="17 8 12 3 7 8"></polyline>
                        <line x1="12" y1="3" x2="12" y2="15"></line>
                    </svg>
                    <div class="upload-text">Click to upload a new photo</div>
                    <div class="upload-text" style="font-size: 0.75rem; margin-top: 0.5rem; color: #9ca3af;">Accepted formats: JPG, PNG. Max size: 2MB</div>
                </label>
                <input type="file" id="photo-upload" class="upload-input" accept="image/jpeg, image/png">
            </div>
            
            <div style="margin-top: 2rem; text-align: center;">
                <button class="action-button primary-button" style="display: inline-flex;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    Save Changes
                </button>
            </div>
        </div>
    </main>
</div>

<script>
    // Script to handle photo upload
    document.getElementById('photo-upload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // File size validation (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size exceeds 2MB limit. Please choose a smaller file.');
                e.target.value = '';
                return;
            }
            
            // Preview the image
            const reader = new FileReader();
            reader.onload = function(event) {
                // Create an image element
                const img = document.createElement('img');
                img.src = event.target.result;
                
                // Replace the SVG icon in the student photo div with the uploaded image
                const photoDiv = document.querySelector('.student-photo');
                photoDiv.innerHTML = '';
                photoDiv.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Animation when clicking on buttons
    document.querySelectorAll('.action-button').forEach(button => {
        button.addEventListener('click', function() {
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 100);
        });
    });
</script>
</body>
</html>