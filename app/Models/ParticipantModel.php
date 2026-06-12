<?php

/**
 * ParticipantModel
 *
 * Manages external contributors — persons, ensembles, orchestras.
 * URLs via entity_urls pivot (UrlModel).
 * Media via entity_media pivot (MediaModel).
 *
 * Name logic:
 *   type: person    → title (opt) + first_name + last_name
 *   type: ensemble  → first_name only (full ensemble name)
 *   type: orchestra → first_name only (full orchestra name)
 *
 * Slug generated from displayName() at app level — not stored in DB.
 */

class ParticipantModel extends BaseModel
{
    private string $table = 'participants';

    /**
     * Get all participants ordered by first_name.
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT p.*, pc.label as category_label
             FROM {$this->table} p
             LEFT JOIN participants_categories pc ON pc.id = p.category_id
             ORDER BY p.first_name ASC"
        );
    }

    /**
     * Get participant by ID.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT p.*, pc.label as category_label
             FROM {$this->table} p
             LEFT JOIN participants_categories pc ON pc.id = p.category_id
             WHERE p.id = ?",
            [$id]
        );
    }

    /**
     * Find participant by slug.
     * Slug generated from displayName() at app level.
     */
    public function getBySlug(string $slug): ?object
    {
        $participants = $this->getAll();

        foreach ($participants as $participant) {
            if (self::generateSlug($participant) === $slug) {
                return $participant;
            }
        }

        return null;
    }

    /**
     * Get participants for a specific event.
     */
    public function getForEvent(int $eventId): array
    {
        return $this->fetchAll(
            "SELECT p.*, pc.label as category_label
             FROM {$this->table} p
             LEFT JOIN participants_categories pc ON pc.id = p.category_id
             INNER JOIN event_participants ep ON ep.participant_id = p.id
             WHERE ep.event_id = ?
             ORDER BY p.first_name ASC",
            [$eventId]
        );
    }

    /**
     * Build display name from participant fields.
     *
     * person    → title (opt) + first_name + last_name
     * ensemble  → first_name only
     * orchestra → first_name only
     */
    public static function displayName(object $participant): string
    {
        if (($participant->type ?? '') !== 'individual') {
            return $participant->first_name ?? '';
        }

        return trim(implode(' ', array_filter([
            $participant->title      ?? null,
            $participant->first_name ?? null,
            $participant->last_name  ?? null,
        ])));
    }

    /**
     * Generate URL-safe slug from display name.
     */
    public static function generateSlug(object $participant): string
    {
        $name = self::displayName($participant);
        $slug = strtolower(trim($name));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Add a participant.
     */
    public function add(array $data): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table}
             (type, title, first_name, last_name, category_id, field, image, image_credit)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['type']         ?? null,
                $data['title']        ?? null,
                $data['first_name']   ?? null,
                $data['last_name']    ?? null,
                $data['category_id']  ?? null,
                $data['field']        ?? null,
                $data['image']        ?? null,
                $data['image_credit'] ?? null,
            ]
        );
    }

    /**
     * Update a participant.
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET type = ?, title = ?, first_name = ?, last_name = ?,
                 category_id = ?, field = ?, image = ?, image_credit = ?
             WHERE id = ?",
            [
                $data['type']         ?? null,
                $data['title']        ?? null,
                $data['first_name']   ?? null,
                $data['last_name']    ?? null,
                $data['category_id']  ?? null,
                $data['field']        ?? null,
                $data['image']        ?? null,
                $data['image_credit'] ?? null,
                $id,
            ]
        );
    }

    /**
     * Delete a participant — hard delete.
     * FK on event_participants is ON DELETE CASCADE. 
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }
}
