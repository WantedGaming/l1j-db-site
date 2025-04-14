<?php
/**
 * Doll detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Magic Doll Details';
$pageDescription = 'Detailed information about magic dolls in L1J Remastered, including stats, abilities, and effects.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Get doll ID from URL
$dollId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to dolls list
if($dollId <= 0) {
    header('Location: dolls-list.php');
    exit;
}

// Get doll details
$query = "SELECT e.*, d.*, n.*, p.*,
          SUBSTRING_INDEX(e.material, '(', 1) as material_name
          FROM etcitem e 
          JOIN magicdoll_info d ON e.item_id = d.itemId
          JOIN npc n ON d.dollNpcId = n.npcid
          LEFT JOIN magicdoll_potential p ON d.bonusItemId = p.bonusId
          WHERE e.item_id = ? AND e.use_type = 'MAGICDOLL' AND n.impl LIKE '%L1Doll%'";
$doll = $db->getRow($query, [$dollId]);

// If doll not found, show error
if(!$doll) {
    echo '<div class="container"><div class="error-message">Magic Doll not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get monsters that drop this doll with spawn information
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
$dropMonsters = $db->getRows($dropQuery, [$dollId]);

// Set page title to doll name
$pageTitle = $doll['desc_en'];

?>

<!-- Hero Section with Transparent Doll Image -->
<div class="weapon-hero">
    <div class="weapon-hero-image-container">
        <img src="<?= SITE_URL ?>/assets/img/items/<?= $doll['iconId'] ?>.png" 
             alt="<?= htmlspecialchars($doll['desc_en']) ?>" 
             class="weapon-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
    </div>
    <div class="weapon-hero-content">
        <h1><?= htmlspecialchars($doll['desc_en']) ?></h1>
        <p>Level <?= $doll['lvl'] ?> Magic Doll</p>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a> &raquo;
        <a href="<?= SITE_URL ?>/pages/dolls/dolls-list.php">Magic Dolls</a> &raquo;
        <span><?= htmlspecialchars($doll['desc_en']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Image Card -->
        <div class="card">
            <div class="detail-image-container">
                <img src="<?= SITE_URL ?>/assets/img/items/<?= $doll['iconId'] ?>.png" 
                     alt="<?= htmlspecialchars($doll['desc_en']) ?>" 
                     class="detail-image-large"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
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
                        <th>Name</th>
                        <td><?= htmlspecialchars($doll['desc_en']) ?></td>
                    </tr>
                    <tr>
                        <th>Doll Name</th>
                        <td><?= htmlspecialchars($doll['desc_kr']) ?></td>
                    </tr>
                    <tr>
                        <th>Material</th>
                        <td><?= $doll['material_name'] !== null ? htmlspecialchars(formatMaterial($doll['material_name'])) : '' ?></td>
                    </tr>
                    <tr>
                        <th>Grade</th>
                        <td><?= $doll['grade'] ?></td>
                    </tr>
                    <tr>
                        <th>Level</th>
                        <td><?= $doll['lvl'] ?></td>
                    </tr>
                    <tr>
                        <th>Haste</th>
                        <td><?= $doll['haste'] === 'true' ? 'Yes' : 'No' ?></td>
                    </tr>
                    <?php if ($doll['weight'] > 0): ?>
                    <tr>
                        <th>Weight</th>
                        <td><?= $doll['weight'] / 1000 ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($doll['min_lvl'] > 0 || $doll['max_lvl'] > 0): ?>
                    <tr>
                        <th>Required Level</th>
                        <td>
                            <?php if ($doll['min_lvl'] > 0 && $doll['max_lvl'] > 0): ?>
                                <?= $doll['min_lvl'] ?> - <?= $doll['max_lvl'] ?>
                            <?php elseif ($doll['min_lvl'] > 0): ?>
                                Min: <?= $doll['min_lvl'] ?>
                            <?php elseif ($doll['max_lvl'] > 0): ?>
                                Max: <?= $doll['max_lvl'] ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Doll Stats Card -->
        <div class="card">
            <div class="card-header">
                <h2>Doll Stats</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <tr>
                        <th>HP</th>
                        <td><?= $doll['hp'] ?></td>
                    </tr>
                    <tr>
                        <th>MP</th>
                        <td><?= $doll['mp'] ?></td>
                    </tr>
                    <tr>
                        <th>AC</th>
                        <td><?= $doll['ac'] ?></td>
                    </tr>
                    <tr>
                        <th>STR</th>
                        <td><?= $doll['str'] ?></td>
                    </tr>
                    <tr>
                        <th>CON</th>
                        <td><?= $doll['con'] ?></td>
                    </tr>
                    <tr>
                        <th>DEX</th>
                        <td><?= $doll['dex'] ?></td>
                    </tr>
                    <tr>
                        <th>WIS</th>
                        <td><?= $doll['wis'] ?></td>
                    </tr>
                    <tr>
                        <th>INT</th>
                        <td><?= $doll['intel'] ?></td>
                    </tr>
                    <tr>
                        <th>MR</th>
                        <td><?= $doll['mr'] ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Doll Potential Card -->
        <?php if($doll['bonusItemId'] > 0): ?>
        <div class="card">
            <div class="card-header">
                <h2>Doll Potential</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <?php if($doll['name']): ?>
                    <tr>
                        <th>Name</th>
                        <td><?= htmlspecialchars($doll['name']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($doll['bonusCount'] > 0): ?>
                    <tr>
                        <th>Bonus Count</th>
                        <td><?= $doll['bonusCount'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($doll['bonusInterval'] > 0): ?>
                    <tr>
                        <th>Bonus Interval</th>
                        <td><?= $doll['bonusInterval'] ?> seconds</td>
                    </tr>
                    <?php endif; ?>
                    <?php if($doll['damageChance'] > 0): ?>
                    <tr>
                        <th>Damage Chance</th>
                        <td><?= $doll['damageChance'] ?>%</td>
                    </tr>
                    <?php endif; ?>
                    <?php
                    // List of potential stats to check and display
                    $potentialStats = [
                        'ac_bonus' => 'AC Bonus',
                        'str' => 'STR',
                        'con' => 'CON',
                        'dex' => 'DEX',
                        'int' => 'INT',
                        'wis' => 'WIS',
                        'cha' => 'CHA',
                        'allStatus' => 'All Stats',
                        'shortDamage' => 'Short Damage',
                        'shortHit' => 'Short Hit',
                        'shortCritical' => 'Short Critical',
                        'longDamage' => 'Long Damage',
                        'longHit' => 'Long Hit',
                        'longCritical' => 'Long Critical',
                        'spellpower' => 'Spell Power',
                        'magicHit' => 'Magic Hit',
                        'magicCritical' => 'Magic Critical',
                        'hp' => 'HP',
                        'mp' => 'MP',
                        'hpr' => 'HP Regen',
                        'mpr' => 'MP Regen',
                        'hpStill' => 'HP Still',
                        'mpStill' => 'MP Still',
                        'stillChance' => 'Still Chance',
                        'hprAbsol' => 'HP Regen (Absolute)',
                        'mprAbsol' => 'MP Regen (Absolute)',
                        'attrFire' => 'Fire Attribute',
                        'attrWater' => 'Water Attribute',
                        'attrWind' => 'Wind Attribute',
                        'attrEarth' => 'Earth Attribute',
                        'attrAll' => 'All Attributes',
                        'mr' => 'Magic Resistance',
                        'expBonus' => 'EXP Bonus',
                        'carryBonus' => 'Carry Bonus',
                        'dg' => 'DG',
                        'er' => 'ER',
                        'me' => 'ME',
                        'reduction' => 'Damage Reduction',
                        'reductionEgnor' => 'Reduction Ignore',
                        'reductionMagic' => 'Magic Reduction',
                        'reductionPercent' => 'Reduction %',
                        'PVPDamage' => 'PVP Damage',
                        'PVPReduction' => 'PVP Reduction',
                        'PVPReductionEgnor' => 'PVP Reduction Ignore',
                        'PVPReductionMagic' => 'PVP Magic Reduction',
                        'PVPReductionMagicEgnor' => 'PVP Magic Reduction Ignore',
                        'toleranceSkill' => 'Skill Tolerance',
                        'toleranceSpirit' => 'Spirit Tolerance',
                        'toleranceDragon' => 'Dragon Tolerance',
                        'toleranceFear' => 'Fear Tolerance',
                        'toleranceAll' => 'All Tolerance',
                        'hitupSkill' => 'Skill Hit',
                        'hitupSpirit' => 'Spirit Hit',
                        'hitupDragon' => 'Dragon Hit',
                        'hitupFear' => 'Fear Hit',
                        'hitupAll' => 'All Hit',
                        'imunEgnor' => 'Immunity Ignore',
                        'strangeTimeIncrease' => 'Status Time Increase'
                    ];

                    foreach($potentialStats as $key => $label):
                        if(isset($doll[$key]) && $doll[$key] != 0):
                    ?>
                    <tr>
                        <th><?= $label ?></th>
                        <td><?= $doll[$key] ?></td>
                    </tr>
                    <?php 
                        endif;
                    endforeach;
                    ?>
                    <?php
                    // Speed bonuses
                    $speedTypes = ['first', 'second', 'third', 'forth'];
                    foreach($speedTypes as $speed):
                        $field = $speed . 'Speed';
                        if($doll[$field] === 'true'):
                    ?>
                    <tr>
                        <th><?= ucfirst($speed) ?> Speed</th>
                        <td>Yes</td>
                    </tr>
                    <?php
                        endif;
                    endforeach;
                    ?>
                    <?php if($doll['skilId'] > -1): ?>
                    <tr>
                        <th>Skill ID</th>
                        <td><?= $doll['skilId'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($doll['skillChance'] > 0): ?>
                    <tr>
                        <th>Skill Chance</th>
                        <td><?= $doll['skillChance'] ?>%</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Requirements Section -->
        <?php 
        $hasClassRequirements = $doll['use_royal'] || $doll['use_knight'] || $doll['use_elf'] || 
                               $doll['use_mage'] || $doll['use_darkelf'] || $doll['use_dragonknight'] || 
                               $doll['use_illusionist'] || $doll['use_warrior'] || $doll['use_fencer'] || 
                               $doll['use_lancer'];
        if ($hasClassRequirements): 
        ?>
        <div class="card">
            <div class="card-header">
                <h2>Class Requirements</h2>
            </div>
            <div class="card-content">
                <div class="requirements-grid">
                    <?php if ($doll['use_royal']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Royal
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_knight']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Knight
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_elf']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Elf
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_mage']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Mage
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_darkelf']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Dark Elf
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_dragonknight']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Dragon Knight
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_illusionist']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Illusionist
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_warrior']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Warrior
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_fencer']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Fencer
                    </span>
                    <?php endif; ?>
                    <?php if ($doll['use_lancer']): ?>
                    <span class="requirement-switch">
                        <span class="requirement-switch-icon requirement-switch-yes">✓</span>
                        Lancer
                    </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Drop Information -->
        <?php if(!empty($dropMonsters)): ?>
        <div class="card">
            <div class="card-header">
                <h2>Drop Information</h2>
            </div>
            <div class="card-content">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Monster</th>
                            <th>Level</th>
                            <th>Location</th>
                            <th>Drop Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dropMonsters as $monster): ?>
                            <tr>
                                <td><?= htmlspecialchars($monster['monster_name']) ?></td>
                                <td><?= $monster['monster_level'] ?></td>
                                <td>
                                    <?php if($monster['map_name']): ?>
                                        <?= htmlspecialchars($monster['map_name']) ?>
                                        <?php if($monster['locx'] && $monster['locy']): ?>
                                            (<?= $monster['locx'] ?>, <?= $monster['locy'] ?>)
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Unknown
                                    <?php endif; ?>
                                </td>
                                <td><?= number_format($monster['chance'] / 10000, 4) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 