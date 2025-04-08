<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Comprehensive database for the L1J Remastered MMORPG'; ?>">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo SITE_URL; ?>/assets/img/favicon.ico" type="image/x-icon">
    
    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?php
    // Include home.css specifically for the homepage
    $current_file = basename($_SERVER['PHP_SELF']);
    if ($current_file == 'index.php') {
        echo '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/home.css">';
    }
    ?>
    
    <?php if (isset($extraStyles)): ?>
        <?php foreach ($extraStyles as $style): ?>
            <link rel="stylesheet" href="<?php echo $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Header Section -->
    <header>
        <div class="container">
            <nav>
                <div class="logo">
                    <a href="<?php echo SITE_URL; ?>">
                        L1J <span>Database</span>
                    </a>
                </div>
                
                <ul class="nav-links">
                    <li><a href="<?php echo SITE_URL; ?>/pages/items/"><i class="fas fa-sword-alt"></i> Items</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/monsters/"><i class="fas fa-dragon"></i> Monsters</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/skills/"><i class="fas fa-hat-wizard"></i> Skills</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/maps/"><i class="fas fa-map-marked-alt"></i> Maps</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/pages/characters/"><i class="fas fa-user-alt"></i> Characters</a></li>
					<li><a href="<?php echo SITE_URL; ?>/admin/login.php">Admin Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/search.php"><i class="fas fa-search"></i> Search</a></li>
                </ul>
            </nav>
        </div>
    </header>   
    <main>
        <?php displayFlash(); ?>