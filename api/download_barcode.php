<?php
/**
 * Download Barcode API
 * Handles barcode image downloads with minimal processing
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Get parameters
$data = isset($_GET['data']) ? trim($_GET['data']) : null;
$name = isset($_GET['name']) ? trim($_GET['name']) : 'barcode';

// Validate input
if (!$data) {
    http_response_code(400);
    die(json_encode(['error' => 'Data parameter is required']));
}

// Sanitize filename
$filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
if (empty($filename)) {
    $filename = 'barcode';
}

// Generate barcode URL from external API using EXACT same settings as displayed barcode
// This ensures the scanner can read it the same way
$barcodeUrl = "https://barcode.tec-it.com/barcode.ashx?data=" . urlencode($data) . 
              "&code=Code39&dpi=150&print=true&width=350&height=120";

// Fetch the barcode image
$context = stream_context_create([
    'http' => [
        'timeout' => 30,
        'user_agent' => 'CTU-Scanner/1.0'
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$imageData = @file_get_contents($barcodeUrl, false, $context);

if ($imageData === false) {
    http_response_code(500);
    die(json_encode(['error' => 'Failed to generate barcode image']));
}

// Create image from fetched data
$barcodeImage = @imagecreatefromstring($imageData);
if ($barcodeImage === false) {
    http_response_code(500);
    die(json_encode(['error' => 'Failed to process barcode image']));
}

// Get original barcode dimensions
$barcodeWidth = imagesx($barcodeImage);
$barcodeHeight = imagesy($barcodeImage);

// Create new image with white background and border
$padding = 60; // larger white space padding
$borderWidth = 4; // thicker border width
$finalWidth = $barcodeWidth + ($padding * 2) + ($borderWidth * 2);
$finalHeight = $barcodeHeight + ($padding * 2) + ($borderWidth * 2);

$finalImage = imagecreatetruecolor($finalWidth, $finalHeight);

// Fill with white background
$white = imagecolorallocate($finalImage, 255, 255, 255);
imagefill($finalImage, 0, 0, $white);

// Draw dark border
$darkGray = imagecolorallocate($finalImage, 44, 62, 80); // #2c3e50
for ($i = 0; $i < $borderWidth; $i++) {
    imagerectangle($finalImage, $i, $i, $finalWidth - 1 - $i, $finalHeight - 1 - $i, $darkGray);
}

// Copy barcode image into the center
$posX = $padding + $borderWidth;
$posY = $padding + $borderWidth;
imagecopy($finalImage, $barcodeImage, $posX, $posY, 0, 0, $barcodeWidth, $barcodeHeight);

// Free memory
imagedestroy($barcodeImage);

// Output final image as PNG
ob_start();
imagepng($finalImage);
$imageData = ob_get_clean();
imagedestroy($finalImage);

// Set appropriate headers for download
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="Barcode_' . $filename . '.png"');
header('Content-Length: ' . strlen($imageData));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Output the image
echo $imageData;
exit;
?>
