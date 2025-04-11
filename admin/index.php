<?php
/**
 * Admin Dashboard for L1J Database Website
 */

// Set page title
$pageTitle = 'Dashboard';

// Include admin header
require_once '../includes/admin-header.php';

// Include models
require_once '../models/Item.php';
require_once '../models/Monster.php';

// Initialize models
$itemModel = new Item();
$monsterModel = new Monster();

// Get stats
$itemStats = $itemModel->getItemStats();
$monsterStats = $monsterModel->getMonsterStats();

// Get database instance
$db = Database::getInstance();

// Get total stats
$totalWeapons = $itemStats['weapons'];
$totalArmor = $itemStats['armor'];
$totalEtcItems = $itemStats['etcitems'];
$totalMonsters = $db->getColumn("SELECT COUNT(*) FROM npc WHERE impl LIKE '%L1monster%'");
$totalSkills = $db->getColumn("SELECT COUNT(*) FROM skills");
$totalPassiveSkills = $db->getColumn("SELECT COUNT(*) FROM skills_passive");
$totalMaps = $db->getColumn("SELECT COUNT(*) FROM mapids");

// Get log data from different log tables
$logTables = [
    'app_alim_log' => 'Application Logs',
    'app_engine_log' => 'Engine Logs',
    'clan_warehouse_log' => 'Clan Warehouse Logs',
    'log_chat' => 'Chat Logs',
    'log_enchant' => 'Enchant Logs',
    'log_shop' => 'Shop Logs',
    'log_warehouse' => 'Warehouse Logs',
    'log_private_shop' => 'Private Shop Logs'
];

// Get recent logs from all tables (limit 5 from each table, but more for display)
$combinedLogs = [];
$logsPerTable = 3; // Get 3 logs per table by default
foreach ($logTables as $table => $displayName) {
    // Skip tables that don't exist
    $tableExists = $db->getColumn("SHOW TABLES LIKE '$table'");
    if (!$tableExists) {
        continue;
    }
    
    // Get ID field name (might be different for some tables)
    $idField = 'id';
    
    // Some tables use 'startTime' instead of standard datetime fields
    $dateField = ($table == 'log_adena_monster' || $table == 'log_adena_shop') ? 'startTime' : 'date';
    if (!$db->columnExists($table, $dateField)) {
        $dateField = 'timestamp';
        if (!$db->columnExists($table, $dateField)) {
            $dateField = null;
        }
    }
    
    // Build query based on available fields
    $query = "SELECT * FROM $table";
    if ($dateField) {
        $query .= " ORDER BY $dateField DESC";
    } elseif ($db->columnExists($table, $idField)) {
        $query .= " ORDER BY $idField DESC";
    }
    $query .= " LIMIT 10"; // Get 10 per table to support expanded view
    
    $logs = $db->getRows($query);
    
    if ($logs) {
        foreach ($logs as $log) {
            $log['source_table'] = $displayName;
            $log['table_name'] = $table;
            $combinedLogs[] = $log;
        }
    }
}

// Sort combined logs by date (if available)
usort($combinedLogs, function($a, $b) {
    // Try to find a date field to sort by
    $dateFields = ['datetime', 'date', 'timestamp', 'startTime'];
    
    foreach ($dateFields as $field) {
        if (isset($a[$field]) && isset($b[$field])) {
            return strtotime($b[$field]) - strtotime($a[$field]);
        }
    }
    
    // If no date field is found, sort by ID if available
    if (isset($a['id']) && isset($b['id'])) {
        return $b['id'] - $a['id'];
    }
    
    return 0;
});

// Get total number of logs for display
$totalLogsAvailable = count($combinedLogs);

// Create two arrays: one for initial display (5 logs) and one for expanded view (10 logs)
$initialLogs = array_slice($combinedLogs, 0, 5);
$expandedLogs = array_slice($combinedLogs, 0, 10);

// Get recent activity
$recentActivity = [
    [
        'type' => 'edit',
        'item' => 'Sword of Destruction',
        'user' => 'admin',
        'date' => date('Y-m-d H:i:s', strtotime('-1 hour'))
    ],
    [
        'type' => 'add',
        'item' => 'Fire Dragon',
        'user' => 'admin',
        'date' => date('Y-m-d H:i:s', strtotime('-2 hour'))
    ],
    [
        'type' => 'delete',
        'item' => 'Old Potion',
        'user' => 'admin',
        'date' => date('Y-m-d H:i:s', strtotime('-1 day'))
    ]
];
?>

