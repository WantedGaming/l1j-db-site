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

// Process form submission for updating monster
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_monster') {
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

// Process form submission for adding new drop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_drop') {
    $itemId = intval($_POST['itemId'] ?? 0);
    $min = intval($_POST['min'] ?? 1);
    $max = intval($_POST['max'] ?? 1);
    $chance = intval($_POST['chance'] ?? 0);
    
    // Validation
    $errors = [];
    
    if ($itemId <= 0) {
        $errors[] = "Item ID is required";
    }
    
    if ($chance <= 0) {
        $errors[] = "Drop chance must be greater than 0";
    }
    
    if ($min > $max) {
        $errors[] = "Minimum amount cannot be greater than maximum amount";
    }
    
    // If no errors, add the drop
    if (empty($errors)) {
        $result = $monsterModel->addDrop($monsterId, $itemId, $min, $max, $chance);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Drop added successfully."
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Failed to add drop. Please check the item ID exists."
            ];
        }
        
        // Redirect to avoid form resubmission
        header("Location: edit.php?id={$monsterId}");
        exit;
    }
}

// Process form submission for updating drop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_drop') {
    $itemId = intval($_POST['itemId'] ?? 0);
    $min = intval($_POST['min'] ?? 1);
    $max = intval($_POST['max'] ?? 1);
    $chance = intval($_POST['chance'] ?? 0);
    
    // Validation
    $errors = [];
    
    if ($itemId <= 0) {
        $errors[] = "Item ID is required";
    }
    
    if ($chance <= 0) {
        $errors[] = "Drop chance must be greater than 0";
    }
    
    if ($min > $max) {
        $errors[] = "Minimum amount cannot be greater than maximum amount";
    }
    
    // If no errors, update the drop
    if (empty($errors)) {
        $result = $monsterModel->updateDrop($monsterId, $itemId, $min, $max, $chance);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Drop updated successfully."
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Failed to update drop."
            ];
        }
        
        // Redirect to avoid form resubmission
        header("Location: edit.php?id={$monsterId}");
        exit;
    }
}

// Process drop deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_drop') {
    $itemId = intval($_POST['itemId'] ?? 0);
    
    if ($itemId > 0) {
        $result = $monsterModel->deleteDrop($monsterId, $itemId);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Drop deleted successfully."
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Failed to delete drop."
            ];
        }
        
        // Redirect to avoid form resubmission
        header("Location: edit.php?id={$monsterId}");
        exit;
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

// Get all possible items for drop selection dropdown
$weapons = $db->getRows("SELECT item_id, desc_en, iconId FROM weapon ORDER BY desc_en");
$armor = $db->getRows("SELECT item_id, desc_en, iconId FROM armor ORDER BY desc_en");
$etcItems = $db->getRows("SELECT item_id, desc_en, iconId FROM etcitem ORDER BY desc_en");

// Combine all items for the dropdown
$allItems = [];
foreach ($weapons as $weapon) {
    $allItems[] = [
        'id' => $weapon['item_id'],
        'name' => $weapon['desc_en'] . ' [Weapon]',
        'type' => 'weapon',
        'iconId' => $weapon['iconId']
    ];
}

foreach ($armor as $item) {
    $allItems[] = [
        'id' => $item['item_id'],
        'name' => $item['desc_en'] . ' [Armor]',
        'type' => 'armor',
        'iconId' => $item['iconId']
    ];
}

foreach ($etcItems as $item) {
    $allItems[] = [
        'id' => $item['item_id'],
        'name' => $item['desc_en'] . ' [Etc]',
        'type' => 'etc',
        'iconId' => $item['iconId']
    ];
}

