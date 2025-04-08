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
$totalMonsters = $monsterStats['total'];
$totalSkills = $db->getColumn("SELECT COUNT(*) FROM skills");
$totalPassiveSkills = $db->getColumn("SELECT COUNT(*) FROM skills_passive");
$totalMaps = $db->getColumn("SELECT COUNT(*) FROM mapids");

// Get recent activity (this would be a real implementation with a admin_activity table)
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

<!-- Dashboard Stats -->
<div class="admin-dashboard-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <i class="fas fa-sword"></i>
        </div>
        <div class="admin-stat-info">
            <div class="admin-stat-number"><?php echo number_format($totalWeapons); ?></div>
            <div class="admin-stat-label">Weapons</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <div class="admin-stat-info">
            <div class="admin-stat-number"><?php echo number_format($totalArmor); ?></div>
            <div class="admin-stat-label">Armor</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="admin-stat-info">
            <div class="admin-stat-number"><?php echo number_format($totalEtcItems); ?></div>
            <div class="admin-stat-label">Other Items</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <i class="fas fa-dragon"></i>
        </div>
        <div class="admin-stat-info">
            <div class="admin-stat-number"><?php echo number_format($totalMonsters); ?></div>
            <div class="admin-stat-label">Monsters</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <i class="fas fa-magic"></i>
        </div>
        <div class="admin-stat-info">
            <div class="admin-stat-number"><?php echo number_format($totalSkills); ?></div>
            <div class="admin-stat-label">Skills</div>
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-icon">
            <i class="fas fa-map"></i>
        </div>
        <div class="admin-stat-info">
            <div class="admin-stat-number"><?php echo number_format($totalMaps); ?></div>
            <div class="admin-stat-label">Maps</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card">
    <h2 class="admin-card-title">Quick Actions</h2>
    
    <div class="admin-quick-actions">
        <a href="<?php echo SITE_URL; ?>/admin/items/create.php" class="admin-quick-action">
            <i class="fas fa-plus"></i>
            <span>Add Item</span>
        </a>
        
        <a href="<?php echo SITE_URL; ?>/admin/monsters/create.php" class="admin-quick-action">
            <i class="fas fa-plus"></i>
            <span>Add Monster</span>
        </a>
        
        <a href="<?php echo SITE_URL; ?>/admin/skills/create.php" class="admin-quick-action">
            <i class="fas fa-plus"></i>
            <span>Add Skill</span>
        </a>
        
        <a href="<?php echo SITE_URL; ?>/admin/backup.php" class="admin-quick-action">
            <i class="fas fa-database"></i>
            <span>Backup Database</span>
        </a>
        
        <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="admin-quick-action">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
        
        <a href="<?php echo SITE_URL; ?>" target="_blank" class="admin-quick-action">
            <i class="fas fa-external-link-alt"></i>
            <span>View Website</span>
        </a>
    </div>
</div>

<div class="admin-row">
    <!-- Recent Activity -->
    <div class="admin-column">
        <div class="admin-card">
            <h2 class="admin-card-title">Recent Activity</h2>
            
            <div class="admin-activity-list">
                <?php foreach ($recentActivity as $activity): ?>
                    <div class="admin-activity-item">
                        <div class="admin-activity-icon">
                            <?php if ($activity['type'] === 'add'): ?>
                                <i class="fas fa-plus-circle text-success"></i>
                            <?php elseif ($activity['type'] === 'edit'): ?>
                                <i class="fas fa-edit text-warning"></i>
                            <?php elseif ($activity['type'] === 'delete'): ?>
                                <i class="fas fa-trash-alt text-danger"></i>
                            <?php else: ?>
                                <i class="fas fa-info-circle text-info"></i>
                            <?php endif; ?>
                        </div>
                        <div class="admin-activity-details">
                            <div class="admin-activity-title">
                                <?php echo ucfirst($activity['type']); ?> - <?php echo htmlspecialchars($activity['item']); ?>
                            </div>
                            <div class="admin-activity-meta">
                                By <?php echo htmlspecialchars($activity['user']); ?> • <?php echo formatDate($activity['date'], 'M j, Y g:i A'); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="admin-card-footer">
                <a href="<?php echo SITE_URL; ?>/admin/activity.php" class="admin-link">View All Activity</a>
            </div>
        </div>
    </div>
    
    <!-- Statistics Overview -->
    <div class="admin-column">
        <div class="admin-card">
            <h2 class="admin-card-title">Item Grade Distribution</h2>
            
            <div class="admin-chart">
                <?php if (isset($itemStats['grades']) && count($itemStats['grades']) > 0): ?>
                    <div class="stat-bars">
                        <?php 
                        $totalItems = $totalWeapons + $totalArmor;
                        foreach ($itemStats['grades'] as $grade): 
                            $percentage = calculatePercentage($grade['count'], $totalItems);
                        ?>
                            <div class="stat-bar-item">
                                <div class="stat-bar-label">
                                    <span class="badge <?php echo getGradeClass($grade['itemGrade']); ?>"><?php echo $grade['itemGrade']; ?></span>
                                    <span class="stat-bar-value"><?php echo number_format($grade['count']); ?> (<?php echo $percentage; ?>%)</span>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="admin-no-data">No item grade data available.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="admin-card">
            <h2 class="admin-card-title">Monster Level Distribution</h2>
            
            <div class="admin-chart">
                <?php if (isset($monsterStats['level_ranges']) && count($monsterStats['level_ranges']) > 0): ?>
                    <div class="stat-bars">
                        <?php 
                        foreach ($monsterStats['level_ranges'] as $range => $count): 
                            $percentage = calculatePercentage($count, $totalMonsters);
                        ?>
                            <div class="stat-bar-item">
                                <div class="stat-bar-label">
                                    <span class="stat-bar-range"><?php echo $range; ?></span>
                                    <span class="stat-bar-value"><?php echo number_format($count); ?> (<?php echo $percentage; ?>%)</span>
                                </div>
                                <div class="stat-bar">
                                    <div class="stat-fill" style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="admin-no-data">No monster level data available.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize any dashboard-specific JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Example of showing a welcome toast message
        if (typeof showToast === 'function') {
            showToast('Welcome to the L1J Database Admin Dashboard!', 'info');
        }
    });
</script>

<?php
// Include the admin footer
include_once '../includes/admin-footer.php';
?>