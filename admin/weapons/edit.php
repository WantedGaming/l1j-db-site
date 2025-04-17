<?php
/**
 * Admin - Edit Weapon
 */

// Set page title
$pageTitle = 'Edit Weapon';

// Include admin header
require_once '../../includes/admin-header.php';

// Include weapons functions
require_once '../../includes/weapons-functions.php';

// Get database instance
$db = Database::getInstance();

// Get weapon ID from URL
$weaponId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to weapons list
if($weaponId <= 0) {
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect weapon data from form
    $weapon = [
        'desc_en' => $_POST['desc_en'] ?? '',
        'desc_kr' => $_POST['desc_kr'] ?? '',
        'type' => $_POST['type'] ?? '',
        'material' => $_POST['material'] ?? '',
        'weight' => intval($_POST['weight'] ?? 0),
        'dmg_small' => intval($_POST['dmg_small'] ?? 0),
        'dmg_large' => intval($_POST['dmg_large'] ?? 0),
        'safenchant' => intval($_POST['safenchant'] ?? 0),
        'hitmodifier' => intval($_POST['hitmodifier'] ?? 0),
        'dmgmodifier' => intval($_POST['dmgmodifier'] ?? 0),
        'double_dmg_chance' => intval($_POST['double_dmg_chance'] ?? 0),
        'min_lvl' => intval($_POST['min_lvl'] ?? 0),
        'max_lvl' => intval($_POST['max_lvl'] ?? 0),
        'bless' => isset($_POST['bless']) ? 1 : 0,
        'trade' => isset($_POST['trade']) ? 1 : 0,
        'haste_item' => isset($_POST['haste_item']) ? 1 : 0,
        'itemGrade' => $_POST['itemGrade'] ?? 'NORMAL',
        // Character class usage
        'use_royal' => isset($_POST['use_royal']) ? 1 : 0,
        'use_knight' => isset($_POST['use_knight']) ? 1 : 0,
        'use_mage' => isset($_POST['use_mage']) ? 1 : 0,
        'use_elf' => isset($_POST['use_elf']) ? 1 : 0,
        'use_darkelf' => isset($_POST['use_darkelf']) ? 1 : 0,
        'use_dragonknight' => isset($_POST['use_dragonknight']) ? 1 : 0,
        'use_illusionist' => isset($_POST['use_illusionist']) ? 1 : 0,
        'use_warrior' => isset($_POST['use_warrior']) ? 1 : 0,
        'use_fencer' => isset($_POST['use_fencer']) ? 1 : 0,
        'use_lancer' => isset($_POST['use_lancer']) ? 1 : 0,
        // Stats
        'add_str' => intval($_POST['add_str'] ?? 0),
        'add_con' => intval($_POST['add_con'] ?? 0),
        'add_dex' => intval($_POST['add_dex'] ?? 0),
        'add_int' => intval($_POST['add_int'] ?? 0),
        'add_wis' => intval($_POST['add_wis'] ?? 0),
        'add_cha' => intval($_POST['add_cha'] ?? 0),
        'add_hp' => intval($_POST['add_hp'] ?? 0),
        'add_mp' => intval($_POST['add_mp'] ?? 0),
        'add_hpr' => intval($_POST['add_hpr'] ?? 0),
        'add_mpr' => intval($_POST['add_mpr'] ?? 0),
        'add_sp' => intval($_POST['add_sp'] ?? 0),
        'iconId' => intval($_POST['iconId'] ?? 0),
        'spriteId' => intval($_POST['spriteId'] ?? 0),
        'canbedmg' => isset($_POST['canbedmg']) ? 1 : 0,
        'cant_delete' => isset($_POST['cant_delete']) ? 1 : 0,
        'cant_sell' => isset($_POST['cant_sell']) ? 1 : 0,
        'retrieve' => isset($_POST['retrieve']) ? 1 : 0,
        'specialretrieve' => isset($_POST['specialretrieve']) ? 1 : 0,
        'm_def' => intval($_POST['m_def'] ?? 0),
        'shortCritical' => intval($_POST['shortCritical'] ?? 0),
        'longCritical' => intval($_POST['longCritical'] ?? 0),
        'magicCritical' => intval($_POST['magicCritical'] ?? 0),
        'damage_reduction' => intval($_POST['damage_reduction'] ?? 0),
        'MagicDamageReduction' => intval($_POST['MagicDamageReduction'] ?? 0),
        'PVPDamage' => intval($_POST['PVPDamage'] ?? 0),
        'PVPDamageReduction' => intval($_POST['PVPDamageReduction'] ?? 0),
        'expBonus' => intval($_POST['expBonus'] ?? 0),
        'note' => $_POST['note'] ?? ''
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($weapon['desc_en'])) {
        $errors[] = "Weapon name is required";
    }
    
    if (empty($weapon['type'])) {
        $errors[] = "Weapon type is required";
    }
    
    // If no errors, update the weapon
    if (empty($errors)) {
        // Build the query
        $updateValues = [];
        foreach ($weapon as $field => $value) {
            $updateValues[] = "{$field} = ?";
        }
        
        $query = "UPDATE weapon SET " . implode(', ', $updateValues) . " WHERE item_id = ?";
        
        // Add the weapon ID to the parameters
        $params = array_values($weapon);
        $params[] = $weaponId;
        
        // Execute the query
        $result = $db->execute($query, $params);
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Weapon '{$weapon['desc_en']}' updated successfully."
            ];
            
            // Redirect to weapons list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to update weapon. Database error.";
        }
    }
}

