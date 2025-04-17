<?php
/**
 * Polymorph listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Polymorphs';
$pageDescription = 'Browse all polymorph transformations in L1J Remastered.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Build query
$query = "SELECT p.* FROM polymorphs p";

// Handle search and filters
$whereConditions = [];
$params = [];

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "p.name LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

if(isset($_GET['minlevel']) && !empty($_GET['minlevel'])) {
    $whereConditions[] = "p.minlevel >= ?";
    $params[] = intval($_GET['minlevel']);
}

if(isset($_GET['skilluse']) && !empty($_GET['skilluse'])) {
    $whereConditions[] = "p.isSkillUse = ?";
    $params[] = intval($_GET['skilluse']);
}

if(isset($_GET['bonuspvp']) && !empty($_GET['bonuspvp'])) {
    $whereConditions[] = "p.bonusPVP = ?";
    $params[] = $_GET['bonuspvp'];
}

// Add where conditions to query if any
if(!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY p.minlevel ASC, p.name ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20;
$offset = ($page - 1) * $itemsPerPage;

// Execute query
$polymorphs = $db->getRows($query, $params);

// Calculate pagination
$totalItems = count($polymorphs);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of items for this page
$polymorphs = array_slice($polymorphs, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

// Helper function to generate pagination URL with all current filters
function getPolymorphPaginationUrl($pageNum) {
    $params = ['page' => $pageNum];
    
    if(isset($_GET['q'])) $params['q'] = $_GET['q'];
    if(isset($_GET['minlevel'])) $params['minlevel'] = $_GET['minlevel'];
    if(isset($_GET['skilluse'])) $params['skilluse'] = $_GET['skilluse'];
    if(isset($_GET['bonuspvp'])) $params['bonuspvp'] = $_GET['bonuspvp'];
    
    return '?' . http_build_query($params);
}
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/polymorph-hero.jpg');">
    <div class="container">
        <h1>Polymorph Database</h1>
        <p>Explore all available polymorph transformations in L1J Remastered. Transform into various creatures and gain unique abilities.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search polymorphs by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="minlevel">Minimum Level:</label>
                        <select name="minlevel" id="minlevel" class="form-control">
                            <option value="">All Levels</option>
                            <?php for($i = 1; $i <= 85; $i += 5): ?>
                                <option value="<?= $i ?>" <?= isset($_GET['minlevel']) && intval($_GET['minlevel']) === $i ? 'selected' : '' ?>>Level <?= $i ?>+</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="skilluse">Skill Use:</label>
                        <select name="skilluse" id="skilluse" class="form-control">
                            <option value="">All</option>
                            <option value="1" <?= isset($_GET['skilluse']) && $_GET['skilluse'] === '1' ? 'selected' : '' ?>>Can Use Skills</option>
                            <option value="0" <?= isset($_GET['skilluse']) && $_GET['skilluse'] === '0' ? 'selected' : '' ?>>Cannot Use Skills</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="bonuspvp">PvP Bonus:</label>
                        <select name="bonuspvp" id="bonuspvp" class="form-control">
                            <option value="">All</option>
                            <option value="true" <?= isset($_GET['bonuspvp']) && $_GET['bonuspvp'] === 'true' ? 'selected' : '' ?>>Has PvP Bonus</option>
                            <option value="false" <?= isset($_GET['bonuspvp']) && $_GET['bonuspvp'] === 'false' ? 'selected' : '' ?>>No PvP Bonus</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Polymorph List -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Skills</th>
                        <th>Weapon Equip</th>
                        <th>Armor Equip</th>
                        <th>PvP Bonus</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($polymorphs)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No polymorphs found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($polymorphs as $polymorph): ?>
                            <tr onclick="window.location.href='polymorph-detail.php?id=<?= $polymorph['id'] ?>'" class="polymorph-row">
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/image/poly/<?= $polymorph['polyid'] ?>.gif" 
                                         alt="<?= htmlspecialchars($polymorph['name']) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/image/poly/<?= $polymorph['polyid'] ?>.png'; this.onerror=function(){this.src='<?= SITE_URL ?>/assets/image/poly/default.png';}">
                                </td>
                                <td><?= htmlspecialchars($polymorph['name']) ?></td>
                                <td><?= $polymorph['minlevel'] ?>+</td>
                                <td><?= $polymorph['isSkillUse'] ? 'Yes' : 'No' ?></td>
                                <td><?= $polymorph['weaponequip'] ?: '-' ?></td>
                                <td><?= $polymorph['armorequip'] ?: '-' ?></td>
                                <td><?= $polymorph['bonusPVP'] === 'true' ? '<span class="badge badge-pvp">Yes</span>' : 'No' ?></td>
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
                        <a href="<?= getPolymorphPaginationUrl(1) ?>" class="pagination-link">«« First</a>
                        <a href="<?= getPolymorphPaginationUrl($page - 1) ?>" class="pagination-link">« Prev</a>
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
                        <a href="<?= getPolymorphPaginationUrl($i) ?>" class="pagination-link <?= $isActive ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php
                    // Display ellipsis for end if needed
                    if ($endPage < $totalPages):
                    ?>
                        <span class="pagination-ellipsis">...</span>
                    <?php endif; ?>
                    
                    <?php if($page < $totalPages): ?>
                        <a href="<?= getPolymorphPaginationUrl($page + 1) ?>" class="pagination-link">Next »</a>
                        <a href="<?= getPolymorphPaginationUrl($totalPages) ?>" class="pagination-link">Last »»</a>
                    <?php else: ?>
                        <span class="pagination-link disabled">Next »</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php require_once '../../includes/footer.php'; ?> 