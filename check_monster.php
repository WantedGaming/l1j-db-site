<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/database.php';

// Get database instance
$db = Database::getInstance();

// Check database connection
echo "Database connection: ";
try {
    $query = "SELECT 1";
    $result = $db->getRow($query);
    echo "SUCCESS\n";
} catch (Exception $e) {
    echo "FAILED - " . $e->getMessage() . "\n";
}

// Check if npc table exists
echo "\nChecking npc table: ";
try {
    $query = "SHOW TABLES LIKE 'npc'";
    $result = $db->getRow($query);
    echo (!empty($result)) ? "EXISTS\n" : "DOES NOT EXIST\n";
} catch (Exception $e) {
    echo "ERROR - " . $e->getMessage() . "\n";
}

// Look for monsters in the database
echo "\nLooking for monsters: \n";
try {
    $query = "SELECT npcid, desc_en FROM npc WHERE impl LIKE '%L1Monster%' LIMIT 5";
    $monsters = $db->getRows($query);
    if (!empty($monsters)) {
        foreach ($monsters as $monster) {
            echo "ID: " . $monster['npcid'] . ", Name: " . $monster['desc_en'] . "\n";
        }
    } else {
        echo "No monsters found with L1Monster implementation\n";
    }
} catch (Exception $e) {
    echo "ERROR - " . $e->getMessage() . "\n";
}

// Try to get monster with ID 1
echo "\nLooking for monster with ID 1: ";
try {
    $query = "SELECT npcid, desc_en FROM npc WHERE npcid = 1";
    $monster = $db->getRow($query);
    echo (!empty($monster)) ? "FOUND - " . $monster['desc_en'] . "\n" : "NOT FOUND\n";
} catch (Exception $e) {
    echo "ERROR - " . $e->getMessage() . "\n";
}
?>
