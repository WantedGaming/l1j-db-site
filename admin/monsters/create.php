<?php
/**
 * Admin - Create New Monster
 */

// Set page title
$pageTitle = 'Add New Monster';

// Include admin header
require_once '../../includes/admin-header.php';

// Include Monster model
require_once '../../models/Monster.php';

// Get database instance
$db = Database::getInstance();

// Initialize model
$monsterModel = new Monster();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect monster data from form
    $monster = [
        'npcid' => intval($_POST['npcid'] ?? 0),
        'classId' => intval($_POST['classId'] ?? 0),
        'desc_en' => $_POST['desc_en'] ?? '',
        'desc_powerbook' => $_POST['desc_powerbook'] ?? '',
        'desc_kr' => $_POST['desc_kr'] ?? '',
        'desc_id' => $_POST['desc_id'] ?? '',
        'note' => $_POST['note'] ?? '',
        'impl' => $_POST['impl'] ?? 'L1Monster',
        'spriteId' => intval($_POST['spriteId'] ?? 0),
        'lvl' => intval($_POST['lvl'] ?? 0),
        'hp' => intval($_POST['hp'] ?? 0),
        'mp' => intval($_POST['mp'] ?? 0),
        'ac' => intval($_POST['ac'] ?? 0),
        'str' => intval($_POST['str'] ?? 0),
        'con' => intval($_POST['con'] ?? 0),
        'dex' => intval($_POST['dex'] ?? 0),
        'wis' => intval($_POST['wis'] ?? 0),
        'intel' => intval($_POST['intel'] ?? 0),
        'mr' => intval($_POST['mr'] ?? 0),
        'exp' => intval($_POST['exp'] ?? 0),
        'alignment' => intval($_POST['alignment'] ?? 0),
        'big' => isset($_POST['big']) ? 'true' : 'false',
        'weakAttr' => $_POST['weakAttr'] ?? 'NONE',
        'ranged' => intval($_POST['ranged'] ?? 0),
        'is_taming' => isset($_POST['is_taming']) ? 'true' : 'false',
        'passispeed' => intval($_POST['passispeed'] ?? 0),
        'atkspeed' => intval($_POST['atkspeed'] ?? 0),
        'atk_magic_speed' => intval($_POST['atk_magic_speed'] ?? 0),
        'sub_magic_speed' => intval($_POST['sub_magic_speed'] ?? 0),
        'undead' => $_POST['undead'] ?? 'NONE',
        'poison_atk' => $_POST['poison_atk'] ?? 'NONE',
        'is_agro' => isset($_POST['is_agro']) ? 'true' : 'false',
        'is_agro_poly' => isset($_POST['is_agro_poly']) ? 'true' : 'false',
        'is_agro_invis' => isset($_POST['is_agro_invis']) ? 'true' : 'false',
        'family' => $_POST['family'] ?? '',
        'agrofamily' => intval($_POST['agrofamily'] ?? 0),
        'agrogfxid1' => intval($_POST['agrogfxid1'] ?? -1),
        'agrogfxid2' => intval($_POST['agrogfxid2'] ?? -1),
        'is_picupitem' => isset($_POST['is_picupitem']) ? 'true' : 'false',
        'digestitem' => intval($_POST['digestitem'] ?? 0),
        'is_bravespeed' => isset($_POST['is_bravespeed']) ? 'true' : 'false',
        'hprinterval' => intval($_POST['hprinterval'] ?? 0),
        'hpr' => intval($_POST['hpr'] ?? 0),
        'mprinterval' => intval($_POST['mprinterval'] ?? 0),
        'mpr' => intval($_POST['mpr'] ?? 0),
        'is_teleport' => isset($_POST['is_teleport']) ? 'true' : 'false',
        'randomlevel' => intval($_POST['randomlevel'] ?? 0),
        'randomhp' => intval($_POST['randomhp'] ?? 0),
        'randommp' => intval($_POST['randommp'] ?? 0),
        'randomac' => intval($_POST['randomac'] ?? 0),
        'randomexp' => intval($_POST['randomexp'] ?? 0),
        'randomAlign' => intval($_POST['randomAlign'] ?? 0),
        'damage_reduction' => intval($_POST['damage_reduction'] ?? 0),
        'is_hard' => isset($_POST['is_hard']) ? 'true' : 'false',
        'is_bossmonster' => isset($_POST['is_bossmonster']) ? 'true' : 'false',
        'can_turnundead' => isset($_POST['can_turnundead']) ? 'true' : 'false',
        'bowSpritetId' => intval($_POST['bowSpritetId'] ?? 0),
        'karma' => intval($_POST['karma'] ?? 0),
        'transform_id' => intval($_POST['transform_id'] ?? -1),
        'transform_gfxid' => intval($_POST['transform_gfxid'] ?? 0),
        'light_size' => intval($_POST['light_size'] ?? 0),
        'is_amount_fixed' => isset($_POST['is_amount_fixed']) ? 'true' : 'false',
        'is_change_head' => isset($_POST['is_change_head']) ? 'true' : 'false',
        'spawnlist_door' => intval($_POST['spawnlist_door'] ?? 0),
        'count_map' => intval($_POST['count_map'] ?? 0),
        'cant_resurrect' => isset($_POST['cant_resurrect']) ? 'true' : 'false',
        'isHide' => isset($_POST['isHide']) ? 'true' : 'false'
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($monster['npcid'])) {
        $errors[] = "Monster ID is required";
    }
    
    if (empty($monster['desc_en'])) {
        $errors[] = "Monster name is required";
    }
    
    // Check if monster ID already exists
    $existingMonster = $db->getRow("SELECT npcid FROM npc WHERE npcid = ?", [$monster['npcid']]);
    if ($existingMonster) {
        $errors[] = "A monster with ID {$monster['npcid']} already exists";
    }
    
    // If no errors, insert the monster
    if (empty($errors)) {
        // Create the monster
        $result = $monsterModel->createMonster($monster);
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Monster '{$monster['desc_en']}' created successfully."
            ];
            
            // Redirect to monster list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to create monster. Database error.";
        }
    }
}

