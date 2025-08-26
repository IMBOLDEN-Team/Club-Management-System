# Club Management System - Error Handling & Security

This directory contains the comprehensive error handling, security, and database management system for the Club Management System.

## 🚀 Quick Start

To use the error handling system in your PHP files, simply include the main configuration:

```php
require_once 'config/config.php';
```

This will automatically:
- Initialize error handling
- Set up security features
- Establish database connection
- Start session management
- Validate user permissions

## 📁 File Structure

```
config/
├── config.php          # Main configuration file
├── error_handler.php   # Comprehensive error handling
├── security.php        # Security and validation
├── database.php        # Database connection wrapper
├── status_check.php    # System status checker
└── README.md          # This documentation
```

## 🛡️ Error Handling System

### Features

- **Automatic Error Capture**: Catches PHP errors, exceptions, and fatal errors
- **User-Friendly Messages**: Shows appropriate messages to users based on error type
- **Developer Debug Mode**: Detailed error information for developers
- **Error Logging**: Comprehensive logging to `logs/error.log`
- **Session-Based Error Storage**: Errors stored in session for display
- **Custom Error Types**: Support for custom error scenarios

### Usage

#### Basic Error Handling

```php
// Errors are automatically caught and handled
// No additional code needed for basic error handling
```

#### Custom Errors

```php
// Throw custom system errors
throwSystemError("User not found", "Validation Error");

// Check if there are errors
if (hasSystemErrors()) {
    displaySystemErrors();
}

// Clear errors
clearSystemErrors();
```

#### Error Display

```php
// Display errors in admin/debug areas
displaySystemErrors();

// Check for errors
if (hasSystemErrors()) {
    // Handle errors
}
```

### Error Types

- **PHP Errors**: E_ERROR, E_WARNING, E_NOTICE, etc.
- **Exceptions**: Uncaught exceptions
- **Fatal Errors**: Parse errors, core errors
- **Database Errors**: Connection and query failures
- **Custom Errors**: Application-specific errors

## 🔒 Security Features

### Input Validation

```php
// Sanitize and validate input
$email = sanitize_input($_POST['email'], 'email');
$age = sanitize_input($_POST['age'], 'int');
$name = sanitize_input($_POST['name'], 'string', ['max_length' => 50]);

// Validate file uploads
if (validateFileUpload($_FILES['document'], ['application/pdf'], 5 * 1024 * 1024)) {
    // Process file
}
```

### Authentication & Authorization

```php
// Require authentication
require_auth();

// Require specific role
require_role('admin');
require_role('clubber');
require_role('student');

// Check permissions
if (has_permission('admin')) {
    // Admin-only code
}
```

### CSRF Protection

```php
// Get CSRF token for forms
$token = get_csrf_token();

// Verify token on form submission
if (verify_csrf_token($_POST['csrf_token'])) {
    // Process form
}
```

### Password Security

```php
// Hash passwords
$hashedPassword = hash_password($password);

// Verify passwords
if (verify_password($password, $hashedPassword)) {
    // Login successful
}

// Check if password needs rehashing
if (password_needs_rehash($hash)) {
    $newHash = hash_password($password);
}
```

## 🗄️ Database Management

### Connection

```php
// Get database instance
$db = Database::getInstance();

// Get connection (legacy compatibility)
$connect = $db->getConnection();
```

### Query Execution

```php
// Execute queries with parameters
$result = db_query("SELECT * FROM users WHERE id = ?", [$userId]);

// Get single row
$user = db_query_one("SELECT * FROM users WHERE id = ?", [$userId]);

// Get all rows
$users = db_query_all("SELECT * FROM users");

// Insert data
$insertId = db_insert("INSERT INTO users (name, email) VALUES (?, ?)", [$name, $email]);

// Update/Delete
$affectedRows = db_execute("UPDATE users SET status = ? WHERE id = ?", ['active', $userId]);
```

### Transactions

```php
// Begin transaction
db_begin_transaction();

try {
    // Multiple database operations
    db_execute("UPDATE accounts SET balance = balance - ? WHERE id = ?", [$amount, $fromId]);
    db_execute("UPDATE accounts SET balance = balance + ? WHERE id = ?", [$amount, $toId]);
    
    // Commit transaction
    db_commit();
} catch (Exception $e) {
    // Rollback on error
    db_rollback();
    throw $e;
}
```

