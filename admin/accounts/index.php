<?php
/**
 * Admin Accounts List for L1J Database Website
 */

// Set page title
$pageTitle = 'Manage Accounts';

// Include admin header
require_once('../../includes/admin-header.php');

// Get database instance
$db = Database::getInstance();

// Build query for accounts list
$query = "SELECT a.*, COUNT(c.objid) as character_count 
          FROM accounts a 
          LEFT JOIN characters c ON a.login = c.account_name";

// Handle search if present
$params = [];
if (isset($_GET['q']) && !empty($_GET['q'])) {
    $query .= " WHERE a.login LIKE ? OR a.host LIKE ?";
    $params[] = '%' . $_GET['q'] . '%';
    $params[] = '%' . $_GET['q'] . '%';
}

// Group by login to count characters per account
$query .= " GROUP BY a.login";

// Add order by
$query .= " ORDER BY a.login ASC";

// Pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = 25;
$offset = ($page - 1) * $itemsPerPage;

// Get total count
$totalAccounts = $db->getColumn("SELECT COUNT(*) FROM accounts");
$totalPages = ceil($totalAccounts / $itemsPerPage);

// Add limit for pagination
$query .= " LIMIT ?, ?";
$params[] = $offset;
$params[] = $itemsPerPage;

// Execute query
$accounts = $db->getRows($query, $params);
?>

