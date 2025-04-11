<?php
/**
 * Admin - Edit Armor
 */

// Set page title
$pageTitle = 'Edit Armor';

// Include admin header
require_once '../../includes/admin-header.php';

// Include armor functions
require_once '../../includes/armor-functions.php';

// Get database instance
$db = Database::getInstance();

// Get all armor sets for dropdown
// Instead of getAll, use query() which returns a PDOStatement
$stmt = $db->query("SELECT id, note FROM armor_set ORDER BY note");
$armorSets = [];
// Use PDO's fetch() method instead of fetch_assoc()
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $armorSets[] = $row;
}

// Get armor ID from URL
$armorId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to armor list
if($armorId <= 0) {
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect armor data from form
    $armor = [
        'item_name_id' => intval($_POST['item_name_id'] ?? 0),
        'desc_en' => $_POST['desc_en'] ?? '',
        'desc_kr' => $_POST['desc_kr'] ?? '',
        'desc_powerbook' => $_POST['desc_powerbook'] ?? '',
        'desc_id' => $_POST['desc_id'] ?? '',
        'note' => $_POST['note'] ?? '',
        'type' => $_POST['type'] ?? '',
        'material' => $_POST['material'] ?? '',
        'weight' => intval($_POST['weight'] ?? 0),
        'itemGrade' => $_POST['itemGrade'] ?? 'NORMAL',
        'grade' => intval($_POST['grade'] ?? 0),
        'iconId' => intval($_POST['iconId'] ?? 0),
        'spriteId' => intval($_POST['spriteId'] ?? 0),
        'ac' => intval($_POST['ac'] ?? 0),
        'ac_sub' => intval($_POST['ac_sub'] ?? 0),
        'safenchant' => intval($_POST['safenchant'] ?? 0),
        'min_lvl' => intval($_POST['min_lvl'] ?? 0),
        'max_lvl' => intval($_POST['max_lvl'] ?? 0),
        'bless' => isset($_POST['bless']) ? 1 : 0,
        'trade' => isset($_POST['trade']) ? 1 : 0,
        
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
        
        // Item properties
        'cant_delete' => isset($_POST['cant_delete']) ? 1 : 0,
        'cant_sell' => isset($_POST['cant_sell']) ? 1 : 0,
        'retrieve' => isset($_POST['retrieve']) ? 1 : 0,
        'specialretrieve' => isset($_POST['specialretrieve']) ? 1 : 0,
        'retrieveEnchant' => intval($_POST['retrieveEnchant'] ?? 0),
        'max_use_time' => intval($_POST['max_use_time'] ?? 0),
        
        // Combat related
        'm_def' => intval($_POST['m_def'] ?? 0),
        'damage_reduction' => intval($_POST['damage_reduction'] ?? 0),
        'hit_rate' => intval($_POST['hit_rate'] ?? 0),
        'dmg_rate' => intval($_POST['dmg_rate'] ?? 0),
        'bow_hit_rate' => intval($_POST['bow_hit_rate'] ?? 0),
        'bow_dmg_rate' => intval($_POST['bow_dmg_rate'] ?? 0),
        'haste_item' => intval($_POST['haste_item'] ?? 0),
        'carryBonus' => intval($_POST['carryBonus'] ?? 0),
        
        // Elemental resistances
        'defense_water' => intval($_POST['defense_water'] ?? 0),
        'defense_wind' => intval($_POST['defense_wind'] ?? 0),
        'defense_fire' => intval($_POST['defense_fire'] ?? 0),
        'defense_earth' => intval($_POST['defense_earth'] ?? 0),
        'attr_all' => intval($_POST['attr_all'] ?? 0),
        
        // Status resistances
        'regist_stone' => intval($_POST['regist_stone'] ?? 0),
        'regist_sleep' => intval($_POST['regist_sleep'] ?? 0),
        'regist_freeze' => intval($_POST['regist_freeze'] ?? 0),
        'regist_blind' => intval($_POST['regist_blind'] ?? 0),
        'regist_skill' => intval($_POST['regist_skill'] ?? 0),
        'regist_spirit' => intval($_POST['regist_spirit'] ?? 0),
        'regist_dragon' => intval($_POST['regist_dragon'] ?? 0),
        'regist_fear' => intval($_POST['regist_fear'] ?? 0),
        'regist_all' => intval($_POST['regist_all'] ?? 0),
        
        // Hit bonuses
        'hitup_skill' => intval($_POST['hitup_skill'] ?? 0),
        'hitup_spirit' => intval($_POST['hitup_spirit'] ?? 0),
        'hitup_dragon' => intval($_POST['hitup_dragon'] ?? 0),
        'hitup_fear' => intval($_POST['hitup_fear'] ?? 0),
        'hitup_all' => intval($_POST['hitup_all'] ?? 0),
        'hitup_magic' => intval($_POST['hitup_magic'] ?? 0),
        
        // Damage reduction
        'MagicDamageReduction' => intval($_POST['MagicDamageReduction'] ?? 0),
        'reductionEgnor' => intval($_POST['reductionEgnor'] ?? 0),
        'reductionPercent' => intval($_POST['reductionPercent'] ?? 0),
        
        // PVP Stats
        'PVPDamage' => intval($_POST['PVPDamage'] ?? 0),
        'PVPDamageReduction' => intval($_POST['PVPDamageReduction'] ?? 0),
        'PVPDamageReductionPercent' => intval($_POST['PVPDamageReductionPercent'] ?? 0),
        'PVPMagicDamageReduction' => intval($_POST['PVPMagicDamageReduction'] ?? 0),
        'PVPReductionEgnor' => intval($_POST['PVPReductionEgnor'] ?? 0),
        'PVPMagicDamageReductionEgnor' => intval($_POST['PVPMagicDamageReductionEgnor'] ?? 0),
        'abnormalStatusDamageReduction' => intval($_POST['abnormalStatusDamageReduction'] ?? 0),
        'abnormalStatusPVPDamageReduction' => intval($_POST['abnormalStatusPVPDamageReduction'] ?? 0),
        'PVPDamagePercent' => intval($_POST['PVPDamagePercent'] ?? 0),
        
        // Bonuses
        'expBonus' => intval($_POST['expBonus'] ?? 0),
        'rest_exp_reduce_efficiency' => intval($_POST['rest_exp_reduce_efficiency'] ?? 0),
        
        // Critical rates
        'shortCritical' => intval($_POST['shortCritical'] ?? 0),
        'longCritical' => intval($_POST['longCritical'] ?? 0),
        'magicCritical' => intval($_POST['magicCritical'] ?? 0),
        
        // Advanced stats
        'addDg' => intval($_POST['addDg'] ?? 0),
        'addEr' => intval($_POST['addEr'] ?? 0),
        'addMe' => intval($_POST['addMe'] ?? 0),
        
        // Special effects
        'poisonRegist' => $_POST['poisonRegist'] ?? 'false',
        'imunEgnor' => intval($_POST['imunEgnor'] ?? 0),
        'stunDuration' => intval($_POST['stunDuration'] ?? 0),
        'tripleArrowStun' => intval($_POST['tripleArrowStun'] ?? 0),
        'strangeTimeIncrease' => intval($_POST['strangeTimeIncrease'] ?? 0),
        'strangeTimeDecrease' => intval($_POST['strangeTimeDecrease'] ?? 0),
        
        // Potion effects
        'potionRegist' => intval($_POST['potionRegist'] ?? 0),
        'potionPercent' => intval($_POST['potionPercent'] ?? 0),
        'potionValue' => intval($_POST['potionValue'] ?? 0),
        'hprAbsol32Second' => intval($_POST['hprAbsol32Second'] ?? 0),
        'mprAbsol64Second' => intval($_POST['mprAbsol64Second'] ?? 0),
        'mprAbsol16Second' => intval($_POST['mprAbsol16Second'] ?? 0),
        'hpPotionDelayDecrease' => intval($_POST['hpPotionDelayDecrease'] ?? 0),
        'hpPotionCriticalProb' => intval($_POST['hpPotionCriticalProb'] ?? 0),
        
        // Skill related
        'increaseArmorSkillProb' => intval($_POST['increaseArmorSkillProb'] ?? 0),
        'attackSpeedDelayRate' => intval($_POST['attackSpeedDelayRate'] ?? 0),
        'moveSpeedDelayRate' => intval($_POST['moveSpeedDelayRate'] ?? 0),
        
        // Set and polymorph related
        'MainId' => intval($_POST['MainId'] ?? 0),
        'MainId2' => intval($_POST['MainId2'] ?? 0),
        'MainId3' => intval($_POST['MainId3'] ?? 0),
        'Set_Id' => intval($_POST['Set_Id'] ?? 0),
        'polyDescId' => intval($_POST['polyDescId'] ?? 0),
        'Magic_name' => $_POST['Magic_name'] ?? null,
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($armor['desc_en'])) {
        $errors[] = "Armor name is required";
    }
    
    if (empty($armor['type'])) {
        $errors[] = "Armor type is required";
    }
    
    // If no errors, update the armor
    if (empty($errors)) {
        // Build the query
        $updateValues = [];
        foreach ($armor as $field => $value) {
            $updateValues[] = "{$field} = ?";
        }
        
        $query = "UPDATE armor SET " . implode(', ', $updateValues) . " WHERE item_id = ?";
        
        // Add the armor ID to the parameters
        $params = array_values($armor);
        $params[] = $armorId;
        
        // Execute the query
        $result = $db->execute($query, $params);
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Armor '{$armor['desc_en']}' updated successfully."
            ];
            
            // Redirect to armor list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to update armor. Database error.";
        }
    }
}

