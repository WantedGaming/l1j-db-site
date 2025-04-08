<?php
/**
 * Configuration file for L1J Database Website
 */

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');         // Default XAMPP username
define('DB_PASS', '');             // Default XAMPP password
define('DB_NAME', 'l1j_remastered');

// Site configuration
define('SITE_NAME', 'L1J Remastered Database');
define('SITE_URL', 'http://localhost/l1j-db-site');
define('ADMIN_EMAIL', 'admin@example.com');

// Path configuration
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDE_PATH', ROOT_PATH . '/includes');
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads');

// Default pagination limit
define('DEFAULT_LIMIT', 20);

// Session timeout (in seconds)
define('SESSION_TIMEOUT', 1800); // 30 minutes

// Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'password'); // Change this in production!

// Debug mode (set to false in production)
define('DEBUG_MODE', true);

// Default timezone
date_default_timezone_set('UTC');

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
