<?php

/**
 * ContributorModel
 *
 * Handles all DB operations for the contributors table.
 * Type is a free string — partner, foerderer, unterstuetzer, institution,
 * or any custom value the editor sets. No enum enforcement.
 *
 * Status lifecycle: draft → published
 * Draft contributors are visible to editors, hidden from public.
 * Draft cards always sort first (order_index = 0 by default).
 * Editor reorders via drag after publishing.
 *
 * Used by:
 *   ContributorController → /unterstuetzer (public listing + editor CRUD)
 */

require_once __DIR__ . '/BaseModel.php';

class ContributorModel extends BaseModel
{
    protected string $table = 'contributors';

    /**
     * Get all non-deleted contributors ordered by order_index.
     * Published only for public-facing views.
     */
    public function getAll(bool $publishedOnly = false): array
    {
        $where = $publishedOnly
            ? "WHERE status = 'published' AND deleted_at IS NULL"
            : "WHERE deleted_at IS NULL";

        return $this->fetchAll(
            "SELECT * FROM {$this->table} {$where} ORDER BY status ASC, name ASC"
        );
    }

    /**
     * Get a single contributor by ID.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL",
            [$id]
        ) ?: null;
    }

    /**
     * Add a new draft contributor.
     */
    public function add(string $name): int|false
    {
        $this->execute(
            "INSERT INTO {$this->table} (name, status) VALUES (?, 'draft')",
            [$name]
        );

        return $this->lastInsertId() ?: false;
    }

    /**
     * Update a single field.
     */
    public function updateField(int $id, string $field, mixed $value): bool
    {
        $allowed = ['name', 'type', 'description', 'url', 'status'];

        if (!in_array($field, $allowed)) return false;

        return $this->execute(
            "UPDATE {$this->table} SET {$field} = ? WHERE id = ?",
            [$value ?: null, $id]
        );
    }

    /**
     * Publish a contributor.
     */
    public function publish(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 'published' WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Unpublish a contributor (back to draft).
     */
    public function unpublish(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 'draft' WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Soft delete — only when status is draft.
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET deleted_at = NOW()
             WHERE id = ? AND status = 'draft' AND deleted_at IS NULL",
            [$id]
        );
    }
}
