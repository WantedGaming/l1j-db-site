<?php
/**
 * Admin - Edit Guide
 */

// Set page title
$pageTitle = 'Edit Guide';

// Include admin header
require_once '../../includes/admin-header.php';

// Get database instance
$db = Database::getInstance();

// Get guide ID from URL
$guideId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Redirect if no ID provided
if (!$guideId) {
    header('Location: index.php');
    exit;
}

// Load guide data
$guide = $db->getRow("SELECT * FROM guides WHERE id = ?", [$guideId]);

// Redirect if guide not found
if (!$guide) {
    $_SESSION['admin_message'] = ['type' => 'error', 'message' => 'Guide not found.'];
    header('Location: index.php');
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect guide data from form
    $updatedGuide = [
        'title' => $_POST['title'] ?? '',
        'category' => $_POST['category'] ?? '',
        'author' => $_POST['author'] ?? '',
        'content' => $_POST['content'] ?? '',
        'updated_at' => date('Y-m-d H:i:s'),
        'featured' => isset($_POST['featured']) ? 1 : 0,
        'thumbnail' => $_POST['thumbnail'] ?? ''
    ];
    
    // Validation
    $errors = [];
    
    // Required fields
    if (empty($updatedGuide['title'])) {
        $errors[] = "Guide title is required";
    }
    
    if (empty($updatedGuide['content'])) {
        $errors[] = "Guide content is required";
    }
    
    if (empty($updatedGuide['category'])) {
        $errors[] = "Guide category is required";
    }
    
    // If no errors, update the guide
    if (empty($errors)) {
        // Build the query parts
        $setParts = [];
        $params = [];
        
        foreach ($updatedGuide as $field => $value) {
            $setParts[] = "$field = ?";
            $params[] = $value;
        }
        
        // Add the WHERE parameter (guide ID)
        $params[] = $guideId;
        
        $query = "UPDATE guides SET " . implode(', ', $setParts) . " WHERE id = ?";
        
        // Execute the query
        $result = $db->execute($query, $params);
        
        if ($result) {
            // Set success message
            $_SESSION['admin_message'] = [
                'type' => 'success',
                'message' => "Guide '{$updatedGuide['title']}' updated successfully."
            ];
            
            // Redirect to guides list
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Failed to update guide. Database error.";
        }
    }
    
    // Update the guide variable with form data if there were errors
    if (!empty($errors)) {
        $guide = array_merge($guide, $updatedGuide);
    }
}

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
            <h2>Edit Guide: <?= htmlspecialchars($guide['title']) ?></h2>
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
        
        <form method="POST" action="edit.php?id=<?= $guideId ?>" class="admin-form">
            <div class="form-grid">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-group">
                        <label for="id" class="form-label">ID</label>
                        <input type="number" id="id" value="<?= $guide['id'] ?>" class="form-control" disabled>
                        <span class="form-text">Unique identifier for this guide (cannot be changed)</span>
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
                        <input type="text" id="thumbnail" name="thumbnail" value="<?= htmlspecialchars($guide['thumbnail'] ?? '') ?>" class="form-control">
                        <span class="form-text">URL to the guide thumbnail image</span>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" id="featured" name="featured" class="form-check-input" <?= $guide['featured'] ? 'checked' : '' ?>>
                        <label for="featured" class="form-check-label">Featured Guide</label>
                        <span class="form-text">Feature this guide on the homepage</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Created</label>
                        <div class="form-static-text"><?= date('Y-m-d H:i:s', strtotime($guide['created_at'])) ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Updated</label>
                        <div class="form-static-text"><?= date('Y-m-d H:i:s', strtotime($guide['updated_at'])) ?></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Views</label>
                        <div class="form-static-text"><?= number_format($guide['view_count']) ?></div>
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
                    <i class="fas fa-save"></i> Save Changes
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