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
    
    <style>
        /* Inline styles to ensure login page looks correct */
        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--secondary);
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: var(--primary);
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .login-header {
            padding: 30px 30px 20px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }
        
        .login-logo {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }
        
        .login-logo span {
            color: var(--accent);
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            color: var(--text);
            margin: 0;
            font-weight: 600;
        }
        
        .login-form-container {
            padding: 30px;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: var(--text);
        }
        
        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 12px;
            color: var(--text);
            opacity: 0.7;
        }
        
        .login-form input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--secondary);
            color: var(--text);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .login-form input:focus {
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 0 2px rgba(249, 75, 31, 0.2);
        }
        
        .btn-login {
            width: 100%;
            background-color: var(--accent);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            background-color: #ff6b43;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .login-footer {
            padding: 20px;
            text-align: center;
            border-top: 1px solid var(--border-color);
        }
        
        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .login-footer a:hover {
            color: #ff6b43;
            text-decoration: none;
        }
        
        /* Flash Messages */
        .flash-message {
            padding: 15px;
            margin: 0 30px 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .flash-message i {
            font-size: 20px;
        }
        
        .flash-message-content {
            flex: 1;
        }
        
        .flash-message.error {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: #f44336;
        }
        
        .flash-message.success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: #4caf50;
        }
        
        .flash-message.info {
            background-color: rgba(33, 150, 243, 0.1);
            border: 1px solid rgba(33, 150, 243, 0.3);
            color: #2196f3;
        }
        
        .flash-message.warning {
            background-color: rgba(255, 152, 0, 0.1);
            border: 1px solid rgba(255, 152, 0, 0.3);
            color: #ff9800;
        }
    </style>
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
                        <input type="text" id="username" name="username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" required>
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