<?php

/**
 * VenueModel
 *
 * Manages physical locations for events and projects.
 * Venues are reusable — created once, linked to many events.
 * URLs managed via urls table (entity_type: 'venue').
 */

class VenueModel extends BaseModel
{
    private string $table = 'venues';

    /**
     * Get all venues ordered by name.
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY name ASC"
        );
    }

    /**
     * Get venue by ID.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get formatted address string.
     */
    public static function formatAddress(object $venue): string
    {
        $parts = array_filter([
            $venue->street   ?? null,
            trim(($venue->postcode ?? '') . ' ' . ($venue->city ?? '')),
            $venue->country  ?? null,
        ]);
        return implode(', ', $parts);
    }

    /**
     * Add a new venue.
     */
    public function add(array $data): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (name, street, postcode, city, country)
             VALUES (?, ?, ?, ?, ?)",
            [
                $data['name']     ?? null,
                $data['street']   ?? null,
                $data['postcode'] ?? null,
                $data['city']     ?? null,
                $data['country']  ?? null,
            ]
        );
    }

    /**
     * Update a venue.
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET name = ?, street = ?, postcode = ?, city = ?, country = ?
             WHERE id = ?",
            [
                $data['name']     ?? null,
                $data['street']   ?? null,
                $data['postcode'] ?? null,
                $data['city']     ?? null,
                $data['country']  ?? null,
                $id,
            ]
        );
    }

    /**
     * Delete a venue — hard delete.
     * FK on events.venue_id is ON DELETE SET NULL — safe to delete. 
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }
}