// Get weapon details
$query = "SELECT * FROM weapon WHERE item_id = ?";
$weapon = $db->getRow($query, [$weaponId]);

// If weapon not found, show error and redirect
if(!$weapon) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => "Weapon not found."
    ];
    header("Location: index.php");
    exit;
}

// Get weapon types and materials for dropdown
$weaponTypes = [
    'SWORD' => 'Sword',
    'DAGGER' => 'Dagger',
    'TOHAND_SWORD' => 'Two-handed Sword',
    'BOW' => 'Bow (2H)',
    'SPEAR' => 'Spear (2H)',
    'BLUNT' => 'Blunt',
    'STAFF' => 'Staff',
    'GAUNTLET' => 'Gauntlet',
    'CLAW' => 'Claw',
    'EDORYU' => 'Edoryu',
    'SINGLE_BOW' => 'Bow',
    'SINGLE_SPEAR' => 'Spear',
    'TOHAND_BLUNT' => 'Blunt (2H)',
    'TOHAND_STAFF' => 'Staff (2H)',
    'KEYRINGK' => 'Keyringk',
    'CHAINSWORD' => 'Chain Sword',
    'STING' => 'Sting',
    'ARROW' => 'Arrow'
];

$materialTypes = [
    'IRON' => 'Iron',
    'WOOD' => 'Wood',
    'MITHRIL' => 'Mithril',
    'DRAGON_HIDE' => 'Dragon Hide',
    'ORIHARUKON' => 'Oriharukon',
    'DRANIUM' => 'Dranium',
    'SILVER' => 'Silver',
    'STEEL' => 'Steel',
    'CRYSTAL' => 'Crystal',
    'COPPER' => 'Copper',
    'GOLD' => 'Gold',
    'BONE' => 'Bone',
    'LEATHER' => 'Leather',
    'CLOTH' => 'Cloth',
    'LIQUID' => 'Liquid',
    'PAPER' => 'Paper',
    'STONE' => 'Stone'
];

$itemGrades = [
    'NORMAL' => 'Normal',
    'ADVANC' => 'Advanced',
    'RARE' => 'Rare',
    'HERO' => 'Hero',
    'LEGEND' => 'Legend',
    'MYTH' => 'Myth',
    'ONLY' => 'Unique'
];

