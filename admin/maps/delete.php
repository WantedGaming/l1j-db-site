<?php
/**
 * Admin Delete Map Handler for L1J Database Website
 */

// Include necessary files
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect(SITE_URL . '/admin/login.php');
}

// Get database instance
$db = Database::getInstance();

// Check if we have a valid map ID
$mapId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($mapId <= 0) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => 'Invalid map ID'
    ];
    redirect('index.php');
}

// Get map details to confirm it exists and to show its name in confirmation
$map = $db->getRow("SELECT * FROM mapids WHERE mapid = ?", [$mapId]);

if (!$map) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => 'Map not found'
    ];
    redirect('index.php');
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
    try {
        // Check if this map has any dependencies before deleting
        
        // Check for spawns in this map
        $spawnCount = $db->getColumn("SELECT COUNT(*) FROM spawnlist WHERE mapid = ?", [$mapId]);
        
        // Check for other dependencies if applicable (teleport locations, etc.)
        $teleportCount = 0;
        if ($db->columnExists('teleport_locations', 'mapid')) {
            $teleportCount = $db->getColumn("SELECT COUNT(*) FROM teleport_locations WHERE mapid = ? OR mapid_to = ?", [$mapId, $mapId]);
        }
        
        if ($spawnCount > 0 || $teleportCount > 0) {
            // Map has dependencies, warn the user
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Cannot delete map because it has dependencies: $spawnCount spawns, $teleportCount teleport locations"
            ];
            redirect('index.php');
        }
        
        // Delete the map
        $result = $db->query("DELETE FROM mapids WHERE mapid = ?", [$mapId]);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Map '{$map['locationname']}' (ID: $mapId) deleted successfully"
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => 'Failed to delete map'
            ];
        }
    } catch (Exception $e) {
        $_SESSION['admin_message'] = [
            'type' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
    }
    
    redirect('index.php');
}

// Prepare image path for preview
$imagePath = '';
$base_path = $_SERVER['DOCUMENT_ROOT'] . parse_url(SITE_URL, PHP_URL_PATH);

// Check for map image using pngId first if available
if (isset($map['pngId']) && !empty($map['pngId']) && $map['pngId'] > 0) {
    // First try using pngId
    $png_id = $map['pngId'];
    $image_path = "/assets/img/maps/{$png_id}.jpeg";
    $server_path = $base_path . $image_path;
    
    // Try png format if jpeg doesn't exist
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$png_id}.png";
        $server_path = $base_path . $image_path;
    }
    
    // Try jpg format if png doesn't exist
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$png_id}.jpg";
        $server_path = $base_path . $image_path;
    }
} else {
    // Fall back to using mapId if pngId isn't available
    $map_id = $map['mapid'];
    $image_path = "/assets/img/maps/{$map_id}.jpeg";
    $server_path = $base_path . $image_path;
    
    // Try png format
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$map_id}.png";
        $server_path = $base_path . $image_path;
    }
    
    // Try jpg format
    if (!file_exists($server_path)) {
        $image_path = "/assets/img/maps/{$map_id}.jpg";
        $server_path = $base_path . $image_path;
    }
}

// Use placeholder if no image found
if (!file_exists($server_path)) {
    $image_path = "/assets/img/placeholders/map-placeholder.png";
}

// Final image URL
$imagePath = SITE_URL . $image_path;

// Set page title for the confirmation page
$pageTitle = 'Delete Map';

// Include admin header
require_once '../../includes/admin-header.php';
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Delete Map</h1>
                <p class="admin-hero-subtitle">Are you sure you want to delete this map?</p>
                
                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Maps
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-content">
            <div class="text-center mb-4">
                <div class="image-preview-container" style="margin: 0 auto; max-width: 300px;">
                    <img src="<?= $imagePath ?>" alt="Map Preview" class="item-image-preview" style="max-width: 100%;" onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png'">
                </div>
            </div>
            
            <div class="text-center">
                <h2 class="text-danger">Warning: This action cannot be undone</h2>
                <p>You are about to delete the following map:</p>
                <h3><?= htmlspecialchars($map['locationname']) ?> (ID: <?= $map['mapid'] ?>)</h3>
                
                <?php if (isset($map['dungeon']) && $map['dungeon']): ?>
                    <p><span class="badge badge-danger">Dungeon</span></p>
                <?php else: ?>
                    <p><span class="badge badge-secondary">Field</span></p>
                <?php endif; ?>
                
                <form action="delete.php?id=<?= $mapId ?>" method="POST" style="margin-top: 20px;">
                    <input type="hidden" name="confirm_delete" value="yes">
                    
                    <div class="form-actions" style="justify-content: center;">
                        <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-danger">Delete Map</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>
