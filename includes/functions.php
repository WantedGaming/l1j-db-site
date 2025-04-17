<?php
/**
 * Helper functions for L1J Database Website
 */

require_once 'config.php';
require_once 'database.php';

/**
 * Sanitize user input
 * @param string $input
 * @return string
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a specific URL
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Set flash message
 * @param string $type (success, error, info, warning)
 * @param string $message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 * @return array|null
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlash() {
    $flash = getFlash();
    if ($flash) {
        $type = $flash['type'];
        $message = $flash['message'];
        echo "<div class='alert alert-$type'>$message</div>";
    }
}

/**
 * Format date
 * @param string $date
 * @param string $format
 * @return string
 */
function formatDate($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

/**
 * Generate pagination links
 * @param int $currentPage
 * @param int $totalPages
 * @param string $urlPattern
 * @return string
 */
function generatePagination($currentPage, $totalPages, $urlPattern) {
    $links = '';
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $links .= '<ul class="pagination">';
    
    // Previous link
    if ($currentPage > 1) {
        $prevPage = $currentPage - 1;
        $links .= "<li><a href='" . sprintf($urlPattern, $prevPage) . "'>&laquo; Previous</a></li>";
    } else {
        $links .= "<li class='disabled'><span>&laquo; Previous</span></li>";
    }
    
    // Page numbers
    $startPage = max($currentPage - 2, 1);
    $endPage = min($startPage + 4, $totalPages);
    
    if ($startPage > 1) {
        $links .= "<li><a href='" . sprintf($urlPattern, 1) . "'>1</a></li>";
        if ($startPage > 2) {
            $links .= "<li class='disabled'><span>...</span></li>";
        }
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $links .= "<li class='active'><span>$i</span></li>";
        } else {
            $links .= "<li><a href='" . sprintf($urlPattern, $i) . "'>$i</a></li>";
        }
    }
    
    if ($endPage < $totalPages) {
        if ($endPage < $totalPages - 1) {
            $links .= "<li class='disabled'><span>...</span></li>";
        }
        $links .= "<li><a href='" . sprintf($urlPattern, $totalPages) . "'>$totalPages</a></li>";
    }
    
    // Next link
    if ($currentPage < $totalPages) {
        $nextPage = $currentPage + 1;
        $links .= "<li><a href='" . sprintf($urlPattern, $nextPage) . "'>Next &raquo;</a></li>";
    } else {
        $links .= "<li class='disabled'><span>Next &raquo;</span></li>";
    }
    
    $links .= '</ul>';
    
    return $links;
}

/**
 * Get the CSS class for a grade
 * @param string $grade
 * @return string
 */
function getGradeClass($grade) {
    switch (strtoupper($grade)) {
        case 'NORMAL':
            return 'badge-normal';
        case 'ADVANC':
            return 'badge-advanced';
        case 'RARE':
            return 'badge-rare';
        case 'HERO':
            return 'badge-hero';
        case 'LEGEND':
            return 'badge-legend';
        case 'MYTH':
            return 'badge-myth';
        case 'ONLY':
            return 'badge-only';
        default:
            return 'badge-normal';
    }
}

/**
 * Get the CSS class for a grade badge
 * @param string $grade
 * @return string
 */
function getGradeBadgeClass($grade) {
    switch (strtoupper($grade)) {
        case 'NORMAL':
            return 'badge-normal';
        case 'ADVANC':
            return 'badge-advanced';
        case 'RARE':
            return 'badge-rare';
        case 'HERO':
            return 'badge-hero';
        case 'LEGEND':
            return 'badge-legend';
        case 'MYTH':
            return 'badge-myth';
        case 'ONLY':
            return 'badge-only';
        default:
            return 'badge-normal';
    }
}

/**
 * Format the grade of an item for display
 * @param string $grade
 * @return string
 */
function formatGrade($grade) {
    switch(strtoupper($grade)) {
        case 'NORMAL':
            return 'Normal';
        case 'ADVANC':
            return 'Advanced';
        case 'RARE':
            return 'Rare';
        case 'HERO':
            return 'Hero';
        case 'LEGEND':
            return 'Legend';
        case 'MYTH':
            return 'Myth';
        case 'ONLY':
            return 'Only';
        default:
            return $grade;
    }
}

