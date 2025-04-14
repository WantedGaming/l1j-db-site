<?php
/**
 * Weapon-related functions for L1J Database Website
 */

// Helper function to format weapon type
function formatWeaponType($type) {
    switch($type) {
        case 'SWORD':
            return 'Sword';
        case 'DAGGER':
            return 'Dagger';
        case 'TOHAND_SWORD':
            return 'Two-Handed Sword';
        case 'BOW':
            return 'Bow';
        case 'SPEAR':
            return 'Spear';
        case 'BLUNT':
            return 'Blunt';
        case 'STAFF':
            return 'Staff';
        case 'THROW_WEAPON':
            return 'Throwing Weapon';
        case 'ARROW':
            return 'Arrow';
        case 'GAUNTLET':
            return 'Gauntlet';
        case 'CLAW':
            return 'Claw';
        case 'EDORYU':
            return 'Edoryu';
        case 'SINGLE_BOW':
            return 'Single Bow';
        case 'SINGLE_SPEAR':
            return 'Single Spear';
        case 'KIRINGKU':
            return 'Kiringku';
        case 'CHAINSWORD':
            return 'Chain Sword';
        default:
            return ucfirst(strtolower($type));
    }
}
?>