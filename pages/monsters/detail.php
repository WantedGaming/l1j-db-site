<?php
/**
 * Monster detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Monster Details';
$pageDescription = 'Detailed information about monsters in L1J Remastered, including stats, skills, drops, and spawn locations.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Get monster ID from URL
$monsterId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to monster list
if($monsterId <= 0) {
    header('Location: monster-list.php');
    exit;
}

// Get monster details
$query = "SELECT * FROM npc WHERE npcid = ?";
$monster = $db->getRow($query, [$monsterId]);

// If monster not found or is not a monster, show error
if(!$monster || (strpos($monster['impl'], 'L1Monster') === false && strpos($monster['impl'], 'L1Doppelganger') === false)) {
    echo '<div class="container"><div class="error-message">Monster not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get monster drops
$dropQuery = "SELECT d.*, 
              CASE 
                WHEN w.item_id IS NOT NULL THEN w.desc_en
                WHEN a.item_id IS NOT NULL THEN a.desc_en
                ELSE e.desc_en
              END as item_name,
              CASE 
                WHEN w.item_id IS NOT NULL THEN w.iconId
                WHEN a.item_id IS NOT NULL THEN a.iconId
                ELSE e.iconId
              END as item_icon,
              CASE 
                WHEN w.item_id IS NOT NULL THEN 'weapon'
                WHEN a.item_id IS NOT NULL THEN 'armor'
                ELSE 'etcitem'
              END as item_type
              FROM droplist d
              LEFT JOIN weapon w ON d.itemId = w.item_id
              LEFT JOIN armor a ON d.itemId = a.item_id
              LEFT JOIN etcitem e ON d.itemId = e.item_id
              WHERE d.mobId = ?
              ORDER BY d.chance DESC";
              
$drops = $db->getRows($dropQuery, [$monsterId]);

// Get monster skills
$skillQuery = "SELECT ms.*, 
               ms.SkillId as skillId,
               ms.prob as probability,
               ms.type as target,
               s.name as skill_name, 
               s.skill_level,
               si.desc_en as skill_description
               FROM mobskill ms 
               LEFT JOIN skills s ON ms.SkillId = s.skill_id
               LEFT JOIN skills_info si ON ms.SkillId = si.skillId
               WHERE ms.mobid = ?
               ORDER BY ms.actNo ASC";
$skills = $db->getRows($skillQuery, [$monsterId]);

// Get spawn locations
$spawnQuery = "SELECT s.*, m.locationname as map_name
              FROM spawnlist s
              LEFT JOIN mapids m ON s.mapid = m.mapid
              WHERE s.npc_templateid = ?";
$spawns = $db->getRows($spawnQuery, [$monsterId]);

// Get boss spawns if this is a boss monster
$bossSpawns = [];
if($monster['is_bossmonster'] === 'true') {
    $bossSpawnQuery = "SELECT sb.*, m.locationname as map_name
                      FROM spawnlist_boss sb
                      LEFT JOIN mapids m ON sb.spawnMapId = m.mapid
                      WHERE sb.npcid = ?";
    $bossSpawns = $db->getRows($bossSpawnQuery, [$monsterId]);
}

// Set page title to monster name
$pageTitle = $monster['desc_en'];

// Get monster image path using the existing function
$monsterImagePath = get_monster_image($monster['spriteId']);

// Format drop chance percentage
function formatDropChance($chance) {
    $percentage = ($chance / 10000) * 100;
    return $percentage < 0.01 ? '< 0.01%' : number_format($percentage, 2) . '%';
}

/**
 * Get monster image path for display
 */
function get_monster_image($spriteId) {
    // Base URL path for images (for HTML src attribute)
    $baseUrl = SITE_URL . '/assets/img/monsters/';
    
    // For debugging - let's see what paths we're checking
    $debugInfo = '';
    
    // Simplified approach - just return the URL and let the browser handle fallback
    return $baseUrl . "ms{$spriteId}.png";
}

// Format undead type
function formatUndeadType($undeadType) {
    switch($undeadType) {
        case 'UNDEAD':
            return 'Undead';
        case 'DEMON':
            return 'Demon';
        case 'UNDEAD_BOSS':
            return 'Undead Boss';
        case 'DRANIUM':
            return 'Dranium';
        default:
            return 'Normal';
    }
}

// Format attribute weakness
function formatWeakAttr($attr) {
    switch($attr) {
        case 'EARTH':
            return 'Earth';
        case 'FIRE':
            return 'Fire';
        case 'WATER':
            return 'Water';
        case 'WIND':
            return 'Wind';
        default:
            return 'None';
    }
}

