<?php
/**
 * Admin Edit Map Page for L1J Database Website
 */

// Set page title
$pageTitle = 'Edit Map';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Get map ID from URL
$mapId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to maps list
if ($mapId <= 0) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => 'Invalid map ID'
    ];
    header('Location: index.php');
    exit;
}

// Initialize variables
$map = null;
$errors = [];

// Get existing map data
$map = $db->getRow("SELECT * FROM mapids WHERE mapid = ?", [$mapId]);

// If map not found, redirect to maps list
if (!$map) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => 'Map not found'
    ];
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $updatedMap = [
        'locationname' => $_POST['locationname'] ?? '',
        'desc_kr' => $_POST['desc_kr'] ?? '',
        'startX' => isset($_POST['startX']) ? intval($_POST['startX']) : 0,
        'endX' => isset($_POST['endX']) ? intval($_POST['endX']) : 0,
        'startY' => isset($_POST['startY']) ? intval($_POST['startY']) : 0,
        'endY' => isset($_POST['endY']) ? intval($_POST['endY']) : 0,
        'monster_amount' => isset($_POST['monster_amount']) ? floatval($_POST['monster_amount']) : 1,
        'drop_rate' => isset($_POST['drop_rate']) ? floatval($_POST['drop_rate']) : 1,
        'underwater' => isset($_POST['underwater']) ? 1 : 0,
        'markable' => isset($_POST['markable']) ? 1 : 0,
        'teleportable' => isset($_POST['teleportable']) ? 1 : 0,
        'escapable' => isset($_POST['escapable']) ? 1 : 0,
        'resurrection' => isset($_POST['resurrection']) ? 1 : 0,
        'painwand' => isset($_POST['painwand']) ? 1 : 0,
        'penalty' => isset($_POST['penalty']) ? 1 : 0,
        'take_pets' => isset($_POST['take_pets']) ? 1 : 0,
        'recall_pets' => isset($_POST['recall_pets']) ? 1 : 0,
        'usable_item' => isset($_POST['usable_item']) ? 1 : 0,
        'usable_skill' => isset($_POST['usable_skill']) ? 1 : 0,
        'dungeon' => isset($_POST['dungeon']) ? 1 : 0,
        'dmgModiPc2Npc' => isset($_POST['dmgModiPc2Npc']) ? intval($_POST['dmgModiPc2Npc']) : 0,
        'dmgModiNpc2Pc' => isset($_POST['dmgModiNpc2Pc']) ? intval($_POST['dmgModiNpc2Pc']) : 0,
        'decreaseHp' => isset($_POST['decreaseHp']) ? 1 : 0,
        'dominationTeleport' => isset($_POST['dominationTeleport']) ? 1 : 0,
        'beginZone' => isset($_POST['beginZone']) ? 1 : 0,
        'redKnightZone' => isset($_POST['redKnightZone']) ? 1 : 0,
        'ruunCastleZone' => isset($_POST['ruunCastleZone']) ? 1 : 0,
        'interWarZone' => isset($_POST['interWarZone']) ? 1 : 0,
        'geradBuffZone' => isset($_POST['geradBuffZone']) ? 1 : 0,
        'growBuffZone' => isset($_POST['growBuffZone']) ? 1 : 0,
        'interKind' => isset($_POST['interKind']) ? intval($_POST['interKind']) : 0,
        'script' => $_POST['script'] ?? '',
        'cloneStart' => isset($_POST['cloneStart']) ? intval($_POST['cloneStart']) : 0,
        'cloneEnd' => isset($_POST['cloneEnd']) ? intval($_POST['cloneEnd']) : 0,
        'pngId' => isset($_POST['pngId']) ? intval($_POST['pngId']) : 0,
        'min_level' => isset($_POST['min_level']) ? intval($_POST['min_level']) : 0,
        'max_level' => isset($_POST['max_level']) ? intval($_POST['max_level']) : 0
    ];
    
    // Validate required fields
    if (empty($updatedMap['locationname'])) {
        $errors[] = 'Location name is required';
    }
    
    // Process if no errors
    if (empty($errors)) {
        try {
            // Update map in database
            $result = $db->update('mapids', $updatedMap, 'mapid = :mapid', ['mapid' => $mapId]);
            
            if ($result) {
                // Set success message
                $_SESSION['admin_message'] = [
                    'type' => 'success',
                    'message' => 'Map updated successfully'
                ];
                
                // Redirect to maps list
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Error updating map';
            }
        } catch (Exception $e) {
            $errors[] = 'Database error: ' . $e->getMessage();
        }
    }
    
    // Update the map data with submitted values if there are errors
    $map = array_merge($map, $updatedMap);
}

