<?php
/**
 * Admin Maps List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Maps';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Handle delete action if present
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $mapId = intval($_GET['id']);
    
    // Add confirmation check with POST for security
    if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
        // Delete the map
        $result = $db->query("DELETE FROM mapids WHERE mapid = ?", [$mapId]);
        
        if ($result) {
            // Set success message and redirect
            $_SESSION['admin_message'] = ['type' => 'success', 'message' => 'Map deleted successfully.'];
        } else {
            // Set error message
            $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Failed to delete map.'];
        }
        
        // Redirect to avoid form resubmission
        header("Location: index.php");
        exit;
    }
}

// Pagination settings
$itemsPerPage = 12;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $itemsPerPage;

// Get filter parameters
$dungeon_filter = isset($_GET['dungeon']) ? intval($_GET['dungeon']) : -1;
$teleportable_filter = isset($_GET['teleportable']) ? intval($_GET['teleportable']) : -1;
$markable_filter = isset($_GET['markable']) ? intval($_GET['markable']) : -1;
$underwater_filter = isset($_GET['underwater']) ? intval($_GET['underwater']) : -1;
$name_search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';

// Build the query
$baseSql = "SELECT * FROM mapids";
$params = [];
$whereClauses = [];

// Add dungeon filter
if ($dungeon_filter !== -1) {
    $whereClauses[] = "dungeon = ?";
    $params[] = $dungeon_filter;
}

// Add teleportable filter
if ($teleportable_filter !== -1) {
    $whereClauses[] = "teleportable = ?";
    $params[] = $teleportable_filter;
}

// Add markable filter
if ($markable_filter !== -1) {
    $whereClauses[] = "markable = ?";
    $params[] = $markable_filter;
}

// Add underwater filter
if ($underwater_filter !== -1) {
    $whereClauses[] = "underwater = ?";
    $params[] = $underwater_filter;
}

// Add name search
if (!empty($name_search)) {
    $whereClauses[] = "(locationname LIKE ? OR mappdfname LIKE ?)";
    $params[] = "%$name_search%";
    $params[] = "%$name_search%";
}

$whereSql = empty($whereClauses) ? "" : "WHERE " . implode(" AND ", $whereClauses);

// Get total count for pagination
$countSql = "SELECT COUNT(*) as count FROM mapids $whereSql";
$totalMaps = $db->getColumn($countSql, $params);
$totalPages = ceil($totalMaps / $itemsPerPage);

if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
    $offset = ($page - 1) * $itemsPerPage;
}

// Determine sort order
switch ($sort) {
    case 'name_desc':
        $orderBy = "locationname DESC";
        break;
    case 'mapid_asc':
        $orderBy = "mapid ASC";
        break;
    case 'mapid_desc':
        $orderBy = "mapid DESC";
        break;
    case 'dungeon_asc':
        $orderBy = "dungeon ASC, locationname ASC";
        break;
    case 'dungeon_desc':
        $orderBy = "dungeon DESC, locationname ASC";
        break;
    case 'name_asc':
    default:
        $orderBy = "locationname ASC";
        break;
}

// Get maps
$sql = "$baseSql $whereSql ORDER BY $orderBy LIMIT $offset, $itemsPerPage";
$maps = $db->getRows($sql, $params);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];
?>

<!-- Add custom CSS for card grid -->
<style>
    .card-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    @media (max-width: 1200px) {
        .card-grid {
            grid-template-columns: repeat(4, 1fr);
        }
    }
    
    @media (max-width: 992px) {
        .card-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 768px) {
        .card-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .card-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .card {
        width: 100%;
        transition: transform 0.2s;
        background: #1a1a1a;
        border: 1px solid #333;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card-image {
        height: 120px !important;
        background-size: cover;
        background-position: center;
        background-color: #2a2a2a;
    }
    
    .card-content {
        padding: 1rem;
    }
    
    .card-title {
        font-size: 1rem;
        margin-bottom: 0.5rem;
        color: #fff;
    }
    
    .card-text {
        font-size: 0.85rem;
        color: #ccc;
    }
    
    .card-text p {
        margin-bottom: 0.3rem;
    }
    
    .card-actions {
        padding: 0.5rem 1rem;
        background: #252525;
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }
    
    .filter-container {
        background: #1a1a1a;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .filter-group {
        margin-bottom: 1rem;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: #fff;
    }
    
    .filter-group select {
        width: 100%;
        padding: 0.5rem;
        background: #252525;
        border: 1px solid #333;
        color: #fff;
        border-radius: 4px;
    }
    
    .sort-group {
        grid-column: 1 / -1;
    }
</style>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Manage Maps</h1>
                <p class="admin-hero-subtitle">Total Maps: <?= $totalMaps ?></p>
                
                <div class="hero-search-form mt-4">
                    <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                        <input type="text" name="search" placeholder="Search maps by name..." value="<?= htmlspecialchars($name_search) ?>">
                        <button type="submit" class="btn">Search</button>
                    </form>
                </div>
                
                <div class="mt-3">
                    <a href="create.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Add New Map
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
    
    <!-- Filter System -->
    <div class="filter-container">
        <form action="<?= $currentPath ?>" method="GET" class="filters-form">
            <!-- Preserve search query if present -->
            <?php if(!empty($name_search)): ?>
                <input type="hidden" name="search" value="<?= htmlspecialchars($name_search) ?>">
            <?php endif; ?>
            
            <div class="filter-group">
                <label for="dungeon">Dungeon</label>
                <select name="dungeon" id="dungeon">
                    <option value="-1">All</option>
                    <option value="1" <?= $dungeon_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $dungeon_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="teleportable">Teleportable</label>
                <select name="teleportable" id="teleportable">
                    <option value="-1">All</option>
                    <option value="1" <?= $teleportable_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $teleportable_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="markable">Markable</label>
                <select name="markable" id="markable">
                    <option value="-1">All</option>
                    <option value="1" <?= $markable_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $markable_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="underwater">Underwater</label>
                <select name="underwater" id="underwater">
                    <option value="-1">All</option>
                    <option value="1" <?= $underwater_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $underwater_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group sort-group">
                <label for="sort">Sort By</label>
                <select name="sort" id="sort">
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                    <option value="mapid_asc" <?= $sort === 'mapid_asc' ? 'selected' : '' ?>>Map ID (Low-High)</option>
                    <option value="mapid_desc" <?= $sort === 'mapid_desc' ? 'selected' : '' ?>>Map ID (High-Low)</option>
                    <option value="dungeon_asc" <?= $sort === 'dungeon_asc' ? 'selected' : '' ?>>Dungeon (Yes-No)</option>
                    <option value="dungeon_desc" <?= $sort === 'dungeon_desc' ? 'selected' : '' ?>>Dungeon (No-Yes)</option>
                </select>
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
            </div>
        </form>
    </div>
    
    <!-- Maps Grid -->
    <div class="card-grid">
        <?php if (empty($maps)): ?>
            <div class="col-span-full text-center py-8">
                <p>No maps found matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($maps as $map): ?>
                <div class="card">
                    <div class="card-image" style="background-image: url('<?= SITE_URL ?>/assets/img/maps/<?= $map['mapid'] ?>.jpg')"></div>
                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($map['locationname']) ?></h3>
                        <div class="card-text">
                            <p>Map ID: <?= $map['mapid'] ?></p>
                            <p>Level: <?= $map['min_level'] ? $map['min_level'].'-'.$map['max_level'] : 'N/A' ?></p>
                            <p>Dungeon: <?= $map['dungeon'] ? 'Yes' : 'No' ?></p>
                            <p>Teleportable: <?= $map['teleportable'] ? 'Yes' : 'No' ?></p>
                        </div>
                    </div>
                    <div class="card-actions">
                        <a href="edit.php?id=<?= $map['mapid'] ?>" class="btn btn-sm btn-edit" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" class="btn btn-sm btn-delete" title="Delete" 
                           onclick="confirmDelete(<?= $map['mapid'] ?>, '<?= addslashes($map['locationname']) ?>')">
                            <i class="fas fa-trash"></i>
                        </a>
                        <a href="<?= SITE_URL ?>/pages/maps/map-detail.php?id=<?= $map['mapid'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                Showing <?= ($offset + 1) ?>-<?= min($offset + $itemsPerPage, $totalMaps) ?> of <?= $totalMaps ?> maps
            </div>
            
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="<?= $currentPath ?>?page=1<?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="<?= $currentPath ?>?page=<?= ($page - 1) ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" class="pagination-link">
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
                    <a href="<?= $currentPath ?>?page=<?= $i ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" 
                       class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $currentPath ?>?page=<?= ($page + 1) ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="<?= $currentPath ?>?page=<?= $totalPages ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?>" class="pagination-link">
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
            <p>Are you sure you want to delete the map: <span id="deleteItemName"></span>?</p>
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