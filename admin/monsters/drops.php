<?php
/**
 * Admin - Monster Drops Manager
 */

// Set page title
$pageTitle = 'Manage Monster Drops';

// Include admin header
require_once '../../includes/admin-header.php';

// Include Monster model
require_once '../../models/Monster.php';

// Get database instance
$db = Database::getInstance();

// Initialize model
$monsterModel = new Monster();

// Get monster ID from URL
$monsterId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no valid ID provided, redirect to monster list
if($monsterId <= 0) {
    header('Location: index.php');
    exit;
}

// Process form submission for adding new drop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_drop') {
    $itemId = intval($_POST['itemId'] ?? 0);
    $min = intval($_POST['min'] ?? 1);
    $max = intval($_POST['max'] ?? 1);
    $chance = intval($_POST['chance'] ?? 0);
    
    // Validation
    $errors = [];
    
    if ($itemId <= 0) {
        $errors[] = "Item ID is required";
    }
    
    if ($chance <= 0) {
        $errors[] = "Drop chance must be greater than 0";
    }
    
    if ($min > $max) {
        $errors[] = "Minimum amount cannot be greater than maximum amount";
    }
    
    // If no errors, add the drop
    if (empty($errors)) {
        $result = $monsterModel->addDrop($monsterId, $itemId, $min, $max, $chance);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Drop added successfully."
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Failed to add drop. Please check the item ID exists."
            ];
        }
        
        // Redirect to avoid form resubmission
        header("Location: drops.php?id={$monsterId}");
        exit;
    }
}

// Process form submission for updating drop
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_drop') {
    $itemId = intval($_POST['itemId'] ?? 0);
    $min = intval($_POST['min'] ?? 1);
    $max = intval($_POST['max'] ?? 1);
    $chance = intval($_POST['chance'] ?? 0);
    
    // Validation
    $errors = [];
    
    if ($itemId <= 0) {
        $errors[] = "Item ID is required";
    }
    
    if ($chance <= 0) {
        $errors[] = "Drop chance must be greater than 0";
    }
    
    if ($min > $max) {
        $errors[] = "Minimum amount cannot be greater than maximum amount";
    }
    
    // If no errors, update the drop
    if (empty($errors)) {
        $result = $monsterModel->updateDrop($monsterId, $itemId, $min, $max, $chance);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Drop updated successfully."
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Failed to update drop."
            ];
        }
        
        // Redirect to avoid form resubmission
        header("Location: drops.php?id={$monsterId}");
        exit;
    }
}

// Process drop deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_drop') {
    $itemId = intval($_POST['itemId'] ?? 0);
    
    if ($itemId > 0) {
        $result = $monsterModel->deleteDrop($monsterId, $itemId);
        
        if ($result) {
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Drop deleted successfully."
            ];
        } else {
            $_SESSION['admin_message'] = [
                'type' => 'error',
                'message' => "Failed to delete drop."
            ];
        }
        
        // Redirect to avoid form resubmission
        header("Location: drops.php?id={$monsterId}");
        exit;
    }
}

// Get monster details
$monster = $monsterModel->getMonsterById($monsterId);

// If monster not found, show error and redirect
if(!$monster) {
    $_SESSION['admin_message'] = [
        'type' => 'error',
        'message' => "Monster not found."
    ];
    header("Location: index.php");
    exit;
}

// Get all possible items for dropdown
$weapons = $db->getRows("SELECT item_id, desc_en FROM weapon ORDER BY desc_en");
$armor = $db->getRows("SELECT item_id, desc_en FROM armor ORDER BY desc_en");
$etcItems = $db->getRows("SELECT item_id, desc_en FROM etcitem ORDER BY desc_en");

// Combine all items for the dropdown
$allItems = [];
foreach ($weapons as $weapon) {
    $allItems[] = [
        'id' => $weapon['item_id'],
        'name' => $weapon['desc_en'] . ' [Weapon]',
        'type' => 'weapon'
    ];
}

foreach ($armor as $item) {
    $allItems[] = [
        'id' => $item['item_id'],
        'name' => $item['desc_en'] . ' [Armor]',
        'type' => 'armor'
    ];
}

foreach ($etcItems as $item) {
    $allItems[] = [
        'id' => $item['item_id'],
        'name' => $item['desc_en'] . ' [Etc]',
        'type' => 'etc'
    ];
}

