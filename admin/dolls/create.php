<?php
/**
 * Admin - Create New Magic Doll
 */

// Set page title
$pageTitle = 'Add New Magic Doll';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // First, collect etcitem data
    $etcitem = [
        'item_id' => intval($_POST['item_id'] ?? 0),
        'desc_en' => $_POST['name'] ?? '',
        'iconId' => intval($_POST['icon_id'] ?? 0),
        'use_type' => 'MAGICDOLL',
        'material' => $_POST['material'] ?? null,
        'weight' => intval($_POST['weight'] ?? 0),
        'itemGrade' => $_POST['itemGrade'] ?? 'NORMAL'
    ];
    
    // Then, collect magicdoll_info data
    $magicDollInfo = [
        'itemId' => intval($_POST['item_id'] ?? 0),
        'dollNpcId' => intval($_POST['dollNpcId'] ?? 0),
        'grade' => intval($_POST['grade'] ?? 0),
        'hp' => intval($_POST['hp'] ?? 0),
        'mp' => intval($_POST['mp'] ?? 0),
        'ac' => intval($_POST['ac'] ?? 0),
        'str' => intval($_POST['str'] ?? 0),
        'con' => intval($_POST['con'] ?? 0),
        'dex' => intval($_POST['dex'] ?? 0),
        'intel' => intval($_POST['int'] ?? 0),  // Map int form field to intel database field
        'wis' => intval($_POST['wis'] ?? 0),
        'cha' => intval($_POST['cha'] ?? 0),
        'haste' => isset($_POST['haste']) ? 'true' : 'false',
        'bonusItemId' => intval($_POST['bonusItemId'] ?? 0),
        'bonusCount' => intval($_POST['bonusCount'] ?? 0),
        'bonusInterval' => intval($_POST['bonusInterval'] ?? 0),
        'hitXp' => intval($_POST['hitXp'] ?? 0),
        'dmgXp' => intval($_POST['dmgXp'] ?? 0),
        'damageChance' => intval($_POST['damageChance'] ?? 0),
        'blessItemId' => intval($_POST['blessItemId'] ?? 0),
        'mr' => intval($_POST['mr'] ?? 0)
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($etcitem['desc_en'])) {
        $errors[] = "Doll name is required";
    }
    
    if (empty($etcitem['item_id'])) {
        $errors[] = "Item ID is required";
    }
    
    // Check if item ID already exists in etcitem table
    $existingItem = $db->getRow("SELECT item_id FROM etcitem WHERE item_id = ?", [$etcitem['item_id']]);
    if ($existingItem) {
        $errors[] = "An item with ID {$etcitem['item_id']} already exists";
    }
    
    // Check if item ID already exists in magicdoll_info table
    $existingDoll = $db->getRow("SELECT itemId FROM magicdoll_info WHERE itemId = ?", [$magicDollInfo['itemId']]);
    if ($existingDoll) {
        $errors[] = "A magic doll with Item ID {$magicDollInfo['itemId']} already exists";
    }
    
    // Handle image upload
    $hasImageUpload = isset($_FILES['doll_image']) && $_FILES['doll_image']['error'] !== UPLOAD_ERR_NO_FILE;
    $uploadSuccess = false;
    $targetFileName = '';
    
    if ($hasImageUpload) {
        $targetDir = "../../assets/img/items/";
        $targetFileName = $etcitem['iconId'] . ".png"; // Using iconId for consistency
        $targetFilePath = $targetDir . $targetFileName;
        $fileType = strtolower(pathinfo($_FILES["doll_image"]["name"], PATHINFO_EXTENSION));
        
        // Validate file type
        $allowedTypes = array("jpg", "jpeg", "png", "gif");
        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Only JPG, JPEG, PNG & GIF files are allowed.";
        }
        
        // Check file size (max 5MB)
        if ($_FILES["doll_image"]["size"] > 5000000) {
            $errors[] = "File is too large. Maximum file size is 5MB.";
        }
        
        // If no errors, try to upload the file
        if (empty($errors)) {
            if (move_uploaded_file($_FILES["doll_image"]["tmp_name"], $targetFilePath)) {
                $uploadSuccess = true;
            } else {
                $errors[] = "Failed to upload image. Please check file permissions.";
            }
        }
    }
    
    // If no errors, insert the data
    if (empty($errors)) {
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Insert into etcitem table
            $etcItemFields = implode(', ', array_keys($etcitem));
            $etcItemPlaceholders = implode(', ', array_fill(0, count($etcitem), '?'));
            
            $etcItemQuery = "INSERT INTO etcitem ({$etcItemFields}) VALUES ({$etcItemPlaceholders})";
            
            // Execute the query
            $result1 = $db->execute($etcItemQuery, array_values($etcitem));
            
            if (!$result1) {
                throw new Exception("Failed to insert into etcitem table");
            }
            
            // Insert into magicdoll_info table
            $magicDollFields = implode(', ', array_keys($magicDollInfo));
            $magicDollPlaceholders = implode(', ', array_fill(0, count($magicDollInfo), '?'));
            
            $magicDollQuery = "INSERT INTO magicdoll_info ({$magicDollFields}) VALUES ({$magicDollPlaceholders})";
            
            // Execute the query
            $result2 = $db->execute($magicDollQuery, array_values($magicDollInfo));
            
            if (!$result2) {
                throw new Exception("Failed to insert into magicdoll_info table");
            }
            
            // Commit the transaction
            $db->commit();
            
            // Set success message
            $successMessage = "Magic Doll '{$etcitem['desc_en']}' created successfully.";
            if ($uploadSuccess) {
                $successMessage .= " Image uploaded successfully.";
            }
            
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => $successMessage
            ];
            
            // Redirect to dolls list
            header("Location: index.php");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction
            $db->rollback();
            $errors[] = "Database error: " . $e->getMessage();
            
            // Remove uploaded image if there was a database error
            if ($uploadSuccess && file_exists($targetFilePath)) {
                unlink($targetFilePath);
            }
        }
    }
}