/**
 * Format material name for display
 * @param string|null $material
 * @return string
 */
function formatMaterial($material) {
    if ($material === null) {
        return '';
    }
    
    // Remove any text in parentheses (including Korean text)
    $material = preg_replace('/\([^)]+\)/', '', $material);
    
    // Convert underscores to spaces and trim
    $material = trim(str_replace('_', ' ', $material));
    
    // Capitalize first letter of each word
    return ucwords(strtolower($material));
}

/**
 * Check if an item's icon file exists in the assets directory
 * @param int $iconId
 * @param string $siteUrl
 * @return bool
 */
function isItemAvailable($iconId, $siteUrl) {
    $iconPath = $_SERVER['DOCUMENT_ROOT'] . parse_url($siteUrl, PHP_URL_PATH) . '/assets/img/items/' . $iconId . '.png';
    return file_exists($iconPath);
}

/**
 * Get item type label
 * @param string $type
 * @return string
 */
function getItemTypeLabel($type) {
    // Convert enum values to readable labels
    $types = [
        'SWORD' => 'Sword',
        'DAGGER' => 'Dagger',
        'TOHAND_SWORD' => 'Two-Handed Sword',
        'BOW' => 'Bow',
        'SPEAR' => 'Spear',
        'BLUNT' => 'Blunt',
        'STAFF' => 'Staff',
        'STING' => 'Sting',
        'ARROW' => 'Arrow',
        'GAUNTLET' => 'Gauntlet',
        'CLAW' => 'Claw',
        'EDORYU' => 'Edoryu',
        'SINGLE_BOW' => 'Single Bow',
        'SINGLE_SPEAR' => 'Single Spear',
        'TOHAND_BLUNT' => 'Two-Handed Blunt',
        'TOHAND_STAFF' => 'Two-Handed Staff',
        'KEYRINGK' => 'Keyringk',
        'CHAINSWORD' => 'Chain Sword',
        // Armor types
        'HELMET' => 'Helmet',
        'ARMOR' => 'Armor',
        'T_SHIRT' => 'T-Shirt',
        'CLOAK' => 'Cloak',
        'GLOVE' => 'Glove',
        'BOOTS' => 'Boots',
        'SHIELD' => 'Shield',
        'AMULET' => 'Amulet',
        'RING' => 'Ring',
        'BELT' => 'Belt',
        'RING_2' => 'Ring',
        'EARRING' => 'Earring',
        'GARDER' => 'Garder',
        'RON' => 'Ron',
        'PAIR' => 'Pair',
        'SENTENCE' => 'Sentence',
        'SHOULDER' => 'Shoulder',
        'BADGE' => 'Badge',
        'PENDANT' => 'Pendant',
        // Etc item types
        'POTION' => 'Potion',
        'FOOD' => 'Food',
        'SCROLL' => 'Scroll',
        'QUEST_ITEM' => 'Quest Item',
        'SPELL_BOOK' => 'Spell Book',
        'GEM' => 'Gem',
        'MATERIAL' => 'Material',
        'TREASURE_BOX' => 'Treasure Box',
        'TOTEM' => 'Totem',
        'NONE' => 'Other',
    ];
    
    return isset($types[$type]) ? $types[$type] : $type;
}

/**
 * Format money with commas
 * @param int $amount
 * @return string
 */
function formatMoney($amount) {
    return number_format($amount);
}

/**
 * Calculate percentage
 * @param int $part
 * @param int $total
 * @return int
 */
function calculatePercentage($part, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($part / $total) * 100);
}

/**
 * Get current URL
 * @return string
 */
function getCurrentUrl() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}

/**
 * Check if a string contains a substring
 * @param string $haystack
 * @param string $needle
 * @return bool
 */
function strContains($haystack, $needle) {
    return strpos($haystack, $needle) !== false;
}

/**
 * Get file extension
 * @param string $filename
 * @return string
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if file is an image
 * @param string $filename
 * @return bool
 */
function isImage($filename) {
    $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    return in_array(getFileExtension($filename), $imageExtensions);
}

/**
 * Generate a random string
 * @param int $length
 * @return string
 */
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/**
 * Debug function to print arrays/objects
 * @param mixed $data
 * @param bool $die
 */
