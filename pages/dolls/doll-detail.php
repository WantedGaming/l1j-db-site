<?php
/**
 * Magic Doll detail page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Magic Doll Details';
$pageDescription = 'Detailed information about magic dolls in L1J Remastered.';

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

// Get doll details with join to magicdoll_info
$query = "SELECT e.*, m.*, n.*, 
           (SELECT COUNT(*) FROM magicdoll_info WHERE blessItemId = e.item_id) as is_blessed_version,
           (SELECT item_id FROM etcitem WHERE item_id = m.blessItemId) as blessed_version_id,
           (SELECT desc_en FROM etcitem WHERE item_id = m.blessItemId) as blessed_version_name
          FROM etcitem e 
          LEFT JOIN magicdoll_info m ON e.item_id = m.itemId
          LEFT JOIN npc n ON m.dollNpcId = n.npcid
          WHERE e.item_id = ? AND e.use_type = 'MAGICDOLL'";
$doll = $db->getRow($query, [$dollId]);

// If doll not found, show error
if(!$doll) {
    echo '<div class="container"><div class="error-message">Magic Doll not found.</div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Get monsters that drop this doll
$dropQuery = "SELECT d.*, n.desc_kr as monster_name, n.lvl as monster_level, 
              n.spriteId as monster_sprite_id, n.npcid
              FROM droplist d
              JOIN npc n ON d.mobId = n.npcid
              WHERE d.itemId = ? AND n.impl LIKE '%L1Monster%'
              ORDER BY d.chance DESC";
$drops = $db->getRows($dropQuery, [$dollId]);

// Set page title to doll name
$pageTitle = $doll['desc_en'];

// Get potential doll if available
$potential = null;
if (isset($doll['bonusItemId']) && $doll['bonusItemId'] > 0) {
    $potentialQuery = "SELECT * FROM magicdoll_potential WHERE bonusId = ?";
    $potential = $db->getRow($potentialQuery, [$doll['bonusItemId']]);
}

// Check if there's any common potential info
$commonPotential = null;
if ($potential && isset($potential['id'])) {
    $commonPotentialQuery = "SELECT * FROM bin_potential_common WHERE id = ?";
    $commonPotential = $db->getRow($commonPotentialQuery, [$potential['id']]);
}
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/dolls-hero.jpg');">
    <div class="container">
        <h1><?= htmlspecialchars($doll['desc_en']) ?></h1>
        <p>Grade <?= isset($doll['grade']) ? $doll['grade'] : '0' ?> Magic Doll</p>
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
                <img src="<?= SITE_URL ?>/assets/img/dolls/<?= $doll['iconId'] ?>.png" 
                     alt="<?= htmlspecialchars($doll['desc_en']) ?>" 
                     class="detail-image-large"
                     onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/doll_default.png'">
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
                    <?php if(isset($doll['grade'])): ?>
                    <tr>
                        <th>Grade</th>
                        <td><?= $doll['grade'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($doll['haste']) && $doll['haste'] === 'true'): ?>
                    <tr>
                        <th>Haste</th>
                        <td>Yes</td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($doll['min_lvl']) && $doll['min_lvl'] > 0): ?>
                    <tr>
                        <th>Required Level</th>
                        <td><?= $doll['min_lvl'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($doll['weight']) && $doll['weight'] > 0): ?>
                    <tr>
                        <th>Weight</th>
                        <td><?= $doll['weight'] / 1000 ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($doll['itemGrade']) && !empty($doll['itemGrade'])): ?>
                    <tr>
                        <th>Item Grade</th>
                        <td><?= formatGrade($doll['itemGrade']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($doll['blessed_version_id']) && !empty($doll['blessed_version_id'])): ?>
                    <tr>
                        <th>Blessed Version</th>
                        <td>
                            <a href="doll-detail.php?id=<?= $doll['blessed_version_id'] ?>"><?= htmlspecialchars($doll['blessed_version_name']) ?></a>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if(isset($doll['is_blessed_version']) && $doll['is_blessed_version'] > 0): ?>
                    <tr>
                        <th>Blessed Type</th>
                        <td>Yes</td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- NPC Information -->
    <?php if(isset($doll['dollNpcId']) && !empty($doll['dollNpcId'])): ?>
    <div class="card">
        <div class="card-header">
            <h2>Doll NPC Information</h2>
        </div>
        <div class="card-content">
            <div class="detail-content-grid">
                <div>
                    <?php if(isset($doll['spriteId']) && !empty($doll['spriteId'])): ?>
                    <div style="text-align: center; margin-bottom: 1rem;">
                        <img src="<?= SITE_URL ?>/assets/img/monsters/ms<?= $doll['spriteId'] ?>.png" 
                             alt="<?= htmlspecialchars($doll['desc_kr'] ?? '') ?>" 
                             style="max-width: 200px; max-height: 200px;"
                             onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/monster-placeholder.png'">
                    </div>
                    <?php endif; ?>
                </div>
                <div>
                    <table class="detail-table">
                        <?php if(isset($doll['desc_kr'])): ?>
                        <tr>
                            <th>NPC Name</th>
                            <td><?= htmlspecialchars($doll['desc_kr']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['lvl'])): ?>
                        <tr>
                            <th>Level</th>
                            <td><?= $doll['lvl'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['hp'])): ?>
                        <tr>
                            <th>HP</th>
                            <td><?= number_format($doll['hp']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['mp'])): ?>
                        <tr>
                            <th>MP</th>
                            <td><?= number_format($doll['mp']) ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['ac'])): ?>
                        <tr>
                            <th>AC</th>
                            <td><?= $doll['ac'] ?></td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Doll Attributes -->
    <div class="card">
        <div class="card-header">
            <h2>Doll Attributes</h2>
        </div>
        <div class="card-content">
            <div class="detail-content-grid">
                <div>
                    <h3>Base Stats</h3>
                    <table class="detail-table">
                        <tr>
                            <th>STR</th>
                            <td><?= isset($doll['str']) ? $doll['str'] : 0 ?></td>
                        </tr>
                        <tr>
                            <th>DEX</th>
                            <td><?= isset($doll['dex']) ? $doll['dex'] : 0 ?></td>
                        </tr>
                        <tr>
                            <th>CON</th>
                            <td><?= isset($doll['con']) ? $doll['con'] : 0 ?></td>
                        </tr>
                        <tr>
                            <th>WIS</th>
                            <td><?= isset($doll['wis']) ? $doll['wis'] : 0 ?></td>
                        </tr>
                        <tr>
                            <th>INT</th>
                            <td><?= isset($doll['intel']) ? $doll['intel'] : 0 ?></td>
                        </tr>
                        <tr>
                            <th>CHA</th>
                            <td><?= isset($doll['cha']) ? $doll['cha'] : 0 ?></td>
                        </tr>
                    </table>
                </div>
                <div>
                    <h3>Additional Stats</h3>
                    <table class="detail-table">
                        <?php if(isset($doll['mr'])): ?>
                        <tr>
                            <th>MR</th>
                            <td><?= $doll['mr'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['hpr']) && $doll['hpr'] > 0): ?>
                        <tr>
                            <th>HP Regen</th>
                            <td><?= $doll['hpr'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['mpr']) && $doll['mpr'] > 0): ?>
                        <tr>
                            <th>MP Regen</th>
                            <td><?= $doll['mpr'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['damage_reduction']) && $doll['damage_reduction'] > 0): ?>
                        <tr>
                            <th>Damage Reduction</th>
                            <td><?= $doll['damage_reduction'] ?>%</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Potential Information -->
    <?php if($potential): ?>
    <div class="card">
        <div class="card-header">
            <h2>Doll Potential</h2>
            <?php if(isset($potential['name']) && !empty($potential['name'])): ?>
            <div class="potential-name"><?= htmlspecialchars($potential['name']) ?></div>
            <?php endif; ?>
        </div>
        <div class="card-content">
            <div class="detail-content-grid">
                <div>
                    <h3>Bonus Information</h3>
                    <table class="detail-table">
                        <?php if(isset($doll['bonusCount']) && $doll['bonusCount'] > 0): ?>
                        <tr>
                            <th>Bonus Count</th>
                            <td><?= $doll['bonusCount'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['bonusInterval']) && $doll['bonusInterval'] > 0): ?>
                        <tr>
                            <th>Bonus Interval</th>
                            <td><?= $doll['bonusInterval'] ?> seconds</td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($doll['damageChance']) && $doll['damageChance'] > 0): ?>
                        <tr>
                            <th>Damage Chance</th>
                            <td><?= $doll['damageChance'] ?>%</td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($potential['skilId']) && $potential['skilId'] > -1): ?>
                        <tr>
                            <th>Skill ID</th>
                            <td><?= $potential['skilId'] ?></td>
                        </tr>
                        <?php endif; ?>
                        <?php if(isset($potential['skillChance']) && $potential['skillChance'] > 0): ?>
                        <tr>
                            <th>Skill Chance</th>
                            <td><?= $potential['skillChance'] ?>%</td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <?php if(
                        (isset($potential['firstSpeed']) && $potential['firstSpeed'] === 'true') || 
                        (isset($potential['secondSpeed']) && $potential['secondSpeed'] === 'true') || 
                        (isset($potential['thirdSpeed']) && $potential['thirdSpeed'] === 'true') || 
                        (isset($potential['forthSpeed']) && $potential['forthSpeed'] === 'true')
                    ): ?>
                    <h3>Speed Bonuses</h3>
                    <div class="speed-bonuses">
                        <?php if(isset($potential['firstSpeed']) && $potential['firstSpeed'] === 'true'): ?>
                        <span class="speed-badge">First Speed</span>
                        <?php endif; ?>
                        <?php if(isset($potential['secondSpeed']) && $potential['secondSpeed'] === 'true'): ?>
                        <span class="speed-badge">Second Speed</span>
                        <?php endif; ?>
                        <?php if(isset($potential['thirdSpeed']) && $potential['thirdSpeed'] === 'true'): ?>
                        <span class="speed-badge">Third Speed</span>
                        <?php endif; ?>
                        <?php if(isset($potential['forthSpeed']) && $potential['forthSpeed'] === 'true'): ?>
                        <span class="speed-badge">Fourth Speed</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div>
                    <h3>Stat Bonuses</h3>
                    <table class="detail-table">
                        <?php
                        // Basic Stats
                        $statFields = [
                            'ac_bonus' => 'AC Bonus',
                            'str' => 'STR',
                            'con' => 'CON',
                            'dex' => 'DEX',
                            'int' => 'INT',
                            'wis' => 'WIS',
                            'cha' => 'CHA',
                            'allStatus' => 'All Stats',
                            'hp' => 'HP',
                            'mp' => 'MP',
                            'hpr' => 'HP Regen',
                            'mpr' => 'MP Regen',
                            'sp' => 'SP',
                            'mr' => 'Magic Resistance',
                            'expBonus' => 'EXP Bonus',
                            'carryBonus' => 'Weight Bonus'
                        ];
                        
                        foreach($statFields as $field => $label):
                            if(isset($potential[$field]) && $potential[$field] != 0):
                        ?>
                            <tr>
                                <th><?= $label ?></th>
                                <td><?= $potential[$field] > 0 ? '+' . $potential[$field] : $potential[$field] ?></td>
                            </tr>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </table>
                    
                    <?php 
                    // Check if any combat bonuses exist
                    $hasCombatBonuses = false;
                    $combatFields = ['shortDamage', 'shortHit', 'shortCritical', 'longDamage', 'longHit', 'longCritical', 
                                    'spellpower', 'magicHit', 'magicCritical'];
                    
                    foreach($combatFields as $field) {
                        if(isset($potential[$field]) && $potential[$field] != 0) {
                            $hasCombatBonuses = true;
                            break;
                        }
                    }
                    
                    if($hasCombatBonuses):
                    ?>
                    <h3>Combat Bonuses</h3>
                    <table class="detail-table">
                        <?php
                        $combatLabels = [
                            'shortDamage' => 'Melee Damage',
                            'shortHit' => 'Melee Hit',
                            'shortCritical' => 'Melee Critical',
                            'longDamage' => 'Ranged Damage',
                            'longHit' => 'Ranged Hit',
                            'longCritical' => 'Ranged Critical',
                            'spellpower' => 'Spell Power',
                            'magicHit' => 'Magic Hit',
                            'magicCritical' => 'Magic Critical'
                        ];
                        
                        foreach($combatLabels as $field => $label):
                            if(isset($potential[$field]) && $potential[$field] != 0):
                        ?>
                            <tr>
                                <th><?= $label ?></th>
                                <td><?= $potential[$field] > 0 ? '+' . $potential[$field] : $potential[$field] ?></td>
                            </tr>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </table>
                    <?php endif; ?>
                    
                    <?php 
                    // Check if any damage reduction bonuses exist
                    $hasReductionBonuses = false;
                    $reductionFields = ['reduction', 'reductionEgnor', 'reductionMagic', 'reductionPercent',
                                      'PVPDamage', 'PVPReduction', 'PVPReductionEgnor', 'PVPReductionMagic', 'PVPReductionMagicEgnor'];
                    
                    foreach($reductionFields as $field) {
                        if(isset($potential[$field]) && $potential[$field] != 0) {
                            $hasReductionBonuses = true;
                            break;
                        }
                    }
                    
                    if($hasReductionBonuses):
                    ?>
                    <h3>Damage Reduction</h3>
                    <table class="detail-table">
                        <?php
                        $reductionLabels = [
                            'reduction' => 'Damage Reduction',
                            'reductionEgnor' => 'Reduction Ignore',
                            'reductionMagic' => 'Magic Reduction',
                            'reductionPercent' => 'Reduction %',
                            'PVPDamage' => 'PVP Damage',
                            'PVPReduction' => 'PVP Reduction',
                            'PVPReductionEgnor' => 'PVP Reduction Ignore',
                            'PVPReductionMagic' => 'PVP Magic Reduction',
                            'PVPReductionMagicEgnor' => 'PVP Magic Reduction Ignore'
                        ];
                        
                        foreach($reductionLabels as $field => $label):
                            if(isset($potential[$field]) && $potential[$field] != 0):
                        ?>
                            <tr>
                                <th><?= $label ?></th>
                                <td><?= $potential[$field] > 0 ? '+' . $potential[$field] : $potential[$field] ?></td>
                            </tr>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </table>
                    <?php endif; ?>
                    
                    <?php 
                    // Check if any attribute bonuses exist
                    $hasAttributeBonuses = false;
                    $attrFields = ['attrFire', 'attrWater', 'attrWind', 'attrEarth', 'attrAll'];
                    
                    foreach($attrFields as $field) {
                        if(isset($potential[$field]) && $potential[$field] != 0) {
                            $hasAttributeBonuses = true;
                            break;
                        }
                    }
                    
                    if($hasAttributeBonuses):
                    ?>
                    <h3>Elemental Attributes</h3>
                    <table class="detail-table">
                        <?php
                        $attrLabels = [
                            'attrFire' => 'Fire',
                            'attrWater' => 'Water',
                            'attrWind' => 'Wind',
                            'attrEarth' => 'Earth',
                            'attrAll' => 'All Elements'
                        ];
                        
                        foreach($attrLabels as $field => $label):
                            if(isset($potential[$field]) && $potential[$field] != 0):
                        ?>
                            <tr>
                                <th><?= $label ?></th>
                                <td><?= $potential[$field] > 0 ? '+' . $potential[$field] : $potential[$field] ?></td>
                            </tr>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </table>
                    <?php endif; ?>
                    
                    <?php 
                    // Check if any resistance/tolerance bonuses exist
                    $hasToleranceBonuses = false;
                    $toleranceFields = ['toleranceSkill', 'toleranceSpirit', 'toleranceDragon', 'toleranceFear', 'toleranceAll',
                                       'hitupSkill', 'hitupSpirit', 'hitupDragon', 'hitupFear', 'hitupAll'];
                    
                    foreach($toleranceFields as $field) {
                        if(isset($potential[$field]) && $potential[$field] != 0) {
                            $hasToleranceBonuses = true;
                            break;
                        }
                    }
                    
                    if($hasToleranceBonuses):
                    ?>
                    <h3>Resistances & Hit Rates</h3>
                    <table class="detail-table">
                        <?php
                        $toleranceLabels = [
                            'toleranceSkill' => 'Skill Resistance',
                            'toleranceSpirit' => 'Spirit Resistance',
                            'toleranceDragon' => 'Dragon Resistance',
                            'toleranceFear' => 'Fear Resistance',
                            'toleranceAll' => 'All Resistances',
                            'hitupSkill' => 'Skill Hit',
                            'hitupSpirit' => 'Spirit Hit',
                            'hitupDragon' => 'Dragon Hit',
                            'hitupFear' => 'Fear Hit',
                            'hitupAll' => 'All Hit'
                        ];
                        
                        foreach($toleranceLabels as $field => $label):
                            if(isset($potential[$field]) && $potential[$field] != 0):
                        ?>
                            <tr>
                                <th><?= $label ?></th>
                                <td><?= $potential[$field] > 0 ? '+' . $potential[$field] : $potential[$field] ?></td>
                            </tr>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </table>
                    <?php endif; ?>
                    
                    <?php 
                    // Check if any other misc bonuses exist
                    $hasMiscBonuses = false;
                    $miscFields = ['hpStill', 'mpStill', 'stillChance', 'hprAbsol', 'mprAbsol', 'imunEgnor', 'strangeTimeIncrease',
                                  'dg', 'er', 'me'];
                    
                    foreach($miscFields as $field) {
                        if(isset($potential[$field]) && $potential[$field] != 0) {
                            $hasMiscBonuses = true;
                            break;
                        }
                    }
                    
                    if($hasMiscBonuses):
                    ?>
                    <h3>Misc Bonuses</h3>
                    <table class="detail-table">
                        <?php
                        $miscLabels = [
                            'hpStill' => 'HP Steal',
                            'mpStill' => 'MP Steal',
                            'stillChance' => 'Steal Chance',
                            'hprAbsol' => 'Absolute HP Regen',
                            'mprAbsol' => 'Absolute MP Regen',
                            'imunEgnor' => 'Immunity Ignore',
                            'strangeTimeIncrease' => 'Status Time Increase',
                            'dg' => 'DG',
                            'er' => 'ER',
                            'me' => 'ME'
                        ];
                        
                        foreach($miscLabels as $field => $label):
                            if(isset($potential[$field]) && $potential[$field] != 0):
                        ?>
                            <tr>
                                <th><?= $label ?></th>
                                <td><?= $potential[$field] > 0 ? '+' . $potential[$field] : $potential[$field] ?></td>
                            </tr>
                        <?php 
                            endif;
                        endforeach;
                        ?>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Drop Locations -->
    <?php if(!empty($drops)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Drop Locations</h2>
        </div>
        <div class="card-content">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Monster</th>
                        <th>Level</th>
                        <th>Drop Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($drops as $drop): ?>
                    <tr>
                        <td>
                            <div class="monster-list-item">
                                <img src="<?= SITE_URL ?>/assets/img/monsters/ms<?= $drop['monster_sprite_id'] ?>.png" 
                                     alt="<?= htmlspecialchars($drop['monster_name']) ?>"
                                     class="monster-sprite"
                                     onerror="this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
                                <a href="<?= SITE_URL ?>/pages/monsters/detail.php?id=<?= $drop['npcid'] ?>">
                                    <?= htmlspecialchars($drop['monster_name']) ?>
                                </a>
                            </div>
                        </td>
                        <td><?= $drop['monster_level'] ?></td>
                        <td><?= formatDropChance($drop['chance']) ?></td>
                        <td><?= $drop['max'] > 1 ? "1-{$drop['max']}" : "1" ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
/* Additional styling for the detail page */
.detail-image-large {
    max-width: 200px;
    max-height: 200px;
    object-fit: contain;
}

.detail-content-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

@media (max-width: 768px) {
    .detail-content-grid {
        grid-template-columns: 1fr;
    }
}

.monster-list-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.monster-sprite {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.potential-name {
    margin-top: 8px;
    font-size: 1rem;
    color: var(--accent);
    font-style: italic;
}

.speed-bonuses {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 10px;
}

.speed-badge {
    display: inline-block;
    background-color: #16c9b0;
    color: white;
    border-radius: 4px;
    padding: 5px 10px;
    font-size: 0.85rem;
    font-weight: 500;
}

.card-header {
    position: relative;
}

h3 {
    margin-top: 15px;
    margin-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 5px;
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';
?>