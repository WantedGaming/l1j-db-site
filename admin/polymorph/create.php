<?php
/**
 * Admin - Create New Polymorph
 */

// Set page title
$pageTitle = 'Add New Polymorph';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect polymorph data from form
    $polymorph = [
        'id' => intval($_POST['id'] ?? 0),
        'name' => $_POST['name'] ?? '',
        'polyid' => intval($_POST['polyid'] ?? 0),
        'minlevel' => intval($_POST['minlevel'] ?? 0),
        'weaponequip' => $_POST['weaponequip'] ?? '',
        'armorequip' => $_POST['armorequip'] ?? '',
        'isSkillUse' => isset($_POST['isSkillUse']) ? 1 : 0,
        'cause' => $_POST['cause'] ?? '',
        'bonusPVP' => isset($_POST['bonusPVP']) ? 'true' : 'false',
        'formLongEnable' => isset($_POST['formLongEnable']) ? 'true' : 'false'
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($polymorph['name'])) {
        $errors[] = "Polymorph name is required";
    }
    
    if (empty($polymorph['polyid'])) {
        $errors[] = "Polymorph ID is required";
    }
    
    // Check if polymorph already exists
    $existingPolymorph = $db->getRow("SELECT id FROM polymorphs WHERE id = ?", [$polymorph['id']]);
    if ($existingPolymorph) {
        $errors[] = "A polymorph with ID {$polymorph['id']} already exists";
    }
    
    // If no errors, insert the polymorph
    if (empty($errors)) {
        // Build the query
        $fields = implode(', ', array_keys($polymorph));
        $placeholders = implode(', ', array_fill(0, count($polymorph), '?'));
        
        $query = "INSERT INTO polymorphs ({$fields}) VALUES ({$placeholders})";
        
        // Execute the query
        $result = $db->execute($query, array_values($polymorph));
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Polymorph '{$polymorph['name']}' created successfully."
            ];
            
            // Redirect to polymorphs list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to create polymorph. Database error.";
        }
    }
}

// Generate next available polymorph id
$nextId = $db->getColumn("SELECT MAX(id) + 1 FROM polymorphs") ?: 1;

// Initialize default polymorph values
$polymorph = [
    'id' => $nextId,
    'name' => '',
    'polyid' => 0,
    'minlevel' => 1,
    'weaponequip' => '',
    'armorequip' => '',
    'isSkillUse' => 0,
    'cause' => '',
    'bonusPVP' => 'false',
    'formLongEnable' => 'false'
];
?>

<div class="admin-container">
    <div class="admin-header-actions">
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Polymorphs
        </a>
    </div>
    
    <div class="admin-content-card">
        <div class="admin-content-header">
            <h2>Add New Polymorph</h2>
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
                        <input type="number" id="id" name="id" value="<?= $polymorph['id'] ?>" class="form-control" required>
                        <span class="form-text">Unique identifier for this polymorph</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($polymorph['name']) ?>" class="form-control" required>
                        <span class="form-text">Display name of the polymorph</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="polyid" class="form-label">Polymorph ID</label>
                        <input type="number" id="polyid" name="polyid" value="<?= $polymorph['polyid'] ?>" class="form-control" required>
                        <span class="form-text">Polymorph graphic ID (used for image display)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="minlevel" class="form-label">Minimum Level</label>
                        <input type="number" id="minlevel" name="minlevel" value="<?= $polymorph['minlevel'] ?>" class="form-control" required min="1" max="99">
                        <span class="form-text">Minimum character level required to use this polymorph</span>
                    </div>
                </div>
                
                <!-- Additional Properties -->
                <div class="form-section">
                    <h3>Properties</h3>
                    
                    <div class="form-group">
                        <label for="weaponequip" class="form-label">Weapon Equip</label>
                        <input type="text" id="weaponequip" name="weaponequip" value="<?= htmlspecialchars($polymorph['weaponequip']) ?>" class="form-control">
                        <span class="form-text">Allowed weapon type (leave blank for none)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="armorequip" class="form-label">Armor Equip</label>
                        <input type="text" id="armorequip" name="armorequip" value="<?= htmlspecialchars($polymorph['armorequip']) ?>" class="form-control">
                        <span class="form-text">Allowed armor type (leave blank for none)</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="cause" class="form-label">Cause</label>
                        <input type="text" id="cause" name="cause" value="<?= htmlspecialchars($polymorph['cause']) ?>" class="form-control">
                        <span class="form-text">Source of the polymorph transformation</span>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="isSkillUse" name="isSkillUse" class="form-check-input" <?= $polymorph['isSkillUse'] ? 'checked' : '' ?>>
                        <label for="isSkillUse" class="form-check-label">Allow Skill Use</label>
                        <span class="form-text">Whether skills can be used in this form</span>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="bonusPVP" name="bonusPVP" class="form-check-input" <?= $polymorph['bonusPVP'] === 'true' ? 'checked' : '' ?>>
                        <label for="bonusPVP" class="form-check-label">PvP Bonus</label>
                        <span class="form-text">Whether this polymorph provides PvP bonuses</span>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="formLongEnable" name="formLongEnable" class="form-check-input" <?= $polymorph['formLongEnable'] === 'true' ? 'checked' : '' ?>>
                        <label for="formLongEnable" class="form-check-label">Long Form Enable</label>
                        <span class="form-text">Whether this polymorph can be maintained for a long time</span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Polymorph
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