function debug($data, $die = false) {
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    if ($die) {
        die();
    }
}

/**
 * Calculate drop rates for a specific monster
 * @param int $npcId Monster NPC ID
 * @return array Array of drop items with details
 */
function calculateMonsterDrops($npcId) {
    $db = Database::getInstance();
    
    // Query to get all drops for this monster with item details
    $query = "SELECT d.itemId, d.min, d.max, d.chance, 
                     COALESCE(a.desc_en, w.desc_en, e.desc_en) as item_name,
                     COALESCE(a.iconId, w.iconId, e.iconId) as iconId
              FROM droplist d
              LEFT JOIN armor a ON d.itemId = a.item_id
              LEFT JOIN weapon w ON d.itemId = w.item_id
              LEFT JOIN etcitem e ON d.itemId = e.item_id
              WHERE d.mobId = ?
              ORDER BY d.chance DESC";
    
    $drops = $db->getRows($query, [$npcId]);
    
    $result = [];
    foreach ($drops as $drop) {
        $result[] = [
            'item_id' => $drop['itemId'],
            'item_name' => $drop['item_name'] ?? 'Unknown Item',
            'icon_id' => $drop['iconId'] ?? $drop['itemId'],
            'min' => $drop['min'],
            'max' => $drop['max'],
            'chance' => formatDropChance($drop['chance']),
            'raw_chance' => $drop['chance']
        ];
    }
    
    return $result;
}

/**
 * Format the drop chance of an item
 * @param int $chance
 * @return string
 */
function formatDropChance($chance) {
    if ($chance >= 1000000) {
        return '100%';
    } elseif ($chance >= 10000) {
        return round($chance / 10000, 2) . '%';
    } else {
        return round($chance / 10000, 4) . '%';
    }
}

/**
 * Get drop rates for multiple monsters at once
 * @param array $npcIds Array of monster NPC IDs
 * @return array Multi-dimensional array of drops keyed by NPC ID
 */
function getBulkMonsterDrops($npcIds) {
    if (empty($npcIds)) return [];
    
    $db = Database::getInstance();
    $placeholders = implode(',', array_fill(0, count($npcIds), '?'));
    
    $query = "SELECT d.mobId, d.itemId, d.min, d.max, d.chance, 
                     COALESCE(a.desc_en, w.desc_en, e.desc_en) as item_name,
                     COALESCE(a.iconId, w.iconId, e.iconId) as iconId
              FROM droplist d
              LEFT JOIN armor a ON d.itemId = a.item_id
              LEFT JOIN weapon w ON d.itemId = w.item_id
              LEFT JOIN etcitem e ON d.itemId = e.item_id
              WHERE d.mobId IN ($placeholders)
              ORDER BY d.mobId, d.chance DESC";
    
    $allDrops = $db->getRows($query, $npcIds);
    
    $result = [];
    foreach ($allDrops as $drop) {
        if (!isset($result[$drop['mobId']])) {
            $result[$drop['mobId']] = [];
        }
        
        $result[$drop['mobId']][] = [
            'item_id' => $drop['itemId'],
            'item_name' => $drop['item_name'] ?? 'Unknown Item',
            'icon_id' => $drop['iconId'] ?? $drop['itemId'],
            'min' => $drop['min'],
            'max' => $drop['max'],
            'chance' => formatDropChance($drop['chance']),
            'raw_chance' => $drop['chance']
        ];
    }
    
    return $result;
}

/**
 * Get monster image path (legacy wrapper for backward compatibility)
 */
function get_monster_image($spriteId) {
    // Define possible image paths
    $paths = [
        ROOT_PATH . "/assets/img/monsters/ms{$spriteId}.png",
        ROOT_PATH . "/assets/img/monsters/ms{$spriteId}.gif",
        ROOT_PATH . "/assets/img/monsters/ms{$spriteId}.jpg",
        ROOT_PATH . "/assets/img/placeholders/monster-placeholder.png"
    ];

    // Find the first existing image
    foreach ($paths as $path) {
        if (file_exists($path)) {
            // Convert server path to URL path by removing ROOT_PATH and adding SITE_URL
            return SITE_URL . str_replace(ROOT_PATH, '', $path);
        }
    }

    // Fallback to placeholder if no image found
    return SITE_URL . "/assets/img/placeholders/monster-placeholder.png";
}

