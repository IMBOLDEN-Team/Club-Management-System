<html>
<?php
/**
 * Main Entry Point for Club Management System
 * Includes comprehensive error handling, security, and database components
 */

// Include the main configuration file
require_once __DIR__ . '/config/config.php';

// The system is now fully configured with:
// - Error handling
// - Security features
// - Database connection
// - Session management
// - Input validation

// Legacy compatibility - maintain the old $connect variable for existing code
// New code should use the $db instance or helper functions
$connect = $db->getConnection();

// Log successful system initialization
logSystemActivity('Page Loaded', 'Index page accessed successfully');
?>
</html>