// Prepare image path for preview
$imagePath = '';
$base_path = $_SERVER['DOCUMENT_ROOT'] . parse_url(SITE_URL, PHP_URL_PATH);

// Check for map image using pngId first if available
if (isset($map['pngId']) && !empty($map['pngId']) && $map['pngId'] > 0) {
    $png_id = $map['pngId'];
    $image_path = "/assets/img/maps/{$png_id}.jpeg";
    $server_path = $base_path . $image_path;
    
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$png_id}.png";
        $server_path = $base_path . $image_path;
    }
    
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$png_id}.jpg";
        $server_path = $base_path . $image_path;
    }
} else {
    $map_id = $map['mapid'];
    $image_path = "/assets/img/maps/{$map_id}.jpeg";
    $server_path = $base_path . $image_path;
    
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$map_id}.png";
        $server_path = $base_path . $image_path;
    }
    
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$map_id}.jpg";
        $server_path = $base_path . $image_path;
    }
}

if (!file_exists($server_path)) {
    $image_path = "/assets/img/placeholders/map-placeholder.png";
}

$imagePath = SITE_URL . $image_path;

// Helper function to safely handle HTML escaping
function safe_html($value) {
    return htmlspecialchars(($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title"><?= safe_html($map['locationname']) ?></h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-tag me-2"></i>Map ID: <?= $mapId ?>
                    </span>
                    <span class="mx-3 text-muted">|</span>
                    <span class="badge bg-secondary fs-5 px-3 py-2">
                        <i class="fas fa-map me-2"></i><?= $map['dungeon'] ? 'Dungeon' : 'Field Map' ?>
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-arrow-left me-1"></i> Back to Maps
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
            <li class="breadcrumb-item"><a href="index.php">Maps</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Map</li>
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
            <!-- Map Image and Basic Info -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Map Preview
                </div>
                <div class="acquisition-card-body d-flex flex-column align-items-center justify-content-center">
                    <img src="<?= $imagePath ?>" 
                         alt="<?= safe_html($map['locationname']) ?>" 
                         style="max-width: 100%; max-height: 200px;"
                         onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png';">
                    
                    <h5 class="mt-3"><?= safe_html($map['locationname']) ?></h5>
                    <div class="item-ids w-100 text-center mt-3">
                        <div class="badge bg-secondary mb-1">Map ID: <?= $mapId ?></div>
                        <div class="badge bg-secondary">Image ID: <?= $map['pngId'] ?? 'N/A' ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Map Stats Quick View -->
            <div class="acquisition-card mb-4">
                <div class="acquisition-card-header">
                    Map Stats
                </div>
                <div class="acquisition-card-body">
                    <ul class="list-group list-group-flush bg-transparent">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Type</span>
                            <span class="badge <?= $map['dungeon'] ? 'bg-danger' : 'bg-success' ?> rounded-pill">
                                <?= $map['dungeon'] ? 'Dungeon' : 'Field' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Monster Amount</span>
                            <span class="badge bg-info rounded-pill"><?= $map['monster_amount'] ?>x</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Drop Rate</span>
                            <span class="badge bg-primary rounded-pill"><?= $map['drop_rate'] ?>x</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Teleportable</span>
                            <span class="badge <?= $map['teleportable'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                <?= $map['teleportable'] ? 'Yes' : 'No' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Markable</span>
                            <span class="badge <?= $map['markable'] ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                <?= $map['markable'] ? 'Yes' : 'No' ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: transparent; border-color: #2d2d2d;">
                            <span>Level Range</span>
                            <span class="badge bg-warning rounded-pill">
                                <?= $map['min_level'] > 0 ? $map['min_level'] : '1' ?>-<?= $map['max_level'] > 0 ? $map['max_level'] : '∞' ?>
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
                    <h4><i class="fas fa-edit me-2"></i> Edit Map</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <form method="POST" action="" id="editForm">
                        <div class="row">
                            <!-- Form Tabs -->
                            <div class="col-lg-12 mb-4">
                                <div class="form-tabs">
                                    <button type="button" class="form-tab active" data-tab="basic">Basic</button>
                                    <button type="button" class="form-tab" data-tab="coordinates">Coordinates</button>
                                    <button type="button" class="form-tab" data-tab="properties">Properties</button>
                                    <button type="button" class="form-tab" data-tab="combat">Combat</button>
                                    <button type="button" class="form-tab" data-tab="zones">Zones</button>
                                    <button type="button" class="form-tab" data-tab="advanced">Advanced</button>
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
                                                <label for="locationname" class="form-label">Location Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="locationname" name="locationname" value="<?= safe_html($map['locationname']) ?>" required>
                                                <small class="form-text">English name of the map location</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="desc_kr" class="form-label">Korean Name</label>
                                                <input type="text" class="form-control" id="desc_kr" name="desc_kr" value="<?= safe_html($map['desc_kr']) ?>">
                                                <small class="form-text">Korean name of the map location</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pngId" class="form-label">Image ID</label>
                                                <input type="number" class="form-control no-spinner" id="pngId" name="pngId" value="<?= safe_html($map['pngId']) ?>">
                                                <small class="form-text">ID for the map image file</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="min_level" class="form-label">Minimum Level</label>
                                                <input type="number" class="form-control no-spinner" id="min_level" name="min_level" value="<?= isset($map['min_level']) ? safe_html($map['min_level']) : '0' ?>">
                                                <small class="form-text">Minimum recommended level (0 = No restriction)</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="max_level" class="form-label">Maximum Level</label>
                                                <input type="number" class="form-control no-spinner" id="max_level" name="max_level" value="<?= isset($map['max_level']) ? safe_html($map['max_level']) : '0' ?>">
                                                <small class="form-text">Maximum recommended level (0 = No restriction)</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="dungeon" name="dungeon" <?= $map['dungeon'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="dungeon">Dungeon</label>
                                                </div>
                                                <small class="form-text">Check if this map is a dungeon</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Coordinates Section -->
                            <div class="col-lg-12 form-section" id="coordinates-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Coordinates
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="startX" class="form-label">Start X</label>
                                                <input type="number" class="form-control no-spinner" id="startX" name="startX" value="<?= safe_html($map['startX']) ?>">
                                                <small class="form-text">Starting X coordinate</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="endX" class="form-label">End X</label>
                                                <input type="number" class="form-control no-spinner" id="endX" name="endX" value="<?= safe_html($map['endX']) ?>">
                                                <small class="form-text">Ending X coordinate</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="startY" class="form-label">Start Y</label>
                                                <input type="number" class="form-control no-spinner" id="startY" name="startY" value="<?= safe_html($map['startY']) ?>">
                                                <small class="form-text">Starting Y coordinate</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="endY" class="form-label">End Y</label>
                                                <input type="number" class="form-control no-spinner" id="endY" name="endY" value="<?= safe_html($map['endY']) ?>">
                                                <small class="form-text">Ending Y coordinate</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Properties Section -->
                            <div class="col-lg-12 form-section" id="properties-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Map Properties
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="monster_amount" class="form-label">Monster Amount</label>
                                                <input type="number" class="form-control no-spinner" id="monster_amount" name="monster_amount" step="0.1" value="<?= safe_html($map['monster_amount']) ?>">
                                                <small class="form-text">Multiplier for monster spawn amounts (1.0 = normal)</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="drop_rate" class="form-label">Drop Rate</label>
                                                <input type="number" class="form-control no-spinner" id="drop_rate" name="drop_rate" step="0.1" value="<?= safe_html($map['drop_rate']) ?>">
                                                <small class="form-text">Multiplier for item drop rates (1.0 = normal)</small>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="underwater" name="underwater" <?= $map['underwater'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="underwater">Underwater</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="markable" name="markable" <?= $map['markable'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="markable">Markable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="teleportable" name="teleportable" <?= $map['teleportable'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="teleportable">Teleportable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="escapable" name="escapable" <?= $map['escapable'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="escapable">Escapable</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="resurrection" name="resurrection" <?= $map['resurrection'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="resurrection">Resurrection</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="painwand" name="painwand" <?= $map['painwand'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="painwand">Pain Wand</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="penalty" name="penalty" <?= $map['penalty'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="penalty">Death Penalty</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="take_pets" name="take_pets" <?= $map['take_pets'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="take_pets">Allow Pets</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="recall_pets" name="recall_pets" <?= $map['recall_pets'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="recall_pets">Recall Pets</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="usable_item" name="usable_item" <?= $map['usable_item'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="usable_item">Allow Items</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="usable_skill" name="usable_skill" <?= $map['usable_skill'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="usable_skill">Allow Skills</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="decreaseHp" name="decreaseHp" <?= $map['decreaseHp'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="decreaseHp">HP Decreases</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Combat Section -->
                            <div class="col-lg-12 form-section" id="combat-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Combat Modifiers
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="dmgModiPc2Npc" class="form-label">Player to NPC Damage (%)</label>
                                                <input type="number" class="form-control no-spinner" id="dmgModiPc2Npc" name="dmgModiPc2Npc" value="<?= safe_html($map['dmgModiPc2Npc']) ?>">
                                                <small class="form-text">Damage modifier when players attack NPCs (0 = normal, positive = more damage)</small>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dmgModiNpc2Pc" class="form-label">NPC to Player Damage (%)</label>
                                                <input type="number" class="form-control no-spinner" id="dmgModiNpc2Pc" name="dmgModiNpc2Pc" value="<?= safe_html($map['dmgModiNpc2Pc']) ?>">
                                                <small class="form-text">Damage modifier when NPCs attack players (0 = normal, positive = more damage)</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Zones Section -->
                            <div class="col-lg-12 form-section" id="zones-section">
                                <div class="card bg-dark">
                                    <div class="card-header">
                                        Special Zones
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="dominationTeleport" name="dominationTeleport" <?= $map['dominationTeleport'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="dominationTeleport">Domination Teleport</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="beginZone" name="beginZone" <?= $map['beginZone'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="beginZone">Beginner Zone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="redKnightZone" name="redKnightZone" <?= $map['redKnightZone'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="redKnightZone">Red Knight Zone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="ruunCastleZone" name="ruunCastleZone" <?= $map['ruunCastleZone'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="ruunCastleZone">Ruun Castle Zone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="interWarZone" name="interWarZone" <?= $map['interWarZone'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="interWarZone">Inter-War Zone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="geradBuffZone" name="geradBuffZone" <?= $map['geradBuffZone'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="geradBuffZone">Gerad Buff Zone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="growBuffZone" name="growBuffZone" <?= $map['growBuffZone'] ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="growBuffZone">Growth Buff Zone</label>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="interKind" class="form-label">Inter Kind</label>
                                                <input type="number" class="form-control no-spinner" id="interKind" name="interKind" value="<?= safe_html($map['interKind']) ?>">
                                                <small class="form-text">Inter-server kind identifier</small>
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
                                                <label for="script" class="form-label">Map Script</label>
                                                <input type="text" class="form-control" id="script" name="script" value="<?= safe_html($map['script']) ?>">
                                                <small class="form-text">Custom script for this map (leave blank for none)</small>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="cloneStart" class="form-label">Clone Start ID</label>
                                                <input type="number" class="form-control no-spinner" id="cloneStart" name="cloneStart" value="<?= safe_html($map['cloneStart']) ?>">
                                                <small class="form-text">Starting ID for map clones</small>
                                            </div>
                                            <div class="col-md-3 mb-3">
                                                <label for="cloneEnd" class="form-label">Clone End ID</label>
                                                <input type="number" class="form-control no-spinner" id="cloneEnd" name="cloneEnd" value="<?= safe_html($map['cloneEnd']) ?>">
                                                <small class="form-text">Ending ID for map clones</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions mt-4">
                            <button type="submit" class="btn btn-primary">Update Map</button>
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
            
            // Show the corresponding section
            const targetSection = document.getElementById(this.getAttribute('data-tab') + '-section');
            targetSection.classList.add('active');
        });
    });
    
    // Image preview functionality
    const pngIdInput = document.getElementById('pngId');
    const imagePreview = document.querySelector('.acquisition-card-body img');
    const basePath = '<?= SITE_URL ?>/assets/img/maps/';
    const defaultImage = '<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png';
    
    if (pngIdInput && imagePreview) {
        pngIdInput.addEventListener('input', function() {
            const pngId = this.value.trim();
            if (pngId && !isNaN(pngId)) {
                // Try different image formats
                const formats = ['jpeg', 'png', 'jpg'];
                let found = false;
                
                formats.forEach(format => {
                    if (!found) {
                        const testSrc = basePath + pngId + '.' + format;
                        const img = new Image();
                        img.onload = function() {
                            imagePreview.src = testSrc;
                            found = true;
                        };
                        img.onerror = function() {
                            // Try next format
                        };
                        img.src = testSrc;
                    }
                });
                
                if (!found) {
                    imagePreview.src = defaultImage;
                }
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