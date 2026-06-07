<?php

/**
 * EditorModel
 *
 * Extends UserModel for authorised_editors table.
 * Inherits all authentication methods (findByEmail, generateOtp, validateOtp, clearOtp).
 * Adds editor-specific CRUD methods.
 */

class EditorModel extends UserModel
{
    public function __construct()
    {
        parent::__construct('authorised_editors');
    }

    // ── Editor CRUD ──────────────────────────────────────────

    /**
     * Fetch all authorised editors.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT id, name, email, can_manage_editors, created_at
             FROM {$this->table}
             ORDER BY created_at ASC"
        );
    }

    /**
     * Add a new authorised editor.
     *
     * @param string $name
     * @param string $email
     * @param bool   $canManageEditors
     * @return bool
     */
    public function add(string $name, string $email, bool $canManageEditors = false): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (name, email, can_manage_editors)
             VALUES (?, ?, ?)",
            [$name, strtolower(trim($email)), $canManageEditors]
        );
    }

    /**
     * Update an editor's details.
     *
     * @param int    $id
     * @param string $name
     * @param bool   $canManageEditors
     * @return bool
     */
    public function update(int $id, string $name, bool $canManageEditors): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET name = ?, can_manage_editors = ?
             WHERE id = ?",
            [$name, $canManageEditors, $id]
        );
    }

    /**
     * Remove an authorised editor.
     * Cannot remove yourself — enforced at controller level.
     *
     * @param int $id
     * @return bool
     */
    public function remove(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }
}
