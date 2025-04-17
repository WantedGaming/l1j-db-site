<?php
/**
 * Helper functions for skill-related operations
 */

/**
 * Get the URL for a skill icon
 */
function get_skill_icon_url($castgfx, $tooltipStrId, $info_icon) {
    if (!empty($info_icon)) {
        return SITE_URL . "/assets/img/skills/" . $info_icon . ".png";
    }
    
    if (!empty($tooltipStrId)) {
        return SITE_URL . "/assets/img/skills/" . $tooltipStrId . ".png";
    }
    
    // Fallback to castgfx
    return SITE_URL . "/assets/img/skills/default/" . $castgfx . ".png";
}

/**
 * Get the CSS class for rarity badge
 */
function get_rarity_badge_class($rarity) {
    $classes = [
        1 => 'badge-common',
        2 => 'badge-uncommon',
        3 => 'badge-rare',
        4 => 'badge-epic',
        5 => 'badge-legendary',
    ];

    return isset($classes[$rarity]) ? $classes[$rarity] : 'badge-common';
}

/**
 * Get the text representation of rarity
 */
function get_rarity_text($grade) {
    switch ($grade) {
        case 0:
            return 'Common';
        case 1:
            return 'Uncommon';
        case 2:
            return 'Rare';
        case 3:
            return 'Epic';
        case 4:
            return 'Legendary';
        default:
            return 'Common';
    }
}

/**
 * Get the name of the skill type
 */
function get_type_name($type) {
    switch ($type) {
        case 'active':
            return 'Active';
        case 'passive':
            return 'Passive';
        case 'buff':
            return 'Buff';
        case 'debuff':
            return 'Debuff';
        case 'NONE':
            return '';
        default:
            return '';
    }
}

/**
 * Get the name of the target type
 */
function get_target_type($target) {
    switch ($target) {
        case 'self':
            return 'Self';
        case 'target':
            return 'Single Target';
        case 'area':
            return 'Area';
        case 'none':
            return 'None';
        default:
            return 'Unknown';
    }
}

/**
 * Get the name of the class
 */
function get_class_name($classType) {
    switch ($classType) {
        case 'knight':
            return 'Knight';
        case 'wizard':
            return 'Wizard';
        case 'elf':
            return 'Elf';
        case 'darkelf':
            return 'Dark Elf';
        case 'dragonknight':
            return 'Dragon Knight';
        case 'illusionist':
            return 'Illusionist';
        default:
            return 'Unknown';
    }
}

/**
 * Get the name of the attribute
 */
function get_attribute_name($attr) {
    switch ($attr) {
        case 'fire':
            return 'Fire';
        case 'water':
            return 'Water';
        case 'earth':
            return 'Earth';
        case 'wind':
            return 'Wind';
        case 'ray':
            return 'Ray';
        case 'none':
            return 'None';
        default:
            return 'Unknown';
    }
}

/**
 * Get the target name for a skill target type
 * 
 * @param int $target_type The target type ID
 * @return string The target name
 */
function get_target_name($target_type) {
    $target_names = [
        0 => 'Self',
        1 => 'Target',
        2 => 'Party',
        3 => 'Clan',
        4 => 'PK',
        5 => 'Undead',
        6 => 'Area',
        7 => 'AOE',
        8 => 'Enemy Party',
        9 => 'Enemy Clan',
        10 => 'NPC'
    ];
    
    return isset($target_names[$target_type]) ? $target_names[$target_type] : 'Unknown';
}

/**
 * Format skill level requirements for display
 */
function format_skill_requirements($skill) {
    $requirements = [];
    
    // Combine class and level requirements
    if (!empty($skill['classType']) && $skill['classType'] != 'none') {
        $class_name = get_class_name($skill['classType']);
        if (!empty($skill['required_level'])) {
            $requirements[] = "Level " . $skill['required_level'] . " " . $class_name;
        } else {
            $requirements[] = $class_name;
        }
    } else if (!empty($skill['required_level'])) {
        $requirements[] = "Level " . $skill['required_level'];
    }
    
    // Add any additional skill requirements
    if (!empty($skill['required_skill'])) {
        $requirements[] = "Requires " . $skill['required_skill'];
    }
    
    return implode(' • ', $requirements);
} 