// Generate next available item id
$nextId = $db->getColumn("SELECT MAX(item_id) + 1 FROM etcitem") ?: 50000;

// Initialize default values
$doll = [
    'item_id' => $nextId,
    'name' => '',
    'icon_id' => 0,
    'material' => null,
    'weight' => 10,
    'itemGrade' => 'NORMAL',
    'dollNpcId' => 0,
    'grade' => 0,
    'hp' => 0,
    'mp' => 0,
    'ac' => 0,
    'str' => 0,
    'con' => 0,
    'dex' => 0,
    'int' => 0,
    'wis' => 0,
    'cha' => 0,
    'haste' => false,
    'bonusItemId' => 0,
    'bonusCount' => 0,
    'bonusInterval' => 0,
    'hitXp' => 0,
    'dmgXp' => 0,
    'damageChance' => 0,
    'blessItemId' => 0,
    'mr' => 0
];

// Get item grades for dropdown
$itemGrades = ['NORMAL', 'ADVANC', 'RARE', 'HERO', 'LEGEND', 'MYTH', 'ONLY'];

// Get NPCs for dropdown
$npcQuery = "SELECT npcid, desc_kr, lvl FROM npc WHERE impl LIKE '%L1Monster%' ORDER BY desc_kr ASC";
$npcs = $db->getRows($npcQuery);
?>

