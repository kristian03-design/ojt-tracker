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

        // Support common Railway / cloud env var patterns. Prefer a single
        // DATABASE_URL when provided (e.g. mysql://user:pass@host:port/dbname).
        $databaseUrl = getenv('DATABASE_URL') ?: getenv('RAILWAY_DATABASE_URL') ?: getenv('JAWSDB_URL') ?: getenv('CLEARDB_DATABASE_URL') ?: getenv('MYSQL_DATABASE_URL');

        if ($databaseUrl) {
            $parts = parse_url($databaseUrl);
            $this->host = $parts['host'] ?? 'localhost';
            $this->port = $parts['port'] ?? 3306;
            $this->user = isset($parts['user']) ? urldecode($parts['user']) : '';
            $this->pass = isset($parts['pass']) ? urldecode($parts['pass']) : '';
            $this->db   = isset($parts['path']) ? ltrim($parts['path'], '/') : '';
        } else {
            // Fallback to older env var names used locally or by some providers
            $this->host = getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost';
            $this->db   = getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'ojt_tracker';
            $this->user = getenv('MYSQLUSER') ?: getenv('DB_USER') ?: 'root';
            $this->pass = getenv('MYSQLPASSWORD') ?: getenv('DB_PASS') ?: '';
            $this->port = getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306;
        }

        $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db};charset={$this->charset}";

        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->conn = new \PDO($dsn, $this->user, $this->pass, $opt);
        } catch (\PDOException $e) {
            // log and fail fast; Railway will restart container if exit >0
            error_log('Database connection failed: ' . $e->getMessage());
            http_response_code(500);
            exit('Database connection error');
        }
    }

    public static function getConnection() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }

        return self::$instance->conn;
    }
}