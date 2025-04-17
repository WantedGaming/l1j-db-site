<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

$db = Database::getInstance();
$query = "SHOW COLUMNS FROM skills_info";
$columns = $db->getRows($query);

print_r($columns);
?> 