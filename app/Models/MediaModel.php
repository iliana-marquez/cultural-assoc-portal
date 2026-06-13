<?php

/**
 * MediaModel
 *
 * Manages media via pivot architecture.
 * Media stored once in media table — linked to any entity via entity_media pivot.
 * One media item can be linked to multiple entities.
 * Update caption/URL once → reflects everywhere it's linked. ✅
 *
 * stage values:
 *   'promo'   → promotional material (before event, profile photo)
 *   'gallery' → documentary photos (after event)
 *
 * First promo media item = entity's display image.
 */

class MediaModel extends BaseModel
{
    private string $table      = 'media';
    private string $pivotTable = 'entity_media';

    /**
     * Get media by ID.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get all media for an entity via pivot, ordered by order_index.
     *
     * @param string      $entityType  'event' | 'participant' | 'venue' | 'organisation'
     * @param int         $entityId
     * @param string|null $stage       'promo' | 'gallery' | null (all)
     * @return array
     */
    public function getForEntity(string $entityType, int $entityId, ?string $stage = null): array
    {
        $sql = "SELECT m.*
                FROM {$this->table} m
                INNER JOIN {$this->pivotTable} em ON em.media_id = m.id
                WHERE em.entity_type = ? AND em.entity_id = ?";

        $params = [$entityType, $entityId];

        if ($stage) {
            $sql .= " AND m.stage = ?";
            $params[] = $stage;
        }

        $sql .= " ORDER BY m.order_index ASC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get first promo image for an entity.
     * Used as display image on listing cards.
     *
     * @param string $entityType
     * @param int    $entityId
     * @return object|null
     */
    public function getPromo(string $entityType, int $entityId): ?object
    {
        return $this->fetchOne(
            "SELECT m.*
             FROM {$this->table} m
             INNER JOIN {$this->pivotTable} em ON em.media_id = m.id
             WHERE em.entity_type = ? AND em.entity_id = ? AND m.stage = 'promo'
             ORDER BY m.order_index ASC
             LIMIT 1",
            [$entityType, $entityId]
        );
    }

    /**
     * Get random media items across entities.
     * Used in free sections to display curated media from any entity.
     *
     * @param string|null $entityType  Filter by entity type or null for all
     * @param int         $limit
     * @return array
     */
    public function getRandom(?string $entityType = null, int $limit = 6): array
    {
        if ($entityType) {
            return $this->fetchAll(
                "SELECT m.*
                 FROM {$this->table} m
                 INNER JOIN {$this->pivotTable} em ON em.media_id = m.id
                 WHERE em.entity_type = ?
                 ORDER BY RAND()
                 LIMIT ?",
                [$entityType, $limit]
            );
        }

        return $this->fetchAll(
            "SELECT * FROM {$this->table} ORDER BY RAND() LIMIT ?",
            [$limit]
        );
    }

    /**
     * Add media and link to entity.
     * If media_url already exists — reuses existing record.
     *
     * @param string $entityType
     * @param int    $entityId
     * @param array  $data  media_url, caption, stage, order_index
     * @return bool
     */
    public function addForEntity(string $entityType, int $entityId, array $data): bool
    {
        // Check if media_url already exists
        $existing = $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE media_url = ?",
            [$data['media_url']]
        );

        if ($existing) {
            $mediaId = $existing->id;
        } else {
            $this->execute(
                "INSERT INTO {$this->table} (media_url, resource_type, caption, stage, order_index)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $data['media_url']     ?? null,
                    $data['resource_type'] ?? 'image',
                    $data['caption']       ?? null,
                    $data['stage']         ?? 'promo',
                    $data['order_index']   ?? 0,
                ]
            );
            $mediaId = $this->lastInsertId();
        }

        // Link to entity via pivot
        return $this->execute(
            "INSERT IGNORE INTO {$this->pivotTable} (media_id, entity_type, entity_id)
             VALUES (?, ?, ?)",
            [$mediaId, $entityType, $entityId]
        );
    }

    /**
     * Unlink media from entity.
     * Removes pivot row only — media preserved for other entities.
     * If media has no more links → delete media record too.
     *
     * @param int    $mediaId
     * @param string $entityType
     * @param int    $entityId
     * @return bool
     */
    public function unlinkFromEntity(int $mediaId, string $entityType, int $entityId): bool
    {
        $this->execute(
            "DELETE FROM {$this->pivotTable}
             WHERE media_id = ? AND entity_type = ? AND entity_id = ?",
            [$mediaId, $entityType, $entityId]
        );

        // Check if media still linked elsewhere
        $remaining = $this->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->pivotTable} WHERE media_id = ?",
            [$mediaId]
        );

        if (($remaining->count ?? 0) === 0) {
            $this->execute(
                "DELETE FROM {$this->table} WHERE id = ?",
                [$mediaId]
            );
        }

        return true;
    }

    /**
     * Update media item.
     * Updates once — reflects on all linked entities. 
     *
     * @param int   $mediaId
     * @param array $data
     * @return bool
     */
    public function update(int $mediaId, array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET media_url = ?, resource_type = ?, caption = ?, stage = ?, order_index = ?
             WHERE id = ?",
            [
                $data['media_url']     ?? null,
                $data['resource_type'] ?? 'image',
                $data['caption']       ?? null,
                $data['stage']         ?? 'promo',
                $data['order_index']   ?? 0,
                $mediaId,
            ]
        );
    }

    /**
     * Update order index — for drag-and-drop reordering.
     */
    public function updateOrder(int $mediaId, int $orderIndex): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET order_index = ? WHERE id = ?",
            [$orderIndex, $mediaId]
        );
    }
}
