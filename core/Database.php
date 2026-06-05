<?php

/**
 * Database
 * 
 * PDO singleton connection
 * Ensures only one database connection is created per request.
 * Credentials loaded form environment variables via .env
 */

class Database
{
    private static ?Database $instance = null;
    private PDO $connection;

    /** 
     * Private constructor (prevents direct instantiation)
     * Reads credentials from environment and opens PDO connection. 
     * 
     * @throws RuntimeException if connection fails.
     */
    private function __construct()
    {
        $host = $_ENV['DB_HOST'] ?? null;
        $name = $_ENV['DB_NAME'] ?? null;
        $user = $_ENV['DB_USER'] ?? null;
        $password = $_ENV['DB_PASS'] ?? null;

        if (!$host || !$name || !$user ) {
            throw new RuntimeException('Database credentials are missing from environment');
            
        }

        $config  = require __DIR__ . '/../config/app.php';
        $charset = $config['db_charset'];

        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->connection = new PDO($dsn, $user, $password, $options);
        } catch (PDOException $e) {
            throw new RuntimeException('Database connection failed: '. $e->getMessage());
        }
    }

    /**
     * Returns the singleton instante.
     * Creates it on fist call, reuses on subsequent calls.
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
 
        return static::$instance;
    }
 
    /**
     * Returns the raw PDO connection.
     * Used by BaseModel to run prepared statements.
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
 
    /**
     * Prevent cloning of the singleton instance.
     */
    private function __clone() {}

    /**
     * Prevent unserialization of the singleton instance.
     */
    public function __wakeup(): never
    {
        throw new RuntimeException('Cannot unserialize singleton.');
    }
    
    public function __unserialize(array $data): never
    {
        throw new RuntimeException('Cannot unserialize singleton.');
    }
}