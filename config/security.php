<?php
/**
 * Security Handler for Club Management System
 * Provides input validation, CSRF protection, and security utilities
 */

require_once 'error_handler.php';

class Security {
    private static $instance = null;
    private $csrfToken = null;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->generateCSRFToken();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Generate CSRF token
     */
    private function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $this->csrfToken = $_SESSION['csrf_token'];
    }

    /**
     * Get CSRF token
     */
    public function getCSRFToken() {
        return $this->csrfToken;
    }

    /**
     * Verify CSRF token
     */
    public function verifyCSRFToken($token) {
        if (empty($token) || empty($this->csrfToken)) {
            return false;
        }
        return hash_equals($this->csrfToken, $token);
    }

    /**
     * Validate and sanitize input
     */
    public function sanitizeInput($input, $type = 'string', $options = []) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }

        switch ($type) {
            case 'email':
                $input = filter_var(trim($input), FILTER_SANITIZE_EMAIL);
                if (!filter_var($input, FILTER_VALIDATE_EMAIL)) {
                    throwSystemError("Invalid email format", "Validation Error");
                    return false;
                }
                break;

            case 'int':
                $input = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
                if (!filter_var($input, FILTER_VALIDATE_INT)) {
                    throwSystemError("Invalid integer value", "Validation Error");
                    return false;
                }
                break;

            case 'float':
                $input = filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                if (!filter_var($input, FILTER_VALIDATE_FLOAT)) {
                    throwSystemError("Invalid float value", "Validation Error");
                    return false;
                }
                break;

            case 'url':
                $input = filter_var(trim($input), FILTER_SANITIZE_URL);
                if (!filter_var($input, FILTER_VALIDATE_URL)) {
                    throwSystemError("Invalid URL format", "Validation Error");
                    return false;
                }
                break;

            case 'date':
                $input = trim($input);
                if (!strtotime($input)) {
                    throwSystemError("Invalid date format", "Validation Error");
                    return false;
                }
                break;

            case 'password':
                $input = trim($input);
                if (isset($options['min_length']) && strlen($input) < $options['min_length']) {
                    throwSystemError("Password must be at least " . $options['min_length'] . " characters long", "Validation Error");
                    return false;
                }
                if (isset($options['require_special']) && $options['require_special'] && !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $input)) {
                    throwSystemError("Password must contain at least one special character", "Validation Error");
                    return false;
                }
                break;

            case 'string':
            default:
                $input = trim($input);
                if (isset($options['max_length']) && strlen($input) > $options['max_length']) {
                    throwSystemError("Input exceeds maximum length of " . $options['max_length'] . " characters", "Validation Error");
                    return false;
                }
                if (isset($options['min_length']) && strlen($input) < $options['min_length']) {
                    throwSystemError("Input must be at least " . $options['min_length'] . " characters long", "Validation Error");
                    return false;
                }
                if (isset($options['pattern']) && !preg_match($options['pattern'], $input)) {
                    throwSystemError("Input format is invalid", "Validation Error");
                    return false;
                }
                $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
                break;
        }

        return $input;
    }

    /**
     * Validate file upload
     */
    public function validateFileUpload($file, $allowedTypes = [], $maxSize = 5242880) {
        if (!isset($file['error']) || is_array($file['error'])) {
            throwSystemError("Invalid file parameter", "File Error");
            return false;
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throwSystemError("No file was uploaded", "File Error");
                return false;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throwSystemError("File size exceeds limit", "File Error");
                return false;
            case UPLOAD_ERR_PARTIAL:
                throwSystemError("File was only partially uploaded", "File Error");
                return false;
            default:
                throwSystemError("Unknown file upload error", "File Error");
                return false;
        }

        if ($file['size'] > $maxSize) {
            throwSystemError("File size exceeds maximum allowed size", "File Error");
            return false;
        }

        if (!empty($allowedTypes)) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file['tmp_name']);
            
            if (!in_array($mimeType, $allowedTypes)) {
                throwSystemError("File type not allowed", "File Error");
                return false;
            }
        }

        return true;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($requiredRole, $userRole = null) {
        if ($userRole === null) {
            $userRole = $_SESSION['user_type'] ?? 'guest';
        }

        $roleHierarchy = [
            'admin' => 3,
            'clubber' => 2,
            'student' => 1,
            'guest' => 0
        ];

        $requiredLevel = $roleHierarchy[$requiredRole] ?? 0;
        $userLevel = $roleHierarchy[$userRole] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Require authentication
     */
    public function requireAuth($redirectUrl = 'login.php') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    /**
     * Require specific role
     */
    public function requireRole($role, $redirectUrl = 'home.php') {
        $this->requireAuth();
        
        if (!$this->hasPermission($role)) {
            throwSystemError("Access denied. Insufficient permissions.", "Permission Error");
            header("Location: $redirectUrl");
            exit();
        }
    }

    /**
     * Generate secure random string
     */
    public function generateRandomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Hash password securely
     */
    public function hashPassword($password) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Verify password
     */
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Check if password needs rehashing
     */
    public function passwordNeedsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    /**
     * Prevent XSS attacks
     */
    public function preventXSS($data) {
        if (is_array($data)) {
            return array_map([$this, 'preventXSS'], $data);
        }
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Prevent SQL injection (use prepared statements instead)
     */
    public function escapeSQL($string) {
        // This is a fallback - always use prepared statements when possible
        return addslashes($string);
    }

    /**
     * Validate session
     */
    public function validateSession() {
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            session_unset();
            session_destroy();
            header("Location: login.php?expired=1");
            exit();
        }
        $_SESSION['last_activity'] = time();
    }

    /**
     * Regenerate session ID for security
     */
    public function regenerateSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * Log security event
     */
    public function logSecurityEvent($event, $details = '') {
        $logEntry = sprintf(
            "[%s] SECURITY: %s - %s\nUser: %s (%s)\nIP: %s\nURL: %s\nDetails: %s\n",
            date('Y-m-d H:i:s'),
            $event,
            $_SESSION['user_id'] ?? 'guest',
            $_SESSION['user_type'] ?? 'guest',
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['REQUEST_URI'] ?? 'unknown',
            $details
        );

        $logFile = dirname(__DIR__) . '/logs/security.log';
        error_log($logEntry, 3, $logFile);
    }

    /**
     * Check for suspicious activity
     */
    public function checkSuspiciousActivity() {
        $suspicious = false;
        $reasons = [];

        // Check for rapid requests
        if (isset($_SESSION['request_count']) && $_SESSION['request_count'] > 100) {
            $suspicious = true;
            $reasons[] = 'Too many requests';
        }

        // Check for unusual user agent
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (empty($userAgent) || strlen($userAgent) > 500) {
            $suspicious = true;
            $reasons[] = 'Suspicious user agent';
        }

        // Check for multiple failed logins
        if (isset($_SESSION['failed_logins']) && $_SESSION['failed_logins'] > 5) {
            $suspicious = true;
            $reasons[] = 'Multiple failed login attempts';
        }

        if ($suspicious) {
            $this->logSecurityEvent('Suspicious Activity Detected', implode(', ', $reasons));
        }

        return $suspicious;
    }
}

// Initialize security instance
$security = Security::getInstance();

// Helper functions for easy security operations
function sanitize_input($input, $type = 'string', $options = []) {
    global $security;
    return $security->sanitizeInput($input, $type, $options);
}

function has_permission($role, $userRole = null) {
    global $security;
    return $security->hasPermission($role, $userRole);
}

function require_auth($redirectUrl = 'login.php') {
    global $security;
    $security->requireAuth($redirectUrl);
}

function require_role($role, $redirectUrl = 'home.php') {
    global $security;
    $security->requireRole($role, $redirectUrl);
}

function hash_password($password) {
    global $security;
    return $security->hashPassword($password);
}

function verify_password($password, $hash) {
    global $security;
    return $security->verifyPassword($password, $hash);
}

function prevent_xss($data) {
    global $security;
    return $security->preventXSS($data);
}

function get_csrf_token() {
    global $security;
    return $security->getCSRFToken();
}

function verify_csrf_token($token) {
    global $security;
    return $security->verifyCSRFToken($token);
}
?>
