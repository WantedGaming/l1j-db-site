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
if (!empty($map['pngId']) && $map['pngId'] > 0) {
    $imagePath = SITE_URL . "/assets/img/maps/{$map['pngId']}.jpg";
} else {
    $imagePath = SITE_URL . "/assets/img/maps/{$map['mapid']}.jpg";
}

// Helper function to safely handle HTML escaping
function safe_html($value) {
    return htmlspecialchars(($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Edit Map: <?= safe_html($map['locationname']) ?></h1>
                <p class="admin-hero-subtitle">Map ID: <?= $map['mapid'] ?></p>
                
                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Maps
                    </a>
                    <a href="<?= SITE_URL ?>/pages/maps/detail.php?id=<?= $map['mapid'] ?>" class="btn btn-sm btn-view" target="_blank">
                        <i class="fas fa-eye"></i> View Map
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-content">
            <form action="edit.php?id=<?= $mapId ?>" method="POST" class="admin-form">
                <div class="form-tabs">
                    <button type="button" class="form-tab active" data-section="basic-info">Basic Info</button>
                    <button type="button" class="form-tab" data-section="coordinates">Coordinates</button>
                    <button type="button" class="form-tab" data-section="properties">Properties</button>
                    <button type="button" class="form-tab" data-section="damage-modifiers">Damage Modifiers</button>
                    <button type="button" class="form-tab" data-section="special-zones">Special Zones</button>
                    <button type="button" class="form-tab" data-section="advanced">Advanced</button>
                </div>
                
                <!-- Basic Info Section -->
                <div class="form-section active" id="basic-info">
                    <h3>Basic Information</h3>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <!-- Map Image Preview -->
                            <div class="image-preview-container">
                                <img src="<?= $imagePath ?>" alt="Map Preview" class="item-image-preview" onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png'">
                            </div>
                            <p class="text-center mt-2">Map Preview</p>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="locationname">Location Name *</label>
                                    <input type="text" id="locationname" name="locationname" value="<?= safe_html($map['locationname']) ?>" required>
                                    <small>English name of the map location</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="desc_kr">Korean Name</label>
                                    <input type="text" id="desc_kr" name="desc_kr" value="<?= safe_html($map['desc_kr']) ?>">
                                    <small>Korean name of the map location</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="pngId">Image ID</label>
                                    <input type="number" id="pngId" name="pngId" value="<?= safe_html($map['pngId']) ?>">
                                    <small>ID for the map image file</small>
                                </div>
                                
                                <div class="form-group">
                                    <div class="form-check" style="margin-top: 25px;">
                                        <input type="checkbox" id="dungeon" name="dungeon" <?= $map['dungeon'] ? 'checked' : '' ?>>
                                        <label for="dungeon">Dungeon</label>
                                    </div>
                                    <small>Check if this map is a dungeon (unchecked = field map)</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="min_level">Minimum Level</label>
                                    <input type="number" id="pngId" name="pngId" value="<?= isset($map['pngId']) ? safe_html($map['pngId']) : '' ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="max_level">Maximum Level</label>
                                    <input type="checkbox" id="dungeon" name="dungeon" <?= isset($map['dungeon']) && $map['dungeon'] ? 'checked' : '' ?>>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Coordinates Section -->
                <div class="form-section" id="coordinates">
                    <h3>Map Coordinates</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="startX">Start X</label>
                            <input type="number" id="startX" name="startX" value="<?= safe_html($map['startX']) ?>">
                            <small>Starting X coordinate</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="endX">End X</label>
                            <input type="number" id="endX" name="endX" value="<?= safe_html($map['endX']) ?>">
                            <small>Ending X coordinate</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="startY">Start Y</label>
                            <input type="number" id="startY" name="startY" value="<?= safe_html($map['startY']) ?>">
                            <small>Starting Y coordinate</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="endY">End Y</label>
                            <input type="number" id="endY" name="endY" value="<?= safe_html($map['endY']) ?>">
                            <small>Ending Y coordinate</small>
                        </div>
                    </div>
                </div>
                
                <!-- Properties Section -->
                <div class="form-section" id="properties">
                    <h3>Map Properties</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="monster_amount">Monster Amount</label>
                            <input type="number" id="monster_amount" name="monster_amount" step="0.1" value="<?= safe_html($map['monster_amount']) ?>">
                            <small>Multiplier for monster spawn amounts (1.0 = normal)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="drop_rate">Drop Rate</label>
                            <input type="number" id="drop_rate" name="drop_rate" step="0.1" value="<?= safe_html($map['drop_rate']) ?>">
                            <small>Multiplier for item drop rates (1.0 = normal)</small>
                        </div>
                    </div>
                    
                    <div class="checkbox-grid">
                        <div class="form-check">
                            <input type="checkbox" id="underwater" name="underwater" <?= $map['underwater'] ? 'checked' : '' ?>>
                            <label for="underwater">Underwater</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="markable" name="markable" <?= $map['markable'] ? 'checked' : '' ?>>
                            <label for="markable">Markable</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="teleportable" name="teleportable" <?= $map['teleportable'] ? 'checked' : '' ?>>
                            <label for="teleportable">Teleportable</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="escapable" name="escapable" <?= $map['escapable'] ? 'checked' : '' ?>>
                            <label for="escapable">Escapable</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="resurrection" name="resurrection" <?= $map['resurrection'] ? 'checked' : '' ?>>
                            <label for="resurrection">Resurrection</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="painwand" name="painwand" <?= $map['painwand'] ? 'checked' : '' ?>>
                            <label for="painwand">Pain Wand</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="penalty" name="penalty" <?= $map['penalty'] ? 'checked' : '' ?>>
                            <label for="penalty">Death Penalty</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="take_pets" name="take_pets" <?= $map['take_pets'] ? 'checked' : '' ?>>
                            <label for="take_pets">Allow Pets</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="recall_pets" name="recall_pets" <?= $map['recall_pets'] ? 'checked' : '' ?>>
                            <label for="recall_pets">Recall Pets</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="usable_item" name="usable_item" <?= $map['usable_item'] ? 'checked' : '' ?>>
                            <label for="usable_item">Allow Items</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="usable_skill" name="usable_skill" <?= $map['usable_skill'] ? 'checked' : '' ?>>
                            <label for="usable_skill">Allow Skills</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="decreaseHp" name="decreaseHp" <?= $map['decreaseHp'] ? 'checked' : '' ?>>
                            <label for="decreaseHp">HP Decreases</label>
                        </div>
                    </div>
                </div>
                
                <!-- Damage Modifiers Section -->
                <div class="form-section" id="damage-modifiers">
                    <h3>Damage Modifiers</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="dmgModiPc2Npc">Player to NPC Damage (%)</label>
                            <input type="number" id="dmgModiPc2Npc" name="dmgModiPc2Npc" value="<?= safe_html($map['dmgModiPc2Npc']) ?>">
                            <small>Damage modifier when players attack NPCs (0 = normal, positive = more damage)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="dmgModiNpc2Pc">NPC to Player Damage (%)</label>
                            <input type="number" id="dmgModiNpc2Pc" name="dmgModiNpc2Pc" value="<?= safe_html($map['dmgModiNpc2Pc']) ?>">
                            <small>Damage modifier when NPCs attack players (0 = normal, positive = more damage)</small>
                        </div>
                    </div>
                </div>
                
                <!-- Special Zones Section -->
                <div class="form-section" id="special-zones">
                    <h3>Special Zone Settings</h3>
                    
                    <div class="checkbox-grid">
                        <div class="form-check">
                            <input type="checkbox" id="dominationTeleport" name="dominationTeleport" <?= $map['dominationTeleport'] ? 'checked' : '' ?>>
                            <label for="dominationTeleport">Domination Teleport</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="beginZone" name="beginZone" <?= $map['beginZone'] ? 'checked' : '' ?>>
                            <label for="beginZone">Beginner Zone</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="redKnightZone" name="redKnightZone" <?= $map['redKnightZone'] ? 'checked' : '' ?>>
                            <label for="redKnightZone">Red Knight Zone</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="ruunCastleZone" name="ruunCastleZone" <?= $map['ruunCastleZone'] ? 'checked' : '' ?>>
                            <label for="ruunCastleZone">Ruun Castle Zone</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="interWarZone" name="interWarZone" <?= $map['interWarZone'] ? 'checked' : '' ?>>
                            <label for="interWarZone">Inter-War Zone</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="geradBuffZone" name="geradBuffZone" <?= $map['geradBuffZone'] ? 'checked' : '' ?>>
                            <label for="geradBuffZone">Gerad Buff Zone</label>
                        </div>
                        
                        <div class="form-check">
                            <input type="checkbox" id="growBuffZone" name="growBuffZone" <?= $map['growBuffZone'] ? 'checked' : '' ?>>
                            <label for="growBuffZone">Growth Buff Zone</label>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="interKind">Inter Kind</label>
                            <input type="number" id="interKind" name="interKind" value="<?= safe_html($map['interKind']) ?>">
                            <small>Inter-server kind identifier</small>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Section -->
                <div class="form-section" id="advanced">
                    <h3>Advanced Settings</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="script">Map Script</label>
                            <input type="text" id="script" name="script" value="<?= safe_html($map['script']) ?>">
                            <small>Custom script for this map (leave blank for none)</small>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="cloneStart">Clone Start ID</label>
                            <input type="number" id="cloneStart" name="cloneStart" value="<?= safe_html($map['cloneStart']) ?>">
                            <small>Starting ID for map clones</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="cloneEnd">Clone End ID</label>
                            <input type="number" id="cloneEnd" name="cloneEnd" value="<?= safe_html($map['cloneEnd']) ?>">
                            <small>Ending ID for map clones</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Tab navigation
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.form-tab');
    const sections = document.querySelectorAll('.form-section');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetSection = this.getAttribute('data-section');
            
            // Hide all sections
            sections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all tabs
            tabs.forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show target section and activate tab
            document.getElementById(targetSection).classList.add('active');
            this.classList.add('active');
        });
    });
});
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>
