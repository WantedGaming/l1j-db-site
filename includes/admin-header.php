<?php
/**
 * Admin Header for L1J Database Website
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Check if user is logged in and has admin access
if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    redirect(SITE_URL . '/admin/login.php');
}

// Get current page from URL
$currentPage = basename($_SERVER['PHP_SELF']);

// Set default page title if not provided
if (!isset($pageTitle)) {
    $pageTitle = 'Admin Dashboard';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - L1J Database Admin</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <!-- Top Navigation Bar -->
    <header class="admin-header">
        <div class="admin-header-logo">
            <a href="<?php echo SITE_URL; ?>/admin/">
                L1J <span>Database</span>
            </a>
        </div>
        
        <div class="admin-header-actions">
            <div class="dropdown">
                <button class="dropdown-toggle">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu">
                    <a href="<?php echo SITE_URL; ?>/admin/profile.php">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/settings.php">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="<?php echo SITE_URL; ?>/admin/logout.php">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <div class="admin-wrapper">
        <!-- Sidebar Menu -->
        <aside class="admin-sidebar">
            <nav class="admin-nav">
                <ul class="admin-menu">
                    <li class="admin-menu-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/index.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-header">Content Management</li>
                    
                    <li class="admin-menu-item <?php echo strpos($currentPage, 'items') !== false ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/items/index.php">
                            <i class="fas fa-sword"></i>
                            <span>Items</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-item <?php echo strpos($currentPage, 'monsters') !== false ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/monsters/index.php">
                            <i class="fas fa-dragon"></i>
                            <span>Monsters</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-item <?php echo strpos($currentPage, 'skills') !== false ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/skills/index.php">
                            <i class="fas fa-magic"></i>
                            <span>Skills</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-item <?php echo strpos($currentPage, 'maps') !== false ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/maps/index.php">
                            <i class="fas fa-map"></i>
                            <span>Maps</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-header">System</li>
                    
                    <li class="admin-menu-item <?php echo strpos($currentPage, 'users') !== false ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/users/index.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-item <?php echo $currentPage === 'backup.php' ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/backup.php">
                            <i class="fas fa-database"></i>
                            <span>Backup</span>
                        </a>
                    </li>
                    
                    <li class="admin-menu-item <?php echo $currentPage === 'settings.php' ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/settings.php">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <div class="admin-content">
            <?php displayFlash(); ?>