// Extract material name without parentheses if needed
$materialName = $weapon['material'];
if (strpos($materialName, '(') !== false) {
    $materialName = trim(substr($materialName, 0, strpos($materialName, '(')));
}
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title"><?= htmlspecialchars($weapon['desc_en']) ?></h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-tag me-2"></i>Item ID: <?= $weaponId ?>
                    </span>
                    <span class="mx-3 text-muted">|</span>
                    <span class="text-muted fs-5">
                        Type: <?= htmlspecialchars($weaponTypes[$weapon['type']] ?? $weapon['type']) ?>
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Weapons
                    </a>
                    <button type="button" onclick="document.getElementById('editForm').reset();" class="btn" style="background-color: #343434; color: #e0e0e0;">
                        <i class="fas fa-undo me-1"></i> Reset Changes
                    </button>
                    <button type="button" onclick="document.getElementById('editForm').submit();" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../../admin_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php">Weapons</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Weapon</li>
        </ol>
    </nav>
    
    <!-- Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-<?= $_SESSION['admin_message']['type'] ?>">
            <?= $_SESSION['admin_message']['message'] ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>
    
    <div class="row equal-height-row">
        <div class="col-md-3 sidebar-column">
            <!-- Weapon Image and Basic Info -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Weapon Preview
                </div>
                <div class="acquisition-card-body d-flex flex-column align-items-center justify-content-center">
                    <?php if (isset($weapon['iconId']) && $weapon['iconId'] > 0): ?>
                        <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
                             alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
                             style="max-width: 128px;"
                             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png';">
                    <?php else: ?>
                        <img src="<?= SITE_URL ?>/assets/img/items/default.png" 
                             alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
                             style="max-width: 128px; max-height: 128px;">
                    <?php endif; ?>
                    
                    <h5 class="mt-3"><?= htmlspecialchars($weapon['desc_en']) ?></h5>
                    <p class="mb-1"><?= htmlspecialchars($weaponTypes[$weapon['type']] ?? $weapon['type']) ?></p>
                    <div class="item-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Item ID: <?= $weapon['item_id'] ?></div>
                        <div class="badge bg-secondary">Icon ID: <?= $weapon['iconId'] ?? 'N/A' ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Weapon Stats Quick View -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Weapon Stats
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Damage</span>
                            <span class="badge bg-danger rounded-pill"><?= $weapon['dmg_small'] ?>-<?= $weapon['dmg_large'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Hit Modifier</span>
                            <span class="badge bg-info rounded-pill"><?= $weapon['hitmodifier'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Grade</span>
                            <span class="badge rarity-<?= strtolower($weapon['itemGrade'] ?? 'common') ?>"><?= $weapon['itemGrade'] ?? 'Common' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Safe Enchant</span>
                            <span class="badge bg-success rounded-pill"><?= $weapon['safenchant'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Weight</span>
                            <span class="badge bg-secondary rounded-pill"><?= $weapon['weight'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Level Range</span>
                            <span class="badge bg-primary rounded-pill"><?= $weapon['min_lvl'] ?>-<?= $weapon['max_lvl'] ?: '∞' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Edit Form -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-edit me-2"></i> Edit Weapon</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
                        <div class="row">
                            <!-- Form Tabs -->
                            <div class="col-lg-12 mb-0">
                                <div class="form-tabs">
                                    <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                    <button type="button" class="form-tab" data-tab="properties">Properties</button>
                                    <button type="button" class="form-tab" data-tab="stats">Stats</button>
                                    <button type="button" class="form-tab" data-tab="combat">Combats</button>
                                    <button type="button" class="form-tab" data-tab="classes">Restrictions</button>
                                    <button type="button" class="form-tab" data-tab="item_properties">Item</button>
                                    <button type="button" class="form-tab" data-tab="additional">Additional</button>
                                    <button type="button" class="form-tab" data-tab="notes">Notes</button>
                                </div>
                            </div>
                            
                            <!-- Basic Information Section -->
                            <div class="col-lg-12 form-section active" id="basic-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Basic Information
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="desc_en" class="form-label">Weapon Name (English) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="desc_en" name="desc_en" value="<?= htmlspecialchars($weapon['desc_en']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="desc_kr" class="form-label">Weapon Name (Korean)</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr" value="<?= htmlspecialchars($weapon['desc_kr'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="type" class="form-label">Weapon Type</label>
                                                <select class="form-select" id="type" name="type">
                                                    <?php foreach ($weaponTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $weapon['type'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="material" class="form-label">Material</label>
                                                <select class="form-select" id="material" name="material">
                                                    <?php foreach ($materialTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $materialName === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="iconId" class="form-label">Icon ID</label>
                                                <input type="number" class="form-control no-spinner" id="iconId" name="iconId" value="<?= (int)$weapon['iconId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="spriteId" class="form-label">Sprite ID</label>
                                                <input type="number" class="form-control no-spinner" id="spriteId" name="spriteId" value="<?= (int)$weapon['spriteId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="weight" class="form-label">Weight</label>
                                                <input type="number" class="form-control no-spinner" id="weight" name="weight" value="<?= (int)$weapon['weight'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="itemGrade" class="form-label">Item Grade</label>
                                                <select class="form-select" id="itemGrade" name="itemGrade">
                                                    <option value="" <?= empty($weapon['itemGrade']) ? 'selected' : '' ?>>None</option>
                                                    <?php foreach ($itemGrades as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $weapon['itemGrade'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Weapon Properties Section -->
                            <div class="col-lg-12 form-section" id="properties-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Weapon Properties
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="dmg_small" class="form-label">Small Target Damage</label>
                                                <input type="number" class="form-control no-spinner" id="dmg_small" name="dmg_small" value="<?= (int)$weapon['dmg_small'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dmg_large" class="form-label">Large Target Damage</label>
                                                <input type="number" class="form-control no-spinner" id="dmg_large" name="dmg_large" value="<?= (int)$weapon['dmg_large'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="hitmodifier" class="form-label">Hit Modifier</label>
                                                <input type="number" class="form-control no-spinner" id="hitmodifier" name="hitmodifier" value="<?= (int)$weapon['hitmodifier'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dmgmodifier" class="form-label">Damage Modifier</label>
                                                <input type="number" class="form-control no-spinner" id="dmgmodifier" name="dmgmodifier" value="<?= (int)$weapon['dmgmodifier'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="double_dmg_chance" class="form-label">Double Damage Chance (%)</label>
                                                <input type="number" class="form-control no-spinner" id="double_dmg_chance" name="double_dmg_chance" value="<?= (int)$weapon['double_dmg_chance'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="safenchant" class="form-label">Safe Enchant Level</label>
                                                <input type="number" class="form-control no-spinner" id="safenchant" name="safenchant" value="<?= (int)$weapon['safenchant'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="min_lvl" class="form-label">Minimum Level</label>
                                                <input type="number" class="form-control no-spinner" id="min_lvl" name="min_lvl" value="<?= (int)$weapon['min_lvl'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_lvl" class="form-label">Maximum Level</label>
                                                <input type="number" class="form-control no-spinner" id="max_lvl" name="max_lvl" value="<?= (int)$weapon['max_lvl'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats & Bonuses Section -->
                            <div class="col-lg-12 form-section" id="stats-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Stats Bonuses
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="add_str" class="form-label">+ STR</label>
                                                <input type="number" class="form-control no-spinner" id="add_str" name="add_str" value="<?= (int)$weapon['add_str'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_con" class="form-label">+ CON</label>
                                                <input type="number" class="form-control no-spinner" id="add_con" name="add_con" value="<?= (int)$weapon['add_con'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_dex" class="form-label">+ DEX</label>
                                                <input type="number" class="form-control no-spinner" id="add_dex" name="add_dex" value="<?= (int)$weapon['add_dex'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_int" class="form-label">+ INT</label>
                                                <input type="number" class="form-control no-spinner" id="add_int" name="add_int" value="<?= (int)$weapon['add_int'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_wis" class="form-label">+ WIS</label>
                                                <input type="number" class="form-control no-spinner" id="add_wis" name="add_wis" value="<?= (int)$weapon['add_wis'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_cha" class="form-label">+ CHA</label>
                                                <input type="number" class="form-control no-spinner" id="add_cha" name="add_cha" value="<?= (int)$weapon['add_cha'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_hp" class="form-label">+ HP</label>
                                                <input type="number" class="form-control no-spinner" id="add_hp" name="add_hp" value="<?= (int)$weapon['add_hp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_mp" class="form-label">+ MP</label>
                                                <input type="number" class="form-control no-spinner" id="add_mp" name="add_mp" value="<?= (int)$weapon['add_mp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_hpr" class="form-label">+ HP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="add_hpr" name="add_hpr" value="<?= (int)$weapon['add_hpr'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_mpr" class="form-label">+ MP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="add_mpr" name="add_mpr" value="<?= (int)$weapon['add_mpr'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_sp" class="form-label">+ SP</label>
                                                <input type="number" class="form-control no-spinner" id="add_sp" name="add_sp" value="<?= (int)$weapon['add_sp'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Combat Stats Section -->
                            <div class="col-lg-12 form-section" id="combat-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Combat Stats
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="m_def" class="form-label">Magic Defense</label>
                                                <input type="number" class="form-control no-spinner" id="m_def" name="m_def" value="<?= (int)$weapon['m_def'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="shortCritical" class="form-label">Short Range Critical</label>
                                                <input type="number" class="form-control no-spinner" id="shortCritical" name="shortCritical" value="<?= (int)$weapon['shortCritical'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="longCritical" class="form-label">Long Range Critical</label>
                                                <input type="number" class="form-control no-spinner" id="longCritical" name="longCritical" value="<?= (int)$weapon['longCritical'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="magicCritical" class="form-label">Magic Critical</label>
                                                <input type="number" class="form-control no-spinner" id="magicCritical" name="magicCritical" value="<?= (int)$weapon['magicCritical'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Class Restrictions Section -->
                            <div class="col-lg-12 form-section" id="classes-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Class Restrictions
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_royal" name="use_royal" <?= $weapon['use_royal'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_royal">Royal</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_knight" name="use_knight" <?= $weapon['use_knight'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_knight">Knight</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_elf" name="use_elf" <?= $weapon['use_elf'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_elf">Elf</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_mage" name="use_mage" <?= $weapon['use_mage'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_mage">Mage</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_darkelf" name="use_darkelf" <?= $weapon['use_darkelf'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_darkelf">Dark Elf</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_dragonknight" name="use_dragonknight" <?= $weapon['use_dragonknight'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_dragonknight">Dragon Knight</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_illusionist" name="use_illusionist" <?= $weapon['use_illusionist'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_illusionist">Illusionist</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_warrior" name="use_warrior" <?= $weapon['use_warrior'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_warrior">Warrior</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_fencer" name="use_fencer" <?= $weapon['use_fencer'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_fencer">Fencer</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_lancer" name="use_lancer" <?= $weapon['use_lancer'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_lancer">Lancer</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Item Properties Section -->
                            <div class="col-lg-12 form-section" id="item_properties-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Item Properties
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="bless" name="bless" <?= $weapon['bless'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="bless">Blessed</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="trade" name="trade" <?= $weapon['trade'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="trade">Tradeable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="canbedmg" name="canbedmg" <?= $weapon['canbedmg'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="canbedmg">Can Be Damaged</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="haste_item" name="haste_item" <?= $weapon['haste_item'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="haste_item">Haste Item</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cant_delete" name="cant_delete" <?= $weapon['cant_delete'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="cant_delete">Can't Delete</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cant_sell" name="cant_sell" <?= $weapon['cant_sell'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="cant_sell">Can't Sell</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="retrieve" name="retrieve" <?= $weapon['retrieve'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="retrieve">Retrievable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="specialretrieve" name="specialretrieve" <?= $weapon['specialretrieve'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="specialretrieve">Special Retrieve</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Stats Section -->
                            <div class="col-lg-12 form-section" id="additional-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Additional Stats
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="damage_reduction" class="form-label">Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="<?= (int)$weapon['damage_reduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="MagicDamageReduction" class="form-label">Magic Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="MagicDamageReduction" name="MagicDamageReduction" value="<?= (int)$weapon['MagicDamageReduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamage" class="form-label">PVP Damage Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamage" name="PVPDamage" value="<?= (int)$weapon['PVPDamage'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamageReduction" class="form-label">PVP Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamageReduction" name="PVPDamageReduction" value="<?= (int)$weapon['PVPDamageReduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="expBonus" class="form-label">EXP Bonus (%)</label>
                                                <input type="number" class="form-control no-spinner" id="expBonus" name="expBonus" value="<?= (int)$weapon['expBonus'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes Section -->
                            <div class="col-lg-12 form-section" id="notes-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Additional Notes
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="note" class="form-label">Notes</label>
                                            <textarea class="form-control" id="note" name="note" rows="5"><?= htmlspecialchars($weapon['note'] ?? '') ?></textarea>
                                            <small>Enter any additional information about this weapon.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Update Weapon</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.form-tab');
    const sections = document.querySelectorAll('.form-section');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all sections
            sections.forEach(section => {
                section.style.display = 'none';
                section.classList.remove('active');
            });
            
            // Show the selected section
            const tabId = this.getAttribute('data-tab');
            const activeSection = document.getElementById(tabId + '-section');
            activeSection.style.display = 'block';
            setTimeout(() => {
                activeSection.classList.add('active');
            }, 10);
        });
    });
    
    // Initialize first tab as active if none is active
    if (!document.querySelector('.form-tab.active') && tabs.length > 0) {
        tabs[0].click();
    }
    
    // Image preview functionality
    const iconIdInput = document.getElementById('iconId');
    const imagePreview = document.querySelector('.acquisition-card-body img');
    const basePath = '<?= SITE_URL ?>/assets/img/items/';
    const defaultImage = basePath + 'default.png';
    
    // Update image when icon ID changes
    if (iconIdInput && imagePreview) {
        iconIdInput.addEventListener('input', function() {
            const iconId = this.value.trim();
            if (iconId && !isNaN(iconId)) {
                imagePreview.src = basePath + iconId + '.png';
            } else {
                imagePreview.src = defaultImage;
            }
        });
    }
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>