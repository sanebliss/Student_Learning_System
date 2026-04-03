<?php
/**
 * Application Configuration
 */

// Session settings
ini_set('session.use_strict_mode', 1);
ini_set('session.use_only_cookies', 1);

// Set session cookie parameters
session_set_cookie_params([
    'lifetime' => 3600, // 1 hour
    'path' => '/',
    'domain' => '', // Empty for localhost
    'secure' => false, // Set to true in production with HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Application paths
define('APP_ROOT', dirname(dirname(__FILE__)));
define('UPLOADS_DIR', APP_ROOT . '/uploads/');
define('INCLUDES_DIR', APP_ROOT . '/includes/');

// Site URL (for local XAMPP)
define('SITE_URL', 'http://localhost/learning_system/');

// Application name
define('APP_NAME', 'Child Learning & Progress Management System');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_TEACHER', 'teacher');
define('ROLE_STUDENT', 'student');
define('ROLE_PARENT', 'parent');

// Activity types
define('ACTIVITY_TYPE_PDF', 'pdf');
define('ACTIVITY_TYPE_QUIZ', 'quiz');

// Assignment status
define('STATUS_NOT_STARTED', 'not_started');
define('STATUS_IN_PROGRESS', 'in_progress');
define('STATUS_COMPLETED', 'completed');

// Pagination
define('RECORDS_PER_PAGE', 10);

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_FILE_TYPES', ['application/pdf']);

?>