// Generate next available NPC ID
$nextNpcId = $monsterModel->getNextNpcId();

// Initialize default monster values
$monster = [
    'npcid' => $nextNpcId,
    'classId' => 0,
    'desc_en' => '',
    'desc_powerbook' => '',
    'desc_kr' => '',
    'desc_id' => '',
    'note' => '',
    'lvl' => 1,
    'hp' => 100,
    'mp' => 10,
    'ac' => 10,
    'str' => 10,
    'con' => 10,
    'dex' => 10,
    'wis' => 10,
    'intel' => 10,
    'mr' => 0,
    'exp' => 10,
    'alignment' => 0,
    'big' => 'false',
    'weakAttr' => 'NONE',
    'ranged' => 0,
    'is_taming' => 'false',
    'passispeed' => 480,
    'atkspeed' => 480,
    'atk_magic_speed' => 0,
    'sub_magic_speed' => 0,
    'undead' => 'NONE',
    'poison_atk' => 'NONE',
    'is_agro' => 'false',
    'is_agro_poly' => 'false',
    'is_agro_invis' => 'false',
    'family' => '',
    'agrofamily' => 0,
    'agrogfxid1' => -1,
    'agrogfxid2' => -1,
    'is_picupitem' => 'false',
    'digestitem' => 0,
    'is_bravespeed' => 'false',
    'hprinterval' => 0,
    'hpr' => 0,
    'mprinterval' => 0,
    'mpr' => 0,
    'is_teleport' => 'false',
    'randomlevel' => 0,
    'randomhp' => 0,
    'randommp' => 0,
    'randomac' => 0,
    'randomexp' => 0,
    'randomAlign' => 0,
    'damage_reduction' => 0,
    'is_hard' => 'false',
    'is_bossmonster' => 'false',
    'can_turnundead' => 'false',
    'bowSpritetId' => 0,
    'karma' => 0,
    'transform_id' => -1,
    'transform_gfxid' => 0,
    'light_size' => 0,
    'is_amount_fixed' => 'false',
    'is_change_head' => 'false',
    'spawnlist_door' => 0,
    'count_map' => 0,
    'cant_resurrect' => 'false',
    'isHide' => 'false',
    'spriteId' => 0
];

// Size options
$sizeOptions = ['false' => 'Normal', 'true' => 'Big'];

// Attribute options
$attributeOptions = [
    'NONE' => 'None',
    'EARTH' => 'Earth',
    'FIRE' => 'Fire',
    'WATER' => 'Water',
    'WIND' => 'Wind'
];

// Undead type options
$undeadOptions = [
    'NONE' => 'None',
    'UNDEAD' => 'Undead',
    'DEMON' => 'Demon',
    'UNDEAD_BOSS' => 'Undead Boss',
    'DRANIUM' => 'Dranium'
];

