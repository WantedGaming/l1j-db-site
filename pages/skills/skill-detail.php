<?php
/**
 * Skill detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Skill Details';
$pageDescription = 'Detailed information about skills in L1J Remastered, including effects, requirements, and how to obtain them.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Get skill ID from URL
$skillId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to skills list
if($skillId <= 0) {
    header('Location: skill-list.php');
    exit;
}

// Get skill details
$query = "SELECT s.*, 
          si.icon, si.onIconId, si.offIconId, si.tooltipStrId, si.isGood, si.isPassiveSpell,
          si.desc_en as info_desc
          FROM skills s 
          LEFT JOIN skills_info si ON s.skill_id = si.skillId
          WHERE s.skill_id = ?";
$skill = $db->getRow($query, [$skillId]);

// If skill not found, show error
if(!$skill) {
    echo '<div class="container"><div class="alert alert-danger">Skill not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get skill handler if available
$handlerQuery = "SELECT * FROM skills_handler WHERE skillId = ?";
$skillHandler = $db->getRow($handlerQuery, [$skillId]);

// Get passive skill information if applicable
$passiveQuery = "SELECT * FROM skills_passive WHERE back_active_skill_id = ?";
$passiveSkill = $db->getRow($passiveQuery, [$skillId]);

// Get spell effect information if available
$spellQuery = "SELECT * FROM bin_spell_common WHERE spell_id = ?";
$spellEffect = $db->getRow($spellQuery, [$skillId]);

// Get passive spell effect information if available
$passiveSpellQuery = "SELECT * FROM bin_passivespell_common WHERE passive_id = ?";
$passiveSpellEffect = $db->getRow($passiveSpellQuery, [$skillId]);

// Check if this skill is sold by any NPC
$npcShopQuery = "SELECT s.npc_id, n.desc_en as npc_name, n.spriteId as npc_sprite_id, 
                       sp.locx, sp.locy, m.locationname as map_name, m.mapid, m.pngId
                FROM shop s
                JOIN npc n ON s.npc_id = n.npcid
                LEFT JOIN spawnlist sp ON n.npcid = sp.npc_templateid
                LEFT JOIN mapids m ON sp.mapid = m.mapid
                WHERE s.item_id = ? AND s.note LIKE '%skill%'
                LIMIT 1";
$skillNpcSeller = $db->getRow($npcShopQuery, [$skillId]);

// Check if this skill is dropped by any monsters
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.lvl as monster_level, 
              n.spriteId as monster_sprite_id, n.npcid,
              s.count, s.locx, s.locy, s.randomx, s.randomy,
              s.min_respawn_delay as respawnDelay,
              s.locx1, s.locy1, s.locx2, s.locy2,
              m.locationname as map_name, m.mapid, m.pngId
              FROM droplist d
              JOIN npc n ON d.mobId = n.npcid
              LEFT JOIN spawnlist s ON n.npcid = s.npc_templateid
              LEFT JOIN mapids m ON s.mapid = m.mapid
              WHERE d.itemId = ? AND n.impl LIKE '%L1Monster%'
              ORDER BY d.chance DESC";
$dropMonsters = $db->getRows($dropQuery, [$skillId]);

// Set page title to skill name
$pageTitle = $skill['name'];

// Get skill icon path
$iconId = $skill['icon'] ?? $skill['onIconId'] ?? 0;
$skillIconPath = SITE_URL . '/assets/img/skills/' . $iconId . '.png';
?>

<!-- Hero Section with Skill Information -->
<div class="weapon-hero">
    <div class="weapon-hero-image-container">
        <img src="<?= $skillIconPath ?>" 
             alt="<?= htmlspecialchars($skill['desc_en']) ?>" 
             class="weapon-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/skill-placeholder.png'">
    </div>
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars($skill['desc_en']) ?></h1>
        <p>Level <?= $skill['skill_level'] ?> 
            <?php if($skill['classType'] != 'none'): ?>
                <span class="badge badge-info"><?= ucfirst($skill['classType']) ?></span>
            <?php endif; ?>
            <span class="badge <?= getGradeBadgeClass($skill['grade']) ?>"><?= formatGrade($skill['grade']) ?></span>
        </p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/skills/skill-list.php">Skills</a> &raquo;
        <span><?= htmlspecialchars($skill['desc_en']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= $skillIconPath ?>" 
                     alt="<?= htmlspecialchars($skill['desc_en']) ?>" 
                     class="detail-image-large"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/skill-placeholder.png'">
            </div>
        </div>

        <!-- Basic Information Card -->
        <div class="card">
            <div class="card-header">
                <h2>Detail</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <tr>
                        <th>Skill ID (Admin)</th>
                        <td><?= $skill['skill_id'] ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?= htmlspecialchars($skill['desc_en']) ?></td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td><?= $skill['skill_level'] ?></td>
                    </tr>
                    <tr>
                        <th>Class</th>
                        <td><?= $skill['classType'] != 'none' ? ucfirst($skill['classType']) : 'Common' ?></td>
                    </tr>
                    <tr>
                        <th>Target Type</th>
                        <td><?= $skill['target'] != 'NONE' ? ucfirst(strtolower($skill['target'])) : 'Passive' ?></td>
                    </tr>
                    <tr>
                        <th>Target To</th>
                        <td><?= $skill['target_to'] ?></td>
                    </tr>
                    <?php if ($skill['mpConsume'] > 0): ?>
                    <tr>
                        <th>MP Cost</th>
                        <td><?= $skill['mpConsume'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($skill['hpConsume'] > 0): ?>
                    <tr>
                        <th>HP Cost</th>
                        <td><?= $skill['hpConsume'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($skill['reuseDelay'] > 0): ?>
                    <tr>
                        <th>Reuse Delay</th>
                        <td><?= $skill['reuseDelay'] ?> ms</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($skill['buffDuration'] > 0): ?>
                    <tr>
                        <th>Duration</th>
                        <td><?= $skill['buffDuration'] ?> seconds</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($skill['isPassiveSpell'] == 'true'): ?>
                    <tr>
                        <th>Skill Type</th>
                        <td>Passive</td>
                    </tr>
                    <?php else: ?>
                    <tr>
                        <th>Skill Type</th>
                        <td>Active</td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($skill['ranged'] > 0): ?>
                    <tr>
                        <th>Range</th>
                        <td><?= $skill['ranged'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($skill['area']) && $skill['area'] > 0): ?>
                    <tr>
                        <th>Area</th>
                        <td><?= $skill['area'] ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Skill Effect Details Section -->
    <div class="card">
        <div class="card-header">
            <h2>Effects</h2>
        </div>
        <div class="card-content">
            <div class="description">
                <?php if (!empty($skill['desc_en'])): ?>
                    <p><?= nl2br(htmlspecialchars($skill['desc_en'])) ?></p>
                <?php elseif (!empty($skill['info_desc'])): ?>
                    <p><?= nl2br(htmlspecialchars($skill['info_desc'])) ?></p>
                <?php else: ?>
                    <p>No detailed description available for this skill.</p>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($skill['effect_txt'])): ?>
            <div class="effect-details">
                <h3>Effects</h3>
                <p><?= nl2br(htmlspecialchars($skill['effect_txt'])) ?></p>
            </div>
            <?php endif; ?>
            
            <?php
            // Display damage information if available
            $hasDamage = $skill['damage_value'] > 0 || $skill['damage_dice'] > 0 || $skill['damage_dice_count'] > 0;
            $hasProbability = $skill['probability_value'] > 0 || $skill['probability_dice'] > 0;
            
            if ($hasDamage || $hasProbability || !empty($skill['attr']) && $skill['attr'] != 'NONE'):
            ?>
            <div class="stat-grid">
                <?php if ($hasDamage): ?>
                <div class="stat-item">
                    <span class="stat-label">Damage</span>
                    <span class="stat-value">
                        <?= $skill['damage_value'] ?>
                        <?php if ($skill['damage_dice'] > 0 && $skill['damage_dice_count'] > 0): ?>
                            + <?= $skill['damage_dice_count'] ?>d<?= $skill['damage_dice'] ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if ($hasProbability): ?>
                <div class="stat-item">
                    <span class="stat-label">Probability</span>
                    <span class="stat-value">
                        <?= $skill['probability_value'] ?>
                        <?php if ($skill['probability_dice'] > 0): ?>
                            + 1d<?= $skill['probability_dice'] ?>
                        <?php endif; ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skill['attr']) && $skill['attr'] != 'NONE'): ?>
                <div class="stat-item">
                    <span class="stat-label">Attribute</span>
                    <span class="stat-value"><?= ucfirst(strtolower($skill['attr'])) ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($skill['type']) && $skill['type'] != 'NONE'): ?>
                <div class="stat-item">
                    <span class="stat-label">Effect Type</span>
                    <span class="stat-value"><?= ucfirst(strtolower($skill['type'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Spell Effects Section (if applicable) -->
    <?php if ($spellEffect): ?>
    <div class="card">
        <div class="card-header">
            <h2>Spell Effects</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <tr>
                    <th>Duration</th>
                    <td><?= $spellEffect['duration'] ?> seconds</td>
                </tr>
                <?php if (!empty($spellEffect['spell_category']) && $spellEffect['spell_category'] != 'SPELL(0)'): ?>
                <tr>
                    <th>Category</th>
                    <td><?= str_replace(array('(0)', '(1)', '(2)'), '', $spellEffect['spell_category']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($spellEffect['spell_bonus_list'])): ?>
                <tr>
                    <th>Bonus Effects</th>
                    <td><?= nl2br(htmlspecialchars($spellEffect['spell_bonus_list'])) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Passive Spell Effects Section (if applicable) -->
    <?php if ($passiveSpellEffect): ?>
    <div class="card">
        <div class="card-header">
            <h2>Passive Effects</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <?php if (!empty($passiveSpellEffect['duration'])): ?>
                <tr>
                    <th>Duration</th>
                    <td><?= $passiveSpellEffect['duration'] ?> seconds</td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($passiveSpellEffect['spell_bonus_list'])): ?>
                <tr>
                    <th>Bonus Effects</th>
                    <td><?= nl2br(htmlspecialchars($passiveSpellEffect['spell_bonus_list'])) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($passiveSpellEffect['delay_group_id']) && $passiveSpellEffect['delay_group_id'] > 0): ?>
                <tr>
                    <th>Delay Group</th>
                    <td><?= $passiveSpellEffect['delay_group_id'] ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Requirements Section -->
    <?php if ($skill['itemConsumeId'] > 0 || $skill['itemConsumeCount'] > 0): ?>
    <div class="card">
        <div class="card-header">
            <h2>Requirements</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <?php if ($skill['itemConsumeId'] > 0): 
                    // Try to get the item name
                    $itemName = $db->getColumn("SELECT COALESCE(
                        (SELECT desc_en FROM weapon WHERE item_id = ?),
                        (SELECT desc_en FROM armor WHERE item_id = ?),
                        (SELECT desc_en FROM etcitem WHERE item_id = ?)
                    ) as item_name", [$skill['itemConsumeId'], $skill['itemConsumeId'], $skill['itemConsumeId']]);
                ?>
                <tr>
                    <th>Required Item</th>
                    <td>
                        <?= !empty($itemName) ? htmlspecialchars($itemName) : 'Item ID: ' . $skill['itemConsumeId'] ?>
                        <?php if ($skill['itemConsumeCount'] > 1): ?>
                            (×<?= $skill['itemConsumeCount'] ?>)
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Skill Implementation Section (if skill handler exists) -->
    <?php if ($skillHandler): ?>
    <div class="card">
        <div class="card-header">
            <h2>Implementation</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <tr>
                    <th>Handler Class</th>
                    <td><?= htmlspecialchars($skillHandler['className']) ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- NPC Seller Section (if available) -->
    <?php if ($skillNpcSeller): ?>
    <div class="card">
        <div class="card-header">
            <h2>Sold By</h2>
        </div>
        <div class="card-content">
            <div class="detail-content-grid">
                <div>
                    <div style="text-align: center; margin-bottom: 1rem;">
                        <img src="<?= SITE_URL ?>/assets/img/npcs/<?= $skillNpcSeller['npc_sprite_id'] ?>.png" 
                             alt="<?= htmlspecialchars($skillNpcSeller['npc_name']) ?>" 
                             style="max-width: 128px; max-height: 128px;"
                             onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/npc-placeholder.png'">
                    </div>
                </div>
                <div>
                    <h3><?= htmlspecialchars($skillNpcSeller['npc_name']) ?></h3>
                    <table class="detail-table">
                        <tr>
                            <th>Location</th>
                            <td><?= htmlspecialchars($skillNpcSeller['map_name'] ?? 'Unknown') ?></td>
                        </tr>
                        <?php if ($skillNpcSeller['locx'] && $skillNpcSeller['locy']): ?>
                        <tr>
                            <th>Coordinates</th>
                            <td>X: <?= $skillNpcSeller['locx'] ?>, Y: <?= $skillNpcSeller['locy'] ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <?php if ($skillNpcSeller['mapid'] && $skillNpcSeller['pngId']): ?>
                    <div style="margin-top: 1rem;">
                        <div style="position: relative; width: 100%; padding-top: 75%; overflow: hidden; border-radius: 8px;">
                            <img src="<?= get_map_image($skillNpcSeller['pngId']) ?>" 
                                 alt="<?= htmlspecialchars($skillNpcSeller['map_name']) ?>" 
                                 style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                            
                            <!-- NPC Marker -->
                            <div class="npc-marker" style="position: absolute; left: <?= ($skillNpcSeller['locx'] / 32768) * 100 ?>%; top: <?= ($skillNpcSeller['locy'] / 32768) * 100 ?>%; width: 16px; height: 16px; background: #4caf50; border: 2px solid white; border-radius: 50%; transform: translate(-50%, -50%); box-shadow: 0 0 0 2px rgba(0,0,0,0.3);">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monster Drops Section (if any) -->
    <?php if (!empty($dropMonsters)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Dropped By</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Monster</th>
                        <th>Level</th>
                        <th>Drop Chance</th>
                        <th>Max</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($dropMonsters as $drop): ?>
                    <tr>
                        <td>
                            <div class="monster-list-item">
                                <img src="<?= SITE_URL ?>/assets/img/monsters/ms<?= $drop['monster_sprite_id'] ?>.png" 
                                     alt="<?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?>"
                                     class="monster-sprite"
                                     onerror="this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
                                <a href="<?= SITE_URL ?>/pages/monsters/detail.php?id=<?= $drop['npcid'] ?>">
                                    <?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= $drop['monster_level'] ?></td>
                        <td><?= formatDropChance($drop['chance']) ?></td>
                        <td><?= $drop['max'] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Monster Spawn Locations Section (if any) -->
    <?php if (!empty($dropMonsters)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Spawn Locations</h2>
        </div>
        <div class="card-content">
            <div class="spawn-locations-grid">
                <?php foreach($dropMonsters as $drop):
                    if (!empty($drop['map_name'])):
                ?>
                    <div class="spawn-location-card">
                        <div class="spawn-location-header">
                            <h3><?= htmlspecialchars($drop['mobname_en'] ?? $drop['monster_name']) ?></h3>
                            <div class="spawn-meta">
                                <span class="spawn-count"><?= $drop['count'] ?> spawns</span>
                                <?php if($drop['respawnDelay'] > 0): ?>
                                    <span class="respawn-time">
                                        <i class="fas fa-clock"></i>
                                        <?= floor($drop['respawnDelay'] / 60) ?>m <?= $drop['respawnDelay'] % 60 ?>s
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="spawn-location-content">
                            <div class="map-container">
                                <img src="<?= get_map_image($drop['pngId']) ?>" 
                                     alt="<?= htmlspecialchars($drop['map_name']) ?>" 
                                     class="map-image">
                                <?php
                                // If we have a spawn area (locx1/locy1 to locx2/locy2)
                                if ($drop['locx1'] > 0 && $drop['locy1'] > 0 && $drop['locx2'] > 0 && $drop['locy2'] > 0): ?>
                                    <div class="spawn-area" style="
                                        left: <?= ($drop['locx1'] / 32768) * 100 ?>%;
                                        top: <?= ($drop['locy1'] / 32768) * 100 ?>%;
                                        width: <?= (($drop['locx2'] - $drop['locx1']) / 32768) * 100 ?>%;
                                        height: <?= (($drop['locy2'] - $drop['locy1']) / 32768) * 100 ?>%;">
                                        <div class="spawn-area-label">Spawn Area</div>
                                    </div>
                                <?php else: ?>
                                    <!-- Single point spawn with random range -->
                                    <div class="spawn-marker" style="
                                        left: <?= ($drop['locx'] / 32768) * 100 ?>%;
                                        top: <?= ($drop['locy'] / 32768) * 100 ?>%;">
                                        <div class="spawn-point"></div>
                                        <?php if ($drop['randomx'] > 0 || $drop['randomy'] > 0): ?>
                                            <div class="spawn-range" style="
                                                width: <?= ($drop['randomx'] * 2 / 32768) * 100 ?>%;
                                                height: <?= ($drop['randomy'] * 2 / 32768) * 100 ?>%;">
                                                <div class="spawn-range-label">Random Range</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="spawn-details">
                                <p class="map-name"><?= htmlspecialchars($drop['map_name']) ?></p>
                                <p class="coordinates">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php if ($drop['locx1'] > 0): ?>
                                        Area: (<?= $drop['locx1'] ?>, <?= $drop['locy1'] ?>) to (<?= $drop['locx2'] ?>, <?= $drop['locy2'] ?>)
                                    <?php else: ?>
                                        Center: (<?= $drop['locx'] ?>, <?= $drop['locy'] ?>)
                                        <?php if ($drop['randomx'] > 0 || $drop['randomy'] > 0): ?>
                                            <br>
                                            <i class="fas fa-arrows-alt"></i>
                                            Range: ±<?= $drop['randomx'] ?> x, ±<?= $drop['randomy'] ?> y
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Additional Notes -->
    <?php if (!empty($skill['target_to_txt']) || !empty($skill['buffDuration_txt'])): ?>
    <div class="card">
        <div class="card-header">
            <h2>Notes (Admin)</h2>
        </div>
        <div class="card-content">
            <div class="description">
                <?php if (!empty($skill['target_to_txt'])): ?>
                    <p><strong>Target Details:</strong> <?= nl2br(htmlspecialchars($skill['target_to_txt'])) ?></p>
                <?php endif; ?>
                
                <?php if (!empty($skill['buffDuration_txt'])): ?>
                    <p><strong>Duration Details:</strong> <?= nl2br(htmlspecialchars($skill['buffDuration_txt'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Custom styles for skill details page */
