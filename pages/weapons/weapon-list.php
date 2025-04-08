<?php
/**
 * Weapons listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Weapons';
$pageDescription = 'Browse all weapons in L1J Remastered including swords, daggers, bows, and more.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Build query
$query = "SELECT w.item_id, w.desc_en, w.type, 
                 SUBSTRING_BEFORE(w.material, '(') as material_name, 
                 w.dmg_small, w.dmg_large, w.safenchant, w.itemGrade, 
                 w.iconId 
          FROM weapon w";

// Handle search and filters
$whereConditions = [];
$params = [];

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "w.desc_en LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

if(isset($_GET['type']) && !empty($_GET['type'])) {
    $whereConditions[] = "w.type = ?";
    $params[] = $_GET['type'];
}

if(isset($_GET['grade']) && !empty($_GET['grade'])) {
    $whereConditions[] = "w.itemGrade = ?";
    $params[] = $_GET['grade'];
}

if(isset($_GET['material']) && !empty($_GET['material'])) {
    $whereConditions[] = "w.material LIKE ?";
    $params[] = '%' . $_GET['material'] . '%';
}

// Add where conditions to query if any
if(!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY 
    CASE 
        WHEN w.itemGrade = 'ONLY' THEN 1
        WHEN w.itemGrade = 'MYTH' THEN 2
        WHEN w.itemGrade = 'LEGEND' THEN 3
        WHEN w.itemGrade = 'HERO' THEN 4
        WHEN w.itemGrade = 'RARE' THEN 5
        WHEN w.itemGrade = 'ADVANC' THEN 6
        WHEN w.itemGrade = 'NORMAL' THEN 7
        ELSE 8
    END, w.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20;
$offset = ($page - 1) * $itemsPerPage;

// Get total count for pagination
$countQuery = str_replace("SELECT w.item_id, w.desc_en, w.type, 
                 SUBSTRING_BEFORE(w.material, '(') as material_name, 
                 w.dmg_small, w.dmg_large, w.safenchant, w.itemGrade, 
                 w.iconId", "SELECT COUNT(*)", $query);
$totalItems = $db->getColumn($countQuery, $params);
$totalPages = ceil($totalItems / $itemsPerPage);

// Add limit to query
$query .= " LIMIT $offset, $itemsPerPage";

// Execute query
$weapons = $db->getResults($query, $params);

// Helper function to format material name
function formatMaterial($material) {
    // Remove Korean part if exists
    $material = trim($material);
    $material = strtoupper($material);
    return $material;
}

// Helper function to get badge class based on item grade
function getGradeBadgeClass($grade) {
    switch($grade) {
        case 'ONLY':
            return 'badge-only';
        case 'MYTH':
            return 'badge-myth';
        case 'LEGEND':
            return 'badge-legend';
        case 'HERO':
            return 'badge-hero';
        case 'RARE':
            return 'badge-rare';
        default:
            return 'badge-normal';
    }
}
?>

<div class="container">
    <section class="page-section">
        <h1>Weapons Database</h1>
        
        <!-- Search and Filters -->
        <div class="search-container">
            <form action="weapons.php" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search weapons by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn">Search</button>
            </form>
            
            <div class="filters">
                <div class="filter-group">
                    <label for="type">Type:</label>
                    <select name="type" id="type">
                        <option value="">All Types</option>
                        <option value="SWORD" <?= isset($_GET['type']) && $_GET['type'] === 'SWORD' ? 'selected' : '' ?>>Sword</option>
                        <option value="DAGGER" <?= isset($_GET['type']) && $_GET['type'] === 'DAGGER' ? 'selected' : '' ?>>Dagger</option>
                        <option value="TOHAND_SWORD" <?= isset($_GET['type']) && $_GET['type'] === 'TOHAND_SWORD' ? 'selected' : '' ?>>Two-Hand Sword</option>
                        <option value="BOW" <?= isset($_GET['type']) && $_GET['type'] === 'BOW' ? 'selected' : '' ?>>Bow</option>
                        <option value="SPEAR" <?= isset($_GET['type']) && $_GET['type'] === 'SPEAR' ? 'selected' : '' ?>>Spear</option>
                        <option value="BLUNT" <?= isset($_GET['type']) && $_GET['type'] === 'BLUNT' ? 'selected' : '' ?>>Blunt</option>
                        <option value="STAFF" <?= isset($_GET['type']) && $_GET['type'] === 'STAFF' ? 'selected' : '' ?>>Staff</option>
                        <option value="CLAW" <?= isset($_GET['type']) && $_GET['type'] === 'CLAW' ? 'selected' : '' ?>>Claw</option>
                        <option value="EDORYU" <?= isset($_GET['type']) && $_GET['type'] === 'EDORYU' ? 'selected' : '' ?>>Edoryu</option>
                        <option value="GAUNTLET" <?= isset($_GET['type']) && $_GET['type'] === 'GAUNTLET' ? 'selected' : '' ?>>Gauntlet</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="grade">Grade:</label>
                    <select name="grade" id="grade">
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
                
                <div class="filter-group">
                    <label for="material">Material:</label>
                    <select name="material" id="material">
                        <option value="">All Materials</option>
                        <option value="IRON" <?= isset($_GET['material']) && $_GET['material'] === 'IRON' ? 'selected' : '' ?>>Iron</option>
                        <option value="WOOD" <?= isset($_GET['material']) && $_GET['material'] === 'WOOD' ? 'selected' : '' ?>>Wood</option>
                        <option value="MITHRIL" <?= isset($_GET['material']) && $_GET['material'] === 'MITHRIL' ? 'selected' : '' ?>>Mithril</option>
                        <option value="DRAGON_HIDE" <?= isset($_GET['material']) && $_GET['material'] === 'DRAGON_HIDE' ? 'selected' : '' ?>>Dragon Hide</option>
                        <option value="ORIHARUKON" <?= isset($_GET['material']) && $_GET['material'] === 'ORIHARUKON' ? 'selected' : '' ?>>Oriharukon</option>
                        <option value="DRANIUM" <?= isset($_GET['material']) && $_GET['material'] === 'DRANIUM' ? 'selected' : '' ?>>Dranium</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-secondary">Apply Filters</button>
            </div>
        </div>
        
        <!-- Weapon List -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="60"></th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Material</th>
                        <th>Damage (S/L)</th>
                        <th>Safe Enchant</th>
                        <th>Grade</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($weapons)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No weapons found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($weapons as $weapon): ?>
                            <tr>
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars($weapon['desc_en']) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'">
                                </td>
                                <td><?= htmlspecialchars($weapon['desc_en']) ?></td>
                                <td><?= htmlspecialchars($weapon['type']) ?></td>
                                <td><?= htmlspecialchars(formatMaterial($weapon['material_name'])) ?></td>
                                <td><?= $weapon['dmg_small'] ?>/<?= $weapon['dmg_large'] ?></td>
                                <td>+<?= $weapon['safenchant'] ?></td>
                                <td><span class="badge <?= getGradeBadgeClass($weapon['itemGrade']) ?>"><?= $weapon['itemGrade'] ?></span></td>
                                <td><a href="weapon-detail.php?id=<?= $weapon['item_id'] ?>" class="btn-small">Details</a></td>
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
                    // Previous page link
                    if($page > 1):
                        $prevPageUrl = http_build_query(array_merge($_GET, ['page' => $page - 1]));
                    ?>
                        <a href="?<?= $prevPageUrl ?>" class="pagination-link">«</a>
                    <?php else: ?>
                        <a href="#" class="pagination-link disabled">«</a>
                    <?php endif; ?>
                    
                    <?php
                    // Page links
                    $startPage = max(1, min($page - 2, $totalPages - 4));
                    $endPage = min($startPage + 4, $totalPages);
                    
                    for($i = $startPage; $i <= $endPage; $i++):
                        $pageUrl = http_build_query(array_merge($_GET, ['page' => $i]));
                        $isActive = $i === $page;
                    ?>
                        <a href="?<?= $pageUrl ?>" class="pagination-link <?= $isActive ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php
                    // Next page link
                    if($page < $totalPages):
                        $nextPageUrl = http_build_query(array_merge($_GET, ['page' => $page + 1]));
                    ?>
                        <a href="?<?= $nextPageUrl ?>" class="pagination-link">»</a>
                    <?php else: ?>
                        <a href="#" class="pagination-link disabled">»</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
// Include footer
require_once '../../includes/footer.php';
?>