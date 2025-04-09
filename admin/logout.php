<?php
/**
 * Logout Script for L1J Database Website
 */

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Clear all session variables
$_SESSION = array();

// If a session cookie is used, destroy that too
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Set a flash message
setFlash('success', 'You have been successfully logged out.');

// Redirect to login page
redirect(SITE_URL . '/admin/login.php');
?>
