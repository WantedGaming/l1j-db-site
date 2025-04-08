<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Check if user is logged in
requireLogin(SITE_URL . '/admin/login.php');

// Define variable to indicate we're in admin section
$isAdmin = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - Admin' : 'Admin Dashboard'; ?> | <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php if (isset($extraStyles)): ?>
        <?php foreach ($extraStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <div class="admin-sidebar-header">
                <div class="admin-sidebar-logo">
                    L1J <span>Admin</span>
                </div>
            </div>
            
            <ul class="admin-menu">
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/" class="admin-menu-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php' && dirname($_SERVER['PHP_SELF']) == '/admin') ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt admin-menu-icon"></i> Dashboard
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/items/" class="admin-menu-link <?php echo strContains($_SERVER['PHP_SELF'], '/admin/items/') ? 'active' : ''; ?>">
                        <i class="fas fa-sword admin-menu-icon"></i> Items
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/monsters/" class="admin-menu-link <?php echo strContains($_SERVER['PHP_SELF'], '/admin/monsters/') ? 'active' : ''; ?>">
                        <i class="fas fa-dragon admin-menu-icon"></i> Monsters
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/skills/" class="admin-menu-link <?php echo strContains($_SERVER['PHP_SELF'], '/admin/skills/') ? 'active' : ''; ?>">
                        <i class="fas fa-magic admin-menu-icon"></i> Skills
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/characters/" class="admin-menu-link <?php echo strContains($_SERVER['PHP_SELF'], '/admin/characters/') ? 'active' : ''; ?>">
                        <i class="fas fa-user admin-menu-icon"></i> Characters
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/users/" class="admin-menu-link <?php echo strContains($_SERVER['PHP_SELF'], '/admin/users/') ? 'active' : ''; ?>">
                        <i class="fas fa-users admin-menu-icon"></i> Users
                    </a>
                </li>
                <li class="admin-menu-item">
                    <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="admin-menu-link">
                        <i class="fas fa-sign-out-alt admin-menu-icon"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- Main Content -->
        <div class="admin-content">
            <div class="admin-header">
                <div class="admin-toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </div>
                
                <h1 class="admin-page-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                
                <div class="admin-user-dropdown">
                    <div class="admin-user-info">
                        <div class="admin-user-avatar">
                            <img src="<?php echo SITE_URL; ?>/assets/img/avatar.png" alt="User Avatar">
                        </div>
                        <span class="admin-user-name"><?php echo $_SESSION['username']; ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    
                    <div class="admin-dropdown-content">
                        <a href="<?php echo SITE_URL; ?>/admin/profile.php">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="<?php echo SITE_URL; ?>/admin/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <?php displayFlash(); ?>
					