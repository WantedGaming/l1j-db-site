<?php
/**
 * Homepage for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Home';
$pageDescription = 'Welcome to L1J Remastered Database - Your comprehensive resource for items, monsters, skills, and more.';

// Include header
require_once 'includes/header.php';
?>

<?php

// Get database instance
$db = Database::getInstance();

// Get stats
$weaponCount = $db->getColumn("SELECT COUNT(*) FROM weapon");
$armorCount = $db->getColumn("SELECT COUNT(*) FROM armor");
$etcItemCount = $db->getColumn("SELECT COUNT(*) FROM etcitem");
$monsterCount = $db->getColumn("SELECT COUNT(*) FROM npc WHERE impl LIKE '%L1Monster%'");
$skillCount = $db->getColumn("SELECT COUNT(*) FROM skills");
$mapCount = $db->getColumn("SELECT COUNT(*) FROM mapids");

// Get recent updates (placeholder - you'd need to implement a updates tracking table)
$recentUpdates = [
    [
        'type' => 'Item',
        'name' => 'Sword of Destruction',
        'date' => '2023-10-15'
    ],
    [
        'type' => 'Monster',
        'name' => 'Balrog',
        'date' => '2023-10-14'
    ],
    [
        'type' => 'Map',
        'name' => 'Forgotten Island',
        'date' => '2023-10-12'
    ]
];
?>

<!-- Hero Section with Search -->
<section class="hero">
    <div class="container">
        <h1>L1J Remastered Database</h1>
        <p>Your comprehensive resource for L1J Remastered MMORPG data</p>
        
        <!-- Clean Search Form -->
        <div class="search-container">
            <form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search items, monsters, skills...">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
    </div>
</section>

<div class="container">
    <!-- Quick Links Section -->
    <section class="quick-links-section">
        <div class="link-cards">
            <a href="<?php echo SITE_URL; ?>/pages/items/" class="link-card">
                <img src="assets/img/placeholders/scrolls.png" alt="Scrolls Image" class="link-image">
                <h3>Scrolls</h3>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/pages/monsters/" class="link-card">
                <img src="assets/img/placeholders/quest.png" alt="Quest Image" class="link-image">
                <h3>Quests</h3>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/pages/skills/" class="link-card">
                <img src="assets/img/placeholders/amulets.png" alt="Amulets Image" class="link-image">
                <h3>Amulets</h3>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/pages/maps/" class="link-card">
                <img src="assets/img/placeholders/material.png" alt="Material Image" class="link-image">
                <h3>Material</h3>
            </a>
        </div>
    </section>

    <!-- Database Stats Section -->
    <section class="stats-section">
        <h2>Database Statistics</h2>
        
        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-count"><?php echo number_format($weaponCount + $armorCount + $etcItemCount); ?></div>
                <div class="stat-label">Total Items</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-count"><?php echo number_format($monsterCount); ?></div>
                <div class="stat-label">Monsters</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-count"><?php echo number_format($skillCount); ?></div>
                <div class="stat-label">Skills</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-count"><?php echo number_format($mapCount); ?></div>
                <div class="stat-label">Maps</div>
            </div>
        </div>
    </section>

    <!-- Popular Resources Section -->
    <section class="resources-section">
        <h2>Game Resources</h2>
        
        <div class="resources-grid">
            <div class="resource-card">
                <h3>Weapons</h3>
                <img src="assets/img/placeholders/weapons.png" alt="Weapons image" class="card-image">
                <p>Browse all weapons including swords, daggers, axes, bows, staves and more.</p>
                <a href="<?php echo SITE_URL; ?>/pages/weapons/weapon-list.php" class="resource-link">View Weapons</a>
            </div>
            
            <div class="resource-card">
                <h3>Armor</h3>
                <img src="assets/img/placeholders/armor.png" alt="Armor image" class="card-image">
                <p>Explore armor sets, helmets, gloves, boots and other protective gear.</p>
                <a href="<?php echo SITE_URL; ?>/pages/armor/armor-list.php" class="resource-link">View Armor</a>
            </div>
            
            <div class="resource-card">
                <h3>Accessory</h3>
                <img src="assets/img/placeholders/accessory.png" alt="Accessory image" class="card-image">
                <p>Find information necklace, rings, and other jewelry you can utilize.</p>
                <a href="<?php echo SITE_URL; ?>/pages/monsters/bosses.php" class="resource-link">View Accessories</a>
            </div>
            
            <div class="resource-card">
                <h3>Monsters</h3>
                <img src="assets/img/placeholders/monsters.png" alt="Monster image" class="card-image">
                <p>Find information on all bosses, their locations, drops, and strategies.</p>
                <a href="<?php echo SITE_URL; ?>/pages/monsters/index.php" class="resource-link">View Monsters</a>
            </div>
            
            <div class="resource-card">
                <h3>Maps</h3>
                <img src="assets/img/placeholders/maps.png" alt="Map image" class="card-image">
                <p>Where to hunt, hunting grounds, and monster spawn locations.</p>
                <a href="<?php echo SITE_URL; ?>/pages/maps/index.php" class="resource-link">View Maps</a>
            </div>
            
            <div class="resource-card">
                <h3>Dolls</h3>
                <img src="assets/img/placeholders/dolls.png" alt="Dolls image" class="card-image">
                <p>Your ingame compaion to help you along your adventures.</p>
                <a href="<?php echo SITE_URL; ?>/pages/maps/world.php" class="resource-link">View Dolls</a>
            </div>
        </div>
    </section>

    <!-- Main Content Sections -->
    <div class="main-content-grid">
        <!-- Recent Updates Section -->
        <section class="updates-section content-card">
            <h2>Recent Updates</h2>
            
            <div class="updates-list">
                <?php foreach ($recentUpdates as $update): ?>
                    <div class="update-item">
                        <div class="update-icon">
                            <?php if ($update['type'] === 'Item'): ?>
                                <i class="fas fa-sword-alt"></i>
                            <?php elseif ($update['type'] === 'Monster'): ?>
                                <i class="fas fa-dragon"></i>
                            <?php elseif ($update['type'] === 'Map'): ?>
                                <i class="fas fa-map"></i>
                            <?php else: ?>
                                <i class="fas fa-star"></i>
                            <?php endif; ?>
                        </div>
                        <div class="update-content">
                            <div class="update-title">
                                <?php echo htmlspecialchars($update['name']); ?>
                            </div>
                            <div class="update-meta">
                                <span class="update-type"><?php echo htmlspecialchars($update['type']); ?></span>
                                <span class="update-date"><?php echo formatDate($update['date']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="section-action">
                <a href="<?php echo SITE_URL; ?>/pages/updates.php" class="btn">View All Updates</a>
            </div>
        </section>
        
        <!-- Getting Started Section -->
        <section class="getting-started-section content-card">
            <h2>Getting Started</h2>
            
            <div class="guide-steps">
                <div class="guide-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h3>Explore the Database</h3>
                        <p>Browse through items, monsters, skills, and maps to discover game content.</p>
                    </div>
                </div>
                
                <div class="guide-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h3>Search for Specific Data</h3>
                        <p>Use the search function to quickly find exactly what you're looking for.</p>
                    </div>
                </div>
                
                <div class="guide-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h3>Read Game Guides</h3>
                        <p>Access comprehensive guides to enhance your gameplay experience.</p>
                    </div>
                </div>
            </div>
            
            <div class="section-action">
                <a href="<?php echo SITE_URL; ?>/pages/guides/beginners.php" class="btn">Beginner's Guide</a>
            </div>
        </section>
    </div>
</div>

<?php
// Include footer
require_once 'includes/footer.php';
?>
