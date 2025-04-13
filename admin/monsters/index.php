<?php
/**
 * Admin Monster List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Monsters';

// Include admin header
require_once '../../includes/admin-header.php';

// Include Monster model
require_once '../../models/Monster.php';

// Get database instance
$db = Database::getInstance();

// Initialize model
$monsterModel = new Monster();

// Handle delete action if present
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $monsterId = intval($_GET['id']);
    
    // Add confirmation check with POST for security
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Delete the monster
        $result = $monsterModel->deleteMonster($monsterId);
        
        if ($result) {
            // Set success message and redirect
            $_SESSION['admin_message'] = ['type' => 'success', 'message' => 'Monster deleted successfully.'];
        } else {
            // Set error message
            $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Failed to delete monster.'];
        }
        
        // Redirect to avoid form resubmission
        header("Location: index.php");
        exit;
    }
}

// Build query for monster list
$query = "SELECT npcid, desc_en, desc_kr, lvl, hp, mp, is_bossmonster FROM npc 
          WHERE impl LIKE '%L1Monster%'";

// Handle search and boss filter
$params = [];
$whereConditions = [];

if (isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "(desc_en LIKE ? OR desc_kr LIKE ?)";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}

if (isset($_GET['boss']) && $_GET['boss'] === 'true') {
    $whereConditions[] = "is_bossmonster = 'true'";
}

if (!empty($whereConditions)) {
    $query .= " AND " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY npcid ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 25; // More items per page for admin
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$totalSql = "SELECT COUNT(*) FROM npc WHERE impl LIKE '%L1Monster%'";
if (!empty($whereConditions)) {
    $totalSql .= " AND " . implode(" AND ", $whereConditions);
}
$totalMonsters = $db->getColumn($totalSql, $params);
$totalPages = ceil($totalMonsters / $itemsPerPage);

// Add limit for pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Execute query
$monsters = $db->getRows($query, $params);
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Manage Monsters</h1>
                <p class="admin-hero-subtitle">Total Monsters: <?= $totalMonsters ?></p>
                
                <div class="hero-search-form mt-4">
                    <form action="index.php" method="GET" class="d-flex gap-2">
                        <div class="search-input-group">
                            <input type="text" name="q" placeholder="Search monsters by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                                <a href="index.php<?= isset($_GET['boss']) ? '?boss='.$_GET['boss'] : '' ?>" class="search-clear-btn">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="search-input-group">
                            <select name="boss" class="form-control">
                                <option value="">All Monsters</option>
                                <option value="true" <?= (isset($_GET['boss']) && $_GET['boss'] === 'true') ? 'selected' : '' ?>>Boss Monsters Only</option>
                            </select>
                        </div>
                    </form>
                </div>
                
                <div class="mt-3">
                    <a href="create.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Monster
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
    
    <!-- Monster List Table - Wrapped in constrained width -->
    <div class="admin-table-container">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Name (EN)</th>
                        <th>Name (KR)</th>
                        <th>Level</th>
                        <th>HP</th>
                        <th>MP</th>
                        <th>Boss</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($monsters)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No monsters found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($monsters as $monster): ?>
                            <tr>
                                <td><?= $monster['npcid'] ?></td>
                                <td><?= htmlspecialchars($monster['desc_en']) ?></td>
                                <td><?= htmlspecialchars($monster['desc_kr']) ?></td>
                                <td><?= $monster['lvl'] ?></td>
                                <td><?= $monster['hp'] ?></td>
                                <td><?= $monster['mp'] ?></td>
                                <td>
                                    <?php if ($monster['is_bossmonster'] === 'true'): ?>
                                        <span class="badge badge-danger">Boss</span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="edit.php?id=<?= $monster['npcid'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-delete" title="Delete" 
                                       onclick="confirmDelete(<?= $monster['npcid'] ?>, '<?= addslashes($monster['desc_en']) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/pages/monsters/monster-detail.php?id=<?= $monster['npcid'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
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
                Showing <?= ($offset + 1) ?>-<?= min($offset + $itemsPerPage, $totalMonsters) ?> of <?= $totalMonsters ?> monsters
            </div>
            
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=1<?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['boss']) ? '&boss='.urlencode($_GET['boss']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="index.php?page=<?= ($page - 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['boss']) ? '&boss='.urlencode($_GET['boss']) : '' ?>" class="pagination-link">
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
                    <a href="index.php?page=<?= $i ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['boss']) ? '&boss='.urlencode($_GET['boss']) : '' ?>" 
                       class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="index.php?page=<?= ($page + 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['boss']) ? '&boss='.urlencode($_GET['boss']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="index.php?page=<?= $totalPages ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['boss']) ? '&boss='.urlencode($_GET['boss']) : '' ?>" class="pagination-link">
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
            <p>Are you sure you want to delete the monster: <span id="deleteItemName"></span>?</p>
            <p class="warning">This action cannot be undone! All related drops, skills, and spawn data will also be deleted.</p>
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
