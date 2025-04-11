<?php
/**
 * AJAX endpoint to get armor set pieces
 */

// Include required files
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/auth.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get set ID from query string
$setId = isset($_GET['set_id']) ? intval($_GET['set_id']) : 0;

if ($setId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid set ID']);
    exit;
}

// Get database instance
$db = Database::getInstance();

// Get all armor pieces in this set
$query = "SELECT item_id, desc_en, type, iconId FROM armor WHERE Set_Id = ?";
$pieces = $db->getRows($query, [$setId]);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($pieces);
