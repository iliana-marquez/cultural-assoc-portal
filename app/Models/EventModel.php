<?php

/**
 * EventModel
 *
 * Manages programme events — current and historical.
 * Status derived at app level from date — no DB column needed.
 * Archive = events before CURDATE() — fully automatic, no hardcoded year.
 * Media fetched separately via MediaModel (entity_type: 'event').
 * Participants fetched via ParticipantModel::getForEvent().
 * Venue fetched via VenueModel::getById().
 */

class EventModel extends BaseModel
{
    private string $table = 'events';

    /**
     * Get all events ordered by date DESC.
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             ORDER BY e.date DESC"
        );
    }

    /**
     * Get upcoming events (today or future).
     */
    public function getUpcoming(): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.date >= CURDATE()
             ORDER BY e.date ASC"
        );
    }

    /**
     * Get past events (before today) — all of them, no year threshold.
     * Archive is the single home for all past events; /veranstaltungen
     * shows only upcoming. The cut is CURDATE(), not a hardcoded year.
     */
    public function getPast(): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.date < CURDATE()
             ORDER BY e.date DESC"
        );
    }

    /**
     * Get archive events — all events before today.
     * Optionally filtered by year and/or category.
     *
     * @param int|null $year        Filter by calendar year (YEAR(date) = ?)
     * @param int|null $categoryId  Filter by category_id
     */
    public function getArchive(?int $year = null, ?int $categoryId = null): array
    {
        $where  = ['e.date < CURDATE()'];
        $params = [];

        if ($year) {
            $where[]  = 'YEAR(e.date) = ?';
            $params[] = $year;
        }

        if ($categoryId) {
            $where[]  = 'e.category_id = ?';
            $params[] = $categoryId;
        }

        $whereClause = implode(' AND ', $where);

        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE {$whereClause}
             ORDER BY e.date DESC",
            $params
        );
    }

    /**
     * Count archive events — same filters as getArchive() but returns
     * only the total count, including drafts. Used to detect whether
     * unpublished events exist for the curation notice.
     *
     * @param int|null $year
     * @param int|null $categoryId
     */
    public function countArchive(?int $year = null, ?int $categoryId = null): int
    {
        $where  = ['date < CURDATE()'];
        $params = [];

        if ($year) {
            $where[]  = 'YEAR(date) = ?';
            $params[] = $year;
        }

        if ($categoryId) {
            $where[]  = 'category_id = ?';
            $params[] = $categoryId;
        }

        $result = $this->fetchOne(
            "SELECT COUNT(*) as total FROM {$this->table} WHERE " . implode(' AND ', $where),
            $params
        );

        return (int) ($result->total ?? 0);
    }

    /**
     * Get distinct years that have past events — for the archive timeline nav.
     * Ordered newest first so the timeline reads right-to-left naturally.
     */
    public function getArchiveYears(): array
    {
        return $this->fetchAll(
            "SELECT DISTINCT YEAR(date) as year
             FROM {$this->table}
             WHERE date < CURDATE() AND date IS NOT NULL
             ORDER BY year DESC"
        );
    }

    /**
     * Get categories that have at least one published, non-cancelled past
     * event in a given year — with event count per category.
     * Counts only published events so chips reflect what visitors actually see.
     * Category chips still appear as a teaser even when some events are drafts.
     */
    public function getArchiveCategoriesByYear(int $year): array
    {
        return $this->fetchAll(
            "SELECT ec.id, ec.label, COUNT(e.id) as event_count
             FROM {$this->table} e
             INNER JOIN event_categories ec ON ec.id = e.category_id
             WHERE YEAR(e.date) = ?
               AND e.date < CURDATE()
               AND e.status = 'published'
               AND e.cancelled_at IS NULL
             GROUP BY ec.id, ec.label
             ORDER BY ec.label ASC",
            [$year]
        );
    }

    /**
     * Get event by ID with venue and category.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT e.*, ec.label as category_label,
                    v.name as venue_name, v.street as venue_street,
                    v.postcode as venue_postcode, v.city as venue_city
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.id = ?",
            [$id]
        );
    }

    /**
     * Get event by slug (app-generated from title).
     */
    public function getBySlug(string $slug): ?object
    {
        $events = $this->fetchAll(
            "SELECT e.*, ec.label as category_label,
                    v.name as venue_name, v.street as venue_street,
                    v.postcode as venue_postcode, v.city as venue_city
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id"
        );

        foreach ($events as $event) {
            if (self::generateSlug($event->title) === $slug) {
                return $event;
            }
        }

        return null;
    }

    /**
     * Get events by category.
     */
    public function getByCategory(int $categoryId): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.category_id = ?
             ORDER BY e.date DESC",
            [$categoryId]
        );
    }

    /**
     * Get events for a specific participant.
     */
    public function getForParticipant(int $participantId): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             INNER JOIN event_participants ep ON ep.event_id = e.id
             WHERE ep.participant_id = ?
             ORDER BY e.date DESC",
            [$participantId]
        );
    }

    /**
     * Get all event categories.
     */
    public function getCategories(): array
    {
        return $this->fetchAll(
            "SELECT * FROM event_categories ORDER BY label ASC"
        );
    }

    /**
     * Derive event status from date — no DB column needed.
     *
     * @param object $event
     * @return string 'upcoming' | 'past'
     */
    public static function getStatus(object $event): string
    {
        if (empty($event->date)) return 'past';
        return strtotime($event->date) >= strtotime('today') ? 'upcoming' : 'past';
    }

    /**
     * Generate URL-safe slug from event title.
     */
    public static function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $slug);
        $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Publish an event — sets status to published.
     */
    public function publish(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 'published' WHERE id = ?",
            [$id]
        );
    }

    /**
     * Unpublish an event — sets status back to draft.
     */
    public function unpublish(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET status = 'draft' WHERE id = ?",
            [$id]
        );
    }

    /**
     * Cancel an event — sets cancelled_at timestamp.
     * Only applicable to upcoming published events.
     */
    public function cancel(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET cancelled_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    /**
     * Add an event.
     */
    public function add(array $data): int
    {
        $this->execute(
            "INSERT INTO {$this->table}
             (project_id, category_id, title, subtitle, description,
              date, time, venue_id, review, admission, admission_url, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['project_id']    ?? null,
                $data['category_id']   ?? null,
                $data['title']         ?? null,
                $data['subtitle']      ?? null,
                $data['description']   ?? null,
                $data['date']          ?? null,
                $data['time']          ?? null,
                $data['venue_id']      ?? null,
                $data['review']        ?? null,
                $data['admission']     ?? null,
                $data['admission_url'] ?? null,
                $data['status']        ?? 'draft',
            ]
        );

        return $this->lastInsertId();
    }

    /**
     * Update a single field — used by entity-edit-row AJAX saves.
     */
    public function updateField(int $id, string $field, string $value): bool
    {
        $allowed = [
            'title',
            'subtitle',
            'description',
            'date',
            'time',
            'venue_id',
            'category_id',
            'review',
            'admission',
            'admission_amount',
            'admission_url',
            'status',
        ];

        if (!in_array($field, $allowed)) return false;

        return $this->execute(
            "UPDATE {$this->table} SET {$field} = ? WHERE id = ?",
            [$value ?: null, $id]
        );
    }

    /**
     * Update an event — full row update.
     */
    public function update(int $id, array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET project_id = ?, category_id = ?, title = ?, subtitle = ?,
                 description = ?, date = ?, time = ?, venue_id = ?,
                 review = ?, admission = ?, admission_url = ?
             WHERE id = ?",
            [
                $data['project_id']    ?? null,
                $data['category_id']   ?? null,
                $data['title']         ?? null,
                $data['subtitle']      ?? null,
                $data['description']   ?? null,
                $data['date']          ?? null,
                $data['time']          ?? null,
                $data['venue_id']      ?? null,
                $data['review']        ?? null,
                $data['admission']     ?? null,
                $data['admission_url'] ?? null,
                $id,
            ]
        );
    }

    /**
     * Delete an event — hard delete.
     * FK on event_participants is ON DELETE CASCADE — removes pivot rows.
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    /**
     * Add participant to event.
     */
    public function addParticipant(int $eventId, int $participantId): bool
    {
        return $this->execute(
            "INSERT IGNORE INTO event_participants (event_id, participant_id)
             VALUES (?, ?)",
            [$eventId, $participantId]
        );
    }

    /**
     * Remove participant from event.
     */
    public function removeParticipant(int $eventId, int $participantId): bool
    {
        return $this->execute(
            "DELETE FROM event_participants
             WHERE event_id = ? AND participant_id = ?",
            [$eventId, $participantId]
        );
    }
}
