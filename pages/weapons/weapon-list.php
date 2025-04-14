<?php
/**
 * Weapons listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Weapons';
$pageDescription = 'Browse all weapons in L1J Remastered including swords, daggers, bows, and more.';

// Include header
require_once '../../includes/header.php';

// Include weapons functions
require_once '../../includes/weapons-functions.php';

// Get database instance
$db = Database::getInstance();

// Build query
$query = "SELECT w.item_id, w.desc_en, w.type, 
                 SUBSTRING_INDEX(w.material, '(', 1) as material_name, 
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

// Execute query to get all results for filtering
$allWeapons = $db->getRows($query, $params);

// Filter for available items if requested
$filteredWeapons = $allWeapons;
// Default to showing only available items unless explicitly set to "all"
if(!isset($_GET['availability']) || $_GET['availability'] !== 'all') {
    // By default or if explicitly set to "available", filter out unavailable items
    $filteredWeapons = array_filter($allWeapons, function($weapon) {
        return isItemAvailable($weapon['iconId'], SITE_URL);
    });
}

// Calculate pagination based on filtered results
$totalItems = count($filteredWeapons);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of items for this page
$weapons = array_slice($filteredWeapons, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/weapons-hero.jpg');">
    <div class="container">
        <h1>Weapons Database</h1>
        <p>Explore the complete collection of weapons in L1J Remastered. From common blades to legendary artifacts, find detailed information about all weapons in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search weapons by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
						<label for="type">Weapon Type:</label>
						<select name="type" id="type" class="form-control">
							<option value="">All Types</option>
							<option value="SWORD" <?= isset($_GET['type']) && $_GET['type'] === 'SWORD' ? 'selected' : '' ?>>Sword</option>
							<option value="DAGGER" <?= isset($_GET['type']) && $_GET['type'] === 'DAGGER' ? 'selected' : '' ?>>Dagger</option>
							<option value="TOHAND_SWORD" <?= isset($_GET['type']) && $_GET['type'] === 'TOHAND_SWORD' ? 'selected' : '' ?>>Sword (2H)</option>
							<option value="BOW" <?= isset($_GET['type']) && $_GET['type'] === 'BOW' ? 'selected' : '' ?>>Bow (2H)</option>
							<option value="SPEAR" <?= isset($_GET['type']) && $_GET['type'] === 'SPEAR' ? 'selected' : '' ?>>Spear (2H)</option>
							<option value="BLUNT" <?= isset($_GET['type']) && $_GET['type'] === 'BLUNT' ? 'selected' : '' ?>>Blunt</option>
							<option value="STAFF" <?= isset($_GET['type']) && $_GET['type'] === 'STAFF' ? 'selected' : '' ?>>Staff</option>
							<option value="GAUNTLET" <?= isset($_GET['type']) && $_GET['type'] === 'GAUNTLET' ? 'selected' : '' ?>>Gauntlet</option>
							<option value="CLAW" <?= isset($_GET['type']) && $_GET['type'] === 'CLAW' ? 'selected' : '' ?>>Claw</option>
							<option value="EDORYU" <?= isset($_GET['type']) && $_GET['type'] === 'EDORYU' ? 'selected' : '' ?>>Edoryu</option>
							<option value="SINGLE_BOW" <?= isset($_GET['type']) && $_GET['type'] === 'SINGLE_BOW' ? 'selected' : '' ?>>Bow</option>
							<option value="SINGLE_SPEAR" <?= isset($_GET['type']) && $_GET['type'] === 'SINGLE_SPEAR' ? 'selected' : '' ?>>Spear</option>
							<option value="TOHAND_BLUNT" <?= isset($_GET['type']) && $_GET['type'] === 'TOHAND_BLUNT' ? 'selected' : '' ?>>Blunt (2H)</option>
							<option value="TOHAND_STAFF" <?= isset($_GET['type']) && $_GET['type'] === 'TOHAND_STAFF' ? 'selected' : '' ?>>Staff (2H)</option>
							<option value="KEYRINGK" <?= isset($_GET['type']) && $_GET['type'] === 'KEYRINGK' ? 'selected' : '' ?>>Keyringk</option>
							<option value="CHAINSWORD" <?= isset($_GET['type']) && $_GET['type'] === 'CHAINSWORD' ? 'selected' : '' ?>>Chain Sword</option>
						</select>
					</div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Weapon List -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="40">Icon</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Material</th>
                        <th>Dmg(S)</th>
                        <th>Dmg(L)</th>
                        <th>Safe</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($weapons)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No weapons found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($weapons as $weapon): 
                            // Determine if item has an image and is available in-game
                            $isAvailable = true; // Default to true for client-side
                            
                            // Add onerror attribute to img that will set a flag to mark this as unavailable
                            $weaponId = $weapon['item_id'];
                        ?>
                            <tr id="weapon-<?= $weaponId ?>" onclick="window.location.href='weapon-detail.php?id=<?= $weaponId ?>'" class="weapon-row">
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/items/<?= $weapon['iconId'] ?>.png" 
                                         alt="<?= htmlspecialchars(cleanItemName($weapon['desc_en'])) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/items/default.png'; document.getElementById('weapon-<?= $weaponId ?>').classList.add('unavailable-item'); document.getElementById('status-<?= $weaponId ?>').innerHTML = '<span class=\'badge badge-danger\'>Not In-Game</span>';">
                                </td>
                                <td><?= htmlspecialchars(cleanItemName($weapon['desc_en'])) ?></td>
                                <td><?= formatWeaponType($weapon['type']) ?></td>
                                <td><?= formatMaterial($weapon['material_name']) ?></td>
                                <td><?= $weapon['dmg_small'] ?></td>
                                <td><?= $weapon['dmg_large'] ?></td>
                                <td>+<?= $weapon['safenchant'] ?></td>
                                <td><span class="badge <?= getGradeBadgeClass($weapon['itemGrade']) ?>"><?= formatGrade($weapon['itemGrade']) ?></span></td>
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
                    <?php if($page > 1): ?>
                        <a href="<?= getPaginationUrl(1) ?>" class="pagination-link">«« First</a>
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
                    
                    <?php if($page < $totalPages): ?>
                        <a href="<?= getPaginationUrl($page + 1) ?>" class="pagination-link">Next »</a>
                        <a href="<?= getPaginationUrl($totalPages) ?>" class="pagination-link">Last »»</a>
                    <?php else: ?>
                        <span class="pagination-link disabled">Next »</span>
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