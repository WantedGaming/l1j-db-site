<?php
/**
 * Item model for L1J Database Website
 */
class Item {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all weapons with pagination
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getAllWeapons($page = 1, $limit = DEFAULT_LIMIT, $sort = 'item_id', $order = 'ASC') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT w.*, e.iconId FROM weapon w
                LEFT JOIN etcitem e ON e.item_id = w.item_id
                ORDER BY $sort $order
                LIMIT :limit OFFSET :offset";
        
        $weapons = $this->db->getRows($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM weapon";
        $total = $this->db->getColumn($totalSql);
        
        return [
            'items' => $weapons,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get all armor with pagination
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getAllArmor($page = 1, $limit = DEFAULT_LIMIT, $sort = 'item_id', $order = 'ASC') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT a.*, e.iconId FROM armor a
                LEFT JOIN etcitem e ON e.item_id = a.item_id
                ORDER BY $sort $order
                LIMIT :limit OFFSET :offset";
        
        $armor = $this->db->getRows($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM armor";
        $total = $this->db->getColumn($totalSql);
        
        return [
            'items' => $armor,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get all etcitems with pagination
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getAllEtcItems($page = 1, $limit = DEFAULT_LIMIT, $sort = 'item_id', $order = 'ASC') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM etcitem
                ORDER BY $sort $order
                LIMIT :limit OFFSET :offset";
        
        $items = $this->db->getRows($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM etcitem";
        $total = $this->db->getColumn($totalSql);
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get weapon by ID
     * @param int $id
     * @return array|null
     */
    public function getWeaponById($id) {
        $sql = "SELECT w.*, e.iconId FROM weapon w
                LEFT JOIN etcitem e ON e.item_id = w.item_id
                WHERE w.item_id = :id";
        
        $weapon = $this->db->getRow($sql, ['id' => $id]);
        
        if ($weapon) {
            // Get weapon skills
            $skillSql = "SELECT * FROM weapon_skill WHERE weapon_id = :id";
            $weapon['skills'] = $this->db->getRows($skillSql, ['id' => $id]);
            
            // Get item enchant abilities
            $enchantSql = "SELECT * FROM item_enchant_ablity WHERE itemId = :id ORDER BY enchant ASC";
            $weapon['enchant_abilities'] = $this->db->getRows($enchantSql, ['id' => $id]);
        }
        
        return $weapon;
    }
    
    /**
     * Get armor by ID
     * @param int $id
     * @return array|null
     */
    public function getArmorById($id) {
        $sql = "SELECT a.*, e.iconId FROM armor a
                LEFT JOIN etcitem e ON e.item_id = a.item_id
                WHERE a.item_id = :id";
        
        $armor = $this->db->getRow($sql, ['id' => $id]);
        
        if ($armor) {
            // Get armor set if applicable
            $setSql = "SELECT * FROM armor_set WHERE sets LIKE :pattern";
            $armor['set'] = $this->db->getRow($setSql, ['pattern' => '%' . $id . '%']);
            
            // Get item enchant abilities
            $enchantSql = "SELECT * FROM item_enchant_ablity WHERE itemId = :id ORDER BY enchant ASC";
            $armor['enchant_abilities'] = $this->db->getRows($enchantSql, ['id' => $id]);
        }
        
        return $armor;
    }
    
    /**
     * Get etcitem by ID
     * @param int $id
     * @return array|null
     */
    public function getEtcItemById($id) {
        $sql = "SELECT * FROM etcitem WHERE item_id = :id";
        
        $item = $this->db->getRow($sql, ['id' => $id]);
        
        if ($item) {
            // Check if item is inside any box
            $boxSql = "SELECT ib.*, e.desc_kr FROM item_box ib
                      LEFT JOIN etcitem e ON e.item_id = ib.boxId
                      WHERE ib.itemId = :id";
            $item['found_in_boxes'] = $this->db->getRows($boxSql, ['id' => $id]);
            
            // Check if item is a box itself
            $contentsSql = "SELECT ib.*, e.desc_kr FROM item_box ib
                           LEFT JOIN etcitem e ON e.item_id = ib.itemId
                           WHERE ib.boxId = :id";
            $item['box_contents'] = $this->db->getRows($contentsSql, ['id' => $id]);
        }
        
        return $item;
    }
    
    /**
     * Search items by name or description
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function searchItems($keyword, $page = 1, $limit = DEFAULT_LIMIT) {
        $offset = ($page - 1) * $limit;
        $searchTerm = "%$keyword%";
        
        // Search in weapons
        $weaponSql = "SELECT 'weapon' AS table_name, w.* FROM weapon w 
                      WHERE w.desc_kr LIKE :keyword 
                      OR w.desc_en LIKE :keyword";
        $weapons = $this->db->getRows($weaponSql, ['keyword' => $searchTerm]);
        
        // Search in armor
        $armorSql = "SELECT 'armor' AS table_name, a.* FROM armor a 
                     WHERE a.desc_kr LIKE :keyword 
                     OR a.desc_en LIKE :keyword";
        $armor = $this->db->getRows($armorSql, ['keyword' => $searchTerm]);
        
        // Search in etcitems
        $etcSql = "SELECT 'etcitem' AS table_name, e.* FROM etcitem e 
                   WHERE e.desc_kr LIKE :keyword 
                   OR e.desc_en LIKE :keyword";
        $etcitems = $this->db->getRows($etcSql, ['keyword' => $searchTerm]);
        
        // Combine results
        $allItems = array_merge($weapons, $armor, $etcitems);
        
        // Sort by relevance (exact name match first, then description match)
        usort($allItems, function($a, $b) use ($keyword) {
            $aExactMatch = strtolower($a['desc_kr']) === strtolower($keyword) || strtolower($a['desc_en']) === strtolower($keyword);
            $bExactMatch = strtolower($b['desc_kr']) === strtolower($keyword) || strtolower($b['desc_en']) === strtolower($keyword);
            
            if ($aExactMatch && !$bExactMatch) return -1;
            if (!$aExactMatch && $bExactMatch) return 1;
            
            return 0;
        });
        
        // Get total and paginate
        $total = count($allItems);
        $items = array_slice($allItems, $offset, $limit);
        
        return [
            'items' => $items,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get drops for a specific item
     * @param int $itemId
     * @return array
     */
    public function getItemDrops($itemId) {
        $sql = "SELECT d.*, n.desc_kr AS monster_name 
                FROM droplist d
                JOIN npc n ON d.mobId = n.npcid
                WHERE d.itemId = :itemId
                ORDER BY d.chance DESC";
        
        return $this->db->getRows($sql, ['itemId' => $itemId]);
    }
    
    /**
     * Get shop locations for a specific item
     * @param int $itemId
     * @return array
     */
    public function getItemShops($itemId) {
        $sql = "SELECT s.*, n.desc_kr AS npc_name 
                FROM shop s
                JOIN npc n ON s.npc_id = n.npcid
                WHERE s.item_id = :itemId";
        
        return $this->db->getRows($sql, ['itemId' => $itemId]);
    }
    
    /**
     * Create a new weapon
     * @param array $data
     * @return int|bool
     */
    public function createWeapon($data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->insert('weapon', $data);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Update a weapon
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateWeapon($id, $data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->update('weapon', $data, 'item_id = :id', ['id' => $id]);
            
            $this->db->commit();
            return $result > 0;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Create a new armor
     * @param array $data
     * @return int|bool
     */
    public function createArmor($data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->insert('armor', $data);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Update an armor
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateArmor($id, $data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->update('armor', $data, 'item_id = :id', ['id' => $id]);
            
            $this->db->commit();
            return $result > 0;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Create a new etcitem
     * @param array $data
     * @return int|bool
     */
    public function createEtcItem($data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->insert('etcitem', $data);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Update an etcitem
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateEtcItem($id, $data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->update('etcitem', $data, 'item_id = :id', ['id' => $id]);
            
            $this->db->commit();
            return $result > 0;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Delete an item (from all item tables)
     * @param int $id
     * @return bool
     */
    public function deleteItem($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete from all item tables
            $this->db->delete('weapon', 'item_id = :id', ['id' => $id]);
            $this->db->delete('armor', 'item_id = :id', ['id' => $id]);
            $this->db->delete('etcitem', 'item_id = :id', ['id' => $id]);
            
            // Also delete related data
            $this->db->delete('weapon_skill', 'weapon_id = :id', ['id' => $id]);
            $this->db->delete('item_enchant_ablity', 'itemId = :id', ['id' => $id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Get items for admin dashboard
     * @return array
     */
    public function getItemStats() {
        $stats = [];
        
        // Count weapons
        $weaponSql = "SELECT COUNT(*) FROM weapon";
        $stats['weapons'] = $this->db->getColumn($weaponSql);
        
        // Count armor
        $armorSql = "SELECT COUNT(*) FROM armor";
        $stats['armor'] = $this->db->getColumn($armorSql);
        
        // Count etcitems
        $etcSql = "SELECT COUNT(*) FROM etcitem";
        $stats['etcitems'] = $this->db->getColumn($etcSql);
        
        // Count by grade
        $gradeSql = "SELECT itemGrade, COUNT(*) as count FROM (
                        SELECT itemGrade FROM weapon
                        UNION ALL
                        SELECT itemGrade FROM armor
                    ) as combined
                    GROUP BY itemGrade
                    ORDER BY FIELD(itemGrade, 'ONLY', 'MYTH', 'LEGEND', 'HERO', 'RARE', 'ADVANC', 'NORMAL')";
        $stats['grades'] = $this->db->getRows($gradeSql);
        
        return $stats;
    }
    
    /**
     * Get the next available item_id
     * @return int
     */
    public function getNextItemId() {
        // Find the maximum item_id across all item tables
        $sql = "SELECT MAX(item_id) FROM (
                    SELECT item_id FROM weapon
                    UNION ALL
                    SELECT item_id FROM armor
                    UNION ALL
                    SELECT item_id FROM etcitem
                ) as combined";
        
        $maxId = $this->db->getColumn($sql);
        
        return $maxId ? $maxId + 1 : 1;
    }
}