// Format poison attack type
function formatPoisonAtk($poisonType) {
    switch($poisonType) {
        case 'DAMAGE':
            return 'Damage';
        case 'PARALYSIS':
            return 'Paralysis';
        case 'SILENCE':
            return 'Silence';
        default:
            return 'None';
    }
}

// Get badge class for monster type
function getMonsterTypeBadge($monster) {
    if($monster['is_bossmonster'] === 'true') {
        return 'badge-danger';
    } elseif($monster['undead'] !== 'NONE') {
        switch($monster['undead']) {
            case 'UNDEAD_BOSS':
                return 'badge-danger';
            case 'DEMON':
                return 'badge-legend';
            case 'UNDEAD':
                return 'badge-rare';
            case 'DRANIUM':
                return 'badge-hero';
            default:
                return 'badge-normal';
        }
    } else {
        return 'badge-normal';
    }
}
?>

<!-- Hero Section with Monster Information -->
<div class="weapon-hero">
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars($monster['desc_en']) ?></h1>
        <p>Level <?= $monster['lvl'] ?> 
        <?php if($monster['is_bossmonster'] === 'true'): ?>
            <span class="badge badge-danger">Boss</span>
        <?php elseif($monster['undead'] !== 'NONE'): ?>
            <span class="badge <?= getMonsterTypeBadge($monster) ?>"><?= formatUndeadType($monster['undead']) ?></span>
        <?php else: ?>
            <span class="badge badge-normal">Normal</span>
        <?php endif; ?>
        </p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/monsters/monster-list.php">Monsters</a> &raquo;
        <span><?= htmlspecialchars($monster['desc_en']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <!-- Image Card -->
<div class="card">
    <div class="detail-monster-image-container">
        <img src="<?= get_monster_image($monster['spriteId']) ?>" 
             alt="<?= htmlspecialchars($monster['desc_en']) ?>" 
             class="detail-monster-image-large"
             onerror="if(this.src.endsWith('.png')){this.src='<?= SITE_URL ?>/assets/img/monsters/ms<?= $monster['spriteId'] ?>.gif';}else{this.src='<?= SITE_URL ?>/assets/img/monsters/default.png';}">
    </div>
</div>

        <!-- Basic Information Card -->
        <div class="card">
            <div class="card-header">
                <h2>Basic Information</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
					<tr>
						<th>NPC ID:</th>
						<td><?= $monster['npcid'] ?></td>
					</tr>
					<tr>
						<th>Sprite ID:</th>
						<td><?= $monster['spriteId'] ?></td>
					</tr>
                    <tr>
                        <th>Level</th>
                        <td><?= $monster['lvl'] ?></td>
                    </tr>
                    <tr>
                        <th>HP</th>
                        <td><?= number_format($monster['hp']) ?></td>
                    </tr>
                    <tr>
                        <th>MP</th>
                        <td><?= number_format($monster['mp']) ?></td>
                    </tr>
                    <tr>
                        <th>AC</th>
                        <td><?= $monster['ac'] ?></td>
                    </tr>
                    <tr>
                        <th>Experience</th>
                        <td><?= number_format($monster['exp']) ?></td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>
                            <?php if($monster['is_bossmonster'] === 'true'): ?>
                                <span class="badge badge-danger">Boss</span>
                            <?php endif; ?>
                            <?php if($monster['undead'] !== 'NONE'): ?>
                                <span class="badge <?= getMonsterTypeBadge($monster) ?>"><?= formatUndeadType($monster['undead']) ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if($monster['weakAttr'] !== 'NONE'): ?>
                    <tr>
                        <th>Weakness</th>
                        <td><?= formatWeakAttr($monster['weakAttr']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($monster['poison_atk'] !== 'NONE'): ?>
                    <tr>
                        <th>Poison Attack</th>
                        <td><?= formatPoisonAtk($monster['poison_atk']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($monster['is_agro'] === 'true'): ?>
                    <tr>
                        <th>Aggression</th>
                        <td>Aggressive</td>
                    </tr>
                    <?php endif; ?>
                    <?php if($monster['damage_reduction'] > 0): ?>
                    <tr>
                        <th>Damage Reduction</th>
                        <td><?= $monster['damage_reduction'] ?>%</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Attributes Section -->
<div class="card">
    <div class="card-header">
        <h2>Attributes</h2>
    </div>
    <div class="card-content">
        <div class="monster-stat-grid">
            <div class="monster-stat-item">
                <div class="monster-stat-icon">STR</div>
                <div class="monster-stat-value"><?= $monster['str'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">CON</div>
                <div class="monster-stat-value"><?= $monster['con'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">DEX</div>
                <div class="monster-stat-value"><?= $monster['dex'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">WIS</div>
                <div class="monster-stat-value"><?= $monster['wis'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">INT</div>
                <div class="monster-stat-value"><?= $monster['intel'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">MR</div>
                <div class="monster-stat-value"><?= $monster['mr'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">KARMA</div>
                <div class="monster-stat-value"><?= $monster['karma'] ?></div>
            </div>
            <div class="monster-stat-item">
                <div class="monster-stat-icon">ALIGNMENT</div>
                <div class="monster-stat-value"><?= $monster['alignment'] ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Combat Stats Section -->
<div class="card">
    <div class="card-header">
        <h2>Combat Stats</h2>
    </div>
    <div class="card-content">
        <div class="monster-combat-grid">
            <?php if($monster['atkspeed'] > 0): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">Attack Speed</div>
                    <div class="monster-combat-value"><?= $monster['atkspeed'] ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['passispeed'] > 0): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-running"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">Move Speed</div>
                    <div class="monster-combat-value"><?= $monster['passispeed'] ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['atk_magic_speed'] > 0): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-magic"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">Magic Attack Speed</div>
                    <div class="monster-combat-value"><?= $monster['atk_magic_speed'] ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['ranged'] > 0): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-bullseye"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">Ranged</div>
                    <div class="monster-combat-value"><?= $monster['ranged'] ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['is_hard'] === 'true'): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">Hard</div>
                    <div class="monster-combat-value">Yes</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['is_teleport'] === 'true'): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">Teleport</div>
                    <div class="monster-combat-value">Yes</div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['hprinterval'] > 0 && $monster['hpr'] > 0): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">HP Regen</div>
                    <div class="monster-combat-value"><?= $monster['hpr'] ?></div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if($monster['mprinterval'] > 0 && $monster['mpr'] > 0): ?>
            <div class="monster-combat-stat">
                <div class="monster-combat-icon">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="monster-combat-details">
                    <div class="monster-combat-label">MP Regen</div>
                    <div class="monster-combat-value"><?= $monster['mpr'] ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
    
    <?php
// Check if any behavior is true
$hasBehaviors = (
    $monster['is_agro'] === 'true' ||
    $monster['is_agro_poly'] === 'true' ||
    $monster['is_agro_invis'] === 'true' ||
    $monster['is_teleport'] === 'true' ||
    $monster['is_picupitem'] === 'true' ||
    $monster['is_taming'] === 'true' ||
    $monster['can_turnundead'] === 'true' ||
    $monster['cant_resurrect'] === 'true'
);

// Only show the Behavior card if at least one behavior is true
if($hasBehaviors):
?>
<!-- Monster Behavior Section -->
<div class="card">
    <div class="card-header">
        <h2>Behavior</h2>
    </div>
    <div class="card-content">
        <div class="requirements-grid">
            <?php if($monster['is_agro'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Aggressive
            </span>
            <?php endif; ?>
            
            <?php if($monster['is_agro_poly'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Aggressive to Poly
            </span>
            <?php endif; ?>
            
            <?php if($monster['is_agro_invis'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Aggressive to Invisible
            </span>
            <?php endif; ?>
            
            <?php if($monster['is_teleport'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Can Teleport
            </span>
            <?php endif; ?>
            
            <?php if($monster['is_picupitem'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Picks Up Items
            </span>
            <?php endif; ?>
            
            <?php if($monster['is_taming'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Can Be Tamed
            </span>
            <?php endif; ?>
            
            <?php if($monster['can_turnundead'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Affected by Turn Undead
            </span>
            <?php endif; ?>
            
            <?php if($monster['cant_resurrect'] === 'true'): ?>
            <span class="requirement-switch">
                <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                Cannot Be Resurrected
            </span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Monster Skills Section -->
<?php if(!empty($skills)): ?>
<div class="card">
    <div class="card-header">
        <h2>Monster Skills</h2>
    </div>
    <div class="card-content">
        <table class="detail-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Skill</th>
                    <th>%</th>
                    <th>Range</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($skills as $skill): ?>
                <tr>
                    <td><?= $skill['actNo'] ?></td>
                    <td>
                        <?php if(!empty($skill['skillId'])): ?>
                            <?= htmlspecialchars($skill['desc_en']) ?>
                        <?php else: ?>
                            <?= 'Skill #' . $skill['skillId'] ?>
                        <?php endif; ?>
                    </td>
                    <td><?= $skill['prob'] ?>%</td>
                    <td><?= $skill['range'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Drops Section -->
<?php if(!empty($drops)): ?>
<div class="card">
    <div class="card-header">
        <h2>Drops</h2>
    </div>
    <div class="card-content">
        <table class="detail-table">
            <thead>
                <tr>
                    <th width="40">Icon</th>
                    <th>Item</th>
                    <th>Drop Chance</th>
                    <th>Min Count</th>
                    <th>Max Count</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($drops as $drop): ?>
                <tr>
                    <td>
                        <img src="<?= SITE_URL ?>/assets/img/items/<?= $drop['item_icon'] ?>.png" 
                             alt="<?= htmlspecialchars($drop['item_name']) ?>" 
                             class="item-icon"
                             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                    </td>
                    <td>
                        <?php if($drop['item_type'] === 'weapon'): ?>
                            <a href="<?= SITE_URL ?>/pages/weapons/weapon-detail.php?id=<?= $drop['itemId'] ?>">
                                <?= htmlspecialchars($drop['item_name']) ?>
                            </a>
                        <?php elseif($drop['item_type'] === 'armor'): ?>
                            <a href="<?= SITE_URL ?>/pages/armor/armor-detail.php?id=<?= $drop['itemId'] ?>">
                                <?= htmlspecialchars($drop['item_name']) ?>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($drop['item_name']) ?>
                        <?php endif; ?>
                        <?php if($drop['Enchant'] > 0): ?>
                            <span class="badge badge-success">+<?= $drop['Enchant'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= formatDropChance($drop['chance']) ?></td>
                    <td><?= $drop['min'] ?></td>
                    <td><?= $drop['max'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Spawn Locations Section -->
<?php if(!empty($spawns) || !empty($bossSpawns)): ?>
<div class="card">
    <div class="card-header">
        <h2>Spawn Locations</h2>
    </div>
    <div class="card-content">
        <?php if(!empty($spawns)): ?>
        <h3>Regular Spawns</h3>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Map</th>
                    <th>Location</th>
                    <th>Count</th>
                    <th>Respawn Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($spawns as $spawn): ?>
                <tr>
                    <td><?= htmlspecialchars($spawn['map_name'] ?? 'Unknown Map') ?></td>
                    <td>
                        <?php if($spawn['locx1'] > 0 && $spawn['locy1'] > 0): ?>
                            <?= $spawn['locx1'] ?>, <?= $spawn['locy1'] ?>
                            <?php if($spawn['locx2'] > 0 && $spawn['locy2'] > 0): ?>
                                to <?= $spawn['locx2'] ?>, <?= $spawn['locy2'] ?>
                            <?php endif; ?>
                        <?php else: ?>
                            Random
                        <?php endif; ?>
                    </td>
                    <td><?= $spawn['count'] ?></td>
                    <td>
                        <?php 
                        // Format respawn time
                        $minSec = $spawn['min_respawn_delay'];
                        $maxSec = $spawn['max_respawn_delay'];
                        
                        if($minSec === $maxSec) {
                            echo ceil($minSec / 60) . ' minutes';
                        } else {
                            echo ceil($minSec / 60) . '-' . ceil($maxSec / 60) . ' minutes';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
        
        <?php if(!empty($bossSpawns)): ?>
        <h3>Boss Spawns</h3>
        <table class="detail-table">
            <thead>
                <tr>
                    <th>Map</th>
                    <th>Location</th>
                    <th>Respawn Time</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($bossSpawns as $spawn): ?>
                <tr>
                    <td><?= htmlspecialchars($spawn['map_name'] ?? 'Unknown Map') ?></td>
                    <td>
                        <?php if($spawn['locx'] > 0 && $spawn['locy'] > 0): ?>
                            <?= $spawn['locx'] ?>, <?= $spawn['locy'] ?>
                        <?php else: ?>
                            Random
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        // Format respawn time (for boss spawns)
                        $spawnTime = $spawn['SpawnTime'] ?? 0;
                        if($spawnTime > 0) {
                            $hours = floor($spawnTime / 3600);
                            $minutes = floor(($spawnTime % 3600) / 60);
                            
                            if($hours > 0) {
                                echo $hours . ' hours';
                                if($minutes > 0) {
                                    echo ' ' . $minutes . ' minutes';
                                }
                            } else {
                                echo $minutes . ' minutes';
                            }
                        } else {
                            echo 'Random';
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Additional Notes -->
<?php if(!empty($monster['note'])): ?>
<div class="card">
    <div class="card-header">
        <h2>Additional Notes</h2>
    </div>
    <div class="card-content">
        <div class="description">
            <?= nl2br(htmlspecialchars($monster['note'])) ?>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
<?php
// Include footer
require_once '../../includes/footer.php';
?>
