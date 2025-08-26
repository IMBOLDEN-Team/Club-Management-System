<?php
/**
 * Comprehensive Error Handler for Club Management System
 * Handles all types of errors: PHP errors, exceptions, database errors, and custom errors
 */

class ErrorHandler {
    private static $logFile = 'logs/error.log';
    private static $instance = null;
    private $errors = [];
    private $debugMode = false;

    private function __construct() {
        // Create logs directory if it doesn't exist
        $logDir = dirname(__DIR__) . '/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Set error reporting based on environment
        $this->debugMode = defined('DEBUG_MODE') ? DEBUG_MODE : false;
        
        // Set error handlers
        $this->setErrorHandlers();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Set up all error handlers
     */
    private function setErrorHandlers() {
        // Set error handler for PHP errors
        set_error_handler([$this, 'handleError']);
        
        // Set exception handler
        set_exception_handler([$this, 'handleException']);
        
        // Set shutdown function for fatal errors
        register_shutdown_function([$this, 'handleFatalError']);
        
        // Set custom error handler for database errors
        $this->setDatabaseErrorHandler();
    }

    /**
     * Handle PHP errors (E_ERROR, E_WARNING, E_NOTICE, etc.)
     */
    public function handleError($errno, $errstr, $errfile, $errline) {
        $errorType = $this->getErrorType($errno);
        $error = [
            'type' => $errorType,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'user_type' => $_SESSION['user_type'] ?? 'guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->logError($error);
        $this->addError($error);

        // In production, don't display errors to users
        if ($this->debugMode) {
            $this->displayError($error);
        }

        return true; // Prevent default error handler
    }

    /**
     * Handle exceptions
     */
    public function handleException($exception) {
        $error = [
            'type' => 'Exception',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'user_type' => $_SESSION['user_type'] ?? 'guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->logError($error);
        $this->addError($error);

        if ($this->debugMode) {
            $this->displayError($error);
        } else {
            $this->displayUserFriendlyError();
        }
    }

    /**
     * Handle fatal errors
     */
    public function handleFatalError() {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $fatalError = [
                'type' => 'Fatal Error',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'timestamp' => date('Y-m-d H:i:s'),
                'user_id' => $_SESSION['user_id'] ?? 'guest',
                'user_type' => $_SESSION['user_type'] ?? 'guest',
                'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ];

            $this->logError($fatalError);
            $this->addError($fatalError);

            if ($this->debugMode) {
                $this->displayError($fatalError);
            } else {
                $this->displayUserFriendlyError();
            }
        }
    }

    /**
     * Handle database errors specifically
     */
    public function handleDatabaseError($error, $query = '') {
        $dbError = [
            'type' => 'Database Error',
            'message' => $error,
            'query' => $query,
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'user_type' => $_SESSION['user_type'] ?? 'guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->logError($dbError);
        $this->addError($dbError);

        if ($this->debugMode) {
            $this->displayError($dbError);
        } else {
            $this->displayUserFriendlyError('database');
        }
    }

    /**
     * Set database error handler
     */
    private function setDatabaseErrorHandler() {
        // This will be called when database operations fail
        if (function_exists('mysqli_connect_error')) {
            if (mysqli_connect_error()) {
                $this->handleDatabaseError(mysqli_connect_error());
            }
        }
    }

    /**
     * Log error to file
     */
    private function logError($error) {
        $logEntry = sprintf(
            "[%s] %s: %s in %s on line %d\nUser: %s (%s)\nURL: %s\nIP: %s\n%s\n",
            $error['timestamp'],
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line'],
            $error['user_id'],
            $error['user_type'],
            $error['url'],
            $error['ip'],
            isset($error['trace']) ? "Trace:\n" . $error['trace'] : ''
        );

        $logFile = dirname(__DIR__) . '/' . self::$logFile;
        error_log($logEntry, 3, $logFile);
    }

    /**
     * Add error to session for display
     */
    private function addError($error) {
        if (!isset($_SESSION['system_errors'])) {
            $_SESSION['system_errors'] = [];
        }
        $_SESSION['system_errors'][] = $error;
    }

    /**
     * Display error for developers (debug mode)
     */
    private function displayError($error) {
        if (headers_sent()) {
            echo '<div style="background: #fee; border: 1px solid #fcc; padding: 10px; margin: 10px; border-radius: 5px;">';
            echo '<h3 style="color: #c33;">System Error</h3>';
            echo '<p><strong>Type:</strong> ' . htmlspecialchars($error['type']) . '</p>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>';
            echo '<p><strong>Line:</strong> ' . htmlspecialchars($error['line']) . '</p>';
            if (isset($error['trace'])) {
                echo '<p><strong>Trace:</strong></p><pre>' . htmlspecialchars($error['trace']) . '</pre>';
            }
            echo '</div>';
        }
    }

    /**
     * Display user-friendly error message
     */
    private function displayUserFriendlyError($type = 'general') {
        if (headers_sent()) {
            $message = $this->getUserFriendlyMessage($type);
            echo '<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 20px; margin: 20px; border-radius: 8px; text-align: center;">';
            echo '<h2 style="color: #6c757d; margin-bottom: 15px;">Oops! Something went wrong</h2>';
            echo '<p style="color: #6c757d; margin-bottom: 20px;">' . htmlspecialchars($message) . '</p>';
            echo '<a href="home.php" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Go Home</a>';
            echo '</div>';
        }
    }

    /**
     * Get user-friendly error message
     */
    private function getUserFriendlyMessage($type) {
        $messages = [
            'general' => 'We encountered an unexpected error. Please try again later or contact support if the problem persists.',
            'database' => 'We\'re experiencing technical difficulties with our database. Please try again in a few minutes.',
            'auth' => 'There was an authentication error. Please log in again.',
            'permission' => 'You don\'t have permission to perform this action.',
            'validation' => 'The information you provided is invalid. Please check and try again.',
            'file' => 'There was an error processing your file. Please try again.',
            'network' => 'We\'re experiencing network issues. Please check your connection and try again.'
        ];

        return $messages[$type] ?? $messages['general'];
    }

    /**
     * Get error type name from error number
     */
    private function getErrorType($errno) {
        $errorTypes = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];

        return $errorTypes[$errno] ?? 'Unknown Error';
    }

    /**
     * Check if there are any errors
     */
    public function hasErrors() {
        return !empty($_SESSION['system_errors']);
    }

    /**
     * Get all errors
     */
    public function getErrors() {
        return $_SESSION['system_errors'] ?? [];
    }

    /**
     * Clear all errors
     */
    public function clearErrors() {
        unset($_SESSION['system_errors']);
    }

    /**
     * Display errors in a formatted way (for admin/debug purposes)
     */
    public function displayErrors() {
        if (!$this->hasErrors()) {
            return;
        }

        echo '<div class="error-summary" style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 15px; border-radius: 5px;">';
        echo '<h4 style="color: #856404; margin-bottom: 10px;">System Errors (' . count($_SESSION['system_errors']) . ')</h4>';
        
        foreach ($_SESSION['system_errors'] as $error) {
            echo '<div style="margin-bottom: 10px; padding: 10px; background: #fff; border-left: 3px solid #ffc107;">';
            echo '<strong>' . htmlspecialchars($error['type']) . ':</strong> ' . htmlspecialchars($error['message']);
            echo '<br><small style="color: #6c757d;">File: ' . htmlspecialchars($error['file']) . ' (Line: ' . $error['line'] . ')</small>';
            echo '</div>';
        }
        
        echo '<button onclick="this.parentElement.style.display=\'none\'" style="background: #6c757d; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Dismiss</button>';
        echo '</div>';
    }

    /**
     * Custom error for specific scenarios
     */
    public function throwCustomError($message, $type = 'Custom Error', $file = '', $line = 0) {
        $error = [
            'type' => $type,
            'message' => $message,
            'file' => $file ?: debug_backtrace()[0]['file'],
            'line' => $line ?: debug_backtrace()[0]['line'],
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'user_type' => $_SESSION['user_type'] ?? 'guest',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        $this->logError($error);
        $this->addError($error);

        if ($this->debugMode) {
            $this->displayError($error);
        }
    }
}

// Initialize error handler
ErrorHandler::getInstance();

// Define debug mode (set to true for development, false for production)
define('DEBUG_MODE', false);

// Function to easily throw custom errors
function throwSystemError($message, $type = 'Custom Error') {
    ErrorHandler::getInstance()->throwCustomError($message, $type);
}

// Function to check for errors
function hasSystemErrors() {
    return ErrorHandler::getInstance()->hasErrors();
}

// Function to display errors
function displaySystemErrors() {
    ErrorHandler::getInstance()->displayErrors();
}

// Function to clear errors
function clearSystemErrors() {
    ErrorHandler::getInstance()->clearErrors();
}
?>
