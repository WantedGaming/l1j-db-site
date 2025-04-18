<?php
/**
 * Admin Armor List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Armor';

// Include admin header
require_once '../../includes/admin-header.php';

// Include armor functions
require_once '../../includes/armor-functions.php';

// Get database instance
$db = Database::getInstance();

// Handle delete action if present
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $armorId = intval($_GET['id']);
    
    // Add confirmation check with POST for security
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Delete the armor
        $result = $db->query("DELETE FROM armor WHERE item_id = ?", [$armorId]);
        
        if ($result) {
            // Set success message and redirect
            $_SESSION['admin_message'] = ['type' => 'success', 'message' => 'Armor deleted successfully.'];
        } else {
            // Set error message
            $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Failed to delete armor.'];
        }
        
        // Redirect to avoid form resubmission
        header("Location: index.php");
        exit;
    }
}

// Build query for armor list
$query = "SELECT a.item_id, a.desc_en, a.type, 
                 a.ac, a.grade, a.iconId 
          FROM armor a";

// Handle search and type filter
$params = [];
$whereConditions = [];

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "a.desc_en LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $whereConditions[] = "a.type = ?";
    $params[] = $_GET['type'];
}

if (!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY a.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 25; // More items per page for admin
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$totalArmor = $db->getColumn("SELECT COUNT(*) FROM armor");
$totalPages = ceil($totalArmor / $itemsPerPage);

// Add limit for pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Execute query
$armors = $db->getRows($query, $params);
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Manage Armor</h1>
                <p class="admin-hero-subtitle">Total Armor: <?= $totalArmor ?></p>
                
                <div class="hero-search-form mt-4">
                    <form action="index.php" method="GET" class="d-flex gap-2">
                        <div class="search-input-group">
                            <input type="text" name="q" placeholder="Search armor by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                                <a href="index.php" class="search-clear-btn">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="search-input-group">
                            <select name="type" class="form-control">
                                <option value="">All Types</option>
                                <option value="HELMET" <?= (isset($_GET['type']) && $_GET['type'] === 'HELMET') ? 'selected' : '' ?>>Helmet</option>
                                <option value="ARMOR" <?= (isset($_GET['type']) && $_GET['type'] === 'ARMOR') ? 'selected' : '' ?>>Armor</option>
                                <option value="T_SHIRT" <?= (isset($_GET['type']) && $_GET['type'] === 'T_SHIRT') ? 'selected' : '' ?>>T-Shirt</option>
                                <option value="CLOAK" <?= (isset($_GET['type']) && $_GET['type'] === 'CLOAK') ? 'selected' : '' ?>>Cloak</option>
                                <option value="GLOVE" <?= (isset($_GET['type']) && $_GET['type'] === 'GLOVE') ? 'selected' : '' ?>>Glove</option>
                                <option value="BOOTS" <?= (isset($_GET['type']) && $_GET['type'] === 'BOOTS') ? 'selected' : '' ?>>Boots</option>
                                <option value="SHIELD" <?= (isset($_GET['type']) && $_GET['type'] === 'SHIELD') ? 'selected' : '' ?>>Shield</option>
                                <option value="AMULET" <?= (isset($_GET['type']) && $_GET['type'] === 'AMULET') ? 'selected' : '' ?>>Amulet</option>
                                <option value="RING" <?= (isset($_GET['type']) && $_GET['type'] === 'RING') ? 'selected' : '' ?>>Ring</option>
                                <option value="BELT" <?= (isset($_GET['type']) && $_GET['type'] === 'BELT') ? 'selected' : '' ?>>Belt</option>
                                <option value="EARRING" <?= (isset($_GET['type']) && $_GET['type'] === 'EARRING') ? 'selected' : '' ?>>Earring</option>
                                <option value="GARDER" <?= (isset($_GET['type']) && $_GET['type'] === 'GARDER') ? 'selected' : '' ?>>Garder</option>
                                <option value="SHOULDER" <?= (isset($_GET['type']) && $_GET['type'] === 'SHOULDER') ? 'selected' : '' ?>>Shoulder</option>
                                <option value="BADGE" <?= (isset($_GET['type']) && $_GET['type'] === 'BADGE') ? 'selected' : '' ?>>Badge</option>
                                <option value="PENDANT" <?= (isset($_GET['type']) && $_GET['type'] === 'PENDANT') ? 'selected' : '' ?>>Pendant</option>
                            </select>
                        </div>
                    </form>
                </div>
                
                <div class="mt-3">
                    <a href="create.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Armor
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
    
    <!-- Armor List Table - Wrapped in constrained width -->
    <div class="admin-table-container">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="80">Icon</th>
                        <th width="80">Name</th>
                        <th width="80">Item ID</th>
                        <th width="80">Type</th>
                        <th width="80">AC</th>
                        <th width="80">Grade</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($armors)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No armor found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($armors as $armor): ?>
                            <tr>
								<td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $armor['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars($armor['desc_en']) ?>" 
                                         class="admin-item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                                </td>
                                <td><?= htmlspecialchars($armor['desc_en']) ?></td>
                                <td><?= $armor['item_id'] ?></td>
                                <td><?= formatArmorType($armor['type']) ?></td>
                                <td><?= $armor['ac'] ?></td>
                                <td>
                                    <?php if (!empty($armor['grade'])): ?>
                                        <span class="badge rarity-<?= strtolower($armor['grade']) ?>"><?= $armor['grade'] ?></span>
                                    <?php else: ?>
                                        <span class="badge rarity-normal">NORMAL</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="edit.php?id=<?= $armor['item_id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-delete" title="Delete" 
                                       onclick="confirmDelete(<?= $armor['item_id'] ?>, '<?= addslashes($armor['desc_en']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/pages/armor/armor-detail.php?id=<?= $armor['item_id'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                Showing <?= ($offset + 1) ?>-<?= min($offset + $itemsPerPage, $totalArmor) ?> of <?= $totalArmor ?> armor
            </div>
            
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=1<?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['type']) ? '&type='.urlencode($_GET['type']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="index.php?page=<?= ($page - 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['type']) ? '&type='.urlencode($_GET['type']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-link disabled"><i class="fas fa-angle-double-left"></i></span>
                    <span class="pagination-link disabled"><i class="fas fa-angle-left"></i></span>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="index.php?page=<?= $i ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['type']) ? '&type='.urlencode($_GET['type']) : '' ?>" 
                       class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="index.php?page=<?= ($page + 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['type']) ? '&type='.urlencode($_GET['type']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="index.php?page=<?= $totalPages ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['type']) ? '&type='.urlencode($_GET['type']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-link disabled"><i class="fas fa-angle-right"></i></span>
                    <span class="pagination-link disabled"><i class="fas fa-angle-double-right"></i></span>
                <?php endif; ?>
            </div>
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
            <p>Are you sure you want to delete the armor: <span id="deleteItemName"></span>?</p>
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
