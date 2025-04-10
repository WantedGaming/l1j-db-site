<?php
/**
 * System Logs for L1J Database Website
 */

// Set page title
$pageTitle = 'System Logs';

// Include admin header
require_once '../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Get log tables
$logTables = [
    'app_alim_log' => 'Application Logs',
    'app_engine_log' => 'Engine Logs',
    'clan_warehouse_log' => 'Clan Warehouse Logs',
    'log_chat' => 'Chat Logs',
    'log_enchant' => 'Enchant Logs',
    'log_shop' => 'Shop Logs',
    'log_warehouse' => 'Warehouse Logs',
    'log_private_shop' => 'Private Shop Logs',
    'log_adena_monster' => 'Monster Adena Logs',
    'log_adena_shop' => 'Shop Adena Logs',
    'log_cwarehouse' => 'Character Warehouse Logs'
];

// Set up pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$logsPerPage = 30;
$offset = ($page - 1) * $logsPerPage;

// Filter by log type
$selectedLogType = isset($_GET['log_type']) ? $_GET['log_type'] : 'all';

// Search term
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get combined logs from all tables
$combinedLogs = [];
$totalLogs = 0;

// Count total logs for pagination
foreach ($logTables as $table => $displayName) {
    // Skip table if filtered
    if ($selectedLogType !== 'all' && $selectedLogType !== $table) {
        continue;
    }
    
    // Skip tables that don't exist
    $tableExists = $db->getColumn("SHOW TABLES LIKE '$table'");
    if (!$tableExists) {
        continue;
    }
    
    // Count logs
    $countQuery = "SELECT COUNT(*) FROM $table";
    if (!empty($searchTerm)) {
        // Dynamic search across all text columns
        $searchableColumns = [];
        $columnsResult = $db->getRows("SHOW COLUMNS FROM $table");
        foreach ($columnsResult as $column) {
            $columnName = $column['Field'];
            $columnType = strtolower($column['Type']);
            if (strpos($columnType, 'varchar') !== false || 
                strpos($columnType, 'text') !== false || 
                strpos($columnType, 'char') !== false) {
                $searchableColumns[] = "`$columnName` LIKE :search";
            }
        }
        
        if (!empty($searchableColumns)) {
            $countQuery .= " WHERE " . implode(' OR ', $searchableColumns);
            $params = [':search' => "%$searchTerm%"];
            $tableCount = $db->getColumn($countQuery, $params);
        } else {
            $tableCount = 0;
        }
    } else {
        $tableCount = $db->getColumn($countQuery);
    }
    
    $totalLogs += $tableCount;
}

// Get logs for the current page
$paginatedLogs = [];

foreach ($logTables as $table => $displayName) {
    // Skip table if filtered
    if ($selectedLogType !== 'all' && $selectedLogType !== $table) {
        continue;
    }
    
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
    $params = [];
    
    // Add search condition if provided
    if (!empty($searchTerm)) {
        // Dynamic search across all text columns
        $searchableColumns = [];
        $columnsResult = $db->getRows("SHOW COLUMNS FROM $table");
        foreach ($columnsResult as $column) {
            $columnName = $column['Field'];
            $columnType = strtolower($column['Type']);
            if (strpos($columnType, 'varchar') !== false || 
                strpos($columnType, 'text') !== false || 
                strpos($columnType, 'char') !== false) {
                $searchableColumns[] = "`$columnName` LIKE :search";
            }
        }
        
        if (!empty($searchableColumns)) {
            $query .= " WHERE " . implode(' OR ', $searchableColumns);
            $params[':search'] = "%$searchTerm%";
        } else {
            // Skip this table if no text columns to search
            continue;
        }
    }
    
    // Add order by clause
    if ($dateField) {
        $query .= " ORDER BY $dateField DESC";
    } elseif ($db->columnExists($table, $idField)) {
        $query .= " ORDER BY $idField DESC";
    }
    
    // Limit results for this table - we'll adjust later to ensure correct pagination
    $query .= " LIMIT 100";
    
    $logs = $db->getRows($query, $params);
    
    if ($logs) {
        foreach ($logs as $log) {
            $log['source_table'] = $displayName;
            $log['table_name'] = $table;
            $paginatedLogs[] = $log;
        }
    }
}

