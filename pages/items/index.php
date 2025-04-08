<?php
/**
 * Items Index Page for L1J Database Website
 */

// Set page title and description
$pageTitle = 'Item Database';
$pageDescription = 'Browse all weapons, armor, and items in the L1J Remastered database.';

// Include header
require_once '../../includes/header.php';

// Include item model
require_once '../../models/Item.php';

// Get query parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'item_id';
$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'DESC' ? 'DESC' : 'ASC';

// Initialize item model
$itemModel = new Item();

// Get items based on category
$items = [];
$totalCount = 0;

switch ($category) {
    case 'weapons':
        $result = $itemModel->getAllWeapons($page, DEFAULT_LIMIT, $sort, $order);
        $items = $result['items'];
        $totalPages = $result['pages'];
        $currentPage = $result['current_page'];
        $totalCount = $result['total'];
        break;
    case 'armor':
        $result = $itemModel->getAllArmor($page, DEFAULT_LIMIT, $sort, $order);
        $items = $result['items'];
        $totalPages = $result['pages'];
        $currentPage = $result['current_page'];
        $totalCount = $result['total'];
        break;
    case 'etcitems':
        $result = $itemModel->getAllEtcItems($page, DEFAULT_LIMIT, $sort, $order);
        $items = $result['items'];
        $totalPages = $result['pages'];
        $currentPage = $result['current_page'];
        $totalCount = $result['total'];
        break;
    default:
        // Get all item counts
        $db = Database::getInstance();
        $weaponCount = $db->getColumn("SELECT COUNT(*) FROM weapon");
        $armorCount = $db->getColumn("SELECT COUNT(*) FROM armor");
        $etcItemCount = $db->getColumn("SELECT COUNT(*) FROM etcitem");
        
        // For "all" category, we'll show the top 3 items from each category
        $weapons = $db->getRows("SELECT 'weapon' AS category, w.*, e.iconId FROM weapon w
                                LEFT JOIN etcitem e ON e.item_id = w.item_id
                                ORDER BY w.item_id ASC LIMIT 3");
        
        $armor = $db->getRows("SELECT 'armor' AS category, a.*, e.iconId FROM armor a
                              LEFT JOIN etcitem e ON e.item_id = a.item_id
                              ORDER BY a.item_id ASC LIMIT 3");
        
        $etcitems = $db->getRows("SELECT 'etcitem' AS category, e.* FROM etcitem e
                                 ORDER BY e.item_id ASC LIMIT 3");
        
        $items = [
            'weapons' => $weapons,
            'armor' => $armor,
            'etcitems' => $etcitems
        ];
        
        $totalCount = $weaponCount + $armorCount + $etcItemCount;
        break;
}
?>

<div class="page-header">
    <h1>Item Database</h1>
    <p>Browse all weapons, armor, and items available in L1J Remastered. Use the filters below to narrow your search.</p>
</div>

<!-- Search Bar -->
<div class="search-container">
    <form action="<?php echo SITE_URL; ?>/search.php" method="GET" class="search-bar">
        <input type="text" name="q" placeholder="Search for items..." required>
        <input type="hidden" name="type" value="items">
        <button type="submit" class="btn">Search</button>
    </form>
</div>

<!-- Category Tabs -->
<div class="tab-navigation">
    <a href="?category=all" class="tab-link <?php echo $category === 'all' ? 'active' : ''; ?>">
        All Items <span class="count">(<?php echo number_format($totalCount); ?>)</span>
    </a>
    <a href="?category=weapons" class="tab-link <?php echo $category === 'weapons' ? 'active' : ''; ?>">
        Weapons
    </a>
    <a href="?category=armor" class="tab-link <?php echo $category === 'armor' ? 'active' : ''; ?>">
        Armor
    </a>
    <a href="?category=etcitems" class="tab-link <?php echo $category === 'etcitems' ? 'active' : ''; ?>">
        Other Items
    </a>
</div>

<?php if ($category === 'all'): ?>
    <!-- Category Overview -->
    <div class="category-overview">
        <div class="category-section">
            <h2 class="category-title">Weapons <a href="?category=weapons" class="view-all">View All</a></h2>
            <div class="card-grid">
                <?php foreach ($items['weapons'] as $item): ?>
                    <div class="card">
                        <div class="card-image-container">
                            <img src="<?php echo SITE_URL; ?>/assets/img/items/<?php echo $item['item_id']; ?>.png" alt="<?php echo htmlspecialchars($item['desc_kr']); ?>" class="card-image" onerror="this.src='<?php echo SITE_URL; ?>/assets/img/items/default.png'">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($item['desc_kr']); ?></h3>
                            <div class="card-meta">
                                <span class="badge <?php echo getGradeClass($item['itemGrade']); ?>"><?php echo $item['itemGrade']; ?></span>
                                <span class="item-type"><?php echo getItemTypeLabel($item['type']); ?></span>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/pages/items/detail.php?id=<?php echo $item['item_id']; ?>&type=weapon" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="category-section">
            <h2 class="category-title">Armor <a href="?category=armor" class="view-all">View All</a></h2>
            <div class="card-grid">
                <?php foreach ($items['armor'] as $item): ?>
                    <div class="card">
                        <div class="card-image-container">
                            <img src="<?php echo SITE_URL; ?>/assets/img/items/<?php echo $item['item_id']; ?>.png" alt="<?php echo htmlspecialchars($item['desc_kr']); ?>" class="card-image" onerror="this.src='<?php echo SITE_URL; ?>/assets/img/items/default.png'">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($item['desc_kr']); ?></h3>
                            <div class="card-meta">
                                <span class="badge <?php echo getGradeClass($item['itemGrade']); ?>"><?php echo $item['itemGrade']; ?></span>
                                <span class="item-type"><?php echo getItemTypeLabel($item['type']); ?></span>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/pages/items/detail.php?id=<?php echo $item['item_id']; ?>&type=armor" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="category-section">
            <h2 class="category-title">Other Items <a href="?category=etcitems" class="view-all">View All</a></h2>
            <div class="card-grid">
                <?php foreach ($items['etcitems'] as $item): ?>
                    <div class="card">
                        <div class="card-image-container">
                            <img src="<?php echo SITE_URL; ?>/assets/img/items/<?php echo $item['item_id']; ?>.png" alt="<?php echo htmlspecialchars($item['desc_kr']); ?>" class="card-image" onerror="this.src='<?php echo SITE_URL; ?>/assets/img/items/default.png'">
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo htmlspecialchars($item['desc_kr']); ?></h3>
                            <div class="card-meta">
                                <span class="item-type"><?php echo getItemTypeLabel($item['item_type']); ?></span>
                            </div>
                            <a href="<?php echo SITE_URL; ?>/pages/items/detail.php?id=<?php echo $item['item_id']; ?>&type=etcitem" class="btn btn-secondary">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- Sort Options -->
    <div class="sort-options">
        <span>Sort by:</span>
        <a href="?category=<?php echo $category; ?>&sort=item_id&order=<?php echo $sort === 'item_id' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-option <?php echo $sort === 'item_id' ? 'active' : ''; ?>">
            ID <?php echo $sort === 'item_id' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
        </a>
        <a href="?category=<?php echo $category; ?>&sort=desc_kr&order=<?php echo $sort === 'desc_kr' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-option <?php echo $sort === 'desc_kr' ? 'active' : ''; ?>">
            Name <?php echo $sort === 'desc_kr' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
        </a>
        <?php if ($category === 'weapons' || $category === 'armor'): ?>
            <a href="?category=<?php echo $category; ?>&sort=itemGrade&order=<?php echo $sort === 'itemGrade' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-option <?php echo $sort === 'itemGrade' ? 'active' : ''; ?>">
                Grade <?php echo $sort === 'itemGrade' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
            </a>
        <?php endif; ?>
        
        <?php if ($category === 'weapons'): ?>
            <a href="?category=<?php echo $category; ?>&sort=dmg_small&order=<?php echo $sort === 'dmg_small' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-option <?php echo $sort === 'dmg_small' ? 'active' : ''; ?>">
                Damage <?php echo $sort === 'dmg_small' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
            </a>
        <?php endif; ?>
        
        <?php if ($category === 'armor'): ?>
            <a href="?category=<?php echo $category; ?>&sort=ac&order=<?php echo $sort === 'ac' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>" class="sort-option <?php echo $sort === 'ac' ? 'active' : ''; ?>">
                AC <?php echo $sort === 'ac' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?>
            </a>
        <?php endif; ?>
    </div>

    <!-- Item List -->
    <div class="table-container">
        <table class="items-table">
            <thead>
                <tr>
                    <th width="60">Icon</th>
                    <th width="80">ID</th>
                    <th>Name</th>
                    <?php if ($category === 'weapons'): ?>
                        <th>Type</th>
                        <th>Grade</th>
                        <th>Damage</th>
                        <th>Weight</th>
                    <?php elseif ($category === 'armor'): ?>
                        <th>Type</th>
                        <th>Grade</th>
                        <th>AC</th>
                        <th>Weight</th>
                    <?php else: ?>
                        <th>Type</th>
                        <th>Weight</th>
                        <th>Description</th>
                    <?php endif; ?>
                    <th width="100">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <img src="<?php echo SITE_URL; ?>/assets/img/items/<?php echo $item['item_id']; ?>.png" alt="<?php echo htmlspecialchars($item['desc_kr']); ?>" class="item-icon" onerror="this.src='<?php echo SITE_URL; ?>/assets/img/items/default.png'">
                        </td>
                        <td><?php echo $item['item_id']; ?></td>
                        <td><?php echo htmlspecialchars($item['desc_kr']); ?></td>
                        
                        <?php if ($category === 'weapons'): ?>
                            <td><?php echo getItemTypeLabel($item['type']); ?></td>
                            <td><span class="badge <?php echo getGradeClass($item['itemGrade']); ?>"><?php echo $item['itemGrade']; ?></span></td>
                            <td><?php echo $item['dmg_small']; ?> - <?php echo $item['dmg_large']; ?></td>
                            <td><?php echo $item['weight']; ?></td>
                        <?php elseif ($category === 'armor'): ?>
                            <td><?php echo getItemTypeLabel($item['type']); ?></td>
                            <td><span class="badge <?php echo getGradeClass($item['itemGrade']); ?>"><?php echo $item['itemGrade']; ?></span></td>
                            <td><?php echo $item['ac']; ?></td>
                            <td><?php echo $item['weight']; ?></td>
                        <?php else: ?>
                            <td><?php echo getItemTypeLabel($item['item_type']); ?></td>
                            <td><?php echo $item['weight']; ?></td>
                            <td><?php echo substr(htmlspecialchars($item['note']), 0, 50); ?><?php echo strlen($item['note']) > 50 ? '...' : ''; ?></td>
                        <?php endif; ?>
                        
                        <td>
                            <a href="<?php echo SITE_URL; ?>/pages/items/detail.php?id=<?php echo $item['item_id']; ?>&type=<?php echo $category === 'weapons' ? 'weapon' : ($category === 'armor' ? 'armor' : 'etcitem'); ?>" class="btn btn-sm">View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Pagination -->
    <div class="pagination-container">
        <?php 
        if (isset($totalPages) && $totalPages > 1) {
            echo generatePagination(
                $currentPage, 
                $totalPages, 
                "?category=$category&sort=$sort&order=$order&page=%d"
            );
        }
        ?>
    </div>
<?php endif; ?>

<?php
// Include footer
require_once '../../includes/footer.php';
?>
