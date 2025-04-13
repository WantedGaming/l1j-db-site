<?php
/**
 * Weapon detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Weapon Details';
$pageDescription = 'Detailed information about weapons in L1J Remastered, including stats, enchant bonuses, and drop locations.';

// Include header
require_once '../../includes/header.php';

// Include weapons functions
require_once '../../includes/weapons-functions.php';

// Get database instance
$db = Database::getInstance();

// Get weapon ID from URL
$weaponId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to weapons list
if($weaponId <= 0) {
    header('Location: weapons.php');
    exit;
}

// Get weapon details
$query = "SELECT w.*, 
          SUBSTRING_INDEX(w.material, '(', 1) as material_name
          FROM weapon w 
          WHERE w.item_id = ?";
$weapon = $db->getRow($query, [$weaponId]);

// If weapon not found, show error
if(!$weapon) {
    echo '<div class="container"><div class="error-message">Weapon not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get weapon damage modifiers if any
$damageQuery = "SELECT * FROM weapon_damege WHERE item_id = ?";
$weaponDamage = $db->getRow($damageQuery, [$weaponId]);

// Get weapon skills if any
$skillQuery = "SELECT ws.*, ss.name as skill_name, ss.desc_kr 
               FROM weapon_skill ws 
               LEFT JOIN skills ss ON ws.skill_id = ss.skill_id
               WHERE ws.weapon_id = ?";
$weaponSkills = $db->getRows($skillQuery, [$weaponId]);

// Get weapon skill models if any
$modelQuery = "SELECT * FROM weapon_skill_model WHERE item_id = ?";
$weaponSkillModels = $db->getRows($modelQuery, [$weaponId]);

// Get spell definition if any
$spellDefQuery = "SELECT * FROM weapon_skill_spell_def WHERE id = ?";
$weaponSpellDef = $db->getRow($spellDefQuery, [$weaponId]);

// Get monsters that drop this weapon with spawn information
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.lvl as monster_level, 
              n.spriteId as monster_sprite_id, n.npcid,
              GROUP_CONCAT(DISTINCT CONCAT(m.locationname, ':', s.count, ':', m.pngId, ':', s.locx, ':', s.locy) SEPARATOR '|') as spawn_data
              FROM droplist d
              JOIN npc n ON d.mobId = n.npcid
              LEFT JOIN spawnlist s ON n.npcid = s.npc_templateid
              LEFT JOIN mapids m ON s.mapid = m.mapid
              WHERE d.itemId = ? AND n.impl LIKE '%L1Monster%'
              GROUP BY d.mobId, d.itemId, d.chance, d.min, d.max
              ORDER BY d.chance DESC";
$dropMonsters = $db->getRows($dropQuery, [$weaponId]);

// Set page title to weapon name
$pageTitle = $weapon['desc_en'];

// Function to format drop chance (imported from functions.php)
function formatDropChance($chance) {
    $percentage = ($chance / 100000) * 100;
    return $percentage < 0.01 ? '< 0.01%' : number_format($percentage, 2) . '%';
}

// Function to get map image path
function get_map_image($pngId) {
    if ($pngId > 0) {
        $base_path = dirname(dirname(dirname(__FILE__))); // Go up three levels to get to root
        
        // Try jpeg format
        $image_path = "/assets/img/maps/{$pngId}.jpeg";
        $server_path = $base_path . $image_path;
        
        // Try png format if jpeg doesn't exist
        if (!file_exists($server_path)) {
            $image_path = "/assets/img/maps/{$pngId}.png";
            $server_path = $base_path . $image_path;
        }
        
        // Try jpg format if png doesn't exist
        if (!file_exists($server_path)) {
            $image_path = "/assets/img/maps/{$pngId}.jpg";
            $server_path = $base_path . $image_path;
        }
        
        // If any of the formats exist, return the URL
        if (file_exists($server_path)) {
            return SITE_URL . $image_path;
        }
    }
    
    return SITE_URL . '/assets/img/maps/default.jpg';
}

?>

<!-- Hero Section with Transparent Weapon Image -->
<div class="weapon-hero">
    <div class="weapon-hero-image-container">
        <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
             alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
             class="weapon-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
    </div>
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars($weapon['desc_en']) ?></h1>
        <p><?= htmlspecialchars(ucwords(strtolower($weapon['type']))) ?>, <?= htmlspecialchars(formatMaterial($weapon['material_name'])) ?></p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/items/weapons.php">Weapons</a> &raquo;
        <span><?= htmlspecialchars($weapon['desc_en']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
                     alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
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
                <td><?= htmlspecialchars(ucwords(strtolower($weapon['type']))) ?></td>
            </tr>
            <tr>
                <th>Material</th>
                <td><?= htmlspecialchars(formatMaterial($weapon['material_name'])) ?></td>
            </tr>
            <tr>
                <th>Damage (S/L)</th>
                <td><?= $weapon['dmg_small'] ?>/<?= $weapon['dmg_large'] ?></td>
            </tr>
            <tr>
                <th>Safe</th>
                <td>+<?= $weapon['safenchant'] ?></td>
            </tr>
            <tr>
                <th>Hit</th>
                <td><?= $weapon['hitmodifier'] > 0 ? '+' . $weapon['hitmodifier'] : $weapon['hitmodifier'] ?></td>
            </tr>
            <tr>
                <th>Damage</th>
                <td><?= $weapon['dmgmodifier'] > 0 ? '+' . $weapon['dmgmodifier'] : $weapon['dmgmodifier'] ?></td>
            </tr>
            <?php if ($weapon['double_dmg_chance'] > 0): ?>
            <tr>
                <th>Double Damage Chance</th>
                <td><?= $weapon['double_dmg_chance'] ?>%</td>
            </tr>
            <?php endif; ?>
            <?php if ($weapon['haste_item'] > 0): ?>
            <tr>
                <th>Haste</th>
                <td>Yes</td>
            </tr>
            <?php endif; ?>
            <?php if ($weaponDamage): ?>
            <tr>
                <th>Additional Damage</th>
                <td>+<?= $weaponDamage['addDamege'] ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Level</th>
                <td>
                    <?php if ($weapon['min_lvl'] > 0 && $weapon['max_lvl'] > 0): ?>
                        <?= $weapon['min_lvl'] ?> - <?= $weapon['max_lvl'] ?>
                    <?php elseif ($weapon['min_lvl'] > 0): ?>
                        Min: <?= $weapon['min_lvl'] ?>
                    <?php elseif ($weapon['max_lvl'] > 0): ?>
                        Max: <?= $weapon['max_lvl'] ?>
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
    $hasClassRequirements = $weapon['use_royal'] || $weapon['use_knight'] || $weapon['use_elf'] || 
                           $weapon['use_mage'] || $weapon['use_darkelf'] || $weapon['use_dragonknight'] || 
                           $weapon['use_illusionist'] || $weapon['use_warrior'] || $weapon['use_fencer'] || 
                           $weapon['use_lancer'];
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
                        <?php if ($weapon['use_royal']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Royal
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_knight']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Knight
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_elf']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Elf
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_mage']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Mage
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_darkelf']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Dark Elf
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_dragonknight']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Dragon Knight
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_illusionist']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Illusionist
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_warrior']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Warrior
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_fencer']): ?>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            Fencer
                        </span>
                        <?php endif; ?>
                        <?php if ($weapon['use_lancer']): ?>
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

    <!-- Combat Stats Section -->
    <?php
    $hasCombatStats = (isset($weapon['str']) && $weapon['str'] != 0) || 
                      (isset($weapon['dex']) && $weapon['dex'] != 0) || 
                      (isset($weapon['con']) && $weapon['con'] != 0) || 
                      (isset($weapon['wis']) && $weapon['wis'] != 0) || 
                      (isset($weapon['int']) && $weapon['int'] != 0) || 
                      (isset($weapon['cha']) && $weapon['cha'] != 0);
    if ($hasCombatStats):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Combat Stats</h2>
        </div>
        <div class="card-content">
            <div class="stat-grid">
                <?php if (isset($weapon['str']) && $weapon['str'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">STR</span>
                    <span class="stat-value"><?= $weapon['str'] > 0 ? '+' . $weapon['str'] : $weapon['str'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['dex']) && $weapon['dex'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">DEX</span>
                    <span class="stat-value"><?= $weapon['dex'] > 0 ? '+' . $weapon['dex'] : $weapon['dex'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['con']) && $weapon['con'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">CON</span>
                    <span class="stat-value"><?= $weapon['con'] > 0 ? '+' . $weapon['con'] : $weapon['con'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['wis']) && $weapon['wis'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">WIS</span>
                    <span class="stat-value"><?= $weapon['wis'] > 0 ? '+' . $weapon['wis'] : $weapon['wis'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['int']) && $weapon['int'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">INT</span>
                    <span class="stat-value"><?= $weapon['int'] > 0 ? '+' . $weapon['int'] : $weapon['int'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['cha']) && $weapon['cha'] != 0): ?>
                <div class="stat-item">
                    <span class="stat-label">CHA</span>
                    <span class="stat-value"><?= $weapon['cha'] > 0 ? '+' . $weapon['cha'] : $weapon['cha'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Bonuses Section -->
    <?php
    $hasBonuses = (isset($weapon['hp']) && $weapon['hp'] != 0) || 
                  (isset($weapon['mp']) && $weapon['mp'] != 0) || 
                  (isset($weapon['hpr']) && $weapon['hpr'] != 0) || 
                  (isset($weapon['mpr']) && $weapon['mpr'] != 0) || 
                  (isset($weapon['sp']) && $weapon['sp'] != 0);
    if ($hasBonuses):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Bonuses</h2>
        </div>
        <div class="card-content">
            <div class="bonus-grid">
                <?php if (isset($weapon['hp']) && $weapon['hp'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">HP</span>
                    <span class="bonus-value"><?= $weapon['hp'] > 0 ? '+' . $weapon['hp'] : $weapon['hp'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['mp']) && $weapon['mp'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">MP</span>
                    <span class="bonus-value"><?= $weapon['mp'] > 0 ? '+' . $weapon['mp'] : $weapon['mp'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['hpr']) && $weapon['hpr'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">HP Regen</span>
                    <span class="bonus-value"><?= $weapon['hpr'] > 0 ? '+' . $weapon['hpr'] : $weapon['hpr'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['mpr']) && $weapon['mpr'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">MP Regen</span>
                    <span class="bonus-value"><?= $weapon['mpr'] > 0 ? '+' . $weapon['mpr'] : $weapon['mpr'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['sp']) && $weapon['sp'] != 0): ?>
                <div class="bonus-item">
                    <span class="bonus-label">SP</span>
                    <span class="bonus-value"><?= $weapon['sp'] > 0 ? '+' . $weapon['sp'] : $weapon['sp'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resistances Section -->
    <?php
    $hasResistances = (isset($weapon['mr']) && $weapon['mr'] != 0) || 
                      (isset($weapon['fire_resist']) && $weapon['fire_resist'] != 0) || 
                      (isset($weapon['water_resist']) && $weapon['water_resist'] != 0) || 
                      (isset($weapon['wind_resist']) && $weapon['wind_resist'] != 0) || 
                      (isset($weapon['earth_resist']) && $weapon['earth_resist'] != 0);
    if ($hasResistances):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Resistances</h2>
        </div>
        <div class="card-content">
            <div class="resistance-grid">
                <?php if (isset($weapon['mr']) && $weapon['mr'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Magic</span>
                    <span class="resistance-value"><?= $weapon['mr'] > 0 ? '+' . $weapon['mr'] : $weapon['mr'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['fire_resist']) && $weapon['fire_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Fire</span>
                    <span class="resistance-value"><?= $weapon['fire_resist'] > 0 ? '+' . $weapon['fire_resist'] : $weapon['fire_resist'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['water_resist']) && $weapon['water_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Water</span>
                    <span class="resistance-value"><?= $weapon['water_resist'] > 0 ? '+' . $weapon['water_resist'] : $weapon['water_resist'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['wind_resist']) && $weapon['wind_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Wind</span>
                    <span class="resistance-value"><?= $weapon['wind_resist'] > 0 ? '+' . $weapon['wind_resist'] : $weapon['wind_resist'] ?></span>
                </div>
                <?php endif; ?>
                <?php if (isset($weapon['earth_resist']) && $weapon['earth_resist'] != 0): ?>
                <div class="resistance-item">
                    <span class="resistance-label">Earth</span>
                    <span class="resistance-value"><?= $weapon['earth_resist'] > 0 ? '+' . $weapon['earth_resist'] : $weapon['earth_resist'] ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Item Properties Section -->
    <?php
    // Define all properties grouped by category
    $property_groups = [
        'Traits' => [
            'haste_item' => 'Haste',
            'canbedmg' => 'Can Be Damaged',
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
            if (!empty($weapon[$field])) {
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
                        <span class="requirement-switch-icon <?= !empty($weapon[$field]) ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                            <?= !empty($weapon[$field]) ? '✓' : '✗' ?>
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
    
    <!-- Weapon Skills Section -->
    <?php if (!empty($weaponSkills) || !empty($weaponSkillModels)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Weapon Skills</h2>
        </div>
        <div class="card-content">
            <?php if (!empty($weaponSkills)): ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Skill</th>
                        <th>Probability</th>
                        <th>Attack Type</th>
                        <th>Effect</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($weaponSkills as $skill): ?>
                    <tr>
                        <td><?= !empty($skill['skill_name']) ? htmlspecialchars($skill['skill_name']) : 'Custom Skill' ?></td>
                        <td><?= $skill['probability'] ?>%</td>
                        <td><?= $skill['attackType'] ?></td>
                        <td>
                            <?php
                            $effects = [];
                            
                            if ($skill['fix_damage'] > 0) {
                                $effects[] = "Fixed Damage: " . $skill['fix_damage'];
                            }
                            
                            if ($skill['random_damage'] > 0) {
                                $effects[] = "Random Damage: 1-" . $skill['random_damage'];
                            }
                            
                            if ($skill['skill_time'] > 0 && $skill['skill_id'] > 0) {
                                $effects[] = "Duration: " . $skill['skill_time'] . " seconds";
                            }
                            
                            if (!empty($skill['attr']) && $skill['attr'] != 'NONE') {
                                $effects[] = "Attribute: " . $skill['attr'];
                            }
                            
                            if ($skill['hpStill'] == 'true') {
                                $effects[] = "HP Steal: " . $skill['hpStillValue'] . " (" . $skill['hpStill_probabliity'] . "%)";
                            }
                            
                            if ($skill['mpStill'] == 'true') {
                                $effects[] = "MP Steal: " . $skill['mpStillValue'] . " (" . $skill['mpStill_probabliity'] . "%)";
                            }
                            
                            echo !empty($effects) ? implode("<br>", $effects) : 'No additional effects';
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <?php if (!empty($weaponSkillModels)): ?>
            <h3>Skill Models</h3>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Probability</th>
                        <th>Effect</th>
                        <th>Attribute</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($weaponSkillModels as $model): ?>
                    <tr>
                        <td><?= htmlspecialchars($model['procType'] ?? 'Normal') ?></td>
                        <td>
                            Base: <?= $model['default_prob'] ?>%
                            <?= $model['enchant_prob'] > 0 ? '<br>Enchant: +' . $model['enchant_prob'] . '%' : '' ?>
                        </td>
                        <td>
                            <?php
                            $modelEffects = [];
                            
                            if ($model['min_val'] > 0 || $model['max_val'] > 0) {
                                $modelEffects[] = "Damage: " . $model['min_val'] . "-" . $model['max_val'];
                            }
                            
                            if ($model['effect'] > 0) {
                                $modelEffects[] = "Effect ID: " . $model['effect'];
                            }
                            
                            if ($model['is_sp_val'] == 'true') {
                                $modelEffects[] = "SP Effect: Yes";
                            }
                            
                            echo !empty($modelEffects) ? implode("<br>", $modelEffects) : 'Standard effect';
                            ?>
                        </td>
                        <td><?= $model['attr_type'] != 'NONE' ? $model['attr_type'] : 'None' ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <?php if ($weaponSpellDef): ?>
            <h3>Spell Defense</h3>
            <table class="detail-table">
                <tr>
                    <th>Defense Damage</th>
                    <td><?= $weaponSpellDef['def_dmg'] ?></td>
                </tr>
                <tr>
                    <th>Defense Ratio</th>
                    <td><?= $weaponSpellDef['def_ratio'] ?>%</td>
                </tr>
            </table>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Drop By Section -->
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
            <?php foreach($dropMonsters as $drop):
                if (!empty($drop['spawn_data'])):
                    $locations = explode('|', $drop['spawn_data']);
                    foreach($locations as $location):
                        list($mapName, $count, $pngId, $locX, $locY) = explode(':', $location);
            ?>
                <div class="spawn-location-card">
                    <div class="spawn-location-header">
                        <h3><?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?></h3>
                        <span class="spawn-count"><?= $count ?> spawns</span>
                    </div>
                    <div class="spawn-location-content">
                        <div class="map-preview">
                            <img src="<?= get_map_image($pngId) ?>" alt="<?= htmlspecialchars($mapName) ?>" class="map-image">
                            <div class="spawn-marker" style="left: <?= ($locX / 32768) * 100 ?>%; top: <?= ($locY / 32768) * 100 ?>%"></div>
                        </div>
                        <div class="spawn-details">
                            <p class="map-name"><?= htmlspecialchars($mapName) ?></p>
                            <p class="coordinates">Coordinates: <?= $locX ?>, <?= $locY ?></p>
                        </div>
                    </div>
                </div>
            <?php 
                    endforeach;
                endif;
            endforeach; 
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Additional Notes -->
    <?php if (!empty($weapon['note'])): ?>
    <div class="card">
        <div class="card-header">
            <h2>Additional Notes</h2>
        </div>
        <div class="card-content">
            <div class="description">
                <?= nl2br(htmlspecialchars($weapon['note'])) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>