.spawn-locations-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin: 1.5rem 0;
}

.spawn-location-card {
    background: var(--primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
}

.spawn-location-header {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--secondary);
}

.spawn-location-header h3 {
    margin: 0;
    font-size: 1.1rem;
}

.spawn-count {
    background: var(--accent);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-size: 0.9rem;
}

.map-container {
    position: relative;
    width: 100%;
    padding-top: 75%; /* 4:3 aspect ratio */
    overflow: hidden;
}

.map-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.spawn-marker {
    position: absolute;
    transform: translate(-50%, -50%);
    z-index: 2;
}

.spawn-point {
    width: 12px;
    height: 12px;
    background: var(--accent);
    border: 2px solid white;
    border-radius: 50%;
    box-shadow: 0 0 0 2px rgba(0,0,0,0.3);
}

.spawn-range {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    border: 2px solid rgba(255,255,255,0.3);
    border-radius: 50%;
    pointer-events: none;
}

.spawn-range-label,
.spawn-area-label {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translate(-50%, -100%);
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.spawn-marker:hover .spawn-range-label,
.spawn-area:hover .spawn-area-label {
    opacity: 1;
}

.spawn-area {
    position: absolute;
    border: 2px solid rgba(255,75,31,0.5);
    background: rgba(255,75,31,0.1);
    pointer-events: all;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.spawn-area:hover {
    background: rgba(255,75,31,0.2);
}

.spawn-details {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
}

.coordinates {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    color: #888;
    font-size: 0.9rem;
    margin: 0;
}

.coordinates i {
    width: 16px;
    color: var(--accent);
    margin-right: 0.5rem;
}

@media (max-width: 768px) {
    .spawn-locations-grid {
        grid-template-columns: 1fr;
    }
}

.monster-list-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.monster-sprite {
    width: 48px;
    height: 48px;
    object-fit: contain;
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';
?>