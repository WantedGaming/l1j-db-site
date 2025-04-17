<?php
/**
 * Item detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Item Details';
$pageDescription = 'Detailed information about items in L1J Remastered, including stats, use effects, and drop locations.';

// Include header
require_once '../../includes/header.php';

// Include item functions
require_once '../../includes/item-functions.php';

// Get database instance
$db = Database::getInstance();

// Get item ID from URL
$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to items list
if($itemId <= 0) {
    header('Location: items-list.php');
    exit;
}

// Get item details
$query = "SELECT * FROM etcitem WHERE item_id = ? 
          AND item_type IN ('ARROW', 'FOOD', 'LIGHT', 'MATERIAL', 'OTHER', 'TREASURE_BOX', 'WAND', 'SPELL_BOOK', 'POTION')
          AND NOT (item_type = 'OTHER' AND use_type = 'MAGICDOLL')";
$item = $db->getRow($query, [$itemId]);

// If item not found, show error
if(!$item) {
    echo '<div class="container"><div class="error-message">Item not found or not of the correct type.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get monsters that drop this item with spawn information
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.desc_en as monster_name_en, n.lvl as monster_level, 
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
              ORDER BY d.chance DESC
              LIMIT 25";
$dropMonsters = $db->getRows($dropQuery, [$itemId]);

// Initialize empty arrays for shop data since tables don't exist yet
$shopNpcs = [];
$buyingNpcs = [];

// Set page title to item name
$pageTitle = $item['desc_en'];

?>

<!-- Hero Section with Transparent Item Image -->
<div class="weapon-hero">
    <div class="weapon-hero-image-container">
        <img src="<?= SITE_URL ?>/assets/img/items/<?= $item['iconId'] ?>.png" 
             alt="<?= htmlspecialchars($item['desc_en']) ?>" 
             class="weapon-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
    </div>
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars(cleanItemName($item['desc_en'])) ?></h1>
        <p><?= formatItemType($item['item_type']) ?></p>
        <?php if (isPropertyDisplayable($item, 'itemGrade') && $item['itemGrade'] != 'NORMAL'): ?>
            <div class="weapon-grade">
                <span class="badge <?= getGradeBadgeClass($item['itemGrade']) ?>"><?= formatGrade($item['itemGrade']) ?></span>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/items/index.php">Items</a> &raquo;
        <span><?= htmlspecialchars(cleanItemName($item['desc_en'])) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= SITE_URL ?>/assets/img/items/<?= $item['iconId'] ?>.png" 
                     alt="<?= htmlspecialchars($item['desc_en']) ?>" 
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
                        <th>Item ID</th>
                        <td><?= $item['item_id'] ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?= htmlspecialchars(cleanItemName($item['desc_en'])) ?></td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td><?= formatItemType($item['item_type']) ?></td>
                    </tr>
                    <?php if (isPropertyDisplayable($item, 'use_type') && $item['use_type'] != 'NONE'): ?>
                    <tr>
                        <th>Use Type</th>
                        <td><?= formatUseType($item['use_type']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isPropertyDisplayable($item, 'material')): ?>
                    <tr>
                        <th>Material</th>
                        <td><?= formatMaterial($item['material']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isPropertyDisplayable($item, 'weight')): ?>
                    <tr>
                        <th>Weight</th>
                        <td><?= $item['weight'] / 1000 ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isPropertyDisplayable($item, 'max_charge_count')): ?>
                    <tr>
                        <th>Max Charges</th>
                        <td><?= $item['max_charge_count'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isPropertyDisplayable($item, 'food_volume')): ?>
                    <tr>
                        <th>Food Volume</th>
                        <td><?= $item['food_volume'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isPropertyDisplayable($item, 'min_lvl') || isPropertyDisplayable($item, 'max_lvl')): ?>
                    <tr>
                        <th>Level</th>
                        <td>
                            <?php if ($item['min_lvl'] > 0 && $item['max_lvl'] > 0): ?>
                                <?= $item['min_lvl'] ?> - <?= $item['max_lvl'] ?>
                            <?php elseif ($item['min_lvl'] > 0): ?>
                                Min: <?= $item['min_lvl'] ?>
                            <?php elseif ($item['max_lvl'] > 0): ?>
                                Max: <?= $item['max_lvl'] ?>
                            <?php else: ?>
                                None
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if (isPropertyDisplayable($item, 'delay_id') && isPropertyDisplayable($item, 'delay_time')): ?>
                    <tr>
                        <th>Delay Time</th>
                        <td><?= $item['delay_time'] / 1000 ?> seconds</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Requirements Section -->
    <?php 
    $hasClassRequirements = $item['use_royal'] || $item['use_knight'] || $item['use_elf'] || 
                           $item['use_mage'] || $item['use_darkelf'] || $item['use_dragonknight'] || 
                           $item['use_illusionist'] || $item['use_warrior'] || $item['use_fencer'] || 
                           $item['use_lancer'];
                           
    $allClassesEnabled = $item['use_royal'] && $item['use_knight'] && $item['use_elf'] && 
                        $item['use_mage'] && $item['use_darkelf'] && $item['use_dragonknight'] && 
                        $item['use_illusionist'] && $item['use_warrior'] && $item['use_fencer'] && 
                        $item['use_lancer'];

    // Check for traits
    $hasTraits = !empty($item['haste_item']) || !empty($item['bless']);

    // Define restrictions properties
    $restrictions = [
        'trade' => 'Tradable',
        'retrieve' => 'Retrievable',
        'specialretrieve' => 'Special Retrieve',
        'cant_delete' => 'Cannot Delete',
        'cant_sell' => 'Cannot Sell'
    ];

    // Check for active restrictions
    $activeRestrictions = [];
    foreach ($restrictions as $field => $label) {
        if (isset($item[$field]) && $item[$field] != 0) {
            $activeRestrictions[$field] = $label;
        }
    }
    $hasRestrictions = !empty($activeRestrictions);

    if ($hasClassRequirements || $hasTraits || $hasRestrictions): 
    ?>
    <div class="requirements-section">
        <?php if ($hasClassRequirements): ?>
        <div class="card">
            <div class="card-header">
                <h2>Class</h2>
            </div>
            <div class="card-content">
                <div class="requirements-grid">
                    <!-- Class Requirements -->
                    <div class="requirement-item">
                        <div class="requirements-grid">
                            <?php if ($allClassesEnabled): ?>
                            <span class="requirement-switch">
                                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                All Classes
                            </span>
                            <?php else: ?>
                                <?php if ($item['use_royal']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Royal
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_knight']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Knight
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_elf']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Elf
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_mage']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Mage
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_darkelf']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Dark Elf
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_dragonknight']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Dragon Knight
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_illusionist']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Illusionist
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_warrior']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Warrior
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_fencer']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Fencer
                                </span>
                                <?php endif; ?>
                                <?php if ($item['use_lancer']): ?>
                                <span class="requirement-switch">
                                    <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                                    Lancer
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($hasTraits): ?>
        <div class="card">
            <div class="card-header">
                <h2>Traits</h2>
            </div>
            <div class="card-content">
                <div class="requirements-grid">
                    <?php if (!empty($item['haste_item'])): ?>
                    <div class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Haste
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($item['bless'])): ?>
                    <div class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Blessed
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($hasRestrictions): ?>
        <div class="card">
            <div class="card-header">
                <h2>Restrictions</h2>
            </div>
            <div class="card-content">
                <table class="detail-table" style="border-top: 1px solid var(--border-color);">
                    <tbody>
                        <?php foreach ($activeRestrictions as $field => $label): ?>
                        <tr>
                            <th><?= $label ?></th>
                            <td>
                                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Item Properties Section -->
    <?php
    // Define all properties grouped by category
    $property_groups = [
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
        $active_properties = [];
        foreach ($properties as $field => $label) {
            if (isset($item[$field]) && $item[$field] != 0) {
                $active_properties[$field] = $label;
            }
        }
        if (!empty($active_properties)) {
            $active_groups[$group_name] = $active_properties;
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
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
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
    $hasAttributes = !empty($item['str']) || !empty($item['con']) || !empty($item['dex']) || 
                    !empty($item['int']) || !empty($item['wis']) || !empty($item['cha']);
    $hasHpMp = !empty($item['hp']) || !empty($item['mp']) || !empty($item['hpr']) || !empty($item['mpr']);
    $hasCombatStats = !empty($item['dmg_short']) || !empty($item['dmg_long']) || !empty($item['hit_short']) || 
                      !empty($item['hit_long']) || !empty($item['dmg_magic']) || !empty($item['hit_magic']);
    $hasResistances = !empty($item['defense_water']) || !empty($item['defense_wind']) || 
                      !empty($item['defense_fire']) || !empty($item['defense_earth']);
    $hasOtherStats = !empty($item['dmg_reduction']) || !empty($item['weight_reduction']);

    if ($hasAttributes || $hasHpMp || $hasCombatStats || $hasResistances || $hasOtherStats): 
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Stats Bonuses</h2>
        </div>
        <div class="card-content">
            <table class="detail-table" style="border-top: 1px solid var(--border-color);">
                <tbody>
                    <?php if ($hasAttributes): ?>
                    <tr>
                        <td colspan="2"><strong>Attributes</strong></td>
                    </tr>
                    <?php if (!empty($item['str'])): ?>
                    <tr>
                        <th>STR</th>
                        <td><?= format_signed_number($item['str']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['dex'])): ?>
                    <tr>
                        <th>DEX</th>
                        <td><?= format_signed_number($item['dex']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['con'])): ?>
                    <tr>
                        <th>CON</th>
                        <td><?= format_signed_number($item['con']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['int'])): ?>
                    <tr>
                        <th>INT</th>
                        <td><?= format_signed_number($item['int']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['wis'])): ?>
                    <tr>
                        <th>WIS</th>
                        <td><?= format_signed_number($item['wis']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['cha'])): ?>
                    <tr>
                        <th>CHA</th>
                        <td><?= format_signed_number($item['cha']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($hasHpMp): ?>
                    <tr>
                        <td colspan="2"><strong>HP/MP</strong></td>
                    </tr>
                    <?php if (!empty($item['hp'])): ?>
                    <tr>
                        <th>HP</th>
                        <td><?= format_signed_number($item['hp']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['mp'])): ?>
                    <tr>
                        <th>MP</th>
                        <td><?= format_signed_number($item['mp']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['hpr'])): ?>
                    <tr>
                        <th>HP Regen</th>
                        <td><?= format_signed_number($item['hpr']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['mpr'])): ?>
                    <tr>
                        <th>MP Regen</th>
                        <td><?= format_signed_number($item['mpr']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($hasCombatStats): ?>
                    <tr>
                        <td colspan="2"><strong>Combat Stats</strong></td>
                    </tr>
                    <?php if (!empty($item['dmg_short'])): ?>
                    <tr>
                        <th>Melee Damage</th>
                        <td><?= format_signed_number($item['dmg_short']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['hit_short'])): ?>
                    <tr>
                        <th>Melee Hit</th>
                        <td><?= format_signed_number($item['hit_short']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['dmg_long'])): ?>
                    <tr>
                        <th>Ranged Damage</th>
                        <td><?= format_signed_number($item['dmg_long']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['hit_long'])): ?>
                    <tr>
                        <th>Ranged Hit</th>
                        <td><?= format_signed_number($item['hit_long']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['dmg_magic'])): ?>
                    <tr>
                        <th>Magic Damage</th>
                        <td><?= format_signed_number($item['dmg_magic']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['hit_magic'])): ?>
                    <tr>
                        <th>Magic Hit</th>
                        <td><?= format_signed_number($item['hit_magic']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($hasResistances): ?>
                    <tr>
                        <td colspan="2"><strong>Resistances</strong></td>
                    </tr>
                    <?php if (!empty($item['defense_water'])): ?>
                    <tr>
                        <th>Water</th>
                        <td><?= format_signed_number($item['defense_water']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['defense_wind'])): ?>
                    <tr>
                        <th>Wind</th>
                        <td><?= format_signed_number($item['defense_wind']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['defense_fire'])): ?>
                    <tr>
                        <th>Fire</th>
                        <td><?= format_signed_number($item['defense_fire']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['defense_earth'])): ?>
                    <tr>
                        <th>Earth</th>
                        <td><?= format_signed_number($item['defense_earth']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($hasOtherStats): ?>
                    <tr>
                        <td colspan="2"><strong>Other</strong></td>
                    </tr>
                    <?php if (!empty($item['dmg_reduction'])): ?>
                    <tr>
                        <th>Damage Reduction</th>
                        <td><?= format_signed_number($item['dmg_reduction']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($item['weight_reduction'])): ?>
                    <tr>
                        <th>Weight Reduction</th>
                        <td><?= format_signed_number($item['weight_reduction']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- HP/MP Bonuses Section -->
    <?php
    $hasBonuses = (isset($item['add_hp']) && $item['add_hp'] != 0) || 
                  (isset($item['add_mp']) && $item['add_mp'] != 0) || 
                  (isset($item['add_hpr']) && $item['add_hpr'] != 0) || 
                  (isset($item['add_mpr']) && $item['add_mpr'] != 0) || 
                  (isset($item['add_sp']) && $item['add_sp'] != 0);
    if ($hasBonuses):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>HP/MP Bonuses</h2>
        </div>
        <div class="card-content">
            <table class="detail-table" style="border-top: 1px solid var(--border-color);">
                <?php if (isset($item['add_hp']) && $item['add_hp'] != 0): ?>
                <tr>
                    <th>HP</th>
                    <td><?= $item['add_hp'] > 0 ? '+' . $item['add_hp'] : $item['add_hp'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['add_mp']) && $item['add_mp'] != 0): ?>
                <tr>
                    <th>MP</th>
                    <td><?= $item['add_mp'] > 0 ? '+' . $item['add_mp'] : $item['add_mp'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['add_hpr']) && $item['add_hpr'] != 0): ?>
                <tr>
                    <th>HP Regen</th>
                    <td><?= $item['add_hpr'] > 0 ? '+' . $item['add_hpr'] : $item['add_hpr'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['add_mpr']) && $item['add_mpr'] != 0): ?>
                <tr>
                    <th>MP Regen</th>
                    <td><?= $item['add_mpr'] > 0 ? '+' . $item['add_mpr'] : $item['add_mpr'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['add_sp']) && $item['add_sp'] != 0): ?>
                <tr>
                    <th>SP</th>
                    <td><?= $item['add_sp'] > 0 ? '+' . $item['add_sp'] : $item['add_sp'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Combat Stats Section -->
    <?php
    $hasCombatStats = (isset($item['dmg_small']) && $item['dmg_small'] != 0) || 
                     (isset($item['dmg_large']) && $item['dmg_large'] != 0) || 
                     (isset($item['shortHit']) && $item['shortHit'] != 0) || 
                     (isset($item['shortDmg']) && $item['shortDmg'] != 0) || 
                     (isset($item['longHit']) && $item['longHit'] != 0) || 
                     (isset($item['longDmg']) && $item['longDmg'] != 0) ||
                     (isset($item['shortCritical']) && $item['shortCritical'] != 0) ||
                     (isset($item['longCritical']) && $item['longCritical'] != 0) ||
                     (isset($item['magicCritical']) && $item['magicCritical'] != 0);
    if ($hasCombatStats):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Combat Stats</h2>
        </div>
        <div class="card-content">
            <table class="detail-table" style="border-top: 1px solid var(--border-color);">
                <?php if (isset($item['dmg_small']) && $item['dmg_small'] != 0): ?>
                <tr>
                    <th>Damage (Small)</th>
                    <td><?= $item['dmg_small'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['dmg_large']) && $item['dmg_large'] != 0): ?>
                <tr>
                    <th>Damage (Large)</th>
                    <td><?= $item['dmg_large'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['shortHit']) && $item['shortHit'] != 0): ?>
                <tr>
                    <th>Melee Hit</th>
                    <td><?= $item['shortHit'] > 0 ? '+' . $item['shortHit'] : $item['shortHit'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['shortDmg']) && $item['shortDmg'] != 0): ?>
                <tr>
                    <th>Melee Damage</th>
                    <td><?= $item['shortDmg'] > 0 ? '+' . $item['shortDmg'] : $item['shortDmg'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['longHit']) && $item['longHit'] != 0): ?>
                <tr>
                    <th>Ranged Hit</th>
                    <td><?= $item['longHit'] > 0 ? '+' . $item['longHit'] : $item['longHit'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['longDmg']) && $item['longDmg'] != 0): ?>
                <tr>
                    <th>Ranged Damage</th>
                    <td><?= $item['longDmg'] > 0 ? '+' . $item['longDmg'] : $item['longDmg'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['shortCritical']) && $item['shortCritical'] != 0): ?>
                <tr>
                    <th>Melee Critical</th>
                    <td><?= $item['shortCritical'] > 0 ? '+' . $item['shortCritical'] : $item['shortCritical'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['longCritical']) && $item['longCritical'] != 0): ?>
                <tr>
                    <th>Ranged Critical</th>
                    <td><?= $item['longCritical'] > 0 ? '+' . $item['longCritical'] : $item['longCritical'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['magicCritical']) && $item['magicCritical'] != 0): ?>
                <tr>
                    <th>Magic Critical</th>
                    <td><?= $item['magicCritical'] > 0 ? '+' . $item['magicCritical'] : $item['magicCritical'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resistances Section -->
    <?php
    $hasResistances = (isset($item['m_def']) && $item['m_def'] != 0) || 
                      (isset($item['defense_water']) && $item['defense_water'] != 0) || 
                      (isset($item['defense_wind']) && $item['defense_wind'] != 0) || 
                      (isset($item['defense_fire']) && $item['defense_fire'] != 0) || 
                      (isset($item['defense_earth']) && $item['defense_earth'] != 0) ||
                      (isset($item['attr_all']) && $item['attr_all'] != 0) ||
                      (isset($item['damage_reduction']) && $item['damage_reduction'] != 0) ||
                      (isset($item['MagicDamageReduction']) && $item['MagicDamageReduction'] != 0);
    if ($hasResistances):
    ?>
    <div class="card">
        <div class="card-header">
            <h2>Resistances</h2>
        </div>
        <div class="card-content">
            <table class="detail-table" style="border-top: 1px solid var(--border-color);">
                <?php if (isset($item['m_def']) && $item['m_def'] != 0): ?>
                <tr>
                    <th>Magic Defense</th>
                    <td><?= $item['m_def'] > 0 ? '+' . $item['m_def'] : $item['m_def'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['defense_water']) && $item['defense_water'] != 0): ?>
                <tr>
                    <th>Water</th>
                    <td><?= $item['defense_water'] > 0 ? '+' . $item['defense_water'] : $item['defense_water'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['defense_wind']) && $item['defense_wind'] != 0): ?>
                <tr>
                    <th>Wind</th>
                    <td><?= $item['defense_wind'] > 0 ? '+' . $item['defense_wind'] : $item['defense_wind'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['defense_fire']) && $item['defense_fire'] != 0): ?>
                <tr>
                    <th>Fire</th>
                    <td><?= $item['defense_fire'] > 0 ? '+' . $item['defense_fire'] : $item['defense_fire'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['defense_earth']) && $item['defense_earth'] != 0): ?>
                <tr>
                    <th>Earth</th>
                    <td><?= $item['defense_earth'] > 0 ? '+' . $item['defense_earth'] : $item['defense_earth'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['attr_all']) && $item['attr_all'] != 0): ?>
                <tr>
                    <th>All Elements</th>
                    <td><?= $item['attr_all'] > 0 ? '+' . $item['attr_all'] : $item['attr_all'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['damage_reduction']) && $item['damage_reduction'] != 0): ?>
                <tr>
                    <th>Damage Reduction</th>
                    <td><?= $item['damage_reduction'] > 0 ? '+' . $item['damage_reduction'] : $item['damage_reduction'] ?></td>
                </tr>
                <?php endif; ?>
                <?php if (isset($item['MagicDamageReduction']) && $item['MagicDamageReduction'] != 0): ?>
                <tr>
                    <th>Magic Damage Reduction</th>
                    <td><?= $item['MagicDamageReduction'] > 0 ? '+' . $item['MagicDamageReduction'] : $item['MagicDamageReduction'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>
	<!-- Spell or Effect Section -->
<?php 
$hasSpell = !empty($item['Magic_name']) || $item['attr'] != 'NONE' || $item['buffDurationSecond'] > 0;
if ($hasSpell): 
?>
<div class="card">
    <div class="card-header">
        <h2>Spell Information</h2>
    </div>
    <div class="card-content">
        <table class="detail-table">
            <?php if (!empty($item['Magic_name'])): ?>
            <tr>
                <th>Spell</th>
                <td><?= htmlspecialchars($item['Magic_name']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($item['attr'] != 'NONE' && !empty($item['attr'])): ?>
            <tr>
                <th>Attribute</th>
                <td><?= htmlspecialchars($item['attr']) ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($item['level'] > 0): ?>
            <tr>
                <th>Spell Level</th>
                <td><?= $item['level'] ?></td>
            </tr>
            <?php endif; ?>
            <?php if ($item['buffDurationSecond'] > 0): ?>
            <tr>
                <th>Duration</th>
                <td><?= $item['buffDurationSecond'] ?> seconds</td>
            </tr>
            <?php endif; ?>
            <?php if (isset($item['hprAbsol32Second']) && $item['hprAbsol32Second'] > 0): ?>
            <tr>
                <th>HP Regen Bonus</th>
                <td><?= $item['hprAbsol32Second'] ?> HP / 32 sec</td>
            </tr>
            <?php endif; ?>
            <?php if (isset($item['mprAbsol64Second']) && $item['mprAbsol64Second'] > 0): ?>
            <tr>
                <th>MP Regen Bonus</th>
                <td><?= $item['mprAbsol64Second'] ?> MP / 64 sec</td>
            </tr>
            <?php endif; ?>
            <?php if (isset($item['mprAbsol16Second']) && $item['mprAbsol16Second'] > 0): ?>
            <tr>
                <th>MP Regen Bonus</th>
                <td><?= $item['mprAbsol16Second'] ?> MP / 16 sec</td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Shop Information -->
<?php if (!empty($shopNpcs)): ?>
<div class="card">
    <div class="card-header">
        <h2>Available From</h2>
    </div>
    <div class="card-content">
        <table class="detail-table">
            <thead>
                <tr>
                    <th>NPC</th>
                    <th>Price</th>
                    <th>Pack Size</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($shopNpcs as $shop): ?>
                <tr>
                    <td><?= htmlspecialchars(!empty($shop['npc_name_en']) ? $shop['npc_name_en'] : $shop['npc_name']) ?></td>
                    <td><?= number_format($shop['selling_price']) ?> Adena</td>
                    <td><?= $shop['pack_count'] > 1 ? $shop['pack_count'] : '1' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($buyingNpcs)): ?>
<div class="card">
    <div class="card-header">
        <h2>Can Be Sold To</h2>
    </div>
    <div class="card-content">
        <table class="detail-table">
            <thead>
                <tr>
                    <th>NPC</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($buyingNpcs as $shop): ?>
                <tr>
                    <td><?= htmlspecialchars(!empty($shop['npc_name_en']) ? $shop['npc_name_en'] : $shop['npc_name']) ?></td>
                    <td><?= number_format($shop['buying_price']) ?> Adena</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Drop By Section -->
<?php if (!empty($dropMonsters)): ?>
<div class="card">
    <div class="card-header">
        <h2>Dropped By</h2>
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
                                 alt="<?= htmlspecialchars(!empty($drop['monster_name_en']) ? $drop['monster_name_en'] : $drop['monster_name']) ?>"
                                 class="monster-sprite"
                                 onerror="this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
                            <a href="<?= SITE_URL ?>/pages/monsters/detail.php?id=<?= $drop['npcid'] ?>">
                                <?= htmlspecialchars(!empty($drop['monster_name_en']) ? $drop['monster_name_en'] : $drop['monster_name']) ?>
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
        <h2>Monster Locations</h2>
    </div>
    <div class="card-content">
        <div class="spawn-locations-grid">
            <?php 
            $displayedMaps = [];
            foreach($dropMonsters as $drop):
                if (!empty($drop['map_name']) && !in_array($drop['mapid'], $displayedMaps)):
                    $displayedMaps[] = $drop['mapid'];
            ?>
                <div class="spawn-location-card">
                    <div class="spawn-location-header">
                        <h3><?= htmlspecialchars(!empty($drop['monster_name_en']) ? $drop['monster_name_en'] : $drop['monster_name']) ?></h3>
                        <div class="spawn-meta">
                            <span class="spawn-count"><?= $drop['count'] ? $drop['count'] : '?' ?> spawns</span>
                            <?php if(isset($drop['respawnDelay']) && $drop['respawnDelay'] > 0): ?>
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
                            if (isset($drop['locx1']) && $drop['locx1'] > 0 && isset($drop['locy1']) && $drop['locy1'] > 0 
                                && isset($drop['locx2']) && $drop['locx2'] > 0 && isset($drop['locy2']) && $drop['locy2'] > 0): ?>
                                <div class="spawn-area" style="
                                    left: <?= ($drop['locx1'] / 32768) * 100 ?>%;
                                    top: <?= ($drop['locy1'] / 32768) * 100 ?>%;
                                    width: <?= (($drop['locx2'] - $drop['locx1']) / 32768) * 100 ?>%;
                                    height: <?= (($drop['locy2'] - $drop['locy1']) / 32768) * 100 ?>%;">
                                    <div class="spawn-area-label">Spawn Area</div>
                                </div>
                            <?php elseif(isset($drop['locx']) && $drop['locx'] > 0 && isset($drop['locy']) && $drop['locy'] > 0): ?>
                                <!-- Single point spawn with random range -->
                                <div class="spawn-marker" style="
                                    left: <?= ($drop['locx'] / 32768) * 100 ?>%;
                                    top: <?= ($drop['locy'] / 32768) * 100 ?>%;">
                                    <div class="spawn-point"></div>
                                    <?php if (isset($drop['randomx']) && $drop['randomx'] > 0 || isset($drop['randomy']) && $drop['randomy'] > 0): ?>
                                        <div class="spawn-range" style="
                                            width: <?= (isset($drop['randomx']) ? ($drop['randomx'] * 2 / 32768) * 100 : 5) ?>%;
                                            height: <?= (isset($drop['randomy']) ? ($drop['randomy'] * 2 / 32768) * 100 : 5) ?>%;">
                                            <div class="spawn-range-label">Random Range</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="spawn-details">
                            <p class="map-name"><?= htmlspecialchars($drop['map_name']) ?></p>
                            <div class="coordinates">
                                <?php if (isset($drop['locx1']) && $drop['locx1'] > 0): ?>
                                    <i class="fas fa-map-marker-alt"></i>
                                    Area: (<?= $drop['locx1'] ?>, <?= $drop['locy1'] ?>) to (<?= $drop['locx2'] ?>, <?= $drop['locy2'] ?>)
                                <?php elseif(isset($drop['locx']) && $drop['locx'] > 0): ?>
                                    <i class="fas fa-map-marker-alt"></i>
                                    Center: (<?= $drop['locx'] ?>, <?= $drop['locy'] ?>)
                                    <?php if (isset($drop['randomx']) && $drop['randomx'] > 0 || isset($drop['randomy']) && $drop['randomy'] > 0): ?>
                                        <br>
                                        <i class="fas fa-arrows-alt"></i>
                                        Range: ±<?= $drop['randomx'] ?? 0 ?> x, ±<?= $drop['randomy'] ?? 0 ?> y
                                    <?php endif; ?>
                                <?php else: ?>
                                    <i class="fas fa-exclamation-circle"></i>
                                    Location data unavailable
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php 
                endif;
            endforeach; 
            ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Description -->
<?php if (!empty($item['note'])): ?>
<div class="card">
    <div class="card-header">
        <h2>Description</h2>
    </div>
    <div class="card-content">
        <div class="description">
            <?= nl2br(htmlspecialchars($item['note'])) ?>
        </div>
    </div>
</div>
<?php endif; ?>