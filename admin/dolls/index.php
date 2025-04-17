<?php
/**
 * Admin Dolls List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Dolls';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Handle delete action if present
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $dollId = intval($_GET['id']);
    
    // Add confirmation check with POST for security
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Delete the doll
        $result = $db->query("DELETE FROM dolls WHERE id = ?", [$dollId]);
        
        if ($result) {
            // Set success message and redirect
            $_SESSION['admin_message'] = ['type' => 'success', 'message' => 'Doll deleted successfully.'];
        } else {
            // Set error message
            $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Failed to delete doll.'];
        }
        
        // Redirect to avoid form resubmission
        header("Location: index.php");
        exit;
    }
}

// Build query for dolls list
$query = "SELECT d.* FROM dolls d";

// Handle search if present
$params = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $query .= " WHERE d.name LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

// Add order by
$query .= " ORDER BY d.name ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 25; // More items per page for admin
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$totalDolls = $db->getColumn("SELECT COUNT(*) FROM dolls");
$totalPages = ceil($totalDolls / $itemsPerPage);

// Add limit for pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Execute query
$dolls = $db->getRows($query, $params);
?>

<div class="admin-container">
    <div class="admin-hero-section">
    <div class="admin-hero-container">
        <div class="admin-hero-content">
            <h1 class="admin-hero-title">Manage Dolls</h1>
            <p class="admin-hero-subtitle">Total Dolls: <?= $totalDolls ?></p>
            
            <div class="hero-search-form mt-4">
                <form action="index.php" method="GET">
                    <div class="search-input-group">
                        <input type="text" name="q" placeholder="Search dolls by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                            <a href="index.php" class="search-clear-btn">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="mt-3">
                <a href="create.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add New Doll
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
    
    <!-- Dolls List Table - Wrapped in constrained width -->
    <div class="admin-table-container">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th width="60">Icon</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Item ID</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dolls)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No dolls found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dolls as $doll): ?>
                            <tr>
                                <td><?= $doll['id'] ?></td>
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $doll['icon_id'] ?>.png" 
                                         alt="<?= htmlspecialchars($doll['name']) ?>" 
                                         class="admin-item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                                </td>
                                <td><?= htmlspecialchars($doll['name']) ?></td>
                                <td><?= htmlspecialchars($doll['type'] ?? 'Standard') ?></td>
                                <td><?= $doll['item_id'] ?></td>
                                <td class="actions">
                                    <a href="edit.php?id=<?= $doll['id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-delete" title="Delete" 
                                       onclick="confirmDelete(<?= $doll['id'] ?>, '<?= addslashes($doll['name']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/pages/dolls/doll-detail.php?id=<?= $doll['id'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
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
                Showing <?= ($offset + 1) ?>-<?= min($offset + $itemsPerPage, $totalDolls) ?> of <?= $totalDolls ?> dolls
            </div>
            
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=1<?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="index.php?page=<?= ($page - 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
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
                    <a href="index.php?page=<?= $i ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" 
                       class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="index.php?page=<?= ($page + 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="index.php?page=<?= $totalPages ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
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
            <p>Are you sure you want to delete the doll: <span id="deleteItemName"></span>?</p>
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
    var modal = document.getElementById('deleteModal');
    var nameSpan = document.getElementById('deleteItemName');
    var deleteForm = document.getElementById('deleteForm');
    
    // Set the item name and form action
    nameSpan.textContent = name;
    deleteForm.action = 'index.php?action=delete&id=' + id;
    
    // Display the modal
    modal.style.display = 'block';
    
    // Close modal functionality
    var closeButtons = modal.getElementsByClassName('close');
    for (var i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    var cancelButtons = modal.getElementsByClassName('close-modal');
    for (var i = 0; i < cancelButtons.length; i++) {
        cancelButtons[i].onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    // Close when clicking outside the modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
}
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?> 