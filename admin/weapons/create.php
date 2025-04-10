<?php
/**
 * Admin - Create New Weapon
 */

// Set page title
$pageTitle = 'Add New Weapon';

// Include admin header
require_once '../../includes/admin-header.php';

// Include weapons functions
require_once '../../includes/weapons-functions.php';

// Get database instance
$db = Database::getInstance();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect weapon data from form
    $weapon = [
        'item_id' => intval($_POST['item_id'] ?? 0),
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
    if (empty($weapon['item_id'])) {
        $errors[] = "Item ID is required";
    }
    
    if (empty($weapon['desc_en'])) {
        $errors[] = "Weapon name is required";
    }
    
    if (empty($weapon['type'])) {
        $errors[] = "Weapon type is required";
    }
    
    // Check if item_id already exists
    $existingItem = $db->getRow("SELECT item_id FROM weapon WHERE item_id = ?", [$weapon['item_id']]);
    if ($existingItem) {
        $errors[] = "An item with ID {$weapon['item_id']} already exists";
    }
    
    // If no errors, insert the weapon
    if (empty($errors)) {
        // Build the query
        $fields = implode(', ', array_keys($weapon));
        $placeholders = implode(', ', array_fill(0, count($weapon), '?'));
        
        $query = "INSERT INTO weapon ({$fields}) VALUES ({$placeholders})";
        
        // Execute the query
        $result = $db->execute($query, array_values($weapon));
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Weapon '{$weapon['desc_en']}' created successfully."
            ];
            
            // Redirect to weapons list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to create weapon. Database error.";
        }
    }
}

// Generate next available item_id
$nextItemId = $db->getColumn("SELECT MAX(item_id) + 1 FROM weapon") ?: 100000;

