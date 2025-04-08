<?php
/**
 * Weapon detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Weapon Details';
$pageDescription = 'Detailed information about weapons in L1J Remastered, including stats, enchant bonuses, and drop locations.';

// Include header
require_once '../../includes/header.php';

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
          SUBSTRING_BEFORE(w.material, '(') as material_name
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
$weaponSkills = $db->getResults($skillQuery, [$weaponId]);

// Get weapon skill models if any
$modelQuery = "SELECT * FROM weapon_skill_model WHERE item_id = ?";
$weaponSkillModels = $db->getResults($modelQuery, [$weaponId]);

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
$dropMonsters = $db->getResults($dropQuery, [$weaponId]);

// Helper function to format material name
function formatMaterial($material) {
    // Remove Korean part if exists
    $material = trim($material);
    $material = strtoupper($material);
    return $material;
}

// Helper function to get badge class based on item grade
function getGradeBadgeClass($grade) {
    switch($grade) {
        case 'ONLY':
            return 'badge-only';
        case 'MYTH':
            return 'badge-myth';
        case 'LEGEND':
            return 'badge-legend';
        case 'HERO':
            return 'badge-hero';
        case 'RARE':
            return 'badge-rare';
        default:
            return 'badge-normal';
    }
}

// Helper function to get a formatted drop chance
function formatDropChance($chance) {
    if ($chance >= 10000) {
        return '100%';
    } else if ($chance >= 1000) {
        return round($chance / 100, 2) . '%';
    } else if ($chance >= 100) {
        return round($chance / 100, 2) . '%';
    } else {
        return round($chance / 100, 3) . '%';
    }
}

// Set page title to weapon name
$pageTitle = $weapon['desc_en'];
?>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/items/weapons.php">Weapons</a> &raquo;
        <span><?= htmlspecialchars($weapon['desc_en']) ?></span>
    </div>

    <div class="detail-header">
        <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
             alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
             class="detail-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
        
        <div class="detail-title-section">
            <h1 class="detail-title">
                <?= htmlspecialchars($weapon['desc_en']) ?>
                <span class="badge <?= getGradeBadgeClass($weapon['itemGrade']) ?>"><?= $weapon['itemGrade'] ?></span>
            </h1>
            <div class="detail-meta">
                <?= htmlspecialchars($weapon['type']) ?> | 
                <?= htmlspecialchars(formatMaterial($weapon['material_name'])) ?> |
                Weight: <?= $weapon['weight'] ?>
            </div>
        </div>
    </div>

    <div class="detail-content-grid">
        <!-- Main Stats Section -->
        <div class="card">
            <div class="card-header">
                <h2>Basic Information</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <tr>
                        <th>Type</th>
                        <td><?= htmlspecialchars($weapon['type']) ?></td>
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
                        <th>Safe Enchant</th>
                        <td>+<?= $weapon['safenchant'] ?></td>
                    </tr>
                    <tr>
                        <th>Hit Modifier</th>
                        <td><?= $weapon['hitmodifier'] > 0 ? '+' . $weapon['hitmodifier'] : $weapon['hitmodifier'] ?></td>
                    </tr>
                    <tr>
                        <th>Damage Modifier</th>
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
                </table>
            </div>
        </div>

        <!-- Requirements Section -->
        <div class="card">
            <div class="card-header">
                <h2>Requirements</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
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
                    <tr>
                        <th>Class Restrictions</th>
                        <td>
                            <?php
                            $classes = [];
                            if ($weapon['use_royal'] == 1) $classes[] = 'Royal';
                            if ($weapon['use_knight'] == 1) $classes[] = 'Knight';
                            if ($weapon['use_elf'] == 1) $classes[] = 'Elf';
                            if ($weapon['use_mage'] == 1) $classes[] = 'Mage';
                            if ($weapon['use_darkelf'] == 1) $classes[] = 'Dark Elf';
                            if ($weapon['use_dragonknight'] == 1) $classes[] = 'Dragon Knight';
                            if ($weapon['use_illusionist'] == 1) $classes[] = 'Illusionist';
                            if ($weapon['use_warrior'] == 1) $classes[] = 'Warrior';
                            if ($weapon['use_fencer'] == 1) $classes[] = 'Fencer';
                            if ($weapon['use_lancer'] == 1) $classes[] = 'Lancer';
                            
                            if (empty($classes)) {
                                echo 'None';
                            } else {
                                echo implode(', ', $classes);
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

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
            <h2>Stat Bonuses</h2>
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
            <h2>Drop Locations</h2>
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