// Sort by name
usort($allItems, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Create an associative array with item ID as key for easy access in JavaScript
$itemsById = [];
foreach ($allItems as $item) {
    $itemsById[$item['id']] = [
        'name' => $item['name'],
        'iconId' => $item['iconId']
    ];
}

// Get monster drops
$dropQuery = "SELECT d.*, 
              CASE 
                WHEN w.item_id IS NOT NULL THEN w.desc_en
                WHEN a.item_id IS NOT NULL THEN a.desc_en
                ELSE e.desc_en
              END as item_name,
              CASE 
                WHEN w.item_id IS NOT NULL THEN 'weapon'
                WHEN a.item_id IS NOT NULL THEN 'armor'
                ELSE 'etc'
              END as item_type,
              COALESCE(w.iconId, a.iconId, e.iconId, 0) as icon_id,
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

// Get monster spawn locations
$spawnQuery = "SELECT s.*, m.locationname as map_name, m.startX, m.startY, m.endX, m.endY, m.pngId
               FROM spawnlist s
               LEFT JOIN mapids m ON s.mapid = m.mapid
               WHERE s.npc_templateid = ?
               ORDER BY m.locationname";
$spawns = $db->getRows($spawnQuery, [$monsterId]);

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
        </div>
        
        <!-- Main Form Column -->
        <div class="col-md-9">
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-edit me-2"></i> Edit Monster</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
                        <input type="hidden" name="action" value="update_monster">
                        
                        <!-- Form Tabs -->
                        <div class="col-lg-12 mb-0">
                            <div class="form-tabs">
                                <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                <button type="button" class="form-tab" data-tab="stats">Stats</button>
                                <button type="button" class="form-tab" data-tab="attributes">Attributes</button>
                                <button type="button" class="form-tab" data-tab="behavior">Behavior</button>
                                <button type="button" class="form-tab" data-tab="advanced">Advanced</button>
                                <button type="button" class="form-tab" data-tab="notes">Notes</button>
                                <button type="button" class="form-tab" data-tab="spawns">Spawns</button>
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
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_agro" name="is_agro" <?= $monster['is_agro'] === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_agro">
													Aggressive
												</label>
											</div>
										</div>
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_agro_poly" name="is_agro_poly" <?= $monster['is_agro_poly'] === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_agro_poly">
													Aggressive (Poly)
												</label>
											</div>
										</div>
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_agro_invis" name="is_agro_invis" <?= $monster['is_agro_invis'] === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_agro_invis">
													Aggressive (Invisible)
												</label>
											</div>
										</div>
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_teleport" name="is_teleport" <?= $monster['is_teleport'] === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_teleport">
													Can Teleport
												</label>
											</div>
										</div>
									</div>

									<div class="row">
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_taming" name="is_taming" <?= $monster['is_taming'] === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_taming">
													Can Be Tamed
												</label>
											</div>
										</div>
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_picupitem" name="is_picupitem" <?= $monster['is_picupitem'] === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_picupitem">
													Loot Items
												</label>
											</div>
										</div>
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
												<input class="form-check-input" type="checkbox" id="is_bravespeed" name="is_bravespeed" <?= ($monster['is_bravespeed'] ?? 'false') === 'true' ? 'checked' : '' ?>>
												<label class="form-check-label" for="is_bravespeed">
													Brave Speed
												</label>
											</div>
										</div>
										<div class="col-md-3 mb-3">
											<div class="form-check form-switch mt-4">
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
                        
                        <!-- Spawns Section -->
                        <div class="col-lg-12 form-section" id="spawns-section">
                            <div class="card bg-dark">
                                <div class="card-header">
                                    Spawn Locations
                                </div>
                                <div class="card-body">
                                    <?php if (empty($spawns)): ?>
                                        <div class="alert alert-info">
                                            <p>This monster doesn't have any spawn locations defined.</p>
                                        </div>
                                    <?php else: ?>
                                        <!-- Map preview with spawn points -->
                                        <div class="row">
                                            <div class="col-md-12 mb-4">
                                                <div class="card bg-dark">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h5 class="mb-0">Map Preview</h5>
                                                        <div class="map-selector">
                                                            <select id="mapSelector" class="form-select form-select-sm" style="width: 200px;">
                                                                <?php 
                                                                $uniqueMaps = [];
                                                                foreach ($spawns as $spawn) {
                                                                    if (!isset($uniqueMaps[$spawn['mapid']]) && !empty($spawn['map_name'])) {
                                                                        $uniqueMaps[$spawn['mapid']] = $spawn['map_name'];
                                                                    }
                                                                }
                                                                
                                                                foreach ($uniqueMaps as $mapId => $mapName): 
                                                                ?>
                                                                    <option value="<?= $mapId ?>"><?= htmlspecialchars($mapName) ?> (Map ID: <?= $mapId ?>)</option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="map-container" style="position: relative; min-height: 400px; overflow: hidden;">
                                                            <!-- Map image will be loaded here by JavaScript -->
                                                            <img id="mapImage" src="" alt="Map" style="width: 100%; max-height: 500px; object-fit: contain;">
                                                            
                                                            <!-- Spawn markers will be added here by JavaScript -->
                                                            <div id="spawnMarkers"></div>
                                                            
                                                            <!-- Map info -->
                                                            <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">
                                                                <span id="mapInfo">Select a map to view spawns</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Spawn list table -->
                                        <div class="table-responsive">
                                            <table class="admin-table">
                                                <thead>
                                                    <tr>
                                                        <th>Map</th>
                                                        <th>Coordinates</th>
                                                        <th>Count</th>
                                                        <th>Respawn Time</th>
                                                        <th>View</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($spawns as $spawn): ?>
                                                        <tr data-mapid="<?= $spawn['mapid'] ?>" class="spawn-row">
                                                            <td><?= htmlspecialchars($spawn['map_name'] ?? 'Unknown Map') ?></td>
                                                            <td>X: <?= $spawn['locx'] ?>, Y: <?= $spawn['locy'] ?></td>
                                                            <td><?= $spawn['count'] ?></td>
                                                            <td><?= $spawn['respawn_delay'] ?> sec</td>
                                                            <td>
                                                                <button type="button" class="btn btn-sm btn-view view-on-map" 
                                                                        data-mapid="<?= $spawn['mapid'] ?>" 
                                                                        data-x="<?= $spawn['locx'] ?>" 
                                                                        data-y="<?= $spawn['locy'] ?>">
                                                                    <i class="fas fa-map-marker-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    <div class="form-actions mt-4 p-3" style="background-color: var(--secondary); border-radius: 4px;">
                        <button type="submit" form="editForm" class="btn btn-primary">Update Monster</button>
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Monster Drops Table (Full Width) -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="acquisition-card">
                <div class="acquisition-card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-coins me-2"></i> Monster Drops</h4>
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addDropModal">
                        <i class="fas fa-plus me-1"></i> Add New Drop
                    </button>
                </div>
                <div class="acquisition-card-body p-4">
                    <?php if (empty($drops)): ?>
						<div class="alert alert-info">
							<p>This monster doesn't have any drops defined. Click the "Add New Drop" button to add drops.</p>
						</div>
					<?php else: ?>
						<div class="table-responsive">
							<table class="admin-table">
								<thead>
									<tr>
										<th width="80">Icon</th>
										<th width="80">Name</th>
										<th width="80">Item ID</th>
										<th width="80">Min</th>
										<th width="80">Max</th>
										<th width="80">Chance</th>
										<th width="80">Raw</th>
										<th width="80">Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($drops as $drop): ?>
										<tr>
											<td class="text-center">
												<img src="<?= SITE_URL ?>/assets/img/items/<?= $drop['icon_id'] ?>.png" 
													 alt="<?= htmlspecialchars($drop['item_name']) ?>"
													 class="admin-item-icon"
													 onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
											</td>
											<td><?= htmlspecialchars($drop['item_name']) ?></td>
											<td><?= $drop['itemId'] ?></td>
											<td><?= $drop['min'] ?></td>
											<td><?= $drop['max'] ?></td>
											<td><?= number_format($drop['chance'] / 10000, 4) ?>%</td>
											<td><?= $drop['chance'] ?></td>
											<td class="actions">
												<button class="btn btn-sm btn-edit" title="Edit" onclick="editDrop(<?= $drop['itemId'] ?>, '<?= addslashes($drop['item_name']) ?>', <?= $drop['min'] ?>, <?= $drop['max'] ?>, <?= $drop['chance'] ?>, <?= $drop['icon_id'] ?>)">
													<i class="fas fa-edit"></i>
												</button>
												<button class="btn btn-sm btn-delete" title="Delete" onclick="confirmDeleteDrop(<?= $drop['itemId'] ?>, '<?= addslashes($drop['item_name']) ?>', <?= $drop['icon_id'] ?>)">
													<i class="fas fa-trash"></i>
												</button>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Drop Modal -->
<div class="modal" id="addDropModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Drop</h3>
            <span class="close" data-bs-dismiss="modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addDropForm" method="POST" action="">
                <input type="hidden" name="action" value="add_drop">
                
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label for="itemId" class="form-label">Item</label>
                            <select class="form-select" id="itemId" name="itemId" required onchange="updateItemPreview()">
                                <option value="">Select an item...</option>
                                <?php foreach ($allItems as $item): ?>
                                    <option value="<?= $item['id'] ?>" data-type="<?= $item['type'] ?>" data-icon="<?= $item['iconId'] ?>">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="item-preview text-center mb-3">
                            <h5>Item Preview</h5>
                            <div class="preview-container p-3 border rounded mb-2" style="background-color: rgba(0,0,0,0.1); min-height: 80px; display: flex; align-items: center; justify-content: center;">
                                <img id="add-item-preview" src="<?= SITE_URL ?>/assets/img/items/default.png" alt="Item Preview" style="max-width: 64px; max-height: 64px;">
                            </div>
                            <div id="add-item-name" class="item-name">No item selected</div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="min" class="form-label">Min Amount</label>
                        <input type="number" class="form-control no-spinner" id="min" name="min" value="1" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="max" class="form-label">Max Amount</label>
                        <input type="number" class="form-control no-spinner" id="max" name="max" value="1" min="1" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="chance" class="form-label">Drop Chance (in 10000ths)</label>
                    <input type="number" class="form-control no-spinner" id="chance" name="chance" value="10000" min="1" max="1000000" required>
                    <small class="text-muted">10000 = 1.0000% chance, 1000000 = 100% chance</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-text">
                        <strong>Percent Chance:</strong> <span id="chancePercent">1.0000%</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="addDropForm" class="btn btn-primary">Add Drop</button>
        </div>
    </div>
</div>

<!-- Edit Drop Modal -->
<div class="modal" id="editDropModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Drop</h3>
            <span class="close" data-bs-dismiss="modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editDropForm" method="POST" action="">
                <input type="hidden" name="action" value="update_drop">
                <input type="hidden" name="itemId" id="edit_itemId">
                
                <div class="row">
                    <div class="col-md-7">
                        <div class="mb-3">
                            <label for="edit_itemName" class="form-label">Item</label>
                            <input type="text" class="form-control" id="edit_itemName" readonly>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="item-preview text-center mb-3">
                            <div class="preview-container p-3 border rounded mb-2" style="background-color: rgba(0,0,0,0.1); min-height: 80px; display: flex; align-items: center; justify-content: center;">
                                <img id="edit-item-preview" src="<?= SITE_URL ?>/assets/img/items/default.png" alt="Item Preview" style="max-width: 128px; max-height: 128px;">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_min" class="form-label">Min</label>
                        <input type="number" class="form-control no-spinner" id="edit_min" name="min" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_max" class="form-label">Max</label>
                        <input type="number" class="form-control no-spinner" id="edit_max" name="max" min="1" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_chance" class="form-label">Chance</label>
                    <input type="number" class="form-control no-spinner" id="edit_chance" name="chance" min="1" max="1000000" required>
                    <small class="text-muted">10000 = 1.0000% chance, 1000000 = 100% chance</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-text">
                        <strong>Chance: </strong> <span id="edit_chancePercent">1.0000%</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="editDropForm" class="btn btn-primary">Update Drop</button>
        </div>
    </div>
</div>

<!-- Delete Drop Modal -->
<div class="modal" id="deleteDropModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <span class="close" data-bs-dismiss="modal">&times;</span>
        </div>
        <div class="modal-body">
            <div class="item-preview text-center mb-4">
                <div class="preview-container p-3 border rounded mb-2" style="background-color: rgba(0,0,0,0.1); min-height: 120px; display: flex; align-items: center; justify-content: center;">
                    <img id="delete-item-preview" src="<?= SITE_URL ?>/assets/img/items/default.png" alt="Item Preview" style="max-width: 96px; max-height: 96px;">
                </div>
                <div id="delete-item-name" class="item-name fs-5 mb-3"></div>
            </div>
            <p>Are you sure you want to delete this drop?</p>
            <p class="warning">This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
            <form id="deleteDropForm" method="POST" action="">
                <input type="hidden" name="action" value="delete_drop">
                <input type="hidden" name="itemId" id="delete_itemId">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
// Item data from PHP for JavaScript access
const itemsData = <?= json_encode($itemsById) ?>;

// Map data
const mapData = <?= json_encode(array_map(function($spawn) {
    return [
        'mapid' => $spawn['mapid'],
        'map_name' => $spawn['map_name'] ?? 'Unknown Map',
        'locx' => $spawn['locx'],
        'locy' => $spawn['locy'],
        'count' => $spawn['count'],
        'startX' => $spawn['startX'] ?? 0,
        'startY' => $spawn['startY'] ?? 0,
        'endX' => $spawn['endX'] ?? 1000,
        'endY' => $spawn['endY'] ?? 1000,
        'pngId' => $spawn['pngId'] ?? null
    ];
}, $spawns)) ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
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
    
    // Initialize modal functionality
    function initModal(modalId) {
        const modal = document.getElementById(modalId);
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
        
        // Close buttons
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Initialize all modals
    initModal('addDropModal');
    initModal('editDropModal');
    initModal('deleteDropModal');
    
    // Show modal function (replacement for Bootstrap's modal show)
    window.showModal = function(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'block';
    };
    
    // Add button click event
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target').replace('#', '');
            showModal(target);
        });
    });
    
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
    
    // Update percent chance display on change for add modal
    const chanceInput = document.getElementById('chance');
    const chancePercentSpan = document.getElementById('chancePercent');
    if (chanceInput && chancePercentSpan) {
        chanceInput.addEventListener('input', function() {
            const percent = (parseFloat(this.value) / 10000).toFixed(4);
            chancePercentSpan.textContent = percent + '%';
        });
    }
    
    // Update percent chance display on change for edit modal
    const editChanceInput = document.getElementById('edit_chance');
    const editChancePercentSpan = document.getElementById('edit_chancePercent');
    if (editChanceInput && editChancePercentSpan) {
        editChanceInput.addEventListener('input', function() {
            const percent = (parseFloat(this.value) / 10000).toFixed(4);
            editChancePercentSpan.textContent = percent + '%';
        });
    }
    
    // Map Selector and Visualization
    const mapSelector = document.getElementById('mapSelector');
    if (mapSelector) {
        // Initialize map view on page load
        if (mapSelector.options.length > 0) {
            loadMapData(mapSelector.value);
        }
        
        // Change map when selecting from dropdown
        mapSelector.addEventListener('change', function() {
            loadMapData(this.value);
        });
        
        // Highlight corresponding row in table when selecting a map
        mapSelector.addEventListener('change', function() {
            const mapId = this.value;
            document.querySelectorAll('.spawn-row').forEach(row => {
                row.classList.remove('highlight-row');
                if (row.getAttribute('data-mapid') === mapId) {
                    row.classList.add('highlight-row');
                }
            });
        });
    }
    
    // View on map button
    document.querySelectorAll('.view-on-map').forEach(button => {
        button.addEventListener('click', function() {
            const mapId = this.getAttribute('data-mapid');
            const x = parseInt(this.getAttribute('data-x'));
            const y = parseInt(this.getAttribute('data-y'));
            
            // Select the map in dropdown
            if (mapSelector) {
                mapSelector.value = mapId;
                mapSelector.dispatchEvent(new Event('change'));
            }
            
            // Highlight the marker
            setTimeout(() => {
                highlightMarker(x, y);
            }, 300);
        });
    });
});

