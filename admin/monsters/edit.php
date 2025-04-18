<?php
/**
 * Admin - Edit Monster
 */

// Set page title
$pageTitle = 'Edit Monster';

// Include admin header
require_once '../../includes/admin-header.php';

// Include Monster model
require_once '../../models/Monster.php';

// Get database instance
$db = Database::getInstance();

// Initialize model
$monsterModel = new Monster();

// Get monster ID from URL
$monsterId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to monster list
if($monsterId <= 0) {
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect monster data from form
    $monster = [
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
    
    if (empty($monster['desc_en'])) {
        $errors[] = "Monster name is required";
    }
    
    if (empty($errors)) {
        $result = $monsterModel->updateMonster($monsterId, $monster);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Monster '{$monster['desc_en']}' updated successfully."
            ];
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to update monster. Database error.";
        }
    }
}

// Get monster details
$monster = $monsterModel->getMonsterById($monsterId);

if(!$monster) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => "Monster not found."
    ];
    header("Location: index.php");
    exit;
}

// Initialize missing fields with default values if not present
$defaultMonsterFields = [
    'poison_atk' => 'NONE',
    'agrogfxid1' => -1,
    'agrogfxid2' => -1,
    'transform_id' => -1,
    'transform_gfxid' => 0
];

foreach ($defaultMonsterFields as $field => $defaultValue) {
    if (!isset($monster[$field])) {
        $monster[$field] = $defaultValue;
    }
}

// Get monster drops for sidebar
$dropQuery = "SELECT d.*, 
              CASE 
                WHEN w.item_id IS NOT NULL THEN w.desc_en
                WHEN a.item_id IS NOT NULL THEN a.desc_en
                ELSE e.desc_en
              END as item_name,
              d.chance
              FROM droplist d
              LEFT JOIN weapon w ON d.itemId = w.item_id
              LEFT JOIN armor a ON d.itemId = a.item_id
              LEFT JOIN etcitem e ON d.itemId = e.item_id
              WHERE d.mobId = ?
              ORDER BY d.chance DESC";
$drops = $db->getRows($dropQuery, [$monsterId]);

// Get maps for dropdown
$maps = $db->getRows("SELECT mapid, locationname FROM mapids ORDER BY locationname");

