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
                <div class="stat-icon"><i class="fas fa-database"></i></div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-value"><?php echo number_format($totalMonsters); ?></div>
                <div class="stat-label">Monsters</div>
                <div class="stat-icon"><i class="fas fa-dragon"></i></div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-value"><?php echo number_format($totalSkills); ?></div>
                <div class="stat-label">Skills</div>
                <div class="stat-icon"><i class="fas fa-magic"></i></div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-value"><?php echo number_format($totalMaps); ?></div>
                <div class="stat-label">Maps</div>
                <div class="stat-icon"><i class="fas fa-map"></i></div>
            </div>
        </div>
    </section>

    <!-- Quick Actions -->
    <section class="dashboard-section">
        <div class="section-header">
            <h2>Quick Actions</h2>
        </div>
        
        <div class="quick-actions">
            <a href="<?php echo SITE_URL; ?>/admin/items/create.php" class="action-card">
                <div class="action-icon"><i class="fas fa-plus-circle"></i></div>
                <div class="action-label">Add Item</div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/monsters/create.php" class="action-card">
                <div class="action-icon"><i class="fas fa-dragon"></i></div>
                <div class="action-label">Add Monster</div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/skills/create.php" class="action-card">
                <div class="action-icon"><i class="fas fa-magic"></i></div>
                <div class="action-label">Add Skill</div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/backup.php" class="action-card">
                <div class="action-icon"><i class="fas fa-database"></i></div>
                <div class="action-label">Backup Database</div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/maps/index.php" class="action-card">
                <div class="action-icon"><i class="fas fa-map-marked-alt"></i></div>
                <div class="action-label">Manage Maps</div>
            </a>
            
            <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="action-card">
                <div class="action-icon"><i class="fas fa-cog"></i></div>
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
                <div class="activity-item">
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