<div class="dashboard-container">
    <!-- Overview Section -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Database Overview</h2>
        </div>
        
        <div class="stats-overview">
            <div class="stat-card primary">
				<div class="stat-value"><?php echo number_format($totalWeapons + $totalArmor + $totalEtcItems); ?></div>
				<div class="stat-label">Total Items</div>
				<div class="stat-icon">
					<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/items.png" alt="Items icon" style="width: 64px; height: 64px;">
				</div>
			</div>
            
            <div class="stat-card success">
                <div class="stat-value"><?php echo number_format($totalMonsters); ?></div>
                <div class="stat-label">Monsters</div>
                <div class="stat-icon"><img src="<?php echo SITE_URL; ?>/assets/img/placeholders/monsters.png" alt="Items icon" style="width: 64px; height: 64px;"></div>
			</div>
            
            <div class="stat-card info">
                <div class="stat-value"><?php echo number_format($totalSkills); ?></div>
                <div class="stat-label">Skills</div>
                <div class="stat-icon"><img src="<?php echo SITE_URL; ?>/assets/img/placeholders/skill.png" alt="Items icon" style="width: 64px; height: 64px;"></div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-value"><?php echo number_format($totalMaps); ?></div>
                <div class="stat-label">Maps</div>
                <div class="stat-icon"><img src="<?php echo SITE_URL; ?>/assets/img/placeholders/maps.png" alt="Items icon" style="width: 64px; height: 64px;"></div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Management</h2>
        </div>
        
        <div class="quick-actions">
			<a href="<?php echo SITE_URL; ?>/admin/weapons/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/weapons.png" alt="Weapons icon" class="action-icon">
				<div class="action-label">Weapons</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/armor/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/armor.png" alt="Armor icon" class="action-icon">
				<div class="action-label">Armor</div>
			</a>

			<a href="<?php echo SITE_URL; ?>/admin/monsters/create.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/monsters.png" alt="Monster icon" class="action-icon">
				<div class="action-label">Monster</div>
			</a>

			<a href="<?php echo SITE_URL; ?>/admin/skills/create.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/skill.png" alt="Skill icon" class="action-icon">
				<div class="action-label">Skill</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/maps.png" alt="Maps icon" class="action-icon">
				<div class="action-label">Maps</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/items.png" alt="Maps icon" class="action-icon">
				<div class="action-label">Items</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/dolls.png" alt="Maps icon" class="action-icon">
				<div class="action-label">Dolls</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/potions.png" alt="Maps icon" class="action-icon">
				<div class="action-label">Potions</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/scroll2.png" alt="Maps icon" class="action-icon">
				<div class="action-label">Scrolls</div>
			</a>
			
			<a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/currency.png" alt="Maps icon" class="action-icon">
				<div class="action-label">Currency</div>
			</a>

			<a href="<?php echo SITE_URL; ?>/admin/backup.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/backup.png" alt="Backup icon" class="action-icon">
				<div class="action-label">Backup Database</div>
			</a>

			<a href="<?php echo SITE_URL; ?>/admin/settings.php" class="action-card">
				<img src="<?php echo SITE_URL; ?>/assets/img/placeholders/settings.png" alt="Settings icon" class="action-icon">
				<div class="action-label">Settings</div>
			</a>
		</div>
	</section>
    
    <!-- Data Visualization Section -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Data Analysis</h2>
        </div>
        
        <div class="data-cards">
            <!-- Item Distribution Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3>Item Distribution</h3>
                </div>
                <div class="data-visualization">
                    <div class="chart-container">
                        <div class="donut-chart">
                            <canvas id="itemDistributionChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #f94b1f"></span>
                                <span class="legend-label">Weapons (<?php echo number_format($totalWeapons); ?>)</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #1cc88a"></span>
                                <span class="legend-label">Armor (<?php echo number_format($totalArmor); ?>)</span>
                            </div>
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: #36b9cc"></span>
                                <span class="legend-label">Other (<?php echo number_format($totalEtcItems); ?>)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Item Grades Card -->
            <div class="data-card">
                <div class="data-card-header">
                    <h3>Item Grade Distribution</h3>
                </div>
                <div class="data-visualization">
                    <?php if (isset($itemStats['grades']) && count($itemStats['grades']) > 0): ?>
                        <div class="horizontal-bars">
                            <?php 
                            $totalItems = $totalWeapons + $totalArmor;
                            foreach ($itemStats['grades'] as $grade): 
                                $percentage = calculatePercentage($grade['count'], $totalItems);
                            ?>
                                <div class="bar-item">
                                    <div class="bar-label">
                                        <span class="bar-title"><?php echo $grade['itemGrade']; ?></span>
                                        <span class="bar-value"><?php echo number_format($grade['count']); ?></span>
                                    </div>
                                    <div class="bar-container">
                                        <div class="bar-fill" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <div class="bar-percentage"><?php echo $percentage; ?>%</div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="no-data">No item grade data available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- System Logs Section -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>System Logs</h2>
            <div class="section-header-actions">
                <button id="expandLogsBtn" class="btn btn-sm btn-secondary me-2">
                    <i class="fas fa-expand-alt"></i> Show More
                </button>
                <a href="<?php echo SITE_URL; ?>/admin/logs.php" class="view-all" target="_blank">
                    View All <i class="fas fa-chevron-right"></i>
                </a>
            </div>
        </div>
        
        <div class="logs-card">
            <div class="log-filters">
                <select id="logTypeFilter" class="form-control">
                    <option value="all">All Log Types</option>
                    <?php foreach ($logTables as $table => $displayName): ?>
                        <option value="<?php echo $table; ?>"><?php echo $displayName; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="log-table-container">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Timestamp</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody id="logs-initial-view">
                        <?php if (count($initialLogs) > 0): ?>
                            <?php foreach ($initialLogs as $log): ?>
                                <tr data-log-type="<?php echo $log['table_name']; ?>">
                                    <td class="log-source">
                                        <span class="badge 
                                            <?php 
                                            $badgeClass = '';
                                            if (strpos($log['table_name'], 'app_') === 0) {
                                                $badgeClass = 'badge-info';
                                            } elseif (strpos($log['table_name'], 'clan_') === 0) {
                                                $badgeClass = 'badge-warning';
                                            } else {
                                                $badgeClass = 'badge-primary';
                                            }
                                            echo $badgeClass;
                                            ?>
                                        ">
                                            <i class="<?php 
                                            $iconClass = 'fas fa-file-alt'; // Default icon
                                            
                                            switch($log['table_name']) {
                                                case 'app_alim_log':
                                                    $iconClass = 'fas fa-code';
                                                    break;
                                                case 'app_engine_log':
                                                    $iconClass = 'fas fa-cogs';
                                                    break;
                                                case 'clan_warehouse_log':
                                                    $iconClass = 'fas fa-warehouse';
                                                    break;
                                                case 'log_chat':
                                                    $iconClass = 'fas fa-comments';
                                                    break;
                                                case 'log_enchant':
                                                    $iconClass = 'fas fa-scroll';
                                                    break;
                                                case 'log_shop':
                                                    $iconClass = 'fas fa-store';
                                                    break;
                                                case 'log_warehouse':
                                                    $iconClass = 'fas fa-box';
                                                    break;
                                                case 'log_private_shop':
                                                    $iconClass = 'fas fa-store-alt';
                                                    break;
                                                case 'log_adena_monster':
                                                    $iconClass = 'fas fa-dragon';
                                                    break;
                                                case 'log_adena_shop':
                                                    $iconClass = 'fas fa-cash-register';
                                                    break;
                                                case 'log_cwarehouse':
                                                    $iconClass = 'fas fa-boxes';
                                                    break;
                                            }
                                            
                                            echo $iconClass;
                                            ?>"></i>
                                            <span class="badge-text"><?php echo $log['source_table']; ?></span>
                                        </span>
                                    </td>
                                    <td class="log-time">
                                        <?php 
                                        $timeStr = '';
                                        // Try to find a date field to display
                                        $dateFields = ['datetime', 'date', 'timestamp', 'startTime'];
                                        foreach ($dateFields as $field) {
                                            if (isset($log[$field])) {
                                                $timeStr = formatDate($log[$field], 'M j, Y g:i A');
                                                break;
                                            }
                                        }
                                        if (empty($timeStr) && isset($log['id'])) {
                                            $timeStr = 'ID: ' . $log['id'];
                                        }
                                        echo $timeStr;
                                        ?>
                                    </td>
                                    <td class="log-details">
                                        <?php 
                                        // Determine which fields to display based on the log type
                                        $detailsStr = '';
                                        
                                        // Application-specific logs
                                        if ($log['table_name'] === 'app_alim_log' && isset($log['logContent'])) {
                                            $detailsStr = htmlspecialchars($log['logContent']);
                                        } 
                                        // Engine logs
                                        elseif ($log['table_name'] === 'app_engine_log' && isset($log['log'])) {
                                            $detailsStr = htmlspecialchars($log['log']);
                                        }
                                        // Chat logs
                                        elseif ($log['table_name'] === 'log_chat' && isset($log['chat_type']) && isset($log['content'])) {
                                            $detailsStr = '<strong>' . htmlspecialchars($log['chat_type']) . ':</strong> ' . htmlspecialchars($log['content']);
                                        }
                                        // Enchant logs
                                        elseif ($log['table_name'] === 'log_enchant' && isset($log['item_name'])) {
                                            $result = isset($log['result']) && $log['result'] == 1 ? 'Success' : 'Failed';
                                            $detailsStr = 'Enchant ' . $result . ': ' . htmlspecialchars($log['item_name']);
                                        }
                                        // Warehouse logs
                                        elseif ($log['table_name'] === 'log_warehouse' && isset($log['item_name'])) {
                                            $detailsStr = '<strong>Type:</strong> ' . htmlspecialchars($log['type']) . 
                                                ' | <strong>Character:</strong> ' . htmlspecialchars($log['char_name']) . ' (ID: ' . $log['char_id'] . ')' .
                                                ' | <strong>Item:</strong> ' . htmlspecialchars($log['item_name']) . ' (ID: ' . $log['item_id'] . ')' .
                                                ' | <strong>Enchant:</strong> ' . htmlspecialchars($log['item_enchantlvl']) .
                                                ' | <strong>Count:</strong> ' . $log['item_count'];
                                        }
                                        // CWarehouse logs (keeping this separate from log_warehouse)
                                        elseif ($log['table_name'] === 'log_cwarehouse' && isset($log['item_name'])) {
                                            $action = isset($log['action']) ? $log['action'] : 'Action';
                                            $detailsStr = htmlspecialchars($action) . ': ' . htmlspecialchars($log['item_name']);
                                        }
                                        // Shop logs
                                        elseif (($log['table_name'] === 'log_shop' || $log['table_name'] === 'log_private_shop') && isset($log['item_name'])) {
                                            $action = isset($log['action']) ? $log['action'] : 'Transaction';
                                            $detailsStr = htmlspecialchars($action) . ': ' . htmlspecialchars($log['item_name']);
                                        }
                                        // Fallback - show a few key fields if available
                                        else {
                                            $priorityFields = ['message', 'description', 'content', 'action', 'item_name', 'character_name', 'account_name'];
                                            foreach ($priorityFields as $field) {
                                                if (isset($log[$field]) && !empty($log[$field])) {
                                                    $detailsStr = htmlspecialchars($log[$field]);
                                                    break;
                                                }
                                            }
                                            
                                            // If no priority fields found, show the first non-id, non-date field
                                            if (empty($detailsStr)) {
                                                $skipFields = ['id', 'date', 'timestamp', 'startTime', 'table_name', 'source_table'];
                                                foreach ($log as $key => $value) {
                                                    if (!in_array($key, $skipFields) && !empty($value) && !is_array($value)) {
                                                        $detailsStr = $key . ': ' . htmlspecialchars(substr($value, 0, 100));
                                                        if (strlen($value) > 100) {
                                                            $detailsStr .= '...';
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        echo !empty($detailsStr) ? $detailsStr : 'No details available';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No log entries found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    
                    <!-- This is the expanded view that will be shown when the button is clicked -->
                    <tbody id="logs-expanded-view" style="display: none;">
                        <?php if (count($expandedLogs) > 0): ?>
                            <?php foreach ($expandedLogs as $log): ?>
                                <tr data-log-type="<?php echo $log['table_name']; ?>">
                                    <td class="log-source">
                                        <span class="badge 
                                            <?php 
                                            $badgeClass = '';
                                            if (strpos($log['table_name'], 'app_') === 0) {
                                                $badgeClass = 'badge-info';
                                            } elseif (strpos($log['table_name'], 'clan_') === 0) {
                                                $badgeClass = 'badge-warning';
                                            } else {
                                                $badgeClass = 'badge-primary';
                                            }
                                            echo $badgeClass;
                                            ?>
                                        ">
                                            <i class="<?php 
                                            $iconClass = 'fas fa-file-alt'; // Default icon
                                            
                                            switch($log['table_name']) {
                                                case 'app_alim_log':
                                                    $iconClass = 'fas fa-code';
                                                    break;
                                                case 'app_engine_log':
                                                    $iconClass = 'fas fa-cogs';
                                                    break;
                                                case 'clan_warehouse_log':
                                                    $iconClass = 'fas fa-warehouse';
                                                    break;
                                                case 'log_chat':
                                                    $iconClass = 'fas fa-comments';
                                                    break;
                                                case 'log_enchant':
                                                    $iconClass = 'fas fa-scroll';
                                                    break;
                                                case 'log_shop':
                                                    $iconClass = 'fas fa-store';
                                                    break;
                                                case 'log_warehouse':
                                                    $iconClass = 'fas fa-box';
                                                    break;
                                                case 'log_private_shop':
                                                    $iconClass = 'fas fa-store-alt';
                                                    break;
                                                case 'log_adena_monster':
                                                    $iconClass = 'fas fa-dragon';
                                                    break;
                                                case 'log_adena_shop':
                                                    $iconClass = 'fas fa-cash-register';
                                                    break;
                                                case 'log_cwarehouse':
                                                    $iconClass = 'fas fa-boxes';
                                                    break;
                                            }
                                            
                                            echo $iconClass;
                                            ?>"></i>
                                            <span class="badge-text"><?php echo $log['source_table']; ?></span>
                                        </span>
                                    </td>
                                    <td class="log-time">
                                        <?php 
                                        $timeStr = '';
                                        // Try to find a date field to display
                                        $dateFields = ['datetime', 'date', 'timestamp', 'startTime'];
                                        foreach ($dateFields as $field) {
                                            if (isset($log[$field])) {
                                                $timeStr = formatDate($log[$field], 'M j, Y g:i A');
                                                break;
                                            }
                                        }
                                        if (empty($timeStr) && isset($log['id'])) {
                                            $timeStr = 'ID: ' . $log['id'];
                                        }
                                        echo $timeStr;
                                        ?>
                                    </td>
                                    <td class="log-details">
                                        <?php 
                                        // Determine which fields to display based on the log type
                                        $detailsStr = '';
                                        
                                        // Application-specific logs
                                        if ($log['table_name'] === 'app_alim_log' && isset($log['logContent'])) {
                                            $detailsStr = htmlspecialchars($log['logContent']);
                                        } 
                                        // Engine logs
                                        elseif ($log['table_name'] === 'app_engine_log' && isset($log['log'])) {
                                            $detailsStr = htmlspecialchars($log['log']);
                                        }
                                        // Chat logs
                                        elseif ($log['table_name'] === 'log_chat' && isset($log['chat_type']) && isset($log['content'])) {
                                            $detailsStr = '<strong>' . htmlspecialchars($log['chat_type']) . ':</strong> ' . htmlspecialchars($log['content']);
                                        }
                                        // Enchant logs
                                        elseif ($log['table_name'] === 'log_enchant' && isset($log['item_name'])) {
                                            $result = isset($log['result']) && $log['result'] == 1 ? 'Success' : 'Failed';
                                            $detailsStr = 'Enchant ' . $result . ': ' . htmlspecialchars($log['item_name']);
                                        }
                                        // Warehouse logs
                                        elseif ($log['table_name'] === 'log_warehouse' && isset($log['item_name'])) {
                                            $detailsStr = '<strong>Type:</strong> ' . htmlspecialchars($log['type']) . 
                                                ' | <strong>Character:</strong> ' . htmlspecialchars($log['char_name']) . ' (ID: ' . $log['char_id'] . ')' .
                                                ' | <strong>Item:</strong> ' . htmlspecialchars($log['item_name']) . ' (ID: ' . $log['item_id'] . ')' .
                                                ' | <strong>Enchant:</strong> ' . htmlspecialchars($log['item_enchantlvl']) .
                                                ' | <strong>Count:</strong> ' . $log['item_count'];
                                        }
                                        // CWarehouse logs (keeping this separate from log_warehouse)
                                        elseif ($log['table_name'] === 'log_cwarehouse' && isset($log['item_name'])) {
                                            $action = isset($log['action']) ? $log['action'] : 'Action';
                                            $detailsStr = htmlspecialchars($action) . ': ' . htmlspecialchars($log['item_name']);
                                        }
                                        // Shop logs
                                        elseif (($log['table_name'] === 'log_shop' || $log['table_name'] === 'log_private_shop') && isset($log['item_name'])) {
                                            $action = isset($log['action']) ? $log['action'] : 'Transaction';
                                            $detailsStr = htmlspecialchars($action) . ': ' . htmlspecialchars($log['item_name']);
                                        }
                                        // Fallback - show a few key fields if available
                                        else {
                                            $priorityFields = ['message', 'description', 'content', 'action', 'item_name', 'character_name', 'account_name'];
                                            foreach ($priorityFields as $field) {
                                                if (isset($log[$field]) && !empty($log[$field])) {
                                                    $detailsStr = htmlspecialchars($log[$field]);
                                                    break;
                                                }
                                            }
                                            
                                            // If no priority fields found, show the first non-id, non-date field
                                            if (empty($detailsStr)) {
                                                $skipFields = ['id', 'date', 'timestamp', 'startTime', 'table_name', 'source_table'];
                                                foreach ($log as $key => $value) {
                                                    if (!in_array($key, $skipFields) && !empty($value) && !is_array($value)) {
                                                        $detailsStr = $key . ': ' . htmlspecialchars(substr($value, 0, 100));
                                                        if (strlen($value) > 100) {
                                                            $detailsStr .= '...';
                                                        }
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        echo !empty($detailsStr) ? $detailsStr : 'No details available';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($totalLogsAvailable > 10): ?>
                                <tr>
                                    <td colspan="3" class="text-center view-more-row">
                                        <a href="<?php echo SITE_URL; ?>/admin/logs.php" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-external-link-alt"></i> View All Logs (<?php echo number_format($totalLogsAvailable); ?>)
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center">No log entries found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Recent Activity Section -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Recent Activity</h2>
            <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="view-all">
                View All <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        
        <div class="activity-card">
            <?php foreach ($recentActivity as $activity): ?>
                <div class="activity-item py-3">
                    <div class="activity-icon 
                        <?php if ($activity['type'] === 'add') echo 'add';
                              elseif ($activity['type'] === 'edit') echo 'edit';
                              elseif ($activity['type'] === 'delete') echo 'delete'; ?>">
                        <?php if ($activity['type'] === 'add'): ?>
                            <i class="fas fa-plus-circle"></i>
                        <?php elseif ($activity['type'] === 'edit'): ?>
                            <i class="fas fa-edit"></i>
                        <?php elseif ($activity['type'] === 'delete'): ?>
                            <i class="fas fa-trash-alt"></i>
                        <?php endif; ?>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <?php echo ucfirst($activity['type']); ?> - <?php echo htmlspecialchars($activity['item']); ?>
                        </div>
                        <div class="activity-meta">
                            by <?php echo htmlspecialchars($activity['user']); ?> • <?php echo formatDate($activity['date'], 'M j, Y g:i A'); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- Include Logs Viewer JavaScript -->
<script src="<?php echo SITE_URL; ?>/assets/js/logs-viewer.js"></script>

<script>
// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Item Distribution Chart
    const ctxItems = document.getElementById('itemDistributionChart').getContext('2d');
    new Chart(ctxItems, {
        type: 'doughnut',
        data: {
            labels: ['Weapons', 'Armor', 'Other Items'],
            datasets: [{
                data: [
                    <?php echo $totalWeapons; ?>, 
                    <?php echo $totalArmor; ?>, 
                    <?php echo $totalEtcItems; ?>
                ],
                backgroundColor: ['#f94b1f', '#1cc88a', '#36b9cc'],
                hoverBackgroundColor: ['#e03a10', '#17a673', '#2c9faf'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    
    // Welcome toast notification
    if (typeof showToast === 'function') {
        showToast('Welcome to the L1J Database Admin Dashboard!', 'info');
    }
});
</script>

<?php 
// Include the admin footer
include_once '../includes/admin-footer.php';
?>
