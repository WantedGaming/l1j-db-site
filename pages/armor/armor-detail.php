<?php
/**
 * Armor detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Armor Details';
$pageDescription = 'Detailed information about armor in L1J Remastered, including stats, enchant bonuses, and drop locations.';

// Include header
require_once '../../includes/header.php';

// Include armor functions
require_once '../../includes/armor-functions.php';

// Get database instance
$db = Database::getInstance();

// Get armor ID from URL
$armorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to armor list
if($armorId <= 0) {
    header('Location: armor-list.php');
    exit;
}

// Get armor details
$query = "SELECT a.*, 
          SUBSTRING_INDEX(a.material, '(', 1) as material_name
          FROM armor a 
          WHERE a.item_id = ?";
$armor = $db->getRow($query, [$armorId]);

// If armor not found, show error
if(!$armor) {
    echo '<div class="container"><div class="error-message">Armor not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Check if this armor is part of a set
$setQuery = "SELECT * FROM armor_set WHERE id = ?";
$armorSet = $db->getRow($setQuery, [$armor['Set_Id']]);

// Get monsters that drop this armor
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.lvl as monster_level, 
              n.spriteId as monster_sprite_id
              FROM droplist d
              JOIN npc n ON d.mobId = n.npcid
              WHERE d.itemId = ? AND n.impl LIKE '%L1Monster%'
              ORDER BY d.chance DESC";
$dropMonsters = $db->getRows($dropQuery, [$armorId]);

// Set page title to armor name
$pageTitle = $armor['desc_en'];

// Function to format drop chance (imported from functions.php)
function formatDropChance($chance) {
    $percentage = ($chance / 10000) * 100;
    return $percentage < 0.01 ? '< 0.01%' : number_format($percentage, 2) . '%';
}

?>

<!-- Hero Section with Transparent Armor Image -->
<div class="weapon-hero">
    <div class="weapon-hero-image-container">
        <img src="<?= SITE_URL ?>/assets/img/items/<?= $armor['iconId'] ?>.png" 
             alt="<?= htmlspecialchars($armor['desc_en']) ?>" 
             class="weapon-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
    </div>
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars($armor['desc_en']) ?></h1>
        <p><?= htmlspecialchars(formatArmorType($armor['type'])) ?>, <?= htmlspecialchars(formatMaterial($armor['material_name'])) ?></p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/armor/armor-list.php">Armor</a> &raquo;
        <span><?= htmlspecialchars($armor['desc_en']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= SITE_URL ?>/assets/img/items/<?= $armor['iconId'] ?>.png" 
                     alt="<?= htmlspecialchars($armor['desc_en']) ?>" 
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
                        <td><?= htmlspecialchars(formatArmorType($armor['type'])) ?></td>
                    </tr>
                    <tr>
                        <th>Material</th>
                        <td><?= htmlspecialchars(formatMaterial($armor['material_name'])) ?></td>
                    </tr>
                    <tr>
                        <th>AC</th>
                        <td><?= $armor['ac'] ?></td>
                    </tr>
                    <?php if ($armor['ac_sub'] != 0): ?>
                    <tr>
                        <th>AC (Sub)</th>
                        <td><?= $armor['ac_sub'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Safe</th>
                        <td>+<?= $armor['safenchant'] ?></td>
                    </tr>
                    <?php if ($armor['weight'] > 0): ?>
                    <tr>
                        <th>Weight</th>
                        <td><?= $armor['weight'] / 1000 ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($armor['m_def'] > 0): ?>
                    <tr>
                        <th>Magic Defense</th>
                        <td><?= $armor['m_def'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($armor['damage_reduction'] > 0): ?>
                    <tr>
                        <th>Damage Reduction</th>
                        <td><?= $armor['damage_reduction'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($armor['MagicDamageReduction'] > 0): ?>
                    <tr>
                        <th>Magic Damage Reduction</th>
                        <td><?= $armor['MagicDamageReduction'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($armor['haste_item'] > 0): ?>
                    <tr>
                        <th>Haste</th>
                        <td>Yes</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($armor['carryBonus'] > 0): ?>
                    <tr>
                        <th>Carry Bonus</th>
                        <td>+<?= $armor['carryBonus'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Level</th>
                        <td>
                            <?php if ($armor['min_lvl'] > 0 && $armor['max_lvl'] > 0): ?>
                                <?= $armor['min_lvl'] ?> - <?= $armor['max_lvl'] ?>
                            <?php elseif ($armor['min_lvl'] > 0): ?>
                                Min: <?= $armor['min_lvl'] ?>
                            <?php elseif ($armor['max_lvl'] > 0): ?>
                                Max: <?= $armor['max_lvl'] ?>
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
                            <span class="requirement-switch-icon <?= $armor['use_royal'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_royal'] ? '✓' : '✗' ?>
                            </span>
                            Royal
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_knight'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_knight'] ? '✓' : '✗' ?>
                            </span>
                            Knight
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_elf'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_elf'] ? '✓' : '✗' ?>
                            </span>
                            Elf
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_mage'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_mage'] ? '✓' : '✗' ?>
                            </span>
                            Mage
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_darkelf'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_darkelf'] ? '✓' : '✗' ?>
                            </span>
                            Dark Elf
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_dragonknight'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_dragonknight'] ? '✓' : '✗' ?>
                            </span>
                            Dragon Knight
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_illusionist'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_illusionist'] ? '✓' : '✗' ?>
                            </span>
                            Illusionist
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_warrior'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_warrior'] ? '✓' : '✗' ?>
                            </span>
                            Warrior
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_fencer'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_fencer'] ? '✓' : '✗' ?>
                            </span>
                            Fencer
                        </span>
                        <span class="requirement-switch">
                            <span class="requirement-switch-icon <?= $armor['use_lancer'] ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                                <?= $armor['use_lancer'] ? '✓' : '✗' ?>
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
            if (!empty($armor[$field])) {
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
                        <span class="requirement-switch-icon <?= !empty($armor[$field]) ? 'requirement-switch-yes' : 'requirement-switch-no' ?>">
                            <?= !empty($armor[$field]) ? '✓' : '✗' ?>
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
    $hasBonuses = $armor['add_str'] != 0 || $armor['add_con'] != 0 || $armor['add_dex'] != 0 ||
                 $armor['add_int'] != 0 || $armor['add_wis'] != 0 || $armor['add_cha'] != 0 ||
                 $armor['add_hp'] != 0 || $armor['add_mp'] != 0 || $armor['add_hpr'] != 0 ||
                 $armor['add_mpr'] != 0 || $armor['add_sp'] != 0;
    
    if ($hasBonuses):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Bonuses</h2>
        </div>
        <div class="card-content">
            <div class="stat-grid">
                <?php if ($armor['add_str'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">STR</div>
                    <div class="stat-value"><?= $armor['add_str'] > 0 ? '+' . $armor['add_str'] : $armor['add_str'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_con'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">CON</div>
                    <div class="stat-value"><?= $armor['add_con'] > 0 ? '+' . $armor['add_con'] : $armor['add_con'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_dex'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">DEX</div>
                    <div class="stat-value"><?= $armor['add_dex'] > 0 ? '+' . $armor['add_dex'] : $armor['add_dex'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_int'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">INT</div>
                    <div class="stat-value"><?= $armor['add_int'] > 0 ? '+' . $armor['add_int'] : $armor['add_int'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_wis'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">WIS</div>
                    <div class="stat-value"><?= $armor['add_wis'] > 0 ? '+' . $armor['add_wis'] : $armor['add_wis'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_cha'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">CHA</div>
                    <div class="stat-value"><?= $armor['add_cha'] > 0 ? '+' . $armor['add_cha'] : $armor['add_cha'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_hp'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">HP</div>
                    <div class="stat-value"><?= $armor['add_hp'] > 0 ? '+' . $armor['add_hp'] : $armor['add_hp'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_mp'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">MP</div>
                    <div class="stat-value"><?= $armor['add_mp'] > 0 ? '+' . $armor['add_mp'] : $armor['add_mp'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_hpr'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">HP Regen</div>
                    <div class="stat-value"><?= $armor['add_hpr'] > 0 ? '+' . $armor['add_hpr'] : $armor['add_hpr'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_mpr'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">MP Regen</div>
                    <div class="stat-value"><?= $armor['add_mpr'] > 0 ? '+' . $armor['add_mpr'] : $armor['add_mpr'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['add_sp'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">SP</div>
                    <div class="stat-value"><?= $armor['add_sp'] > 0 ? '+' . $armor['add_sp'] : $armor['add_sp'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['hit_rate'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Hit Rate</div>
                    <div class="stat-value"><?= $armor['hit_rate'] > 0 ? '+' . $armor['hit_rate'] : $armor['hit_rate'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['dmg_rate'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Damage Rate</div>
                    <div class="stat-value"><?= $armor['dmg_rate'] > 0 ? '+' . $armor['dmg_rate'] : $armor['dmg_rate'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['bow_hit_rate'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Bow Hit Rate</div>
                    <div class="stat-value"><?= $armor['bow_hit_rate'] > 0 ? '+' . $armor['bow_hit_rate'] : $armor['bow_hit_rate'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['bow_dmg_rate'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Bow Damage Rate</div>
                    <div class="stat-value"><?= $armor['bow_dmg_rate'] > 0 ? '+' . $armor['bow_dmg_rate'] : $armor['bow_dmg_rate'] ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Resistances Section -->
    <?php
    $hasResistances = $armor['defense_water'] != 0 || $armor['defense_wind'] != 0 || 
                     $armor['defense_fire'] != 0 || $armor['defense_earth'] != 0 || 
                     $armor['attr_all'] != 0 || $armor['regist_stone'] != 0 ||
                     $armor['regist_sleep'] != 0 || $armor['regist_freeze'] != 0 ||
                     $armor['regist_blind'] != 0 || $armor['regist_skill'] != 0 ||
                     $armor['regist_spirit'] != 0 || $armor['regist_dragon'] != 0 ||
                     $armor['regist_fear'] != 0 || $armor['regist_all'] != 0;
    
    if ($hasResistances):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Resistances</h2>
        </div>
        <div class="card-content">
            <div class="stat-grid">
                <?php if ($armor['defense_water'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Water</div>
                    <div class="stat-value"><?= $armor['defense_water'] > 0 ? '+' . $armor['defense_water'] : $armor['defense_water'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['defense_wind'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Wind</div>
                    <div class="stat-value"><?= $armor['defense_wind'] > 0 ? '+' . $armor['defense_wind'] : $armor['defense_wind'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['defense_fire'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Fire</div>
                    <div class="stat-value"><?= $armor['defense_fire'] > 0 ? '+' . $armor['defense_fire'] : $armor['defense_fire'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['defense_earth'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Earth</div>
                    <div class="stat-value"><?= $armor['defense_earth'] > 0 ? '+' . $armor['defense_earth'] : $armor['defense_earth'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['attr_all'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">All Elements</div>
                    <div class="stat-value"><?= $armor['attr_all'] > 0 ? '+' . $armor['attr_all'] : $armor['attr_all'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_stone'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Stone</div>
                    <div class="stat-value"><?= $armor['regist_stone'] > 0 ? '+' . $armor['regist_stone'] : $armor['regist_stone'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_sleep'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Sleep</div>
                    <div class="stat-value"><?= $armor['regist_sleep'] > 0 ? '+' . $armor['regist_sleep'] : $armor['regist_sleep'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_freeze'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Freeze</div>
                    <div class="stat-value"><?= $armor['regist_freeze'] > 0 ? '+' . $armor['regist_freeze'] : $armor['regist_freeze'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_blind'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Blind</div>
                    <div class="stat-value"><?= $armor['regist_blind'] > 0 ? '+' . $armor['regist_blind'] : $armor['regist_blind'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_skill'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Skills</div>
                    <div class="stat-value"><?= $armor['regist_skill'] > 0 ? '+' . $armor['regist_skill'] : $armor['regist_skill'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_spirit'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Spirit</div>
                    <div class="stat-value"><?= $armor['regist_spirit'] > 0 ? '+' . $armor['regist_spirit'] : $armor['regist_spirit'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_dragon'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Dragon</div>
                    <div class="stat-value"><?= $armor['regist_dragon'] > 0 ? '+' . $armor['regist_dragon'] : $armor['regist_dragon'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_fear'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">Fear</div>
                    <div class="stat-value"><?= $armor['regist_fear'] > 0 ? '+' . $armor['regist_fear'] : $armor['regist_fear'] ?></div>
                </div>
                <?php endif; ?>
                
                <?php if ($armor['regist_all'] != 0): ?>
                <div class="stat-item">
                    <div class="stat-label">All Resistances</div>
                    <div class="stat-value"><?= $armor['regist_all'] > 0 ? '+' . $armor['regist_all'] : $armor['regist_all'] ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Armor Set Section -->
    <?php if ($armorSet): ?>
	<div class="card">
    <div class="card-header">
        <h2>Armor Set</h2>
    </div>
    <div class="card-content">
        <div class="set-info">
            <h3><?= htmlspecialchars($armorSet['note'] ?: 'Armor Set') ?></h3>
            <?php if (!empty($armorSet['sets'])): ?>
            <div class="set-pieces">
                <?php 
                $setPieces = explode(',', $armorSet['sets']);
                if (count($setPieces) > 0): 
                ?>
                <div class="set-pieces-grid" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <?php foreach ($setPieces as $pieceId): 
                        // Get piece details with icon
                        $pieceQuery = "SELECT item_id, desc_en, type, iconId FROM armor WHERE item_id = ?";
                        $piece = $db->getRow($pieceQuery, [trim($pieceId)]);
                        if ($piece):
                    ?>
                    <div class="set-piece-item" style="flex: 0 0 calc(25% - 10px); min-width: 200px; margin-bottom: 10px;">
                        <div class="set-piece-icon">
                            <img src="<?= SITE_URL ?>/assets/img/items/<?= $piece['iconId'] ?>.png" 
                                 alt="<?= htmlspecialchars($piece['desc_en']) ?>" 
                                 onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                        </div>
                        <div class="set-piece-info">
                            <a href="armor-detail.php?id=<?= $piece['item_id'] ?>">
                                <?= htmlspecialchars($piece['desc_en']) ?> 
                                <span class="set-piece-type">(<?= formatArmorType($piece['type']) ?>)</span>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
                
                <div class="set-bonuses">
                    <h4>Set Bonus:</h4>
                    <table class="detail-table">
                        <?php if ($armorSet['ac'] != 0): ?>
                        <tr>
                            <th>AC</th>
                            <td><?= $armorSet['ac'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['hp'] != 0): ?>
                        <tr>
                            <th>HP</th>
                            <td><?= $armorSet['hp'] > 0 ? '+' . $armorSet['hp'] : $armorSet['hp'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['mp'] != 0): ?>
                        <tr>
                            <th>MP</th>
                            <td><?= $armorSet['mp'] > 0 ? '+' . $armorSet['mp'] : $armorSet['mp'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['hpr'] != 0): ?>
                        <tr>
                            <th>HP Regen</th>
                            <td><?= $armorSet['hpr'] > 0 ? '+' . $armorSet['hpr'] : $armorSet['hpr'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['mpr'] != 0): ?>
                        <tr>
                            <th>MP Regen</th>
                            <td><?= $armorSet['mpr'] > 0 ? '+' . $armorSet['mpr'] : $armorSet['mpr'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['mr'] != 0): ?>
                        <tr>
                            <th>Magic Resistance</th>
                            <td><?= $armorSet['mr'] > 0 ? '+' . $armorSet['mr'] : $armorSet['mr'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['str'] != 0): ?>
                        <tr>
                            <th>STR</th>
                            <td><?= $armorSet['str'] > 0 ? '+' . $armorSet['str'] : $armorSet['str'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['dex'] != 0): ?>
                        <tr>
                            <th>DEX</th>
                            <td><?= $armorSet['dex'] > 0 ? '+' . $armorSet['dex'] : $armorSet['dex'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['con'] != 0): ?>
                        <tr>
                            <th>CON</th>
                            <td><?= $armorSet['con'] > 0 ? '+' . $armorSet['con'] : $armorSet['con'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['wis'] != 0): ?>
                        <tr>
                            <th>WIS</th>
                            <td><?= $armorSet['wis'] > 0 ? '+' . $armorSet['wis'] : $armorSet['wis'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['cha'] != 0): ?>
                        <tr>
                            <th>CHA</th>
                            <td><?= $armorSet['cha'] > 0 ? '+' . $armorSet['cha'] : $armorSet['cha'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['intl'] != 0): ?>
                        <tr>
                            <th>INT</th>
                            <td><?= $armorSet['intl'] > 0 ? '+' . $armorSet['intl'] : $armorSet['intl'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['sp'] != 0): ?>
                        <tr>
                            <th>SP</th>
                            <td><?= $armorSet['sp'] > 0 ? '+' . $armorSet['sp'] : $armorSet['sp'] ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <!-- Weapon stats if any -->
                        <?php if ($armorSet['shorthitup'] != 0 || $armorSet['shortdmgup'] != 0 || $armorSet['shortCritical'] != 0): ?>
                        <tr>
                            <th>Melee Stats</th>
                            <td>
                                <?php if ($armorSet['shorthitup'] != 0): ?>
                                    Hit: <?= $armorSet['shorthitup'] > 0 ? '+' . $armorSet['shorthitup'] : $armorSet['shorthitup'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['shortdmgup'] != 0): ?>
                                    Damage: <?= $armorSet['shortdmgup'] > 0 ? '+' . $armorSet['shortdmgup'] : $armorSet['shortdmgup'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['shortCritical'] != 0): ?>
                                    Critical: <?= $armorSet['shortCritical'] > 0 ? '+' . $armorSet['shortCritical'] : $armorSet['shortCritical'] ?>%
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['longhitup'] != 0 || $armorSet['longdmgup'] != 0 || $armorSet['longCritical'] != 0): ?>
                        <tr>
                            <th>Ranged Stats</th>
                            <td>
                                <?php if ($armorSet['longhitup'] != 0): ?>
                                    Hit: <?= $armorSet['longhitup'] > 0 ? '+' . $armorSet['longhitup'] : $armorSet['longhitup'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['longdmgup'] != 0): ?>
                                    Damage: <?= $armorSet['longdmgup'] > 0 ? '+' . $armorSet['longdmgup'] : $armorSet['longdmgup'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['longCritical'] != 0): ?>
                                    Critical: <?= $armorSet['longCritical'] > 0 ? '+' . $armorSet['longCritical'] : $armorSet['longCritical'] ?>%
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['magichitup'] != 0 || $armorSet['magicCritical'] != 0): ?>
                        <tr>
                            <th>Magic Stats</th>
                            <td>
                                <?php if ($armorSet['magichitup'] != 0): ?>
                                    Hit: <?= $armorSet['magichitup'] > 0 ? '+' . $armorSet['magichitup'] : $armorSet['magichitup'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['magicCritical'] != 0): ?>
                                    Critical: <?= $armorSet['magicCritical'] > 0 ? '+' . $armorSet['magicCritical'] : $armorSet['magicCritical'] ?>%
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <!-- Elemental Resistances -->
                        <?php if ($armorSet['earth'] != 0 || $armorSet['fire'] != 0 || $armorSet['wind'] != 0 || $armorSet['water'] != 0): ?>
                        <tr>
                            <th>Elemental Resistance</th>
                            <td>
                                <?php if ($armorSet['earth'] != 0): ?>
                                    Earth: <?= $armorSet['earth'] > 0 ? '+' . $armorSet['earth'] : $armorSet['earth'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['fire'] != 0): ?>
                                    Fire: <?= $armorSet['fire'] > 0 ? '+' . $armorSet['fire'] : $armorSet['fire'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['wind'] != 0): ?>
                                    Wind: <?= $armorSet['wind'] > 0 ? '+' . $armorSet['wind'] : $armorSet['wind'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['water'] != 0): ?>
                                    Water: <?= $armorSet['water'] > 0 ? '+' . $armorSet['water'] : $armorSet['water'] ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($armorSet['reduction'] != 0 || $armorSet['magicReduction'] != 0): ?>
                        <tr>
                            <th>Damage Reduction</th>
                            <td>
                                <?php if ($armorSet['reduction'] != 0): ?>
                                    Physical: <?= $armorSet['reduction'] > 0 ? '+' . $armorSet['reduction'] : $armorSet['reduction'] ?><br>
                                <?php endif; ?>
                                <?php if ($armorSet['magicReduction'] != 0): ?>
                                    Magic: <?= $armorSet['magicReduction'] > 0 ? '+' . $armorSet['magicReduction'] : $armorSet['magicReduction'] ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
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
    <?php if (!empty($armor['note'])): ?>
    <div class="card">
        <div class="card-header">
            <h2>Additional Notes</h2>
        </div>
        <div class="card-content">
            <div class="description">
                <?= nl2br(htmlspecialchars($armor['note'])) ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
