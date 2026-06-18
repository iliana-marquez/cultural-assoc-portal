<?php

/**
 * EventModel
 *
 * Manages programme events — current and historical.
 * Status derived at app level from date — no DB column needed.
 * Archive = events before 2025 (or date null).
 * Media fetched separately via MediaModel (entity_type: 'event').
 * Participants fetched via ParticipantModel::getForEvent().
 * Venue fetched via VenueModel::getById().
 */

class EventModel extends BaseModel
{
    private string $table = 'events';

    // Archive threshold — events before this year shown on /archiv
    private const ARCHIVE_YEAR = 2025;

    /**
     * Get all current events (2025+) ordered by date DESC.
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.date >= ?
             ORDER BY e.date DESC",
            [self::ARCHIVE_YEAR . '-01-01']
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
             ORDER BY e.date ASC",
        );
    }

    /**
     * Get past events (before today, from 2025+).
     */
    public function getPast(): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.date < CURDATE() AND e.date >= ?
             ORDER BY e.date DESC",
            [self::ARCHIVE_YEAR . '-01-01']
        );
    }

    /**
     * Get archive events (pre-2025 or date null).
     */
    public function getArchive(): array
    {
        return $this->fetchAll(
            "SELECT e.*, ec.label as category_label, v.name as venue_name
             FROM {$this->table} e
             LEFT JOIN event_categories ec ON ec.id = e.category_id
             LEFT JOIN venues v ON v.id = e.venue_id
             WHERE e.date < ? OR e.date IS NULL
             ORDER BY e.date DESC",
            [self::ARCHIVE_YEAR . '-01-01']
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
     * Add an event.
     */
    public function add(array $data): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table}
             (project_id, category_id, title, subtitle, description,
              date, time, venue_id, review, admission, admission_url)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['project_id']   ?? null,
                $data['category_id']  ?? null,
                $data['title']        ?? null,
                $data['subtitle']     ?? null,
                $data['description']  ?? null,
                $data['date']         ?? null,
                $data['time']         ?? null,
                $data['venue_id']     ?? null,
                $data['review']       ?? null,
                $data['admission']      ?? null,
                $data['admission_url'] ?? null,
            ]
        );
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
        ];

        if (!in_array($field, $allowed)) return false;

        return $this->execute(
            "UPDATE {$this->table} SET {$field} = ? WHERE id = ?",
            [$value ?: null, $id]
        );
    }

    /**
     * Update an event.
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
                $data['project_id']   ?? null,
                $data['category_id']  ?? null,
                $data['title']        ?? null,
                $data['subtitle']     ?? null,
                $data['description']  ?? null,
                $data['date']         ?? null,
                $data['time']         ?? null,
                $data['venue_id']     ?? null,
                $data['review']       ?? null,
                $data['admission']      ?? null,
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
