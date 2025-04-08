<?php
/**
 * Admin Weapons Management Page for L1J Database
 */

// Set page title
$pageTitle = 'Manage Weapons';

// Include admin header
require_once '../includes/admin-header.php';

// Check if user has admin access
if (!isAdmin()) {
    echo '<div class="admin-error">Access denied. You must be an administrator to view this page.</div>';
    require_once '../includes/admin-footer.php';
    exit;
}

// Get database instance
$db = Database::getInstance();

// Process actions
$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$message = '';
$messageType = '';

// Handle deletion
if (isset($_POST['delete']) && isset($_POST['item_id'])) {
    $itemId = intval($_POST['item_id']);
    $deleteQuery = "DELETE FROM weapon WHERE item_id = ?";
    $result = $db->execute($deleteQuery, [$itemId]);
    
    if ($result) {
        $message = "Weapon ID $itemId has been deleted successfully.";
        $messageType = 'success';
    } else {
        $message = "Error deleting weapon. It may have related records or dependencies.";
        $messageType = 'error';
    }
    $action = 'list'; // Return to list view after deletion
}

// Handle form submission for add/edit
if (isset($_POST['save_weapon'])) {
    $itemId = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $isUpdate = ($itemId > 0);
    
    // Collect form data
    $weaponData = [
        'desc_en' => $_POST['desc_en'],
        'type' => $_POST['type'],
        'material' => $_POST['material'],
        'dmg_small' => intval($_POST['dmg_small']),
        'dmg_large' => intval($_POST['dmg_large']),
        'weight' => intval($_POST['weight']),
        'iconId' => intval($_POST['iconId']),
        'safenchant' => intval($_POST['safenchant']),
        'itemGrade' => $_POST['itemGrade'],
        'hitmodifier' => intval($_POST['hitmodifier']),
        'dmgmodifier' => intval($_POST['dmgmodifier']),
        'min_lvl' => intval($_POST['min_lvl']),
        'max_lvl' => intval($_POST['max_lvl']),
        'use_royal' => isset($_POST['use_royal']) ? 1 : 0,
        'use_knight' => isset($_POST['use_knight']) ? 1 : 0,
        'use_mage' => isset($_POST['use_mage']) ? 1 : 0,
        'use_elf' => isset($_POST['use_elf']) ? 1 : 0,
        'use_darkelf' => isset($_POST['use_darkelf']) ? 1 : 0,
        'use_dragonknight' => isset($_POST['use_dragonknight']) ? 1 : 0,
        'use_illusionist' => isset($_POST['use_illusionist']) ? 1 : 0,
        'use_warrior' => isset($_POST['use_warrior']) ? 1 : 0,
        'use_fencer' => isset($_POST['use_fencer']) ? 1 : 0,
        'use_lancer' => isset($_POST['use_lancer']) ? 1 : 0,
        'add_str' => intval($_POST['add_str']),
        'add_con' => intval($_POST['add_con']),
        'add_dex' => intval($_POST['add_dex']),
        'add_int' => intval($_POST['add_int']),
        'add_wis' => intval($_POST['add_wis']),
        'add_cha' => intval($_POST['add_cha']),
        'add_hp' => intval($_POST['add_hp']),
        'add_mp' => intval($_POST['add_mp']),
        'add_hpr' => intval($_POST['add_hpr']),
        'add_mpr' => intval($_POST['add_mpr']),
        'add_sp' => intval($_POST['add_sp']),
        'double_dmg_chance' => intval($_POST['double_dmg_chance']),
        'haste_item' => intval($_POST['haste_item']),
        'note' => $_POST['note']
    ];
    
    if ($isUpdate) {
        // Update existing weapon
        $updateColumns = [];
        $updateParams = [];
        
        foreach ($weaponData as $column => $value) {
            $updateColumns[] = "$column = ?";
            $updateParams[] = $value;
        }
        
        // Add the item_id to the parameters
        $updateParams[] = $itemId;
        
        $updateQuery = "UPDATE weapon SET " . implode(", ", $updateColumns) . " WHERE item_id = ?";
        $result = $db->execute($updateQuery, $updateParams);
        
        if ($result) {
            $message = "Weapon updated successfully.";
            $messageType = 'success';
        } else {
            $message = "Error updating weapon.";
            $messageType = 'error';
        }
    } else {
        // Insert new weapon
        $insertColumns = array_keys($weaponData);
        $placeholders = array_fill(0, count($insertColumns), '?');
        
        // Generate a new item_id (get the max ID and add 1)
        $maxIdQuery = "SELECT MAX(item_id) FROM weapon";
        $maxId = $db->getColumn($maxIdQuery);
        $newItemId = intval($maxId) + 1;
        
        // Add item_id to the data
        array_unshift($insertColumns, 'item_id');
        array_unshift($placeholders, '?');
        array_unshift($weaponData, $newItemId);
        
        $insertQuery = "INSERT INTO weapon (" . implode(", ", $insertColumns) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $result = $db->execute($insertQuery, array_values($weaponData));
        
        if ($result) {
            $message = "Weapon added successfully.";
            $messageType = 'success';
            $action = 'edit'; // Switch to edit mode to show the new item
            $itemId = $newItemId;
        } else {
            $message = "Error adding weapon.";
            $messageType = 'error';
        }
    }
}

