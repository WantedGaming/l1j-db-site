<?php
/**
 * Magic Dolls listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Magic Dolls';
$pageDescription = 'Browse all magic dolls in L1J Remastered.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Build base query with join to magicdoll_info table
// Add a left join to check if the item is a blessed version of another doll
$query = "SELECT e.item_id, e.desc_en, e.iconId, 
          m.grade as doll_grade, m.haste, m.blessItemId,
          (SELECT COUNT(*) FROM magicdoll_info WHERE blessItemId = e.item_id) as is_blessed_version
          FROM etcitem e 
          LEFT JOIN magicdoll_info m ON e.item_id = m.itemId
          WHERE e.use_type = 'MAGICDOLL'";

// Handle search and filters
$whereConditions = [];
$params = [];

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "e.desc_en LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
}

// Filter by doll grade (from magicdoll_info table)
if(isset($_GET['grade']) && $_GET['grade'] !== '') {
    $whereConditions[] = "m.grade = ?";
    $params[] = intval($_GET['grade']);
}

// Add where conditions to query if any
if(!empty($whereConditions)) {
    $query .= " AND " . implode(" AND ", $whereConditions);
}

// Add order by doll grade first, then item name
$query .= " ORDER BY m.grade DESC, e.desc_en ASC";

// Execute query to get all results
$allDolls = $db->getRows($query, $params);

// Filter out blessed versions
$filteredDolls = array_filter($allDolls, function($doll) {
    // Only include the doll if it's not a blessed version of another doll
    return $doll['is_blessed_version'] == 0;
});

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 24;
$offset = ($page - 1) * $itemsPerPage;

// Calculate pagination
$totalItems = count($filteredDolls);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of items for this page
$dolls = array_slice($filteredDolls, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

// Get list of available doll grades for filter
$gradeQuery = "SELECT DISTINCT grade FROM magicdoll_info ORDER BY grade DESC";
$availableGrades = $db->getRows($gradeQuery);
?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/dolls-hero.jpg');">
    <div class="container">
        <h1>Magic Dolls Database</h1>
        <p>Explore the complete collection of magic dolls in L1J Remastered. Find detailed information about all magic dolls in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search magic dolls by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
                
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; justify-content: center;">
                    <div class="grade-buttons" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                        <span style="font-weight: 600; margin-right: 0.5rem;">Grade:</span>
                        <a href="<?= $currentPath . '?' . http_build_query(array_merge($_GET, ['grade' => ''])) ?>" 
                           class="grade-button <?= !isset($_GET['grade']) || $_GET['grade'] === '' ? 'active' : '' ?>"
                           style="padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; color: white; font-weight: 500; transition: all 0.2s; background-color: #666;">
                            All
                        </a>
                        
                        <?php foreach($availableGrades as $grade): ?>
                            <?php 
                                $gradeValue = $grade['grade'];
                                $isActive = isset($_GET['grade']) && $_GET['grade'] == $gradeValue;
                                
                                // Different colors based on grade level
                                $gradeColors = [
                                    0 => '#6c757d', // Normal - gray
                                    1 => '#28a745', // Advanced - green
                                    2 => '#007bff', // Rare - blue
                                    3 => '#6f42c1', // Hero - purple
                                    4 => '#fd7e14', // Legend - orange
                                    5 => '#dc3545', // Myth - red
                                    6 => '#ffc107'  // Only - yellow
                                ];
                                
                                $gradeColor = isset($gradeColors[$gradeValue]) ? $gradeColors[$gradeValue] : '#6c757d';
                            ?>
                            <a href="<?= $currentPath . '?' . http_build_query(array_merge($_GET, ['grade' => $gradeValue])) ?>" 
                               class="grade-button <?= $isActive ? 'active' : '' ?>"
                               style="padding: 0.5rem 1rem; border-radius: 0.375rem; text-decoration: none; color: white; font-weight: 500; transition: all 0.2s; background-color: <?= $gradeColor ?>;">
                                <?= $gradeValue ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    
                    <div>
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results info -->
        <div style="margin: 1rem 0; text-align: center;">
            <span>Found <?= $totalItems ?> magic dolls</span>
        </div>
        
        <!-- Doll List -->
        <div class="card-grid">
            <?php if(empty($dolls)): ?>
                <div class="text-center" style="grid-column: 1 / -1;">
                    <p>No magic dolls found matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach($dolls as $doll): 
                    $dollId = $doll['item_id'];
                ?>
                    <div class="card" onclick="window.location.href='doll-detail.php?id=<?= $dollId ?>'">
                        <div class="card-content">
                            <div style="text-align: center; margin-bottom: 1rem;">
                                <img src="<?= SITE_URL ?>/assets/img/dolls/<?= $doll['iconId'] ?>.png" 
                                     alt="<?= htmlspecialchars(cleanItemName($doll['desc_en'])) ?>" 
                                     class="card-image"
                                     onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/doll_default.png';">
                            </div>
                            <h3 class="card-title" style="text-align: center;">
                                <?= htmlspecialchars(str_replace('Magic Doll: ', '', cleanItemName($doll['desc_en']))) ?>
                            </h3>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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

<!-- Custom CSS for Magic Dolls specific styling -->
<style>
.card {
    cursor: pointer;
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: var(--accent);
}

.grade-button {
    opacity: 0.7;
    border: 1px solid rgba(255,255,255,0.1);
}

.grade-button:hover, .grade-button.active {
    opacity: 1;
    transform: translateY(-2px);
}

.grade-button.active {
    box-shadow: 0 0 10px rgba(249, 75, 31, 0.5);
}

.card-image {
    max-width: 100px;
    max-height: 100px;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.card:hover .card-image {
    transform: scale(1.1);
}
</style>

<?php
// Include footer
require_once '../../includes/footer.php';
?>