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
        'weakAttr' => $_POST['weakAttr'] ?? '',
        'ranged' => intval($_POST['ranged'] ?? 0),
        'is_atk_magic' => $_POST['is_atk_magic'] ?? 'false',
        'is_bossmonster' => isset($_POST['is_bossmonster']) ? 'true' : 'false',
        'attr' => $_POST['attr'] ?? '',
        'undead' => $_POST['undead'] ?? '',
        'karma' => intval($_POST['karma'] ?? 0),
        'passispeed' => intval($_POST['passispeed'] ?? 0),
        'atkspeed' => intval($_POST['atkspeed'] ?? 0),
        'is_agro' => isset($_POST['is_agro']) ? 'true' : 'false',
        'is_agro_poly' => isset($_POST['is_agro_poly']) ? 'true' : 'false',
        'is_agro_invis' => isset($_POST['is_agro_invis']) ? 'true' : 'false',
        'family' => $_POST['family'] ?? '',
        'agrofamily' => intval($_POST['agrofamily'] ?? 0),
        'scale' => intval($_POST['scale'] ?? 0),
        'altsound' => intval($_POST['altsound'] ?? 0),
        'atksound' => intval($_POST['atksound'] ?? 0),
        'deadsound' => intval($_POST['deadsound'] ?? 0),
        'movesound' => intval($_POST['movesound'] ?? 0),
        'damage_reduction' => intval($_POST['damage_reduction'] ?? 0),
        'is_hard' => isset($_POST['is_hard']) ? 'true' : 'false',
        'is_doppel' => isset($_POST['is_doppel']) ? 'true' : 'false',
        'tu_point' => intval($_POST['tu_point'] ?? 0),
        'eris' => intval($_POST['eris'] ?? 0),
        'is_teleport' => isset($_POST['is_teleport']) ? 'true' : 'false',
        'is_taming' => isset($_POST['is_taming']) ? 'true' : 'false',
        'is_perceptive' => isset($_POST['is_perceptive']) ? 'true' : 'false',
        'bowActId' => intval($_POST['bowActId'] ?? 0),
        'digestitem' => intval($_POST['digestitem'] ?? 0),
        'bowx' => intval($_POST['bowx'] ?? 0),
        'bowy' => intval($_POST['bowy'] ?? 0),
        'hprinterval' => intval($_POST['hprinterval'] ?? 0),
        'hpr' => intval($_POST['hpr'] ?? 0),
        'mprinterval' => intval($_POST['mprinterval'] ?? 0),
        'mpr' => intval($_POST['mpr'] ?? 0),
        'randomlevel' => intval($_POST['randomlevel'] ?? 0),
        'randomhp' => intval($_POST['randomhp'] ?? 0),
        'randommp' => intval($_POST['randommp'] ?? 0),
        'randomac' => intval($_POST['randomac'] ?? 0),
        'randomexp' => intval($_POST['randomexp'] ?? 0),
        'randomlawful' => intval($_POST['randomlawful'] ?? 0),
        'damage_iwrench' => intval($_POST['damage_iwrench'] ?? 0),
        'brain' => $_POST['brain'] ?? '',
        'polyid' => intval($_POST['polyid'] ?? 0),
        'spriteId' => intval($_POST['spriteId'] ?? 0),
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
        'is_crusuitem' => isset($_POST['is_crusuitem']) ? 'true' : 'false',
        'is_haste' => isset($_POST['is_haste']) ? 'true' : 'false',
        'impl' => $_POST['impl'] ?? 'L1Monster',
        'cant_resurrect' => isset($_POST['cant_resurrect']) ? 'true' : 'false',
        'can_turnundead' => isset($_POST['can_turnundead']) ? 'true' : 'false',
        'is_picupitem' => isset($_POST['is_picupitem']) ? 'true' : 'false'
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
$sizeOptions = ['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'];
$attributeOptions = ['NONE' => 'None', 'EARTH' => 'Earth', 'FIRE' => 'Fire', 'WATER' => 'Water', 'WIND' => 'Wind'];
$undeadOptions = ['NONE' => 'None', 'UNDEAD' => 'Undead', 'DEMON' => 'Demon', 'UNDEAD_BOSS' => 'Undead Boss', 'DRANIUM' => 'Dranium'];
$brainOptions = ['' => 'None', 'guard' => 'Guard', 'atak' => 'Attack', 'shop' => 'Shop', 'guild' => 'Guild'];
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
        <div class="col-md-3 sidebar-column">
            <!-- Monster Image and Basic Info -->
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
            
            <!-- Monster Stats Quick View -->
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
            
            <!-- Monster Drops Quick View -->
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
        
        <div class="col-md-9">
            <!-- Edit Form -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-edit me-2"></i> Edit Monster</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
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
                                                <label for="npcid" class="form-label">Monster ID</label>
                                                <input type="number" class="form-control no-spinner" id="npcid" value="<?= $monsterId ?>" disabled>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_en" class="form-label">Monster Name (English) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="desc_en" name="desc_en" value="<?= htmlspecialchars($monster['desc_en']) ?>" required>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="desc_kr" class="form-label">Monster Name (Korean)</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr" value="<?= htmlspecialchars($monster['desc_kr'] ?? '') ?>">
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
                                                <label for="cls_desc" class="form-label">Class Description</label>
                                                <input type="text" class="form-control" id="cls_desc" name="cls_desc" value="<?= htmlspecialchars($monster['cls_desc'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="lvl" class="form-label">Level</label>
                                                <input type="number" class="form-control no-spinner" id="lvl" name="lvl" value="<?= (int)$monster['lvl'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="size" class="form-label">Size</label>
                                                <select class="form-select" id="size" name="size">
                                                    <?php foreach ($sizeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $monster['size'] === $value ? 'selected' : '' ?>>
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
                                            <div class="col-md-4 mb-3">
                                                <label for="polyid" class="form-label">Polymorph ID</label>
                                                <input type="number" class="form-control no-spinner" id="polyid" name="polyid" value="<?= (int)$monster['polyid'] ?>">
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
                                                <label for="lawful" class="form-label">Lawful</label>
                                                <input type="number" class="form-control no-spinner" id="lawful" name="lawful" value="<?= (int)$monster['lawful'] ?>">
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
                                                <label for="attr" class="form-label">Primary Attribute</label>
                                                <select class="form-select" id="attr" name="attr">
                                                    <?php foreach ($attributeOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $monster['attr'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="attr1_power" class="form-label">Primary Attribute Power</label>
                                                <input type="number" class="form-control no-spinner" id="attr1_power" name="attr1_power" value="<?= (int)$monster['attr1_power'] ?>">
                                            </div>
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
                                                        <option value="<?= $value ?>" <?= $monster['poison_atk'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
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
                                                <input type="number" class="form-control no-spinner" id="damage_reduction" name="damage_reduction" value="<?= (int)$monster['damage_reduction'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="damagereduction" class="form-label">Physical Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="damagereduction" name="damagereduction" value="<?= (int)$monster['damagereduction'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="mdamagereduction" class="form-label">Magic Damage Reduction</label>
                                                <input type="number" class="form-control no-spinner" id="mdamagereduction" name="mdamagereduction" value="<?= (int)$monster['mdamagereduction'] ?>">
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
                                            <div class="col-md-4 mb-3">
                                                <label for="ranged" class="form-label">Ranged Attack Distance</label>
                                                <input type="number" class="form-control no-spinner" id="ranged" name="ranged" value="<?= (int)$monster['ranged'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="is_atk_magic" class="form-label">Magic Attack</label>
                                                <select class="form-select" id="is_atk_magic" name="is_atk_magic">
                                                    <?php foreach ($yesNoOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $monster['is_atk_magic'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="brain" class="form-label">Brain Type</label>
                                                <select class="form-select" id="brain" name="brain">
                                                    <?php foreach ($brainOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $monster['brain'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
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
                                                        Aggressive to Poly
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="is_agro_invis" name="is_agro_invis" <?= $monster['is_agro_invis'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_agro_invis">
                                                        Aggressive to Invisible
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
                                                        Picks Up Items
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="can_turnundead" name="can_turnundead" <?= $monster['can_turnundead'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="can_turnundead">
                                                        Affected by Turn Undead
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check mt-4">
                                                    <input class="form-check-input" type="checkbox" id="cant_resurrect" name="cant_resurrect" <?= $monster['cant_resurrect'] === 'true' ? 'checked' : '' ?>>
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
                                        Sound Settings
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label for="atksound" class="form-label">Attack Sound</label>
                                                <input type="number" class="form-control no-spinner" id="atksound" name="atksound" value="<?= (int)$monster['atksound'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="deadsound" class="form-label">Death Sound</label>
                                                <input type="number" class="form-control no-spinner" id="deadsound" name="deadsound" value="<?= (int)$monster['deadsound'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="movesound" class="form-label">Move Sound</label>
                                                <input type="number" class="form-control no-spinner" id="movesound" name="movesound" value="<?= (int)$monster['movesound'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="altsound" class="form-label">Alt Sound</label>
                                                <input type="number" class="form-control no-spinner" id="altsound" name="altsound" value="<?= (int)$monster['altsound'] ?>">
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
                                                <label for="is_hard" class="form-label">Is Hard</label>
                                                <select class="form-select" id="is_hard" name="is_hard">
                                                    <?php foreach ($yesNoOptions as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $monster['is_hard'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="scale" class="form-label">Scale</label>
                                                <input type="number" class="form-control no-spinner" id="scale" name="scale" value="<?= (int)$monster['scale'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="digestitem" class="form-label">Digest Item</label>
                                                <input type="number" class="form-control no-spinner" id="digestitem" name="digestitem" value="<?= (int)$monster['digestitem'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="bowActId" class="form-label">Bow Action ID</label>
                                                <input type="number" class="form-control no-spinner" id="bowActId" name="bowActId" value="<?= (int)$monster['bowActId'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="bowx" class="form-label">Bow X</label>
                                                <input type="number" class="form-control no-spinner" id="bowx" name="bowx" value="<?= (int)$monster['bowx'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="bowy" class="form-label">Bow Y</label>
                                                <input type="number" class="form-control no-spinner" id="bowy" name="bowy" value="<?= (int)$monster['bowy'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="agrofamily" class="form-label">Aggro Family</label>
                                                <input type="number" class="form-control no-spinner" id="agrofamily" name="agrofamily" value="<?= (int)$monster['agrofamily'] ?>">
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="use_item_id" class="form-label">Use Item ID</label>
                                                <input type="number" class="form-control no-spinner" id="use_item_id" name="use_item_id" value="<?= (int)$monster['use_item_id'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="use_item_name" class="form-label">Use Item Name</label>
                                                <input type="text" class="form-control" id="use_item_name" name="use_item_name" value="<?= htmlspecialchars($monster['use_item_name'] ?? '') ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="use_type" class="form-label">Use Type</label>
                                                <input type="number" class="form-control no-spinner" id="use_type" name="use_type" value="<?= (int)$monster['use_type'] ?>">
                                            </div>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_doppel" name="is_doppel" <?= $monster['is_doppel'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_doppel">
                                                        Doppelganger
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_perceptive" name="is_perceptive" <?= $monster['is_perceptive'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_perceptive">
                                                        Is Perceptive
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_haste" name="is_haste" <?= $monster['is_haste'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_haste">
                                                        Is Haste
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="is_crusuitem" name="is_crusuitem" <?= $monster['is_crusuitem'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="is_crusuitem">
                                                        Is Crusuitem
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
                                            <div class="col-md-4 mb-3">
                                                <label for="randomac" class="form-label">Random AC</label>
                                                <input type="number" class="form-control no-spinner" id="randomac" name="randomac" value="<?= (int)$monster['randomac'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomexp" class="form-label">Random EXP</label>
                                                <input type="number" class="form-control no-spinner" id="randomexp" name="randomexp" value="<?= (int)$monster['randomexp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="randomlawful" class="form-label">Random Lawful</label>
                                                <input type="number" class="form-control no-spinner" id="randomlawful" name="randomlawful" value="<?= (int)$monster['randomlawful'] ?>">
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
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            sections.forEach(section => section.classList.remove('active'));
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-section').classList.add('active');
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
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>