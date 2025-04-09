<?php
/**
 * Admin - Edit Weapon
 */

// Set page title
$pageTitle = 'Edit Weapon';

// Include admin header
require_once '../../includes/admin-header.php';

// Include weapons functions
require_once '../../includes/weapons-functions.php';

// Get database instance
$db = Database::getInstance();

// Get weapon ID from URL
$weaponId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to weapons list
if($weaponId <= 0) {
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect weapon data from form
    $weapon = [
        'desc_en' => $_POST['desc_en'] ?? '',
        'type' => $_POST['type'] ?? '',
        'material' => $_POST['material'] ?? '',
        'weight' => intval($_POST['weight'] ?? 0),
        'dmg_small' => intval($_POST['dmg_small'] ?? 0),
        'dmg_large' => intval($_POST['dmg_large'] ?? 0),
        'safenchant' => intval($_POST['safenchant'] ?? 0),
        'hitmodifier' => intval($_POST['hitmodifier'] ?? 0),
        'dmgmodifier' => intval($_POST['dmgmodifier'] ?? 0),
        'double_dmg_chance' => intval($_POST['double_dmg_chance'] ?? 0),
        'min_lvl' => intval($_POST['min_lvl'] ?? 0),
        'max_lvl' => intval($_POST['max_lvl'] ?? 0),
        'bless' => isset($_POST['bless']) ? 1 : 0,
        'trade' => isset($_POST['trade']) ? 1 : 0,
        'haste_item' => isset($_POST['haste_item']) ? 1 : 0,
        'itemGrade' => $_POST['itemGrade'] ?? 'NORMAL',
        // Character class usage
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
        // Stats
        'add_str' => intval($_POST['add_str'] ?? 0),
        'add_con' => intval($_POST['add_con'] ?? 0),
        'add_dex' => intval($_POST['add_dex'] ?? 0),
        'add_int' => intval($_POST['add_int'] ?? 0),
        'add_wis' => intval($_POST['add_wis'] ?? 0),
        'add_cha' => intval($_POST['add_cha'] ?? 0),
        'add_hp' => intval($_POST['add_hp'] ?? 0),
        'add_mp' => intval($_POST['add_mp'] ?? 0),
        'add_hpr' => intval($_POST['add_hpr'] ?? 0),
        'add_mpr' => intval($_POST['add_mpr'] ?? 0),
        'add_sp' => intval($_POST['add_sp'] ?? 0),
        'iconId' => intval($_POST['iconId'] ?? 0),
        'canbedmg' => isset($_POST['canbedmg']) ? 1 : 0,
        'cant_delete' => isset($_POST['cant_delete']) ? 1 : 0,
        'cant_sell' => isset($_POST['cant_sell']) ? 1 : 0,
        'retrieve' => isset($_POST['retrieve']) ? 1 : 0,
        'specialretrieve' => isset($_POST['specialretrieve']) ? 1 : 0,
        'note' => $_POST['note'] ?? ''
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($weapon['desc_en'])) {
        $errors[] = "Weapon name is required";
    }
    
    if (empty($weapon['type'])) {
        $errors[] = "Weapon type is required";
    }
    
    // If no errors, update the weapon
    if (empty($errors)) {
        // Build the query
        $updateValues = [];
        foreach ($weapon as $field => $value) {
            $updateValues[] = "{$field} = ?";
        }
        
        $query = "UPDATE weapon SET " . implode(', ', $updateValues) . " WHERE item_id = ?";
        
        // Add the weapon ID to the parameters
        $params = array_values($weapon);
        $params[] = $weaponId;
        
        // Execute the query
        $result = $db->execute($query, $params);
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Weapon '{$weapon['desc_en']}' updated successfully."
            ];
            
            // Redirect to weapons list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to update weapon. Database error.";
        }
    }
}

// Get weapon details
$query = "SELECT * FROM weapon WHERE item_id = ?";
$weapon = $db->getRow($query, [$weaponId]);

