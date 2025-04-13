<?php
/**
 * Map detail page for L1J Database Website
 */

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/functions.php';
require_once '../../includes/database.php';

// Get database instance
$db = Database::getInstance();

// Get map ID from URL
$mapId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to maps list
if($mapId <= 0) {
    header('Location: index.php');
    exit;
}

// Get map details
$query = "SELECT * FROM mapids WHERE mapid = ?";
$map = $db->getRow($query, [$mapId]);

// If map not found, show error
if(!$map) {
    // Set page title and include header
    $pageTitle = 'Map Not Found';
    require_once '../../includes/header.php';
    echo '<div class="container"><div class="alert alert-danger">Map not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get monsters that appear on this map
$monstersQuery = "
    SELECT DISTINCT n.npcid, n.desc_en AS name, n.lvl, n.hp, n.spriteId
    FROM npc n 
    INNER JOIN spawnlist s ON n.npcid = s.npc_templateid 
    WHERE s.mapid = ? AND n.impl = 'L1Monster'
    LIMIT 20
";
$monsters = $db->getRows($monstersQuery, [$mapId]);

// Get NPCs on this map
$npcsQuery = "
    SELECT DISTINCT n.npcid, n.desc_en AS name, n.spriteId, n.impl
    FROM npc n 
    INNER JOIN spawnlist s ON n.npcid = s.npc_templateid 
    WHERE s.mapid = ? AND n.impl NOT LIKE '%L1Monster%'
    ORDER BY n.desc_en ASC
";
$npcs = $db->getRows($npcsQuery, [$mapId]);

// Get shops on this map
$shopsQuery = "
    SELECT s.npc_id, n.desc_en AS name, COUNT(s.item_id) as item_count 
    FROM shop s
    JOIN npc n ON s.npc_id = n.npcid
    JOIN spawnlist sp ON n.npcid = sp.npc_templateid
    WHERE sp.mapid = ?
    GROUP BY s.npc_id, n.desc_en
";
$shops = $db->getRows($shopsQuery, [$mapId]);

// Get spawn points for monsters on this map
$spawn_points_sql = "
    SELECT s.*, n.desc_en as monster_name, n.lvl as monster_level
    FROM spawnlist s 
    INNER JOIN npc n ON s.npc_templateid = n.npcid 
    WHERE s.mapid = ? AND n.impl = 'L1Monster'
    ORDER BY n.lvl DESC
";
$spawn_points = $db->getRows($spawn_points_sql, [$mapId]);

// Initialize teleport locations as empty array
$teleports = [];

// Check if teleport_locations table exists and get data only if it exists
try {
    // Try to get table structure to see if it exists
    $checkTableQuery = "SHOW TABLES LIKE 'teleport_locations'";
    $tableExists = $db->getRow($checkTableQuery);
    
    if ($tableExists) {
        // Get connected maps/teleport locations
        $teleportsQuery = "
            SELECT t.*, m.locationname as destination_name 
            FROM teleport_locations t
            JOIN mapids m ON t.mapid_to = m.mapid
            WHERE t.mapid = ?
        ";
        $teleports = $db->getRows($teleportsQuery, [$mapId]);
    }
} catch (Exception $e) {
    // If there's an error, just skip the teleport locations section
    $teleports = [];
}

// Set page title to map name
$pageTitle = $map['locationname'];

// Check for map image using mapId
$map_id = $map['mapid'];

// First try the maps directory with the map ID
$base_path = dirname(dirname(dirname(__FILE__))); // Go up three levels to get to root
$image_path = "/assets/img/maps/{$map_id}.jpeg";
$server_path = $base_path . $image_path;

// Check for map image using pngId first
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

// Final image source for HTML
$image_src = SITE_URL . $image_path;

// Initialize variables for spawn markers
$placed_markers = [];
$total_markers = 0;

// Include header
require_once '../../includes/header.php';
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= $image_src ?>');">
    <div class="container">
        <h1><?= sanitize($map['locationname']) ?></h1>
        <p><?= $map['dungeon'] ? 'Dungeon' : 'Field' ?> (Map ID: <?= $map['mapid'] ?>)</p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/maps/">Maps</a> &raquo;
        <span><?= sanitize($map['locationname']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Map Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= $image_src ?>" 
                     alt="<?= sanitize($map['locationname']) ?>" 
                     class="detail-image-large"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png'">
            </div>
            <div class="card-content">
                <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1rem;">
                    <?php if ($map['teleportable']): ?>
                        <span class="badge badge-success">Teleportable</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Not Teleportable</span>
                    <?php endif; ?>
                    
                    <?php if ($map['markable']): ?>
                        <span class="badge badge-success">Markable</span>
                    <?php else: ?>
                        <span class="badge badge-secondary">Not Markable</span>
                    <?php endif; ?>
                    
                    <?php if ($map['underwater']): ?>
                        <span class="badge badge-info">Underwater</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Basic Information Card -->
        <div class="card">
            <div class="card-content">
                <h2 class="card-title">Map Information</h2>
                
                <table class="detail-table">
                    <tr>
                        <th>Map ID</th>
                        <td><?= $map['mapid'] ?></td>
                    </tr>
                    <tr>
                        <th>Name (Korean)</th>
                        <td><?= sanitize($map['desc_kr']) ?></td>
                    </tr>
                    <tr>
                        <th>Area Type</th>
                        <td><?= $map['dungeon'] ? 'Dungeon' : 'Field' ?></td>
                    </tr>
                    <?php if (!empty($map['startX']) && !empty($map['endX']) && !empty($map['startY']) && !empty($map['endY'])): ?>
                    <tr>
                        <th>Coordinates</th>
                        <td>X: <?= $map['startX'] ?> - <?= $map['endX'] ?>, Y: <?= $map['startY'] ?> - <?= $map['endY'] ?></td>
                    </tr>
                    <tr>
                        <th>Dimensions</th>
                        <td>
                            Width: <?= abs($map['endX'] - $map['startX']) ?><br>
                            Height: <?= abs($map['endY'] - $map['startY']) ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($map['monster_amount'] > 0): ?>
                    <tr>
                        <th>Monster Density</th>
                        <td><?= $map['monster_amount'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($map['drop_rate'] > 0): ?>
                    <tr>
                        <th>Drop Rate</th>
                        <td><?= $map['drop_rate'] ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
                
                <h3 style="margin-top: 20px;">Map Properties</h3>
				<div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
					<?php if (isset($map['escapable']) && $map['escapable']): ?>
						<span class="badge badge-success">Escape Scrolls Allowed</span>
					<?php endif; ?>
					
					<?php if (isset($map['resurrection']) && $map['resurrection']): ?>
						<span class="badge badge-success">Resurrection Allowed</span>
					<?php endif; ?>
					
					<?php if (isset($map['painwand']) && $map['painwand']): ?>
						<span class="badge badge-success">Can use Wands</span>
					<?php endif; ?>
					
					<?php if (isset($map['penalty']) && $map['penalty']): ?>
						<span class="badge badge-danger">Death Penalty</span>
					<?php endif; ?>
					
					<?php if (isset($map['take_pets']) && $map['take_pets']): ?>
						<span class="badge badge-success">Pets Allowed</span>
					<?php endif; ?>
					
					<?php if (isset($map['recall_pets']) && $map['recall_pets']): ?>
						<span class="badge badge-success">Pet Recall Allowed</span>
					<?php endif; ?>
					
					<?php if (isset($map['usable_item']) && $map['usable_item']): ?>
						<span class="badge badge-success">Can use Items</span>
					<?php endif; ?>
					
					<?php if (isset($map['usable_skill']) && $map['usable_skill']): ?>
						<span class="badge badge-success">Can use Skills</span>
					<?php endif; ?>
				</div>
                
                <?php if ($map['dmgModiPc2Npc'] != 0 || $map['dmgModiNpc2Pc'] != 0): ?>
                <h3 style="margin-top: 20px;">Damage Modifiers</h3>
                <div style="margin-top: 10px;">
                    <?php if ($map['dmgModiPc2Npc'] != 0): ?>
                    <p>Player to Monster: <?= $map['dmgModiPc2Npc'] > 0 ? '+' : '' ?><?= $map['dmgModiPc2Npc'] ?>%</p>
                    <?php endif; ?>
                    <?php if ($map['dmgModiNpc2Pc'] != 0): ?>
                    <p>Monster to Player: <?= $map['dmgModiNpc2Pc'] > 0 ? '+' : '' ?><?= $map['dmgModiNpc2Pc'] ?>%</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($map['decreaseHp']): ?>
                <div style="margin-top: 15px;">
                    <h3 style="color: #f44336;">Warning</h3>
                    <p>This map has HP decrease effect!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Special Zone Properties -->
    <?php 
    $hasSpecialZones = isset($map['dominationTeleport']) && $map['dominationTeleport'] || 
                     isset($map['beginZone']) && $map['beginZone'] || 
                     isset($map['redKnightZone']) && $map['redKnightZone'] || 
                     isset($map['ruunCastleZone']) && $map['ruunCastleZone'] || 
                     isset($map['interWarZone']) && $map['interWarZone'] || 
                     isset($map['geradBuffZone']) && $map['geradBuffZone'] || 
                     isset($map['growBuffZone']) && $map['growBuffZone'];
    
    if ($hasSpecialZones):
    ?>
    <div class="card">
        <div class="card-content">
            <h2 class="card-title">Special Zone Properties</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                <?php if (isset($map['beginZone']) && $map['beginZone']): ?>
                <span class="badge badge-info">Beginner Zone</span>
                <?php endif; ?>
                
                <?php if (isset($map['dominationTeleport']) && $map['dominationTeleport']): ?>
                <span class="badge badge-info">Domination Teleport</span>
                <?php endif; ?>
                
                <?php if (isset($map['redKnightZone']) && $map['redKnightZone']): ?>
                <span class="badge badge-danger">Red Knight Zone</span>
                <?php endif; ?>
                
                <?php if (isset($map['ruunCastleZone']) && $map['ruunCastleZone']): ?>
                <span class="badge badge-info">Ruun Castle Zone</span>
                <?php endif; ?>
                
                <?php if (isset($map['interWarZone']) && $map['interWarZone']): ?>
                <span class="badge badge-danger">Inter-War Zone</span>
                <?php endif; ?>
                
                <?php if (isset($map['geradBuffZone']) && $map['geradBuffZone']): ?>
                <span class="badge badge-success">Gerad Buff Zone</span>
                <?php endif; ?>
                
                <?php if (isset($map['growBuffZone']) && $map['growBuffZone']): ?>
                <span class="badge badge-success">Growth Buff Zone</span>
                <?php endif; ?>
            </div>
            
            <?php if (isset($map['interKind']) && $map['interKind'] > 0): ?>
            <div style="margin-top: 15px;">
                <p><strong>Inter Kind:</strong> <?= $map['interKind'] ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mt-4">
        <!-- Monster Spawn Visualization -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-content">
                    <h2 class="card-title">Map View with Spawn Points</h2>
                    <div style="position: relative; min-height: 300px; overflow: hidden;">
                        <!-- Map Image -->
                        <img src="<?= $image_src ?>" alt="<?= sanitize($map['locationname']) ?>" style="width: 100%; height: 100%; object-fit: cover; min-height: 300px;" onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png'">
                        
                        <!-- Monster Spawn Markers -->
                        <?php if (!empty($spawn_points)): ?>
                            <?php 
                            foreach ($spawn_points as $spawn): 
                                $total_markers++;
                                
                                // Calculate marker position based on map bounds
                                $map_width = $map['endX'] - $map['startX'];
                                $map_height = $map['endY'] - $map['startY'];
                                
                                // Ensure we don't divide by zero
                                $map_width = $map_width > 0 ? $map_width : 1000;
                                $map_height = $map_height > 0 ? $map_height : 1000;
                                
                                // Calculate percentage position
                                $marker_x = (($spawn['locx'] - $map['startX']) / $map_width) * 100;
                                $marker_y = (($spawn['locy'] - $map['startY']) / $map_height) * 100;
                                
                                // Ensure position is within bounds (0-100%)
                                $marker_x = max(1, min(99, $marker_x));
                                $marker_y = max(1, min(99, $marker_y));
                                
                                // Create a key for this position (rounded to 2 decimal places to group very close markers)
                                $position_key = round($marker_x, 2) . '_' . round($marker_y, 2);
                                
                                if (!isset($placed_markers[$position_key])) {
                                    $placed_markers[$position_key] = [
                                        'count' => 1,
                                        'x' => $marker_x,
                                        'y' => $marker_y,
                                        'monsters' => [['name' => $spawn['monster_name'], 'level' => $spawn['monster_level'] ?? 0, 'coords' => "{$spawn['locx']},{$spawn['locy']}"]],
                                    ];
                                } else {
                                    $placed_markers[$position_key]['count']++;
                                    $placed_markers[$position_key]['monsters'][] = ['name' => $spawn['monster_name'], 'level' => $spawn['monster_level'] ?? 0, 'coords' => "{$spawn['locx']},{$spawn['locy']}"];
                                }
                            endforeach;
                            
                            // Now display markers, using groups for positions with multiple monsters
                            foreach ($placed_markers as $key => $marker):
                                if ($marker['count'] == 1):
                                    // Single monster marker
                                    $monster = $marker['monsters'][0];
                            ?>
                                <div class="spawn-marker" style="position: absolute; left: <?= $marker['x'] ?>%; top: <?= $marker['y'] ?>%; color: #f94b1f; font-size: 1.3rem;" 
                                     data-bs-toggle="tooltip" data-bs-placement="top" 
                                     title="<?= sanitize($monster['name']) ?> (<?= sanitize($monster['coords']) ?>)">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            <?php else: ?>
                                <!-- Group marker for multiple monsters -->
                                <div class="spawn-marker-group" style="position: absolute; left: <?= $marker['x'] ?>%; top: <?= $marker['y'] ?>%; background: rgba(249, 75, 31, 0.8); color: white; border-radius: 50%; width: 24px; height: 24px; display: flex; justify-content: center; align-items: center; font-size: 0.8rem; font-weight: bold;"
                                     data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="top"
                                     title="<?php
                                        $tooltip = '';
                                        foreach ($marker['monsters'] as $idx => $monster) {
                                            if ($idx < 5) { // Limit to 5 monsters in tooltip
                                                $tooltip .= sanitize($monster['name']) . " (Lv. " . sanitize($monster['level']) . ")<br>";
                                            }
                                        }
                                        if ($marker['count'] > 5) {
                                            $tooltip .= "...and " . ($marker['count'] - 5) . " more";
                                        }
                                        echo $tooltip;
                                     ?>">
                                    <?= $marker['count'] ?>
                                </div>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            
                            <!-- Only show marker count if we have any spawn points -->
                            <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">
                                <span>
                                    <?= $total_markers ?> monster spawns at <?= count($placed_markers) ?> locations
                                </span>
                            </div>
                        <?php else: ?>
                            <!-- No monsters on this map -->
                            <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.7); padding: 5px 10px; border-radius: 4px;">
                                <span>This map has no monster spawns</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spawn Points List -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-content">
                    <h2 class="card-title">Spawn Points</h2>
                    <?php if (!empty($spawn_points)): ?>
                        <div class="spawn-points-list" style="max-height: 300px; overflow-y: auto;">
                            <?php foreach ($spawn_points as $spawn): ?>
                                <div class="source-item" style="display: flex; align-items: center; padding: 8px; border-bottom: 1px solid var(--border-color);">
                                    <div class="source-icon" style="margin-right: 10px; color: #f94b1f;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="source-details">
                                        <h6 style="margin: 0;"><?= sanitize($spawn['monster_name']) ?></h6>
                                        <p style="margin: 0; font-size: 0.9rem;">Coords: <?= sanitize($spawn['locx']) ?>,<?= sanitize($spawn['locy']) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No spawn points found on this map.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Teleport Locations - Only display if we have data -->
    <?php if (!empty($teleports)): ?>
    <div class="card mt-4">
        <div class="card-content">
            <h2 class="card-title">Teleport Locations</h2>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Destination</th>
                        <th>Coordinates</th>
                        <th>Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teleports as $teleport): ?>
                    <tr>
                        <td>
                            <a href="detail.php?id=<?= $teleport['mapid_to'] ?>">
                                <?= sanitize($teleport['destination_name']) ?>
                            </a>
                        </td>
                        <td>X: <?= $teleport['loc_x'] ?>, Y: <?= $teleport['loc_y'] ?></td>
                        <td><?= sanitize($teleport['teleport_type'] ?? 'Standard') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- NPCs Section -->
    <?php if (!empty($npcs)): ?>
    <div class="card mt-4">
        <div class="card-content">
            <h2 class="card-title">NPCs</h2>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($npcs as $npc): ?>
                    <tr>
                        <td>
                            <a href="../npcs/detail.php?id=<?= $npc['npcid'] ?>">
                                <?= sanitize($npc['name']) ?>
                            </a>
                        </td>
                        <td>
                            <?php
                            // Simple NPC type determination based on impl
                            $npcType = 'Generic NPC';
                            if (strpos($npc['impl'], 'Merchant') !== false) {
                                $npcType = 'Merchant';
                            } elseif (strpos($npc['impl'], 'Teleporter') !== false) {
                                $npcType = 'Teleporter';
                            } elseif (strpos($npc['impl'], 'Dwarf') !== false) {
                                $npcType = 'Storage NPC';
                            }
                            echo $npcType;
                            ?>
                        </td>
                        <td>
                            <?php 
                            // Check if this NPC is also in the shops list
                            $isShop = false;
                            if (!empty($shops)) {
                                foreach ($shops as $shop) {
                                    if ($shop['npc_id'] == $npc['npcid']) {
                                        echo '<span class="badge badge-info">Shop (' . $shop['item_count'] . ' items)</span>';
                                        $isShop = true;
                                        break;
                                    }
                                }
                            }
                            if (!$isShop) {
                                echo sanitize($npc['impl'] ?? 'Talk');
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monsters Section -->
    <?php if (!empty($monsters)): ?>
    <div class="card mt-4">
        <div class="card-header">
            <h2>Monsters</h2>
        </div>
        <div class="card-content">
            <div class="monster-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 1.5rem; padding: 1rem;">
                <?php foreach ($monsters as $monster): ?>
                <a href="../monsters/detail.php?id=<?= $monster['npcid'] ?>" 
                   class="monster-card" 
                   style="text-decoration: none; color: inherit; display: flex; flex-direction: column; align-items: center; padding: 1.5rem; border: 1px solid var(--border-color); border-radius: 8px; transition: all 0.3s ease; background: var(--primary);">
                    <div class="monster-icon-container" style="width: 96px; height: 96px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                        <img src="<?= get_monster_image($monster['spriteId']) ?>" 
                             alt="<?= sanitize($monster['name']) ?>" 
                             class="monster-list-icon"
                             style="width: 100%; height: 100%; object-fit: contain;"
                             onerror="if(this.src.endsWith('.png')){this.src=this.src.replace('.png','.gif');}else{this.src='<?= SITE_URL ?>/assets/img/monsters/default.png';}">
                    </div>
                    <div class="monster-name" style="font-size: 1rem; font-weight: 500; text-align: center; color: var(--text); line-height: 1.3;">
                        <?= sanitize($monster['name']) ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <style>
    .monster-card {
        position: relative;
        overflow: hidden;
    }
    
    .monster-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 75, 31, 0.2);
        border-color: var(--accent);
    }
    
    .monster-card:hover .monster-name {
        color: var(--accent) !important;
    }
    
    .monster-card:active {
        transform: translateY(0);
    }
    
    @media (max-width: 768px) {
        .monster-grid {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
        }
        
        .monster-icon-container {
            width: 72px !important;
            height: 72px !important;
        }
        
        .monster-name {
            font-size: 0.9rem !important;
        }
    }
    </style>
    <?php endif; ?>

    <!-- Clone Map Information (if applicable) -->
    <?php if (isset($map['cloneStart']) && $map['cloneStart'] > 0 || isset($map['cloneEnd']) && $map['cloneEnd'] > 0): ?>
    <div class="card mt-4">
        <div class="card-content">
            <h2 class="card-title">Clone Map Information</h2>
            <p>This map has clone instances available.</p>
            <p><strong>Clone ID Range:</strong> <?= $map['cloneStart'] ?> - <?= $map['cloneEnd'] ?></p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Custom Script (if applicable) -->
    <?php if (isset($map['script']) && !empty($map['script'])): ?>
    <div class="card mt-4">
        <div class="card-content">
            <h2 class="card-title">Custom Map Script</h2>
            <p>This map uses a custom script: <code><?= sanitize($map['script']) ?></code></p>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Optional: Scroll to and highlight marker when clicking on spawn list item
    const spawnItems = document.querySelectorAll('.source-item');
    spawnItems.forEach(item => {
        item.addEventListener('click', function() {
            const coords = this.querySelector('p').textContent.replace('Coords: ', '').split(',');
            const x = parseFloat(coords[0]);
            const y = parseFloat(coords[1]);
            
            // Find and highlight the corresponding marker
            const markers = document.querySelectorAll('.spawn-marker');
            markers.forEach(marker => {
                const title = marker.getAttribute('title');
                if (title.includes(`(${x},${y})`)) {
                    // Highlight effect
                    marker.style.color = '#FFEB3B';
                    marker.style.fontSize = '1.8rem';
                    
                    // Scroll map to view
                    marker.scrollIntoView({behavior: 'smooth', block: 'center'});
                    
                    // Reset after a few seconds
                    setTimeout(() => {
                        marker.style.color = '';
                        marker.style.fontSize = '';
                    }, 3000);
                }
            });
        });
    });
});
</script>

<style>
.monster-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    background: var(--card-hover-bg, rgba(255,255,255,0.05));
}

@media (max-width: 768px) {
    .monster-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)) !important;
    }
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';
?>