// Get data for edit form or listing
if ($action == 'edit' || $action == 'add') {
    if ($action == 'edit' && isset($_GET['id'])) {
        $itemId = intval($_GET['id']);
        $query = "SELECT * FROM weapon WHERE item_id = ?";
        $weapon = $db->getRow($query, [$itemId]);
        
        if (!$weapon) {
            $message = "Weapon not found.";
            $messageType = 'error';
            $action = 'list';
        }
    } else if ($action == 'add') {
        // Set default values for a new weapon
        $weapon = [
            'item_id' => 0,
            'desc_en' => '',
            'type' => 'SWORD',
            'material' => 'IRON',
            'dmg_small' => 0,
            'dmg_large' => 0,
            'weight' => 0,
            'iconId' => 0,
            'safenchant' => 0,
            'itemGrade' => 'NORMAL',
            'hitmodifier' => 0,
            'dmgmodifier' => 0,
            'min_lvl' => 0,
            'max_lvl' => 0,
            'use_royal' => 0,
            'use_knight' => 0,
            'use_mage' => 0,
            'use_elf' => 0,
            'use_darkelf' => 0,
            'use_dragonknight' => 0,
            'use_illusionist' => 0,
            'use_warrior' => 0,
            'use_fencer' => 0,
            'use_lancer' => 0,
            'add_str' => 0,
            'add_con' => 0,
            'add_dex' => 0,
            'add_int' => 0,
            'add_wis' => 0,
            'add_cha' => 0,
            'add_hp' => 0,
            'add_mp' => 0,
            'add_hpr' => 0,
            'add_mpr' => 0,
            'add_sp' => 0,
            'double_dmg_chance' => 0,
            'haste_item' => 0,
            'note' => ''
        ];
    }
} else if ($action == 'list') {
    // Pagination for list view
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $itemsPerPage = 20;
    $offset = ($page - 1) * $itemsPerPage;
    
    // Search functionality
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $whereClause = '';
    $params = [];
    
    if (!empty($search)) {
        $whereClause = " WHERE desc_en LIKE ? OR item_id = ?";
        $params[] = "%$search%";
        $params[] = intval($search);
    }
    
    // Get total count
    $countQuery = "SELECT COUNT(*) FROM weapon" . $whereClause;
    $totalItems = $db->getColumn($countQuery, $params);
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    // Get data for current page
    $query = "SELECT item_id, desc_en, type, material, dmg_small, dmg_large, safenchant, itemGrade 
              FROM weapon" . $whereClause . " 
              ORDER BY item_id 
              LIMIT $offset, $itemsPerPage";
    $weapons = $db->getResults($query, $params);
}
?>

