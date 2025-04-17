<?php
/**
 * Item-specific functions for L1J Database Website
 */

// Include the main functions file if not already included
require_once 'functions.php';

/**
 * Format item type for display
 * @param string $type
 * @return string
 */
function formatItemType($type) {
    $type = str_replace('_', ' ', $type);
    return ucwords(strtolower($type));
}

/**
 * Check if an item property is displayable
 * @param array $item
 * @param string $property
 * @return bool
 */
function isPropertyDisplayable($item, $property) {
    return isset($item[$property]) && $item[$property] != 0 && $item[$property] != 'NONE' && $item[$property] != '';
}

/**
 * Format use type for display
 * @param string $useType
 * @return string
 */
function formatUseType($useType) {
    $useType = str_replace('_', ' ', $useType);
    return ucwords(strtolower($useType));
} 