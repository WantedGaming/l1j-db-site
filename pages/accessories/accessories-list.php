<?php
/**
 * Accessories listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Accessories';
$pageDescription = 'Browse all accessories in L1J Remastered including rings, earrings, belts, and more.';

// Include header
require_once '../../includes/header.php';

// Include armor functions
require_once '../../includes/armor-functions.php';

// Get database instance
$db = Database::getInstance();

// Build query
$query = "SELECT a.item_id, a.desc_en, a.type, 
                 SUBSTRING_INDEX(a.material, '(', 1) as material_name, 
                 a.ac, a.safenchant, a.itemGrade, 
                 a.iconId 
          FROM armor a 
          WHERE a.type IN ('BELT', 'RING_2', 'EARRING', 'RON', 'BADGE', 'PENDANT', 'RING', 'AMULET', 'SENTENCE')";

// Handle search and filters
$whereConditions = [];
$params = [];

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "a.desc_en LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $whereConditions[] = "a.type = ?";
    $params[] = $_GET['type'];
}

if(isset($_GET['grade']) && !empty($_GET['grade'])) {
    $whereConditions[] = "a.itemGrade = ?";
    $params[] = $_GET['grade'];
}

if(isset($_GET['material']) && !empty($_GET['material'])) {
    $whereConditions[] = "a.material LIKE ?";
    $params[] = '%' . $_GET['material'] . '%';
}

// Add where conditions to query if any
if(!empty($whereConditions)) {
    $query .= " AND " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY 
    CASE 
        WHEN a.itemGrade = 'ONLY' THEN 1
        WHEN a.itemGrade = 'MYTH' THEN 2
        WHEN a.itemGrade = 'LEGEND' THEN 3
        WHEN a.itemGrade = 'HERO' THEN 4
        WHEN a.itemGrade = 'RARE' THEN 5
        WHEN a.itemGrade = 'ADVANC' THEN 6
        WHEN a.itemGrade = 'NORMAL' THEN 7
        ELSE 8
    END, a.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20;
$offset = ($page - 1) * $itemsPerPage;

// Execute query to get all results for filtering
$allAccessories = $db->getRows($query, $params);

// Filter for available items if requested
$filteredAccessories = $allAccessories;
// Default to showing only available items unless explicitly set to "all"
if(!isset($_GET['availability']) || $_GET['availability'] !== 'all') {
    // By default or if explicitly set to "available", filter out unavailable items
    $filteredAccessories = array_filter($allAccessories, function($accessory) {
        return isItemAvailable($accessory['iconId'], SITE_URL);
    });
}

// Calculate pagination based on filtered results
$totalItems = count($filteredAccessories);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of accessories for this page
$accessories = array_slice($filteredAccessories, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/weapons-hero.jpg');">
    <div class="container">
        <h1>Accessories Database</h1>
        <p>Explore the complete collection of accessories in L1J Remastered. From common items to legendary artifacts, find detailed information about all accessories in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search accessories by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <section class="page-section">
        <!-- Filter System with Global Styles -->
        <div class="filter-container">
            <form action="<?= $currentPath ?>" method="GET" class="filters-form">
                <!-- Preserve search query if present -->
                <?php if(isset($_GET['q']) && !empty($_GET['q'])): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
                <?php endif; ?>
                
                <div style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem;">
                    <!-- Availability Filter -->
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="availability">Availability:</label>
                        <select name="availability" id="availability" class="form-control">
                            <option value="available" <?= (!isset($_GET['availability']) || $_GET['availability'] === 'available') ? 'selected' : '' ?>>In-Game Only</option>
                            <option value="all" <?= (isset($_GET['availability']) && $_GET['availability'] === 'all') ? 'selected' : '' ?>>Show All Items</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="grade">Grade:</label>
                        <select name="grade" id="grade" class="form-control">
                            <option value="">All Grades</option>
                            <option value="NORMAL" <?= isset($_GET['grade']) && $_GET['grade'] === 'NORMAL' ? 'selected' : '' ?>>Normal</option>
                            <option value="ADVANC" <?= isset($_GET['grade']) && $_GET['grade'] === 'ADVANC' ? 'selected' : '' ?>>Advanced</option>
                            <option value="RARE" <?= isset($_GET['grade']) && $_GET['grade'] === 'RARE' ? 'selected' : '' ?>>Rare</option>
                            <option value="HERO" <?= isset($_GET['grade']) && $_GET['grade'] === 'HERO' ? 'selected' : '' ?>>Hero</option>
                            <option value="LEGEND" <?= isset($_GET['grade']) && $_GET['grade'] === 'LEGEND' ? 'selected' : '' ?>>Legend</option>
                            <option value="MYTH" <?= isset($_GET['grade']) && $_GET['grade'] === 'MYTH' ? 'selected' : '' ?>>Myth</option>
                            <option value="ONLY" <?= isset($_GET['grade']) && $_GET['grade'] === 'ONLY' ? 'selected' : '' ?>>Only</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="material">Material:</label>
                        <select name="material" id="material" class="form-control">
                            <option value="">All Materials</option>
                            <option value="GOLD" <?= isset($_GET['material']) && $_GET['material'] === 'GOLD' ? 'selected' : '' ?>>Gold</option>
                            <option value="SILVER" <?= isset($_GET['material']) && $_GET['material'] === 'SILVER' ? 'selected' : '' ?>>Silver</option>
                            <option value="CRYSTAL" <?= isset($_GET['material']) && $_GET['material'] === 'CRYSTAL' ? 'selected' : '' ?>>Crystal</option>
                            <option value="GEMSTONE" <?= isset($_GET['material']) && $_GET['material'] === 'GEMSTONE' ? 'selected' : '' ?>>Gemstone</option>
                            <option value="LEATHER" <?= isset($_GET['material']) && $_GET['material'] === 'LEATHER' ? 'selected' : '' ?>>Leather</option>
                            <option value="CLOTH" <?= isset($_GET['material']) && $_GET['material'] === 'CLOTH' ? 'selected' : '' ?>>Cloth</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
						<label for="type">Accessory Type:</label>
						<select name="type" id="type" class="form-control">
							<option value="">All Types</option>
							<option value="BELT" <?= isset($_GET['type']) && $_GET['type'] === 'BELT' ? 'selected' : '' ?>>Belt</option>
							<option value="RING_2" <?= isset($_GET['type']) && $_GET['type'] === 'RING_2' ? 'selected' : '' ?>>Ring 2</option>
							<option value="EARRING" <?= isset($_GET['type']) && $_GET['type'] === 'EARRING' ? 'selected' : '' ?>>Earring</option>
							<option value="RON" <?= isset($_GET['type']) && $_GET['type'] === 'RON' ? 'selected' : '' ?>>Ron</option>
							<option value="BADGE" <?= isset($_GET['type']) && $_GET['type'] === 'BADGE' ? 'selected' : '' ?>>Badge</option>
							<option value="PENDANT" <?= isset($_GET['type']) && $_GET['type'] === 'PENDANT' ? 'selected' : '' ?>>Pendant</option>
							<option value="RING" <?= isset($_GET['type']) && $_GET['type'] === 'RING' ? 'selected' : '' ?>>Ring</option>
							<option value="AMULET" <?= isset($_GET['type']) && $_GET['type'] === 'AMULET' ? 'selected' : '' ?>>Amulet</option>
							<option value="SENTENCE" <?= isset($_GET['type']) && $_GET['type'] === 'SENTENCE' ? 'selected' : '' ?>>Sentence</option>
						</select>
					</div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Section -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40">Icon</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Material</th>
                        <th>AC</th>
                        <th>Safe</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($accessories)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No accessories found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($accessories as $item): 
                            // Determine if item has an image and is available in-game
                            $isAvailable = true; // Default to true for client-side
                            
                            // Add onerror attribute to img that will set a flag to mark this as unavailable
                            $itemId = $item['item_id'];
                        ?>
                            <tr id="accessory-<?= $itemId ?>" onclick="window.location.href='accessories-detail.php?id=<?= $itemId ?>'" class="armor-row">
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $item['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars(cleanItemName($item['desc_en'])) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'; document.getElementById('accessory-<?= $itemId ?>').classList.add('unavailable-item'); document.getElementById('status-<?= $itemId ?>').innerHTML = '<span class=\'badge badge-danger\'>Not In-Game</span>';">
                                </td>
                                <td><?= htmlspecialchars(cleanItemName($item['desc_en'])) ?></td>
                                <td><?= formatArmorType($item['type']) ?></td>
                                <td><?= formatMaterial($item['material_name']) ?></td>
                                <td><?= $item['ac'] ?></td>
                                <td>+<?= $item['safenchant'] ?></td>
                                <td><span class="badge <?= getGradeBadgeClass($item['itemGrade']) ?>"><?= formatGrade($item['itemGrade']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
            <div class="pagination">
                <span class="pagination-info">
                    Showing <?= $offset + 1 ?>-<?= min($offset + $itemsPerPage, $totalItems) ?> of <?= $totalItems ?> items
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

<style>
/* Additional Styles */
.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.item-card {
    background-color: var(--primary);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: var(--text);
}

.item-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}

.item-image {
    padding: 1rem;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: var(--secondary);
}

.item-icon {
    width: 64px;
    height: 64px;
    object-fit: contain;
}

.item-info {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.item-name {
    font-size: 1.1rem;
    margin: 0;
    color: var(--text);
}

.item-type {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

.item-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.9rem;
}

.stat-value {
    background-color: var(--secondary);
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge-normal { background-color: #6c757d; }
.badge-advanc { background-color: #28a745; }
.badge-rare { background-color: #17a2b8; }
.badge-hero { background-color: #ffc107; color: #000; }
.badge-legend { background-color: #dc3545; }
.badge-myth { background-color: #6f42c1; }
.badge-only { background-color: #fd7e14; }

.unavailable-item {
    opacity: 0.5;
    filter: grayscale(100%);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .items-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 576px) {
    .items-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';
?> 