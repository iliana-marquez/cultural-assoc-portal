<?php

/**
 * Url Model
 * 
 * Manages all external URLs across the system. 
 * Polymorphic - entity_type and entity_id adapt to any entitiy.
 * 
 *  entity_type: 'organisation' | 'team' | 'participant'
 *                'contributor'  | 'event' | 'project' | 'venue'
 *  entity_id:   the id of the specific record
 * 
 * Usage:
 *  - $urlModel->getForEntity('team', $memberId); 
 *  - $urlModel->add('participant', $participantId, $urlTypeId, $url); 
 *  - $urlModel->delete($urlId, 'team'); 
 */

class UrlModel extends BaseModel
{
    private string $table = 'urls';

    /**
     * Fetch all URLs for a given entity.
     * Joins url_types to include label and icon. 
     * 
     * @param string  $entityType Entity type strig
     * @param int     $entityId   Entity record ID
     * @return array               Array of URL objects with label and icon
     */
    public function getForEntity(string $entityType, int $entityId): array
    {
        return $this->fetchAll(
            "SELECT u.id, u.url, ut.label, ut.icon
            FROM {$this->table} u
            JOIN url_types ut ON u.url_type_id = ut.id
            WHERE u.entity_type = ?
            AND u.entity_id = ?
            ORDER BY ut.id ASC",
            [$entityType, $entityId]
        );
    }

    /**
     * Fetch all available URL types
     * Used to populate dropdowns in edit mode
     * 
     * @return array Array of url_type objects
     */
    public function getTypes(): array
    {
        return $this->fetchAll(
            "SELECT id, label, icon
            FROM url_types
            ORDER BY id ASC"
        );
    }

    /**
     * Add a URL for a given entity.
     * 
     * @param string $entityType  Entity type string
     * @param int    $entityId    Entity record ID  
     * @param int    $urlTypeId   URL type ID from url_types table
     * @param string $url         The URL value 
     * @return bool
     */
    public function add(string $entityType, int $entityId, int $urlTypeId, string $url): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table} (entity_type, entity_id, url_type, url)
            VALUES (?, ?, ?, ?)",
            [$entityType, $entityId, $urlTypeId, $url]
        );
    }

    /**
     * Update an existing URL.
     *
     * @param int    $urlId      URL record ID
     * @param int    $urlTypeId  New URL type ID
     * @param string $url        New URL value
     * @param string $entityType Entity type — used to prevent cross-entity updates
     * @return bool
     */
    public function update(int $urlId, int $urlTypeId, string $url, string $entityType): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
            SET url_type_id = ? , url = ?
            WHERE id = ? AND entity_type = ?",
            [$urlTypeId, $url, $urlId, $entityType]

        );
    }

    /**
     * Delete a URL.
     * entity_type check prevents accidental cross-entity deletion
     * 
     * @param int     $urlId       URL record ID
     * @param string  $entityType  Safety check
     * @return bool
     */
    public function delete(int $urlId, string $entityType): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table}
            WHERE id = ? AND entity_type = ?",
            [$urlId, $entityType]
        );
    }
}
