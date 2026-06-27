<?php

/**
 * MediaModel
 *
 * Manages media via pivot architecture.
 * Media stored once in media table — linked to any entity via entity_media pivot.
 * One media item can be linked to multiple entities.
 * Update caption/URL once → reflects everywhere it's linked.
 *
 * stage values are caller-defined — the model passes them through as filters only.
 * Stage logic (which stage means what) belongs in the controller, not here.
 *       could be 'promo' | 'gallery' | 'profile' | or scale as more stage tags come along.
 */

class MediaModel extends BaseModel
{
    private string $table      = 'media';
    private string $pivotTable = 'entity_media';

    /**
     * Detect if media is a video from URL extension.
     * No DB column needed — derived at app level.
     */
    public static function isVideo(string $mediaUrl): bool
    {
        $ext = strtolower(pathinfo($mediaUrl, PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'mov', 'webm', 'avi']);
    }

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
     * @param string|null $stage       caller-defined stage filter, or null for all
     * @return array
     */
    public function getForEntity(string $entityType, int $entityId, ?string $stage = null): array
    {
        $sql = "SELECT m.*
                FROM {$this->table} m
                INNER JOIN {$this->pivotTable} em ON em.media_id = m.id
                WHERE em.entity_type = ? AND em.entity_id = ?";

        $params = [$entityType, $entityId];

        if ($stage !== null) {
            $sql .= " AND m.stage = ?";
            $params[] = $stage;
        }

        $sql .= " ORDER BY m.order_index ASC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get first media item for an entity, optionally filtered by stage.
     * Stage is a caller concern — pass any stage string, or null for the
     * first item regardless of stage.
     *
     * @param string      $entityType
     * @param int         $entityId
     * @param string|null $stage  optional filter — any stage value or null
     * @return object|null
     */
    public function getFirstForEntity(string $entityType, int $entityId, ?string $stage = null): ?object
    {
        $sql = "SELECT m.*
                FROM {$this->table} m
                INNER JOIN {$this->pivotTable} em ON em.media_id = m.id
                WHERE em.entity_type = ? AND em.entity_id = ?";

        $params = [$entityType, $entityId];

        if ($stage !== null) {
            $sql .= " AND m.stage = ?";
            $params[] = $stage;
        }

        $sql .= " ORDER BY m.order_index ASC LIMIT 1";

        return $this->fetchOne($sql, $params);
    }

    /**
     * Get random media items across entities.
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
     * Stage must be passed explicitly by the caller — no default.
     *
     * @param string $entityType
     * @param int    $entityId
     * @param array  $data  media_url, caption, credit, stage, order_index
     * @return int|false  the media id on success, false on failure
     */
    public function addForEntity(string $entityType, int $entityId, array $data): int|false
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
                "INSERT INTO {$this->table} (media_url, caption, credit, stage, order_index)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $data['media_url']   ?? null,
                    $data['caption']     ?? null,
                    $data['credit']      ?? null,
                    $data['stage']       ?? null,
                    $data['order_index'] ?? 0,
                ]
            );
            $mediaId = $this->lastInsertId();
        }

        // Link to entity via pivot
        $this->execute(
            "INSERT IGNORE INTO {$this->pivotTable} (media_id, entity_type, entity_id)
     VALUES (?, ?, ?)",
            [$mediaId, $entityType, $entityId]
        );

        return (int) $mediaId;
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
     * Update media item — only updates fields present in $data.
     * Dynamic SET clause prevents partial updates from nulling unrelated fields.
     * No stage defaults — caller passes stage explicitly if needed.
     *
     * @param int   $mediaId
     * @param array $data
     * @return bool
     */
    public function update(int $mediaId, array $data): bool
    {
        $allowed = ['media_url', 'caption', 'credit', 'stage', 'order_index'];
        $sets    = [];
        $params  = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $sets[]   = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($sets)) return false;

        $params[] = $mediaId;

        return $this->execute(
            "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE id = ?",
            $params
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
