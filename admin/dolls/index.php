<?php
/**
 * Admin Dolls List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Magic Dolls';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Handle delete action if present
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $dollId = intval($_GET['id']);
    
    // Add confirmation check with POST for security
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Get doll details before deletion to handle image
        $doll = $db->getRow("SELECT e.iconId, e.desc_en FROM etcitem e WHERE e.item_id = ?", [$dollId]);
        
        // Begin a transaction to delete from both tables
        $db->beginTransaction();
        
        try {
            // Delete from magicdoll_info table
            $result1 = $db->execute("DELETE FROM magicdoll_info WHERE itemId = ?", [$dollId]);
            
            // Delete from etcitem table
            $result2 = $db->execute("DELETE FROM etcitem WHERE item_id = ? AND use_type = 'MAGICDOLL'", [$dollId]);
            
            if ($result1 && $result2) {
                // Transaction succeeded
                $db->commit();
                
                // Delete the image file if it exists
                if ($doll && isset($doll['iconId'])) {
                    $imagePath = "../../assets/img/items/{$doll['iconId']}.png";
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                }
                
                // Set success message
                $_SESSION['admin_message'] = [
                    'type' => 'success',
                    'message' => "Magic doll '" . ($doll ? htmlspecialchars($doll['desc_en']) : "#{$dollId}") . "' deleted successfully."
                ];
            } else {
                // Something went wrong
                $db->rollback();
                $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Failed to delete magic doll.'];
            }
        } catch (Exception $e) {
            // Exception occurred
            $db->rollback();
            $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
        }
        
        // Redirect to avoid form resubmission
        header("Location: index.php");
        exit;
    }
}

// Build base query with join to magicdoll_info table
// Add a left join to check if the item is a blessed version of another doll
$query = "SELECT e.item_id, e.desc_en, e.iconId, 
          m.grade as doll_grade, m.haste, m.blessItemId,
          (SELECT COUNT(*) FROM magicdoll_info WHERE blessItemId = e.item_id) as is_blessed_version
          FROM etcitem e 
          LEFT JOIN magicdoll_info m ON e.item_id = m.itemId
          WHERE e.use_type = 'MAGICDOLL'";

// Handle search if present
$params = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $query .= " AND e.desc_en LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

// Filter by doll grade (from magicdoll_info table)
if(isset($_GET['grade']) && $_GET['grade'] !== '') {
    $query .= " AND m.grade = ?";
    $params[] = intval($_GET['grade']);
}

// Add order by doll grade first, then item name
$query .= " ORDER BY 
    CASE 
        WHEN m.grade = 0 THEN 1
        WHEN m.grade = 1 THEN 2
        WHEN m.grade = 2 THEN 3
        WHEN m.grade = 3 THEN 4
        WHEN m.grade = 4 THEN 5
        WHEN m.grade = 5 THEN 6
        WHEN m.grade = 6 THEN 7
        ELSE 8
    END, e.desc_en ASC";

// Execute query to get all results
$allDolls = $db->getRows($query, $params);

// Filter out blessed versions
$filteredDolls = array_filter($allDolls, function($doll) {
    // Only include the doll if it's not a blessed version of another doll
    return $doll['is_blessed_version'] == 0;
});

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 25; // More items per page for admin
$offset = ($page - 1) * $itemsPerPage;

// Calculate pagination
$totalItems = count($filteredDolls);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of items for this page
$dolls = array_slice($filteredDolls, $offset, $itemsPerPage);

// Get list of available doll grades for filter
$gradeQuery = "SELECT DISTINCT grade FROM magicdoll_info ORDER BY grade ASC";
$availableGrades = $db->getRows($gradeQuery);
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Manage Magic Dolls</h1>
                <p class="admin-hero-subtitle">Total Dolls: <?= $totalItems ?></p>
                
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
                
                <!-- Grade Filter -->
                <div class="mt-4">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; justify-content: center;">
                        <div class="grade-buttons" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                            <span style="font-weight: 600; margin-right: 0.5rem;">Grade:</span>
                            <a href="<?= 'index.php' . (isset($_GET['q']) ? '?q=' . urlencode($_GET['q']) : '') ?>" 
                               class="grade-button <?= !isset($_GET['grade']) || $_GET['grade'] === '' ? 'active' : '' ?>"
                               style="padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; color: white; font-weight: 500; transition: all 0.2s; background-color: #666;">
                                All
                            </a>
                            
                            <?php foreach($availableGrades as $grade): ?>
                                <?php 
                                    $gradeValue = $grade['grade'];
                                    $isActive = isset($_GET['grade']) && $_GET['grade'] == $gradeValue;
                                    
                                    // Different colors based on grade level
                                    $gradeColors = [
                                        0 => '#6c757d', // Normal - gray
                                        1 => '#28a745', // Advanced - green
                                        2 => '#007bff', // Rare - blue
                                        3 => '#6f42c1', // Hero - purple
                                        4 => '#fd7e14', // Legend - orange
                                        5 => '#dc3545', // Myth - red
                                        6 => '#ffc107'  // Only - yellow
                                    ];
                                    
                                    $gradeColor = isset($gradeColors[$gradeValue]) ? $gradeColors[$gradeValue] : '#6c757d';
                                    
                                    // Determine the URL
                                    $url = 'index.php?grade=' . $gradeValue;
                                    if (isset($_GET['q']) && !empty($_GET['q'])) {
                                        $url .= '&q=' . urlencode($_GET['q']);
                                    }
                                ?>
                                <a href="<?= $url ?>" 
                                   class="grade-button <?= $isActive ? 'active' : '' ?>"
                                   style="padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; color: white; font-weight: 500; transition: all 0.2s; background-color: <?= $gradeColor ?>;">
                                    <?= $gradeValue ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
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
                        <th width="80">Icon</th>
                        <th width="80">Name</th>
                        <th width="80">Item ID</th>
                        <th width="80">Grade</th>
                        <th width="80">Type</th>
                        <th width="80">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dolls)): ?>
                        <tr>
                            <td colspan="6" class="text-center">No dolls found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dolls as $doll): ?>
                            <?php 
                                // Check if icon image exists
                                $iconPath = "../../assets/img/items/{$doll['iconId']}.png";
                                $hasImage = file_exists($iconPath);
                            ?>
                            <tr>
								<td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $doll['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars(cleanItemName($doll['desc_en'])) ?>" 
                                         class="admin-item-icon <?= $hasImage ? '' : 'no-image' ?>"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'; this.classList.add('no-image');">
                                </td>
                                <td><?= $doll['desc_en'] ?></td>
                                <td><?= htmlspecialchars(cleanItemName($doll['item_id'])) ?></td>
                                <td>
                                    <?php if (isset($doll['doll_grade'])): ?>
                                        <span class="badge <?= getGradeBadgeClass('grade-' . $doll['doll_grade']) ?>">
                                            <?= $doll['doll_grade'] ?>
                                        </span>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?= ($doll['haste'] === 'true') ? 'Haste' : 'Standard' ?></td>
                                <td class="actions">
                                    <a href="edit.php?id=<?= $doll['item_id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm btn-delete" title="Delete" 
                                       onclick="confirmDelete(<?= $doll['item_id'] ?>, '<?= addslashes(cleanItemName($doll['desc_en'])) ?>')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <a href="<?= SITE_URL ?>/pages/dolls/doll-detail.php?id=<?= $doll['item_id'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
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
                Showing <?= ($offset + 1) ?>-<?= min($offset + $itemsPerPage, $totalItems) ?> of <?= $totalItems ?> dolls
            </div>
            
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=1<?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['grade']) ? '&grade='.$_GET['grade'] : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="index.php?page=<?= ($page - 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['grade']) ? '&grade='.$_GET['grade'] : '' ?>" class="pagination-link">
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
                
                for ($i = $startPage; $i <= $endPage; $i++): 
                    $queryParams = [];
                    if (isset($_GET['q'])) $queryParams['q'] = $_GET['q'];
                    if (isset($_GET['grade'])) $queryParams['grade'] = $_GET['grade'];
                    $queryParams['page'] = $i;
                    $queryString = http_build_query($queryParams);
                ?>
                    <a href="index.php?<?= $queryString ?>" 
                       class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): 
                    $queryParams = [];
                    if (isset($_GET['q'])) $queryParams['q'] = $_GET['q'];
                    if (isset($_GET['grade'])) $queryParams['grade'] = $_GET['grade'];
                ?>
                    <a href="index.php?page=<?= ($page + 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['grade']) ? '&grade='.$_GET['grade'] : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="index.php?page=<?= $totalPages ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?><?= isset($_GET['grade']) ? '&grade='.$_GET['grade'] : '' ?>" class="pagination-link">
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
            <p class="warning">This action cannot be undone! This will delete the doll entry and any associated images.</p>
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

<style>
    .grade-button {
        opacity: 0.7;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .grade-button:hover, .grade-button.active {
        opacity: 1;
        transform: translateY(-2px);
    }
    
    .grade-button.active {
        box-shadow: 0 0 10px rgba(249, 75, 31, 0.5);
    }
    
    .admin-item-icon.no-image {
        opacity: 0.5;
        border: 1px dashed #ccc;
    }
</style>

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
