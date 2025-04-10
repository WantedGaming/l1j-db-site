<?php
/**
 * Admin Header for L1J Database Website
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(SITE_URL . '/admin/login.php');
}

// Get username from session
$username = $_SESSION['user_id'] ?? 'Admin';

// Get current page for active nav highlighting
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'Admin Panel'; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-layout">
        <!-- Admin Header -->
        <header class="admin-header">
            <div class="admin-header-container">
                <div class="admin-brand">
                    <a href="<?php echo SITE_URL; ?>/admin/" class="admin-logo">
                        L1J <span>Database</span>
                    </a>
                </div>
                
                <nav class="admin-nav">
						<ul class="admin-nav-links">
							<li class="admin-nav-item">
								<a href="<?php echo SITE_URL; ?>/" class="admin-nav-link back-to-site">
							<i class="fas fa-globe me-1"></i> View Site
							</a>
						</li>
                        <li class="admin-nav-item">
                            <a href="<?php echo SITE_URL; ?>/admin/" class="admin-nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>">
                                Dashboard
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="<?php echo SITE_URL; ?>/admin/items/" class="admin-nav-link <?php echo strpos($currentPage, 'item') !== false ? 'active' : ''; ?>">
                                Items
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="<?php echo SITE_URL; ?>/admin/monsters/" class="admin-nav-link <?php echo strpos($currentPage, 'monster') !== false ? 'active' : ''; ?>">
                                Monsters
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="<?php echo SITE_URL; ?>/admin/skills/" class="admin-nav-link <?php echo strpos($currentPage, 'skill') !== false ? 'active' : ''; ?>">
                                Skills
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="<?php echo SITE_URL; ?>/admin/maps/" class="admin-nav-link <?php echo strpos($currentPage, 'map') !== false ? 'active' : ''; ?>">
                                Maps
                            </a>
                        </li>
                        <li class="admin-nav-item">
                            <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="admin-nav-link <?php echo $currentPage === 'settings' ? 'active' : ''; ?>">
                                Settings
                            </a>
                        </li>
                    </ul>
                    
                    <div class="admin-user">
                        <div class="admin-user-avatar">
                            <?php echo substr($username, 0, 1); ?>
                        </div>
                        <div class="admin-user-info">
                            <div class="admin-user-name"><?php echo $username; ?></div>
                            <div class="admin-user-role">Administrator</div>
                        </div>
                        <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="admin-nav-link" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </nav>
            </div>
        </header>
        
        <!-- Main Content -->
        <main class="admin-content">
            <?php displayFlash(); ?>