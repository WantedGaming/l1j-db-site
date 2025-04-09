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

// Get monsters that drop this weapon
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.lvl as monster_level, 
              n.spriteId as monster_sprite_id
              FROM droplist d
              JOIN npc n ON d.mobId = n.npcid
              WHERE d.itemId = ? AND n.impl LIKE '%L1Monster%'
              ORDER BY d.chance DESC";
$dropMonsters = $db->getRows($dropQuery, [$weaponId]);

// Set page title to weapon name
$pageTitle = $weapon['desc_en'];

// Function to format drop chance (imported from functions.php)
function formatDropChance($chance) {
    $percentage = ($chance / 10000) * 100;
    return $percentage < 0.01 ? '< 0.01%' : number_format($percentage, 2) . '%';
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
    <div class="card">
        <div class="card-header">
            <h2>Class</h2>
        </div>
        <div class="card-content">
            <div class="requirements-grid">
                <!-- Class Requirements -->
                <div class="requirement-item">
                    <div class="requirements-grid">
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_royal'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_royal'] ? '✓' : '✗' ?>
                            </span>
                            Royal
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_knight'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_knight'] ? '✓' : '✗' ?>
                            </span>
                            Knight
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_elf'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_elf'] ? '✓' : '✗' ?>
                            </span>
                            Elf
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_mage'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_mage'] ? '✓' : '✗' ?>
                            </span>
                            Mage
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_darkelf'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_darkelf'] ? '✓' : '✗' ?>
                            </span>
                            Dark Elf
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_dragonknight'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_dragonknight'] ? '✓' : '✗' ?>
                            </span>
                            Dragon Knight
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_illusionist'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_illusionist'] ? '✓' : '✗' ?>
                            </span>
                            Illusionist
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_warrior'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_warrior'] ? '✓' : '✗' ?>
                            </span>
                            Warrior
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_fencer'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_fencer'] ? '✓' : '✗' ?>
                            </span>
                            Fencer
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $weapon['use_lancer'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $weapon['use_lancer'] ? '✓' : '✗' ?>
                            </span>
                            Lancer
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
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
    
    <!-- Stats Bonuses Section -->
    <?php
    $hasBonuses = $weapon['add_str'] != 0 || $weapon['add_con'] != 0 || $weapon['add_dex'] != 0 ||
                 $weapon['add_int'] != 0 || $weapon['add_wis'] != 0 || $weapon['add_cha'] != 0 ||
                 $weapon['add_hp'] != 0 || $weapon['add_mp'] != 0 || $weapon['add_hpr'] != 0 ||
                 $weapon['add_mpr'] != 0 || $weapon['add_sp'] != 0;
    
    if ($hasBonuses):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Bonuses</h2>
        </div>
        <div class="card-content">
            <div class="stat-grid">
                <?php if ($weapon['add_str'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">STR</div>
                    <div class="stat-value"><?= $weapon['add_str'] > 0 ? '+' . $weapon['add_str'] : $weapon['add_str'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_con'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">CON</div>
                    <div class="stat-value"><?= $weapon['add_con'] > 0 ? '+' . $weapon['add_con'] : $weapon['add_con'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_dex'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">DEX</div>
                    <div class="stat-value"><?= $weapon['add_dex'] > 0 ? '+' . $weapon['add_dex'] : $weapon['add_dex'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_int'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">INT</div>
                    <div class="stat-value"><?= $weapon['add_int'] > 0 ? '+' . $weapon['add_int'] : $weapon['add_int'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_wis'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">WIS</div>
                    <div class="stat-value"><?= $weapon['add_wis'] > 0 ? '+' . $weapon['add_wis'] : $weapon['add_wis'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_cha'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">CHA</div>
                    <div class="stat-value"><?= $weapon['add_cha'] > 0 ? '+' . $weapon['add_cha'] : $weapon['add_cha'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_hp'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">HP</div>
                    <div class="stat-value"><?= $weapon['add_hp'] > 0 ? '+' . $weapon['add_hp'] : $weapon['add_hp'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_mp'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">MP</div>
                    <div class="stat-value"><?= $weapon['add_mp'] > 0 ? '+' . $weapon['add_mp'] : $weapon['add_mp'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_hpr'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">HP Regen</div>
                    <div class="stat-value"><?= $weapon['add_hpr'] > 0 ? '+' . $weapon['add_hpr'] : $weapon['add_hpr'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_mpr'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">MP Regen</div>
                    <div class="stat-value"><?= $weapon['add_mpr'] > 0 ? '+' . $weapon['add_mpr'] : $weapon['add_mpr'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($weapon['add_sp'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">SP</div>
                    <div class="stat-value"><?= $weapon['add_sp'] > 0 ? '+' . $weapon['add_sp'] : $weapon['add_sp'] ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
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
                        <th>Min Count</th>
                        <th>Max Count</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($dropMonsters as $drop): ?>
                    <tr>
                        <td>
                            <a href="<?= SITE_URL ?>/pages/monsters/monster-detail.php?id=<?= $drop['mobId'] ?>">
                                <?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?>
                            </a>
                        </td>
                        <td><?= $drop['monster_level'] ?></td>
                        <td><?= formatDropChance($drop['chance']) ?></td>
                        <td><?= $drop['min'] ?></td>
                        <td><?= $drop['max'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
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