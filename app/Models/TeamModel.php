<?php

/**
 * TeamModel
 *
 * Fetches team members from the team table.
 * URLs fetched separately via UrlModel.
 * Slug generated at app level from first_name + last_name.
 */

class TeamModel extends BaseModel
{
    private string $table = 'team';

    /**
     * Get all team members ordered by last name.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table}
             ORDER BY last_name ASC"
        );
    }

    /**
     * Get a single team member by ID.
     *
     * @param int $id
     * @return object|null
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
     * Slug format: strtolower(first_name . '-' . last_name)
     * e.g. 'monica-guillen'
     *
     * @param string $slug
     * @return object|null
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
     *
     * @param array $data Member data
     * @return bool
     */
    public function add(array $data): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table}
             (first_name, last_name, title, role, profession, motto, biography, image, image_credits)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['first_name']    ?? null,
                $data['last_name']     ?? null,
                $data['title']         ?? null,
                $data['role']          ?? null,
                $data['profession']    ?? null,
                $data['motto']         ?? null,
                $data['biography']     ?? null,
                $data['image']         ?? null,
                $data['image_credits'] ?? null,
            ]
        );
    }

    /**
     * Update a team member.
     *
     * @param int   $id   Member ID
     * @param array $data Updated data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET first_name = ?, last_name = ?, title = ?, role = ?,
                 profession = ?, motto = ?, biography = ?, image = ?, image_credits = ?
             WHERE id = ?",
            [
                $data['first_name']    ?? null,
                $data['last_name']     ?? null,
                $data['title']         ?? null,
                $data['role']          ?? null,
                $data['profession']    ?? null,
                $data['motto']         ?? null,
                $data['biography']     ?? null,
                $data['image']         ?? null,
                $data['image_credits'] ?? null,
                $id,
            ]
        );
    }

    /**
     * Delete a team member — hard delete.
     * Team data is not historical record for this system.
     *
     * @param int $id Member ID
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Generate slug from first and last name.
     * Used by controller and views — single source of truth.
     *
     * @param string $firstName
     * @param string $lastName
     * @return string
     */
    public static function generateSlug(string $firstName, string $lastName): string
    {
        $slug = strtolower(trim($firstName) . '-' . trim($lastName));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug); // handle umlauts
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Get full display name with optional title.
     *
     * @param object $member
     * @return string
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
