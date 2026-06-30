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
             ORDER BY order_index ASC"
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
        // New members are appended to the end of the draggable order —
        // never claim order_index 0, which is reserved for whoever the
        // editor designates as legal representative via org-edit.
        $orderIndex = $data['order_index'] ?? ($this->getMaxOrderIndex() + 1);

        $ok = $this->execute(
            "INSERT INTO {$this->table}
             (first_name, last_name, title, role, profession, motto, biography, status, order_index)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['first_name'] ?? null,
                $data['last_name']  ?? null,
                $data['title']      ?? null,
                $data['role']       ?? null,
                $data['profession'] ?? null,
                $data['motto']      ?? null,
                $data['biography']  ?? null,
                $data['status']     ?? 'draft',
                $orderIndex,
            ]
        );

        return $ok ? $this->lastInsertId() : false;
    }

    /**
     * Update a single field — used by entity-edit-row AJAX saves.
     */
    public function updateField(int $id, string $field, mixed $value): bool
    {
        $allowed = ['first_name', 'last_name', 'title', 'role', 'profession', 'motto', 'biography', 'status', 'order_index'];

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
     * Get the legal representative — the team member at order_index 0.
     * Fully editor-controlled via org-edit's select, decoupled from
     * role text entirely — no longer inferred from "präsident" matching,
     * which was typo-prone and broke silently on free-text role entries.
     * Returns null if no published member currently holds position 0.
     */
    public function getLegalRepresentative(): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table}
             WHERE deleted_at IS NULL
             AND status = 'published'
             AND order_index = 0
             LIMIT 1"
        );
    }

    /**
     * Highest order_index currently in use — for appending new or
     * demoted members to the end of the draggable sequence.
     */
    public function getMaxOrderIndex(): int
    {
        $result = $this->fetchOne(
            "SELECT MAX(order_index) as max_index FROM {$this->table} WHERE deleted_at IS NULL"
        );
        return (int) ($result->max_index ?? -1);
    }

    /**
     * Set a team member as legal representative (order_index 0).
     * Whoever previously held position 0 is appended to the end of
     * the draggable sequence — never deleted, never auto-assigned a
     * "demoted" position the editor didn't choose. Two separate
     * updates rather than a swap, since the previous holder's new
     * position is intentionally "end of list", not the new
     * representative's old slot.
     */
    public function setLegalRepresentative(int $id): bool
    {
        $previous = $this->fetchOne(
            "SELECT * FROM {$this->table}
         WHERE deleted_at IS NULL AND order_index = 0
         LIMIT 1"
        );

        if ($previous && $previous->id === $id) {
            return true;
        }

        if ($previous) {
            $this->execute(
                "UPDATE {$this->table} SET order_index = ? WHERE id = ?",
                [$this->getMaxOrderIndex() + 1, $previous->id]
            );
        }

        return $this->execute(
            "UPDATE {$this->table} SET order_index = 0 WHERE id = ?",
            [$id]
        );
    }

    /**
     * Reorder the draggable team grid (order_index 1+).
     * Position 0 (legal representative) is never included here — it's
     * only ever changed via setLegalRepresentative() from org-edit,
     * never through team-grid drag-and-drop.
     *
     * @param array $order  [['id' => int, 'order_index' => int], ...]
     */
    public function reorderTeam(array $order): void
    {
        foreach ($order as $item) {
            $id    = (int) ($item['id'] ?? 0);
            $index = (int) ($item['order_index'] ?? 0);
            if ($id && $index >= 1) {
                $this->execute(
                    "UPDATE {$this->table} SET order_index = ? WHERE id = ?",
                    [$index, $id]
                );
            }
        }
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
