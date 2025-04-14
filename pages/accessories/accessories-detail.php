<?php
/**
 * Accessories detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Accessory Details';
$pageDescription = 'Detailed information about accessories in L1J Remastered, including stats, enchant bonuses, and drop locations.';

// Include header
require_once '../../includes/header.php';

// Include armor functions
require_once '../../includes/armor-functions.php';

// Get database instance
$db = Database::getInstance();

// Get accessory ID from URL
$accessoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to accessories list
if($accessoryId <= 0) {
    header('Location: accessories-list.php');
    exit;
}

// Get accessory details
$query = "SELECT a.*, 
          SUBSTRING_INDEX(a.material, '(', 1) as material_name
          FROM armor a 
          WHERE a.item_id = ? AND a.type IN ('BELT', 'RING_2', 'EARRING', 'RON', 'BADGE', 'PENDANT', 'RING', 'AMULET', 'SENTENCE')";
$accessory = $db->getRow($query, [$accessoryId]);

// If accessory not found, show error
if(!$accessory) {
    echo '<div class="container"><div class="error-message">Accessory not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Check if this accessory is part of a set
$setQuery = "SELECT * FROM armor_set WHERE id = ?";
$accessorySet = $db->getRow($setQuery, [$accessory['Set_Id']]);

// Get monsters that drop this accessory with spawn information
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.lvl as monster_level, 
              n.spriteId as monster_sprite_id, n.npcid,
              s.count, s.locx, s.locy, s.randomx, s.randomy,
              s.min_respawn_delay as respawnDelay,
              s.locx1, s.locy1, s.locx2, s.locy2,
              m.locationname as map_name, m.mapid, m.pngId
              FROM droplist d
              JOIN npc n ON d.mobId = n.npcid
              LEFT JOIN spawnlist s ON n.npcid = s.npc_templateid
              LEFT JOIN mapids m ON s.mapid = m.mapid
              WHERE d.itemId = ? AND n.impl LIKE '%L1Monster%'
              ORDER BY d.chance DESC";
$dropMonsters = $db->getRows($dropQuery, [$accessoryId]);

// Set page title to accessory name
$pageTitle = $accessory['desc_en'];

?>

<!-- Hero Section with Transparent Accessory Image -->
<div class="weapon-hero">
    <div class="weapon-hero-image-container">
        <img src="<?= SITE_URL ?>/assets/img/items/<?= $accessory['iconId'] ?>.png" 
             alt="<?= htmlspecialchars($accessory['desc_en']) ?>" 
             class="weapon-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
    </div>
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars($accessory['desc_en']) ?></h1>
        <p><?= htmlspecialchars(formatArmorType($accessory['type'])) ?>, <?= htmlspecialchars(formatMaterial($accessory['material_name'])) ?></p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/accessories/accessories-list.php">Accessories</a> &raquo;
        <span><?= htmlspecialchars($accessory['desc_en']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= SITE_URL ?>/assets/img/items/<?= $accessory['iconId'] ?>.png" 
                     alt="<?= htmlspecialchars($accessory['desc_en']) ?>" 
                     class="detail-image-large"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
            </div>
        </div>

        <!-- Basic Information Card -->
        <div class="card">
            <div class="card-header">
                <h2>Basic Information</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <tr>
                        <th>Type</th>
                        <td><?= htmlspecialchars(formatArmorType($accessory['type'])) ?></td>
                    </tr>
                    <tr>
                        <th>Material</th>
                        <td><?= htmlspecialchars(formatMaterial($accessory['material_name'])) ?></td>
                    </tr>
                    <?php if ($accessory['ac'] != 0): ?>
                    <tr>
                        <th>AC</th>
                        <td><?= $accessory['ac'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($accessory['ac_sub'] != 0): ?>
                    <tr>
                        <th>AC (Sub)</th>
                        <td><?= $accessory['ac_sub'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Safe</th>
                        <td>+<?= $accessory['safenchant'] ?></td>
                    </tr>
                    <?php if ($accessory['weight'] > 0): ?>
                    <tr>
                        <th>Weight</th>
                        <td><?= $accessory['weight'] / 1000 ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($accessory['m_def'] > 0): ?>
                    <tr>
                        <th>Magic Defense</th>
                        <td><?= $accessory['m_def'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($accessory['damage_reduction'] > 0): ?>
                    <tr>
                        <th>Damage Reduction</th>
                        <td><?= $accessory['damage_reduction'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($accessory['MagicDamageReduction'] > 0): ?>
                    <tr>
                        <th>Magic Damage Reduction</th>
                        <td><?= $accessory['MagicDamageReduction'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($accessory['haste_item'] > 0): ?>
                    <tr>
                        <th>Haste</th>
                        <td>Yes</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($accessory['carryBonus'] > 0): ?>
                    <tr>
                        <th>Carry Bonus</th>
                        <td>+<?= $accessory['carryBonus'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Level</th>
                        <td>
                            <?php if ($accessory['min_lvl'] > 0 && $accessory['max_lvl'] > 0): ?>
                                <?= $accessory['min_lvl'] ?> - <?= $accessory['max_lvl'] ?>
                            <?php elseif ($accessory['min_lvl'] > 0): ?>
                                Min: <?= $accessory['min_lvl'] ?>
                            <?php elseif ($accessory['max_lvl'] > 0): ?>
                                Max: <?= $accessory['max_lvl'] ?>
                            <?php else: ?>
                                None
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Requirements Section -->
    <?php 
    $hasClassRequirements = $accessory['use_royal'] || $accessory['use_knight'] || $accessory['use_elf'] || 
                           $accessory['use_mage'] || $accessory['use_darkelf'] || $accessory['use_dragonknight'] || 
                           $accessory['use_illusionist'] || $accessory['use_warrior'] || $accessory['use_fencer'] || 
                           $accessory['use_lancer'];
    if ($hasClassRequirements): 
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Class</h2>
        </div>
        <div class="card-content">
            <div class="requirements-grid">
                <!-- Class Requirements -->
                <div class="requirement-item">
                    <div class="requirements-grid">
                        <?php if ($accessory['use_royal']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Royal
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_knight']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Knight
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_elf']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Elf
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_mage']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Mage
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_darkelf']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Dark Elf
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_dragonknight']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Dragon Knight
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_illusionist']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Illusionist
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_warrior']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Warrior
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_fencer']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Fencer
                        </span>
                        <?php endif; ?>
                        <?php if ($accessory['use_lancer']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Lancer
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Set Information -->
    <?php if ($accessorySet): ?>
    <div class="card">
        <div class="card-header">
            <h2>Set Information</h2>
        </div>
        <div class="card-content">
            <h3><?= htmlspecialchars($accessorySet['name']) ?></h3>
            <p><?= htmlspecialchars($accessorySet['description']) ?></p>
            
            <?php if ($accessorySet['effects']): ?>
            <div class="set-effects">
                <h4>Set Effects:</h4>
                <ul>
                    <?php foreach (explode("\n", $accessorySet['effects']) as $effect): ?>
                        <li><?= htmlspecialchars($effect) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Item Properties Section -->
    <?php
    // Define all properties grouped by category
    $property_groups = [
        'Traits' => [
            'haste_item' => 'Haste',
            'bless' => 'Blessed'
        ],
        'Restrictions' => [
            'trade' => 'Tradable',
            'retrieve' => 'Retrievable',
            'specialretrieve' => 'Special Retrieve',
            'cant_delete' => 'Cannot Delete',
            'cant_sell' => 'Cannot Sell'
        ]
    ];

    // Check which groups have active properties
    $active_groups = [];
    foreach ($property_groups as $group_name => $properties) {
        foreach ($properties as $field => $label) {
            if (!empty($accessory[$field])) {
                $active_groups[$group_name] = $properties;
                break;
            }
        }
    }

    $show_grid = count($active_groups) > 1;
    ?>

    <?php if (!empty($active_groups)): ?>
    <div class="<?= $show_grid ? 'detail-content-grid' : '' ?>">
        <?php foreach ($active_groups as $group_name => $properties): ?>
        <div class="card" style="<?= !$show_grid ? 'grid-column: 1 / -1;' : '' ?>">
            <div class="card-header">
                <h2><?= $group_name ?></h2>
            </div>
            <div class="card-content">
                <div class="requirements-grid">
                    <?php foreach ($properties as $field => $label): ?>
                    <div class="requirement-switch">
                        <span class="requirement-switch-icon <?= !empty($accessory[$field]) ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                            <?= !empty($accessory[$field]) ? '✓' : '✗' ?>
                        </span>
                        <?= $label ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Combat Stats Section -->
    <?php
    $hasCombatStats = (isset($accessory['str']) && $accessory['str'] != 0) || 
                      (isset($accessory['dex']) && $accessory['dex'] != 0) || 
                      (isset($accessory['con']) && $accessory['con'] != 0) || 
                      (isset($accessory['wis']) && $accessory['wis'] != 0) || 
                      (isset($accessory['int']) && $accessory['int'] != 0) || 
                      (isset($accessory['cha']) && $accessory['cha'] != 0);
    if ($hasCombatStats):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Combat Stats</h2>
        </div>
        <div class="card-content">
            <div class="stat-grid">
                <?php if (isset($accessory['str']) && $accessory['str'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">STR</span>
                    <span class="stat-value"><?= $accessory['str'] > 0 ? '+' . $accessory['str'] : $accessory['str'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['dex']) && $accessory['dex'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">DEX</span>
                    <span class="stat-value"><?= $accessory['dex'] > 0 ? '+' . $accessory['dex'] : $accessory['dex'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['con']) && $accessory['con'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">CON</span>
                    <span class="stat-value"><?= $accessory['con'] > 0 ? '+' . $accessory['con'] : $accessory['con'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['wis']) && $accessory['wis'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">WIS</span>
                    <span class="stat-value"><?= $accessory['wis'] > 0 ? '+' . $accessory['wis'] : $accessory['wis'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['int']) && $accessory['int'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">INT</span>
                    <span class="stat-value"><?= $accessory['int'] > 0 ? '+' . $accessory['int'] : $accessory['int'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['cha']) && $accessory['cha'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">CHA</span>
                    <span class="stat-value"><?= $accessory['cha'] > 0 ? '+' . $accessory['cha'] : $accessory['cha'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Bonuses Section -->
    <?php
    $hasBonuses = (isset($accessory['hp']) && $accessory['hp'] != 0) || 
                  (isset($accessory['mp']) && $accessory['mp'] != 0) || 
                  (isset($accessory['hpr']) && $accessory['hpr'] != 0) || 
                  (isset($accessory['mpr']) && $accessory['mpr'] != 0) || 
                  (isset($accessory['sp']) && $accessory['sp'] != 0);
    if ($hasBonuses):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Bonuses</h2>
        </div>
        <div class="card-content">
            <div class="bonus-grid">
                <?php if (isset($accessory['hp']) && $accessory['hp'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">HP</span>
                    <span class="bonus-value"><?= $accessory['hp'] > 0 ? '+' . $accessory['hp'] : $accessory['hp'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['mp']) && $accessory['mp'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">MP</span>
                    <span class="bonus-value"><?= $accessory['mp'] > 0 ? '+' . $accessory['mp'] : $accessory['mp'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['hpr']) && $accessory['hpr'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">HP Regen</span>
                    <span class="bonus-value"><?= $accessory['hpr'] > 0 ? '+' . $accessory['hpr'] : $accessory['hpr'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['mpr']) && $accessory['mpr'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">MP Regen</span>
                    <span class="bonus-value"><?= $accessory['mpr'] > 0 ? '+' . $accessory['mpr'] : $accessory['mpr'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['sp']) && $accessory['sp'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">SP</span>
                    <span class="bonus-value"><?= $accessory['sp'] > 0 ? '+' . $accessory['sp'] : $accessory['sp'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Resistances Section -->
    <?php
    $hasResistances = (isset($accessory['mr']) && $accessory['mr'] != 0) || 
                      (isset($accessory['fire_resist']) && $accessory['fire_resist'] != 0) || 
                      (isset($accessory['water_resist']) && $accessory['water_resist'] != 0) || 
                      (isset($accessory['wind_resist']) && $accessory['wind_resist'] != 0) || 
                      (isset($accessory['earth_resist']) && $accessory['earth_resist'] != 0);
    if ($hasResistances):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Resistances</h2>
        </div>
        <div class="card-content">
            <div class="resistance-grid">
                <?php if (isset($accessory['mr']) && $accessory['mr'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Magic</span>
                    <span class="resistance-value"><?= $accessory['mr'] > 0 ? '+' . $accessory['mr'] : $accessory['mr'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['fire_resist']) && $accessory['fire_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Fire</span>
                    <span class="resistance-value"><?= $accessory['fire_resist'] > 0 ? '+' . $accessory['fire_resist'] : $accessory['fire_resist'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['water_resist']) && $accessory['water_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Water</span>
                    <span class="resistance-value"><?= $accessory['water_resist'] > 0 ? '+' . $accessory['water_resist'] : $accessory['water_resist'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['wind_resist']) && $accessory['wind_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Wind</span>
                    <span class="resistance-value"><?= $accessory['wind_resist'] > 0 ? '+' . $accessory['wind_resist'] : $accessory['wind_resist'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($accessory['earth_resist']) && $accessory['earth_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Earth</span>
                    <span class="resistance-value"><?= $accessory['earth_resist'] > 0 ? '+' . $accessory['earth_resist'] : $accessory['earth_resist'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Drop Locations Section -->
    <?php if (!empty($dropMonsters)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Drop By</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Monster</th>
                        <th>Level</th>
                        <th>Drop Chance</th>
                        <th>Max</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($dropMonsters as $drop): ?>
                    <tr>
                        <td>
                            <div class="monster-list-item">
                                <img src="<?= SITE_URL ?>/assets/img/monsters/ms<?= $drop['monster_sprite_id'] ?>.png" 
                                     alt="<?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?>"
                                     class="monster-sprite"
                                     onerror="this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
                                <a href="<?= SITE_URL ?>/pages/monsters/detail.php?id=<?= $drop['npcid'] ?>">
                                    <?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= $drop['monster_level'] ?></td>
                        <td><?= formatDropChance($drop['chance']) ?></td>
                        <td><?= $drop['max'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Spawn Locations Section -->
    <?php if (!empty($dropMonsters)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Spawn Locations</h2>
        </div>
        <div class="card-content">
            <div class="spawn-locations-grid">
                <?php foreach($dropMonsters as $drop):
                    if (!empty($drop['map_name'])):
                ?>
                    <div class="spawn-location-card">
                        <div class="spawn-location-header">
                            <h3><?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?></h3>
                            <div class="spawn-meta">
                                <span class="spawn-count"><?= $drop['count'] ?> spawns</span>
                                <?php if($drop['respawnDelay'] > 0): ?>
                                    <span class="respawn-time">
                                        <i class="fas fa-clock"></i>
                                        <?= floor($drop['respawnDelay'] / 60) ?>m <?= $drop['respawnDelay'] % 60 ?>s
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="spawn-location-content">
                            <div class="map-container">
                                <img src="<?= get_map_image($drop['pngId']) ?>" 
                                     alt="<?= htmlspecialchars($drop['map_name']) ?>" 
                                     class="map-image">
                                <?php
                                // If we have a spawn area (locx1/locy1 to locx2/locy2)
                                if ($drop['locx1'] > 0 && $drop['locy1'] > 0 && $drop['locx2'] > 0 && $drop['locy2'] > 0): ?>
                                    <div class="spawn-area" style="
                                        left: <?= ($drop['locx1'] / 32768) * 100 ?>%;
                                        top: <?= ($drop['locy1'] / 32768) * 100 ?>%;
                                        width: <?= (($drop['locx2'] - $drop['locx1']) / 32768) * 100 ?>%;
                                        height: <?= (($drop['locy2'] - $drop['locy1']) / 32768) * 100 ?>%;">
                                        <div class="spawn-area-label">Spawn Area</div>
                                    </div>
                                <?php else: ?>
                                    <!-- Single point spawn with random range -->
                                    <div class="spawn-marker" style="
                                        left: <?= ($drop['locx'] / 32768) * 100 ?>%;
                                        top: <?= ($drop['locy'] / 32768) * 100 ?>%;">
                                        <div class="spawn-point"></div>
                                        <?php if ($drop['randomx'] > 0 || $drop['randomy'] > 0): ?>
                                            <div class="spawn-range" style="
                                                width: <?= ($drop['randomx'] * 2 / 32768) * 100 ?>%;
                                                height: <?= ($drop['randomy'] * 2 / 32768) * 100 ?>%;">
                                                <div class="spawn-range-label">Random Range</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="location-details">
                                <div class="location-name"><?= htmlspecialchars($drop['map_name']) ?></div>
                                <?php if ($drop['locx'] && $drop['locy']): ?>
                                    <div class="coordinates">
                                        <span class="coordinate">X: <?= $drop['locx'] ?></span>
                                        <span class="coordinate">Y: <?= $drop['locy'] ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?> 