// Sort combined logs by date (if available)
usort($paginatedLogs, function($a, $b) {
    // Try to find a date field to sort by
    $dateFields = ['date', 'timestamp', 'startTime'];
    
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

// Apply pagination to the combined logs
$totalPages = ceil($totalLogs / $logsPerPage);
$paginatedLogs = array_slice($paginatedLogs, 0, $logsPerPage);

?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">System Logs</h1>
                <p class="admin-hero-subtitle">View and manage system logs from different sources</p>
            </div>
            
            <div class="admin-hero-actions">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" class="hero-search-form">
                    <div class="search-input-group">
                        <?php if ($selectedLogType !== 'all'): ?>
                            <input type="hidden" name="log_type" value="<?php echo htmlspecialchars($selectedLogType); ?>">
                        <?php endif; ?>
                        <input type="text" name="search" placeholder="Search logs..." value="<?php echo htmlspecialchars($searchTerm); ?>" aria-label="Search logs">
                        <button type="submit" class="search-btn" aria-label="Search">
                            <i class="fas fa-search"></i>
                        </button>
                        <?php if (!empty($searchTerm)): ?>
                            <a href="<?php echo $_SERVER['PHP_SELF'] . ($selectedLogType !== 'all' ? '?log_type=' . urlencode($selectedLogType) : ''); ?>" class="search-clear-btn" aria-label="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="admin-message-container">
        <?php
        // Show messages if any
        if (isset($_SESSION['success_message'])) {
            echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
            unset($_SESSION['success_message']);
        }
        
        if (isset($_SESSION['error_message'])) {
            echo '<div class="alert alert-danger">' . $_SESSION['error_message'] . '</div>';
            unset($_SESSION['error_message']);
        }
        ?>
    </div>
    
    <div class="admin-table-container">
        <div class="logs-filters d-flex justify-content-between align-items-center mb-4">
            <div class="logs-type-filter">
                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" id="logTypeForm">
                    <?php if (!empty($searchTerm)): ?>
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <?php endif; ?>
                    <select name="log_type" id="logTypeSelect" class="form-control" onchange="document.getElementById('logTypeForm').submit();">
                        <option value="all" <?php echo $selectedLogType === 'all' ? 'selected' : ''; ?>>All Log Types</option>
                        <?php foreach ($logTables as $table => $displayName): ?>
                            <option value="<?php echo $table; ?>" <?php echo $selectedLogType === $table ? 'selected' : ''; ?>>
                                <?php echo $displayName; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            
            <div class="logs-actions">
                <button class="btn btn-secondary" id="refreshLogsBtn">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>
        
        <div class="logs-card">
            <div class="log-table-container">
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Timestamp</th>
                            <th>Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($paginatedLogs) > 0): ?>
                            <?php foreach ($paginatedLogs as $log): ?>
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
                                        $dateFields = ['date', 'timestamp', 'startTime'];
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
                                        elseif (($log['table_name'] === 'log_warehouse' || $log['table_name'] === 'log_cwarehouse') && isset($log['item_name'])) {
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
                                    <td class="log-actions">
                                        <button class="btn btn-sm btn-secondary view-details-btn" data-log-id="<?php echo isset($log['id']) ? $log['id'] : ''; ?>" data-log-table="<?php echo $log['table_name']; ?>">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No log entries found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <div class="pagination-info">
                        Showing <?php echo count($paginatedLogs); ?> of <?php echo $totalLogs; ?> logs
                    </div>
                    <div class="pagination-links">
                        <?php if ($page > 1): ?>
                            <a href="?page=1<?php echo $selectedLogType !== 'all' ? '&log_type=' . urlencode($selectedLogType) : ''; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-link" aria-label="First page">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                            <a href="?page=<?php echo $page - 1; ?><?php echo $selectedLogType !== 'all' ? '&log_type=' . urlencode($selectedLogType) : ''; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-link" aria-label="Previous page">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $startPage + 4);
                        if ($endPage - $startPage < 4) {
                            $startPage = max(1, $endPage - 4);
                        }
                        
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <a href="?page=<?php echo $i; ?><?php echo $selectedLogType !== 'all' ? '&log_type=' . urlencode($selectedLogType) : ''; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo $selectedLogType !== 'all' ? '&log_type=' . urlencode($selectedLogType) : ''; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-link" aria-label="Next page">
                                <i class="fas fa-angle-right"></i>
                            </a>
                            <a href="?page=<?php echo $totalPages; ?><?php echo $selectedLogType !== 'all' ? '&log_type=' . urlencode($selectedLogType) : ''; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-link" aria-label="Last page">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Log Details Modal -->
<div id="logDetailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Log Details</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div id="logDetailsContent">
                <div class="log-details-loading">Loading...</div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="closeLogDetailsBtn">Close</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Refresh button functionality
    const refreshBtn = document.getElementById('refreshLogsBtn');
    if (refreshBtn) {
        refreshBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
    
    // Modal functionality
    const modal = document.getElementById('logDetailsModal');
    const closeBtn = modal.querySelector('.close');
    const closeLogDetailsBtn = document.getElementById('closeLogDetailsBtn');
    const logDetailsContent = document.getElementById('logDetailsContent');
    
    // Close modal when clicking the close button
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Close modal when clicking the close button in footer
    closeLogDetailsBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
    
    // View log details button functionality
    const viewDetailsBtns = document.querySelectorAll('.view-details-btn');
    viewDetailsBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const logId = this.getAttribute('data-log-id');
            const logTable = this.getAttribute('data-log-table');
            
            // Set loading state
            logDetailsContent.innerHTML = '<div class="log-details-loading">Loading...</div>';
            modal.style.display = 'block';
            
            // Get log details - in a real app, this would be an AJAX call
            // For now, we'll just get the log details from the row
            const logRow = this.closest('tr');
            const logSource = logRow.querySelector('.log-source').textContent.trim();
            const logTime = logRow.querySelector('.log-time').textContent.trim();
            const logDetails = logRow.querySelector('.log-details').textContent.trim();
            
            // Build details HTML
            let detailsHTML = `
                <div class="log-detail-item">
                    <strong>Source:</strong> ${logSource}
                </div>
                <div class="log-detail-item">
                    <strong>Time:</strong> ${logTime}
                </div>
                <div class="log-detail-item">
                    <strong>Details:</strong> ${logDetails}
                </div>
                <div class="log-detail-item">
                    <strong>Table:</strong> ${logTable}
                </div>
                <div class="log-detail-item">
                    <strong>ID:</strong> ${logId || 'N/A'}
                </div>
            `;
            
            // Display details
            logDetailsContent.innerHTML = detailsHTML;
        });
    });
});
</script>

<?php
// Include the admin footer
include_once '../includes/admin-footer.php';
?>
