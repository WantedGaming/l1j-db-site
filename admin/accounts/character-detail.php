<?php
/**
 * Admin Character Detail View
 */

// Set page title
$pageTitle = 'Character Details';

// Include admin header
require_once('../../includes/admin-header.php');

// Get database instance
$db = Database::getInstance();

// Get character ID from URL
$characterId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no ID provided
if (!$characterId) {
    header('Location: index.php');
    exit;
}

// Get character details
$character = $db->getRow("SELECT c.*, a.login as account_login, a.banned as account_banned 
                          FROM characters c 
                          LEFT JOIN accounts a ON c.account_name = a.login
                          WHERE c.objid = ?", [$characterId]);

// If character not found, redirect
if (!$character) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => 'Character not found.'
    ];
    header('Location: index.php');
    exit;
}

// Get class names
$classNames = [
    0 => 'Royal',
    1 => 'Knight',
    2 => 'Elf',
    3 => 'Mage',
    4 => 'Dark Elf',
    5 => 'Dragon Knight',
    6 => 'Illusionist',
    7 => 'Warrior',
    8 => 'Fencer',
    9 => 'Lancer'
];

// Get gender names
$genderNames = [
    0 => 'Male',
    1 => 'Female'
];

// Get map location
$location = $db->getRow("SELECT locationname FROM mapids WHERE mapid = ?", [$character['MapID']]);

// Get character inventory count
$inventoryCount = $db->getColumn("SELECT COUNT(*) FROM character_items WHERE char_id = ?", [$characterId]);