<div class="admin-container">
    <div class="admin-header-actions">
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dolls
        </a>
    </div>
    
    <div class="admin-content-card">
        <div class="admin-content-header">
            <h2>Add New Magic Doll</h2>
        </div>
        
        <!-- Display validation errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="create.php" class="admin-form" enctype="multipart/form-data">
            <div class="form-tabs">
                <button type="button" class="form-tab active" data-tab="basic-info">Basic Information</button>
                <button type="button" class="form-tab" data-tab="stats">Stats</button>
                <button type="button" class="form-tab" data-tab="bonuses">Bonuses & Effects</button>
                <button type="button" class="form-tab" data-tab="image">Image</button>
            </div>
            
            <!-- Basic Information Tab -->
            <div id="basic-info" class="form-section active">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="item_id" class="form-label">Item ID</label>
                        <input type="number" id="item_id" name="item_id" value="<?= $doll['item_id'] ?>" class="form-control" required>
                        <span class="form-text">Unique identifier for this doll (must be unique)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($doll['name']) ?>" class="form-control" required>
                        <span class="form-text">Name of the doll</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="icon_id" class="form-label">Icon ID</label>
                        <input type="number" id="icon_id" name="icon_id" value="<?= $doll['icon_id'] ?>" class="form-control" required>
                        <span class="form-text">Icon ID for display</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="weight" class="form-label">Weight</label>
                        <input type="number" id="weight" name="weight" value="<?= $doll['weight'] ?>" class="form-control">
                        <span class="form-text">Weight of the item (in multiples of 0.1)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="itemGrade" class="form-label">Item Grade</label>
                        <select id="itemGrade" name="itemGrade" class="form-control">
                            <?php foreach ($itemGrades as $grade): ?>
                                <option value="<?= $grade ?>" <?= ($doll['itemGrade'] === $grade) ? 'selected' : '' ?>><?= formatGrade($grade) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-text">Grade of the item</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="dollNpcId" class="form-label">Doll NPC</label>
                        <select id="dollNpcId" name="dollNpcId" class="form-control">
                            <option value="0">-- Select NPC --</option>
                            <?php foreach ($npcs as $npc): ?>
                                <option value="<?= $npc['npcid'] ?>">
                                    <?= htmlspecialchars($npc['desc_kr']) ?> (Lvl <?= $npc['lvl'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-text">NPC model for this doll</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="material" class="form-label">Material</label>
                        <input type="text" id="material" name="material" value="<?= htmlspecialchars($doll['material'] ?? '') ?>" class="form-control">
                        <span class="form-text">Material of the item</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="grade" class="form-label">Doll Grade</label>
                        <input type="number" id="grade" name="grade" value="<?= $doll['grade'] ?>" class="form-control" min="0" max="6">
                        <span class="form-text">Grade of the doll (0-6)</span>
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" id="haste" name="haste" class="form-check-input" <?= $doll['haste'] ? 'checked' : '' ?>>
                        <label for="haste" class="form-check-label">Has Haste Effect</label>
                        <span class="form-text">Check if the doll provides haste effect</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="blessItemId" class="form-label">Blessed Version Item ID</label>
                        <input type="number" id="blessItemId" name="blessItemId" value="<?= $doll['blessItemId'] ?>" class="form-control">
                        <span class="form-text">Item ID of the blessed version of this doll (if any)</span>
                    </div>
                </div>
            </div>
            
            <!-- Stats Tab -->
            <div id="stats" class="form-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="hp" class="form-label">HP</label>
                        <input type="number" id="hp" name="hp" value="<?= $doll['hp'] ?>" class="form-control">
                        <span class="form-text">Hit Points</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="mp" class="form-label">MP</label>
                        <input type="number" id="mp" name="mp" value="<?= $doll['mp'] ?>" class="form-control">
                        <span class="form-text">Magic Points</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="ac" class="form-label">AC</label>
                        <input type="number" id="ac" name="ac" value="<?= $doll['ac'] ?>" class="form-control">
                        <span class="form-text">Armor Class</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="str" class="form-label">STR</label>
                        <input type="number" id="str" name="str" value="<?= $doll['str'] ?>" class="form-control">
                        <span class="form-text">Strength</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="con" class="form-label">CON</label>
                        <input type="number" id="con" name="con" value="<?= $doll['con'] ?>" class="form-control">
                        <span class="form-text">Constitution</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="dex" class="form-label">DEX</label>
                        <input type="number" id="dex" name="dex" value="<?= $doll['dex'] ?>" class="form-control">
                        <span class="form-text">Dexterity</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="int" class="form-label">INT</label>
                        <input type="number" id="int" name="int" value="<?= $doll['int'] ?>" class="form-control">
                        <span class="form-text">Intelligence</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="wis" class="form-label">WIS</label>
                        <input type="number" id="wis" name="wis" value="<?= $doll['wis'] ?>" class="form-control">
                        <span class="form-text">Wisdom</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="cha" class="form-label">CHA</label>
                        <input type="number" id="cha" name="cha" value="<?= $doll['cha'] ?>" class="form-control">
                        <span class="form-text">Charisma</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="mr" class="form-label">MR</label>
                        <input type="number" id="mr" name="mr" value="<?= $doll['mr'] ?>" class="form-control">
                        <span class="form-text">Magic Resistance</span>
                    </div>
                </div>
            </div>
            
            <!-- Bonuses Tab -->
            <div id="bonuses" class="form-section">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="bonusItemId" class="form-label">Bonus Item ID</label>
                        <input type="number" id="bonusItemId" name="bonusItemId" value="<?= $doll['bonusItemId'] ?>" class="form-control">
                        <span class="form-text">Item ID for the bonus effect</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="bonusCount" class="form-label">Bonus Count</label>
                        <input type="number" id="bonusCount" name="bonusCount" value="<?= $doll['bonusCount'] ?>" class="form-control">
                        <span class="form-text">Number of times the bonus is applied</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="bonusInterval" class="form-label">Bonus Interval</label>
                        <input type="number" id="bonusInterval" name="bonusInterval" value="<?= $doll['bonusInterval'] ?>" class="form-control">
                        <span class="form-text">Interval between bonus applications (in seconds)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="hitXp" class="form-label">Hit XP</label>
                        <input type="number" id="hitXp" name="hitXp" value="<?= $doll['hitXp'] ?>" class="form-control">
                        <span class="form-text">Experience points from hits</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="dmgXp" class="form-label">Damage XP</label>
                        <input type="number" id="dmgXp" name="dmgXp" value="<?= $doll['dmgXp'] ?>" class="form-control">
                        <span class="form-text">Experience points from damage</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="damageChance" class="form-label">Damage Chance</label>
                        <input type="number" id="damageChance" name="damageChance" value="<?= $doll['damageChance'] ?>" class="form-control">
                        <span class="form-text">Chance of dealing damage (%)</span>
                    </div>
                </div>
            </div>
            
            <!-- Image Tab -->
            <div id="image" class="form-section">
                <div class="form-group">
                    <label for="doll_image" class="form-label">Doll Image</label>
                    <input type="file" id="doll_image" name="doll_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <span class="form-text">Upload an image for this doll (will be saved as [icon_id].png)</span>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Image Preview</label>
                    <div class="image-preview-container">
                        <img id="image-preview" src="<?= SITE_URL ?>/assets/img/items/default.png" alt="Image Preview" class="item-image-preview">
                    </div>
                    <span class="form-text">Preview of the uploaded image</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Doll
                </button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .form-section {
        display: none;
        padding: 20px 0;
    }
    
    .form-section.active {
        display: block;
    }
    
    .image-preview-container {
        display: flex;
        justify-content: center;
        align-items: center;
        margin: 15px 0;
        background-color: var(--secondary);
        border-radius: 8px;
        padding: 24px;
        min-height: 180px;
        min-width: 180px;
        width: 100%;
        max-width: 300px;
        transition: all 0.3s ease;
    }
    
    .item-image-preview {
        max-width: 100%;
        max-height: 160px;
        object-fit: contain;
        transition: transform 0.2s ease;
    }
    
    .item-image-preview:hover {
        transform: scale(1.05);
    }
</style>

<script>
    // Tab functionality
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.form-tab');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all sections
                const sections = document.querySelectorAll('.form-section');
                sections.forEach(section => section.classList.remove('active'));
                
                // Show the corresponding section
                const targetSection = document.getElementById(this.getAttribute('data-tab'));
                targetSection.classList.add('active');
            });
        });
    });
    
    // Image preview functionality
    function previewImage(input) {
        const preview = document.getElementById('image-preview');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '<?= SITE_URL ?>/assets/img/items/default.png';
        }
    }
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?>
