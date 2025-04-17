<?php
/**
 * Skills listing page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Skills';
$pageDescription = 'Browse all skills in L1J Remastered including magical abilities, combat techniques, and passive effects.';

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Build base query
$query = "SELECT s.skill_id, s.name, s.desc_en, s.desc_kr, s.skill_level, 
                 s.target, s.classType, s.grade
          FROM skills s";

// Handle search and filters
$whereConditions = [];
$params = [];

if(isset($_GET['q']) && !empty($_GET['q'])) {
    $whereConditions[] = "(s.name LIKE ? OR s.desc_en LIKE ? OR s.desc_kr LIKE ?)";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}

if(isset($_GET['class']) && !empty($_GET['class']) && $_GET['class'] !== 'none') {
    $whereConditions[] = "s.classType = ?";
    $params[] = $_GET['class'];
}

if(isset($_GET['target']) && !empty($_GET['target']) && $_GET['target'] !== 'ALL') {
    $whereConditions[] = "s.target = ?";
    $params[] = $_GET['target'];
}

if(isset($_GET['grade']) && !empty($_GET['grade'])) {
    $whereConditions[] = "s.grade = ?";
    $params[] = $_GET['grade'];
}

if(isset($_GET['level_min']) && !empty($_GET['level_min'])) {
    $whereConditions[] = "s.skill_level >= ?";
    $params[] = intval($_GET['level_min']);
}

if(isset($_GET['level_max']) && !empty($_GET['level_max'])) {
    $whereConditions[] = "s.skill_level <= ?";
    $params[] = intval($_GET['level_max']);
}

// Add WHERE clause if we have any conditions
if(!empty($whereConditions)) {
    $query .= " WHERE " . implode(" AND ", $whereConditions);
}

// Add to the existing WHERE conditions
if(isset($_GET['skill_level']) && !empty($_GET['skill_level'])) {
    $whereConditions[] = "s.skill_level = ?";
    $params[] = intval($_GET['skill_level']);
}

if(isset($_GET['attr']) && !empty($_GET['attr']) && $_GET['attr'] !== 'NONE') {
    $whereConditions[] = "s.attr = ?";
    $params[] = $_GET['attr'];
}

// Add order by
$query .= " ORDER BY s.classType, s.skill_level ASC, s.name ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 20;
$offset = ($page - 1) * $itemsPerPage;

// Execute query to get all results for filtering
$allSkills = $db->getRows($query, $params);

// Calculate pagination
$totalItems = count($allSkills);
$totalPages = ceil($totalItems / $itemsPerPage);

// Get the portion of skills for this page
$skills = array_slice($allSkills, $offset, $itemsPerPage);

// Current URL path (without query string)
$currentPath = $_SERVER['PHP_SELF'];

// Get class types for filter
$classQuery = "SELECT DISTINCT classType FROM skills WHERE classType != 'none' ORDER BY classType";
$classTypes = $db->getRows($classQuery);

?>

<div class="hero" style="background: linear-gradient(rgba(3, 3, 3, 0.7), rgba(3, 3, 3, 0.9)), url('<?= SITE_URL ?>/assets/img/backgrounds/skills-hero.jpg');">
    <div class="container">
        <h1>Skills Database</h1>
        <p>Explore the complete collection of skills in L1J Remastered. From basic abilities to legendary powers, find detailed information about all skills in the game.</p>
        
        <!-- Search Bar in Hero Section -->
        <div class="search-container">
            <form action="<?= $currentPath ?>" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search skills by name..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
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
                    <!-- Class Filter -->
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="class">Class:</label>
                        <select name="class" id="class" class="form-control">
                            <option value="">All Classes</option>
                            <option value="none" <?= isset($_GET['class']) && $_GET['class'] === 'none' ? 'selected' : '' ?>>Common</option>
                            <?php foreach($classTypes as $class): ?>
                                <option value="<?= $class['classType'] ?>" <?= isset($_GET['class']) && $_GET['class'] === $class['classType'] ? 'selected' : '' ?>>
                                    <?= ucfirst($class['classType']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
					
					<!-- After the class dropdown -->
					<div class="form-group skill-level-filter" style="margin-bottom: 0; flex: 1; min-width: 180px; display: none;">
						<label for="skill_level">Skill Level:</label>
						<select name="skill_level" id="skill_level" class="form-control">
							<option value="">All Levels</option>
							<option value="1" <?= isset($_GET['skill_level']) && $_GET['skill_level'] === '1' ? 'selected' : '' ?>>Level 1</option>
							<option value="2" <?= isset($_GET['skill_level']) && $_GET['skill_level'] === '2' ? 'selected' : '' ?>>Level 2</option>
							<option value="3" <?= isset($_GET['skill_level']) && $_GET['skill_level'] === '3' ? 'selected' : '' ?>>Level 3</option>
							<option value="4" <?= isset($_GET['skill_level']) && $_GET['skill_level'] === '4' ? 'selected' : '' ?>>Level 4</option>
							<option value="5" <?= isset($_GET['skill_level']) && $_GET['skill_level'] === '5' ? 'selected' : '' ?>>Level 5</option>
						</select>
					</div>

					<div class="form-group elemental-filter" style="margin-bottom: 0; flex: 1; min-width: 180px; display: none;">
						<label for="attr">Elemental Attribute:</label>
						<select name="attr" id="attr" class="form-control">
							<option value="">All Elements</option>
							<option value="EARTH" <?= isset($_GET['attr']) && $_GET['attr'] === 'EARTH' ? 'selected' : '' ?>>Earth</option>
							<option value="FIRE" <?= isset($_GET['attr']) && $_GET['attr'] === 'FIRE' ? 'selected' : '' ?>>Fire</option>
							<option value="WATER" <?= isset($_GET['attr']) && $_GET['attr'] === 'WATER' ? 'selected' : '' ?>>Water</option>
							<option value="WIND" <?= isset($_GET['attr']) && $_GET['attr'] === 'WIND' ? 'selected' : '' ?>>Wind</option>
							<option value="RAY" <?= isset($_GET['attr']) && $_GET['attr'] === 'RAY' ? 'selected' : '' ?>>Ray</option>
						</select>
					</div>
                    
                    <!-- Target Filter -->
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="target">Target Type:</label>
                        <select name="target" id="target" class="form-control">
                            <option value="">All Types</option>
                            <option value="NONE" <?= isset($_GET['target']) && $_GET['target'] === 'NONE' ? 'selected' : '' ?>>None</option>
                            <option value="ATTACK" <?= isset($_GET['target']) && $_GET['target'] === 'ATTACK' ? 'selected' : '' ?>>Attack</option>
                            <option value="BUFF" <?= isset($_GET['target']) && $_GET['target'] === 'BUFF' ? 'selected' : '' ?>>Buff</option>
                        </select>
                    </div>
                    
                    <!-- Grade Filter -->
                    <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 180px;">
                        <label for="grade">Grade:</label>
                        <select name="grade" id="grade" class="form-control">
                            <option value="">All Grades</option>
                            <option value="NORMAL" <?= isset($_GET['grade']) && $_GET['grade'] === 'NORMAL' ? 'selected' : '' ?>>Normal</option>
                            <option value="RARE" <?= isset($_GET['grade']) && $_GET['grade'] === 'RARE' ? 'selected' : '' ?>>Rare</option>
                            <option value="LEGEND" <?= isset($_GET['grade']) && $_GET['grade'] === 'LEGEND' ? 'selected' : '' ?>>Legend</option>
                            <option value="MYTH" <?= isset($_GET['grade']) && $_GET['grade'] === 'MYTH' ? 'selected' : '' ?>>Myth</option>
                            <option value="ONLY" <?= isset($_GET['grade']) && $_GET['grade'] === 'ONLY' ? 'selected' : '' ?>>Only</option>
                        </select>
                    </div>
                    
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
                    
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="<?= $currentPath ?>" class="btn btn-secondary">Reset</a>
                        <button type="submit" class="btn">Apply</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Skills List -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Level</th>
                        <th>Class</th>
                        <th>Type</th>
                        <th>MP</th>
                        <th>Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($skills)): ?>
                        <tr>
                            <td colspan="7" class="text-center">No skills found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($skills as $skill): 
                            // Get skill icon if available
                            $skillInfo = $db->getRow("SELECT icon FROM skills_info WHERE skillId = ?", [$skill['skill_id']]);
                            $iconId = $skillInfo ? $skillInfo['icon'] : 0;
                        ?>
                            <tr onclick="window.location.href='skill-detail.php?id=<?= $skill['skill_id'] ?>'" style="cursor: pointer;">
                                <td>
                                    <img src="<?= SITE_URL ?>/assets/img/skills/<?= $iconId ?>.png" 
                                         alt="<?= htmlspecialchars($skill['desc_en']) ?>" 
                                         class="item-icon"
                                         onerror="this.src='<?= SITE_URL ?>/assets/img/placeholders/skill-placeholder.png';">
                                </td>
                                <td><?= htmlspecialchars($skill['desc_en']) ?></td>
                                <td><?= $skill['skill_level'] ?></td>
                                <td><?= $skill['classType'] != 'none' ? ucfirst($skill['classType']) : 'Common' ?></td>
                                <td><?= $skill['target'] != 'NONE' ? ucfirst(strtolower($skill['target'])) : 'Passive' ?></td>
                                <td>
                                    <?php 
                                        $mpConsume = $db->getColumn("SELECT mpConsume FROM skills WHERE skill_id = ?", [$skill['skill_id']]);
                                        echo $mpConsume ? $mpConsume : '-';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?= getGradeBadgeClass($skill['grade']) ?>">
                                        <?= formatGrade($skill['grade']) ?>
                                    </span>
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
                    Showing <?= $offset + 1 ?>-<?= min($offset + $itemsPerPage, $totalItems) ?> of <?= $totalItems ?> skills
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const classSelect = document.getElementById('class');
    const skillLevelFilter = document.querySelector('.skill-level-filter');
    const elementalFilter = document.querySelector('.elemental-filter');
    
    // Function to toggle filters based on selected class
    function toggleFilters() {
        const selectedClass = classSelect.value;
        
        // Hide all conditional filters first
        skillLevelFilter.style.display = 'none';
        elementalFilter.style.display = 'none';
        
        // Show relevant filters based on class
        if (['wizard', 'illusionist', 'dragonknight'].includes(selectedClass)) {
            skillLevelFilter.style.display = 'block';
        }
        
        // Show elemental filter for elf and some other classes that might use elemental magic
        if (['elf', 'wizard'].includes(selectedClass)) {
            elementalFilter.style.display = 'block';
        }
    }
    
    // Run once on page load
    toggleFilters();
    
    // Add event listener for class change
    classSelect.addEventListener('change', toggleFilters);
});
</script>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