// Yes/No options
$yesNoOptions = [
    'true' => 'Yes',
    'false' => 'No'
];

// Poison attack options
$poisonAtkOptions = [
    'NONE' => 'None',
    'DAMAGE' => 'Damage',
    'PARALYSIS' => 'Paralysis',
    'SILENCE' => 'Silence'
];
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title">Add New Monster</h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-dragon me-2"></i>Monster ID: <?= $nextNpcId ?>
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Monsters
                    </a>
                    <button type="button" onclick="document.getElementById('createForm').reset();" class="btn" style="background-color: #343434; color: #e0e0e0;">
                        <i class="fas fa-undo me-1"></i> Reset Form
                    </button>
                    <button type="button" onclick="document.getElementById('createForm').submit();" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-save me-1"></i> Create Monster
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
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php">Monsters</a></li>
            <li class="breadcrumb-item active" aria-current="page">Add New Monster</li>
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
            <!-- Monster Image and Basic Info -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Monster Preview
                </div>
                <div class="acquisition-card-body d-flex flex-column align-items-center justify-content-center">
                    <img id="monster-image-preview" 
                         src="<?= SITE_URL ?>/assets/img/monsters/<?= $nextNpcId ?>.png" 
                         alt="Monster Image Preview" 
                         style="max-width: 128px;"
                         onerror="this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
                    
                    <h5 class="mt-3">New Monster</h5>
                    <p class="mb-1">Level: 1</p>
                    <div class="monster-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Monster ID: <?= $nextNpcId ?></div>
                        <div class="badge bg-info">Level: 1</div>
                    </div>
                </div>
            </div>
            
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Monster Stats
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>HP</span>
                            <span class="badge bg-danger rounded-pill" id="hp-preview">100</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>MP</span>
                            <span class="badge bg-primary rounded-pill" id="mp-preview">10</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>AC</span>
                            <span class="badge bg-success rounded-pill" id="ac-preview">10</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Exp</span>
                            <span class="badge bg-warning rounded-pill" id="exp-preview">10</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Type</span>
                            <span class="badge bg-secondary" id="boss-preview">Normal</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Create Form -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-plus-circle me-2"></i> Add New Monster</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="createForm">
                        <div class="row">
                            <!-- Form Tabs -->
                            <div class="col-lg-12 mb-4">
                                <div class="form-tabs">
                                    <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                    <button type="button" class="form-tab" data-tab="stats">Stats</button>
                                    <button type="button" class="form-tab" data-tab="attributes">Attributes</button>
                                    <button type="button" class="form-tab" data-tab="behavior">Behavior</button>
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
                                                <label for="npcid" class="form-label">Monster ID <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control no-spinner" id="npcid" name="npcid" value="<?= $nextNpcId ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_en" class="form-label">Monster Name (English) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="desc_en" name="desc_en" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_powerbook" class="form-label">Monster Name (Powerbook)</label>
                                                <input type="text" class="form-control" id="desc_powerbook" name="desc_powerbook">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_kr" class="form-label">Monster Name (Korean)</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_id" class="form-label">ID Description</label>
                                                <input type="text" class="form-control" id="desc_id" name="desc_id">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="classId" class="form-label">Class ID</label>
                                                <input type="number" class="form-control no-spinner" id="classId" name="classId" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="family" class="form-label">Family</label>
                                                <input type="text" class="form-control" id="family" name="family">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="lvl" class="form-label">Level</label>
                                                <input type="number" class="form-control no-spinner" id="lvl" name="lvl" value="1">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="big" class="form-label">Size</label>
                                                <select class="form-select" id="big" name="big">
                                                    <?php foreach ($sizeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $value === 'false' ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="impl" class="form-label">Implementation</label>
                                                <input type="text" class="form-control" id="impl" name="impl" value="L1Monster">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_bossmonster" name="is_bossmonster">
                                                    <label class="form-check-label" for="is_bossmonster">
                                                        Boss Monster
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="spriteId" class="form-label">Sprite ID</label>
                                                <input type="number" class="form-control no-spinner" id="spriteId" name="spriteId" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Section -->
                            <div class="col-lg-12 form-section" id="stats-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Stats
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="hp" class="form-label">HP</label>
                                                <input type="number" class="form-control no-spinner" id="hp" name="hp" value="100">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="mp" class="form-label">MP</label>
                                                <input type="number" class="form-control no-spinner" id="mp" name="mp" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="ac" class="form-label">AC</label>
                                                <input type="number" class="form-control no-spinner" id="ac" name="ac" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="str" class="form-label">STR</label>
                                                <input type="number" class="form-control no-spinner" id="str" name="str" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="con" class="form-label">CON</label>
                                                <input type="number" class="form-control no-spinner" id="con" name="con" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="dex" class="form-label">DEX</label>
                                                <input type="number" class="form-control no-spinner" id="dex" name="dex" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="wis" class="form-label">WIS</label>
                                                <input type="number" class="form-control no-spinner" id="wis" name="wis" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="intel" class="form-label">INT</label>
                                                <input type="number" class="form-control no-spinner" id="intel" name="intel" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="mr" class="form-label">Magic Resistance</label>
                                                <input type="number" class="form-control no-spinner" id="mr" name="mr" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="exp" class="form-label">EXP</label>
                                                <input type="number" class="form-control no-spinner" id="exp" name="exp" value="10">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="alignment" class="form-label">Alignment</label>
                                                <input type="number" class="form-control no-spinner" id="alignment" name="alignment" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="karma" class="form-label">Karma</label>
                                                <input type="number" class="form-control no-spinner" id="karma" name="karma" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card bg-dark mt-4">
                                    <div class="card-header">
                                        Regeneration
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="hpr" class="form-label">HP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="hpr" name="hpr" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="hprinterval" class="form-label">HP Regen Interval</label>
                                                <input type="number" class="form-control no-spinner" id="hprinterval" name="hprinterval" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="mpr" class="form-label">MP Regen</label>
                                                <input type="number" class="form-control no-spinner" id="mpr" name="mpr" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="mprinterval" class="form-label">MP Regen Interval</label>
                                                <input type="number" class="form-control no-spinner" id="mprinterval" name="mprinterval" value="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Attributes Section -->
                            <div class="col-lg-12 form-section" id="attributes-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Elemental Attributes
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="weakAttr" class="form-label">Weak Attribute</label>
                                                <select class="form-select" id="weakAttr" name="weakAttr">
                                                    <?php foreach ($attributeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="undead" class="form-label">Undead Type</label>
                                                <select class="form-select" id="undead" name="undead">
                                                    <?php foreach ($undeadOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="poison_atk" class="form-label">Poison Attack</label>
                                                <select class="form-select" id="poison_atk" name="poison_atk">
                                                    <?php foreach ($poisonAtkOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="can_turnundead" name="can_turnundead">
                                                    <label class="form-check-label" for="can_turnundead">
                                                        Affected by Turn Undead
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card bg-dark mt-4">
                                    <div class="card-header">
                                        Damage Reduction
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="damage_reduction" class="form-label">Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_hard" name="is_hard">
                                                    <label class="form-check-label" for="is_hard">
                                                        Hard Monster (Resistant to damage)
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Behavior Section -->
                            <div class="col-lg-12 form-section" id="behavior-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Movement & Combat Behavior
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="passispeed" class="form-label">Passive Speed</label>
                                                <input type="number" class="form-control no-spinner" id="passispeed" name="passispeed" value="480">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="atkspeed" class="form-label">Attack Speed</label>
                                                <input type="number" class="form-control no-spinner" id="atkspeed" name="atkspeed" value="480">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="atk_magic_speed" class="form-label">Magic Attack Speed</label>
                                                <input type="number" class="form-control no-spinner" id="atk_magic_speed" name="atk_magic_speed" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="sub_magic_speed" class="form-label">Sub Magic Speed</label>
                                                <input type="number" class="form-control no-spinner" id="sub_magic_speed" name="sub_magic_speed" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="ranged" class="form-label">Ranged Attack Distance</label>
                                                <input type="number" class="form-control no-spinner" id="ranged" name="ranged" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="bowSpritetId" class="form-label">Bow Sprite ID</label>
                                                <input type="number" class="form-control no-spinner" id="bowSpritetId" name="bowSpritetId" value="0">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_agro" name="is_agro">
                                                    <label class="form-check-label" for="is_agro">
                                                        Aggressive
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_agro_poly" name="is_agro_poly">
                                                    <label class="form-check-label" for="is_agro_poly">
                                                        Aggressive (Poly)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_agro_invis" name="is_agro_invis">
                                                    <label class="form-check-label" for="is_agro_invis">
                                                        Aggressive (Invisible)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_teleport" name="is_teleport">
                                                    <label class="form-check-label" for="is_teleport">
                                                        Can Teleport
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_taming" name="is_taming">
                                                    <label class="form-check-label" for="is_taming">
                                                        Can Be Tamed
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_picupitem" name="is_picupitem">
                                                    <label class="form-check-label" for="is_picupitem">
                                                        Loot Items
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_bravespeed" name="is_bravespeed">
                                                    <label class="form-check-label" for="is_bravespeed">
                                                        Brave Speed
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch mt-4">
                                                    <input class="form-check-input" type="checkbox" id="cant_resurrect" name="cant_resurrect">
                                                    <label class="form-check-label" for="cant_resurrect">
                                                        Cannot Be Resurrected
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card bg-dark mt-4">
                                    <div class="card-header">
                                        Family Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="agrofamily" class="form-label">Aggro Family</label>
                                                <input type="number" class="form-control no-spinner" id="agrofamily" name="agrofamily" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="agrogfxid1" class="form-label">Aggro GFX ID 1</label>
                                                <input type="number" class="form-control no-spinner" id="agrogfxid1" name="agrogfxid1" value="-1">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="agrogfxid2" class="form-label">Aggro GFX ID 2</label>
                                                <input type="number" class="form-control no-spinner" id="agrogfxid2" name="agrogfxid2" value="-1">
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
                                            <div class="col-md-4 mb-3">
                                                <label for="digestitem" class="form-label">Digest Item</label>
                                                <input type="number" class="form-control no-spinner" id="digestitem" name="digestitem" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="transform_id" class="form-label">Transform ID</label>
                                                <input type="number" class="form-control no-spinner" id="transform_id" name="transform_id" value="-1">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="transform_gfxid" class="form-label">Transform GFX ID</label>
                                                <input type="number" class="form-control no-spinner" id="transform_gfxid" name="transform_gfxid" value="0">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="light_size" class="form-label">Light Size</label>
                                                <input type="number" class="form-control no-spinner" id="light_size" name="light_size" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="spawnlist_door" class="form-label">Spawnlist Door</label>
                                                <input type="number" class="form-control no-spinner" id="spawnlist_door" name="spawnlist_door" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="count_map" class="form-label">Count Map</label>
                                                <input type="number" class="form-control no-spinner" id="count_map" name="count_map" value="0">
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_amount_fixed" name="is_amount_fixed">
                                                    <label class="form-check-label" for="is_amount_fixed">
                                                        Amount Fixed
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_change_head" name="is_change_head">
                                                    <label class="form-check-label" for="is_change_head">
                                                        Change Head
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="isHide" name="isHide">
                                                    <label class="form-check-label" for="isHide">
                                                        Is Hidden
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card bg-dark mt-4">
                                    <div class="card-header">
                                        Random Value Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="randomlevel" class="form-label">Random Level</label>
                                                <input type="number" class="form-control no-spinner" id="randomlevel" name="randomlevel" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomhp" class="form-label">Random HP</label>
                                                <input type="number" class="form-control no-spinner" id="randomhp" name="randomhp" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randommp" class="form-label">Random MP</label>
                                                <input type="number" class="form-control no-spinner" id="randommp" name="randommp" value="0">
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="randomac" class="form-label">Random AC</label>
                                                <input type="number" class="form-control no-spinner" id="randomac" name="randomac" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomexp" class="form-label">Random EXP</label>
                                                <input type="number" class="form-control no-spinner" id="randomexp" name="randomexp" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomAlign" class="form-label">Random Alignment</label>
                                                <input type="number" class="form-control no-spinner" id="randomAlign" name="randomAlign" value="0">
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
                                            <small>Enter any additional information about this monster.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Create Monster</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Additional styles for the drops section */
.item-preview {
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 4px;
    padding: 10px;
}

.preview-container {
    background-color: rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

.preview-container:hover {
    background-color: rgba(0, 0, 0, 0.15);
}

.item-name {
    font-weight: 500;
    margin-top: 8px;
}

.admin-item-icon {
    width: 48px;
    height: 48px;
    object-fit: contain;
    background-color: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
    padding: 3px;
}

/* Modal styling */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: relative;
    background-color: var(--primary);
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    width: 500px;
    max-width: 90%;
    animation: slideIn 0.3s;
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--text);
}

.close {
    color: var(--text);
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    opacity: 0.7;
}

.close:hover {
    opacity: 1;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Styling for spawn locations section */
.nav-pills .nav-link {
    color: #adb5bd;
    background-color: #343a40;
    margin: 0 3px;
}

.nav-pills .nav-link:hover {
    color: #fff;
    background-color: #495057;
}

.nav-pills .nav-link.active {
    color: #fff;
    background-color: #3d78db;
}

.table-dark {
    background-color: #272b30;
    color: #e0e0e0;
    border-radius: 5px;
    overflow: hidden;
}

.table-dark thead th {
    background-color: #212529;
}

.table-dark td, .table-dark th {
    padding: 0.5rem;
    border-color: #373b3e;
}

/* Make list group items in the special tab more compact */
.list-group-item {
    padding: 0.75rem 1rem;
}
</style>

<script>
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
    if (!document.querySelector('.form-section.active') && sections.length > 0) {
        sections[0].style.display = 'block';
        setTimeout(() => {
            sections[0].classList.add('active');
        }, 10);
    }
    
    // Image preview functionality
    const npcidInput = document.getElementById('npcid');
    const imagePreview = document.getElementById('monster-image-preview');
    const basePath = '<?= SITE_URL ?>/assets/img/monsters/';
    const defaultImage = basePath + 'default.png';
    
    // Update image when NPC ID changes
    if (npcidInput && imagePreview) {
        npcidInput.addEventListener('input', function() {
            const npcid = this.value.trim();
            if (npcid && !isNaN(npcid)) {
                imagePreview.src = basePath + npcid + '.png';
            } else {
                imagePreview.src = defaultImage;
            }
        });
    }

    // Live update monster name in preview
    const nameInput = document.getElementById('desc_en');
    const namePreview = document.querySelector('.acquisition-card-body h5');
    
    if (nameInput && namePreview) {
        nameInput.addEventListener('input', function() {
            const monsterName = this.value.trim();
            namePreview.textContent = monsterName || 'New Monster';
        });
    }

    // Live update level in preview
    const levelInput = document.getElementById('lvl');
    const levelPreview = document.querySelector('.acquisition-card-body p');
    const levelBadgePreview = document.querySelector('.monster-ids .badge.bg-info');
    
    if (levelInput && levelPreview && levelBadgePreview) {
        levelInput.addEventListener('input', function() {
            const level = this.value.trim();
            levelPreview.textContent = 'Level: ' + (level || '1');
            levelBadgePreview.textContent = 'Level: ' + (level || '1');
        });
    }

    // Live update stats in the sidebar
    const hpInput = document.getElementById('hp');
    const mpInput = document.getElementById('mp');
    const acInput = document.getElementById('ac');
    const expInput = document.getElementById('exp');
    const bossCheck = document.getElementById('is_bossmonster');
    const undeadSelect = document.getElementById('undead');
    
    // Get preview elements
    const hpPreview = document.getElementById('hp-preview');
    const mpPreview = document.getElementById('mp-preview');
    const acPreview = document.getElementById('ac-preview');
    const expPreview = document.getElementById('exp-preview');
    const bossPreview = document.getElementById('boss-preview');
    
    // Update HP preview
    if (hpInput && hpPreview) {
        hpInput.addEventListener('input', function() {
            hpPreview.textContent = this.value || '100';
        });
    }
    
    // Update MP preview
    if (mpInput && mpPreview) {
        mpInput.addEventListener('input', function() {
            mpPreview.textContent = this.value || '10';
        });
    }
    
    // Update AC preview
    if (acInput && acPreview) {
        acInput.addEventListener('input', function() {
            acPreview.textContent = this.value || '10';
        });
    }
    
    // Update EXP preview
    if (expInput && expPreview) {
        expInput.addEventListener('input', function() {
            expPreview.textContent = this.value || '10';
        });
    }
    
    // Update Boss preview with undead type
    function updateBossPreview() {
        const isBoss = document.getElementById('is_bossmonster').checked;
        const undeadValue = document.getElementById('undead').value;
        
        if (isBoss) {
            bossPreview.textContent = 'Boss';
            bossPreview.className = 'badge bg-danger';
        } else if (undeadValue !== 'NONE') {
            const undeadOptions = <?= json_encode($undeadOptions) ?>;
            bossPreview.textContent = undeadOptions[undeadValue] || undeadValue;
            bossPreview.className = 'badge bg-warning';
        } else {
            bossPreview.textContent = 'Normal';
            bossPreview.className = 'badge bg-secondary';
        }
    }
    
    // Add event listeners for boss and undead changes
    if (bossCheck && bossPreview) {
        bossCheck.addEventListener('change', function() {
            updateBossPreview();
        });
    }
    
    if (undeadSelect && bossPreview) {
        undeadSelect.addEventListener('change', function() {
            updateBossPreview();
        });
    }
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>