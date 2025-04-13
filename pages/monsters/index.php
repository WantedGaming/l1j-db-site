<?php
/**
 * Monster listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Monsters';
$pageDescription = 'Browse all monsters in L1J Remastered including regular monsters, bosses, and special creatures.';

// Include header
require_once '../../includes/header.php';
// Include drop calculation functions
require_once '../../includes/functions.php';

// Get database instance
$db = Database::getInstance();

// Build base query
$query = "SELECT n.npcid, n.desc_en, n.desc_kr, n.lvl, n.spriteId, n.is_bossmonster, 
                 n.exp, n.impl, n.hp, n.undead
          FROM npc n";

// Handle search and filters
$whereConditions = [];
$params = [];

// Base condition for monsters
$whereConditions[] = "(n.impl LIKE '%L1Monster%' OR n.impl LIKE '%L1Doppelganger%')";

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "(n.desc_en LIKE ? OR n.desc_kr LIKE ?)";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}

if(isset($_GET['level_min']) && !empty($_GET['level_min'])) {
    $whereConditions[] = "n.lvl >= ?";
    $params[] = intval($_GET['level_min']);
}

if(isset($_GET['level_max']) && !empty($_GET['level_max'])) {
    $whereConditions[] = "n.lvl <= ?";
    $params[] = intval($_GET['level_max']);
}

if(isset($_GET['boss']) && $_GET['boss'] === 'true') {
    $whereConditions[] = "n.is_bossmonster = 'true'";
}

if(isset($_GET['undead']) && !empty($_GET['undead']) && $_GET['undead'] !== 'NONE') {
    $whereConditions[] = "n.undead = ?";
    $params[] = $_GET['undead'];
}

// Add WHERE clause if we have any conditions
if(!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add order by
$query .= " ORDER BY n.lvl ASC, n.desc_en ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20; // Changed from 20 to 10
$offset = ($page - 1) * $itemsPerPage;

// Execute query to get all results for filtering
$allMonsters = $db->getRows($query, $params);

// Calculate pagination
$totalItems = count($allMonsters);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of items for this page
$monsters = array_slice($allMonsters, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

// Create a function to build pagination URLs
function getPaginationUrl($newPage) {
    $params = $_GET;
    $params['page'] = $newPage;
    return htmlspecialchars($_SERVER['PHP_SELF']) . '?' . http_build_query($params);
}

/**
 * Get monster image path for display
 */
function get_monster_image($spriteId) {
    // Base URL path for images (for HTML src attribute)
    $baseUrl = SITE_URL . '/assets/img/monsters/';
    
    // For debugging - let's see what paths we're checking
    $debugInfo = '';
    
    // Simplified approach - just return the URL and let the browser handle fallback
    return $baseUrl . "ms{$spriteId}.png";
}

// Helper function to get undead type display name
function formatUndeadType($undeadType) {
    switch($undeadType) {
        case 'UNDEAD':
            return 'Undead';
        case 'DEMON':
            return 'Demon';
        case 'UNDEAD_BOSS':
            return 'Undead Boss';
        case 'DRANIUM':
            return 'Dranium';
        default:
            return 'Normal';
    }
}
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/weapons-hero.jpg');">
    <div class="container">
        <h1>Monster Database</h1>
        <p>Explore the complete collection of monsters in L1J Remastered. From common creatures to legendary bosses, find detailed information about all monsters in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search monsters by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
                    <!-- Level Range Filter -->
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label for="level_min">Min Level:</label>
                        <select name="level_min" id="level_min" class="form-control">
                            <option value="">Any</option>
                            <?php for($i = 1; $i <= 100; $i += 5): ?>
                                <option value="<?= $i ?>" <?= (isset($_GET['level_min']) && $_GET['level_min'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 120px;">
                        <label for="level_max">Max Level:</label>
                        <select name="level_max" id="level_max" class="form-control">
                            <option value="">Any</option>
                            <?php for($i = 5; $i <= 100; $i += 5): ?>
                                <option value="<?= $i ?>" <?= (isset($_GET['level_max']) && $_GET['level_max'] == $i) ? 'selected' : '' ?>><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                        <label for="boss">Monster Type:</label>
                        <select name="boss" id="boss" class="form-control">
                            <option value="">All Types</option>
                            <option value="true" <?= (isset($_GET['boss']) && $_GET['boss'] === 'true') ? 'selected' : '' ?>>Bosses Only</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 150px;">
                        <label for="undead">Undead Type:</label>
                        <select name="undead" id="undead" class="form-control">
                            <option value="">All Types</option>
                            <option value="NONE" <?= (isset($_GET['undead']) && $_GET['undead'] === 'NONE') ? 'selected' : '' ?>>Normal</option>
                            <option value="UNDEAD" <?= (isset($_GET['undead']) && $_GET['undead'] === 'UNDEAD') ? 'selected' : '' ?>>Undead</option>
                            <option value="DEMON" <?= (isset($_GET['undead']) && $_GET['undead'] === 'DEMON') ? 'selected' : '' ?>>Demon</option>
                            <option value="UNDEAD_BOSS" <?= (isset($_GET['undead']) && $_GET['undead'] === 'UNDEAD_BOSS') ? 'selected' : '' ?>>Undead Boss</option>
                            <option value="DRANIUM" <?= (isset($_GET['undead']) && $_GET['undead'] === 'DRANIUM') ? 'selected' : '' ?>>Dranium</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Monster List -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="80">Icon</th> <!-- Increased width for larger images -->
                        <th>Name</th>
                        <th>Level</th>
                        <th>HP</th>
                        <th>Exp</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($monsters)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No monsters found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($monsters as $monster): 
    $isBoss = $monster['is_bossmonster'] === 'true';
    $monsterId = $monster['npcid'];
?>
    <tr id="monster-<?= $monsterId ?>" onclick="window.location.href='detail.php?id=<?= $monsterId ?>'" class="monster-row">
        <td>
            <div class="monster-icon-container">
                <img src="<?= get_monster_image($monster['spriteId']) ?>" 
                     alt="<?= htmlspecialchars($monster['desc_en']) ?>" 
                     class="monster-list-icon"
                     style="width: 64px; height: 64px;"
                     onerror="this.onerror=null;this.src='<?= SITE_URL ?>/assets/img/monsters/default.png'">
            </div>
        </td>
        <td><?= htmlspecialchars($monster['desc_en']) ?></td>
        <td><?= $monster['lvl'] ?></td>
        <td><?= number_format($monster['hp']) ?></td>
        <td><?= number_format($monster['exp']) ?></td>
        <td><?= formatUndeadType($monster['undead']) ?></td>
        <td>
            <?php if($isBoss): ?>
                <span class="badge badge-danger">Boss</span>
            <?php else: ?>
                <span class="badge badge-success">Normal</span>
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
                    Showing <?= $offset + 1 ?>-<?= min($offset + $itemsPerPage, $totalItems) ?> of <?= $totalItems ?> monsters
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