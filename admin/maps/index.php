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

<!-- Enhanced custom CSS for the cards grid -->
<style>
    /* Card Grid Layout */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.2rem;
        margin-bottom: 2rem;
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Responsive Adjustments */
    @media (max-width: 1200px) {
        .card-grid {
            grid-template-columns: repeat(3, 1fr);
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
    
    /* Enhanced Card Styling */
    .card {
        width: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: var(--primary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        border-color: var(--accent);
    }
    
    .card-image {
        height: 150px !important;
        background-size: cover;
        background-position: center;
        object-fit: cover;
        width: 100%;
        background-color: var(--secondary);
    }
    
    .card-content {
        padding: 1rem;
        display: flex;
        flex-direction: column;
    }
    
    .card-title {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        color: var(--text);
        font-weight: 600;
    }
    
    .card-text {
        color: #cccccc;
        font-size: 0.9rem;
    }
    
    .card-text p {
        margin-bottom: 0.5rem;
    }
    
    .card-actions {
        padding: 0.75rem 1rem;
        background: var(--secondary);
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
        border-top: 1px solid var(--border-color);
        margin-top: auto;
    }
    
    /* Filter Container Styling */
    .filter-container {
        background: var(--primary);
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
    }
    
    /* Badge Styles */
    .badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 0.5rem;
    }
    
    .badge-danger {
        background-color: #dc3545;
    }
    
    .badge-secondary {
        background-color: #6c757d;
    }
    
    /* Action Buttons */
    .btn-sm.btn-edit, 
    .btn-sm.btn-delete, 
    .btn-sm.btn-view {
        padding: 0.5rem 0.75rem;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Hero Section Styling */
    .admin-hero-section {
        background-color: var(--primary);
        padding: 40px 0;
        margin-bottom: 30px;
        border-bottom: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    /* Filters Form */
    .filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .filter-buttons {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
        gap: 0.5rem;
    }
    
    /* Property Icons */
    .property-icons {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        margin-top: 0.5rem;
        color: var(--text);
        opacity: 0.7;
    }
    
    .property-icons i {
        transition: color 0.3s ease;
    }
    
    .property-icons i:hover {
        color: var(--accent);
    }
    
    /* Fixed width container */
    .admin-container {
        max-width: 1200px;
        margin-left: auto;
        margin-right: auto;
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
                    <a href="import-export.php" class="btn btn-secondary btn-sm">
                        <i class="fas fa-exchange-alt"></i> Import/Export
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
                <label for="dungeon">Area Type:</label>
                <select name="dungeon" id="dungeon" class="form-control">
                    <option value="-1">All Areas</option>
                    <option value="1" <?= $dungeon_filter === 1 ? 'selected' : '' ?>>Dungeon</option>
                    <option value="0" <?= $dungeon_filter === 0 ? 'selected' : '' ?>>Field</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="teleportable">Teleportable:</label>
                <select name="teleportable" id="teleportable" class="form-control">
                    <option value="-1">All</option>
                    <option value="1" <?= $teleportable_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $teleportable_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="markable">Markable:</label>
                <select name="markable" id="markable" class="form-control">
                    <option value="-1">All</option>
                    <option value="1" <?= $markable_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $markable_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="underwater">Underwater:</label>
                <select name="underwater" id="underwater" class="form-control">
                    <option value="-1">All</option>
                    <option value="1" <?= $underwater_filter === 1 ? 'selected' : '' ?>>Yes</option>
                    <option value="0" <?= $underwater_filter === 0 ? 'selected' : '' ?>>No</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="sort">Sort By:</label>
                <select name="sort" id="sort" class="form-control">
                    <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name (A-Z)</option>
                    <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>Name (Z-A)</option>
                    <option value="mapid_asc" <?= $sort === 'mapid_asc' ? 'selected' : '' ?>>Map ID (Low-High)</option>
                    <option value="mapid_desc" <?= $sort === 'mapid_desc' ? 'selected' : '' ?>>Map ID (High-Low)</option>
                    <option value="dungeon_asc" <?= $sort === 'dungeon_asc' ? 'selected' : '' ?>>Field First</option>
                    <option value="dungeon_desc" <?= $sort === 'dungeon_desc' ? 'selected' : '' ?>>Dungeon First</option>
                </select>
            </div>
            
            <div class="filter-buttons">
                <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
    
    <!-- Filter summary -->
    <?php if (!empty($whereClauses)): ?>
        <div style="margin-bottom: 1.5rem;">
            <span>Filters applied:</span>
            <span style="display: inline-flex; flex-wrap: wrap; gap: 0.5rem; margin-left: 0.5rem;">
                <?php if (!empty($name_search)): ?>
                    <span class="badge badge-secondary">Search: <?= htmlspecialchars($name_search) ?></span>
                <?php endif; ?>
                <?php if ($dungeon_filter !== -1): ?>
                    <span class="badge badge-secondary">Area: <?= $dungeon_filter ? 'Dungeon' : 'Field' ?></span>
                <?php endif; ?>
                <?php if ($teleportable_filter !== -1): ?>
                    <span class="badge badge-secondary">Teleportable: <?= $teleportable_filter ? 'Yes' : 'No' ?></span>
                <?php endif; ?>
                <?php if ($markable_filter !== -1): ?>
                    <span class="badge badge-secondary">Markable: <?= $markable_filter ? 'Yes' : 'No' ?></span>
                <?php endif; ?>
                <?php if ($underwater_filter !== -1): ?>
                    <span class="badge badge-secondary">Underwater: <?= $underwater_filter ? 'Yes' : 'No' ?></span>
                <?php endif; ?>
            </span>
        </div>
    <?php endif; ?>
    
    <!-- Maps Grid -->
    <div class="card-grid">
        <?php if (empty($maps)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; background: var(--primary); border-radius: 8px; border: 1px solid var(--border-color);">
                <p>No maps found matching your criteria.</p>
            </div>
        <?php else: ?>
            <?php foreach ($maps as $map): ?>
                <div class="card">
                    <?php 
                    // Check for map image using mapId
                    $map_id = $map['mapid'];
                    $base_path = $_SERVER['DOCUMENT_ROOT'] . parse_url(SITE_URL, PHP_URL_PATH);
                    
                    // Check for map image using pngId first if available
                    if (isset($map['pngId']) && !empty($map['pngId']) && $map['pngId'] > 0) {
                        // First try using pngId
                        $png_id = $map['pngId'];
                        $image_path = "/assets/img/maps/{$png_id}.jpeg";
                        $server_path = $base_path . $image_path;
                        
                        // Try png format if jpeg doesn't exist
                        if (!file_exists($server_path)) {
                            $image_path = "/assets/img/maps/{$png_id}.png";
                            $server_path = $base_path . $image_path;
                        }
                        
                        // Try jpg format if png doesn't exist
                        if (!file_exists($server_path)) {
                            $image_path = "/assets/img/maps/{$png_id}.jpg";
                            $server_path = $base_path . $image_path;
                        }
                    } else {
                        // Fall back to using mapId if pngId isn't available
                        $image_path = "/assets/img/maps/{$map_id}.jpeg";
                        $server_path = $base_path . $image_path;
                        
                        // Try png format if jpeg doesn't exist
                        if (!file_exists($server_path)) {
                            $image_path = "/assets/img/maps/{$map_id}.png";
                            $server_path = $base_path . $image_path;
                        }
                        
                        // Try jpg format if png doesn't exist
                        if (!file_exists($server_path)) {
                            $image_path = "/assets/img/maps/{$map_id}.jpg";
                            $server_path = $base_path . $image_path;
                        }
                    }
                    
                    // Use placeholder if no image found
                    if (!file_exists($server_path)) {
                        $image_path = "/assets/img/placeholders/map-placeholder.png";
                    }
                    
                    // Final image URL
                    $image_url = SITE_URL . $image_path;
                    ?>
                    <div class="card-image" style="background-image: url('<?= $image_url ?>'); background-size: cover; background-position: center;"></div>
                    <div class="card-content">
                        <h3 class="card-title"><?= htmlspecialchars($map['locationname']) ?></h3>
                        <div class="card-text">
                            <p>Map ID: <?= $map['mapid'] ?></p>
                            <p><?= isset($map['min_level']) && $map['min_level'] ? 'Level: ' . $map['min_level'] . '-' . $map['max_level'] : '' ?></p>
                            <p>
                                <?= $map['dungeon'] ? '<span class="badge badge-danger">Dungeon</span>' : '<span class="badge badge-secondary">Field</span>' ?>
                            </p>
                            <div class="property-icons">
                                <?php if ($map['teleportable']): ?>
                                    <span title="Teleportable"><i class="fas fa-magic"></i></span>
                                <?php endif; ?>
                                <?php if ($map['markable']): ?>
                                    <span title="Markable"><i class="fas fa-map-marker-alt"></i></span>
                                <?php endif; ?>
                                <?php if ($map['underwater']): ?>
                                    <span title="Underwater"><i class="fas fa-water"></i></span>
                                <?php endif; ?>
                            </div>
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
                        <a href="<?= SITE_URL ?>/pages/maps/detail.php?id=<?= $map['mapid'] ?>" class="btn btn-sm btn-view" title="View" target="_blank">
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
                    <a href="<?= $currentPath ?>?page=1<?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= $dungeon_filter !== -1 ? '&dungeon='.$dungeon_filter : '' ?><?= $sort ? '&sort='.$sort : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="<?= $currentPath ?>?page=<?= ($page - 1) ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= $dungeon_filter !== -1 ? '&dungeon='.$dungeon_filter : '' ?><?= $sort ? '&sort='.$sort : '' ?>" class="pagination-link">
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
                    $url = $currentPath . '?page=' . $i;
                    if (isset($_GET['search'])) $url .= '&search=' . urlencode($_GET['search']);
                    if ($dungeon_filter !== -1) $url .= '&dungeon=' . $dungeon_filter;
                    if ($teleportable_filter !== -1) $url .= '&teleportable=' . $teleportable_filter;
                    if ($markable_filter !== -1) $url .= '&markable=' . $markable_filter;
                    if ($underwater_filter !== -1) $url .= '&underwater=' . $underwater_filter;
                    if ($sort) $url .= '&sort=' . $sort;
                ?>
                    <a href="<?= $url ?>" class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="<?= $currentPath ?>?page=<?= ($page + 1) ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= $dungeon_filter !== -1 ? '&dungeon='.$dungeon_filter : '' ?><?= $sort ? '&sort='.$sort : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="<?= $currentPath ?>?page=<?= $totalPages ?><?= isset($_GET['search']) ? '&search='.urlencode($_GET['search']) : '' ?><?= $dungeon_filter !== -1 ? '&dungeon='.$dungeon_filter : '' ?><?= $sort ? '&sort='.$sort : '' ?>" class="pagination-link">
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