// Initialize default weapon values
$weapon = [
    'item_id' => $nextItemId,
    'desc_en' => '',
    'desc_kr' => '',
    'type' => '',
    'material' => '',
    'weight' => 0,
    'dmg_small' => 0,
    'dmg_large' => 0,
    'safenchant' => 0,
    'hitmodifier' => 0,
    'dmgmodifier' => 0,
    'double_dmg_chance' => 0,
    'min_lvl' => 0,
    'max_lvl' => 0,
    'bless' => 0,
    'trade' => 1,
    'haste_item' => 0,
    'itemGrade' => 'NORMAL',
    'use_royal' => 1,
    'use_knight' => 1,
    'use_mage' => 1,
    'use_elf' => 1,
    'use_darkelf' => 1,
    'use_dragonknight' => 1,
    'use_illusionist' => 1,
    'use_warrior' => 1,
    'use_fencer' => 1,
    'use_lancer' => 1,
    'add_str' => 0,
    'add_con' => 0,
    'add_dex' => 0,
    'add_int' => 0,
    'add_wis' => 0,
    'add_cha' => 0,
    'add_hp' => 0,
    'add_mp' => 0,
    'add_hpr' => 0,
    'add_mpr' => 0,
    'add_sp' => 0,
    'iconId' => $nextItemId,
    'spriteId' => 0,
    'canbedmg' => 0,
    'cant_delete' => 0,
    'cant_sell' => 0,
    'retrieve' => 1,
    'specialretrieve' => 0,
    'm_def' => 0,
    'shortCritical' => 0,
    'longCritical' => 0,
    'magicCritical' => 0,
    'damage_reduction' => 0,
    'MagicDamageReduction' => 0,
    'PVPDamage' => 0,
    'PVPDamageReduction' => 0,
    'expBonus' => 0,
    'note' => ''
];

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
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title">Add New Weapon</h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-tag me-2"></i>Item ID: <?= $nextItemId ?>
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Weapons
                    </a>
                    <button type="button" onclick="document.getElementById('createForm').reset();" class="btn" style="background-color: #343434; color: #e0e0e0;">
                        <i class="fas fa-undo me-1"></i> Reset Form
                    </button>
                    <button type="button" onclick="document.getElementById('createForm').submit();" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-save me-1"></i> Create Weapon
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
            <li class="breadcrumb-item active" aria-current="page">Add New Weapon</li>
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
                    <img id="item-image-preview" 
                         src="<?= SITE_URL ?>/assets/img/items/<?= $nextItemId ?>.png" 
                         alt="Weapon Image Preview" 
                         style="max-width: 128px;"
                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                    
                    <h5 class="mt-3">New Weapon</h5>
                    <p class="mb-1">Select a weapon type</p>
                    <div class="item-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Item ID: <?= $nextItemId ?></div>
                        <div class="badge bg-secondary">Icon ID: <?= $nextItemId ?></div>
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
                            <span class="badge bg-danger rounded-pill">0-0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Hit Modifier</span>
                            <span class="badge bg-info rounded-pill">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Grade</span>
                            <span class="badge rarity-normal">NORMAL</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Safe Enchant</span>
                            <span class="badge bg-success rounded-pill">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Weight</span>
                            <span class="badge bg-secondary rounded-pill">0</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Level Range</span>
                            <span class="badge bg-primary rounded-pill">0-∞</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Create Form -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-plus-circle me-2"></i> Add New Weapon</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="createForm">
                        <div class="row">
                            <!-- Form Tabs -->
                            <div class="col-lg-12 mb-4">
                                <div class="form-tabs">
                                    <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                    <button type="button" class="form-tab" data-tab="properties">Properties</button>
                                    <button type="button" class="form-tab" data-tab="stats">Stats</button>
                                    <button type="button" class="form-tab" data-tab="combat">Combat</button>
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
                                            <div class="col-md-4 mb-3">
                                                <label for="item_id" class="form-label">Item ID <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control no-spinner" id="item_id" name="item_id" value="<?= $nextItemId ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_en" class="form-label">Weapon Name (English) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="desc_en" name="desc_en" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_kr" class="form-label">Weapon Name (Korean)</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="type" class="form-label">Weapon Type</label>
                                                <select class="form-select" id="type" name="type">
                                                    <option value="">Select Type</option>
                                                    <?php foreach ($weaponTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="material" class="form-label">Material</label>
                                                <select class="form-select" id="material" name="material">
                                                    <option value="">Select Material</option>
                                                    <?php foreach ($materialTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="iconId" class="form-label">Icon ID</label>
                                                <input type="number" class="form-control no-spinner" id="iconId" name="iconId" value="<?= $nextItemId ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="spriteId" class="form-label">Sprite ID</label>
                                                <input type="number" class="form-control no-spinner" id="spriteId" name="spriteId" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="weight" class="form-label">Weight</label>
                                                <input type="number" class="form-control no-spinner" id="weight" name="weight" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="itemGrade" class="form-label">Item Grade</label>
                                                <select class="form-select" id="itemGrade" name="itemGrade">
                                                    <option value="">None</option>
                                                    <?php foreach ($itemGrades as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $value === 'NORMAL' ? 'selected' : '' ?>>
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
                                                <input type="number" class="form-control no-spinner" id="dmg_small" name="dmg_small" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dmg_large" class="form-label">Large Target Damage</label>
                                                <input type="number" class="form-control no-spinner" id="dmg_large" name="dmg_large" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="hitmodifier" class="form-label">Hit Modifier</label>
                                                <input type="number" class="form-control no-spinner" id="hitmodifier" name="hitmodifier" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dmgmodifier" class="form-label">Damage Modifier</label>
                                                <input type="number" class="form-control no-spinner" id="dmgmodifier" name="dmgmodifier" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="double_dmg_chance" class="form-label">Double Damage Chance (%)</label>
                                                <input type="number" class="form-control no-spinner" id="double_dmg_chance" name="double_dmg_chance" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="safenchant" class="form-label">Safe Enchant Level</label>
                                                <input type="number" class="form-control no-spinner" id="safenchant" name="safenchant" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="min_lvl" class="form-label">Minimum Level</label>
                                                <input type="number" class="form-control no-spinner" id="min_lvl" name="min_lvl" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_lvl" class="form-label">Maximum Level</label>
                                                <input type="number" class="form-control no-spinner" id="max_lvl" name="max_lvl" value="0">
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
                                                <input type="number" class="form-control no-spinner" id="add_str" name="add_str" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_con" class="form-label">+ CON</label>
                                                <input type="number" class="form-control no-spinner" id="add_con" name="add_con" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_dex" class="form-label">+ DEX</label>
                                                <input type="number" class="form-control no-spinner" id="add_dex" name="add_dex" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_int" class="form-label">+ INT</label>
                                                <input type="number" class="form-control no-spinner" id="add_int" name="add_int" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_wis" class="form-label">+ WIS</label>
                                                <input type="number" class="form-control no-spinner" id="add_wis" name="add_wis" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_cha" class="form-label">+ CHA</label>
                                                <input type="number" class="form-control no-spinner" id="add_cha" name="add_cha" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_hp" class="form-label">+ HP</label>
                                                <input type="number" class="form-control no-spinner" id="add_hp" name="add_hp" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_mp" class="form-label">+ MP</label>
                                                <input type="number" class="form-control no-spinner" id="add_mp" name="add_mp" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_hpr" class="form-label">+ HP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="add_hpr" name="add_hpr" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_mpr" class="form-label">+ MP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="add_mpr" name="add_mpr" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_sp" class="form-label">+ SP</label>
                                                <input type="number" class="form-control no-spinner" id="add_sp" name="add_sp" value="0">
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
                                                <input type="number" class="form-control no-spinner" id="m_def" name="m_def" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="shortCritical" class="form-label">Short Range Critical</label>
                                                <input type="number" class="form-control no-spinner" id="shortCritical" name="shortCritical" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="longCritical" class="form-label">Long Range Critical</label>
                                                <input type="number" class="form-control no-spinner" id="longCritical" name="longCritical" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="magicCritical" class="form-label">Magic Critical</label>
                                                <input type="number" class="form-control no-spinner" id="magicCritical" name="magicCritical" value="0">
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
                                                    <input class="form-check-input" type="checkbox" id="use_royal" name="use_royal" checked>
                                                    <label class="form-check-label" for="use_royal">Royal</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_knight" name="use_knight" checked>
                                                    <label class="form-check-label" for="use_knight">Knight</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_elf" name="use_elf" checked>
                                                    <label class="form-check-label" for="use_elf">Elf</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_mage" name="use_mage" checked>
                                                    <label class="form-check-label" for="use_mage">Mage</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_darkelf" name="use_darkelf" checked>
                                                    <label class="form-check-label" for="use_darkelf">Dark Elf</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_dragonknight" name="use_dragonknight" checked>
                                                    <label class="form-check-label" for="use_dragonknight">Dragon Knight</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_illusionist" name="use_illusionist" checked>
                                                    <label class="form-check-label" for="use_illusionist">Illusionist</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_warrior" name="use_warrior" checked>
                                                    <label class="form-check-label" for="use_warrior">Warrior</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_fencer" name="use_fencer" checked>
                                                    <label class="form-check-label" for="use_fencer">Fencer</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_lancer" name="use_lancer" checked>
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
                                                    <input class="form-check-input" type="checkbox" id="bless" name="bless">
                                                    <label class="form-check-label" for="bless">Blessed</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="trade" name="trade" checked>
                                                    <label class="form-check-label" for="trade">Tradeable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="canbedmg" name="canbedmg">
                                                    <label class="form-check-label" for="canbedmg">Can Be Damaged</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="haste_item" name="haste_item">
                                                    <label class="form-check-label" for="haste_item">Haste Item</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cant_delete" name="cant_delete">
                                                    <label class="form-check-label" for="cant_delete">Can't Delete</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cant_sell" name="cant_sell">
                                                    <label class="form-check-label" for="cant_sell">Can't Sell</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="retrieve" name="retrieve" checked>
                                                    <label class="form-check-label" for="retrieve">Retrievable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="specialretrieve" name="specialretrieve">
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
                                                <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="MagicDamageReduction" class="form-label">Magic Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="MagicDamageReduction" name="MagicDamageReduction" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamage" class="form-label">PVP Damage Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamage" name="PVPDamage" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamageReduction" class="form-label">PVP Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamageReduction" name="PVPDamageReduction" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="expBonus" class="form-label">EXP Bonus (%)</label>
                                                <input type="number" class="form-control no-spinner" id="expBonus" name="expBonus" value="0">
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
                                            <textarea class="form-control" id="note" name="note" rows="5"></textarea>
                                            <small>Enter any additional information about this weapon.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Create Weapon</button>
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
            sections.forEach(section => section.classList.remove('active'));
            
            // Show the selected section
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-section').classList.add('active');
        });
    });
    
    // Image preview functionality
    const iconIdInput = document.getElementById('iconId');
    const imagePreview = document.getElementById('item-image-preview');
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

    // Live update weapon name in preview
    const nameInput = document.getElementById('desc_en');
    const namePreview = document.querySelector('.acquisition-card-body h5');
    
    if (nameInput && namePreview) {
        nameInput.addEventListener('input', function() {
            const weaponName = this.value.trim();
            namePreview.textContent = weaponName || 'New Weapon';
        });
    }

    // Live update weapon type in preview
    const typeSelect = document.getElementById('type');
    const typePreview = document.querySelector('.acquisition-card-body p');
    
    if (typeSelect && typePreview) {
        typeSelect.addEventListener('change', function() {
            const selectedIndex = this.selectedIndex;
            if (selectedIndex > 0) {
                const selectedOption = this.options[selectedIndex];
                typePreview.textContent = selectedOption.text;
            } else {
                typePreview.textContent = 'Select a weapon type';
            }
        });
    }

    // Live update stats in the sidebar
    const dmgSmallInput = document.getElementById('dmg_small');
    const dmgLargeInput = document.getElementById('dmg_large');
    const hitModifierInput = document.getElementById('hitmodifier');
    const weightInput = document.getElementById('weight');
    const safeEnchantInput = document.getElementById('safenchant');
    const minLvlInput = document.getElementById('min_lvl');
    const maxLvlInput = document.getElementById('max_lvl');
    const itemGradeSelect = document.getElementById('itemGrade');
    
    // Get preview elements
    const damagePreview = document.querySelector('.list-group-item:nth-child(1) .badge');
    const hitModifierPreview = document.querySelector('.list-group-item:nth-child(2) .badge');
    const gradePreview = document.querySelector('.list-group-item:nth-child(3) .badge');
    const safeEnchantPreview = document.querySelector('.list-group-item:nth-child(4) .badge');
    const weightPreview = document.querySelector('.list-group-item:nth-child(5) .badge');
    const levelRangePreview = document.querySelector('.list-group-item:nth-child(6) .badge');
    
    // Update damage preview
    if (dmgSmallInput && dmgLargeInput && damagePreview) {
        const updateDamage = function() {
            const small = dmgSmallInput.value || '0';
            const large = dmgLargeInput.value || '0';
            damagePreview.textContent = small + '-' + large;
        };
        
        dmgSmallInput.addEventListener('input', updateDamage);
        dmgLargeInput.addEventListener('input', updateDamage);
    }
    
    // Update hit modifier preview
    if (hitModifierInput && hitModifierPreview) {
        hitModifierInput.addEventListener('input', function() {
            hitModifierPreview.textContent = this.value || '0';
        });
    }
    
    // Update grade preview
    if (itemGradeSelect && gradePreview) {
        itemGradeSelect.addEventListener('change', function() {
            const grade = this.value || 'NORMAL';
            gradePreview.textContent = grade;
            
            // Update class
            gradePreview.className = 'badge rarity-' + grade.toLowerCase();
        });
    }
    
    // Update safe enchant preview
    if (safeEnchantInput && safeEnchantPreview) {
        safeEnchantInput.addEventListener('input', function() {
            safeEnchantPreview.textContent = this.value || '0';
        });
    }
    
    // Update weight preview
    if (weightInput && weightPreview) {
        weightInput.addEventListener('input', function() {
            weightPreview.textContent = this.value || '0';
        });
    }
    
    // Update level range preview
    if (minLvlInput && maxLvlInput && levelRangePreview) {
        const updateLevelRange = function() {
            const min = minLvlInput.value || '0';
            const max = maxLvlInput.value || '∞';
            levelRangePreview.textContent = min + '-' + (max || '∞');
        };
        
        minLvlInput.addEventListener('input', updateLevelRange);
        maxLvlInput.addEventListener('input', updateLevelRange);
    }
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>