/**
 * Format undead type display name
 * @param string $undeadType The undead type code
 * @return string The formatted display name
 */
function formatUndeadType($undeadType) {
    switch($undeadType) {
        case 'UNDEAD':
            return 'Undead';
        case 'DEMON':
            return 'Demon';
        case 'UNDEAD_BOSS':
            return 'Undead Boss';
        case 'DRANIUM':
            return 'Dranium';
        default:
            return 'Normal';
    }
}

/**
 * Format attribute weakness
 * @param string $attr The attribute code
 * @return string The formatted attribute name
 */
function formatWeakAttr($attr) {
    switch($attr) {
        case 'EARTH':
            return 'Earth';
        case 'FIRE':
            return 'Fire';
        case 'WATER':
            return 'Water';
        case 'WIND':
            return 'Wind';
        default:
            return 'None';
    }
}

/**
 * Format poison attack type
 * @param string $poisonType The poison type code
 * @return string The formatted poison type name
 */
function formatPoisonAtk($poisonType) {
    switch($poisonType) {
        case 'DAMAGE':
            return 'Damage';
        case 'PARALYSIS':
            return 'Paralysis';
        case 'SILENCE':
            return 'Silence';
        default:
            return 'None';
    }
}

/**
 * Get badge class for monster type
 * @param array $monster The monster data array
 * @return string The CSS class for the badge
 */
function getMonsterTypeBadge($monster) {
    if($monster['is_bossmonster'] === 'true') {
        return 'badge-danger';
    } elseif($monster['undead'] !== 'NONE') {
        switch($monster['undead']) {
            case 'UNDEAD_BOSS':
                return 'badge-danger';
            case 'DEMON':
                return 'badge-legend';
            case 'UNDEAD':
                return 'badge-rare';
            case 'DRANIUM':
                return 'badge-hero';
            default:
                return 'badge-normal';
        }
    } else {
        return 'badge-normal';
    }
}

/**
 * Get map image path
 * @param int $pngId The PNG ID of the map
 * @return string The URL path to the map image
 */
function get_map_image($pngId) {
    if ($pngId > 0) {
        $base_path = ROOT_PATH;
        
        // Try jpeg format
        $image_path = "/assets/img/maps/{$pngId}.jpeg";
        $server_path = $base_path . $image_path;
        
        // Try png format if jpeg doesn't exist
        if (!file_exists($server_path)) {
            $image_path = "/assets/img/maps/{$pngId}.png";
            $server_path = $base_path . $image_path;
        }
        
        // Try jpg format if png doesn't exist
        if (!file_exists($server_path)) {
            $image_path = "/assets/img/maps/{$pngId}.jpg";
            $server_path = $base_path . $image_path;
        }
        
        // If any of the formats exist, return the URL
        if (file_exists($server_path)) {
            return SITE_URL . $image_path;
        }
    }
    
    return SITE_URL . '/assets/img/maps/default.jpg';
}

/**
 * Generate URL for pagination with updated page number
 * @param int $newPage The page number to generate URL for
 * @return string The URL with updated page parameter
 */
function getPaginationUrl($newPage) {
    $params = $_GET;
    $params['page'] = $newPage;
    return '?' . http_build_query($params);
}

/**
 * Clean item names by removing special prefixes
 * @param string $name The item name to clean
 * @return string Cleaned item name
 */
function cleanItemName($name) {
    return preg_replace('/\\\\a[a-zA-Z]/', '', $name);
}

/**
 * Get availability status text for an item
 * @param bool $isAvailable Whether the item is available
 * @return string Status text
 */
function getAvailabilityStatus($isAvailable) {
    return $isAvailable ? 'Available' : 'Not Available In-Game';
}

/**
 * Get availability status HTML for an item with appropriate styling
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
 * @param bool $isAvailable Whether the item is available
 * @return string CSS class name or empty string
 */
function getUnavailableItemRowClass($isAvailable) {
    return $isAvailable ? '' : 'unavailable-item';
}

/**
 * Sanitize output for HTML display
 */
function h($text) {
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}

