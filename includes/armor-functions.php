<?php
/**
 * Armor-related functions for L1J Database Website
 */

// Helper function to format armor type
function formatArmorType($type) {
    switch($type) {
        case 'ARMOR':
            return 'Armor';
        case 'HELM':
            return 'Helmet';
        case 'SHIELD':
            return 'Shield';
        case 'T':
            return 'T-Shirt';
        case 'CLOAK':
            return 'Cloak';
        case 'GLOVE':
            return 'Gloves';
        case 'BOOTS':
            return 'Boots';
        case 'AMULET':
            return 'Amulet';
        case 'RING':
            return 'Ring';
        case 'BELT':
            return 'Belt';
        case 'EARRING':
            return 'Earring';
        case 'GARDER':
            return 'Garter';
        default:
            return ucfirst(strtolower($type));
    }
}
?>
