<?php
/**
 * Items listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Items';
$pageDescription = 'Browse all items in L1J Remastered including potions, scrolls, and materials.';

// Include header
require_once '../../includes/header.php';

// Include item functions
require_once '../../includes/functions.php';

// Get database instance
$db = Database::getInstance();

// Build query
$query = "SELECT e.item_id, e.desc_kr, e.desc_en, e.item_type, 
                 e.weight, e.itemGrade, e.iconId
          FROM etcitem e
          WHERE e.item_type IN ('ARROW', 'FOOD', 'LIGHT', 'MATERIAL', 'OTHER', 'TREASURE_BOX', 'WAND', 'SPELL_BOOK', 'POTION')";

// Exclude MAGICDOLL from OTHER type
$query .= " AND NOT (e.item_type = 'OTHER' AND e.use_type = 'MAGICDOLL')";

// Handle search and filters
$whereConditions = [];
$params = [];

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "(e.desc_en LIKE ? OR e.desc_kr LIKE ?)";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $whereConditions[] = "e.item_type = ?";
    $params[] = $_GET['type'];
}

if(isset($_GET['grade']) && !empty($_GET['grade'])) {
    $whereConditions[] = "e.itemGrade = ?";
    $params[] = $_GET['grade'];
}

// Add where conditions to query if any
if(!empty($whereConditions)) {
    $query .= " AND " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY 
    CASE 
        WHEN e.itemGrade = 'NORMAL' THEN 1
        WHEN e.itemGrade = 'ADVANC' THEN 2
        WHEN e.itemGrade = 'RARE' THEN 3
        WHEN e.itemGrade = 'HERO' THEN 4
        WHEN e.itemGrade = 'LEGEND' THEN 5
        WHEN e.itemGrade = 'MYTH' THEN 6
        WHEN e.itemGrade = 'ONLY' THEN 7
        ELSE 8
    END, e.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20;
$offset = ($page - 1) * $itemsPerPage;

// Get total count for pagination
$countQuery = "SELECT COUNT(*) FROM (" . $query . ") as filtered_items";
$totalItems = $db->getColumn($countQuery, $params);
$totalPages = ceil($totalItems / $itemsPerPage);

// Add limit and offset to the main query
$query .= " LIMIT ? OFFSET ?";
$params[] = $itemsPerPage;
$params[] = $offset;

// Execute query to get paginated results
$items = $db->getRows($query, $params);

// Filter for available items if requested
$filteredItems = $items;
// Default to showing only available items unless explicitly set to "all"
if(!isset($_GET['availability']) || $_GET['availability'] !== 'all') {
    // By default or if explicitly set to "available", filter out unavailable items
    $filteredItems = array_filter($items, function($item) {
        return isItemAvailable($item['iconId'], SITE_URL);
    });
}

// Calculate pagination based on filtered results
$totalItems = count($filteredItems);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of items for this page
$items = array_slice($filteredItems, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

// Get item type counts for the filter
$itemTypeCounts = [];
$allowedTypes = ['ARROW', 'FOOD', 'LIGHT', 'MATERIAL', 'OTHER', 'TREASURE_BOX', 'WAND', 'SPELL_BOOK', 'POTION'];
foreach ($allowedTypes as $type) {
    $countQuery = "SELECT COUNT(*) FROM etcitem WHERE item_type = ?";
    if ($type === 'OTHER') {
        $countQuery .= " AND use_type != 'MAGICDOLL'";
    }
    $itemTypeCounts[$type] = $db->getColumn($countQuery, [$type]);
}
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/items-hero.jpg');">
    <div class="container">
        <h1>Items Database</h1>
        <p>Explore the complete collection of items in L1J Remastered. Browse potions, scrolls, materials and more.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search items by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
                        <label for="type">Item Type:</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="ARROW" <?= isset($_GET['type']) && $_GET['type'] === 'ARROW' ? 'selected' : '' ?>>Arrow (<?= $itemTypeCounts['ARROW'] ?>)</option>
                            <option value="FOOD" <?= isset($_GET['type']) && $_GET['type'] === 'FOOD' ? 'selected' : '' ?>>Food (<?= $itemTypeCounts['FOOD'] ?>)</option>
                            <option value="LIGHT" <?= isset($_GET['type']) && $_GET['type'] === 'LIGHT' ? 'selected' : '' ?>>Light (<?= $itemTypeCounts['LIGHT'] ?>)</option>
                            <option value="MATERIAL" <?= isset($_GET['type']) && $_GET['type'] === 'MATERIAL' ? 'selected' : '' ?>>Material (<?= $itemTypeCounts['MATERIAL'] ?>)</option>
                            <option value="OTHER" <?= isset($_GET['type']) && $_GET['type'] === 'OTHER' ? 'selected' : '' ?>>Other (<?= $itemTypeCounts['OTHER'] ?>)</option>
                            <option value="TREASURE_BOX" <?= isset($_GET['type']) && $_GET['type'] === 'TREASURE_BOX' ? 'selected' : '' ?>>Treasure Box (<?= $itemTypeCounts['TREASURE_BOX'] ?>)</option>
                            <option value="WAND" <?= isset($_GET['type']) && $_GET['type'] === 'WAND' ? 'selected' : '' ?>>Wand (<?= $itemTypeCounts['WAND'] ?>)</option>
                            <option value="SPELL_BOOK" <?= isset($_GET['type']) && $_GET['type'] === 'SPELL_BOOK' ? 'selected' : '' ?>>Spell Book (<?= $itemTypeCounts['SPELL_BOOK'] ?>)</option>
                            <option value="POTION" <?= isset($_GET['type']) && $_GET['type'] === 'POTION' ? 'selected' : '' ?>>Potion (<?= $itemTypeCounts['POTION'] ?>)</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Item List -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40">Icon</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Weight</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($items)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No items found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($items as $item): 
                            // Add onerror attribute to img that will set a flag to mark this as unavailable
                            $itemId = $item['item_id'];
                        ?>
                            <tr id="item-<?= $itemId ?>" onclick="window.location.href='detail.php?id=<?= $itemId ?>'" class="item-row">
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $item['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars($item['desc_en']) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'; document.getElementById('item-<?= $itemId ?>').classList.add('unavailable-item');">
                                </td>
                                <td><?= htmlspecialchars(cleanItemName($item['desc_en'])) ?></td>
                                <td><?= formatItemType($item['item_type']) ?></td>
                                <td><?= $item['weight'] / 1000 ?></td>
                                <td>
                                    <?php if (!empty($item['itemGrade']) && $item['itemGrade'] != 'NORMAL'): ?>
                                        <span class="badge <?= getGradeBadgeClass($item['itemGrade']) ?>">
                                            <?= formatGrade($item['itemGrade']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-normal">Normal</span>
                                    <?php endif; ?>
                                </td>
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
.unavailable-item {
    opacity: 0.6;
    background-color: rgba(244, 67, 54, 0.05);
}

.item-row {
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.item-row:hover {
    background-color: var(--secondary);
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';

/**
 * Helper function to format item type for display
 */
function formatItemType($type) {
    $type = str_replace('_', ' ', $type);
    return ucwords(strtolower($type));
}

// Helper function to check if an item property is displayable
function isPropertyDisplayable($item, $property) {
    return isset($item[$property]) && $item[$property] != 0 && $item[$property] != 'NONE' && $item[$property] != '';
}

/**
 * Function to format use type for display
 */
function formatUseType($useType) {
    $useType = str_replace('_', ' ', $useType);
    return ucwords(strtolower($useType));
}

?>
