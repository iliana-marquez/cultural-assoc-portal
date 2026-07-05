<?php

/**
 * MemberModel
 *
 * Handles all DB operations for the members table.
 * Members are created via the public membership request form.
 *
 * Status lifecycle:
 *   pending  → submitted, payment not yet confirmed
 *   active   → payment confirmed, expires_at set
 *
 * expires_at is the single source of truth for membership validity.
 * No automatic status changes — editor activates and renews manually.
 */

require_once __DIR__ . '/BaseModel.php';

class MemberModel extends BaseModel
{
    protected string $table = 'members';

    /**
     * Create a new membership request from the public form.
     */
    public function create(array $data): int|false
    {
        $this->execute(
            "INSERT INTO {$this->table}
                (first_name, last_name, email, street, plz, city, phone, birth_date, newsletter, payment_reference)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['street']       ?? null,
                $data['plz']          ?? null,
                $data['city']         ?? null,
                $data['phone']        ?? null,
                $data['birth_date']   ?: null,
                $data['newsletter']   ?? 0,
                $data['payment_reference'],
            ]
        );

        return $this->lastInsertId() ?: false;
    }

    /**
     * Get members filtered by status.
     *
     * Filters:
     *   all      → all non-deleted
     *   pending  → awaiting payment confirmation
     *   active   → confirmed, expires_at in future
     *   renewal  → active but expires_at within 30 days
     *   expired  → active but expires_at in past
     */
    public function getAll(string $filter = 'all'): array
    {
        $where = match ($filter) {
            'pending' => "WHERE status = 'pending' AND deleted_at IS NULL",
            'active'  => "WHERE status = 'active' AND expires_at > NOW() AND deleted_at IS NULL",
            'renewal' => "WHERE status = 'active' AND expires_at <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND expires_at > NOW() AND deleted_at IS NULL",
            'expired' => "WHERE status = 'active' AND expires_at < NOW() AND deleted_at IS NULL",
            default   => "WHERE deleted_at IS NULL",
        };

        return $this->fetchAll(
            "SELECT * FROM {$this->table} {$where} ORDER BY created_at DESC"
        );
    }

    /**
     * Get a single member by ID.
     */
    public function getById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ? AND deleted_at IS NULL",
            [$id]
        ) ?: null;
    }

    /**
     * Activate a pending member.
     * Sets status = active, expires_at = NOW() + 1 year.
     */
    public function activate(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET status = 'active', expires_at = DATE_ADD(NOW(), INTERVAL 1 YEAR)
             WHERE id = ? AND deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Renew an active member's membership.
     * Extends expires_at by 1 year from current expires_at or NOW() if already expired.
     */
    public function renew(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET expires_at = DATE_ADD(GREATEST(expires_at, NOW()), INTERVAL 1 YEAR)
             WHERE id = ? AND status = 'active' AND deleted_at IS NULL",
            [$id]
        );
    }

    /**
     * Soft delete a member.
     */
    public function delete(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = ?",
            [$id]
        );
    }

    /**
     * Get active members with newsletter = 1.
     * Used for newsletter deduplication against newsletter_subscribers.
     */
    public function getNewsletterRecipients(): array
    {
        return $this->fetchAll(
            "SELECT email FROM {$this->table}
             WHERE newsletter = 1
               AND status = 'active'
               AND expires_at > NOW()
               AND deleted_at IS NULL"
        );
    }

    /**
     * Export all non-deleted members for CSV download.
     */
    public function export(): array
    {
        return $this->fetchAll(
            "SELECT first_name, last_name, email, street, plz, city, phone,
                    birth_date, newsletter, payment_reference, status, expires_at, created_at
             FROM {$this->table}
             WHERE deleted_at IS NULL
             ORDER BY last_name ASC, first_name ASC"
        );
    }

    /**
     * For new requests through the website.
     */
    public function emailExists(string $email): bool
    {
        $row = $this->fetchOne(
            "SELECT id FROM {$this->table} WHERE email = ? AND deleted_at IS NULL",
            [$email]
        );
        return $row !== null && $row !== false;
    }
}