// Get equipped items
$equippedItems = $db->getRows("SELECT ci.*, 
                                  COALESCE(a.desc_en, w.desc_en, e.desc_en) as item_name,
                                  COALESCE(a.itemGrade, w.itemGrade, NULL) as item_grade,
                                  COALESCE(a.iconId, w.iconId, e.iconId) as icon_id,
                                  CASE 
                                    WHEN a.item_id IS NOT NULL THEN 'armor'
                                    WHEN w.item_id IS NOT NULL THEN 'weapon'
                                    ELSE 'etc'
                                  END as item_type
                               FROM character_items ci
                               LEFT JOIN armor a ON ci.item_id = a.item_id
                               LEFT JOIN weapon w ON ci.item_id = w.item_id
                               LEFT JOIN etcitem e ON ci.item_id = e.item_id
                               WHERE ci.char_id = ? AND ci.is_equipped = 1
                               ORDER BY ci.item_id", [$characterId]);

// Get character skills
$activeSkills = $db->getRows("SELECT * FROM character_skills_active WHERE char_obj_id = ? ORDER BY skill_name", [$characterId]);
$passiveSkills = $db->getRows("SELECT * FROM character_skills_passive WHERE char_obj_id = ? ORDER BY passive_name", [$characterId]);

// Get character quests
$quests = $db->getRows("SELECT q.*, q_data.quest_name 
                       FROM character_quests q
                       LEFT JOIN quest_data q_data ON q.quest_id = q_data.quest_id WHERE q.char_id = ?
                       ORDER BY q.quest_id", [$characterId]);

// Get character stats for chart
$stats = [
    'STR' => $character['Str'],
    'DEX' => $character['Dex'],
    'CON' => $character['Con'],
    'WIS' => $character['Wis'],
    'INT' => $character['Intel'],
    'CHA' => $character['Cha']
];

// Get warehouse items count
$warehouseCount = $db->getColumn("SELECT COUNT(*) FROM character_warehouse WHERE account_name = ?", [$character['account_name']]);

// Get character buffs
$buffs = $db->getRows("SELECT cb.*, s.name as skill_name, s.skill_id
                      FROM character_buff cb
                      LEFT JOIN skills s ON cb.skill_id = s.skill_id
                      WHERE cb.char_obj_id = ?
                      ORDER BY cb.remaining_time DESC", [$characterId]);
?>

<div class="admin-container">
    <div class="admin-header-actions">
        <a href="characters.php?account=<?= urlencode($character['account_name']) ?>" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Account Characters
        </a>
        <div>
            <a href="character-inventory.php?id=<?= $characterId ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-box-open"></i> Inventory
            </a>
            <a href="character-skills.php?id=<?= $characterId ?>" class="btn btn-info btn-sm">
                <i class="fas fa-magic"></i> Skills
            </a>
            <a href="character-warehouse.php?id=<?= $characterId ?>" class="btn btn-success btn-sm">
                <i class="fas fa-warehouse"></i> Warehouse
            </a>
        </div>
    </div>
    
    <!-- Character Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 mx-auto text-center">
                    <h1 class="hero-title"><?= htmlspecialchars($character['char_name']) ?></h1>
                    <div class="character-meta">
                        <span class="badge badge-primary">Level <?= $character['level'] ?></span>
                        <span class="badge badge-info"><?= htmlspecialchars($classNames[$character['Class']] ?? 'Unknown Class') ?></span>
                        <span class="badge badge-secondary"><?= htmlspecialchars($genderNames[$character['gender']] ?? 'Unknown Gender') ?></span>
                        <?php if ($character['OnlineStatus'] == 1): ?>
                            <span class="badge badge-success">Online</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Offline</span>
                        <?php endif; ?>
                        <?php if ($character['ClanID'] > 0): ?>
                            <span class="badge badge-warning"><?= htmlspecialchars($character['Clanname']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="character-account-info mt-3">
                        <span>Account: <strong><?= htmlspecialchars($character['account_login']) ?></strong></span>
                        <?php if ($character['account_banned']): ?>
                            <span class="badge badge-danger">Account Banned</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Character Overview -->
    <div class="row equal-height-row">
        <!-- Character Stats & Info -->
        <div class="col-md-4">
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-user-circle me-2"></i> Character</h3>
                </div>
                <div class="acquisition-card-body p-0">
                    <div class="character-avatar-container text-center p-4">
                        <div class="character-avatar">
                            <img src="<?= SITE_URL ?>/assets/img/classes/<?= $character['Class'] ?>.png" 
                                 alt="<?= htmlspecialchars($classNames[$character['Class']] ?? 'Unknown Class') ?>"
                                 onerror="this.src='<?= SITE_URL ?>/assets/img/classes/default.png'">
                        </div>
                        <h3 class="mt-3"><?= htmlspecialchars($character['char_name']) ?></h3>
                        <div class="character-title">
                            <?php if (!empty($character['Title'])): ?>
                                "<?= htmlspecialchars($character['Title']) ?>"
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="character-stats-table">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>Class</th>
                                    <td><?= htmlspecialchars($classNames[$character['Class']] ?? 'Unknown') ?></td>
                                </tr>
                                <tr>
                                    <th>Level</th>
                                    <td><?= $character['level'] ?> (<?= $character['HighLevel'] ?> High)</td>
                                </tr>
                                <tr>
                                    <th>Experience</th>
                                    <td><?= number_format($character['Exp']) ?></td>
                                </tr>
                                <tr>
                                    <th>Alignment</th>
                                    <td>
                                        <?php if ($character['Alignment'] < 0): ?>
                                            <span class="text-danger"><?= $character['Alignment'] ?> (Chaotic)</span>
                                        <?php elseif ($character['Alignment'] > 0): ?>
                                            <span class="text-success"><?= $character['Alignment'] ?> (Lawful)</span>
                                        <?php else: ?>
                                            <?= $character['Alignment'] ?> (Neutral)
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Karma</th>
                                    <td><?= $character['Karma'] ?></td>
                                </tr>
                                <tr>
                                    <th>PK Count</th>
                                    <td><?= $character['PKcount'] ?></td>
                                </tr>
                                <tr>
                                    <th>Location</th>
                                    <td>
                                        <?= $location ? htmlspecialchars($location['locationname']) : "Map " . $character['MapID'] ?>
                                        (<?= $character['LocX'] ?>, <?= $character['LocY'] ?>)
                                    </td>
                                </tr>
                                <tr>
                                    <th>Last Login</th>
                                    <td><?= formatDate($character['lastLoginTime'], 'M j, Y g:i A') ?></td>
                                </tr>
                                <tr>
                                    <th>Last Logout</th>
                                    <td><?= formatDate($character['lastLogoutTime'], 'M j, Y g:i A') ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Character Attributes Chart -->
            <div class="acquisition-card mt-4">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-chart-radar me-2"></i> Attributes</h3>
                </div>
                <div class="acquisition-card-body">
                    <div class="character-attributes">
                        <canvas id="attributesChart" height="250"></canvas>
                    </div>
                    
                    <div class="stats-table mt-3">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Attribute</th>
                                    <th>Value</th>
                                    <th>Base</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Strength (STR)</td>
                                    <td><?= $character['Str'] ?></td>
                                    <td><?= $character['BaseStr'] ?></td>
                                </tr>
                                <tr>
                                    <td>Dexterity (DEX)</td>
                                    <td><?= $character['Dex'] ?></td>
                                    <td><?= $character['BaseDex'] ?></td>
                                </tr>
                                <tr>
                                    <td>Constitution (CON)</td>
                                    <td><?= $character['Con'] ?></td>
                                    <td><?= $character['BaseCon'] ?></td>
                                </tr>
                                <tr>
                                    <td>Wisdom (WIS)</td>
                                    <td><?= $character['Wis'] ?></td>
                                    <td><?= $character['BaseWis'] ?></td>
                                </tr>
                                <tr>
                                    <td>Intelligence (INT)</td>
                                    <td><?= $character['Intel'] ?></td>
                                    <td><?= $character['BaseIntel'] ?></td>
                                </tr>
                                <tr>
                                    <td>Charisma (CHA)</td>
                                    <td><?= $character['Cha'] ?></td>
                                    <td><?= $character['BaseCha'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Character Combat & Equipment -->
        <div class="col-md-4">
            <!-- Combat Stats -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-heartbeat me-2"></i> Combat Stats</h3>
                </div>
                <div class="acquisition-card-body p-0">
                    <!-- HP/MP Bars -->
                    <div class="status-bars p-3">
                        <div class="hp-bar mb-3">
                            <div class="bar-label">
                                <span>HP</span>
                                <span><?= $character['CurHp'] ?>/<?= $character['MaxHp'] ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-danger" style="width: <?= ($character['CurHp'] / max(1, $character['MaxHp'])) * 100 ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="mp-bar">
                            <div class="bar-label">
                                <span>MP</span>
                                <span><?= $character['CurMp'] ?>/<?= $character['MaxMp'] ?></span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-primary" style="width: <?= ($character['CurMp'] / max(1, $character['MaxMp'])) * 100 ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Other Combat Stats -->
                    <div class="character-stats-table">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th>AC</th>
                                    <td><?= $character['Ac'] ?></td>
                                </tr>
                                <tr>
                                    <th>Food</th>
                                    <td><?= $character['Food'] ?></td>
                                </tr>
                                <tr>
                                    <th>Bonus Status</th>
                                    <td><?= $character['BonusStatus'] ?></td>
                                </tr>
                                <tr>
                                    <th>Elixir Status</th>
                                    <td><?= $character['ElixirStatus'] ?></td>
                                </tr>
                                <tr>
                                    <th>Elf Attribute</th>
                                    <td><?= $character['ElfAttr'] ?></td>
                                </tr>
                                <tr>
                                    <th>EXP Penalty</th>
                                    <td><?= $character['ExpRes'] ?>%</td>
                                </tr>
                                <tr>
                                    <th>Fatigue</th>
                                    <td><?= $character['fatigue_point'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Equipped Items -->
            <div class="acquisition-card mt-4">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-tshirt me-2"></i> Equipment</h3>
                </div>
                <div class="acquisition-card-body">
                    <?php if (empty($equippedItems)): ?>
                        <div class="alert alert-info">
                            <p>No equipped items found.</p>
                        </div>
                    <?php else: ?>
                        <div class="equipment-list">
                            <?php foreach ($equippedItems as $item): ?>
                                <div class="equipment-item">
                                    <div class="equipment-icon">
                                        <img src="<?= SITE_URL ?>/assets/img/items/<?= $item['icon_id'] ?>.png" 
                                             alt="<?= htmlspecialchars($item['item_name']) ?>"
                                             onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                                    </div>
                                    <div class="equipment-details">
                                        <div class="equipment-name">
                                            <?php if ($item['enchantlvl'] > 0): ?>
                                                <span class="enchant">+<?= $item['enchantlvl'] ?></span>
                                            <?php endif; ?>
                                            <?= htmlspecialchars($item['item_name']) ?>
                                        </div>
                                        <div class="equipment-type">
                                            <?= ucfirst($item['item_type']) ?>
                                            <?php if (!empty($item['item_grade'])): ?>
                                                <span class="badge rarity-<?= strtolower($item['item_grade']) ?>"><?= $item['item_grade'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Character Skills & Quests -->
        <div class="col-md-4">
            <!-- Active Skills -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-fire-alt me-2"></i> Active Skills</h3>
                </div>
                <div class="acquisition-card-body">
                    <?php if (empty($activeSkills)): ?>
                        <div class="alert alert-info">
                            <p>No active skills found.</p>
                        </div>
                    <?php else: ?>
                        <div class="skills-list">
                            <?php foreach ($activeSkills as $skill): ?>
                                <div class="skill-item">
                                    <img src="<?= SITE_URL ?>/assets/img/skills/<?= $skill['skill_id'] ?>.png" 
                                         alt="<?= htmlspecialchars($skill['skill_name']) ?>"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/skills/default.png'">
                                    <span><?= htmlspecialchars($skill['skill_name']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-center mt-3">
                        <a href="character-skills.php?id=<?= $characterId ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-magic"></i> View All Skills
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Character Buffs -->
            <div class="acquisition-card mt-4">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-shield-alt me-2"></i> Active Buffs</h3>
                </div>
                <div class="acquisition-card-body">
                    <?php if (empty($buffs)): ?>
                        <div class="alert alert-info">
                            <p>No active buffs found.</p>
                        </div>
                    <?php else: ?>
                        <div class="buffs-list">
                            <?php foreach ($buffs as $buff): ?>
                                <div class="buff-item">
                                    <img src="<?= SITE_URL ?>/assets/img/skills/<?= $buff['skill_id'] ?>.png" 
                                         alt="<?= htmlspecialchars($buff['skill_name']) ?>"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/skills/default.png'">
                                    <div class="buff-details">
                                        <span class="buff-name"><?= htmlspecialchars($buff['skill_name'] ?? 'Unknown Buff') ?></span>
                                        <span class="buff-time">
                                            <?php 
                                            $minutes = floor($buff['remaining_time'] / 60);
                                            $seconds = $buff['remaining_time'] % 60;
                                            echo sprintf('%02d:%02d', $minutes, $seconds);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Character Quests -->
            <div class="acquisition-card mt-4">
                <div class="acquisition-card-header">
                    <h3><i class="fas fa-scroll me-2"></i> Quests</h3>
                </div>
                <div class="acquisition-card-body">
                    <?php if (empty($quests)): ?>
                        <div class="alert alert-info">
                            <p>No quests found.</p>
                        </div>
                    <?php else: ?>
                        <div class="quests-list">
                            <?php foreach ($quests as $quest): ?>
                                <div class="quest-item">
                                    <span class="quest-id">#<?= $quest['quest_id'] ?></span>
                                    <span class="quest-name"><?= htmlspecialchars($quest['quest_name'] ?? 'Unknown Quest') ?></span>
                                    <span class="quest-step">Step: <?= $quest['quest_step'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Access Links -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="quick-access-links">
                <a href="character-inventory.php?id=<?= $characterId ?>" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-box-open"></i>
                    </div>
                    <div class="quick-link-details">
                        <h4>Inventory</h4>
                        <span><?= $inventoryCount ?> items</span>
                    </div>
                </a>
                
                <a href="character-warehouse.php?id=<?= $characterId ?>" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div class="quick-link-details">
                        <h4>Warehouse</h4>
                        <span><?= $warehouseCount ?> items</span>
                    </div>
                </a>
                
                <a href="character-skills.php?id=<?= $characterId ?>" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div class="quick-link-details">
                        <h4>Skills</h4>
                        <span><?= count($activeSkills) ?> active, <?= count($passiveSkills) ?> passive</span>
                    </div>
                </a>
                
                <a href="character-quests.php?id=<?= $characterId ?>" class="quick-link-card">
                    <div class="quick-link-icon">
                        <i class="fas fa-scroll"></i>
                    </div>
                    <div class="quick-link-details">
                        <h4>Quests</h4>
                        <span><?= count($quests) ?> quests</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
/* Character Avatar */
.character-avatar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.character-avatar {
    width: 120px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    background-color: var(--secondary);
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: center;
}

.character-avatar img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.character-title {
    font-style: italic;
    opacity: 0.8;
    margin-top: 5px;
}

/* Status bars */
.status-bars {
    margin-bottom: 15px;
}

.bar-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-weight: 500;
}

.progress {
    height: 20px;
    background-color: var(--secondary);
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    border-radius: 4px;
}

/* Equipment List */
.equipment-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.equipment-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px;
    background-color: var(--secondary);
    border-radius: 8px;
    transition: all 0.2s ease;
}

.equipment-item:hover {
    transform: translateX(5px);
}

.equipment-icon {
    width: 48px;
    height: 48px;
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 4px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.equipment-icon img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.equipment-details {
    flex: 1;
}

.equipment-name {
    font-weight: 500;
}

.equipment-type {
    font-size: 0.9rem;
    opacity: 0.8;
}

.enchant {
    color: #00a8ff;
    font-weight: 600;
    margin-right: 5px;
}

/* Skills List */
.skills-list {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.skill-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 80px;
    text-align: center;
}

.skill-item img {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    margin-bottom: 5px;
    background-color: var(--secondary);
    padding: 4px;
}

.skill-item span {
    font-size: 0.9rem;
    line-height: 1.2;
}

/* Buffs List */
.buffs-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.buff-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    background-color: var(--secondary);
    border-radius: 4px;
}

.buff-item img {
    width: 32px;
    height: 32px;
    border-radius: 4px;
}

.buff-details {
    display: flex;
    flex-direction: column;
}

.buff-name {
    font-weight: 500;
}

.buff-time {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Quests List */
.quests-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quest-item {
    display: flex;
    align-items: center;
    padding: 8px;
    background-color: var(--secondary);
    border-radius: 4px;
}

.quest-id {
    background-color: var(--accent);
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    margin-right: 10px;
    font-size: 0.9rem;
}

.quest-name {
    flex: 1;
    font-weight: 500;
}

.quest-step {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-left: 10px;
}

/* Quick Access Links */
.quick-access-links {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.quick-link-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background-color: var(--primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text);
    transition: all 0.3s ease;
}

.quick-link-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    border-color: var(--accent);
    text-decoration: none;
    color: var(--text);
}

.quick-link-icon {
    width: 48px;
    height: 48px;
    background-color: var(--accent);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.quick-link-details h4 {
    margin: 0;
    font-size: 1.2rem;
}

.quick-link-details span {
    font-size: 0.9rem;
    opacity: 0.8;
}

/* Stats Table */
.character-stats-table table {
    margin: 0;
}

.character-stats-table th, 
.character-stats-table td {
    padding: 10px 15px;
    border-color: var(--border-color);
    background-color: transparent;
}

.character-stats-table th {
    width: 40%;
    font-weight: 600;
    background-color: rgba(0, 0, 0, 0.1);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .quick-access-links {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 576px) {
    .quick-access-links {
        grid-template-columns: 1fr;
    }
    
    .character-meta {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 5px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character Attributes Chart
    const ctx = document.getElementById('attributesChart').getContext('2d');
    const stats = <?= json_encode($stats) ?>;
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: Object.keys(stats),
            datasets: [{
                label: 'Attributes',
                data: Object.values(stats),
                backgroundColor: 'rgba(249, 75, 31, 0.2)',
                borderColor: 'rgba(249, 75, 31, 1)',
                pointBackgroundColor: 'rgba(249, 75, 31, 1)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgba(249, 75, 31, 1)'
            }]
        },
        options: {
            scales: {
                r: {
                    angleLines: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    pointLabels: {
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    ticks: {
                        backdropColor: 'transparent',
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    min: 0,
                    max: 35
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php
// Include admin footer
require_once '../includes/admin-footer.php';
?>
                       