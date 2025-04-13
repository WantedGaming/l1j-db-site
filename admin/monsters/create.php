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
        'desc_en' => $_POST['desc_en'] ?? '',
        'desc_kr' => $_POST['desc_kr'] ?? '',
        'desc_id' => $_POST['desc_id'] ?? '',
        'cls_desc' => $_POST['cls_desc'] ?? '',
        'note' => $_POST['note'] ?? '',
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
        'lawful' => intval($_POST['lawful'] ?? 0),
        'size' => $_POST['size'] ?? 'small',
        'weak_attr' => $_POST['weak_attr'] ?? '',
        'ranged' => intval($_POST['ranged'] ?? 0),
        'is_atk_magic' => $_POST['is_atk_magic'] ?? 'false',
        'is_bossmonster' => isset($_POST['is_bossmonster']) ? 'true' : 'false',
        'attr' => $_POST['attr'] ?? '',
        'undead' => $_POST['undead'] ?? '',
        'karma' => intval($_POST['karma'] ?? 0),
        'passispeed' => intval($_POST['passispeed'] ?? 0),
        'atkspeed' => intval($_POST['atkspeed'] ?? 0),
        'agro' => isset($_POST['agro']) ? 1 : 0,
        'agrososc' => isset($_POST['agrososc']) ? 1 : 0,
        'agrocoi' => isset($_POST['agrocoi']) ? 1 : 0,
        'family' => $_POST['family'] ?? '',
        'agrofamily' => intval($_POST['agrofamily'] ?? 0),
        'scale' => intval($_POST['scale'] ?? 0),
        'altsound' => intval($_POST['altsound'] ?? 0),
        'atksound' => intval($_POST['atksound'] ?? 0),
        'deadsound' => intval($_POST['deadsound'] ?? 0),
        'movesound' => intval($_POST['movesound'] ?? 0),
        'damage_reduction' => intval($_POST['damage_reduction'] ?? 0),
        'hard' => intval($_POST['hard'] ?? 0),
        'doppel' => isset($_POST['doppel']) ? 1 : 0,
        'tu_point' => intval($_POST['tu_point'] ?? 0),
        'eris' => intval($_POST['eris'] ?? 0),
        'is_teleport' => isset($_POST['is_teleport']) ? 1 : 0,
        'is_tam' => isset($_POST['is_tam']) ? 1 : 0,
        'is_perceptive' => isset($_POST['is_perceptive']) ? 1 : 0,
        'bowActId' => intval($_POST['bowActId'] ?? 0),
        'digestitem' => intval($_POST['digestitem'] ?? 0),
        'bowx' => intval($_POST['bowx'] ?? 0),
        'bowy' => intval($_POST['bowy'] ?? 0),
        'hprinterval' => intval($_POST['hprinterval'] ?? 0),
        'hpr' => intval($_POST['hpr'] ?? 0),
        'mprinterval' => intval($_POST['mprinterval'] ?? 0),
        'mpr' => intval($_POST['mpr'] ?? 0),
        'teleport' => isset($_POST['teleport']) ? 1 : 0,
        'randomlevel' => intval($_POST['randomlevel'] ?? 0),
        'randomhp' => intval($_POST['randomhp'] ?? 0),
        'randommp' => intval($_POST['randommp'] ?? 0),
        'randomac' => intval($_POST['randomac'] ?? 0),
        'randomexp' => intval($_POST['randomexp'] ?? 0),
        'randomlawful' => intval($_POST['randomlawful'] ?? 0),
        'damage_iwrench' => intval($_POST['damage_iwrench'] ?? 0),
        'brain' => $_POST['brain'] ?? '',
        'polyid' => intval($_POST['polyid'] ?? 0),
        'imageid' => intval($_POST['imageid'] ?? 0),
        'itempercentchart' => intval($_POST['itempercentchart'] ?? 0),
        'culture' => intval($_POST['culture'] ?? 0),
        'damagereduction' => intval($_POST['damagereduction'] ?? 0),
        'mdamagereduction' => intval($_POST['mdamagereduction'] ?? 0),
        'attr1_power' => intval($_POST['attr1_power'] ?? 0),
        'attr2' => $_POST['attr2'] ?? '',
        'attr2_power' => intval($_POST['attr2_power'] ?? 0),
        'attr3' => $_POST['attr3'] ?? '',
        'attr3_power' => intval($_POST['attr3_power'] ?? 0),
        'attr4' => $_POST['attr4'] ?? '',
        'attr4_power' => intval($_POST['attr4_power'] ?? 0),
        'attr5' => $_POST['attr5'] ?? '',
        'attr5_power' => intval($_POST['attr5_power'] ?? 0),
        'use_item_name' => $_POST['use_item_name'] ?? '',
        'use_item_id' => intval($_POST['use_item_id'] ?? 0),
        'use_type' => intval($_POST['use_type'] ?? 0),
        'is_crusuitem' => isset($_POST['is_crusuitem']) ? 1 : 0,
        'is_haste' => isset($_POST['is_haste']) ? 1 : 0,
        'impl' => $_POST['impl'] ?? 'L1Monster'
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
    'desc_en' => '',
    'desc_kr' => '',
    'desc_id' => '',
    'cls_desc' => '',
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
    'lawful' => 0,
    'size' => 'small',
    'weak_attr' => '',
    'ranged' => 0,
    'is_atk_magic' => 'false',
    'is_bossmonster' => 'false',
    'attr' => '',
    'undead' => '',
    'karma' => 0,
    'passispeed' => 480,
    'atkspeed' => 480,
    'agro' => 0,
    'agrososc' => 0,
    'agrocoi' => 0,
    'family' => '',
    'agrofamily' => 0,
    'scale' => 0,
    'altsound' => 0,
    'atksound' => 0,
    'deadsound' => 0,
    'movesound' => 0,
    'damage_reduction' => 0,
    'hard' => 0,
    'doppel' => 0,
    'tu_point' => 0,
    'eris' => 0,
    'is_teleport' => 0,
    'is_tam' => 0,
    'is_perceptive' => 0,
    'bowActId' => 0,
    'digestitem' => 0,
    'bowx' => 0,
    'bowy' => 0,
    'hprinterval' => 0,
    'hpr' => 0,
    'mprinterval' => 0,
    'mpr' => 0,
    'teleport' => 0,
    'randomlevel' => 0,
    'randomhp' => 0,
    'randommp' => 0,
    'randomac' => 0,
    'randomexp' => 0,
    'randomlawful' => 0,
    'damage_iwrench' => 0,
    'brain' => '',
    'polyid' => 0,
    'imageid' => 0,
    'itempercentchart' => 0,
    'culture' => 0,
    'damagereduction' => 0,
    'mdamagereduction' => 0,
    'attr1_power' => 0,
    'attr2' => '',
    'attr2_power' => 0,
    'attr3' => '',
    'attr3_power' => 0,
    'attr4' => '',
    'attr4_power' => 0,
    'attr5' => '',
    'attr5_power' => 0,
    'use_item_name' => '',
    'use_item_id' => 0,
    'use_type' => 0,
    'is_crusuitem' => 0,
    'is_haste' => 0,
    'impl' => 'L1Monster'
];