function loadMapData(mapId) {
    // Filter spawns for this map
    const mapSpawns = mapData.filter(spawn => spawn.mapid.toString() === mapId.toString());
    if (mapSpawns.length === 0) return;
    
    // Get map details from first spawn in the map
    const mapDetails = mapSpawns[0];
    
    // Load map image
    let mapImage = document.getElementById('mapImage');
    const pngId = mapDetails.pngId || mapId;
    
    // Try different image formats
    const imagePath = `${window.location.origin}${window.location.pathname.substring(0, window.location.pathname.indexOf('/admin'))}`;
    const imageUrls = [
        `${imagePath}/assets/img/maps/${pngId}.jpeg`,
        `${imagePath}/assets/img/maps/${pngId}.png`,
        `${imagePath}/assets/img/maps/${pngId}.jpg`,
        `${imagePath}/assets/img/maps/default.jpg`
    ];
    
    // Try loading the first image, fall back to next ones if it fails
    mapImage.src = imageUrls[0];
    mapImage.onerror = function() {
        if (imageUrls.length > 1) {
            const nextUrl = imageUrls.shift();
            mapImage.src = nextUrl;
        } else {
            mapImage.src = `${imagePath}/assets/img/maps/default.jpg`;
        }
    };
    
    // Clear existing markers
    const markersContainer = document.getElementById('spawnMarkers');
    markersContainer.innerHTML = '';
    
    // Create markers for each spawn
    mapSpawns.forEach(spawn => {
        // Calculate position percentage based on map boundaries
        const mapWidth = mapDetails.endX - mapDetails.startX || 1000;
        const mapHeight = mapDetails.endY - mapDetails.startY || 1000;
        
        const markerX = ((spawn.locx - mapDetails.startX) / mapWidth) * 100;
        const markerY = ((spawn.locy - mapDetails.startY) / mapHeight) * 100;
        
        // Create marker element
        const marker = document.createElement('div');
        marker.className = 'spawn-marker';
        marker.style.position = 'absolute';
        marker.style.left = `${Math.max(1, Math.min(99, markerX))}%`;
        marker.style.top = `${Math.max(1, Math.min(99, markerY))}%`;
        marker.style.color = '#f94b1f';
        marker.style.fontSize = '1.3rem';
        marker.setAttribute('data-x', spawn.locx);
        marker.setAttribute('data-y', spawn.locy);
        marker.setAttribute('title', `Coordinates: ${spawn.locx},${spawn.locy} (Count: ${spawn.count})`);
        marker.innerHTML = '<i class="fas fa-map-marker-alt"></i>';
        
        // Add to container
        markersContainer.appendChild(marker);
    });
    
    // Update map info text
    document.getElementById('mapInfo').textContent = `${mapDetails.map_name} - ${mapSpawns.length} spawn points`;
}

