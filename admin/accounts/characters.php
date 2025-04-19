<?php
/**
 * Admin Account Characters List
 */

// Set page title
$pageTitle = 'Account Characters';

// Include admin header
require_once('../../includes/admin-header.php');

// Get database instance
$db = Database::getInstance();

// Get account name from URL
$accountName = isset($_GET['account']) ? $_GET['account'] : '';

// Redirect if no account provided
if (empty($accountName)) {
    header('Location: index.php');
    exit;
}

// Get account details
$account = $db->getRow("SELECT * FROM accounts WHERE login = ?", [$accountName]);

// If account doesn't exist, redirect
if (!$account) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => 'Account not found.'
    ];
    header('Location: index.php');
    exit;
}

// Get all characters for this account
$query = "SELECT c.*, 
            ca.item_name as armor_name,
            cw.item_name as weapon_name
          FROM characters c 
          LEFT JOIN character_items ca ON c.objid = ca.char_id AND ca.is_equipped = 1 AND ca.item_id IN (SELECT item_id FROM armor)
          LEFT JOIN character_items cw ON c.objid = cw.char_id AND cw.is_equipped = 1 AND cw.item_id IN (SELECT item_id FROM weapon)
          WHERE c.account_name = ?
          GROUP BY c.objid
          ORDER BY c.level DESC, c.char_name ASC";

$characters = $db->getRows($query, [$accountName]);

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
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Account Characters</h1>
                <p class="admin-hero-subtitle">
                    Account: <strong><?= htmlspecialchars($accountName) ?></strong> | 
                    Total Characters: <strong><?= count($characters) ?></strong>
                </p>
                
                <div class="account-status mt-3">
                    <?php if ($account['banned']): ?>
                        <span class="badge badge-danger">Account Banned</span>
                    <?php else: ?>
                        <span class="badge badge-success">Account Active</span>
                    <?php endif; ?>
                    
                    <?php if ($account['access_level'] > 0): ?>
                        <span class="badge badge-primary">Admin Access</span>
                    <?php endif; ?>
                    
                    <?php if ($account['lastactive']): ?>
                        <span class="badge badge-info">Last Active: <?= formatDate($account['lastactive'], 'M j, Y g:i A') ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="mt-3">
                    <a href="index.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Back to Accounts
                    </a>
                    <a href="edit-account.php?id=<?= urlencode($accountName) ?>" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit Account
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-<?= $_SESSION['admin_message']['type'] ?>">
            <?= $_SESSION['admin_message']['message'] ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>
    
    <?php if (empty($characters)): ?>
        <div class="alert alert-info">
            <p>This account has no characters.</p>
        </div>
    <?php else: ?>
        <!-- Character Cards Grid -->
        <div class="character-cards-grid">
            <?php foreach ($characters as $character): ?>
                <div class="character-card">
                    <div class="character-card-header">
                        <h3 class="character-name"><?= htmlspecialchars($character['char_name']) ?></h3>
                        <span class="character-level">Lv. <?= $character['level'] ?></span>
                    </div>
                    <div class="character-card-body">
                        <div class="character-avatar">
                            <img src="<?= SITE_URL ?>/assets/img/classes/<?= $character['Class'] ?>.png" 
                                 alt="<?= htmlspecialchars($classNames[$character['Class']] ?? 'Unknown Class') ?>"
                                 onerror="this.src='<?= SITE_URL ?>/assets/img/classes/default.png'">
                        </div>
                        <div class="character-details">
                            <div class="character-detail">
                                <span class="detail-label">Class:</span>
                                <span class="detail-value"><?= htmlspecialchars($classNames[$character['Class']] ?? 'Unknown Class') ?></span>
                            </div>
                            <div class="character-detail">
                                <span class="detail-label">HP/MP:</span>
                                <span class="detail-value"><?= $character['CurHp'] ?>/<?= $character['MaxHp'] ?> - <?= $character['CurMp'] ?>/<?= $character['MaxMp'] ?></span>
                            </div>
                            <div class="character-detail">
                                <span class="detail-label">Status:</span>
                                <span class="detail-value">
                                    <?php if ($character['OnlineStatus'] == 1): ?>
                                        <span class="badge badge-success">Online</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Offline</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="character-detail">
                                <span class="detail-label">Clan:</span>
                                <span class="detail-value">
                                    <?php if ($character['ClanID'] > 0): ?>
                                        <?= htmlspecialchars($character['Clanname']) ?>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="character-detail">
                                <span class="detail-label">Karma:</span>
                                <span class="detail-value">
                                    <?php if ($character['Karma'] < 0): ?>
                                        <span class="text-danger"><?= $character['Karma'] ?></span>
                                    <?php elseif ($character['Karma'] > 0): ?>
                                        <span class="text-success"><?= $character['Karma'] ?></span>
                                    <?php else: ?>
                                        <?= $character['Karma'] ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <div class="character-detail">
                                <span class="detail-label">Location:</span>
                                <span class="detail-value">
                                    <?php 
                                    $location = $db->getRow("SELECT locationname FROM mapids WHERE mapid = ?", [$character['MapID']]);
                                    echo $location ? htmlspecialchars($location['locationname']) : "Map " . $character['MapID'];
                                    ?>
                                </span>
                            </div>
                            <div class="character-detail">
                                <span class="detail-label">Equipment:</span>
                                <span class="detail-value">
                                    <?php if (!empty($character['weapon_name'])): ?>
                                        <?= htmlspecialchars($character['weapon_name']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($character['weapon_name']) && !empty($character['armor_name'])): ?>
                                        / 
                                    <?php endif; ?>
                                    <?php if (!empty($character['armor_name'])): ?>
                                        <?= htmlspecialchars($character['armor_name']) ?>
                                    <?php endif; ?>
                                    <?php if (empty($character['weapon_name']) && empty($character['armor_name'])): ?>
                                        None
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="character-card-footer">
                        <a href="character-detail.php?id=<?= $character['objid'] ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-user-circle"></i> View Details
                        </a>
                        <a href="character-inventory.php?id=<?= $character['objid'] ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-box-open"></i> Inventory
                        </a>
                        <a href="character-skills.php?id=<?= $character['objid'] ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-magic"></i> Skills
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Character Cards Grid */
.character-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.character-card {
    background-color: var(--primary);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.character-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
}

.character-card-header {
    background-color: var(--secondary);
    padding: 15px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.character-name {
    margin: 0;
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--text);
}

.character-level {
    background-color: var(--accent);
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 600;
}

.character-card-body {
    padding: 20px;
    display: flex;
    gap: 20px;
}

.character-avatar {
    width: 80px;
    height: 80px;
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

.character-details {
    flex: 1;
}

.character-detail {
    margin-bottom: 8px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    font-size: 0.95rem;
}

.detail-label {
    font-weight: 600;
    color: var(--text);
    opacity: 0.8;
}

.detail-value {
    color: var(--text);
}

.character-card-footer {
    padding: 15px;
    border-top: 1px solid var(--border-color);
    display: flex;
    gap: 10px;
    justify-content: center;
    background-color: var(--secondary);
}

/* Account status badges */
.account-status {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

.account-status .badge {
    padding: 8px 12px;
    font-size: 0.9rem;
}

@media (max-width: 576px) {
    .character-cards-grid {
        grid-template-columns: 1fr;
    }
    
    .character-card-body {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .character-detail {
        justify-content: center;
    }
    
    .detail-label {
        margin-right: 8px;
    }
    
    .character-card-footer {
        flex-direction: column;
    }
}
</style>

<?php
// Include admin footer
require_once '../includes/admin-footer.php';
?>
