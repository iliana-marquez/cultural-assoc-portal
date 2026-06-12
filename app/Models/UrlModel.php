<?php

/**
 * UrlModel
 *
 * Manages external URLs via pivot architecture.
 * URLs stored once in urls table — linked to any entity via entity_urls pivot.
 * One URL can be linked to multiple entities (team + participant, events sharing festival URL).
 * Fix URL once → reflects everywhere it's linked. 
 */

class UrlModel extends BaseModel
{
    private string $table       = 'urls';
    private string $pivotTable  = 'entity_urls';

    /**
     * Get all URLs for an entity via pivot.
     *
     * @param string $entityType  'organisation' | 'team' | 'participant' | 'event' | 'venue'
     * @param int    $entityId
     * @return array
     */
    public function getForEntity(string $entityType, int $entityId): array
    {
        return $this->fetchAll(
            "SELECT u.*, ut.label as type_label, ut.icon
             FROM {$this->table} u
             INNER JOIN {$this->pivotTable} eu ON eu.url_id = u.id
             INNER JOIN url_types ut ON ut.id = u.url_type_id
             WHERE eu.entity_type = ? AND eu.entity_id = ?
             ORDER BY ut.label ASC",
            [$entityType, $entityId]
        );
    }

    /**
     * Find existing URL by url string.
     * Used to prevent duplicates and enable sharing.
     *
     * @param string $url
     * @return object|null
     */
    public function findByUrl(string $url): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE url = ?",
            [$url]
        );
    }

    /**
     * Add URL and link to entity.
     * If URL already exists — reuses existing record.
     * Links URL to entity via pivot.
     *
     * @param string $entityType
     * @param int    $entityId
     * @param int    $urlTypeId
     * @param string $url
     * @return bool
     */
    public function addForEntity(string $entityType, int $entityId, int $urlTypeId, string $url): bool
    {
        // Check if URL already exists
        $existing = $this->findByUrl($url);

        if ($existing) {
            $urlId = $existing->id;
        } else {
            // Insert new URL
            $this->execute(
                "INSERT INTO {$this->table} (url_type_id, url) VALUES (?, ?)",
                [$urlTypeId, $url]
            );
            $urlId = $this->lastInsertId();
        }

        // Link to entity via pivot (ignore if already linked)
        return $this->execute(
            "INSERT IGNORE INTO {$this->pivotTable} (url_id, entity_type, entity_id)
             VALUES (?, ?, ?)",
            [$urlId, $entityType, $entityId]
        );
    }

    /**
     * Unlink URL from entity.
     * Removes pivot row only — URL record preserved for other entities.
     * If URL has no more entity links → delete URL record too.
     *
     * @param int    $urlId
     * @param string $entityType
     * @param int    $entityId
     * @return bool
     */
    public function unlinkFromEntity(int $urlId, string $entityType, int $entityId): bool
    {
        // Remove pivot link
        $this->execute(
            "DELETE FROM {$this->pivotTable}
             WHERE url_id = ? AND entity_type = ? AND entity_id = ?",
            [$urlId, $entityType, $entityId]
        );

        // Check if URL still linked to other entities
        $remaining = $this->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->pivotTable} WHERE url_id = ?",
            [$urlId]
        );

        // If no more links → delete URL record
        if (($remaining->count ?? 0) === 0) {
            $this->execute(
                "DELETE FROM {$this->table} WHERE id = ?",
                [$urlId]
            );
        }

        return true;
    }

    /**
     * Update URL string.
     * Updates once — reflects on all linked entities automatically. 
     *
     * @param int    $urlId
     * @param string $url
     * @param int    $urlTypeId
     * @return bool
     */
    public function update(int $urlId, string $url, int $urlTypeId): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET url = ?, url_type_id = ? WHERE id = ?",
            [$url, $urlTypeId, $urlId]
        );
    }
}
