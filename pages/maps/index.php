<?php
/**
 * Monster listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Maps';
$pageDescription = 'Browse all maps in L1J Remastered including fields, dungeons, and special areas.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

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
    $whereClauses[] = "locationname LIKE ?";
    $params[] = "%$name_search%";
}

$whereSql = empty($whereClauses) ? "" : "WHERE " . implode(" AND ", $whereClauses);

// Get total count for pagination
$countSql = "SELECT COUNT(*) as count FROM mapids $whereSql";
$totalItems = $db->getColumn($countSql, $params);
$totalPages = ceil($totalItems / $itemsPerPage);

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

// Get maps - apply pagination in the SQL query
$sql = "$baseSql $whereSql ORDER BY $orderBy LIMIT $offset, $itemsPerPage";
$maps = $db->getRows($sql, $params);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

?>

<!-- Add custom CSS for 5 cards per row -->
<style>
    /* Override the default card-grid to show 5 cards per row */
    .card-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    /* Responsive adjustments */
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
    
    /* Make cards smaller to fit 5 in a row */
    .card {
        width: 100%;
        transition: transform 0.2s;
    }
    
    .card:hover {
        transform: translateY(-5px);
    }
    
    .card-image {
        height: 120px !important;
    }
    
    .card-title {
        font-size: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .card-text p {
        margin-bottom: 0.3rem;
        font-size: 0.85rem;
    }
    
    /* Center the filter buttons */
    .filter-buttons {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
    }
</style>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/maps-hero.jpg');">
    <div class="container">
        <h1>Maps Database</h1>
        <p>Explore the vast world of L1J Remastered. Discover detailed information about every map in the game, from peaceful villages to dangerous dungeons.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search maps by name..." value="<?= htmlspecialchars($name_search) ?>">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <section class="page-section">
        <!-- Filter System -->
        <div class="filter-container">
            <form action="<?= $currentPath ?>" method="GET" class="filters-form">
                <!-- Preserve search query if present -->
                <?php if(!empty($name_search)): ?>
                    <input type="hidden" name="search" value="<?= htmlspecialchars($name_search) ?>">
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem;">
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="dungeon">Area Type:</label>
                        <select name="dungeon" id="dungeon" class="form-control">
                            <option value="-1">All Areas</option>
                            <option value="1" <?= $dungeon_filter === 1 ? 'selected' : '' ?>>Dungeon</option>
                            <option value="0" <?= $dungeon_filter === 0 ? 'selected' : '' ?>>Field</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="teleportable">Teleportable:</label>
                        <select name="teleportable" id="teleportable" class="form-control">
                            <option value="-1">All</option>
                            <option value="1" <?= $teleportable_filter === 1 ? 'selected' : '' ?>>Yes</option>
                            <option value="0" <?= $teleportable_filter === 0 ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="markable">Markable:</label>
                        <select name="markable" id="markable" class="form-control">
                            <option value="-1">All</option>
                            <option value="1" <?= $markable_filter === 1 ? 'selected' : '' ?>>Yes</option>
                            <option value="0" <?= $markable_filter === 0 ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="underwater">Underwater:</label>
                        <select name="underwater" id="underwater" class="form-control">
                            <option value="-1">All</option>
                            <option value="1" <?= $underwater_filter === 1 ? 'selected' : '' ?>>Yes</option>
                            <option value="0" <?= $underwater_filter === 0 ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
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
                </div>
                
                <!-- Centered buttons -->
                <div class="filter-buttons">
                    <a href="<?= $currentPath ?>" class="btn btn-secondary" style="margin-right: 0.5rem;">Reset</a>
                    <button type="submit" class="btn">Apply</button>
                </div>
            </form>
        </div>
        
        <!-- Filter summary -->
        <div style="margin: 1rem 0;">
            <span>Found <?= $totalItems ?> map<?= $totalItems !== 1 ? 's' : '' ?></span>
            <?php if (!empty($whereClauses)): ?>
                <small style="margin-left: 0.5rem;">
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
                </small>
            <?php endif; ?>
        </div>
        
        <!-- Maps Grid Display -->
        <div class="card-grid">
            <?php if (!empty($maps)): ?>
                <?php foreach ($maps as $map): ?>
                    <div class="card" onclick="window.location='detail.php?id=<?= $map['mapid'] ?? 0 ?>';" style="cursor: pointer;">
                        <?php 
                        // Check for map image using mapId
                        $map_id = $map['mapid'];

                        // First try the maps directory with the map ID
                        $base_path = dirname(dirname(dirname(__FILE__))); // Go up three levels to get to root
                        $image_path = "/assets/img/maps/{$map_id}.jpeg";
                        $server_path = $base_path . $image_path;

                        // Check for map image prioritizing pngId if available
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
                            $map_id = $map['mapid'];
                            $image_path = "/assets/img/maps/{$map_id}.jpeg";
                            $server_path = $base_path . $image_path;
                            
                            // Try png format
                            if (!file_exists($server_path)) {
                                $image_path = "/assets/img/maps/{$map_id}.png";
                                $server_path = $base_path . $image_path;
                            }
                            
                            // Try jpg format
                            if (!file_exists($server_path)) {
                                $image_path = "/assets/img/maps/{$map_id}.jpg";
                                $server_path = $base_path . $image_path;
                            }
                        }

                        // Use placeholder if no image found
                        if (!file_exists($server_path)) {
                            $image_path = "/assets/img/placeholders/map-placeholder.png";
                        }

                        // Final image source for HTML
                        $imageSrc = SITE_URL . $image_path;
                        ?>
                        <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($map['locationname'] ?? 'Map') ?>" class="card-image" style="object-fit: cover;" onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/map-placeholder.png'">
                        <div class="card-content">
                            <h3 class="card-title"><?= htmlspecialchars($map['locationname'] ?? 'Unknown Map') ?></h3>
                            <div class="card-text">
                                <p>Map ID: <?= $map['mapid'] ?></p>
                                <p>
                                    <?= $map['dungeon'] ? '<span class="badge badge-danger">Dungeon</span>' : '<span class="badge badge-secondary">Field</span>' ?>
                                </p>
                                <div style="display: flex; justify-content: space-between; margin-top: 5px;">
                                    <small>&nbsp;</small>
                                    <div>
                                        <?php if ($map['teleportable']): ?>
                                            <span title="Teleportable" style="margin-right: 3px;"><i class="fas fa-magic"></i></span>
                                        <?php endif; ?>
                                        <?php if ($map['markable']): ?>
                                            <span title="Markable" style="margin-right: 3px;"><i class="fas fa-map-marker-alt"></i></span>
                                        <?php endif; ?>
                                        <?php if ($map['underwater']): ?>
                                            <span title="Underwater"><i class="fas fa-water"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                    <p>No maps found matching your criteria. Try adjusting your filters.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
            <div class="pagination">
                <span class="pagination-info">
                    Showing <?= $offset + 1 ?>-<?= min($offset + $itemsPerPage, $totalItems) ?> of <?= $totalItems ?> maps
                </span>
                
                <div class="pagination-links">
                    <?php
                    // First page link
                    if($page > 1):
                    ?>
                        <a href="<?= getPaginationUrl(1) ?>" class="pagination-link">«« First</a>
                    <?php endif; ?>
                    
                    <?php
                    // Previous page link
                    if($page > 1):
                    ?>
                        <a href="<?= getPaginationUrl($page - 1) ?>" class="pagination-link">« Prev</a>
                    <?php else: ?>
                        <span class="pagination-link disabled">« Prev</span>
                    <?php endif; ?>
                    
                    <?php
                    // Page links - improved algorithm
                    if ($totalPages <= 7) {
                        // Show all pages if 7 or fewer
                        $startPage = 1;
                        $endPage = $totalPages;
                    } else {
                        // Show pages around current page with ellipsis
                        if ($page <= 3) {
                            // Near start
                            $startPage = 1;
                            $endPage = 5;
                        } elseif ($page >= $totalPages - 2) {
                            // Near end
                            $startPage = $totalPages - 4;
                            $endPage = $totalPages;
                        } else {
                            // Middle
                            $startPage = $page - 2;
                            $endPage = $page + 2;
                        }
                    }
                    
                    // Display ellipsis for start if needed
                    if ($startPage > 1):
                    ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    
                    <?php
                    // Page number links
                    for($i = $startPage; $i <= $endPage; $i++):
                        $isActive = $i === $page;
                    ?>
                        <a href="<?= getPaginationUrl($i) ?>" class="pagination-link <?= $isActive ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php
                    // Display ellipsis for end if needed
                    if ($endPage < $totalPages):
                    ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    
                    <?php
                    // Next page link
                    if($page < $totalPages):
                    ?>
                        <a href="<?= getPaginationUrl($page + 1) ?>" class="pagination-link">Next »</a>
                    <?php else: ?>
                        <span class="pagination-link disabled">Next »</span>
                    <?php endif; ?>
                    
                    <?php
                    // Last page link
                    if($page < $totalPages):
                    ?>
                        <a href="<?= getPaginationUrl($totalPages) ?>" class="pagination-link">Last »»</a>
                    <?php endif; ?>
                </div>
                
                <!-- Page Jump Form -->
                <div class="page-jump-form">
                    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                        <?php
                        // Preserve all current GET parameters except page
                        foreach ($_GET as $key => $value) {
                            if ($key !== 'page' && $value !== '') {
                                echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                            }
                        }
                        ?>
                        <span>Go to page:</span>
                        <div style="display: flex;">
                            <input type="number" name="page" min="1" max="<?= $totalPages ?>" value="<?= $page ?>" class="page-jump-input" style="border-top-right-radius: 0; border-bottom-right-radius: 0; width: 70px;">
                            <button type="submit" class="btn" style="border-top-left-radius: 0; border-bottom-left-radius: 0; padding: 0.8rem 1rem; margin: 0;">
                                <span style="font-size: 1.2rem;">→</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>