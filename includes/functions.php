<?php
/**
 * Helper functions for L1J Database Website
 */

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
 * Get item grade CSS class
 * @param string $grade
 * @return string
 */
function getGradeClass($grade) {
    switch (strtoupper($grade)) {
        case 'NORMAL':
            return 'badge-normal';
        case 'ADVANC':
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
