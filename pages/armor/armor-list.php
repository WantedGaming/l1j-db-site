<?php
/**
 * Armor listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Armor';
$pageDescription = 'Browse all armor in L1J Remastered including helmets, armors, cloaks, and more.';

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
          WHERE a.type IN ('HELMET', 'ARMOR', 'T_SHIRT', 'CLOAK', 'GLOVE', 'BOOTS', 'SHIELD', 'GARDER', 'PAIR', 'SHOULDER')";

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
        WHEN a.itemGrade = 'NORMAL' THEN 1
        WHEN a.itemGrade = 'ADVANC' THEN 2
        WHEN a.itemGrade = 'RARE' THEN 3
        WHEN a.itemGrade = 'HERO' THEN 4
        WHEN a.itemGrade = 'LEGEND' THEN 5
        WHEN a.itemGrade = 'MYTH' THEN 6
        WHEN a.itemGrade = 'ONLY' THEN 7
        ELSE 8
    END, a.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20;
$offset = ($page - 1) * $itemsPerPage;

// Execute query to get all results for filtering
$allArmor = $db->getRows($query, $params);

// Filter for available items if requested
$filteredArmor = $allArmor;
// Default to showing only available items unless explicitly set to "all"
if(!isset($_GET['availability']) || $_GET['availability'] !== 'all') {
    // By default or if explicitly set to "available", filter out unavailable items
    $filteredArmor = array_filter($allArmor, function($armor) {
        return isItemAvailable($armor['iconId'], SITE_URL);
    });
}

// Calculate pagination based on filtered results
$totalItems = count($filteredArmor);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of armor for this page
$armor = array_slice($filteredArmor, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/weapons-hero.jpg');">
    <div class="container">
        <h1>Armor Database</h1>
        <p>Explore the complete collection of armor in L1J Remastered. From common equipment to legendary artifacts, find detailed information about all armor in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search armor by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
                            <option value="IRON" <?= isset($_GET['material']) && $_GET['material'] === 'IRON' ? 'selected' : '' ?>>Iron</option>
                            <option value="WOOD" <?= isset($_GET['material']) && $_GET['material'] === 'WOOD' ? 'selected' : '' ?>>Wood</option>
                            <option value="MITHRIL" <?= isset($_GET['material']) && $_GET['material'] === 'MITHRIL' ? 'selected' : '' ?>>Mithril</option>
                            <option value="DRAGON_HIDE" <?= isset($_GET['material']) && $_GET['material'] === 'DRAGON_HIDE' ? 'selected' : '' ?>>Dragon Hide</option>
                            <option value="ORIHARUKON" <?= isset($_GET['material']) && $_GET['material'] === 'ORIHARUKON' ? 'selected' : '' ?>>Oriharukon</option>
                            <option value="DRANIUM" <?= isset($_GET['material']) && $_GET['material'] === 'DRANIUM' ? 'selected' : '' ?>>Dranium</option>
                            <option value="SILVER" <?= isset($_GET['material']) && $_GET['material'] === 'SILVER' ? 'selected' : '' ?>>Silver</option>
                            <option value="STEEL" <?= isset($_GET['material']) && $_GET['material'] === 'STEEL' ? 'selected' : '' ?>>Steel</option>
                            <option value="CRYSTAL" <?= isset($_GET['material']) && $_GET['material'] === 'CRYSTAL' ? 'selected' : '' ?>>Crystal</option>
                            <option value="COPPER" <?= isset($_GET['material']) && $_GET['material'] === 'COPPER' ? 'selected' : '' ?>>Copper</option>
                            <option value="GOLD" <?= isset($_GET['material']) && $_GET['material'] === 'GOLD' ? 'selected' : '' ?>>Gold</option>
                            <option value="BONE" <?= isset($_GET['material']) && $_GET['material'] === 'BONE' ? 'selected' : '' ?>>Bone</option>
                            <option value="LEATHER" <?= isset($_GET['material']) && $_GET['material'] === 'LEATHER' ? 'selected' : '' ?>>Leather</option>
                            <option value="CLOTH" <?= isset($_GET['material']) && $_GET['material'] === 'CLOTH' ? 'selected' : '' ?>>Cloth</option>
                            <option value="LIQUID" <?= isset($_GET['material']) && $_GET['material'] === 'LIQUID' ? 'selected' : '' ?>>Liquid</option>
                            <option value="PAPER" <?= isset($_GET['material']) && $_GET['material'] === 'PAPER' ? 'selected' : '' ?>>Paper</option>
                            <option value="STONE" <?= isset($_GET['material']) && $_GET['material'] === 'STONE' ? 'selected' : '' ?>>Stone</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
						<label for="type">Armor Type:</label>
						<select name="type" id="type" class="form-control">
							<option value="">All Types</option>
							<option value="HELMET" <?= isset($_GET['type']) && $_GET['type'] === 'HELMET' ? 'selected' : '' ?>>Helmet</option>
							<option value="ARMOR" <?= isset($_GET['type']) && $_GET['type'] === 'ARMOR' ? 'selected' : '' ?>>Armor</option>
							<option value="T_SHIRT" <?= isset($_GET['type']) && $_GET['type'] === 'T_SHIRT' ? 'selected' : '' ?>>T-Shirt</option>
							<option value="CLOAK" <?= isset($_GET['type']) && $_GET['type'] === 'CLOAK' ? 'selected' : '' ?>>Cloak</option>
							<option value="GLOVE" <?= isset($_GET['type']) && $_GET['type'] === 'GLOVE' ? 'selected' : '' ?>>Glove</option>
							<option value="BOOTS" <?= isset($_GET['type']) && $_GET['type'] === 'BOOTS' ? 'selected' : '' ?>>Boots</option>
							<option value="SHIELD" <?= isset($_GET['type']) && $_GET['type'] === 'SHIELD' ? 'selected' : '' ?>>Shield</option>
                            <option value="GARDER" <?= isset($_GET['type']) && $_GET['type'] === 'GARDER' ? 'selected' : '' ?>>Garder</option>
                            <option value="PAIR" <?= isset($_GET['type']) && $_GET['type'] === 'PAIR' ? 'selected' : '' ?>>Pair</option>
                            <option value="SHOULDER" <?= isset($_GET['type']) && $_GET['type'] === 'SHOULDER' ? 'selected' : '' ?>>Shoulder</option>
						</select>
					</div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Armor List -->
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
                    <?php if(empty($armor)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No armor found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($armor as $armor): 
                            // Determine if item has an image and is available in-game
                            $isAvailable = true; // Default to true for client-side
                            
                            // Add onerror attribute to img that will set a flag to mark this as unavailable
                            $armorId = $armor['item_id'];
                        ?>
                            <tr id="armor-<?= $armorId ?>" onclick="window.location.href='armor-detail.php?id=<?= $armorId ?>'" class="armor-row">
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $armor['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars(cleanItemName($armor['desc_en'])) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'; document.getElementById('armor-<?= $armorId ?>').classList.add('unavailable-item'); document.getElementById('status-<?= $armorId ?>').innerHTML = '<span class=\'badge badge-danger\'>Not In-Game</span>';">
                                </td>
                                <td><?= htmlspecialchars(cleanItemName($armor['desc_en'])) ?></td>
                                <td><?= formatArmorType($armor['type']) ?></td>
                                <td><?= formatMaterial($armor['material_name']) ?></td>
                                <td><?= $armor['ac'] ?></td>
                                <td>+<?= $armor['safenchant'] ?></td>
                                <td><span class="badge <?= getGradeBadgeClass($armor['itemGrade']) ?>"><?= formatGrade($armor['itemGrade']) ?></span></td>
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

<?php
// Include footer
require_once '../../includes/footer.php';
?>
