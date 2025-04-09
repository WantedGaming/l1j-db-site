<?php
/**
 * Weapon-related functions for L1J Database Website
 */

// Helper function to format material name
function formatMaterial($material) {
    // Remove Korean part if exists
    $material = trim($material);
    // Convert to Title Case instead of all uppercase
    $material = ucwords(strtolower($material));
    return $material;
}

// Helper function to format weapon type
function formatWeaponType($type) {
    switch($type) {
        case 'SWORD':
            return 'Sword';
        case 'DAGGER':
            return 'Dagger';
        case 'TOHAND_SWORD':
            return 'Two-Hand Sword';
        case 'BOW':
            return 'Bow';
        case 'SPEAR':
            return 'Spear';
        case 'BLUNT':
            return 'Blunt';
        case 'STAFF':
            return 'Staff';
        case 'CLAW':
            return 'Claw';
        case 'EDORYU':
            return 'Edoryu';
        case 'GAUNTLET':
            return 'Gauntlet';
        case 'KIRINGKU':
            return 'Kiringku';
        case 'CHAINSWORD':
            return 'Chain Sword';
        case 'AXE':
            return 'Axe';
        case 'STICK':
            return 'Stick';
        default:
            return ucfirst(strtolower($type));
    }
}

// Helper function to get badge class based on item grade
function getGradeBadgeClass($grade) {
    switch($grade) {
        case 'ONLY':
            return 'badge-only';
        case 'MYTH':
            return 'badge-myth';
        case 'LEGEND':
            return 'badge-legend';
        case 'HERO':
            return 'badge-hero';
        case 'RARE':
            return 'badge-rare';
        default:
            return 'badge-normal';
    }
}

// Helper function to format grade for display
function formatGrade($grade) {
    switch($grade) {
        case 'ADVANC':
            return 'Advanced';
        default:
            return ucfirst(strtolower($grade));
    }
}

// Helper function to clean item names (remove prefixes like "\aG")
function cleanItemName($name) {
    return preg_replace('/\\\\a[a-zA-Z]/', '', $name);
}

/**
 * Check if an item is available to players in the game
 * Items without images are considered unavailable
 * 
 * @param int $iconId The item's icon ID
 * @param string $sitePath The site path (optional)
 * @return bool True if the item is available, false otherwise
 */
function isItemAvailable($iconId, $sitePath = '') {
    // If no icon ID is provided, consider item unavailable
    if (empty($iconId)) {
        return false;
    }
    
    // Build the path to the icon image
    $imagePath = $sitePath . '/assets/img/items/' . $iconId . '.png';
    
    // If we're checking server-side
    if (empty($sitePath)) {
        // Check if the file exists on the server
        return file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/img/items/' . $iconId . '.png');
    }
    
    // For client-side, we'll use a different approach (this is just a placeholder)
    // In practice, we can't reliably check file existence from client side
    // so we'll need to either:
    // 1. Have server generate this info for us
    // 2. Rely on onerror event in HTML (as we do in the listings page)
    return true;
}

/**
 * Get availability status text for an item
 * 
 * @param bool $isAvailable Whether the item is available
 * @return string Status text
 */
function getAvailabilityStatus($isAvailable) {
    return $isAvailable ? 'Available' : 'Not Available In-Game';
}

/**
 * Get availability status HTML for an item with appropriate styling
 * 
 * @param bool $isAvailable Whether the item is available
 * @return string Status HTML with badge
 */
function getAvailabilityStatusHTML($isAvailable) {
    if ($isAvailable) {
        return '<span class="badge badge-success">Available</span>';
    } else {
        return '<span class="badge badge-danger">Not Available In-Game</span>';
    }
}

/**
 * Apply row styling for unavailable items
 * Adds a CSS class for styling rows with unavailable items
 * 
 * @param bool $isAvailable Whether the item is available
 * @return string CSS class name or empty string
 */
function getUnavailableItemRowClass($isAvailable) {
    return $isAvailable ? '' : 'unavailable-item';
}
?>