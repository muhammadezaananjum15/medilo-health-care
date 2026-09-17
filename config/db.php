<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Database Connection & PDO Helper Library
 * 
 * Implements prepared statements for all SQL queries to prevent SQL Injection attack vectors.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/auth_check.php';


class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            // First connect without database name to ensure DB creation if missing
            $dsn_no_db = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $tmp_pdo = new PDO($dsn_no_db, DB_USER, DB_PASS, $options);
            $tmp_pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            // Now connect to specific database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Auto-seed table structures if empty
            $this->checkAndInitializeSchema();

        } catch (PDOException $e) {
            die("Database Connection Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Executes a prepared query safely with bound parameters
     * 
     * @param string $sql SQL query string with parameter placeholders (?)
     * @param array $params Array of parameters to bind
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database Query Error: " . $e->getMessage() . " | SQL: " . $sql);
            throw new Exception("Database operation failed: " . $e->getMessage());
        }
    }

    /**
     * Automatic Database Schema Initialization Handler
     */
    private function checkAndInitializeSchema() {
        try {
            $stmt = $this->pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() == 0) {
                $schemaFile = __DIR__ . '/../database/schema.sql';
                if (file_exists($schemaFile)) {
                    $sql = file_get_contents($schemaFile);
                    $this->pdo->exec($sql);
                }
            }
        } catch (Exception $e) {
            error_log("Schema initialization error: " . $e->getMessage());
        }
    }
}

// Global DB function helper for easy access
function getDB() {
    return Database::getInstance()->getConnection();
}

/**
 * Execute prepared SELECT query and fetch all matching records
 */
function fetchAll($sql, $params = []) {
    return Database::getInstance()->query($sql, $params)->fetchAll();
}

/**
 * Execute prepared SELECT query and fetch single matching record
 */
function fetchOne($sql, $params = []) {
    return Database::getInstance()->query($sql, $params)->fetch();
}

/**
 * Execute INSERT, UPDATE, or DELETE query and return last insert ID or affected rows
 */
function executeQuery($sql, $params = []) {
    $db = Database::getInstance();
    $stmt = $db->query($sql, $params);
    $conn = $db->getConnection();
    $lastId = $conn->lastInsertId();
    return $lastId ? $lastId : $stmt->rowCount();
}
