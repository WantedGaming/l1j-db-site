<?php
/**
 * Authentication functions for L1J Database Website
 */

/**
 * Register a new user
 * @param string $username
 * @param string $email
 * @param string $password
 * @return bool
 */
function registerUser($username, $email, $password) {
    // Validate inputs
    if (empty($username) || empty($email) || empty($password)) {
        return false;
    }
    
    // Check if username or email already exists
    $db = Database::getInstance();
    $existingUser = $db->getRow("SELECT * FROM admin_users WHERE username = :username OR email = :email", [
        'username' => $username,
        'email' => $email
    ]);
    
    if ($existingUser) {
        return false; // User already exists
    }
    
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $result = $db->insert('admin_users', [
        'username' => $username,
        'email' => $email,
        'password' => $hashedPassword,
        'role' => 'user',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    
    return $result > 0;
}

/**
 * Login a user
 * @param string $username
 * @param string $password
 * @return bool
 */
function loginUser($username, $password) {
    // Check default admin login first
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        // Set session variables for built-in admin
        $_SESSION['user_id'] = 0;
        $_SESSION['username'] = 'admin';
        $_SESSION['user_role'] = 'admin';
        $_SESSION['last_activity'] = time();
        return true;
    }
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        return false;
    }
    
    // Check database users
    $db = Database::getInstance();
    $user = $db->getRow("SELECT * FROM admin_users WHERE username = :username", [
        'username' => $username
    ]);
    
    if (!$user) {
        return false; // User not found
    }
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        return false; // Password doesn't match
    }
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Update last login time
    $db->update('admin_users', [
        'last_login' => date('Y-m-d H:i:s')
    ], 'id = :id', [
        'id' => $user['id']
    ]);
    
    return true;
}

/**
 * Logout the current user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Check if user session is still valid
 * @return bool
 */
function checkSession() {
    // Check if user is logged in
    if (!isLoggedIn()) {
        return false;
    }
    
    // Check if session has expired
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        logoutUser();
        return false;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    
    return true;
}

/**
 * Require user to be logged in, redirect if not
 * @param string $redirectUrl
 */
function requireLogin($redirectUrl = '/admin/login.php') {
    if (!checkSession()) {
        setFlash('error', 'Please log in to access this page.');
        redirect($redirectUrl);
    }
}

/**
 * Require user to be an admin, redirect if not
 * @param string $redirectUrl
 */
function requireAdmin($redirectUrl = '/admin/login.php') {
    requireLogin($redirectUrl);
    
    if (!isAdmin()) {
        setFlash('error', 'You need administrative privileges to access this page.');
        redirect($redirectUrl);
    }
}

/**
 * Create admin_users table if it doesn't exist
 * This is called during installation
 */
function createUsersTable() {
    $db = Database::getInstance();
    
    // Check if table exists
    $tableExists = $db->getColumn("SHOW TABLES LIKE 'admin_users'");
    
    if (!$tableExists) {
        // Create table
        $sql = "CREATE TABLE admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            role ENUM('admin', 'editor', 'user') NOT NULL DEFAULT 'user',
            created_at DATETIME NOT NULL,
            last_login DATETIME NULL
        )";
        
        $db->query($sql);
        
        // Create default admin user (should be changed in production)
        $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
        
        $db->insert('admin_users', [
            'username' => 'webadmin',
            'email' => ADMIN_EMAIL,
            'password' => $hashedPassword,
            'role' => 'admin',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return true;
    }
    
    return false;
}
