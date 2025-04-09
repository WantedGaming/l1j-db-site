<?php
/**
 * Admin Login Page for L1J Database Website
 */

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Get database instance
$db = Database::getInstance();

// Check if user is already logged in
if (isLoggedIn()) {
    redirect(SITE_URL . '/admin/');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        setFlash('error', 'Please enter both username and password.');
    } else {
        // Query the accounts table with the provided credentials
        $user = $db->getRow("SELECT login, password, access_level, banned FROM accounts WHERE login = ?", [$username]);
        
        if ($user) {
            // Check if the account is banned
            if ($user['banned'] != 0) {
                setFlash('error', 'This account has been banned.');
            }
            // Verify password - assuming plain text passwords in the database
            else if ($user['password'] === $password) {
                // Check if user has admin access (access_level = 1)
                if ($user['access_level'] != 1) {
                    setFlash('error', 'You do not have administrator privileges.');
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['login'];
                    $_SESSION['is_admin'] = true;
                    
                    // Update last active timestamp
                    // Using query() method instead of execute() since it seems execute() doesn't exist
                    $db->query("UPDATE accounts SET lastactive = NOW() WHERE login = ?", [$username]);
                    
                    // Redirect to admin dashboard
                    redirect(SITE_URL . '/admin/');
                }
            } else {
                setFlash('error', 'Invalid username or password.');
            }
        } else {
            setFlash('error', 'Invalid username or password.');
        }
    }
}

/**
 * Display flash messages with improved styling
 */
function displayLoginFlash() {
    if (isset($_SESSION['flash']) && !empty($_SESSION['flash'])) {
        foreach ($_SESSION['flash'] as $type => $message) {
            $icon = '';
            switch ($type) {
                case 'error':
                    $icon = '<i class="fas fa-exclamation-circle"></i>';
                    break;
                case 'success':
                    $icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'info':
                    $icon = '<i class="fas fa-info-circle"></i>';
                    break;
                case 'warning':
                    $icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
            }
            
            echo '<div class="flash-message ' . $type . '">';
            echo $icon;
            echo '<div class="flash-message-content">' . $message . '</div>';
            echo '</div>';
        }
        
        // Clear flash messages after displaying
        $_SESSION['flash'] = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                L1J <span>Database</span>
            </div>
            <h1>Admin Login</h1>
        </div>
        
        <?php displayLoginFlash(); ?>
        
        <div class="login-form-container">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </div>
            </form>
        </div>
        
        <div class="login-footer">
            <a href="<?php echo SITE_URL; ?>">
                <i class="fas fa-arrow-left"></i> Back to Website
            </a>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>