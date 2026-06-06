<?php

/**
 * BaseModel
 *
 * Foundation class extended by all models.
 * Provides a PDO wrapper with prepared statements.
 * All queries go through prepare/execute — no raw SQL interpolation.
 */

class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Fetch all rows matching a query.
     *
     * @param string $sql    SQL query with ? or :named placeholders
     * @param array  $params Parameters to bind
     * @return array         Array of result objects
     */
    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single row.
     *
     * @param string $sql    SQL query with ? or :named placeholders
     * @param array  $params Parameters to bind
     * @return object|null   Result object or null if not found
     */
    protected function fetchOne(string $sql, array $params = []): ?object
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Execute an INSERT, UPDATE or DELETE query.
     *
     * @param string $sql    SQL query with ? or :named placeholders
     * @param array  $params Parameters to bind
     * @return bool          True on success
     */
    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Get the last inserted row ID.
     * Call immediately after execute() for INSERT queries.
     *
     * @return int Last insert ID
     */
    protected function lastInsertId(): int
    {
        return (int) $this->db->lastInsertId();
    }

    /**
     * Count rows matching a query.
     *
     * @param string $sql    SQL query returning a single COUNT column
     * @param array  $params Parameters to bind
     * @return int           Row count
     */
    protected function count(string $sql, array $params = []): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Begin a database transaction.
     */
    protected function beginTransaction(): void
    {
        $this->db->beginTransaction();
    }

    /**
     * Commit the current transaction.
     */
    protected function commit(): void
    {
        $this->db->commit();
    }

    /**
     * Roll back the current transaction.
     */
    protected function rollback(): void
    {
        $this->db->rollBack();
    }
}
