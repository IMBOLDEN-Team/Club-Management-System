<?php
/**
 * Database Connection Wrapper with Error Handling
 * Provides a robust database connection with comprehensive error handling
 */

require_once 'error_handler.php';

class Database {
    private static $instance = null;
    private $connection = null;
    private $host;
    private $username;
    private $password;
    private $database;
    private $port;

    private function __construct() {
        $this->host = 'localhost';
        $this->username = 'root';
        $this->password = '';
        $this->database = 'CLUB-MANAGEMENT-SYSTEM';
        $this->port = 3306;
        
        $this->connect();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection with error handling
     */
    private function connect() {
        try {
            $this->connection = mysqli_connect(
                $this->host,
                $this->username,
                $this->password,
                $this->database,
                $this->port
            );

            if (!$this->connection) {
                throw new Exception("Failed to connect to MySQL: " . mysqli_connect_error());
            }

            // Set charset to prevent encoding issues
            if (!mysqli_set_charset($this->connection, "utf8mb4")) {
                throw new Exception("Error loading character set utf8mb4: " . mysqli_error($this->connection));
            }

            // Set timezone
            mysqli_query($this->connection, "SET time_zone = '+00:00'");

        } catch (Exception $e) {
            ErrorHandler::getInstance()->handleDatabaseError($e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the database connection
     */
    public function getConnection() {
        if (!$this->connection || !mysqli_ping($this->connection)) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Execute a query with error handling
     */
    public function query($sql, $params = []) {
        try {
            $connection = $this->getConnection();
            
            if (!empty($params)) {
                $stmt = mysqli_prepare($connection, $sql);
                if (!$stmt) {
                    throw new Exception("Prepare failed: " . mysqli_error($connection));
                }
                
                // Bind parameters
                if (!empty($params)) {
                    $types = '';
                    $bindParams = [];
                    
                    foreach ($params as $param) {
                        if (is_int($param)) {
                            $types .= 'i';
                        } elseif (is_float($param)) {
                            $types .= 'd';
                        } else {
                            $types .= 's';
                        }
                        $bindParams[] = $param;
                    }
                    
                    array_unshift($bindParams, $types);
                    call_user_func_array([$stmt, 'bind_param'], $this->refValues($bindParams));
                }
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Execute failed: " . mysqli_stmt_error($stmt));
                }
                
                $result = mysqli_stmt_get_result($stmt);
                mysqli_stmt_close($stmt);
                
                return $result;
            } else {
                $result = mysqli_query($connection, $sql);
                if (!$result) {
                    throw new Exception("Query failed: " . mysqli_error($connection));
                }
                return $result;
            }
            
        } catch (Exception $e) {
            ErrorHandler::getInstance()->handleDatabaseError($e->getMessage(), $sql);
            return false;
        }
    }

    /**
     * Execute a query and return a single row
     */
    public function queryOne($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result && mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        return null;
    }

    /**
     * Execute a query and return all rows
     */
    public function queryAll($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result) {
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            return $rows;
        }
        return [];
    }

    /**
     * Execute an INSERT query and return the insert ID
     */
    public function insert($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result) {
            return mysqli_insert_id($this->getConnection());
        }
        return false;
    }

    /**
     * Execute an UPDATE or DELETE query and return affected rows
     */
    public function execute($sql, $params = []) {
        $result = $this->query($sql, $params);
        if ($result) {
            return mysqli_affected_rows($this->getConnection());
        }
        return false;
    }

    /**
     * Begin a transaction
     */
    public function beginTransaction() {
        try {
            $connection = $this->getConnection();
            if (!mysqli_begin_transaction($connection)) {
                throw new Exception("Failed to begin transaction: " . mysqli_error($connection));
            }
            return true;
        } catch (Exception $e) {
            ErrorHandler::getInstance()->handleDatabaseError($e->getMessage());
            return false;
        }
    }

    /**
     * Commit a transaction
     */
    public function commit() {
        try {
            $connection = $this->getConnection();
            if (!mysqli_commit($connection)) {
                throw new Exception("Failed to commit transaction: " . mysqli_error($connection));
            }
            return true;
        } catch (Exception $e) {
            ErrorHandler::getInstance()->handleDatabaseError($e->getMessage());
            return false;
        }
    }

    /**
     * Rollback a transaction
     */
    public function rollback() {
        try {
            $connection = $this->getConnection();
            if (!mysqli_rollback($connection)) {
                throw new Exception("Failed to rollback transaction: " . mysqli_error($connection));
            }
            return true;
        } catch (Exception $e) {
            ErrorHandler::getInstance()->handleDatabaseError($e->getMessage());
            return false;
        }
    }

    /**
     * Escape a string to prevent SQL injection
     */
    public function escape($string) {
        $connection = $this->getConnection();
        return mysqli_real_escape_string($connection, $string);
    }

    /**
     * Get the last error message
     */
    public function getLastError() {
        $connection = $this->getConnection();
        return mysqli_error($connection);
    }

    /**
     * Get the last error number
     */
    public function getLastErrorNo() {
        $connection = $this->getConnection();
        return mysqli_errno($connection);
    }

    /**
     * Close the database connection
     */
    public function close() {
        if ($this->connection) {
            mysqli_close($this->connection);
            $this->connection = null;
        }
    }

    /**
     * Helper function for binding parameters
     */
    private function refValues($arr) {
        $refs = [];
        foreach ($arr as $key => $value) {
            $refs[$key] = &$arr[$key];
        }
        return $refs;
    }

    /**
     * Destructor to ensure connection is closed
     */
    public function __destruct() {
        $this->close();
    }
}

// Create global database instance
$db = Database::getInstance();

// Legacy compatibility - maintain the old $connect variable
$connect = $db->getConnection();

// Helper functions for easy database operations
function db_query($sql, $params = []) {
    global $db;
    return $db->query($sql, $params);
}

function db_query_one($sql, $params = []) {
    global $db;
    return $db->queryOne($sql, $params);
}

function db_query_all($sql, $params = []) {
    global $db;
    return $db->queryAll($sql, $params);
}

function db_insert($sql, $params = []) {
    global $db;
    return $db->insert($sql, $params);
}

function db_execute($sql, $params = []) {
    global $db;
    return $db->execute($sql, $params);
}

function db_escape($string) {
    global $db;
    return $db->escape($string);
}

function db_begin_transaction() {
    global $db;
    return $db->beginTransaction();
}

function db_commit() {
    global $db;
    return $db->commit();
}

function db_rollback() {
    global $db;
    return $db->rollback();
}
?>
