<?php
/**
 * Polymorph detail page for L1J Database Website
 */

// Include header
require_once '../../includes/header.php';

// Get database instance
$db = Database::getInstance();

// Get polymorph ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id <= 0) {
    header('Location: polymorph-list.php');
    exit;
}

// Get polymorph details
$query = "SELECT * FROM polymorphs WHERE id = ?";
$polymorph = $db->getRow($query, [$id]);

if(!$polymorph) {
    header('Location: polymorph-list.php');
    exit;
}

// Set page title and description
$pageTitle = $polymorph['name'] . ' - Polymorph Details';
$pageDescription = 'Detailed information about the ' . $polymorph['name'] . ' polymorph transformation in L1J Remastered.';
?>

<!-- Hero Section with Transparent Polymorph Image -->
<div class="polymorph-hero">
    <div class="polymorph-hero-image-container">
        <img src="<?= SITE_URL ?>/assets/image/poly/<?= $polymorph['polyid'] ?>.gif" 
             alt="<?= htmlspecialchars($polymorph['name']) ?>" 
             class="polymorph-hero-image"
             onerror="this.src='<?= SITE_URL ?>/assets/image/poly/<?= $polymorph['polyid'] ?>.png'; this.onerror=function(){this.src='<?= SITE_URL ?>/assets/image/poly/default.png';}">
    </div>
    <div class="polymorph-hero-content">
        <h1><?= htmlspecialchars($polymorph['name']) ?></h1>
        <p>Level <?= $polymorph['minlevel'] ?>+ Polymorph Transformation</p>
        <div class="polymorph-hero-badges">
            <?php if($polymorph['bonusPVP'] === 'true'): ?>
                <span class="badge badge-pvp">PvP Bonus</span>
            <?php endif; ?>
            <?php if($polymorph['isSkillUse']): ?>
                <span class="badge badge-skill">Can Use Skills</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <div class="breadcrumb">
        <a href="<?= SITE_URL ?>">Home</a>
        <span>›</span>
        <a href="<?= SITE_URL ?>/pages/polymorph/polymorph-list.php">Polymorphs</a>
        <span>›</span>
        <span><?= htmlspecialchars($polymorph['name']) ?></span>
    </div>

    <!-- Main Content Grid -->
    <div class="detail-content-grid">
        <!-- Basic Information Card -->
        <div class="card">
            <div class="card-header">
                <h2>Basic Information</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <tr>
                        <th>Polymorph ID</th>
                        <td><?= $polymorph['polyid'] ?></td>
                    </tr>
                    <tr>
                        <th>Minimum Level</th>
                        <td><?= $polymorph['minlevel'] ?>+</td>
                    </tr>
                    <tr>
                        <th>Can Use Skills</th>
                        <td><?= $polymorph['isSkillUse'] ? 'Yes' : 'No' ?></td>
                    </tr>
                    <?php if($polymorph['cause']): ?>
                    <tr>
                        <th>Cause</th>
                        <td><?= $polymorph['cause'] ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Equipment Properties Card -->
        <div class="card">
            <div class="card-header">
                <h2>Equipment Properties</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <?php if($polymorph['weaponequip']): ?>
                    <tr>
                        <th>Weapon Equipment</th>
                        <td><?= $polymorph['weaponequip'] ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($polymorph['armorequip']): ?>
                    <tr>
                        <th>Armor Equipment</th>
                        <td><?= $polymorph['armorequip'] ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <!-- Special Properties Card -->
        <div class="card">
            <div class="card-header">
                <h2>Special Properties</h2>
            </div>
            <div class="card-content">
                <table class="detail-table">
                    <tr>
                        <th>PvP Bonus</th>
                        <td><?= $polymorph['bonusPVP'] === 'true' ? '<span class="badge badge-pvp">Yes</span>' : 'No' ?></td>
                    </tr>
                    <tr>
                        <th>Long Form Enable</th>
                        <td><?= $polymorph['formLongEnable'] === 'true' ? 'Yes' : 'No' ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?> 