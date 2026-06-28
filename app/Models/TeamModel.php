<?php

/**
 * TeamModel
 *
 * Manages team members.
 * Media via entity_media pivot (MediaModel) — no direct image column.
 * URLs via entity_urls pivot (UrlModel).
 * Slug generated at app level from first_name + last_name.
 * Soft delete via deleted_at — preserves history for future event/project credits.
 */

class TeamModel extends BaseModel
{
    private string $table = 'team';

    /**
     * Get all active team members (not soft-deleted), ordered by last name.
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table}
             WHERE deleted_at IS NULL
             ORDER BY last_name ASC"
        );
    }

    /**
     * Get a single team member by ID.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Find team member by slug (app-generated from first + last name).
     */
    public function getBySlug(string $slug): ?object
    {
        $members = $this->getAll();

        foreach ($members as $member) {
            if (self::generateSlug($member->first_name, $member->last_name) === $slug) {
                return $member;
            }
        }

        return null;
    }

    /**
     * Add a new team member.
     * Returns inserted ID for slug generation and redirect.
     */
    public function add(array $data): int|false
    {
        $ok = $this->execute(
            "INSERT INTO {$this->table}
             (first_name, last_name, title, role, profession, motto, biography, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['first_name'] ?? null,
                $data['last_name']  ?? null,
                $data['title']      ?? null,
                $data['role']       ?? null,
                $data['profession'] ?? null,
                $data['motto']      ?? null,
                $data['biography']  ?? null,
                $data['status']     ?? 'draft',
            ]
        );

        return $ok ? $this->lastInsertId() : false;
    }

    /**
     * Update a single field — used by entity-edit-row AJAX saves.
     */
    public function updateField(int $id, string $field, mixed $value): bool
    {
        $allowed = ['first_name', 'last_name', 'title', 'role', 'profession', 'motto', 'biography', 'status'];

        if (!in_array($field, $allowed)) return false;

        return $this->execute(
            "UPDATE {$this->table} SET {$field} = ? WHERE id = ?",
            [$value ?: null, $id]
        );
    }

    /**
     * Update a team member — full row update.
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET first_name = ?, last_name = ?, title = ?, role = ?,
                 profession = ?, motto = ?, biography = ?
             WHERE id = ?",
            [
                $data['first_name'] ?? null,
                $data['last_name']  ?? null,
                $data['title']      ?? null,
                $data['role']       ?? null,
                $data['profession'] ?? null,
                $data['motto']      ?? null,
                $data['biography']  ?? null,
                $id,
            ]
        );
    }

    /**
     * Publish a team member.
     */
    public function publish(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 'published' WHERE id = ?",
            [$id]
        );
    }

    /**
     * Unpublish a team member — sets status back to draft.
     */
    public function unpublish(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 'draft' WHERE id = ?",
            [$id]
        );
    }

    /**
     * Soft delete — preserves history for future event/project credits.
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    /**
     * Generate slug from first and last name.
     */
    public static function generateSlug(string $firstName, string $lastName): string
    {
        $slug = strtolower(trim($firstName) . '-' . trim($lastName));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Get full display name with optional title.
     */
    public static function displayName(object $member): string
    {
        $parts = array_filter([
            $member->title      ?? null,
            $member->first_name ?? null,
            $member->last_name  ?? null,
        ]);
        return implode(' ', $parts);
    }
}