// Sort by name
usort($allItems, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 mx-auto text-center">
                <h1 class="hero-title">Manage Drops: <?= htmlspecialchars($monster['desc_en']) ?></h1>
                <div class="item-id-display mb-3">
                    <span class="badge bg-primary fs-4 px-3 py-2">
                        <i class="fas fa-dragon me-2"></i>Monster ID: <?= $monsterId ?>
                    </span>
                    <span class="mx-3 text-muted">|</span>
                    <span class="text-muted fs-5">
                        Level: <?= $monster['lvl'] ?>
                    </span>
                </div>
                
                <!-- Buttons row -->
                <div class="hero-buttons mt-3">
                    <a href="index.php" class="btn" style="background-color: #212121; color: #e0e0e0;">
                        <i class="fas fa-list me-1"></i> All Monsters
                    </a>
                    <a href="edit.php?id=<?= $monsterId ?>" class="btn" style="background-color: #343434; color: #e0e0e0;">
                        <i class="fas fa-edit me-1"></i> Edit Monster
                    </a>
                    <button type="button" class="btn" style="background-color: #212121; color: #e0e0e0;" data-bs-toggle="modal" data-bs-target="#addDropModal">
                        <i class="fas fa-plus me-1"></i> Add New Drop
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/admin/index.php">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="index.php">Monsters</a></li>
            <li class="breadcrumb-item"><a href="edit.php?id=<?= $monsterId ?>"><?= htmlspecialchars($monster['desc_en']) ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Drops</li>
        </ol>
    </nav>
    
    <!-- Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-<?= $_SESSION['admin_message']['type'] ?>">
            <?= $_SESSION['admin_message']['message'] ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-12">
            <!-- Monster Drop Table -->
            <div class="acquisition-card">
                <div class="acquisition-card-header">
                    <h4><i class="fas fa-coins me-2"></i> Monster Drops</h4>
                </div>
                <div class="acquisition-card-body p-4">
                    <?php if (empty($monster['drops'])): ?>
                        <div class="alert alert-info">
                            <p>This monster does not have any drops defined. Use the "Add New Drop" button to add drops.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Icon</th>
                                        <th>Item ID</th>
                                        <th>Item Name</th>
                                        <th>Min</th>
                                        <th>Max</th>
                                        <th>Chance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($monster['drops'] as $drop): ?>
                                        <tr>
                                            <td>
                                                <img src="<?= SITE_URL ?>/assets/img/items/<?= $drop['itemId'] ?>.png" 
                                                     alt="<?= htmlspecialchars($drop['item_name']) ?>"
                                                     class="admin-item-icon"
                                                     onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                                            </td>
                                            <td><?= $drop['itemId'] ?></td>
                                            <td><?= htmlspecialchars($drop['item_name']) ?></td>
                                            <td><?= $drop['min'] ?></td>
                                            <td><?= $drop['max'] ?></td>
                                            <td><?= number_format($drop['chance'] / 10000, 4) ?>% (<?= $drop['chance'] ?>)</td>
                                            <td class="actions">
                                                <button class="btn btn-sm btn-edit" title="Edit" onclick="editDrop(<?= $drop['itemId'] ?>, '<?= addslashes($drop['item_name']) ?>', <?= $drop['min'] ?>, <?= $drop['max'] ?>, <?= $drop['chance'] ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-delete" title="Delete" onclick="confirmDeleteDrop(<?= $drop['itemId'] ?>, '<?= addslashes($drop['item_name']) ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Drop Modal -->
<div class="modal" id="addDropModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Drop</h3>
            <span class="close" data-bs-dismiss="modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="addDropForm" method="POST" action="">
                <input type="hidden" name="action" value="add_drop">
                
                <div class="mb-3">
                    <label for="itemId" class="form-label">Item</label>
                    <select class="form-select" id="itemId" name="itemId" required>
                        <option value="">Select an item...</option>
                        <?php foreach ($allItems as $item): ?>
                            <option value="<?= $item['id'] ?>" data-type="<?= $item['type'] ?>">
                                <?= htmlspecialchars($item['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="min" class="form-label">Min Amount</label>
                        <input type="number" class="form-control no-spinner" id="min" name="min" value="1" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="max" class="form-label">Max Amount</label>
                        <input type="number" class="form-control no-spinner" id="max" name="max" value="1" min="1" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="chance" class="form-label">Drop Chance (in 10000ths)</label>
                    <input type="number" class="form-control no-spinner" id="chance" name="chance" value="10000" min="1" max="1000000" required>
                    <small class="text-muted">10000 = 1.0000% chance, 1000000 = 100% chance</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-text">
                        <strong>Percent Chance:</strong> <span id="chancePercent">1.0000%</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="addDropForm" class="btn btn-primary">Add Drop</button>
        </div>
    </div>
</div>

<!-- Edit Drop Modal -->
<div class="modal" id="editDropModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Drop</h3>
            <span class="close" data-bs-dismiss="modal">&times;</span>
        </div>
        <div class="modal-body">
            <form id="editDropForm" method="POST" action="">
                <input type="hidden" name="action" value="update_drop">
                <input type="hidden" name="itemId" id="edit_itemId">
                
                <div class="mb-3">
                    <label for="edit_itemName" class="form-label">Item</label>
                    <input type="text" class="form-control" id="edit_itemName" readonly>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="edit_min" class="form-label">Min Amount</label>
                        <input type="number" class="form-control no-spinner" id="edit_min" name="min" min="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="edit_max" class="form-label">Max Amount</label>
                        <input type="number" class="form-control no-spinner" id="edit_max" name="max" min="1" required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="edit_chance" class="form-label">Drop Chance (in 10000ths)</label>
                    <input type="number" class="form-control no-spinner" id="edit_chance" name="chance" min="1" max="1000000" required>
                    <small class="text-muted">10000 = 1.0000% chance, 1000000 = 100% chance</small>
                </div>
                
                <div class="mb-3">
                    <div class="form-text">
                        <strong>Percent Chance:</strong> <span id="edit_chancePercent">1.0000%</span>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" form="editDropForm" class="btn btn-primary">Update Drop</button>
        </div>
    </div>
</div>

<!-- Delete Drop Modal -->
<div class="modal" id="deleteDropModal" tabindex="-1">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <span class="close" data-bs-dismiss="modal">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the drop: <span id="deleteDropItemName"></span>?</p>
            <p class="warning">This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
            <form id="deleteDropForm" method="POST" action="">
                <input type="hidden" name="action" value="delete_drop">
                <input type="hidden" name="itemId" id="delete_itemId">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal functionality
    function initModal(modalId) {
        const modal = document.getElementById(modalId);
        const closeButtons = modal.querySelectorAll('[data-bs-dismiss="modal"]');
        
        // Close buttons
        closeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        // Close when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Initialize all modals
    initModal('addDropModal');
    initModal('editDropModal');
    initModal('deleteDropModal');
    
    // Show modal function (replacement for Bootstrap's modal show)
    window.showModal = function(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = 'block';
    };
    
    // Add button click event
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target').replace('#', '');
            showModal(target);
        });
    });
    
    // Update percent chance display on change
    const chanceInput = document.getElementById('chance');
    const chancePercentSpan = document.getElementById('chancePercent');
    if (chanceInput && chancePercentSpan) {
        chanceInput.addEventListener('input', function() {
            const percent = (parseFloat(this.value) / 10000).toFixed(4);
            chancePercentSpan.textContent = percent + '%';
        });
    }
    
    // Update edit percent chance display on change
    const editChanceInput = document.getElementById('edit_chance');
    const editChancePercentSpan = document.getElementById('edit_chancePercent');
    if (editChanceInput && editChancePercentSpan) {
        editChanceInput.addEventListener('input', function() {
            const percent = (parseFloat(this.value) / 10000).toFixed(4);
            editChancePercentSpan.textContent = percent + '%';
        });
    }
});

// Edit drop function
function editDrop(itemId, itemName, min, max, chance) {
    document.getElementById('edit_itemId').value = itemId;
    document.getElementById('edit_itemName').value = itemName;
    document.getElementById('edit_min').value = min;
    document.getElementById('edit_max').value = max;
    document.getElementById('edit_chance').value = chance;
    
    // Update percent display
    const percent = (parseFloat(chance) / 10000).toFixed(4);
    document.getElementById('edit_chancePercent').textContent = percent + '%';
    
    // Show the modal
    showModal('editDropModal');
}

// Confirm delete drop function
function confirmDeleteDrop(itemId, itemName) {
    document.getElementById('delete_itemId').value = itemId;
    document.getElementById('deleteDropItemName').textContent = itemName;
    
    // Show the modal
    showModal('deleteDropModal');
}
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>
