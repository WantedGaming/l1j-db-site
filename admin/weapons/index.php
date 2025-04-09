<?php
/**
 * Admin Weapons List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Weapons';

// Include admin header
require_once '../../includes/admin-header.php';

// Include weapons functions
require_once '../../includes/weapons-functions.php';

// Get database instance
$db = Database::getInstance();

// Handle delete action if present
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $weaponId = intval($_GET['id']);
    
    // Add confirmation check with POST for security
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Delete associated records first (foreign key constraints)
        $db->execute("DELETE FROM weapon_skill WHERE weapon_id = ?", [$weaponId]);
        $db->execute("DELETE FROM weapon_skill_model WHERE item_id = ?", [$weaponId]);
        $db->execute("DELETE FROM weapon_damege WHERE item_id = ?", [$weaponId]);
        
        // Then delete the weapon
        $result = $db->execute("DELETE FROM weapon WHERE item_id = ?", [$weaponId]);
        
        if ($result) {
            // Set success message and redirect
            $_SESSION['admin_message'] = ['type' => 'success', 'message' => 'Weapon deleted successfully.'];
        } else {
            // Set error message
            $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Failed to delete weapon.'];
        }
        
        // Redirect to avoid form resubmission
        header("Location: index.php");
        exit;
    }
}

// Build query for weapons list
$query = "SELECT w.item_id, w.desc_en, w.type, 
                 SUBSTRING_INDEX(w.material, '(', 1) as material_name, 
                 w.dmg_small, w.dmg_large, w.safenchant, w.itemGrade, 
                 w.iconId 
          FROM weapon w";

// Handle search if present
$params = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $query .= " WHERE w.desc_en LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

// Add order by
$query .= " ORDER BY w.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 25; // More items per page for admin
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$totalWeapons = $db->getColumn("SELECT COUNT(*) FROM weapon");
$totalPages = ceil($totalWeapons / $itemsPerPage);

// Add limit for pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Execute query
$weapons = $db->getRows($query, $params);
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Manage Weapons</h1>
        <div class="admin-actions">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Weapon
            </a>
        </div>
    </div>
    
    <!-- Search and Filters -->
    <div class="admin-filters">
        <form action="index.php" method="GET" class="search-form">
            <div class="form-group">
                <input type="text" name="q" placeholder="Search weapons..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn">Search</button>
                <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                    <a href="index.php" class="btn btn-secondary">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-<?= $_SESSION['admin_message']['type'] ?>">
            <?= $_SESSION['admin_message']['message'] ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>
    
    <!-- Weapons List Table -->
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th width="60">ID</th>
                    <th width="60">Icon</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Material</th>
                    <th>Dmg(S/L)</th>
                    <th>Safe</th>
                    <th>Grade</th>
                    <th width="150">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($weapons)): ?>
                    <tr>
                        <td colspan="9" class="text-center">No weapons found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($weapons as $weapon): ?>
                        <tr>
                            <td><?= $weapon['item_id'] ?></td>
                            <td>
                                <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
                                     alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
                                     class="admin-item-icon"
                                     onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                            </td>
                            <td><?= htmlspecialchars($weapon['desc_en']) ?></td>
                            <td><?= formatWeaponType($weapon['type']) ?></td>
                            <td><?= formatMaterial($weapon['material_name']) ?></td>
                            <td><?= $weapon['dmg_small'] ?>/<?= $weapon['dmg_large'] ?></td>
                            <td>+<?= $weapon['safenchant'] ?></td>
                            <td><span class="badge <?= getGradeBadgeClass($weapon['itemGrade']) ?>"><?= formatGrade($weapon['itemGrade']) ?></span></td>
                            <td class="actions">
                                <a href="edit.php?id=<?= $weapon['item_id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="#" class="btn btn-sm btn-delete" title="Delete" 
                                   onclick="confirmDelete(<?= $weapon['item_id'] ?>, '<?= addslashes($weapon['desc_en']) ?>')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <a href="<?= SITE_URL ?>/pages/items/weapon-detail.php?id=<?= $weapon['item_id'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="admin-pagination">
            <?php if ($page > 1): ?>
                <a href="index.php?page=<?= ($page - 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="btn btn-sm">
                    &laquo; Previous
                </a>
            <?php endif; ?>
            
            <span class="pagination-info">Page <?= $page ?> of <?= $totalPages ?></span>
            
            <?php if ($page < $totalPages): ?>
                <a href="index.php?page=<?= ($page + 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="btn btn-sm">
                    Next &raquo;
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirm Deletion</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete the weapon: <span id="deleteItemName"></span>?</p>
            <p class="warning">This action cannot be undone!</p>
        </div>
        <div class="modal-footer">
            <form id="deleteForm" method="POST">
                <input type="hidden" name="confirm_delete" value="yes">
                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
// Delete confirmation modal functionality
function confirmDelete(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    document.getElementById('deleteForm').action = 'index.php?action=delete&id=' + id;
    
    // Show the modal
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'block';
    
    // Close modal when clicking the × button
    const closeBtn = document.getElementsByClassName('close')[0];
    closeBtn.onclick = function() {
        modal.style.display = 'none';
    }
    
    // Close modal when clicking Cancel
    const closeBtns = document.getElementsByClassName('close-modal');
    for (let i = 0; i < closeBtns.length; i++) {
        closeBtns[i].onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Prevent default link behavior
    return false;
}
</script>

<?php
// Include the admin footer
require_once '../../includes/admin-footer.php';
?>