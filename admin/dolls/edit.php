<?php
/**
 * Admin - Edit Magic Doll
 */

// Set page title
$pageTitle = 'Edit Magic Doll';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Get doll ID from URL
$dollId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no ID provided
if (!$dollId) {
    header('Location: index.php');
    exit;
}

// Load etcitem data
$etcitem = $db->getRow("SELECT * FROM etcitem WHERE item_id = ? AND use_type = 'MAGICDOLL'", [$dollId]);

// Load magicdoll_info data
$magicDollInfo = $db->getRow("SELECT * FROM magicdoll_info WHERE itemId = ?", [$dollId]);

// Redirect if doll not found
if (!$etcitem || !$magicDollInfo) {
    $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Magic doll not found.'];
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect etcitem data from form
    $updatedEtcitem = [
        'desc_en' => $_POST['name'] ?? '',
        'iconId' => intval($_POST['icon_id'] ?? 0),
        'use_type' => 'MAGICDOLL',
        'material' => $_POST['material'] ?? null,
        'weight' => intval($_POST['weight'] ?? 0),
        'itemGrade' => $_POST['itemGrade'] ?? 'NORMAL'
    ];
    
    // Collect magicdoll_info data from form
    $updatedMagicDollInfo = [
        'dollNpcId' => intval($_POST['dollNpcId'] ?? 0),
        'grade' => intval($_POST['grade'] ?? 0),
        'hp' => intval($_POST['hp'] ?? 0),
        'mp' => intval($_POST['mp'] ?? 0),
        'ac' => intval($_POST['ac'] ?? 0),
        'str' => intval($_POST['str'] ?? 0),
        'con' => intval($_POST['con'] ?? 0),
        'dex' => intval($_POST['dex'] ?? 0),
        'intel' => intval($_POST['int'] ?? 0),
        'wis' => intval($_POST['wis'] ?? 0),
        'cha' => intval($_POST['cha'] ?? 0),
        'haste' => isset($_POST['haste']) ? 'true' : 'false',
        'bonusItemId' => intval($_POST['bonusItemId'] ?? 0),
        'bonusCount' => intval($_POST['bonusCount'] ?? 0),
        'bonusInterval' => intval($_POST['bonusInterval'] ?? 0),
        'hitXp' => intval($_POST['hitXp'] ?? 0),
        'dmgXp' => intval($_POST['dmgXp'] ?? 0),
        'damageChance' => intval($_POST['damageChance'] ?? 0),
        'blessItemId' => intval($_POST['blessItemId'] ?? 0),
        'mr' => intval($_POST['mr'] ?? 0)
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($updatedEtcitem['desc_en'])) {
        $errors[] = "Doll name is required";
    }
    
    // If no errors, update the doll
    if (empty($errors)) {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Update etcitem table
            $etcItemSetParts = [];
            $etcItemParams = [];
            
            foreach ($updatedEtcitem as $field => $value) {
                $etcItemSetParts[] = "$field = ?";
                $etcItemParams[] = $value;
            }
            
            // Add the WHERE parameter
            $etcItemParams[] = $dollId;
            
            $etcItemQuery = "UPDATE etcitem SET " . implode(', ', $etcItemSetParts) . " WHERE item_id = ?";
            
            // Execute the query
            $result1 = $db->execute($etcItemQuery, $etcItemParams);
            
            if (!$result1) {
                throw new Exception("Failed to update etcitem table");
            }
            
            // Update magicdoll_info table
            $magicDollSetParts = [];
            $magicDollParams = [];
            
            foreach ($updatedMagicDollInfo as $field => $value) {
                $magicDollSetParts[] = "$field = ?";
                $magicDollParams[] = $value;
            }
            
            // Add the WHERE parameter
            $magicDollParams[] = $dollId;
            
            $magicDollQuery = "UPDATE magicdoll_info SET " . implode(', ', $magicDollSetParts) . " WHERE itemId = ?";
            
            // Execute the query
            $result2 = $db->execute($magicDollQuery, $magicDollParams);
            
            if (!$result2) {
                throw new Exception("Failed to update magicdoll_info table");
            }
            
            // Commit the transaction
            $db->commit();
            
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Magic Doll '{$updatedEtcitem['desc_en']}' updated successfully."
            ];
            
            // Redirect to dolls list
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction
            $db->rollback();
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Ensure all required fields exist with defaults if not set
$defaultDoll = [
    // etcitem fields
    'item_id' => $dollId,
    'name' => $etcitem['desc_en'] ?? '',
    'iconId' => $etcitem['iconId'] ?? 0,
    'weight' => $etcitem['weight'] ?? 0,
    'itemGrade' => $etcitem['itemGrade'] ?? 'NORMAL',
    'material' => $etcitem['material'] ?? '',
    
    // magicdoll_info fields
    'dollNpcId' => $magicDollInfo['dollNpcId'] ?? 0,
    'grade' => $magicDollInfo['grade'] ?? 0,
    'hp' => $magicDollInfo['hp'] ?? 0,
    'mp' => $magicDollInfo['mp'] ?? 0,
    'ac' => $magicDollInfo['ac'] ?? 0,
    'str' => $magicDollInfo['str'] ?? 0,
    'con' => $magicDollInfo['con'] ?? 0,
    'dex' => $magicDollInfo['dex'] ?? 0,
    'int' => $magicDollInfo['intel'] ?? 0,
    'wis' => $magicDollInfo['wis'] ?? 0,
    'cha' => $magicDollInfo['cha'] ?? 0,
    'mr' => $magicDollInfo['mr'] ?? 0,
    'haste' => $magicDollInfo['haste'] ?? 'false',
    'bonusItemId' => $magicDollInfo['bonusItemId'] ?? 0,
    'bonusCount' => $magicDollInfo['bonusCount'] ?? 0,
    'bonusInterval' => $magicDollInfo['bonusInterval'] ?? 0,
    'hitXp' => $magicDollInfo['hitXp'] ?? 0,
    'dmgXp' => $magicDollInfo['dmgXp'] ?? 0,
    'damageChance' => $magicDollInfo['damageChance'] ?? 0,
    'blessItemId' => $magicDollInfo['blessItemId'] ?? 0
];

// Check if image exists
$iconId = $defaultDoll['iconId'];
$imagePath = "../../assets/img/items/{$iconId}.png";
$imageExists = file_exists($imagePath);
$imageUrl = $imageExists ? SITE_URL . "/assets/img/items/{$iconId}.png" : SITE_URL . "/assets/img/items/default.png";

// Get item grades for dropdown
$itemGrades = [
    'NORMAL' => 'Normal',
    'ADVANC' => 'Advanced',
    'RARE' => 'Rare',
    'HERO' => 'Hero',
    'LEGEND' => 'Legend',
    'MYTH' => 'Myth',
    'ONLY' => 'Unique'
];

// Get NPCs for dropdown
$npcQuery = "SELECT npcid, desc_kr, lvl FROM npc WHERE impl LIKE '%L1Monster%' ORDER BY desc_kr ASC";
$npcs = $db->getRows($npcQuery);

// Material types
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
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title"><?= htmlspecialchars($defaultDoll['name']) ?></h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-tag me-2"></i>Item ID: <?= $dollId ?>
                    </span>
                    <span class="mx-3 text-muted">|</span>
                    <span class="badge bg-secondary fs-5 px-3 py-2">
                        <i class="fas fa-magic me-2"></i>Magic Doll
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Dolls
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
            <li class="breadcrumb-item"><a href="index.php">Magic Dolls</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Doll</li>
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
            <!-- Doll Image and Basic Info -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Doll Preview
                </div>
                <div class="acquisition-card-body d-flex flex-column align-items-center justify-content-center">
                    <img src="<?= $imageUrl ?>" 
                         alt="<?= htmlspecialchars($defaultDoll['name']) ?>" 
                         style="max-width: 128px; max-height: 128px;"
                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png';">
                    
                    <h5 class="mt-3"><?= htmlspecialchars($defaultDoll['name']) ?></h5>
                    <div class="item-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Item ID: <?= $dollId ?></div>
                        <div class="badge bg-secondary">Icon ID: <?= $defaultDoll['iconId'] ?? 'N/A' ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Doll Stats Quick View -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Doll Stats
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Grade</span>
                            <span class="badge rarity-<?= strtolower($defaultDoll['itemGrade'] ?? 'common') ?>"><?= $defaultDoll['itemGrade'] ?? 'Common' ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>HP Bonus</span>
                            <span class="badge bg-success rounded-pill"><?= $defaultDoll['hp'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>MP Bonus</span>
                            <span class="badge bg-info rounded-pill"><?= $defaultDoll['mp'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>AC Bonus</span>
                            <span class="badge bg-primary rounded-pill"><?= $defaultDoll['ac'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Weight</span>
                            <span class="badge bg-secondary rounded-pill"><?= $defaultDoll['weight'] ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Haste</span>
                            <span class="badge <?= $defaultDoll['haste'] === 'true' ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                <?= $defaultDoll['haste'] === 'true' ? 'Yes' : 'No' ?>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <!-- Edit Form -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-edit me-2"></i> Edit Magic Doll</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
                        <div class="row">
                            <!-- Form Tabs -->
                            <div class="col-lg-12 mb-4">
                                <div class="form-tabs">
                                    <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                    <button type="button" class="form-tab" data-tab="stats">Stats</button>
                                    <button type="button" class="form-tab" data-tab="bonuses">Bonuses</button>
                                    <button type="button" class="form-tab" data-tab="additional">Additional</button>
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
                                                <label for="name" class="form-label">Doll Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($defaultDoll['name']) ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="icon_id" class="form-label">Icon ID</label>
                                                <input type="number" class="form-control no-spinner" id="icon_id" name="icon_id" value="<?= $defaultDoll['iconId'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="itemGrade" class="form-label">Item Grade</label>
                                                <select class="form-select" id="itemGrade" name="itemGrade">
                                                    <?php foreach ($itemGrades as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= $defaultDoll['itemGrade'] === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="material" class="form-label">Material</label>
                                                <select class="form-select" id="material" name="material">
                                                    <?php foreach ($materialTypes as $value => $label): ?>
                                                        <option value="<?= $value ?>" <?= strtoupper($defaultDoll['material']) === $value ? 'selected' : '' ?>>
                                                            <?= $label ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="weight" class="form-label">Weight</label>
                                                <input type="number" class="form-control no-spinner" id="weight" name="weight" value="<?= $defaultDoll['weight'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="grade" class="form-label">Doll Grade (0-6)</label>
                                                <input type="number" class="form-control no-spinner" id="grade" name="grade" value="<?= $defaultDoll['grade'] ?>" min="0" max="6">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dollNpcId" class="form-label">Doll NPC</label>
                                                <select class="form-select" id="dollNpcId" name="dollNpcId">
                                                    <option value="0">-- Select NPC --</option>
                                                    <?php foreach ($npcs as $npc): ?>
                                                        <?php $selected = ($npc['npcid'] == $defaultDoll['dollNpcId']) ? 'selected' : ''; ?>
                                                        <option value="<?= $npc['npcid'] ?>" <?= $selected ?>>
                                                            <?= htmlspecialchars($npc['desc_kr']) ?> (Lvl <?= $npc['lvl'] ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="blessItemId" class="form-label">Blessed Version Item ID</label>
                                                <input type="number" class="form-control no-spinner" id="blessItemId" name="blessItemId" value="<?= $defaultDoll['blessItemId'] ?>">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="haste" name="haste" <?= $defaultDoll['haste'] === 'true' ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="haste">Has Haste Effect</label>
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
                                        Stat Bonuses
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="hp" class="form-label">HP Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="hp" name="hp" value="<?= $defaultDoll['hp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="mp" class="form-label">MP Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="mp" name="mp" value="<?= $defaultDoll['mp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="ac" class="form-label">AC Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="ac" name="ac" value="<?= $defaultDoll['ac'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="str" class="form-label">STR Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="str" name="str" value="<?= $defaultDoll['str'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="con" class="form-label">CON Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="con" name="con" value="<?= $defaultDoll['con'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="dex" class="form-label">DEX Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="dex" name="dex" value="<?= $defaultDoll['dex'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="int" class="form-label">INT Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="int" name="int" value="<?= $defaultDoll['int'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="wis" class="form-label">WIS Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="wis" name="wis" value="<?= $defaultDoll['wis'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="cha" class="form-label">CHA Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="cha" name="cha" value="<?= $defaultDoll['cha'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="mr" class="form-label">MR Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="mr" name="mr" value="<?= $defaultDoll['mr'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bonuses Section -->
                            <div class="col-lg-12 form-section" id="bonuses-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Special Bonuses
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="bonusItemId" class="form-label">Bonus Item ID</label>
                                                <input type="number" class="form-control no-spinner" id="bonusItemId" name="bonusItemId" value="<?= $defaultDoll['bonusItemId'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="bonusCount" class="form-label">Bonus Count</label>
                                                <input type="number" class="form-control no-spinner" id="bonusCount" name="bonusCount" value="<?= $defaultDoll['bonusCount'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="bonusInterval" class="form-label">Bonus Interval (sec)</label>
                                                <input type="number" class="form-control no-spinner" id="bonusInterval" name="bonusInterval" value="<?= $defaultDoll['bonusInterval'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="hitXp" class="form-label">Hit XP Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="hitXp" name="hitXp" value="<?= $defaultDoll['hitXp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="dmgXp" class="form-label">Damage XP Bonus</label>
                                                <input type="number" class="form-control no-spinner" id="dmgXp" name="dmgXp" value="<?= $defaultDoll['dmgXp'] ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="damageChance" class="form-label">Damage Chance (%)</label>
                                                <input type="number" class="form-control no-spinner" id="damageChance" name="damageChance" value="<?= $defaultDoll['damageChance'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Section -->
                            <div class="col-lg-12 form-section" id="additional-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Additional Properties
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="spriteId" class="form-label">Sprite ID</label>
                                                <input type="number" class="form-control no-spinner" id="spriteId" name="spriteId" value="<?= $defaultDoll['spriteId'] ?? 0 ?>">
                                            </div>
                                            <!-- Add any additional fields here if needed -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Update Doll</button>
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
    const iconIdInput = document.getElementById('icon_id');
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
// Include admin footer
require_once '../../includes/admin-footer.php';
?>