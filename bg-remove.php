<?php
/**
 * Professional Background Remover API
 * 
 * AI-powered background removal using Cloudinary
 * Free online tool for transparent PNG generation
 * 
 * @version 2.0
 * @author Your Brand Name
 */

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Transformation\Effect;

// ============================================================================
// CONFIGURATION
// ============================================================================

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// CORS configuration (adjust as needed for your domain)
$allowedOrigins = [
    'https://tumhara-domain.com',
    'http://localhost',
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
} else {
    header('Access-Control-Allow-Origin: *'); // For development only - restrict in production
}

header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Constants
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'image/webp']);
define('CLOUDINARY_FOLDER', 'bg-remove');

// Cloudinary credentials - REPLACE WITH YOUR CREDENTIALS
define('CLOUDINARY_CLOUD_NAME', 'dhf2xwjxe');
define('CLOUDINARY_API_KEY', '359864753282267');
define('CLOUDINARY_API_SECRET', 'Gq3NmfvImS17V-SM7AFCncvru94');

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/**
 * Send JSON error response
 */
function jsonError(string $message, int $statusCode = 400): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    
    $response = [
        'success' => false,
        'error' => $message,
        'timestamp' => date('c')
    ];
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    // Log error (in production, use proper logging)
    error_log("[Background Remover Error] {$message}");
    
    exit;
}

/**
 * Send JSON success response
 */
function jsonSuccess(string $url, array $metadata = []): void
{
    header('Content-Type: application/json');
    
    $response = [
        'success' => true,
        'url' => $url,
        'timestamp' => date('c')
    ];
    
    if (!empty($metadata)) {
        $response['metadata'] = $metadata;
    }
    
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    exit;
}

/**
 * Validate file upload
 */
function validateUpload(array $file): void
{
    // Check if file exists
    if (empty($file) || !isset($file['tmp_name'])) {
        jsonError('No file uploaded', 400);
    }
    
    // Check for upload errors
    if (!isset($file['error']) || is_array($file['error'])) {
        jsonError('Invalid upload data', 400);
    }
    
    // Handle different upload error codes
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            jsonError('File too large. Maximum 10MB allowed', 413);
        case UPLOAD_ERR_PARTIAL:
            jsonError('File upload incomplete. Please try again', 400);
        case UPLOAD_ERR_NO_FILE:
            jsonError('No file uploaded', 400);
        case UPLOAD_ERR_NO_TMP_DIR:
        case UPLOAD_ERR_CANT_WRITE:
        case UPLOAD_ERR_EXTENSION:
            jsonError('Server configuration error. Please contact support', 500);
        default:
            jsonError('Upload error code: ' . $file['error'], 400);
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        jsonError('File too large. Maximum 10MB allowed', 413);
    }
    
    // Validate MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    
    if (!in_array($mimeType, ALLOWED_MIME_TYPES, true)) {
        jsonError('Invalid file type. Only JPG, PNG, and WebP images are allowed', 415);
    }
    
    // Additional security: check if file is actually an image
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        jsonError('Invalid image file. File may be corrupted', 400);
    }
    
    // Validate image dimensions (optional - prevent extremely large images)
    $maxDimension = 8000; // pixels
    if ($imageInfo[0] > $maxDimension || $imageInfo[1] > $maxDimension) {
        jsonError("Image dimensions too large. Maximum {$maxDimension}x{$maxDimension} pixels", 400);
    }
}

/**
 * Sanitize filename
 */
function sanitizeFilename(string $filename): string
{
    $filename = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
    $filename = preg_replace('/_+/', '_', $filename);
    return substr($filename, 0, 200); // Limit length
}

// ============================================================================
// MAIN PROCESSING
// ============================================================================

try {
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonError('Only POST method allowed', 405);
    }
    
    // Rate limiting check (basic implementation - use Redis/Memcached in production)
    // Uncomment and implement proper rate limiting for production
    /*
    $clientIp = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimitKey = "bg_remove_rate_limit_{$clientIp}";
    // Implement rate limiting logic here
    */
    
    // Validate file upload
    if (empty($_FILES) || !isset($_FILES['image'])) {
        jsonError('No image file received', 400);
    }
    
    $file = $_FILES['image'];
    validateUpload($file);
    
    // Get file information
    $tmpPath = $file['tmp_name'];
    $originalName = sanitizeFilename($file['name']);
    
    // Initialize Cloudinary
    $cloudinary = new Cloudinary([
        'cloud' => [
            'cloud_name' => CLOUDINARY_CLOUD_NAME,
            'api_key'    => CLOUDINARY_API_KEY,
            'api_secret' => CLOUDINARY_API_SECRET,
        ],
    ]);
    
    // Upload original image to Cloudinary
    $uploadOptions = [
        'folder'        => CLOUDINARY_FOLDER,
        'resource_type' => 'image',
        'public_id'     => uniqid('bg_remove_', true), // Unique identifier
        'overwrite'     => false,
        'invalidate'    => true,
    ];
    
    $upload = $cloudinary->uploadApi()->upload($tmpPath, $uploadOptions);
    
    if (empty($upload['public_id'])) {
        jsonError('Failed to upload image to processing server', 500);
    }
    
    $publicId = $upload['public_id'];
    
    // Generate background-removed PNG URL
    $uri = $cloudinary->image($publicId)
        ->effect(Effect::backgroundRemoval())
        ->format('png')
        ->toUrl();
    
    $processedUrl = (string)$uri;
    
    if (empty($processedUrl)) {
        jsonError('Failed to generate processed image URL', 500);
    }
    
    // Prepare metadata
    $metadata = [
        'original_filename' => $originalName,
        'original_size' => $file['size'],
        'processed_format' => 'png',
        'cloudinary_id' => $publicId,
    ];
    
    // Log success (in production, use proper analytics)
    error_log("[Background Remover Success] Processed: {$originalName}, Size: {$file['size']} bytes");
    
    // Return success response
    jsonSuccess($processedUrl, $metadata);
    
} catch (Cloudinary\Api\Exception\ApiError $e) {
    // Cloudinary API errors
    error_log("[Cloudinary API Error] " . $e->getMessage());
    jsonError('Image processing service error. Please try again later', 503);
    
} catch (Exception $e) {
    // Generic errors
    error_log("[Background Remover Exception] " . $e->getMessage());
    jsonError('Server error: ' . $e->getMessage(), 500);
    
} catch (Throwable $e) {
    // Catch all (PHP 7+)
    error_log("[Background Remover Critical Error] " . $e->getMessage());
    jsonError('Critical server error. Please contact support', 500);
}