function highlightMarker(x, y) {
    // Find marker with matching coordinates
    const markers = document.querySelectorAll('.spawn-marker');
    markers.forEach(marker => {
        const markerX = parseInt(marker.getAttribute('data-x'));
        const markerY = parseInt(marker.getAttribute('data-y'));
        
        // Reset all markers first
        marker.style.color = '#f94b1f';
        marker.style.fontSize = '1.3rem';
        marker.style.zIndex = '1';
        
        // Highlight matching marker
        if (markerX === x && markerY === y) {
            marker.style.color = '#FFEB3B'; // Yellow highlight
            marker.style.fontSize = '1.8rem';
            marker.style.zIndex = '10';
            
            // Scroll into view if needed
            marker.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Reset after a delay
            setTimeout(() => {
                marker.style.color = '#f94b1f';
                marker.style.fontSize = '1.3rem';
            }, 3000);
        }
    });
}

// Update item preview in add modal
function updateItemPreview() {
    const selectElement = document.getElementById('itemId');
    const previewImage = document.getElementById('add-item-preview');
    const previewName = document.getElementById('add-item-name');
    
    if (selectElement.value) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const iconId = selectedOption.getAttribute('data-icon');
        
        previewImage.src = `<?= SITE_URL ?>/assets/img/items/${iconId}.png`;
        previewImage.onerror = function() {
            this.src = '<?= SITE_URL ?>/assets/img/items/default.png';
        };
        
        previewName.textContent = selectedOption.text;
    } else {
        previewImage.src = '<?= SITE_URL ?>/assets/img/items/default.png';
        previewName.textContent = 'No item selected';
    }
}

