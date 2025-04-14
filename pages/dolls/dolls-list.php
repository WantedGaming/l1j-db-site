<?php
/**
 * Modern Magic Dolls listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Magic Dolls';
$pageDescription = 'Browse all magic dolls in L1J Remastered including their stats, abilities, and effects.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Build base query - removing both non-existent columns
$baseQuery = "SELECT e.item_id, e.desc_en, e.iconId, e.itemGrade, e.min_lvl,
              d.grade as doll_grade, d.haste,
              COALESCE(d.attackSkillEffectId, 0) as has_skill,
              COALESCE(d.bonusItemId, 0) as has_bonus_item
       FROM etcitem e 
       LEFT JOIN magicdoll_info d ON e.item_id = d.itemId 
       WHERE e.use_type = 'MAGICDOLL'";

// And update the level filter to use e.min_lvl instead of d.min_lvl
if(isset($_GET['min_level']) && !empty($_GET['min_level'])) {
    $whereConditions[] = "e.min_lvl >= ?";
    $params[] = intval($_GET['min_level']);
}

// Handle search and filters
$whereConditions = [];
$params = [];

// Search filter
if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "(e.desc_en LIKE ? OR e.name LIKE ?)";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}

// Item grade filter
if(isset($_GET['grade']) && !empty($_GET['grade'])) {
    $whereConditions[] = "e.itemGrade = ?";
    $params[] = $_GET['grade'];
}

// Doll grade filter
if(isset($_GET['doll_grade']) && !empty($_GET['doll_grade'])) {
    $whereConditions[] = "d.grade = ?";
    $params[] = intval($_GET['doll_grade']);
}

// Haste filter
if(isset($_GET['haste']) && $_GET['haste'] === 'true') {
    $whereConditions[] = "d.haste = 'true'";
}

// Level filter
if(isset($_GET['min_level']) && !empty($_GET['min_level'])) {
    $whereConditions[] = "d.min_lvl >= ?";
    $params[] = intval($_GET['min_level']);
}

// Special abilities filter
if(isset($_GET['has_skill']) && $_GET['has_skill'] === 'true') {
    $whereConditions[] = "d.attackSkillEffectId > 0";
}

// Bonus item filter
if(isset($_GET['has_bonus']) && $_GET['has_bonus'] === 'true') {
    $whereConditions[] = "d.bonusItemId > 0";
}

// Availability filter (defaults to in-game only)
$showAllItems = (isset($_GET['availability']) && $_GET['availability'] === 'all');
if(!$showAllItems) {
    $whereConditions[] = "e.item_id IN (SELECT DISTINCT itemId FROM droplist)";
}

// Add where conditions to query if any
$countQuery = $baseQuery;
if(!empty($whereConditions)) {
    $baseQuery .= " AND " . implode(" AND ", $whereConditions);
    $countQuery .= " AND " . implode(" AND ", $whereConditions);
}

// Add order by
$orderBy = "COALESCE(d.grade, 0) DESC, e.desc_en ASC";
if(isset($_GET['sort']) && !empty($_GET['sort'])) {
    switch($_GET['sort']) {
        case 'name_asc':
            $orderBy = "e.desc_en ASC";
            break;
        case 'name_desc':
            $orderBy = "e.desc_en DESC";
            break;
        case 'grade_asc':
            $orderBy = "COALESCE(d.grade, 0) ASC, e.desc_en ASC";
            break;
        case 'grade_desc':
            $orderBy = "COALESCE(d.grade, 0) DESC, e.desc_en ASC";
            break;
        case 'level_asc':
            $orderBy = "COALESCE(d.min_lvl, 0) ASC, e.desc_en ASC";
            break;
        case 'level_desc':
            $orderBy = "COALESCE(d.min_lvl, 0) DESC, e.desc_en ASC";
            break;
    }
}
$baseQuery .= " ORDER BY $orderBy";

// Execute count query to get total items
$totalItems = count($db->getRows($countQuery, $params));

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$itemsPerPage = 24; // Increased to show more dolls per page
$offset = ($page - 1) * $itemsPerPage;
$totalPages = ceil($totalItems / $itemsPerPage);

// Add pagination to query
$baseQuery .= " LIMIT $itemsPerPage OFFSET $offset";

// Execute the final query
$dolls = $db->getRows($baseQuery, $params);

// Get available item grades for filter
$itemGradesQuery = "SELECT DISTINCT itemGrade FROM etcitem WHERE use_type = 'MAGICDOLL' ORDER BY 
                    FIELD(itemGrade, 'NORMAL', 'ADVANC', 'RARE', 'HERO', 'LEGEND', 'MYTH', 'ONLY')";
$itemGrades = $db->getRows($itemGradesQuery);

// Get available doll grades for filter
$dollGradesQuery = "SELECT DISTINCT grade FROM magicdoll_info ORDER BY grade";
$dollGrades = $db->getRows($dollGradesQuery);

// Create pagination URL pattern for the built-in pagination function
// We'll pass this to the pagination() function from functions.php
$paginationUrlPattern = $_SERVER['PHP_SELF'] . '?';
$queryParams = $_GET;
unset($queryParams['page']); // Remove page from the array
if (!empty($queryParams)) {
    $paginationUrlPattern .= http_build_query($queryParams) . '&';
}
$paginationUrlPattern .= 'page=%d';

?>

<!-- Hero Banner -->
<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/dolls-hero.jpg');">
    <div class="container">
        <h1>Magic Dolls Database</h1>
        <p>Explore the complete collection of magic dolls in L1J Remastered. From common companions to legendary allies, find detailed information about all magic dolls in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search dolls by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit" class="btn">Search</button>
            </form>
        </div>
    </div>
</div>

<div class="container">
    <!-- Information Card -->
    <section class="info-card mt-5 mb-4">
        <div class="card-header">
            <h3><i class="fas fa-info-circle"></i> About Magic Dolls</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-3">What are Magic Dolls?</h4>
                    <p>Magic Dolls are special items that provide buffs and bonuses to your character when equipped. They appear floating around your character and can provide stat boosts, periodic item generation, or even cast spells during combat.</p>
                    
                    <h5 class="mt-4">Key Features:</h5>
                    <ul class="feature-list">
                        <li><i class="fas fa-star"></i> <strong>Stat Bonuses:</strong> Permanent stat increases while equipped</li>
                        <li><i class="fas fa-gift"></i> <strong>Item Generation:</strong> Periodic generation of useful items</li>
                        <li><i class="fas fa-bolt"></i> <strong>Haste Effect:</strong> Increased attack speed</li>
                        <li><i class="fas fa-magic"></i> <strong>Combat Skills:</strong> Special attacks during battle</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h4 class="mb-3">Doll Grades</h4>
                    <p>Dolls are classified into different grades, with higher grades providing stronger effects:</p>
                    
                    <div class="grade-list">
                        <div class="grade-item grade-0"><span class="grade-marker">0</span> Basic dolls with minor effects</div>
                        <div class="grade-item grade-1"><span class="grade-marker">1</span> Improved dolls with moderate effects</div>
                        <div class="grade-item grade-2"><span class="grade-marker">2</span> Advanced dolls with significant bonuses</div>
                        <div class="grade-item grade-3"><span class="grade-marker">3</span> Elite dolls with powerful abilities</div>
                        <div class="grade-item grade-4"><span class="grade-marker">4</span> Superior dolls with exceptional effects</div>
                        <div class="grade-item grade-5"><span class="grade-marker">5</span> Legendary dolls with supreme powers</div>
                    </div>
                    
                    <p class="mt-3"><strong>Note:</strong> Magic dolls can be damaged in battle. When a doll's durability reaches 0, it will need to be repaired.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="page-section">
        <!-- Advanced Filter System -->
        <div class="filter-container">
            <form action="<?= $_SERVER['PHP_SELF'] ?>" method="GET" class="filters-form">
                <!-- Preserve search query if present -->
                <?php if(isset($_GET['q']) && !empty($_GET['q'])): ?>
                    <input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>">
                <?php endif; ?>
                
                <div class="filter-header">
                    <h3><i class="fas fa-filter"></i> Filter Dolls</h3>
                    <div>
                        <button type="submit" class="btn">Apply</button>
                        <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
                
                <div class="filter-grid">
                    <!-- Availability Filter -->
                    <div class="filter-group">
                        <label for="availability">Availability:</label>
                        <select name="availability" id="availability" class="form-control">
                            <option value="available" <?= (!isset($_GET['availability']) || $_GET['availability'] === 'available') ? 'selected' : '' ?>>In-Game Only</option>
                            <option value="all" <?= (isset($_GET['availability']) && $_GET['availability'] === 'all') ? 'selected' : '' ?>>Show All Items</option>
                        </select>
                    </div>
                    
                    <!-- Item Grade Filter -->
                    <div class="filter-group">
                        <label for="grade">Item Grade:</label>
                        <select name="grade" id="grade" class="form-control">
                            <option value="">All Grades</option>
                            <?php foreach($itemGrades as $grade): ?>
                                <option value="<?= $grade['itemGrade'] ?>" <?= isset($_GET['grade']) && $_GET['grade'] === $grade['itemGrade'] ? 'selected' : '' ?>><?= get_item_rarity_name(['itemGrade' => $grade['itemGrade']]) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Doll Grade Filter -->
                    <div class="filter-group">
                        <label for="doll_grade">Doll Grade:</label>
                        <select name="doll_grade" id="doll_grade" class="form-control">
                            <option value="">All Grades</option>
                            <?php foreach($dollGrades as $grade): ?>
                                <option value="<?= $grade['grade'] ?>" <?= isset($_GET['doll_grade']) && $_GET['doll_grade'] == $grade['grade'] ? 'selected' : '' ?>>Grade <?= $grade['grade'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Level Filter -->
                    <div class="filter-group">
                        <label for="min_level">Min Level:</label>
                        <select name="min_level" id="min_level" class="form-control">
                            <option value="">Any Level</option>
                            <?php foreach([1, 25, 45, 55, 70, 80] as $level): ?>
                                <option value="<?= $level ?>" <?= isset($_GET['min_level']) && $_GET['min_level'] == $level ? 'selected' : '' ?>>Level <?= $level ?>+</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Special Features Checkboxes -->
                    <div class="filter-group special-features">
                        <label>Special Features:</label>
                        <div class="checkbox-group">
                            <div class="form-check">
                                <input type="checkbox" id="haste" name="haste" value="true" class="form-check-input" <?= isset($_GET['haste']) && $_GET['haste'] === 'true' ? 'checked' : '' ?>>
                                <label for="haste" class="form-check-label">Haste Effect</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="has_skill" name="has_skill" value="true" class="form-check-input" <?= isset($_GET['has_skill']) && $_GET['has_skill'] === 'true' ? 'checked' : '' ?>>
                                <label for="has_skill" class="form-check-label">Has Combat Skill</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="has_bonus" name="has_bonus" value="true" class="form-check-input" <?= isset($_GET['has_bonus']) && $_GET['has_bonus'] === 'true' ? 'checked' : '' ?>>
                                <label for="has_bonus" class="form-check-label">Has Bonus Item</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sort Options -->
                    <div class="filter-group">
                        <label for="sort">Sort By:</label>
                        <select name="sort" id="sort" class="form-control">
                            <option value="grade_desc" <?= (!isset($_GET['sort']) || $_GET['sort'] === 'grade_desc') ? 'selected' : '' ?>>Grade (High to Low)</option>
                            <option value="grade_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'grade_asc') ? 'selected' : '' ?>>Grade (Low to High)</option>
                            <option value="name_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_asc') ? 'selected' : '' ?>>Name (A to Z)</option>
                            <option value="name_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'name_desc') ? 'selected' : '' ?>>Name (Z to A)</option>
                            <option value="level_desc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'level_desc') ? 'selected' : '' ?>>Level (High to Low)</option>
                            <option value="level_asc" <?= (isset($_GET['sort']) && $_GET['sort'] === 'level_asc') ? 'selected' : '' ?>>Level (Low to High)</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Results Count and View Options -->
        <div class="results-header">
            <div class="results-count">
                <p>Showing <strong><?= count($dolls) ?></strong> of <strong><?= $totalItems ?></strong> dolls</p>
            </div>
            <div class="view-options">
                <button class="view-btn active" data-view="grid"><i class="fas fa-th"></i></button>
                <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
            </div>
        </div>
        
        <!-- Dolls Grid View (Default) -->
        <div class="dolls-container grid-view" id="dolls-grid">
            <?php if(empty($dolls)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No dolls found</h3>
                    <p>Try adjusting your search filters to find what you're looking for.</p>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn">Clear Filters</a>
                </div>
            <?php else: ?>
                <?php foreach($dolls as $doll): ?>
                    <div class="doll-card" onclick="window.location.href='doll-detail.php?id=<?= $doll['item_id'] ?>'">
                        <?php if(!empty($doll['itemGrade'])): ?>
                            <div class="doll-grade <?= get_item_rarity_class(['itemGrade' => $doll['itemGrade']]) ?>">
                                <?= get_item_rarity_name(['itemGrade' => $doll['itemGrade']]) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="doll-image">
                            <?php 
                            $icon_path = '';
                            if (isset($doll['iconId']) && !empty($doll['iconId'])) {
                                $icon_path = SITE_URL . '/assets/img/dolls/' . $doll['iconId'] . '.png';
                                // Check if path exists (in production, use file_exists)
                                $fallback_path = SITE_URL . '/assets/img/placeholders/doll_default.png';
                            } else {
                                $icon_path = SITE_URL . '/assets/img/placeholders/doll_default.png';
                                $fallback_path = $icon_path;
                            }
                            ?>
                            <img src="<?= $icon_path ?>" 
                                 alt="<?= htmlspecialchars($doll['desc_en']) ?>" 
                                 class="doll-icon"
                                 onerror="this.src='<?= $fallback_path ?>'">
                        </div>
                        
                        <div class="doll-info">
                            <h3 class="doll-name"><?= htmlspecialchars(preg_replace('/^Magic Doll:\s*/', '', $doll['desc_en'])) ?></h3>
                            
                            <div class="doll-attributes">
                                <?php if(isset($doll['doll_grade']) && $doll['doll_grade'] !== null): ?>
                                    <span class="doll-attr">Grade <?= $doll['doll_grade'] ?></span>
                                <?php endif; ?>
                                
                                <?php if(isset($doll['min_lvl']) && $doll['min_lvl'] > 0): ?>
                                    <span class="doll-attr">Level <?= $doll['min_lvl'] ?>+</span>
                                <?php endif; ?>
                                
                                <?php if(isset($doll['haste']) && $doll['haste'] === 'true'): ?>
                                    <span class="doll-attr haste"><i class="fas fa-bolt"></i> Haste</span>
                                <?php endif; ?>
                                
                                <?php if(isset($doll['has_skill']) && $doll['has_skill'] > 0): ?>
                                    <span class="doll-attr skill"><i class="fas fa-magic"></i> Skill</span>
                                <?php endif; ?>
                                
                                <?php if(isset($doll['has_bonus_item']) && $doll['has_bonus_item'] > 0): ?>
                                    <span class="doll-attr bonus"><i class="fas fa-gift"></i> Bonus</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <!-- Dolls List View (Hidden by default) -->
        <div class="dolls-container list-view" id="dolls-list" style="display: none;">
            <?php if(empty($dolls)): ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No dolls found</h3>
                    <p>Try adjusting your search filters to find what you're looking for.</p>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn">Clear Filters</a>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th width="50">Icon</th>
                            <th>Name</th>
                            <th width="100">Doll Grade</th>
                            <th width="80">Level</th>
                            <th width="80">Haste</th>
                            <th width="100">Item Grade</th>
                            <th width="120">Features</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dolls as $doll): ?>
                            <tr class="doll-row" onclick="window.location.href='doll-detail.php?id=<?= $doll['item_id'] ?>'">
                                <td>
                                    <?php 
                                    $icon_path = '';
                                    if (isset($doll['iconId']) && !empty($doll['iconId'])) {
                                        $icon_path = SITE_URL . '/assets/img/dolls/' . $doll['iconId'] . '.png';
                                        $fallback_path = SITE_URL . '/assets/img/placeholders/doll_default.png';
                                    } else {
                                        $icon_path = SITE_URL . '/assets/img/placeholders/doll_default.png';
                                        $fallback_path = $icon_path;
                                    }
                                    ?>
                                    <img src="<?= $icon_path ?>" 
                                         alt="<?= htmlspecialchars($doll['desc_en']) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= $fallback_path ?>'">
                                </td>
                                <td><?= htmlspecialchars(preg_replace('/^Magic Doll:\s*/', '', $doll['desc_en'])) ?></td>
                                <td><?= $doll['doll_grade'] ?? 'N/A' ?></td>
                                <td><?= $doll['min_lvl'] > 0 ? $doll['min_lvl'] : 'None' ?></td>
                                <td><?= $doll['haste'] === 'true' ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' ?></td>
                                <td><span class="badge <?= get_item_rarity_class(['itemGrade' => $doll['itemGrade']]) ?>"><?= get_item_rarity_name(['itemGrade' => $doll['itemGrade']]) ?></span></td>
                                <td>
                                    <div class="feature-icons">
                                        <?php if(isset($doll['has_skill']) && $doll['has_skill'] > 0): ?>
                                            <span class="feature-icon" title="Has Combat Skill"><i class="fas fa-magic"></i></span>
                                        <?php endif; ?>
                                        
                                        <?php if(isset($doll['has_bonus_item']) && $doll['has_bonus_item'] > 0): ?>
                                            <span class="feature-icon" title="Has Bonus Item"><i class="fas fa-gift"></i></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- Pagination using the built-in function -->
        <?php if($totalPages > 1): ?>
            <?= pagination($page, $totalPages, $paginationUrlPattern) ?>
        <?php endif; ?>
    </section>
</div>

<!-- JavaScript for View Switching -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridView = document.getElementById('dolls-grid');
    const listView = document.getElementById('dolls-list');
    const viewButtons = document.querySelectorAll('.view-btn');
    
    // Switch between grid and list views
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewType = this.getAttribute('data-view');
            
            // Update active button
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Show selected view
            if (viewType === 'grid') {
                gridView.style.display = 'grid';
                listView.style.display = 'none';
            } else {
                gridView.style.display = 'none';
                listView.style.display = 'block';
            }
            
            // Save preference in localStorage
            localStorage.setItem('dollsViewPreference', viewType);
        });
    });
    
    // Load saved preference if available
    const savedView = localStorage.getItem('dollsViewPreference');
    if (savedView) {
        const button = document.querySelector(`.view-btn[data-view="${savedView}"]`);
        if (button) {
            button.click();
        }
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>