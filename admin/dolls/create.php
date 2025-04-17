<?php
/**
 * Admin - Create New Doll
 */

// Set page title
$pageTitle = 'Add New Doll';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect doll data from form
    $doll = [
        'id' => intval($_POST['id'] ?? 0),
        'name' => $_POST['name'] ?? '',
        'item_id' => intval($_POST['item_id'] ?? 0),
        'icon_id' => intval($_POST['icon_id'] ?? 0),
        'type' => $_POST['type'] ?? 'Standard',
        'hp' => intval($_POST['hp'] ?? 0),
        'mp' => intval($_POST['mp'] ?? 0),
        'ac' => intval($_POST['ac'] ?? 0),
        'str' => intval($_POST['str'] ?? 0),
        'con' => intval($_POST['con'] ?? 0),
        'dex' => intval($_POST['dex'] ?? 0),
        'wis' => intval($_POST['wis'] ?? 0),
        'int' => intval($_POST['int'] ?? 0),
        'cha' => intval($_POST['cha'] ?? 0),
        'mr' => intval($_POST['mr'] ?? 0),
        'hit' => intval($_POST['hit'] ?? 0),
        'dmg' => intval($_POST['dmg'] ?? 0),
        'description' => $_POST['description'] ?? '',
        'requirements' => $_POST['requirements'] ?? ''
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($doll['name'])) {
        $errors[] = "Doll name is required";
    }
    
    if (empty($doll['item_id'])) {
        $errors[] = "Item ID is required";
    }
    
    // Check if doll already exists
    $existingDoll = $db->getRow("SELECT id FROM dolls WHERE id = ?", [$doll['id']]);
    if ($existingDoll) {
        $errors[] = "A doll with ID {$doll['id']} already exists";
    }
    
    // If no errors, insert the doll
    if (empty($errors)) {
        // Build the query
        $fields = implode(', ', array_keys($doll));
        $placeholders = implode(', ', array_fill(0, count($doll), '?'));
        
        $query = "INSERT INTO dolls ({$fields}) VALUES ({$placeholders})";
        
        // Execute the query
        $result = $db->execute($query, array_values($doll));
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Doll '{$doll['name']}' created successfully."
            ];
            
            // Redirect to dolls list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to create doll. Database error.";
        }
    }
}

// Generate next available doll id
$nextId = $db->getColumn("SELECT MAX(id) + 1 FROM dolls") ?: 1;

// Initialize default doll values
$doll = [
    'id' => $nextId,
    'name' => '',
    'item_id' => 0,
    'icon_id' => 0,
    'type' => 'Standard',
    'hp' => 0,
    'mp' => 0,
    'ac' => 0,
    'str' => 0,
    'con' => 0,
    'dex' => 0,
    'wis' => 0,
    'int' => 0,
    'cha' => 0,
    'mr' => 0,
    'hit' => 0,
    'dmg' => 0,
    'description' => '',
    'requirements' => ''
];

// Get doll types for dropdown
$dollTypes = ['Standard', 'Summon', 'Magic', 'Special'];
?>

<div class="admin-container">
    <div class="admin-header-actions">
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Dolls
        </a>
    </div>
    
    <div class="admin-content-card">
        <div class="admin-content-header">
            <h2>Add New Doll</h2>
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
        
        <form method="POST" action="create.php" class="admin-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="id" class="form-label">ID</label>
                        <input type="number" id="id" name="id" value="<?= $doll['id'] ?>" class="form-control" required>
                        <span class="form-text">Unique identifier for this doll</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($doll['name']) ?>" class="form-control" required>
                        <span class="form-text">Name of the doll</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="item_id" class="form-label">Item ID</label>
                        <input type="number" id="item_id" name="item_id" value="<?= $doll['item_id'] ?>" class="form-control" required>
                        <span class="form-text">Associated item ID in the database</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="icon_id" class="form-label">Icon ID</label>
                        <input type="number" id="icon_id" name="icon_id" value="<?= $doll['icon_id'] ?>" class="form-control" required>
                        <span class="form-text">Icon ID for display</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="type" class="form-label">Type</label>
                        <select id="type" name="type" class="form-control">
                            <?php foreach ($dollTypes as $type): ?>
                                <option value="<?= $type ?>" <?= ($doll['type'] === $type) ? 'selected' : '' ?>><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-text">Type of doll</span>
                    </div>
                </div>
                
                <!-- Stats -->
                <div class="form-section">
                    <h3>Stats</h3>
                    
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
                        <label for="wis" class="form-label">WIS</label>
                        <input type="number" id="wis" name="wis" value="<?= $doll['wis'] ?>" class="form-control">
                        <span class="form-text">Wisdom</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="int" class="form-label">INT</label>
                        <input type="number" id="int" name="int" value="<?= $doll['int'] ?>" class="form-control">
                        <span class="form-text">Intelligence</span>
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
                    
                    <div class="form-group">
                        <label for="hit" class="form-label">Hit</label>
                        <input type="number" id="hit" name="hit" value="<?= $doll['hit'] ?>" class="form-control">
                        <span class="form-text">Hit bonus</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="dmg" class="form-label">Damage</label>
                        <input type="number" id="dmg" name="dmg" value="<?= $doll['dmg'] ?>" class="form-control">
                        <span class="form-text">Damage bonus</span>
                    </div>
                </div>
                
                <!-- Description -->
                <div class="form-section full-width">
                    <h3>Description & Requirements</h3>
                    
                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($doll['description']) ?></textarea>
                        <span class="form-text">Description of the doll</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="requirements" class="form-label">Requirements</label>
                        <textarea id="requirements" name="requirements" class="form-control" rows="4"><?= htmlspecialchars($doll['requirements']) ?></textarea>
                        <span class="form-text">Requirements to obtain or use this doll</span>
                    </div>
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

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?> 