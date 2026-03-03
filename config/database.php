<?php
// config/database.php
// PDO connection

class Database {

    private static $instance = null;
    private $conn;

    private $host;
    private $db;
    private $user;
    private $pass;
    private $port; //  important
    private $charset = 'utf8mb4';

    private function __construct() {

        $this->host = getenv('MYSQLHOST') ?: 'localhost';
        $this->db   = getenv('MYSQLDATABASE') ?: 'ojt_tracker';
        $this->user = getenv('MYSQLUSER') ?: 'root';
        $this->pass = getenv('MYSQLPASSWORD') ?: '';
        $this->port = getenv('MYSQLPORT') ?: 3306;

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";

        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->conn = new PDO($dsn, $this->user, $this->pass, $opt);
    }

    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance->conn;
    }
}