// Edit drop function
function editDrop(itemId, itemName, min, max, chance, iconId) {
    document.getElementById('edit_itemId').value = itemId;
    document.getElementById('edit_itemName').value = itemName;
    document.getElementById('edit_min').value = min;
    document.getElementById('edit_max').value = max;
    document.getElementById('edit_chance').value = chance;
    
    // Update preview image
    const previewImage = document.getElementById('edit-item-preview');
    previewImage.src = `<?= SITE_URL ?>/assets/img/items/${iconId}.png`;
    previewImage.onerror = function() {
        this.src = '<?= SITE_URL ?>/assets/img/items/default.png';
    };
    
    // Update percent display
    const percent = (parseFloat(chance) / 10000).toFixed(4);
    document.getElementById('edit_chancePercent').textContent = percent + '%';
    
    // Show the modal
    showModal('editDropModal');
}

// Confirm delete drop function
function confirmDeleteDrop(itemId, itemName, iconId) {
    document.getElementById('delete_itemId').value = itemId;
    document.getElementById('delete-item-name').textContent = itemName;
    
    // Update preview image
    const previewImage = document.getElementById('delete-item-preview');
    previewImage.src = `<?= SITE_URL ?>/assets/img/items/${iconId}.png`;
    previewImage.onerror = function() {
        this.src = '<?= SITE_URL ?>/assets/img/items/default.png';
    };
    
    // Show the modal
    showModal('deleteDropModal');
}
</script>

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
    width: 64px;
    height: 64px;
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

/* Map visualization styles */
.map-container {
    position: relative;
    min-height: 400px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 20px;
}

.spawn-marker {
    position: absolute;
    z-index: 5;
    cursor: pointer;
    transition: all 0.3s ease;
}

.spawn-marker:hover {
    transform: scale(1.2);
    color: #FFEB3B !important;
}

/* Highlight selected row in spawn table */
.highlight-row {
    background-color: rgba(249, 75, 31, 0.1) !important;
}

/* Form actions bottom bar */
.form-actions {
    display: flex;
    gap: 10px;
    padding: 15px;
    background-color: var(--secondary);
    border-radius: 4px;
    margin-top: 20px;
}

/* Make sure spawn points table has proper width */
.table-responsive {
    overflow-x: auto;
    width: 100%;
}

/* Make Monster Drops and Spawns table take full width */
.acquisition-card {
    width: 100%;
}
</style>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>