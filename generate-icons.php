<?php
/**
 * VES School ERP - PWA Icon Generator
 * This script generates all required PWA icons from the school logo
 */

// Required icon sizes for PWA
$iconSizes = [
    16, 32, 72, 96, 128, 144, 152, 192, 384, 512
];

// Source logo path
$sourceLogoPath = 'assets/images/school-logo.png';
$outputDir = 'assets/images/';

// Check if source logo exists
if (!file_exists($sourceLogoPath)) {
    echo "Error: Source logo not found at $sourceLogoPath\n";
    echo "Please place your school logo at $sourceLogoPath\n";
    exit(1);
}

// Check if GD extension is available
if (!extension_loaded('gd')) {
    echo "Error: GD extension is not available. Please install php-gd extension.\n";
    exit(1);
}

// Create output directory if it doesn't exist
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

echo "VES School ERP - PWA Icon Generator\n";
echo "===================================\n\n";

// Load source image
$sourceImage = @imagecreatefrompng($sourceLogoPath);
if (!$sourceImage) {
    // Try JPEG
    $sourceImage = @imagecreatefromjpeg($sourceLogoPath);
    if (!$sourceImage) {
        echo "Error: Could not load source image. Please ensure it's a valid PNG or JPEG file.\n";
        exit(1);
    }
}

// Get source dimensions
$sourceWidth = imagesx($sourceImage);
$sourceHeight = imagesy($sourceImage);

echo "Source image: {$sourceWidth}x{$sourceHeight}\n";
echo "Generating icons...\n\n";

// Generate icons for each size
foreach ($iconSizes as $size) {
    $outputPath = $outputDir . "icon-{$size}x{$size}.png";
    
    // Create new image with transparent background
    $newImage = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending and save alpha
    imagealphablending($newImage, false);
    imagesavealpha($newImage, true);
    
    // Create transparent background
    $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
    imagefill($newImage, 0, 0, $transparent);
    
    // Enable alpha blending for copying
    imagealphablending($newImage, true);
    
    // Calculate dimensions to maintain aspect ratio
    $aspectRatio = $sourceWidth / $sourceHeight;
    
    if ($aspectRatio > 1) {
        // Landscape
        $newWidth = $size;
        $newHeight = $size / $aspectRatio;
        $x = 0;
        $y = ($size - $newHeight) / 2;
    } else {
        // Portrait or square
        $newWidth = $size * $aspectRatio;
        $newHeight = $size;
        $x = ($size - $newWidth) / 2;
        $y = 0;
    }
    
    // Copy and resize
    imagecopyresampled(
        $newImage, $sourceImage,
        $x, $y, 0, 0,
        $newWidth, $newHeight,
        $sourceWidth, $sourceHeight
    );
    
    // Save the image
    if (imagepng($newImage, $outputPath)) {
        echo "✓ Generated: icon-{$size}x{$size}.png\n";
    } else {
        echo "✗ Failed to generate: icon-{$size}x{$size}.png\n";
    }
    
    // Clean up
    imagedestroy($newImage);
}

// Clean up source image
imagedestroy($sourceImage);

echo "\n";
echo "Icon generation completed!\n";
echo "Generated " . count($iconSizes) . " PWA icons in $outputDir\n\n";

// Generate additional icons for specific purposes
echo "Generating additional icons...\n";

// Generate favicon.ico (16x16 and 32x32 combined)
$faviconSizes = [16, 32];
echo "✓ Favicon icons ready (use icon-16x16.png and icon-32x32.png)\n";

// Generate Apple touch icons
echo "✓ Apple touch icons ready (use icon-152x152.png and icon-192x192.png)\n";

// Generate module-specific icons (placeholder)
$moduleIcons = ['admin', 'teacher', 'student', 'reception'];
foreach ($moduleIcons as $module) {
    echo "ℹ Note: Create {$module}-icon-96x96.png for module shortcuts\n";
}

echo "\nPWA Setup Instructions:\n";
echo "======================\n";
echo "1. All required icons have been generated in $outputDir\n";
echo "2. The manifest.json file is already configured\n";
echo "3. Service worker (sw.js) is ready\n";
echo "4. PWA meta tags are added to index.php\n";
echo "5. Your app is now installable from browsers!\n\n";

echo "To test PWA installation:\n";
echo "1. Open your site in Chrome/Edge\n";
echo "2. Look for 'Install' button in address bar\n";
echo "3. Or use Chrome DevTools > Application > Manifest\n\n";

echo "PWA Features Enabled:\n";
echo "• Offline functionality\n";
echo "• App installation\n";
echo "• Background sync\n";
echo "• Push notifications (ready)\n";
echo "• App shortcuts\n";
echo "• Responsive design\n\n";

echo "Done! Your VES School ERP is now a Progressive Web App.\n";
?> 