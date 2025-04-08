<?php
/**
 * Monster model for L1J Database Website
 */
class Monster {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get all monsters with pagination
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return array
     */
    public function getAllMonsters($page = 1, $limit = DEFAULT_LIMIT, $sort = 'npcid', $order = 'ASC') {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM npc 
                WHERE impl LIKE '%L1Monster%' 
                ORDER BY $sort $order
                LIMIT :limit OFFSET :offset";
        
        $monsters = $this->db->getRows($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM npc WHERE impl LIKE '%L1Monster%'";
        $total = $this->db->getColumn($totalSql);
        
        return [
            'monsters' => $monsters,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get monster by ID
     * @param int $id
     * @return array|null
     */
    public function getMonsterById($id) {
        $sql = "SELECT * FROM npc WHERE npcid = :id";
        
        $monster = $this->db->getRow($sql, ['id' => $id]);
        
        if ($monster) {
            // Get monster drops
            $dropsSql = "SELECT d.*, 
                         CASE 
                            WHEN w.item_id IS NOT NULL THEN w.desc_kr
                            WHEN a.item_id IS NOT NULL THEN a.desc_kr
                            ELSE e.desc_kr
                         END as item_name
                         FROM droplist d
                         LEFT JOIN weapon w ON d.itemId = w.item_id
                         LEFT JOIN armor a ON d.itemId = a.item_id
                         LEFT JOIN etcitem e ON d.itemId = e.item_id
                         WHERE d.mobId = :id
                         ORDER BY d.chance DESC";
            $monster['drops'] = $this->db->getRows($dropsSql, ['id' => $id]);
            
            // Get monster skills
            $skillsSql = "SELECT * FROM mobskill WHERE mobid = :id ORDER BY actNo ASC";
            $monster['skills'] = $this->db->getRows($skillsSql, ['id' => $id]);
            
            // Get spawn locations
            $spawnsSql = "SELECT s.*, m.locationname 
                          FROM spawnlist s
                          LEFT JOIN mapids m ON s.mapid = m.mapid
                          WHERE s.npc_templateid = :id";
            $monster['spawns'] = $this->db->getRows($spawnsSql, ['id' => $id]);
            
            // Get boss spawns if applicable
            if ($monster['is_bossmonster'] === 'true') {
                $bossSql = "SELECT sb.*, m.locationname 
                            FROM spawnlist_boss sb
                            LEFT JOIN mapids m ON sb.spawnMapId = m.mapid
                            WHERE sb.npcid = :id";
                $monster['boss_spawns'] = $this->db->getRows($bossSql, ['id' => $id]);
            }
        }
        
        return $monster;
    }
    
    /**
     * Search monsters by name or description
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function searchMonsters($keyword, $page = 1, $limit = DEFAULT_LIMIT) {
        $offset = ($page - 1) * $limit;
        $searchTerm = "%$keyword%";
        
        $sql = "SELECT * FROM npc 
                WHERE (desc_kr LIKE :keyword OR desc_en LIKE :keyword) 
                AND impl LIKE '%L1Monster%'
                ORDER BY npcid ASC
                LIMIT :limit OFFSET :offset";
        
        $monsters = $this->db->getRows($sql, [
            'keyword' => $searchTerm,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM npc 
                     WHERE (desc_kr LIKE :keyword OR desc_en LIKE :keyword) 
                     AND impl LIKE '%L1Monster%'";
        $total = $this->db->getColumn($totalSql, ['keyword' => $searchTerm]);
        
        return [
            'monsters' => $monsters,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get monsters by level range
     * @param int $minLevel
     * @param int $maxLevel
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getMonstersByLevel($minLevel, $maxLevel, $page = 1, $limit = DEFAULT_LIMIT) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM npc 
                WHERE lvl BETWEEN :minLevel AND :maxLevel 
                AND impl LIKE '%L1Monster%'
                ORDER BY lvl ASC, npcid ASC
                LIMIT :limit OFFSET :offset";
        
        $monsters = $this->db->getRows($sql, [
            'minLevel' => $minLevel,
            'maxLevel' => $maxLevel,
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM npc 
                     WHERE lvl BETWEEN :minLevel AND :maxLevel 
                     AND impl LIKE '%L1Monster%'";
        $total = $this->db->getColumn($totalSql, [
            'minLevel' => $minLevel,
            'maxLevel' => $maxLevel
        ]);
        
        return [
            'monsters' => $monsters,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Get boss monsters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getBossMonsters($page = 1, $limit = DEFAULT_LIMIT) {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT * FROM npc 
                WHERE is_bossmonster = 'true' 
                AND impl LIKE '%L1Monster%'
                ORDER BY lvl DESC, npcid ASC
                LIMIT :limit OFFSET :offset";
        
        $monsters = $this->db->getRows($sql, [
            'limit' => $limit,
            'offset' => $offset
        ]);
        
        $totalSql = "SELECT COUNT(*) FROM npc 
                     WHERE is_bossmonster = 'true' 
                     AND impl LIKE '%L1Monster%'";
        $total = $this->db->getColumn($totalSql);
        
        return [
            'monsters' => $monsters,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ];
    }
    
    /**
     * Create a new monster
     * @param array $data
     * @return int|bool
     */
    public function createMonster($data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->insert('npc', $data);
            
            $this->db->commit();
            return $result;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Update a monster
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateMonster($id, $data) {
        try {
            $this->db->beginTransaction();
            
            $result = $this->db->update('npc', $data, 'npcid = :id', ['id' => $id]);
            
            $this->db->commit();
            return $result > 0;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Delete a monster
     * @param int $id
     * @return bool
     */
    public function deleteMonster($id) {
        try {
            $this->db->beginTransaction();
            
            // Delete monster
            $this->db->delete('npc', 'npcid = :id', ['id' => $id]);
            
            // Delete related data
            $this->db->delete('droplist', 'mobId = :id', ['id' => $id]);
            $this->db->delete('mobskill', 'mobid = :id', ['id' => $id]);
            $this->db->delete('spawnlist', 'npc_templateid = :id', ['id' => $id]);
            $this->db->delete('spawnlist_boss', 'npcid = :id', ['id' => $id]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }
    
    /**
     * Add a drop to a monster
     * @param int $monsterId
     * @param int $itemId
     * @param int $min
     * @param int $max
     * @param int $chance
     * @return bool
     */
    public function addDrop($monsterId, $itemId, $min, $max, $chance) {
        try {
            // Get monster and item info for the drop table
            $monsterSql = "SELECT * FROM npc WHERE npcid = :id";
            $monster = $this->db->getRow($monsterSql, ['id' => $monsterId]);
            
            $itemSql = "SELECT 
                        CASE 
                            WHEN w.item_id IS NOT NULL THEN w.desc_kr
                            WHEN a.item_id IS NOT NULL THEN a.desc_kr
                            ELSE e.desc_kr
                        END as item_name,
                        CASE 
                            WHEN w.item_id IS NOT NULL THEN w.desc_en
                            WHEN a.item_id IS NOT NULL THEN a.desc_en
                            ELSE e.desc_en
                        END as item_name_en
                        FROM (
                            SELECT :itemId as id
                        ) as i
                        LEFT JOIN weapon w ON i.id = w.item_id
                        LEFT JOIN armor a ON i.id = a.item_id
                        LEFT JOIN etcitem e ON i.id = e.item_id";
            $item = $this->db->getRow($itemSql, ['itemId' => $itemId]);
            
            if (!$monster || !$item) {
                return false;
            }
            
            $data = [
                'mobId' => $monsterId,
                'mobname_kr' => $monster['desc_kr'],
                'mobname_en' => $monster['desc_en'],
                'moblevel' => $monster['lvl'],
                'itemId' => $itemId,
                'itemname_kr' => $item['item_name'],
                'itemname_en' => $item['item_name_en'],
                'min' => $min,
                'max' => $max,
                'chance' => $chance,
                'Enchant' => 0
            ];
            
            $this->db->insert('droplist', $data);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Update a monster drop
     * @param int $monsterId
     * @param int $itemId
     * @param int $min
     * @param int $max
     * @param int $chance
     * @return bool
     */
    public function updateDrop($monsterId, $itemId, $min, $max, $chance) {
        try {
            $data = [
                'min' => $min,
                'max' => $max,
                'chance' => $chance
            ];
            
            $this->db->update('droplist', $data, 'mobId = :mobId AND itemId = :itemId', [
                'mobId' => $monsterId,
                'itemId' => $itemId
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Delete a monster drop
     * @param int $monsterId
     * @param int $itemId
     * @return bool
     */
    public function deleteDrop($monsterId, $itemId) {
        try {
            $this->db->delete('droplist', 'mobId = :mobId AND itemId = :itemId', [
                'mobId' => $monsterId,
                'itemId' => $itemId
            ]);
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get monster stats for admin dashboard
     * @return array
     */
    public function getMonsterStats() {
        $stats = [];
        
        // Total monsters
        $totalSql = "SELECT COUNT(*) FROM npc WHERE impl LIKE '%L1Monster%'";
        $stats['total'] = $this->db->getColumn($totalSql);
        
        // Total boss monsters
        $bossSql = "SELECT COUNT(*) FROM npc WHERE is_bossmonster = 'true' AND impl LIKE '%L1Monster%'";
        $stats['bosses'] = $this->db->getColumn($bossSql);
        
        // Monster count by level range
        $levelRanges = [
            '1-10' => [1, 10],
            '11-20' => [11, 20],
            '21-40' => [21, 40],
            '41-60' => [41, 60],
            '61-80' => [61, 80],
            '81+' => [81, 999]
        ];
        
        $stats['level_ranges'] = [];
        
        foreach ($levelRanges as $label => $range) {
            $sql = "SELECT COUNT(*) FROM npc 
                    WHERE lvl BETWEEN :min AND :max 
                    AND impl LIKE '%L1Monster%'";
            $count = $this->db->getColumn($sql, ['min' => $range[0], 'max' => $range[1]]);
            
            $stats['level_ranges'][$label] = $count;
        }
        
        return $stats;
    }
    
    /**
     * Get the next available NPC ID
     * @return int
     */
    public function getNextNpcId() {
        $sql = "SELECT MAX(npcid) FROM npc";
        $maxId = $this->db->getColumn($sql);
        
        return $maxId ? $maxId + 1 : 1;
    }
}