<!-- Admin Weapons Management Page -->
<div class="admin-content">
    <?php if (!empty($message)): ?>
        <div class="admin-message admin-message-<?= $messageType ?>"><?= $message ?></div>
    <?php endif; ?>

    <?php if ($action == 'list'): ?>
        <!-- Weapons Listing -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title">Weapons List</h2>
                <div class="admin-card-actions">
                    <a href="?action=add" class="admin-btn admin-btn-primary">Add New Weapon</a>
                </div>
            </div>
            <div class="admin-card-body">
                <!-- Search Form -->
                <form action="" method="GET" class="admin-search-form">
                    <input type="hidden" name="action" value="list">
                    <div class="admin-search-group">
                        <input type="text" name="search" placeholder="Search by name or ID..." value="<?= htmlspecialchars($search) ?>">
                        <button type="submit" class="admin-btn">Search</button>
                        <?php if (!empty($search)): ?>
                            <a href="?action=list" class="admin-btn admin-btn-secondary">Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
                
                <!-- Weapons Table -->
                <div class="admin-table-container">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Material</th>
                                <th>Damage (S/L)</th>
                                <th>Safe Enchant</th>
                                <th>Grade</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($weapons)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">No weapons found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($weapons as $item): ?>
                                    <tr>
                                        <td><?= $item['item_id'] ?></td>
                                        <td><?= htmlspecialchars($item['desc_en']) ?></td>
                                        <td><?= htmlspecialchars($item['type']) ?></td>
                                        <td><?= htmlspecialchars($item['material']) ?></td>
                                        <td><?= $item['dmg_small'] ?>/<?= $item['dmg_large'] ?></td>
                                        <td>+<?= $item['safenchant'] ?></td>
                                        <td><?= $item['itemGrade'] ?></td>
                                        <td class="admin-table-actions">
                                            <a href="?action=edit&id=<?= $item['item_id'] ?>" class="admin-btn admin-btn-sm admin-btn-info" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="" method="POST" class="admin-delete-form" onsubmit="return confirm('Are you sure you want to delete this weapon?');">
                                                <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                                <button type="submit" name="delete" class="admin-btn admin-btn-sm admin-btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if($totalPages > 1): ?>
                    <div class="admin-pagination">
                        <div class="admin-pagination-info">
                            Showing <?= $offset + 1 ?> to <?= min($offset + $itemsPerPage, $totalItems) ?> of <?= $totalItems ?> weapons
                        </div>
                        <div class="admin-pagination-links">
                            <?php if($page > 1): ?>
                                <a href="?action=list&page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>" class="admin-pagination-link">«</a>
                            <?php endif; ?>
                            
                            <?php
                            $startPage = max(1, min($page - 2, $totalPages - 4));
                            $endPage = min($startPage + 4, $totalPages);
                            
                            for($i = $startPage; $i <= $endPage; $i++):
                                $isActive = $i === $page;
                            ?>
                                <a href="?action=list&page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                                   class="admin-pagination-link <?= $isActive ? 'active' : '' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>
                            
                            <?php if($page < $totalPages): ?>
                                <a href="?action=list&page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>" class="admin-pagination-link">»</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Add/Edit Form -->
        <div class="admin-card">
            <div class="admin-card-header">
                <h2 class="admin-card-title"><?= ($action == 'add') ? 'Add New Weapon' : 'Edit Weapon' ?></h2>
                <div class="admin-card-actions">
                    <a href="?action=list" class="admin-btn admin-btn-secondary">Back to List</a>
                </div>
            </div>
            <div class="admin-card-body">
                <form action="" method="POST" class="admin-form">
                    <input type="hidden" name="item_id" value="<?= $weapon['item_id'] ?>">
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="desc_en">Weapon Name</label>
                            <input type="text" id="desc_en" name="desc_en" value="<?= htmlspecialchars($weapon['desc_en']) ?>" required>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="itemGrade">Grade</label>
                            <select id="itemGrade" name="itemGrade">
                                <option value="NORMAL" <?= $weapon['itemGrade'] == 'NORMAL' ? 'selected' : '' ?>>Normal</option>
                                <option value="ADVANC" <?= $weapon['itemGrade'] == 'ADVANC' ? 'selected' : '' ?>>Advanced</option>
                                <option value="RARE" <?= $weapon['itemGrade'] == 'RARE' ? 'selected' : '' ?>>Rare</option>
                                <option value="HERO" <?= $weapon['itemGrade'] == 'HERO' ? 'selected' : '' ?>>Hero</option>
                                <option value="LEGEND" <?= $weapon['itemGrade'] == 'LEGEND' ? 'selected' : '' ?>>Legend</option>
                                <option value="MYTH" <?= $weapon['itemGrade'] == 'MYTH' ? 'selected' : '' ?>>Myth</option>
                                <option value="ONLY" <?= $weapon['itemGrade'] == 'ONLY' ? 'selected' : '' ?>>Only</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="type">Weapon Type</label>
                            <select id="type" name="type">
                                <option value="SWORD" <?= $weapon['type'] == 'SWORD' ? 'selected' : '' ?>>Sword</option>
                                <option value="DAGGER" <?= $weapon['type'] == 'DAGGER' ? 'selected' : '' ?>>Dagger</option>
                                <option value="TOHAND_SWORD" <?= $weapon['type'] == 'TOHAND_SWORD' ? 'selected' : '' ?>>Two-Hand Sword</option>
                                <option value="BOW" <?= $weapon['type'] == 'BOW' ? 'selected' : '' ?>>Bow</option>
                                <option value="SPEAR" <?= $weapon['type'] == 'SPEAR' ? 'selected' : '' ?>>Spear</option>
                                <option value="BLUNT" <?= $weapon['type'] == 'BLUNT' ? 'selected' : '' ?>>Blunt</option>
                                <option value="STAFF" <?= $weapon['type'] == 'STAFF' ? 'selected' : '' ?>>Staff</option>
                                <option value="CLAW" <?= $weapon['type'] == 'CLAW' ? 'selected' : '' ?>>Claw</option>
                                <option value="EDORYU" <?= $weapon['type'] == 'EDORYU' ? 'selected' : '' ?>>Edoryu</option>
                                <option value="GAUNTLET" <?= $weapon['type'] == 'GAUNTLET' ? 'selected' : '' ?>>Gauntlet</option>
                                <option value="CHAINSWORD" <?= $weapon['type'] == 'CHAINSWORD' ? 'selected' : '' ?>>Chain Sword</option>
                                <option value="KEYRINGK" <?= $weapon['type'] == 'KEYRINGK' ? 'selected' : '' ?>>Keyringk</option>
                            </select>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="material">Material</label>
                            <select id="material" name="material">
                                <option value="IRON(철)" <?= $weapon['material'] == 'IRON(철)' ? 'selected' : '' ?>>Iron</option>
                                <option value="WOOD(나무)" <?= $weapon['material'] == 'WOOD(나무)' ? 'selected' : '' ?>>Wood</option>
                                <option value="MITHRIL(미스릴)" <?= $weapon['material'] == 'MITHRIL(미스릴)' ? 'selected' : '' ?>>Mithril</option>
                                <option value="DRAGON_HIDE(용비늘)" <?= $weapon['material'] == 'DRAGON_HIDE(용비늘)' ? 'selected' : '' ?>>Dragon Hide</option>
                                <option value="ORIHARUKON(오리하루콘)" <?= $weapon['material'] == 'ORIHARUKON(오리하루콘)' ? 'selected' : '' ?>>Oriharukon</option>
                                <option value="DRANIUM(드라니움)" <?= $weapon['material'] == 'DRANIUM(드라니움)' ? 'selected' : '' ?>>Dranium</option>
                                <option value="BONE(뼈)" <?= $weapon['material'] == 'BONE(뼈)' ? 'selected' : '' ?>>Bone</option>
                                <option value="METAL(금속)" <?= $weapon['material'] == 'METAL(금속)' ? 'selected' : '' ?>>Metal</option>
                                <option value="COPPER(구리)" <?= $weapon['material'] == 'COPPER(구리)' ? 'selected' : '' ?>>Copper</option>
                                <option value="SILVER(은)" <?= $weapon['material'] == 'SILVER(은)' ? 'selected' : '' ?>>Silver</option>
                                <option value="GOLD(금)" <?= $weapon['material'] == 'GOLD(금)' ? 'selected' : '' ?>>Gold</option>
                                <option value="PLATINUM(백금)" <?= $weapon['material'] == 'PLATINUM(백금)' ? 'selected' : '' ?>>Platinum</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="dmg_small">Small Damage</label>
                            <input type="number" id="dmg_small" name="dmg_small" value="<?= $weapon['dmg_small'] ?>" required>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="dmg_large">Large Damage</label>
                            <input type="number" id="dmg_large" name="dmg_large" value="<?= $weapon['dmg_large'] ?>" required>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="hitmodifier">Hit Modifier</label>
                            <input type="number" id="hitmodifier" name="hitmodifier" value="<?= $weapon['hitmodifier'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="dmgmodifier">Damage Modifier</label>
                            <input type="number" id="dmgmodifier" name="dmgmodifier" value="<?= $weapon['dmgmodifier'] ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="iconId">Icon ID</label>
                            <input type="number" id="iconId" name="iconId" value="<?= $weapon['iconId'] ?>" required>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="weight">Weight</label>
                            <input type="number" id="weight" name="weight" value="<?= $weapon['weight'] ?>" required>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="safenchant">Safe Enchant</label>
                            <input type="number" id="safenchant" name="safenchant" value="<?= $weapon['safenchant'] ?>" required>
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="double_dmg_chance">Double Damage Chance (%)</label>
                            <input type="number" id="double_dmg_chance" name="double_dmg_chance" value="<?= $weapon['double_dmg_chance'] ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="min_lvl">Min Level</label>
                            <input type="number" id="min_lvl" name="min_lvl" value="<?= $weapon['min_lvl'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="max_lvl">Max Level</label>
                            <input type="number" id="max_lvl" name="max_lvl" value="<?= $weapon['max_lvl'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="haste_item">Haste</label>
                            <select id="haste_item" name="haste_item">
                                <option value="0" <?= $weapon['haste_item'] == 0 ? 'selected' : '' ?>>No</option>
                                <option value="1" <?= $weapon['haste_item'] == 1 ? 'selected' : '' ?>>Yes</option>
                            </select>
                        </div>
                    </div>
                    
                    <h3 class="admin-form-section-title">Class Restrictions</h3>
                    <div class="admin-form-row">
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_royal" name="use_royal" <?= $weapon['use_royal'] ? 'checked' : '' ?>>
                            <label for="use_royal">Royal</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_knight" name="use_knight" <?= $weapon['use_knight'] ? 'checked' : '' ?>>
                            <label for="use_knight">Knight</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_mage" name="use_mage" <?= $weapon['use_mage'] ? 'checked' : '' ?>>
                            <label for="use_mage">Mage</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_elf" name="use_elf" <?= $weapon['use_elf'] ? 'checked' : '' ?>>
                            <label for="use_elf">Elf</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_darkelf" name="use_darkelf" <?= $weapon['use_darkelf'] ? 'checked' : '' ?>>
                            <label for="use_darkelf">Dark Elf</label>
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_dragonknight" name="use_dragonknight" <?= $weapon['use_dragonknight'] ? 'checked' : '' ?>>
                            <label for="use_dragonknight">Dragon Knight</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_illusionist" name="use_illusionist" <?= $weapon['use_illusionist'] ? 'checked' : '' ?>>
                            <label for="use_illusionist">Illusionist</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_warrior" name="use_warrior" <?= $weapon['use_warrior'] ? 'checked' : '' ?>>
                            <label for="use_warrior">Warrior</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_fencer" name="use_fencer" <?= $weapon['use_fencer'] ? 'checked' : '' ?>>
                            <label for="use_fencer">Fencer</label>
                        </div>
                        
                        <div class="admin-form-check">
                            <input type="checkbox" id="use_lancer" name="use_lancer" <?= $weapon['use_lancer'] ? 'checked' : '' ?>>
                            <label for="use_lancer">Lancer</label>
                        </div>
                    </div>
                    
                    <h3 class="admin-form-section-title">Stat Bonuses</h3>
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="add_str">STR</label>
                            <input type="number" id="add_str" name="add_str" value="<?= $weapon['add_str'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_con">CON</label>
                            <input type="number" id="add_con" name="add_con" value="<?= $weapon['add_con'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_dex">DEX</label>
                            <input type="number" id="add_dex" name="add_dex" value="<?= $weapon['add_dex'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_int">INT</label>
                            <input type="number" id="add_int" name="add_int" value="<?= $weapon['add_int'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_wis">WIS</label>
                            <input type="number" id="add_wis" name="add_wis" value="<?= $weapon['add_wis'] ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="add_cha">CHA</label>
                            <input type="number" id="add_cha" name="add_cha" value="<?= $weapon['add_cha'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_hp">HP</label>
                            <input type="number" id="add_hp" name="add_hp" value="<?= $weapon['add_hp'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_mp">MP</label>
                            <input type="number" id="add_mp" name="add_mp" value="<?= $weapon['add_mp'] ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="add_hpr">HP Regen</label>
                            <input type="number" id="add_hpr" name="add_hpr" value="<?= $weapon['add_hpr'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_mpr">MP Regen</label>
                            <input type="number" id="add_mpr" name="add_mpr" value="<?= $weapon['add_mpr'] ?>">
                        </div>
                        
                        <div class="admin-form-group">
                            <label for="add_sp">SP</label>
                            <input type="number" id="add_sp" name="add_sp" value="<?= $weapon['add_sp'] ?>">
                        </div>
                    </div>
                    
                    <div class="admin-form-row">
                        <div class="admin-form-group">
                            <label for="note">Additional Notes</label>
                            <textarea id="note" name="note" rows="5"><?= htmlspecialchars($weapon['note']) ?></textarea>
                        </div>
                    </div>
                    
                    <div class="admin-form-actions">
                        <button type="submit" name="save_weapon" class="admin-btn admin-btn-primary">Save Weapon</button>
                        <a href="?action=list" class="admin-btn admin-btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
// Include admin footer
require_once '../includes/admin-footer.php';
?>