## 📊 Logging System

### Error Logs

- **Location**: `logs/error.log`
- **Format**: Structured error information with timestamps
- **Content**: Error type, message, file, line, user info, IP, URL

### Security Logs

- **Location**: `logs/security.log`
- **Content**: Security events, suspicious activity, authentication attempts

### Activity Logs

- **Location**: `logs/activity.log`
- **Content**: User actions, system events, page loads

### PHP Error Logs

- **Location**: `logs/php_errors.log`
- **Content**: Standard PHP error logging

## 🚧 Maintenance Mode

### Enable Maintenance Mode

```bash
# Create maintenance flag file
touch maintenance.flag
```

### Disable Maintenance Mode

```bash
# Remove maintenance flag file
rm maintenance.flag
```

### Maintenance Page

- **File**: `maintenance.php`
- **Features**: Professional maintenance page with progress indicators
- **Auto-refresh**: Checks system status every 30 seconds
- **Contact Information**: Support contact details

## ⚙️ Configuration

### Environment Settings

```php
// Set debug mode (development vs production)
define('DEBUG_MODE', false); // Set to true for development

// System constants
define('SYSTEM_NAME', 'Club Management System');
define('SYSTEM_VERSION', '1.0.0');
define('SESSION_TIMEOUT', 1800); // 30 minutes
```

### File Upload Settings

```php
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_DOCUMENT_TYPES', ['application/pdf', 'application/msword']);
```

## 🔧 Helper Functions

### System Functions

```php
// User authentication
isLoggedIn();
getCurrentUserRole();
getCurrentUserId();
isAdmin();
isClubber();
isStudent();

// System utilities
formatDate($date);
formatFileSize($bytes);
generateRandomString($length);
isValidEmail($email);
isValidURL($url);
sanitizeOutput($data);

// URL utilities
getBaseURL();
getCurrentURL();
isAjaxRequest();

// Response utilities
sendJsonResponse($data);
sendErrorResponse($message);
sendSuccessResponse($data, $message);
```

### Flash Messages

```php
// Set flash messages
redirectWithError('login.php', 'Invalid credentials');
redirectWithSuccess('dashboard.php', 'Login successful');

// Display flash messages
displayFlashMessages();
```

## 📝 Best Practices

### Error Handling

1. **Always use the error handler**: Include `config/config.php` in all files
2. **Use custom errors**: Throw specific errors for application logic
3. **Log important events**: Use `logSystemActivity()` for user actions
4. **Handle database errors**: Use prepared statements and error checking

### Security

1. **Validate all input**: Use `sanitize_input()` for user data
2. **Check permissions**: Use `require_role()` for protected areas
3. **Use CSRF tokens**: Include tokens in all forms
4. **Hash passwords**: Never store plain text passwords

### Database

1. **Use prepared statements**: Always use parameterized queries
2. **Handle transactions**: Use transactions for multiple operations
3. **Check results**: Always verify query success
4. **Use helper functions**: Use `db_query()`, `db_insert()`, etc.

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config/database.php`
   - Verify MySQL service is running
   - Check firewall settings

2. **Permission Denied**
   - Verify user role in session
   - Check `require_role()` calls
   - Ensure proper authentication

3. **Error Logs Not Working**
   - Check `logs/` directory permissions
   - Verify PHP error logging is enabled
   - Check disk space

4. **Maintenance Mode Stuck**
   - Remove `maintenance.flag` file
   - Check `config/status_check.php`
   - Verify file permissions

### Debug Mode

```php
// Enable debug mode for development
define('DEBUG_MODE', true);

// This will show detailed error information
// Only use in development environment
```

## 📞 Support

For technical support or questions about the error handling system:

- **Email**: support@yourdomain.com
- **Documentation**: Check this README and inline code comments
- **Logs**: Review log files in the `logs/` directory
- **Issues**: Report bugs through the system's issue tracker

## 🔄 Version History

- **v1.0.0**: Initial release with comprehensive error handling
- **Features**: Error handling, security, database management, logging
- **Compatibility**: PHP 7.4+, MySQL 5.7+

---

**Note**: This error handling system is designed to be robust and production-ready. Always test thoroughly in a development environment before deploying to production.
