<?php
/**
 * Database connection helper for L1J Database Website
 */

class Database {
    private $host;
    private $username;
    private $password;
    private $dbname;
    private $charset;
    private $conn;
    private static $instance = null;

    /**
     * Constructor - Loads database configuration
     */
    private function __construct() {
        require_once 'config.php';
        
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->dbname = DB_NAME;
        $this->charset = 'utf8mb4';
        
        $this->connect();
    }
    
    /**
     * Get database instance (Singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Create database connection
     */
    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute a query with parameters
     * @param string $sql
     * @param array $params
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die("Query failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get a single row
     * @param string $sql
     * @param array $params
     * @return array|null
     */
    public function getRow($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get multiple rows
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function getRows($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get the value of a single column
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function getColumn($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Insert a record and return the last inserted ID
     * @param string $table
     * @param array $data
     * @return int
     */
    public function insert($table, $data) {
        // Build the column names and placeholders
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
		// Build the query
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            
            // Bind each value
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            return $this->conn->lastInsertId();
        } catch (PDOException $e) {
            die("Insert failed: " . $e->getMessage());
        }
    }
    
    /**
     * Update a record
     * @param string $table
     * @param array $data
     * @param string $where
     * @param array $whereParams
     * @return int Number of rows affected
     */
    public function update($table, $data, $where, $whereParams = []) {
        // Build the SET part of the query
        $setParts = [];
        foreach (array_keys($data) as $key) {
            $setParts[] = "$key = :$key";
        }
        $setClause = implode(', ', $setParts);
        
        // Build the complete query
        $sql = "UPDATE $table SET $setClause WHERE $where";
        
        try {
            $stmt = $this->conn->prepare($sql);
            
            // Bind data values
            foreach ($data as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            // Bind where values
            foreach ($whereParams as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die("Update failed: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a record
     * @param string $table
     * @param string $where
     * @param array $params
     * @return int Number of rows affected
     */
    public function delete($table, $where, $params = []) {
        $sql = "DELETE FROM $table WHERE $where";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            die("Delete failed: " . $e->getMessage());
        }
    }
    
    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        $this->conn->beginTransaction();
    }
    
    /**
     * Commit a transaction
     */
    public function commit() {
        $this->conn->commit();
    }
    
    /**
     * Rollback a transaction
     */
    public function rollback() {
        $this->conn->rollBack();
    }
	
	public function execute($query, $params = []) {
    $stmt = $this->connection->prepare($query);
    return $stmt->execute($params);
	}
	
    /**
     * Check if a column exists in a table
     * @param string $table Table name
     * @param string $column Column name
     * @return bool Whether the column exists
     */
    public function columnExists($table, $column) {
        try {
            $sql = "SHOW COLUMNS FROM $table LIKE ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$column]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            // Table might not exist
            return false;
        }
    }
}