// Options for dropdowns
$sizeOptions = ['false' => 'Normal', 'true' => 'Big'];
$attributeOptions = ['NONE' => 'None', 'EARTH' => 'Earth', 'FIRE' => 'Fire', 'WATER' => 'Water', 'WIND' => 'Wind'];
$undeadOptions = ['NONE' => 'None', 'UNDEAD' => 'Undead', 'DEMON' => 'Demon', 'UNDEAD_BOSS' => 'Undead Boss', 'DRANIUM' => 'Dranium'];
$yesNoOptions = ['true' => 'Yes', 'false' => 'No'];
$poisonAtkOptions = ['NONE' => 'None', 'DAMAGE' => 'Damage', 'PARALYSIS' => 'Paralysis', 'SILENCE' => 'Silence'];
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title"><?= htmlspecialchars($monster['desc_en']) ?></h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-dragon me-2"></i>Monster ID: <?= $monsterId ?>
                    </span>
                    <span class="mx-3 text-muted">|</span>
                    <span class="text-muted fs-5">
                        Level: <?= $monster['lvl'] ?>
                    </span>
                </div>
                
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Monsters
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
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php">Monsters</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Monster</li>
        </ol>
    </nav>
    
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
        <!-- Sidebar Preview Column -->
        <div class="col-md-3 sidebar-column">
            <!-- Monster Preview Card -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Monster Preview
                </div>
                <div class="acquisition-card-body d-flex flex-column align-items-center justify-content-center">
                    <img id="monster-image-preview" 
                         src="<?= get_monster_image($monster['spriteId']) ?>" 
                         alt="Monster Image Preview" 
                         style="max-width: 128px;"
                         onerror="this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
                    
                    <h5 class="mt-3"><?= htmlspecialchars($monster['desc_en']) ?></h5>
                    <p class="mb-1">Level: <?= $monster['lvl'] ?></p>
                    <div class="monster-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Monster ID: <?= $monsterId ?></div>
                        <div class="badge bg-info">Level: <?= $monster['lvl'] ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats Preview -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Monster Stats
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>HP</span>
                            <span class="badge bg-danger rounded-pill" id="hp-preview"><?= $monster['hp'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>MP</span>
                            <span class="badge bg-primary rounded-pill" id="mp-preview"><?= $monster['mp'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>AC</span>
                            <span class="badge bg-success rounded-pill" id="ac-preview"><?= $monster['ac'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Exp</span>
                            <span class="badge bg-warning rounded-pill" id="exp-preview"><?= $monster['exp'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Type</span>
                            <span class="badge <?= $monster['is_bossmonster'] === 'true' ? 'bg-danger' : ($monster['undead'] !== 'NONE' ? 'bg-warning' : 'bg-secondary') ?>" id="boss-preview">
                                <?= $monster['is_bossmonster'] === 'true' ? 'Boss' : ($monster['undead'] !== 'NONE' ? $undeadOptions[$monster['undead']] : 'Normal') ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Monster Drops Preview -->
            <?php if (!empty($drops)): ?>
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Monster Drops
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <?php $displayedDrops = 0; ?>
                        <?php foreach ($drops as $drop): ?>
                            <?php if ($displayedDrops < 5): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                                    <span><?= htmlspecialchars($drop['item_name']) ?></span>
                                    <span class="badge bg-info rounded-pill"><?= number_format($drop['chance'] / 10000, 2) ?>%</span>
                                </li>
                                <?php $displayedDrops++; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <?php if (count($drops) > 5): ?>
                            <li class="list-group-item text-center" style="background-color: transparent; border-color: #2d2d2d;">
                                <a href="drops.php?id=<?= $monsterId ?>">
                                    View all <?= count($drops) ?> drops
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Main Form Column -->
        <div class="col-md-9">
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-edit me-2"></i> Edit Monster</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
                        <!-- Form Tabs -->
                        <div class="col-lg-12 mb-0">
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
                                    Basic
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="npcid" class="form-label">Monster ID</label>
                                            <input type="number" class="form-control no-spinner" id="npcid" value="<?= $monsterId ?>" disabled>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="desc_en" class="form-label">Name (English) <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="desc_en" name="desc_en" value="<?= htmlspecialchars($monster['desc_en']) ?>" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="desc_powerbook" class="form-label">Name (Powerbook)</label>
                                            <input type="text" class="form-control" id="desc_powerbook" name="desc_powerbook" value="<?= htmlspecialchars($monster['desc_powerbook'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="desc_kr" class="form-label">Name (Korean)</label>
                                            <input type="text" class="form-control" id="desc_kr" name="desc_kr" value="<?= htmlspecialchars($monster['desc_kr'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="desc_id" class="form-label">Description (ID)</label>
                                            <input type="text" class="form-control" id="desc_id" name="desc_id" value="<?= htmlspecialchars($monster['desc_id'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="classId" class="form-label">Class ID</label>
                                            <input type="number" class="form-control no-spinner" id="classId" name="classId" value="<?= (int)($monster['classId'] ?? 0) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="spriteId" class="form-label">Sprite ID</label>
                                            <input type="number" class="form-control no-spinner" id="spriteId" name="spriteId" value="<?= (int)$monster['spriteId'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="family" class="form-label">Family</label>
                                            <input type="text" class="form-control" id="family" name="family" value="<?= htmlspecialchars($monster['family'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="lvl" class="form-label">Level</label>
                                            <input type="number" class="form-control no-spinner" id="lvl" name="lvl" value="<?= (int)$monster['lvl'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="big" class="form-label">Size</label>
                                            <select class="form-select" id="big" name="big">
                                                <?php foreach ($sizeOptions as $value => $label): ?>
                                                    <option value="<?= $value ?>" <?= ($monster['big'] ?? 'false') === $value ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="impl" class="form-label">Implementation</label>
                                            <input type="text" class="form-control" id="impl" name="impl" value="<?= htmlspecialchars($monster['impl'] ?? 'L1Monster') ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_bossmonster" name="is_bossmonster" <?= $monster['is_bossmonster'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_bossmonster">
                                                    Boss Monster
                                                </label>
                                            </div>
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
                                            <input type="number" class="form-control no-spinner" id="hp" name="hp" value="<?= (int)$monster['hp'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="mp" class="form-label">MP</label>
                                            <input type="number" class="form-control no-spinner" id="mp" name="mp" value="<?= (int)$monster['mp'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="ac" class="form-label">AC</label>
                                            <input type="number" class="form-control no-spinner" id="ac" name="ac" value="<?= (int)$monster['ac'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="str" class="form-label">STR</label>
                                            <input type="number" class="form-control no-spinner" id="str" name="str" value="<?= (int)$monster['str'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="con" class="form-label">CON</label>
                                            <input type="number" class="form-control no-spinner" id="con" name="con" value="<?= (int)$monster['con'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="dex" class="form-label">DEX</label>
                                            <input type="number" class="form-control no-spinner" id="dex" name="dex" value="<?= (int)$monster['dex'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="wis" class="form-label">WIS</label>
                                            <input type="number" class="form-control no-spinner" id="wis" name="wis" value="<?= (int)$monster['wis'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="intel" class="form-label">INT</label>
                                            <input type="number" class="form-control no-spinner" id="intel" name="intel" value="<?= (int)$monster['intel'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="mr" class="form-label">Magic Resistance</label>
                                            <input type="number" class="form-control no-spinner" id="mr" name="mr" value="<?= (int)$monster['mr'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="exp" class="form-label">EXP</label>
                                            <input type="number" class="form-control no-spinner" id="exp" name="exp" value="<?= (int)$monster['exp'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="alignment" class="form-label">Alignment</label>
                                            <input type="number" class="form-control no-spinner" id="alignment" name="alignment" value="<?= (int)($monster['alignment'] ?? 0) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="karma" class="form-label">Karma</label>
                                            <input type="number" class="form-control no-spinner" id="karma" name="karma" value="<?= (int)$monster['karma'] ?>">
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
                                            <input type="number" class="form-control no-spinner" id="hpr" name="hpr" value="<?= (int)$monster['hpr'] ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="hprinterval" class="form-label">HP Regen Interval</label>
                                            <input type="number" class="form-control no-spinner" id="hprinterval" name="hprinterval" value="<?= (int)$monster['hprinterval'] ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="mpr" class="form-label">MP Regen</label>
                                            <input type="number" class="form-control no-spinner" id="mpr" name="mpr" value="<?= (int)$monster['mpr'] ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="mprinterval" class="form-label">MP Regen Interval</label>
											<input type="number" class="form-control no-spinner" id="mprinterval" name="mprinterval" value="<?= (int)$monster['mprinterval'] ?>">
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
                                                    <option value="<?= $value ?>" <?= $monster['weakAttr'] === $value ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="undead" class="form-label">Undead Type</label>
                                            <select class="form-select" id="undead" name="undead">
                                                <?php foreach ($undeadOptions as $value => $label): ?>
                                                    <option value="<?= $value ?>" <?= $monster['undead'] === $value ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="poison_atk" class="form-label">Poison Attack</label>
                                            <select class="form-select" id="poison_atk" name="poison_atk">
                                                <?php foreach ($poisonAtkOptions as $value => $label): ?>
                                                    <option value="<?= $value ?>" <?= isset($monster['poison_atk']) && $monster['poison_atk'] === $value ? 'selected' : '' ?>>
                                                        <?= $label ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="can_turnundead" name="can_turnundead" <?= ($monster['can_turnundead'] ?? 'false') === 'true' ? 'checked' : '' ?>>
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
                                            <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="<?= (int)$monster['damage_reduction'] ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_hard" name="is_hard" <?= ($monster['is_hard'] ?? 'false') === 'true' ? 'checked' : '' ?>>
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
                                            <input type="number" class="form-control no-spinner" id="passispeed" name="passispeed" value="<?= (int)$monster['passispeed'] ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="atkspeed" class="form-label">Attack Speed</label>
                                            <input type="number" class="form-control no-spinner" id="atkspeed" name="atkspeed" value="<?= (int)$monster['atkspeed'] ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="atk_magic_speed" class="form-label">Magic Attack Speed</label>
                                            <input type="number" class="form-control no-spinner" id="atk_magic_speed" name="atk_magic_speed" value="<?= (int)($monster['atk_magic_speed'] ?? 0) ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="sub_magic_speed" class="form-label">Sub Magic Speed</label>
                                            <input type="number" class="form-control no-spinner" id="sub_magic_speed" name="sub_magic_speed" value="<?= (int)($monster['sub_magic_speed'] ?? 0) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="ranged" class="form-label">Ranged Attack Distance</label>
                                            <input type="number" class="form-control no-spinner" id="ranged" name="ranged" value="<?= (int)$monster['ranged'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="bowSpritetId" class="form-label">Bow Sprite ID</label>
                                            <input type="number" class="form-control no-spinner" id="bowSpritetId" name="bowSpritetId" value="<?= (int)($monster['bowSpritetId'] ?? 0) ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_agro" name="is_agro" <?= $monster['is_agro'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_agro">
                                                    Aggressive
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_agro_poly" name="is_agro_poly" <?= $monster['is_agro_poly'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_agro_poly">
                                                    Aggressive (Poly)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_agro_invis" name="is_agro_invis" <?= $monster['is_agro_invis'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_agro_invis">
                                                    Aggressive (Invisible)
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_teleport" name="is_teleport" <?= $monster['is_teleport'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_teleport">
                                                    Can Teleport
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_taming" name="is_taming" <?= $monster['is_taming'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_taming">
                                                    Can Be Tamed
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_picupitem" name="is_picupitem" <?= $monster['is_picupitem'] === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_picupitem">
                                                    Loot Items
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="is_bravespeed" name="is_bravespeed" <?= ($monster['is_bravespeed'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_bravespeed">
                                                    Brave Speed
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check mt-4">
                                                <input class="form-check-input" type="checkbox" id="cant_resurrect" name="cant_resurrect" <?= ($monster['cant_resurrect'] ?? 'false') === 'true' ? 'checked' : '' ?>>
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
                                            <input type="number" class="form-control no-spinner" id="agrofamily" name="agrofamily" value="<?= (int)$monster['agrofamily'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="agrogfxid1" class="form-label">Aggro GFX ID 1</label>
                                            <input type="number" class="form-control no-spinner" id="agrogfxid1" name="agrogfxid1" value="<?= (int)($monster['agrogfxid1'] ?? -1) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="agrogfxid2" class="form-label">Aggro GFX ID 2</label>
                                            <input type="number" class="form-control no-spinner" id="agrogfxid2" name="agrogfxid2" value="<?= (int)($monster['agrogfxid2'] ?? -1) ?>">
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
                                            <input type="number" class="form-control no-spinner" id="digestitem" name="digestitem" value="<?= (int)$monster['digestitem'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="transform_id" class="form-label">Transform ID</label>
                                            <input type="number" class="form-control no-spinner" id="transform_id" name="transform_id" value="<?= (int)($monster['transform_id'] ?? -1) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="transform_gfxid" class="form-label">Transform GFX ID</label>
                                            <input type="number" class="form-control no-spinner" id="transform_gfxid" name="transform_gfxid" value="<?= (int)($monster['transform_gfxid'] ?? 0) ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="light_size" class="form-label">Light Size</label>
                                            <input type="number" class="form-control no-spinner" id="light_size" name="light_size" value="<?= (int)($monster['light_size'] ?? 0) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="spawnlist_door" class="form-label">Spawnlist Door</label>
                                            <input type="number" class="form-control no-spinner" id="spawnlist_door" name="spawnlist_door" value="<?= (int)($monster['spawnlist_door'] ?? 0) ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="count_map" class="form-label">Count Map</label>
                                            <input type="number" class="form-control no-spinner" id="count_map" name="count_map" value="<?= (int)($monster['count_map'] ?? 0) ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_amount_fixed" name="is_amount_fixed" <?= ($monster['is_amount_fixed'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_amount_fixed">
                                                    Amount Fixed
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_change_head" name="is_change_head" <?= ($monster['is_change_head'] ?? 'false') === 'true' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="is_change_head">
                                                    Change Head
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="isHide" name="isHide" <?= ($monster['isHide'] ?? 'false') === 'true' ? 'checked' : '' ?>>
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
                                            <input type="number" class="form-control no-spinner" id="randomlevel" name="randomlevel" value="<?= (int)$monster['randomlevel'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="randomhp" class="form-label">Random HP</label>
                                            <input type="number" class="form-control no-spinner" id="randomhp" name="randomhp" value="<?= (int)$monster['randomhp'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="randommp" class="form-label">Random MP</label>
                                            <input type="number" class="form-control no-spinner" id="randommp" name="randommp" value="<?= (int)$monster['randommp'] ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label for="randomac" class="form-label">Random AC</label>
                                            <input type="number" class="form-control no-spinner" id="randomac" name="randomac" value="<?= (int)$monster['randomac'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="randomexp" class="form-label">Random EXP</label>
                                            <input type="number" class="form-control no-spinner" id="randomexp" name="randomexp" value="<?= (int)$monster['randomexp'] ?>">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label for="randomAlign" class="form-label">Random Alignment</label>
                                            <input type="number" class="form-control no-spinner" id="randomAlign" name="randomAlign" value="<?= (int)($monster['randomAlign'] ?? 0) ?>">
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
                                        <textarea class="form-control" id="note" name="note" rows="5"><?= htmlspecialchars($monster['note'] ?? '') ?></textarea>
                                        <small>Enter any additional information about this monster.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions mt-4">
                        <button type="submit" class="btn btn-primary">Update Monster</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <a href="drops.php?id=<?= $monsterId ?>" class="btn" style="background-color: #212121; color: #e0e0e0;">
                            <i class="fas fa-coins me-1"></i> Manage Drops
                        </a>
                    </div>
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
    
    // Live update stats in the sidebar
    const hpInput = document.getElementById('hp');
    const mpInput = document.getElementById('mp');
    const acInput = document.getElementById('ac');
    const expInput = document.getElementById('exp');
    const bossCheck = document.getElementById('is_bossmonster');
    const undeadSelect = document.getElementById('undead');
    
    const hpPreview = document.getElementById('hp-preview');
    const mpPreview = document.getElementById('mp-preview');
    const acPreview = document.getElementById('ac-preview');
    const expPreview = document.getElementById('exp-preview');
    const bossPreview = document.getElementById('boss-preview');
    
    if (hpInput && hpPreview) {
        hpInput.addEventListener('input', function() {
            hpPreview.textContent = this.value || '0';
        });
    }
    
    if (mpInput && mpPreview) {
        mpInput.addEventListener('input', function() {
            mpPreview.textContent = this.value || '0';
        });
    }
    
    if (acInput && acPreview) {
        acInput.addEventListener('input', function() {
            acPreview.textContent = this.value || '0';
        });
    }
    
    if (expInput && expPreview) {
        expInput.addEventListener('input', function() {
            expPreview.textContent = this.value || '0';
        });
    }
    
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

    // Live update monster name in preview
    const nameInput = document.getElementById('desc_en');
    const namePreview = document.querySelector('.acquisition-card-body h5');
    
    if (nameInput && namePreview) {
        nameInput.addEventListener('input', function() {
            const monsterName = this.value.trim();
            namePreview.textContent = monsterName || 'Monster';
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
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>