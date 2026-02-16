<?php
/**
 * Professional Passport Photo Maker - Backend API
 * 
 * Features:
 * - Image upload validation (JPG, PNG, WebP)
 * - AI-powered background removal using Cloudinary
 * - Dynamic sizing for 25+ countries
 * - Custom background color application
 * - High-quality PNG output
 * - Comprehensive error handling
 * 
 * @version 2.0
 * @author WebAITool
 */

declare(strict_types=1);

// Error reporting (DISABLE in production)
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Load Cloudinary SDK
require __DIR__ . '/vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Resize;
use Cloudinary\Transformation\Effect;

/**
 * Send JSON error response
 * 
 * @param string $message Error message
 * @param int $statusCode HTTP status code
 * @return void
 */
function json_error(string $message, int $statusCode = 400): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'error' => $message,
        'timestamp' => date('c')
    ]);
    exit;
}

/**
 * Send JSON success response
 * 
 * @param string $url Processed image URL
 * @param array $metadata Additional metadata
 * @return void
 */
function json_success(string $url, array $metadata = []): void
{
    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => true,
        'url' => $url,
        'metadata' => $metadata,
        'timestamp' => date('c')
    ]);
    exit;
}

/**
 * Validate hex color format
 * 
 * @param string $color Hex color code
 * @return bool
 */
function isValidHexColor(string $color): bool
{
    return preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1;
}

/**
 * Log request for debugging (optional)
 * 
 * @param array $data Data to log
 * @return void
 */
function logRequest(array $data): void
{
    $logFile = __DIR__ . '/logs/requests.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    
    $logEntry = date('Y-m-d H:i:s') . ' - ' . json_encode($data) . PHP_EOL;
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// ============================================================================
// CORS Headers - Allow cross-origin requests
// ============================================================================
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Only POST method is allowed', 405);
}

// ============================================================================
// File Upload Validation
// ============================================================================

// Check if file was uploaded
if (empty($_FILES) || !isset($_FILES['image'])) {
    json_error('No image file received');
}

$file = $_FILES['image'];

// Validate file upload
if (!isset($file['error']) || is_array($file['error'])) {
    json_error('Invalid file upload data');
}

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension',
    ];
    
    $errorMsg = $errorMessages[$file['error']] ?? 'Unknown upload error';
    json_error('Upload error: ' . $errorMsg);
}

// Validate file size (10MB maximum)
$maxSize = 10 * 1024 * 1024; // 10MB in bytes
if ($file['size'] > $maxSize) {
    json_error('File too large. Maximum size is 10MB');
}

// Validate file type using MIME type detection
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
$allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($mimeType, $allowedTypes, true)) {
    json_error('Invalid file type. Only JPEG, PNG, and WebP images are allowed');
}

$tmpPath = $file['tmp_name'];

// ============================================================================
// Process Request Parameters
// ============================================================================

// Get background color (default: white)
$bgColor = $_POST['bgColor'] ?? '#ffffff';
$bgColor = trim($bgColor);

// Validate hex color format
if (!isValidHexColor($bgColor)) {
    $bgColor = '#ffffff'; // Fallback to white if invalid
}

// Get dimensions (with defaults for USA 2x2 inch)
$width = isset($_POST['width']) ? (int)$_POST['width'] : 600;
$height = isset($_POST['height']) ? (int)$_POST['height'] : 600;

// Validate dimensions (min 100px, max 2000px for safety)
if ($width < 100 || $width > 2000 || $height < 100 || $height > 2000) {
    json_error('Invalid dimensions. Width and height must be between 100 and 2000 pixels');
}

// ============================================================================
// Cloudinary Configuration
// IMPORTANT: Replace with your actual Cloudinary credentials
// ============================================================================

$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dhf2xwjxe',    // Replace with your cloud name
        'api_key' => '359864753282267',  // Replace with your API key
        'api_secret' => 'Gq3NmfvImS17V-SM7AFCncvru94', // Replace with your API secret
    ],
]);

// ============================================================================
// Process Image with AI Background Removal
// ============================================================================

try {
    // Step 1: Upload original image to Cloudinary
    $uploadResult = $cloudinary->uploadApi()->upload($tmpPath, [
        'folder' => 'passport-maker',
        'resource_type' => 'image',
        'use_filename' => false,
        'unique_filename' => true,
    ]);

    if (empty($uploadResult['public_id'])) {
        json_error('Failed to upload image to Cloudinary', 500);
    }

    $publicId = $uploadResult['public_id'];

    // Step 2: Apply AI transformations
    // - Remove background using Cloudinary AI
    // - Apply user-selected background color
    // - Resize to exact passport dimensions with padding
    // - Convert to PNG for best quality
    // - Set quality to auto:best
    
    $transformedUrl = $cloudinary->image($publicId)
        ->effect(Effect::backgroundRemoval())   // AI background removal
        ->background($bgColor)                  // Apply custom background color
        ->resize(Resize::pad($width, $height))  // Resize with padding to exact dimensions
        ->format('png')                         // Output as PNG
        ->quality('auto:best')                  // Best quality
        ->toUrl();

    $finalUrl = (string)$transformedUrl;

    // Prepare metadata for response
    $metadata = [
        'width' => $width,
        'height' => $height,
        'background_color' => $bgColor,
        'original_filename' => $file['name'],
        'file_size' => $file['size'],
        'format' => 'png',
        'mime_type' => $mimeType
    ];

    // Optional: Log successful request
    logRequest([
        'success' => true,
        'dimensions' => "{$width}x{$height}",
        'background' => $bgColor,
        'original_size' => $file['size'],
        'original_mime' => $mimeType
    ]);

    // Return success response with image URL
    json_success($finalUrl, $metadata);

} catch (Throwable $e) {
    // Log error for debugging
    error_log('Passport Maker Error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Log error details
    logRequest([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    // Return user-friendly error message
    $errorMessage = 'Processing failed';
    
    // Provide more specific error messages when possible
    if (strpos($e->getMessage(), 'Unauthorized') !== false) {
        $errorMessage = 'Invalid Cloudinary credentials. Please check configuration.';
    } elseif (strpos($e->getMessage(), 'timeout') !== false) {
        $errorMessage = 'Processing timeout. Please try again with a smaller image.';
    } elseif (strpos($e->getMessage(), 'rate limit') !== false) {
        $errorMessage = 'Rate limit exceeded. Please try again later.';
    }
    
    json_error($errorMessage, 500);
}