// Get armor details
$query = "SELECT * FROM armor WHERE item_id = ?";
$armor = $db->getRow($query, [$armorId]);

// If armor not found, show error and redirect
if(!$armor) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => "Armor not found."
    ];
    header("Location: index.php");
    exit;
}

// Get armor types for dropdown
$armorTypes = [
    'NONE' => 'None',
    'HELMET' => 'Helmet',
    'ARMOR' => 'Armor',
    'T_SHIRT' => 'T-Shirt',
    'CLOAK' => 'Cloak',
    'GLOVE' => 'Glove',
    'BOOTS' => 'Boots',
    'SHIELD' => 'Shield',
    'AMULET' => 'Amulet',
    'RING' => 'Ring',
    'BELT' => 'Belt',
    'RING_2' => 'Ring (2)',
    'EARRING' => 'Earring',
    'GARDER' => 'Garder',
    'RON' => 'Ron',
    'PAIR' => 'Pair',
    'SENTENCE' => 'Sentence',
    'SHOULDER' => 'Shoulder',
    'BADGE' => 'Badge',
    'PENDANT' => 'Pendant'
];

$materialTypes = [
    'NONE(-)' => 'None',
    'LIQUID(액체)' => 'Liquid',
    'WAX(밀랍)' => 'Wax',
    'VEGGY(식물성)' => 'Vegetable',
    'FLESH(동물성)' => 'Flesh',
    'PAPER(종이)' => 'Paper',
    'CLOTH(천)' => 'Cloth',
    'LEATHER(가죽)' => 'Leather',
    'WOOD(나무)' => 'Wood',
    'BONE(뼈)' => 'Bone',
    'DRAGON_HIDE(용비늘)' => 'Dragon Hide',
    'IRON(철)' => 'Iron',
    'METAL(금속)' => 'Metal',
    'COPPER(구리)' => 'Copper',
    'SILVER(은)' => 'Silver',
    'GOLD(금)' => 'Gold',
    'PLATINUM(백금)' => 'Platinum',
    'MITHRIL(미스릴)' => 'Mithril',
    'PLASTIC(블랙미스릴)' => 'Plastic',
    'GLASS(유리)' => 'Glass',
    'GEMSTONE(보석)' => 'Gemstone',
    'MINERAL(광석)' => 'Mineral',
    'ORIHARUKON(오리하루콘)' => 'Oriharukon',
    'DRANIUM(드라니움)' => 'Dranium'
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

$yesNoOptions = [
    'true' => 'Yes',
    'false' => 'No'
];
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title"><?= htmlspecialchars($armor['desc_en']) ?></h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-tag me-2"></i>Item ID: <?= $armorId ?>
                    </span>
                    <span class="mx-3 text-muted">|</span>
                    <span class="text-muted fs-5">
                        Type: <?= $armorTypes[$armor['type']] ?? $armor['type'] ?>
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Armor
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
            <li class="breadcrumb-item"><a href="index.php">Armor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Armor</li>
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
            <!-- Armor Image and Basic Info -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Armor Preview
                </div>
                <div class="acquisition-card-body d-flex flex-column align-items-center justify-content-center">
                    <?php if (isset($armor['iconId']) && $armor['iconId'] > 0): ?>
                        <img id="item-image-preview" 
                             src="<?= SITE_URL ?>/assets/img/items/<?= $armor['iconId'] ?>.png" 
                             alt="<?= htmlspecialchars($armor['desc_en']) ?>" 
                             style="max-width: 128px;"
                             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png';">
                    <?php else: ?>
                        <img id="item-image-preview" 
                             src="<?= SITE_URL ?>/assets/img/items/default.png" 
                             alt="<?= htmlspecialchars($armor['desc_en']) ?>" 
                             style="max-width: 128px;">
                    <?php endif; ?>
                    
                    <h5 class="mt-3"><?= htmlspecialchars($armor['desc_en']) ?></h5>
                    <p class="mb-1"><?= $armorTypes[$armor['type']] ?? $armor['type'] ?></p>
                    <div class="item-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Item ID: <?= $armor['item_id'] ?></div>
                        <div class="badge bg-secondary">Icon ID: <?= $armor['iconId'] ?? 'N/A' ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Armor Stats Quick View -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Armor Stats
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>AC</span>
                            <span class="badge bg-info rounded-pill" id="ac-preview"><?= $armor['ac'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>M.DEF</span>
                            <span class="badge bg-purple rounded-pill" id="mdef-preview"><?= $armor['m_def'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Grade</span>
                            <span class="badge rarity-<?= strtolower($armor['itemGrade'] ?? 'normal') ?>" id="grade-preview"><?= $armor['itemGrade'] ?? 'NORMAL' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Safe Enchant</span>
                            <span class="badge bg-success rounded-pill" id="safenchant-preview"><?= $armor['safenchant'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Weight</span>
                            <span class="badge bg-secondary rounded-pill" id="weight-preview"><?= $armor['weight'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Level Range</span>
                            <span class="badge bg-primary rounded-pill" id="level-preview"><?= $armor['min_lvl'] ?>-<?= $armor['max_lvl'] ?: '∞' ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Armor Set Info -->
            <?php if ($armor['Set_Id'] > 0): 
                $setInfo = $db->getRow("SELECT * FROM armor_set WHERE id = ?", [$armor['Set_Id']]);
                if ($setInfo): ?>
                <div class="acquisition-card mb-4">
                    <div class="acquisition-card-header">
                        Armor Set
                    </div>
                    <div class="acquisition-card-body">
                        <h6 class="text-center"><?= htmlspecialchars($setInfo['note'] ?: 'Set #'.$setInfo['id']) ?></h6>
                        <?php if (!empty($setInfo['sets'])): ?>
                            <p><small>This armor is part of a set with the following bonuses:</small></p>
                            <ul class="list-group list-group-flush bg-transparent">
                                <?php if ($setInfo['str'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>STR</span>
                                        <span class="badge bg-info rounded-pill">+<?= $setInfo['str'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['con'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>CON</span>
                                        <span class="badge bg-info rounded-pill">+<?= $setInfo['con'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['dex'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>DEX</span>
                                        <span class="badge bg-info rounded-pill">+<?= $setInfo['dex'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['wis'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>WIS</span>
                                        <span class="badge bg-info rounded-pill">+<?= $setInfo['wis'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['intl'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>INT</span>
                                        <span class="badge bg-info rounded-pill">+<?= $setInfo['intl'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['cha'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>CHA</span>
                                        <span class="badge bg-info rounded-pill">+<?= $setInfo['cha'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['hp'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>HP</span>
                                        <span class="badge bg-danger rounded-pill">+<?= $setInfo['hp'] ?></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php if ($setInfo['mp'] > 0): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                        <span>MP</span>
                                        <span class="badge bg-primary rounded-pill">+<?= $setInfo['mp'] ?></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="col-md-9">
            <!-- Edit Form -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-edit me-2"></i> Edit Armor</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
                        <div class="row">
                            <!-- Form Tabs -->
                            <div class="col-lg-12 mb-4">
                                <div class="form-tabs">
                                    <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                    <button type="button" class="form-tab" data-tab="properties">Properties</button>
                                    <button type="button" class="form-tab" data-tab="stats">Stats</button>
                                    <button type="button" class="form-tab" data-tab="defenses">Defenses</button>
                                    <button type="button" class="form-tab" data-tab="classes">Restrictions</button>
                                    <button type="button" class="form-tab" data-tab="item_properties">Item</button>
                                    <button type="button" class="form-tab" data-tab="pvp">PVP</button>
                                    <button type="button" class="form-tab" data-tab="critical">Critical</button>
                                    <button type="button" class="form-tab" data-tab="set">Armor Set</button>
                                    <button type="button" class="form-tab" data-tab="advanced">Advanced</button>
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
                                                <label for="item_name_id" class="form-label">Item Name ID</label>
                                                <input type="number" class="form-control no-spinner" id="item_name_id" name="item_name_id" value="<?= (int)$armor['item_name_id'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_en" class="form-label">Armor Name (English) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="desc_en" name="desc_en" value="<?= htmlspecialchars($armor['desc_en']) ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_kr" class="form-label">Armor Name (Korean)</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr" value="<?= htmlspecialchars($armor['desc_kr'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_powerbook" class="form-label">Powerbook Description</label>
                                                <input type="text" class="form-control" id="desc_powerbook" name="desc_powerbook" value="<?= htmlspecialchars($armor['desc_powerbook'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_id" class="form-label">ID Description</label>
                                                <input type="text" class="form-control" id="desc_id" name="desc_id" value="<?= htmlspecialchars($armor['desc_id'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="type" class="form-label">Armor Type</label>
                                                <select class="form-select" id="type" name="type">
                                                    <?php foreach ($armorTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $armor['type'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="material" class="form-label">Material</label>
                                                <select class="form-select" id="material" name="material">
                                                    <?php foreach ($materialTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $armor['material'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="iconId" class="form-label">Icon ID</label>
                                                <input type="number" class="form-control no-spinner" id="iconId" name="iconId" value="<?= (int)$armor['iconId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="spriteId" class="form-label">Sprite ID</label>
                                                <input type="number" class="form-control no-spinner" id="spriteId" name="spriteId" value="<?= (int)$armor['spriteId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="weight" class="form-label">Weight</label>
                                                <input type="number" class="form-control no-spinner" id="weight" name="weight" value="<?= (int)$armor['weight'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="itemGrade" class="form-label">Item Grade</label>
                                                <select class="form-select" id="itemGrade" name="itemGrade">
                                                    <option value="">None</option>
                                                    <?php foreach ($itemGrades as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $armor['itemGrade'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="grade" class="form-label">Grade Number</label>
                                                <input type="number" class="form-control no-spinner" id="grade" name="grade" value="<?= (int)$armor['grade'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Armor Properties Section -->
                            <div class="col-lg-12 form-section" id="properties-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Armor Properties
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="ac" class="form-label">AC (Armor Class)</label>
                                                <input type="number" class="form-control no-spinner" id="ac" name="ac" value="<?= (int)$armor['ac'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="ac_sub" class="form-label">AC Sub</label>
                                                <input type="number" class="form-control no-spinner" id="ac_sub" name="ac_sub" value="<?= (int)$armor['ac_sub'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="m_def" class="form-label">Magic Defense</label>
                                                <input type="number" class="form-control no-spinner" id="m_def" name="m_def" value="<?= (int)$armor['m_def'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="damage_reduction" class="form-label">Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="<?= (int)$armor['damage_reduction'] ?>">
                                            </div>
											<div class="col-md-6 mb-3">
                                                <label for="hit_rate" class="form-label">Hit Rate</label>
                                                <input type="number" class="form-control no-spinner" id="hit_rate" name="hit_rate" value="<?= (int)$armor['hit_rate'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dmg_rate" class="form-label">Damage Rate</label>
                                                <input type="number" class="form-control no-spinner" id="dmg_rate" name="dmg_rate" value="<?= (int)$armor['dmg_rate'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="bow_hit_rate" class="form-label">Bow Hit Rate</label>
                                                <input type="number" class="form-control no-spinner" id="bow_hit_rate" name="bow_hit_rate" value="<?= (int)$armor['bow_hit_rate'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="bow_dmg_rate" class="form-label">Bow Damage Rate</label>
                                                <input type="number" class="form-control no-spinner" id="bow_dmg_rate" name="bow_dmg_rate" value="<?= (int)$armor['bow_dmg_rate'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="safenchant" class="form-label">Safe Enchant Level</label>
                                                <input type="number" class="form-control no-spinner" id="safenchant" name="safenchant" value="<?= (int)$armor['safenchant'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="haste_item" class="form-label">Haste Item</label>
                                                <input type="number" class="form-control no-spinner" id="haste_item" name="haste_item" value="<?= (int)$armor['haste_item'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="carryBonus" class="form-label">Carry Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="carryBonus" name="carryBonus" value="<?= (int)$armor['carryBonus'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="min_lvl" class="form-label">Minimum Level</label>
                                                <input type="number" class="form-control no-spinner" id="min_lvl" name="min_lvl" value="<?= (int)$armor['min_lvl'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_lvl" class="form-label">Maximum Level</label>
                                                <input type="number" class="form-control no-spinner" id="max_lvl" name="max_lvl" value="<?= (int)$armor['max_lvl'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_use_time" class="form-label">Max Use Time</label>
                                                <input type="number" class="form-control no-spinner" id="max_use_time" name="max_use_time" value="<?= (int)$armor['max_use_time'] ?>">
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
                                                <input type="number" class="form-control no-spinner" id="add_str" name="add_str" value="<?= (int)$armor['add_str'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_con" class="form-label">+ CON</label>
                                                <input type="number" class="form-control no-spinner" id="add_con" name="add_con" value="<?= (int)$armor['add_con'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_dex" class="form-label">+ DEX</label>
                                                <input type="number" class="form-control no-spinner" id="add_dex" name="add_dex" value="<?= (int)$armor['add_dex'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_int" class="form-label">+ INT</label>
                                                <input type="number" class="form-control no-spinner" id="add_int" name="add_int" value="<?= (int)$armor['add_int'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_wis" class="form-label">+ WIS</label>
                                                <input type="number" class="form-control no-spinner" id="add_wis" name="add_wis" value="<?= (int)$armor['add_wis'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_cha" class="form-label">+ CHA</label>
                                                <input type="number" class="form-control no-spinner" id="add_cha" name="add_cha" value="<?= (int)$armor['add_cha'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_hp" class="form-label">+ HP</label>
                                                <input type="number" class="form-control no-spinner" id="add_hp" name="add_hp" value="<?= (int)$armor['add_hp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_mp" class="form-label">+ MP</label>
                                                <input type="number" class="form-control no-spinner" id="add_mp" name="add_mp" value="<?= (int)$armor['add_mp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_hpr" class="form-label">+ HP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="add_hpr" name="add_hpr" value="<?= (int)$armor['add_hpr'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_mpr" class="form-label">+ MP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="add_mpr" name="add_mpr" value="<?= (int)$armor['add_mpr'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="add_sp" class="form-label">+ SP</label>
                                                <input type="number" class="form-control no-spinner" id="add_sp" name="add_sp" value="<?= (int)$armor['add_sp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="addDg" class="form-label">+ DG</label>
                                                <input type="number" class="form-control no-spinner" id="addDg" name="addDg" value="<?= (int)$armor['addDg'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="addEr" class="form-label">+ ER</label>
                                                <input type="number" class="form-control no-spinner" id="addEr" name="addEr" value="<?= (int)$armor['addEr'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="addMe" class="form-label">+ ME</label>
                                                <input type="number" class="form-control no-spinner" id="addMe" name="addMe" value="<?= (int)$armor['addMe'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Defenses & Resistances Section -->
                            <div class="col-lg-12 form-section" id="defenses-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Elemental Defense & Resistances
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="defense_water" class="form-label">Water Defense</label>
                                                <input type="number" class="form-control no-spinner" id="defense_water" name="defense_water" value="<?= (int)$armor['defense_water'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="defense_wind" class="form-label">Wind Defense</label>
                                                <input type="number" class="form-control no-spinner" id="defense_wind" name="defense_wind" value="<?= (int)$armor['defense_wind'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="defense_fire" class="form-label">Fire Defense</label>
                                                <input type="number" class="form-control no-spinner" id="defense_fire" name="defense_fire" value="<?= (int)$armor['defense_fire'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="defense_earth" class="form-label">Earth Defense</label>
                                                <input type="number" class="form-control no-spinner" id="defense_earth" name="defense_earth" value="<?= (int)$armor['defense_earth'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="attr_all" class="form-label">All Attributes</label>
                                                <input type="number" class="form-control no-spinner" id="attr_all" name="attr_all" value="<?= (int)$armor['attr_all'] ?>">
                                            </div>
                                            
                                            <div class="col-12"><hr class="border-gray-600"></div>

                                            <div class="col-md-4 mb-3">
                                                <label for="regist_skill" class="form-label">Skill Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_skill" name="regist_skill" value="<?= (int)$armor['regist_skill'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_stone" class="form-label">Stone Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_stone" name="regist_stone" value="<?= (int)$armor['regist_stone'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_sleep" class="form-label">Sleep Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_sleep" name="regist_sleep" value="<?= (int)$armor['regist_sleep'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_freeze" class="form-label">Freeze Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_freeze" name="regist_freeze" value="<?= (int)$armor['regist_freeze'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_blind" class="form-label">Blind Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_blind" name="regist_blind" value="<?= (int)$armor['regist_blind'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_spirit" class="form-label">Spirit Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_spirit" name="regist_spirit" value="<?= (int)$armor['regist_spirit'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_dragon" class="form-label">Dragon Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_dragon" name="regist_dragon" value="<?= (int)$armor['regist_dragon'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_fear" class="form-label">Fear Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="regist_fear" name="regist_fear" value="<?= (int)$armor['regist_fear'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="regist_all" class="form-label">All Resistances</label>
                                                <input type="number" class="form-control no-spinner" id="regist_all" name="regist_all" value="<?= (int)$armor['regist_all'] ?>">
                                            </div>
                                            
                                            <div class="col-12"><hr class="border-gray-600"></div>
                                            
                                            <div class="col-md-4 mb-3">
                                                <label for="hitup_skill" class="form-label">Hit Up Skill</label>
                                                <input type="number" class="form-control no-spinner" id="hitup_skill" name="hitup_skill" value="<?= (int)$armor['hitup_skill'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="hitup_spirit" class="form-label">Hit Up Spirit</label>
                                                <input type="number" class="form-control no-spinner" id="hitup_spirit" name="hitup_spirit" value="<?= (int)$armor['hitup_spirit'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="hitup_dragon" class="form-label">Hit Up Dragon</label>
                                                <input type="number" class="form-control no-spinner" id="hitup_dragon" name="hitup_dragon" value="<?= (int)$armor['hitup_dragon'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="hitup_fear" class="form-label">Hit Up Fear</label>
                                                <input type="number" class="form-control no-spinner" id="hitup_fear" name="hitup_fear" value="<?= (int)$armor['hitup_fear'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="hitup_all" class="form-label">Hit Up All</label>
                                                <input type="number" class="form-control no-spinner" id="hitup_all" name="hitup_all" value="<?= (int)$armor['hitup_all'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="hitup_magic" class="form-label">Hit Up Magic</label>
                                                <input type="number" class="form-control no-spinner" id="hitup_magic" name="hitup_magic" value="<?= (int)$armor['hitup_magic'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="poisonRegist" class="form-label">Poison Resistance</label>
                                                <select class="form-select" id="poisonRegist" name="poisonRegist">
                                                    <?php foreach ($yesNoOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $armor['poisonRegist'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
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
                                                    <input class="form-check-input" type="checkbox" id="use_royal" name="use_royal" <?= $armor['use_royal'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_royal">Royal</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_knight" name="use_knight" <?= $armor['use_knight'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_knight">Knight</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_elf" name="use_elf" <?= $armor['use_elf'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_elf">Elf</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_mage" name="use_mage" <?= $armor['use_mage'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_mage">Mage</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_darkelf" name="use_darkelf" <?= $armor['use_darkelf'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_darkelf">Dark Elf</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_dragonknight" name="use_dragonknight" <?= $armor['use_dragonknight'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_dragonknight">Dragon Knight</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_illusionist" name="use_illusionist" <?= $armor['use_illusionist'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_illusionist">Illusionist</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_warrior" name="use_warrior" <?= $armor['use_warrior'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_warrior">Warrior</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_fencer" name="use_fencer" <?= $armor['use_fencer'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="use_fencer">Fencer</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="use_lancer" name="use_lancer" <?= $armor['use_lancer'] ? 'checked' : '' ?>>
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
                                                    <input class="form-check-input" type="checkbox" id="bless" name="bless" <?= $armor['bless'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="bless">Blessed</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="trade" name="trade" <?= $armor['trade'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="trade">Tradeable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cant_delete" name="cant_delete" <?= $armor['cant_delete'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="cant_delete">Can't Delete</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="cant_sell" name="cant_sell" <?= $armor['cant_sell'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="cant_sell">Can't Sell</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="retrieve" name="retrieve" <?= $armor['retrieve'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="retrieve">Retrievable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="specialretrieve" name="specialretrieve" <?= $armor['specialretrieve'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="specialretrieve">Special Retrieve</label>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="retrieveEnchant" class="form-label">Retrieve Enchant</label>
                                                <input type="number" class="form-control no-spinner" id="retrieveEnchant" name="retrieveEnchant" value="<?= (int)$armor['retrieveEnchant'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PVP Section -->
                            <div class="col-lg-12 form-section" id="pvp-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        PVP Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamage" class="form-label">PVP Damage</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamage" name="PVPDamage" value="<?= (int)$armor['PVPDamage'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamagePercent" class="form-label">PVP Damage Percent</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamagePercent" name="PVPDamagePercent" value="<?= (int)$armor['PVPDamagePercent'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamageReduction" class="form-label">PVP Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamageReduction" name="PVPDamageReduction" value="<?= (int)$armor['PVPDamageReduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPDamageReductionPercent" class="form-label">PVP Damage Reduction %</label>
                                                <input type="number" class="form-control no-spinner" id="PVPDamageReductionPercent" name="PVPDamageReductionPercent" value="<?= (int)$armor['PVPDamageReductionPercent'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPMagicDamageReduction" class="form-label">PVP Magic Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="PVPMagicDamageReduction" name="PVPMagicDamageReduction" value="<?= (int)$armor['PVPMagicDamageReduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPReductionEgnor" class="form-label">PVP Reduction Ignore</label>
                                                <input type="number" class="form-control no-spinner" id="PVPReductionEgnor" name="PVPReductionEgnor" value="<?= (int)$armor['PVPReductionEgnor'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="PVPMagicDamageReductionEgnor" class="form-label">PVP Magic Dmg Reduction Ignore</label>
                                                <input type="number" class="form-control no-spinner" id="PVPMagicDamageReductionEgnor" name="PVPMagicDamageReductionEgnor" value="<?= (int)$armor['PVPMagicDamageReductionEgnor'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="abnormalStatusDamageReduction" class="form-label">Abnormal Status Dmg Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="abnormalStatusDamageReduction" name="abnormalStatusDamageReduction" value="<?= (int)$armor['abnormalStatusDamageReduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="abnormalStatusPVPDamageReduction" class="form-label">Abnormal Status PVP Dmg Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="abnormalStatusPVPDamageReduction" name="abnormalStatusPVPDamageReduction" value="<?= (int)$armor['abnormalStatusPVPDamageReduction'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Critical Section -->
                            <div class="col-lg-12 form-section" id="critical-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Critical Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="shortCritical" class="form-label">Short Range Critical</label>
                                                <input type="number" class="form-control no-spinner" id="shortCritical" name="shortCritical" value="<?= (int)$armor['shortCritical'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="longCritical" class="form-label">Long Range Critical</label>
                                                <input type="number" class="form-control no-spinner" id="longCritical" name="longCritical" value="<?= (int)$armor['longCritical'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="magicCritical" class="form-label">Magic Critical</label>
                                                <input type="number" class="form-control no-spinner" id="magicCritical" name="magicCritical" value="<?= (int)$armor['magicCritical'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Set Section -->
                            <div class="col-lg-12 form-section" id="set-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Armor Set Information
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="Set_Id" class="form-label">Armor Set</label>
                                                <select class="form-select" id="Set_Id" name="Set_Id">
                                                    <option value="0">None</option>
                                                    <?php foreach ($armorSets as $set): ?>
                                                        <option value="<?= $set['id'] ?>" <?= $armor['Set_Id'] == $set['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($set['note'] ?: 'Set #'.$set['id']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="polyDescId" class="form-label">Polymorph Description ID</label>
                                                <input type="number" class="form-control no-spinner" id="polyDescId" name="polyDescId" value="<?= (int)$armor['polyDescId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="MainId" class="form-label">Main ID</label>
                                                <input type="number" class="form-control no-spinner" id="MainId" name="MainId" value="<?= (int)$armor['MainId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="MainId2" class="form-label">Main ID 2</label>
                                                <input type="number" class="form-control no-spinner" id="MainId2" name="MainId2" value="<?= (int)$armor['MainId2'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="MainId3" class="form-label">Main ID 3</label>
                                                <input type="number" class="form-control no-spinner" id="MainId3" name="MainId3" value="<?= (int)$armor['MainId3'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="Magic_name" class="form-label">Magic Name</label>
                                                <input type="text" class="form-control" id="Magic_name" name="Magic_name" value="<?= htmlspecialchars($armor['Magic_name'] ?? '') ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Section -->
                            <div class="col-lg-12 form-section" id="advanced-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Advanced Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="expBonus" class="form-label">EXP Bonus (%)</label>
                                                <input type="number" class="form-control no-spinner" id="expBonus" name="expBonus" value="<?= (int)$armor['expBonus'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="rest_exp_reduce_efficiency" class="form-label">Rest EXP Reduce Efficiency</label>
                                                <input type="number" class="form-control no-spinner" id="rest_exp_reduce_efficiency" name="rest_exp_reduce_efficiency" value="<?= (int)$armor['rest_exp_reduce_efficiency'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="MagicDamageReduction" class="form-label">Magic Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="MagicDamageReduction" name="MagicDamageReduction" value="<?= (int)$armor['MagicDamageReduction'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reductionEgnor" class="form-label">Reduction Ignore</label>
                                                <input type="number" class="form-control no-spinner" id="reductionEgnor" name="reductionEgnor" value="<?= (int)$armor['reductionEgnor'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reductionPercent" class="form-label">Reduction Percent</label>
                                                <input type="number" class="form-control no-spinner" id="reductionPercent" name="reductionPercent" value="<?= (int)$armor['reductionPercent'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="imunEgnor" class="form-label">Immunity Ignore</label>
                                                <input type="number" class="form-control no-spinner" id="imunEgnor" name="imunEgnor" value="<?= (int)$armor['imunEgnor'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="stunDuration" class="form-label">Stun Duration</label>
                                                <input type="number" class="form-control no-spinner" id="stunDuration" name="stunDuration" value="<?= (int)$armor['stunDuration'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tripleArrowStun" class="form-label">Triple Arrow Stun</label>
                                                <input type="number" class="form-control no-spinner" id="tripleArrowStun" name="tripleArrowStun" value="<?= (int)$armor['tripleArrowStun'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="strangeTimeIncrease" class="form-label">Strange Time Increase</label>
                                                <input type="number" class="form-control no-spinner" id="strangeTimeIncrease" name="strangeTimeIncrease" value="<?= (int)$armor['strangeTimeIncrease'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="strangeTimeDecrease" class="form-label">Strange Time Decrease</label>
                                                <input type="number" class="form-control no-spinner" id="strangeTimeDecrease" name="strangeTimeDecrease" value="<?= (int)$armor['strangeTimeDecrease'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="potionRegist" class="form-label">Potion Resist</label>
                                                <input type="number" class="form-control no-spinner" id="potionRegist" name="potionRegist" value="<?= (int)$armor['potionRegist'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="potionPercent" class="form-label">Potion Percent</label>
                                                <input type="number" class="form-control no-spinner" id="potionPercent" name="potionPercent" value="<?= (int)$armor['potionPercent'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="potionValue" class="form-label">Potion Value</label>
                                                <input type="number" class="form-control no-spinner" id="potionValue" name="potionValue" value="<?= (int)$armor['potionValue'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="hprAbsol32Second" class="form-label">HPR Absol 32 Second</label>
                                                <input type="number" class="form-control no-spinner" id="hprAbsol32Second" name="hprAbsol32Second" value="<?= (int)$armor['hprAbsol32Second'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="mprAbsol64Second" class="form-label">MPR Absol 64 Second</label>
                                                <input type="number" class="form-control no-spinner" id="mprAbsol64Second" name="mprAbsol64Second" value="<?= (int)$armor['mprAbsol64Second'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="mprAbsol16Second" class="form-label">MPR Absol 16 Second</label>
                                                <input type="number" class="form-control no-spinner" id="mprAbsol16Second" name="mprAbsol16Second" value="<?= (int)$armor['mprAbsol16Second'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="hpPotionDelayDecrease" class="form-label">HP Potion Delay Decrease</label>
                                                <input type="number" class="form-control no-spinner" id="hpPotionDelayDecrease" name="hpPotionDelayDecrease" value="<?= (int)$armor['hpPotionDelayDecrease'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="hpPotionCriticalProb" class="form-label">HP Potion Critical Prob</label>
                                                <input type="number" class="form-control no-spinner" id="hpPotionCriticalProb" name="hpPotionCriticalProb" value="<?= (int)$armor['hpPotionCriticalProb'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="increaseArmorSkillProb" class="form-label">Increase Armor Skill Prob</label>
                                                <input type="number" class="form-control no-spinner" id="increaseArmorSkillProb" name="increaseArmorSkillProb" value="<?= (int)$armor['increaseArmorSkillProb'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="attackSpeedDelayRate" class="form-label">Attack Speed Delay Rate</label>
                                                <input type="number" class="form-control no-spinner" id="attackSpeedDelayRate" name="attackSpeedDelayRate" value="<?= (int)$armor['attackSpeedDelayRate'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="moveSpeedDelayRate" class="form-label">Move Speed Delay Rate</label>
                                                <input type="number" class="form-control no-spinner" id="moveSpeedDelayRate" name="moveSpeedDelayRate" value="<?= (int)$armor['moveSpeedDelayRate'] ?>">
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
                                            <textarea class="form-control" id="note" name="note" rows="5"><?= htmlspecialchars($armor['note'] ?? '') ?></textarea>
                                            <small>Enter any additional information about this armor.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Update Armor</button>
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
    
    // Live update armor name in preview
    const nameInput = document.getElementById('desc_en');
    const namePreview = document.querySelector('.acquisition-card-body h5');
    
    if (nameInput && namePreview) {
        nameInput.addEventListener('input', function() {
            const armorName = this.value.trim();
            namePreview.textContent = armorName || 'Unnamed Armor';
        });
    }
    
    // Live update armor type in preview
    const typeSelect = document.getElementById('type');
    const typePreview = document.querySelector('.acquisition-card-body p');
    
    if (typeSelect && typePreview) {
        typeSelect.addEventListener('change', function() {
            const selectedIndex = this.selectedIndex;
            if (selectedIndex >= 0) {
                const selectedOption = this.options[selectedIndex];
                typePreview.textContent = selectedOption.text;
            }
        });
    }
    
    // Live update stats in the sidebar
    const acInput = document.getElementById('ac');
    const mDefInput = document.getElementById('m_def');
    const gradeSelect = document.getElementById('itemGrade');
    const safeEnchantInput = document.getElementById('safenchant');
    const weightInput = document.getElementById('weight');
    const minLvlInput = document.getElementById('min_lvl');
    const maxLvlInput = document.getElementById('max_lvl');
    
    // Get preview elements
    const acPreview = document.getElementById('ac-preview');
    const mDefPreview = document.getElementById('mdef-preview');
    const gradePreview = document.getElementById('grade-preview');
    const safeEnchantPreview = document.getElementById('safenchant-preview');
    const weightPreview = document.getElementById('weight-preview');
    const levelRangePreview = document.getElementById('level-preview');
    
    // Update AC preview
    if (acInput && acPreview) {
        acInput.addEventListener('input', function() {
            acPreview.textContent = this.value || '0';
        });
    }
    
    // Update Magic Defense preview
    if (mDefInput && mDefPreview) {
        mDefInput.addEventListener('input', function() {
            mDefPreview.textContent = this.value || '0';
        });
    }
    
    // Update grade preview
    if (gradeSelect && gradePreview) {
        gradeSelect.addEventListener('change', function() {
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