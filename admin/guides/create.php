<?php
/**
 * Admin - Create New Guide
 */

// Set page title
$pageTitle = 'Add New Guide';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect guide data from form
    $guide = [
        'id' => intval($_POST['id'] ?? 0),
        'title' => $_POST['title'] ?? '',
        'category' => $_POST['category'] ?? '',
        'author' => $_POST['author'] ?? '',
        'content' => $_POST['content'] ?? '',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'view_count' => 0,
        'thumbnail' => $_POST['thumbnail'] ?? ''
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($guide['title'])) {
        $errors[] = "Guide title is required";
    }
    
    if (empty($guide['content'])) {
        $errors[] = "Guide content is required";
    }
    
    if (empty($guide['category'])) {
        $errors[] = "Guide category is required";
    }
    
    // Check if guide already exists
    $existingGuide = $db->getRow("SELECT id FROM guides WHERE id = ?", [$guide['id']]);
    if ($existingGuide) {
        $errors[] = "A guide with ID {$guide['id']} already exists";
    }
    
    // If no errors, insert the guide
    if (empty($errors)) {
        // Build the query
        $fields = implode(', ', array_keys($guide));
        $placeholders = implode(', ', array_fill(0, count($guide), '?'));
        
        $query = "INSERT INTO guides ({$fields}) VALUES ({$placeholders})";
        
        // Execute the query
        $result = $db->execute($query, array_values($guide));
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Guide '{$guide['title']}' created successfully."
            ];
            
            // Redirect to guides list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to create guide. Database error.";
        }
    }
}

// Generate next available guide id
$nextId = $db->getColumn("SELECT MAX(id) + 1 FROM guides") ?: 1;

// Initialize default guide values
$guide = [
    'id' => $nextId,
    'title' => '',
    'category' => '',
    'author' => '',
    'content' => '',
    'thumbnail' => '',
    'featured' => 0
];

// Get categories for dropdown
$categories = $db->getColumn("SELECT DISTINCT category FROM guides WHERE category != '' ORDER BY category");
?>

<div class="admin-container">
    <div class="admin-header-actions">
        <a href="index.php" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Back to Guides
        </a>
    </div>
    
    <div class="admin-content-card">
        <div class="admin-content-header">
            <h2>Add New Guide</h2>
        </div>
        
        <!-- Display validation errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="create.php" class="admin-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="id" class="form-label">ID</label>
                        <input type="number" id="id" name="id" value="<?= $guide['id'] ?>" class="form-control" required>
                        <span class="form-text">Unique identifier for this guide</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" name="title" value="<?= htmlspecialchars($guide['title']) ?>" class="form-control" required>
                        <span class="form-text">Title of the guide</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="category" class="form-label">Category</label>
                        <input type="text" id="category" name="category" value="<?= htmlspecialchars($guide['category']) ?>" class="form-control" list="category-list" required>
                        <datalist id="category-list">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= htmlspecialchars($category) ?>">
                            <?php endforeach; ?>
                        </datalist>
                        <span class="form-text">Category of the guide</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="author" class="form-label">Author</label>
                        <input type="text" id="author" name="author" value="<?= htmlspecialchars($guide['author']) ?>" class="form-control">
                        <span class="form-text">Author of the guide</span>
                    </div>
                    
                    <div class="form-group">
                        <label for="thumbnail" class="form-label">Thumbnail URL</label>
                        <input type="text" id="thumbnail" name="thumbnail" value="<?= htmlspecialchars($guide['thumbnail']) ?>" class="form-control">
                        <span class="form-text">URL to the guide thumbnail image</span>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="featured" name="featured" class="form-check-input" <?= $guide['featured'] ? 'checked' : '' ?>>
                        <label for="featured" class="form-check-label">Featured Guide</label>
                        <span class="form-text">Feature this guide on the homepage</span>
                    </div>
                </div>
                
                <!-- Guide Content -->
                <div class="form-section full-width">
                    <h3>Guide Content</h3>
                    
                    <div class="form-group">
                        <label for="content" class="form-label">Content</label>
                        <textarea id="content" name="content" class="form-control" rows="20" required><?= htmlspecialchars($guide['content']) ?></textarea>
                        <span class="form-text">Content of the guide (supports HTML)</span>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create Guide
                </button>
                <a href="index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Initialize the content editor
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#content'))
            .catch(error => {
                console.error(error);
            });
    }
});
</script>

<?php
// Include admin footer
require_once '../../includes/admin-footer.php';
?> 