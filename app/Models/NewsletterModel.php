<?php

/**
 * NewsletterModel
 *
 * Manages newsletter subscribers.
 * Double opt-in — confirmed = 0 until email link clicked.
 * Unsubscribe via one-click token link — hard delete.
 */

class NewsletterModel extends BaseModel
{
    private string $table = 'newsletter_subscribers';

    /**
     * Get all confirmed subscribers.
     */
    public function getAll(): array
    {
        return $this->fetchAll(
            "SELECT * FROM {$this->table}
             WHERE confirmed = 1
             ORDER BY confirmed_at DESC"
        );
    }

    /**
     * Get subscriber by email.
     */
    public function getByEmail(string $email): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    /**
     * Get subscriber by token — used for confirm and unsubscribe.
     */
    public function getByToken(string $token): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table}
             WHERE token = ? AND token_expiry > NOW()",
            [$token]
        );
    }

    /**
     * Add a new unconfirmed subscriber.
     * Returns false if email already exists.
     */
    public function add(string $email, string $token): bool
    {
        return $this->execute(
            "INSERT INTO {$this->table}
             (email, confirmed, token, token_expiry)
             VALUES (?, 0, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))",
            [$email, $token]
        );
    }

    /**
     * Confirm a subscriber — set confirmed = 1, clear token.
     */
    public function confirm(int $id): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET confirmed = 1, confirmed_at = NOW(), token = NULL, token_expiry = NULL
             WHERE id = ?",
            [$id]
        );
    }

    /**
     * Update token — used to resend confirmation.
     */
    public function updateToken(int $id, string $token): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET token = ?, token_expiry = DATE_ADD(NOW(), INTERVAL 24 HOUR)
             WHERE id = ?",
            [$token, $id]
        );
    }

    /**
     * Delete subscriber by token — one-click unsubscribe.
     */
    public function deleteByToken(string $token): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE token = ?",
            [$token]
        );
    }

    /**
     * Get all confirmed subscribers for CSV export.
     */
    public function getAllForExport(): array
    {
        return $this->fetchAll(
            "SELECT email, confirmed_at FROM {$this->table}
             WHERE confirmed = 1
             ORDER BY confirmed_at DESC"
        );
    }
}