// If weapon not found, show error and redirect
if(!$weapon) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => "Weapon not found."
    ];
    header("Location: index.php");
    exit;
}

// Get weapon types and materials for dropdown
$weaponTypes = [
    'SWORD' => 'Sword',
    'DAGGER' => 'Dagger',
    'TOHAND_SWORD' => 'Sword (2H)',
    'BOW' => 'Bow (2H)',
    'SPEAR' => 'Spear (2H)',
    'BLUNT' => 'Blunt',
    'STAFF' => 'Staff',
    'GAUNTLET' => 'Gauntlet',
    'CLAW' => 'Claw',
    'EDORYU' => 'Edoryu',
    'SINGLE_BOW' => 'Bow',
    'SINGLE_SPEAR' => 'Spear',
    'TOHAND_BLUNT' => 'Blunt (2H)',
    'TOHAND_STAFF' => 'Staff (2H)',
    'KEYRINGK' => 'Keyringk',
    'CHAINSWORD' => 'Chain Sword'
];

$materialTypes = [
    'IRON' => 'Iron',
    'WOOD' => 'Wood',
    'MITHRIL' => 'Mithril',
    'DRAGON_HIDE' => 'Dragon Hide',
    'ORIHARUKON' => 'Oriharukon',
    'DRANIUM' => 'Dranium',
    'SILVER' => 'Silver',
    'STEEL' => 'Steel',
    'CRYSTAL' => 'Crystal',
    'COPPER' => 'Copper',
    'GOLD' => 'Gold',
    'BONE' => 'Bone',
    'LEATHER' => 'Leather',
    'CLOTH' => 'Cloth',
    'LIQUID' => 'Liquid',
    'PAPER' => 'Paper',
    'STONE' => 'Stone'
];

$itemGrades = [
    'NORMAL' => 'Normal',
    'ADVANC' => 'Advanced',
    'RARE' => 'Rare',
    'HERO' => 'Hero',
    'LEGEND' => 'Legend',
    'MYTH' => 'Myth',
    'ONLY' => 'Only'
];