<div class="admin-container">
    <div class="admin-hero-section">
        <div class="admin-hero-container">
            <div class="admin-hero-content">
                <h1 class="admin-hero-title">Manage Accounts</h1>
                <p class="admin-hero-subtitle">Total Accounts: <?= $totalAccounts ?></p>
                
                <div class="hero-search-form mt-4">
                    <form action="index.php" method="GET">
                        <div class="search-input-group">
                            <input type="text" name="q" placeholder="Search accounts..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                            <button type="submit" class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (isset($_GET['q']) && !empty($_GET['q'])): ?>
                                <a href="index.php" class="search-clear-btn">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Display success/error messages -->
    <?php if (isset($_SESSION['admin_message'])): ?>
        <div class="alert alert-<?= $_SESSION['admin_message']['type'] ?>">
            <?= $_SESSION['admin_message']['message'] ?>
        </div>
        <?php unset($_SESSION['admin_message']); ?>
    <?php endif; ?>
    
    <!-- Accounts List Table -->
    <div class="admin-table-container">
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="200">Login</th>
                        <th width="150">IP Address</th>
                        <th width="150">Host</th>
                        <th width="120">Access Level</th>
                        <th width="120">Characters</th>
                        <th width="180">Last Active</th>
                        <th width="100">Status</th>
                        <th width="150">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($accounts)): ?>
                        <tr>
                            <td colspan="8" class="text-center">No accounts found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($accounts as $account): ?>
                            <tr class="<?= $account['banned'] ? 'banned-account' : '' ?>">
                                <td><?= htmlspecialchars($account['login']) ?></td>
                                <td><?= htmlspecialchars($account['ip']) ?></td>
                                <td><?= htmlspecialchars($account['host']) ?></td>
                                <td>
                                    <?php 
                                    switch ($account['access_level']) {
                                        case 0:
                                            echo '<span class="badge badge-secondary">User</span>';
                                            break;
                                        case 1:
                                            echo '<span class="badge badge-primary">Admin</span>';
                                            break;
                                        case 2:
                                            echo '<span class="badge badge-info">GM</span>';
                                            break;
                                        default:
                                            echo '<span class="badge badge-secondary">User</span>';
                                    }
                                    ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= $account['character_count'] ?></span>
                                </td>
                                <td><?= $account['lastactive'] ? formatDate($account['lastactive'], 'M j, Y g:i A') : 'Never' ?></td>
                                <td>
                                    <?php if ($account['banned']): ?>
                                        <span class="badge badge-danger">Banned</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <a href="characters.php?account=<?= urlencode($account['login']) ?>" class="btn btn-sm btn-view" title="View Characters">
                                        <i class="fas fa-users"></i>
                                    </a>
                                    <a href="edit-account.php?id=<?= urlencode($account['login']) ?>" class="btn btn-sm btn-edit" title="Edit Account">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="#" class="btn btn-sm <?= $account['banned'] ? 'btn-success' : 'btn-danger' ?>" 
                                       onclick="confirmToggleBan('<?= addslashes($account['login']) ?>', <?= $account['banned'] ? 'false' : 'true' ?>)"
                                       title="<?= $account['banned'] ? 'Unban Account' : 'Ban Account' ?>">
                                        <i class="fas <?= $account['banned'] ? 'fa-unlock' : 'fa-ban' ?>"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">
            <div class="pagination-info">
                Showing <?= ($offset + 1) ?>-<?= min($offset + $itemsPerPage, $totalAccounts) ?> of <?= $totalAccounts ?> accounts
            </div>
            
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="index.php?page=1<?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="index.php?page=<?= ($page - 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-left"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-link disabled"><i class="fas fa-angle-double-left"></i></span>
                    <span class="pagination-link disabled"><i class="fas fa-angle-left"></i></span>
                <?php endif; ?>
                
                <?php
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                if ($startPage > 1) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                
                for ($i = $startPage; $i <= $endPage; $i++): ?>
                    <a href="index.php?page=<?= $i ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" 
                       class="pagination-link <?= ($i == $page) ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor;
                
                if ($endPage < $totalPages) {
                    echo '<span class="pagination-ellipsis">...</span>';
                }
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="index.php?page=<?= ($page + 1) ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="index.php?page=<?= $totalPages ?><?= isset($_GET['q']) ? '&q='.urlencode($_GET['q']) : '' ?>" class="pagination-link">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php else: ?>
                    <span class="pagination-link disabled"><i class="fas fa-angle-right"></i></span>
                    <span class="pagination-link disabled"><i class="fas fa-angle-double-right"></i></span>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Ban/Unban Confirmation Modal -->
<div id="banModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="banModalTitle">Confirm Action</h3>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <p id="banModalMessage">Are you sure you want to perform this action?</p>
            <p class="warning" id="banModalWarning">This will affect the account's ability to log into the game.</p>
        </div>
        <div class="modal-footer">
            <form id="banForm" method="POST" action="toggle-ban.php">
                <input type="hidden" name="account" id="banAccountName" value="">
                <input type="hidden" name="ban_status" id="banStatus" value="">
                <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                <button type="submit" class="btn btn-danger" id="banSubmitBtn">Confirm</button>
            </form>
        </div>
    </div>
</div>

<style>
.banned-account {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>

<script>
// Ban/Unban confirmation modal functionality
function confirmToggleBan(accountName, banStatus) {
    const modal = document.getElementById('banModal');
    const title = document.getElementById('banModalTitle');
    const message = document.getElementById('banModalMessage');
    const warning = document.getElementById('banModalWarning');
    const accountInput = document.getElementById('banAccountName');
    const statusInput = document.getElementById('banStatus');
    const submitBtn = document.getElementById('banSubmitBtn');
    
    // Set modal content based on action
    if (banStatus) {
        title.textContent = 'Confirm Ban';
        message.textContent = `Are you sure you want to ban the account: ${accountName}?`;
        warning.textContent = 'This will prevent the account from logging into the game.';
        submitBtn.className = 'btn btn-danger';
        submitBtn.textContent = 'Ban Account';
    } else {
        title.textContent = 'Confirm Unban';
        message.textContent = `Are you sure you want to unban the account: ${accountName}?`;
        warning.textContent = 'This will allow the account to log into the game again.';
        submitBtn.className = 'btn btn-success';
        submitBtn.textContent = 'Unban Account';
    }
    
    // Set form values
    accountInput.value = accountName;
    statusInput.value = banStatus ? '1' : '0';
    
    // Display the modal
    modal.style.display = 'block';
    
    // Close modal functionality
    const closeButtons = modal.getElementsByClassName('close');
    for (let i = 0; i < closeButtons.length; i++) {
        closeButtons[i].onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    const cancelButtons = modal.getElementsByClassName('close-modal');
    for (let i = 0; i < cancelButtons.length; i++) {
        cancelButtons[i].onclick = function() {
            modal.style.display = 'none';
        }
    }
    
    // Close when clicking outside the modal
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
    
    // Prevent default link behavior
    return false;
}
</script>

<?php
// Include admin footer
require_once '../includes/admin-footer.php';
?>
