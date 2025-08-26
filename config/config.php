<?php
/**
 * Main Configuration File for Club Management System
 * Includes all error handling, security, and database components
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set error reporting based on environment
error_reporting(E_ALL);
ini_set('display_errors', 0); // Will be handled by our custom error handler
ini_set('log_errors', 1);
ini_set('error_log', 'logs/php_errors.log');

// Set timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Include all configuration files
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/database.php';

// Define system constants
define('SYSTEM_NAME', 'Club Management System');
define('SYSTEM_VERSION', '1.0.0');
define('SYSTEM_URL', 'http://localhost/Club-Management-System');
define('ADMIN_EMAIL', 'admin@yourdomain.com');

// Define file upload constants
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);

// Define pagination constants
define('ITEMS_PER_PAGE', 20);
define('MAX_PAGES_DISPLAY', 10);

// Define session timeout
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Define security constants
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900); // 15 minutes

// Initialize security checks
$security = Security::getInstance();
$security->validateSession();
$security->checkSuspiciousActivity();

// Function to get system configuration
function getSystemConfig($key = null) {
    $config = [
        'name' => SYSTEM_NAME,
        'version' => SYSTEM_VERSION,
        'url' => SYSTEM_URL,
        'admin_email' => ADMIN_EMAIL,
        'max_file_size' => MAX_FILE_SIZE,
        'allowed_image_types' => ALLOWED_IMAGE_TYPES,
        'allowed_document_types' => ALLOWED_DOCUMENT_TYPES,
        'items_per_page' => ITEMS_PER_PAGE,
        'max_pages_display' => MAX_PAGES_DISPLAY,
        'session_timeout' => SESSION_TIMEOUT,
        'password_min_length' => PASSWORD_MIN_LENGTH,
        'login_max_attempts' => LOGIN_MAX_ATTEMPTS,
        'login_lockout_time' => LOGIN_LOCKOUT_TIME
    ];

    if ($key === null) {
        return $config;
    }

    return $config[$key] ?? null;
}

// Function to check if system is in maintenance mode
function isMaintenanceMode() {
    return file_exists(__DIR__ . '/../maintenance.flag');
}

// Function to redirect with error message
function redirectWithError($url, $message, $type = 'error') {
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit();
}

// Function to redirect with success message
function redirectWithSuccess($url, $message) {
    redirectWithError($url, $message, 'success');
}

// Function to display flash messages
function displayFlashMessages() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        $alertClass = $message['type'] === 'success' ? 'success' : 'error';
        $icon = $message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle';
        
        echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo '<i class="fas fa-' . $icon . ' me-2"></i>';
        echo htmlspecialchars($message['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// Function to log system activity
function logSystemActivity($action, $details = '', $userId = null) {
    if ($userId === null) {
        $userId = $_SESSION['user_id'] ?? 'guest';
    }

    $logEntry = sprintf(
        "[%s] ACTIVITY: %s - User: %s - Details: %s\n",
        date('Y-m-d H:i:s'),
        $action,
        $userId,
        $details
    );

    $logFile = dirname(__DIR__) . '/logs/activity.log';
    error_log($logEntry, 3, $logFile);
}

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_type']);
}

// Function to get current user role
function getCurrentUserRole() {
    return $_SESSION['user_type'] ?? 'guest';
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to check if user is admin
function isAdmin() {
    return getCurrentUserRole() === 'admin';
}

// Function to check if user is clubber
function isClubber() {
    return getCurrentUserRole() === 'clubber';
}

// Function to check if user is student
function isStudent() {
    return getCurrentUserRole() === 'student';
}

// Function to format date
function formatDate($date, $format = 'M d, Y g:i A') {
    if (empty($date)) {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Function to generate random string
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

// Function to validate email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Function to validate URL
function isValidURL($url) {
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}

// Function to sanitize output
function sanitizeOutput($data) {
    if (is_array($data)) {
        return array_map('sanitizeOutput', $data);
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Function to get base URL
function getBaseURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $path = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . '://' . $host . $path;
}

// Function to get current page URL
function getCurrentURL() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $uri = $_SERVER['REQUEST_URI'];
    return $protocol . '://' . $host . $uri;
}

// Function to check if request is AJAX
function isAjaxRequest() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Function to send error JSON response
function sendErrorResponse($message, $statusCode = 400) {
    sendJsonResponse(['error' => $message], $statusCode);
}

// Function to send success JSON response
function sendSuccessResponse($data = null, $message = 'Success') {
    sendJsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

// Initialize system
logSystemActivity('System Initialized', 'Configuration loaded successfully');

// Check for maintenance mode
if (isMaintenanceMode() && !isAdmin()) {
    include __DIR__ . '/../maintenance.php';
    exit();
}
?>