// Extract material name without parentheses if needed
$materialName = $weapon['material'];
if (strpos($materialName, '(') !== false) {
    $materialName = trim(substr($materialName, 0, strpos($materialName, '(')));
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Edit Weapon: <?= htmlspecialchars($weapon['desc_en']) ?></h1>
        <div class="admin-actions">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Weapons
            </a>
            <a href="<?= SITE_URL ?>/pages/items/weapon-detail.php?id=<?= $weaponId ?>" class="btn btn-primary" target="_blank">
                <i class="fas fa-eye"></i> View Weapon
            </a>
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
    
    <form method="POST" class="admin-form">
        <div class="form-tabs">
            <button type="button" class="form-tab active" data-tab="basic">Basic Info</button>
            <button type="button" class="form-tab" data-tab="classes">Class Restrictions</button>
            <button type="button" class="form-tab" data-tab="stats">Stats & Bonuses</button>
            <button type="button" class="form-tab" data-tab="properties">Properties</button>
            <button type="button" class="form-tab" data-tab="notes">Notes</button>
        </div>
        
        <div class="form-section active" id="basic-section">
            <h2>Basic Information</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="item_id">Item ID</label>
                    <input type="number" id="item_id" value="<?= $weapon['item_id'] ?>" disabled>
                    <small>ID cannot be changed.</small>
                </div>
                
                <div class="form-group">
                    <label for="iconId">Icon ID</label>
                    <input type="number" id="iconId" name="iconId" value="<?= $weapon['iconId'] ?>">
                    <small>ID used for item icon image.</small>
                </div>
            </div>
            
            <div class="form-group">
                <label for="desc_en">Weapon Name*</label>
                <input type="text" id="desc_en" name="desc_en" value="<?= htmlspecialchars($weapon['desc_en']) ?>" required>
                <small>The name of the weapon.</small>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="type">Weapon Type*</label>
                    <select id="type" name="type" required>
                        <option value="">Select Type</option>
                        <?php foreach ($weaponTypes as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $weapon['type'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="material">Material*</label>
                    <select id="material" name="material" required>
                        <option value="">Select Material</option>
                        <?php foreach ($materialTypes as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $materialName === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="itemGrade">Grade</label>
                    <select id="itemGrade" name="itemGrade">
                        <?php foreach ($itemGrades as $value => $label): ?>
                            <option value="<?= $value ?>" <?= $weapon['itemGrade'] === $value ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="dmg_small">Small Damage</label>
                    <input type="number" id="dmg_small" name="dmg_small" value="<?= $weapon['dmg_small'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="dmg_large">Large Damage</label>
                    <input type="number" id="dmg_large" name="dmg_large" value="<?= $weapon['dmg_large'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="weight">Weight</label>
                    <input type="number" id="weight" name="weight" value="<?= $weapon['weight'] ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="safenchant">Safe Enchant</label>
                    <input type="number" id="safenchant" name="safenchant" value="<?= $weapon['safenchant'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="hitmodifier">Hit Modifier</label>
                    <input type="number" id="hitmodifier" name="hitmodifier" value="<?= $weapon['hitmodifier'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="dmgmodifier">Damage Modifier</label>
                    <input type="number" id="dmgmodifier" name="dmgmodifier" value="<?= $weapon['dmgmodifier'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="double_dmg_chance">Double Damage Chance (%)</label>
                    <input type="number" id="double_dmg_chance" name="double_dmg_chance" value="<?= $weapon['double_dmg_chance'] ?>" min="0" max="100">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="min_lvl">Min Level</label>
                    <input type="number" id="min_lvl" name="min_lvl" value="<?= $weapon['min_lvl'] ?>" min="0">
                </div>
                
                <div class="form-group">
                    <label for="max_lvl">Max Level</label>
                    <input type="number" id="max_lvl" name="max_lvl" value="<?= $weapon['max_lvl'] ?>" min="0">
                </div>
            </div>
        </div>
        
        <div class="form-section" id="classes-section">
            <h2>Class Restrictions</h2>
            
            <div class="form-row checkbox-grid">
                <div class="form-check">
                    <input type="checkbox" id="use_royal" name="use_royal" <?= $weapon['use_royal'] ? 'checked' : '' ?>>
                    <label for="use_royal">Royal</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_knight" name="use_knight" <?= $weapon['use_knight'] ? 'checked' : '' ?>>
                    <label for="use_knight">Knight</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_elf" name="use_elf" <?= $weapon['use_elf'] ? 'checked' : '' ?>>
                    <label for="use_elf">Elf</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_mage" name="use_mage" <?= $weapon['use_mage'] ? 'checked' : '' ?>>
                    <label for="use_mage">Mage</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_darkelf" name="use_darkelf" <?= $weapon['use_darkelf'] ? 'checked' : '' ?>>
                    <label for="use_darkelf">Dark Elf</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_dragonknight" name="use_dragonknight" <?= $weapon['use_dragonknight'] ? 'checked' : '' ?>>
                    <label for="use_dragonknight">Dragon Knight</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_illusionist" name="use_illusionist" <?= $weapon['use_illusionist'] ? 'checked' : '' ?>>
                    <label for="use_illusionist">Illusionist</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_warrior" name="use_warrior" <?= $weapon['use_warrior'] ? 'checked' : '' ?>>
                    <label for="use_warrior">Warrior</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_fencer" name="use_fencer" <?= $weapon['use_fencer'] ? 'checked' : '' ?>>
                    <label for="use_fencer">Fencer</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="use_lancer" name="use_lancer" <?= $weapon['use_lancer'] ? 'checked' : '' ?>>
                    <label for="use_lancer">Lancer</label>
                </div>
            </div>
        </div>
        
        <div class="form-section" id="stats-section">
            <h2>Stats & Bonuses</h2>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="add_str">STR</label>
                    <input type="number" id="add_str" name="add_str" value="<?= $weapon['add_str'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_con">CON</label>
                    <input type="number" id="add_con" name="add_con" value="<?= $weapon['add_con'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_dex">DEX</label>
                    <input type="number" id="add_dex" name="add_dex" value="<?= $weapon['add_dex'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_int">INT</label>
                    <input type="number" id="add_int" name="add_int" value="<?= $weapon['add_int'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_wis">WIS</label>
                    <input type="number" id="add_wis" name="add_wis" value="<?= $weapon['add_wis'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_cha">CHA</label>
                    <input type="number" id="add_cha" name="add_cha" value="<?= $weapon['add_cha'] ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="add_hp">HP</label>
                    <input type="number" id="add_hp" name="add_hp" value="<?= $weapon['add_hp'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_mp">MP</label>
                    <input type="number" id="add_mp" name="add_mp" value="<?= $weapon['add_mp'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_hpr">HP Regen</label>
                    <input type="number" id="add_hpr" name="add_hpr" value="<?= $weapon['add_hpr'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_mpr">MP Regen</label>
                    <input type="number" id="add_mpr" name="add_mpr" value="<?= $weapon['add_mpr'] ?>">
                </div>
                
                <div class="form-group">
                    <label for="add_sp">SP</label>
                    <input type="number" id="add_sp" name="add_sp" value="<?= $weapon['add_sp'] ?>">
                </div>
            </div>
        </div>
        
        <div class="form-section" id="properties-section">
            <h2>Properties</h2>
            
            <div class="form-row checkbox-grid">
                <div class="form-check">
                    <input type="checkbox" id="bless" name="bless" <?= $weapon['bless'] ? 'checked' : '' ?>>
                    <label for="bless">Blessed</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="haste_item" name="haste_item" <?= $weapon['haste_item'] ? 'checked' : '' ?>>
                    <label for="haste_item">Haste</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="canbedmg" name="canbedmg" <?= $weapon['canbedmg'] ? 'checked' : '' ?>>
                    <label for="canbedmg">Can Be Damaged</label>
                </div>
            </div>
            
            <h3>Restrictions</h3>
            
            <div class="form-row checkbox-grid">
                <div class="form-check">
                    <input type="checkbox" id="trade" name="trade" <?= $weapon['trade'] ? 'checked' : '' ?>>
                    <label for="trade">Tradable</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="retrieve" name="retrieve" <?= $weapon['retrieve'] ? 'checked' : '' ?>>
                    <label for="retrieve">Retrievable</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="specialretrieve" name="specialretrieve" <?= $weapon['specialretrieve'] ? 'checked' : '' ?>>
                    <label for="specialretrieve">Special Retrieve</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="cant_delete" name="cant_delete" <?= $weapon['cant_delete'] ? 'checked' : '' ?>>
                    <label for="cant_delete">Cannot Delete</label>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="cant_sell" name="cant_sell" <?= $weapon['cant_sell'] ? 'checked' : '' ?>>
                    <label for="cant_sell">Cannot Sell</label>
                </div>
            </div>
        </div>
        
        <div class="form-section" id="notes-section">
            <h2>Additional Notes</h2>
            
            <div class="form-group">
                <label for="note">Notes</label>
                <textarea id="note" name="note" rows="5"><?= htmlspecialchars($weapon['note'] ?? '') ?></textarea>
                <small>Enter any additional information about this weapon.</small>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Update Weapon</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.form-tab');
    const sections = document.querySelectorAll('.form-section');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Hide all sections
            sections.forEach(section => section.classList.remove('active'));
            
            // Show the selected section
            const tabId = this.getAttribute('data-tab');
            document.getElementById(tabId + '-section').classList.add('active');
        });
    });
});
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>