// Monster size options
$sizeOptions = [
    'small' => 'Small',
    'medium' => 'Medium',
    'large' => 'Large'
];

// Monster attribute options
$attributeOptions = [
    '' => 'None',
    'earth' => 'Earth',
    'fire' => 'Fire',
    'water' => 'Water',
    'wind' => 'Wind'
];

// Undead type options
$undeadOptions = [
    '' => 'None',
    'undead' => 'Undead',
    'undead boss' => 'Undead Boss'
];

// Brain type options
$brainOptions = [
    '' => 'None',
    'guard' => 'Guard',
    'atak' => 'Attack',
    'shop' => 'Shop',
    'guild' => 'Guild'
];

// Yes/No options
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
                            <span class="badge" id="boss-preview">Normal</span>
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
                                                <label for="desc_kr" class="form-label">Monster Name (Korean)</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_id" class="form-label">ID Description</label>
                                                <input type="text" class="form-control" id="desc_id" name="desc_id">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="family" class="form-label">Family</label>
                                                <input type="text" class="form-control" id="family" name="family">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="cls_desc" class="form-label">Class Description</label>
                                                <input type="text" class="form-control" id="cls_desc" name="cls_desc">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="lvl" class="form-label">Level</label>
                                                <input type="number" class="form-control no-spinner" id="lvl" name="lvl" value="1">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="size" class="form-label">Size</label>
                                                <select class="form-select" id="size" name="size">
                                                    <?php foreach ($sizeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $value === 'small' ? 'selected' : '' ?>>
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
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_bossmonster" name="is_bossmonster">
                                                    <label class="form-check-label" for="is_bossmonster">
                                                        Boss Monster
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="imageid" class="form-label">Image ID</label>
                                                <input type="number" class="form-control no-spinner" id="imageid" name="imageid" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="polyid" class="form-label">Polymorph ID</label>
                                                <input type="number" class="form-control no-spinner" id="polyid" name="polyid" value="0">
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
                                                <label for="lawful" class="form-label">Lawful</label>
                                                <input type="number" class="form-control no-spinner" id="lawful" name="lawful" value="0">
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
                                                <label for="attr" class="form-label">Primary Attribute</label>
                                                <select class="form-select" id="attr" name="attr">
                                                    <?php foreach ($attributeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="attr1_power" class="form-label">Primary Attribute Power</label>
                                                <input type="number" class="form-control no-spinner" id="attr1_power" name="attr1_power" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="attr2" class="form-label">Secondary Attribute</label>
                                                <select class="form-select" id="attr2" name="attr2">
                                                    <?php foreach ($attributeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="attr2_power" class="form-label">Secondary Attribute Power</label>
                                                <input type="number" class="form-control no-spinner" id="attr2_power" name="attr2_power" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="weak_attr" class="form-label">Weak Attribute</label>
                                                <select class="form-select" id="weak_attr" name="weak_attr">
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
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card bg-dark mt-4">
                                    <div class="card-header">
                                        Damage Reduction
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="damage_reduction" class="form-label">Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="damagereduction" class="form-label">Physical Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="damagereduction" name="damagereduction" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="mdamagereduction" class="form-label">Magic Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="mdamagereduction" name="mdamagereduction" value="0">
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
                                            <div class="col-md-4 mb-3">
                                                <label for="ranged" class="form-label">Ranged Attack Distance</label>
                                                <input type="number" class="form-control no-spinner" id="ranged" name="ranged" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="is_atk_magic" class="form-label">Magic Attack</label>
                                                <select class="form-select" id="is_atk_magic" name="is_atk_magic">
                                                    <?php foreach ($yesNoOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="brain" class="form-label">Brain Type</label>
                                                <select class="form-select" id="brain" name="brain">
                                                    <?php foreach ($brainOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>"><?= $label ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="agro" name="agro">
                                                    <label class="form-check-label" for="agro">
                                                        Aggressive
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="agrososc" name="agrososc">
                                                    <label class="form-check-label" for="agrososc">
                                                        Aggressive (Sound)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="agrocoi" name="agrocoi">
                                                    <label class="form-check-label" for="agrocoi">
                                                        Aggressive (Invisible)
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_teleport" name="is_teleport">
                                                    <label class="form-check-label" for="is_teleport">
                                                        Can Teleport
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card bg-dark mt-4">
                                    <div class="card-header">
                                        Sound Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="atksound" class="form-label">Attack Sound</label>
                                                <input type="number" class="form-control no-spinner" id="atksound" name="atksound" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="deadsound" class="form-label">Death Sound</label>
                                                <input type="number" class="form-control no-spinner" id="deadsound" name="deadsound" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="movesound" class="form-label">Move Sound</label>
                                                <input type="number" class="form-control no-spinner" id="movesound" name="movesound" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="altsound" class="form-label">Alt Sound</label>
                                                <input type="number" class="form-control no-spinner" id="altsound" name="altsound" value="0">
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
                                            <div class="col-md-3 mb-3">
                                                <label for="hard" class="form-label">Hardness</label>
                                                <input type="number" class="form-control no-spinner" id="hard" name="hard" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="scale" class="form-label">Scale</label>
                                                <input type="number" class="form-control no-spinner" id="scale" name="scale" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="digestitem" class="form-label">Digest Item</label>
                                                <input type="number" class="form-control no-spinner" id="digestitem" name="digestitem" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="bowActId" class="form-label">Bow Action ID</label>
                                                <input type="number" class="form-control no-spinner" id="bowActId" name="bowActId" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="bowx" class="form-label">Bow X</label>
                                                <input type="number" class="form-control no-spinner" id="bowx" name="bowx" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="bowy" class="form-label">Bow Y</label>
                                                <input type="number" class="form-control no-spinner" id="bowy" name="bowy" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="agrofamily" class="form-label">Aggro Family</label>
                                                <input type="number" class="form-control no-spinner" id="agrofamily" name="agrofamily" value="0">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="use_item_id" class="form-label">Use Item ID</label>
                                                <input type="number" class="form-control no-spinner" id="use_item_id" name="use_item_id" value="0">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="use_item_name" class="form-label">Use Item Name</label>
                                                <input type="text" class="form-control" id="use_item_name" name="use_item_name" value="">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="use_type" class="form-label">Use Type</label>
                                                <input type="number" class="form-control no-spinner" id="use_type" name="use_type" value="0">
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="doppel" name="doppel">
                                                    <label class="form-check-label" for="doppel">
                                                        Doppelganger
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_tam" name="is_tam">
                                                    <label class="form-check-label" for="is_tam">
                                                        Is Tame
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_perceptive" name="is_perceptive">
                                                    <label class="form-check-label" for="is_perceptive">
                                                        Is Perceptive
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_haste" name="is_haste">
                                                    <label class="form-check-label" for="is_haste">
                                                        Is Haste
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
                                            <div class="col-md-4 mb-3">
                                                <label for="randomac" class="form-label">Random AC</label>
                                                <input type="number" class="form-control no-spinner" id="randomac" name="randomac" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomexp" class="form-label">Random EXP</label>
                                                <input type="number" class="form-control no-spinner" id="randomexp" name="randomexp" value="0">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomlawful" class="form-label">Random Lawful</label>
                                                <input type="number" class="form-control no-spinner" id="randomlawful" name="randomlawful" value="0">
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
    const levelBadgePreview = document.querySelector('.monster-ids .badge-info');
    
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
    
    // Update Boss preview
    if (bossCheck && bossPreview) {
        bossCheck.addEventListener('change', function() {
            if (this.checked) {
                bossPreview.textContent = 'Boss';
                bossPreview.className = 'badge bg-danger';
            } else {
                bossPreview.textContent = 'Normal';
                bossPreview.className = 'badge bg-secondary';
            }
        });
    }
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>
                