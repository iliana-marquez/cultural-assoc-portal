<?php

/**
 * UserModel
 *
 * Base authentication model — equivalent to Django's auth.User / Laravel's Authenticatable.
 * Table-agnostic via constructor — extended by entity-specific models.
 *
 * Handles entity lookup and complete OTP flow.
 * Pure OTP logic encapsulated as private static methods — no external utility needed.
 *
 * Extended by:
 *   EditorModel → authorised_editors table
 *   and other users when v2 scales.
 *
 * Never instantiated directly — always via a subclass.
 */

class UserModel extends BaseModel
{
    protected string $table;

    public function __construct(string $table)
    {
        parent::__construct();
        $this->table = $table;
    }

    // ── Entity Lookup ────────────────────────────────────────

    /**
     * Find a user by email.
     *
     * @param string $email
     * @return object|null
     */
    public function findByEmail(string $email): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [strtolower(trim($email))]
        );
    }

    /**
     * Find a user by ID.
     *
     * @param int $id
     * @return object|null
     */
    public function findById(int $id): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$id]
        );
    }

    // ── OTP Flow ─────────────────────────────────────────────

    /**
     * Generate OTP, hash it and store with expiry.
     * Controller receives plain code only — hashed version never leaves this model.
     *
     * @param int $id      Entity ID
     * @param int $expiry  Expiry in seconds — from config/app.php otp_expiry
     * @return string      Plain text OTP to send by email
     */
    public function generateOtp(int $id, int $expiry): string
    {
        $code      = self::generateCode();
        $hashed    = self::hashCode($code);
        $expiresAt = self::expiresAt($expiry);

        $this->execute(
            "UPDATE {$this->table}
             SET otp_code = ?, otp_expires_at = ?
             WHERE id = ?",
            [$hashed, $expiresAt, $id]
        );

        return $code;
    }

    /**
     * Validate a submitted OTP code.
     *
     * @param int    $id   Entity ID
     * @param string $code Plain text OTP submitted by user
     * @return bool        True if valid and not expired
     */
    public function validateOtp(int $id, string $code): bool
    {
        $record = $this->fetchOne(
            "SELECT otp_code, otp_expires_at FROM {$this->table} WHERE id = ?",
            [$id]
        );

        if (!$record || !$record->otp_code || !$record->otp_expires_at) {
            return false;
        }

        if (self::isExpired($record->otp_expires_at)) {
            return false;
        }

        return self::verifyCode($code, $record->otp_code);
    }

    /**
     * Clear OTP after successful login — one-time use.
     *
     * @param int $id Entity ID
     */
    public function clearOtp(int $id): void
    {
        $this->execute(
            "UPDATE {$this->table}
             SET otp_code = NULL, otp_expires_at = NULL
             WHERE id = ?",
            [$id]
        );
    }

    // ── Private OTP Logic ────────────────────────────────────
    // Pure utility methods — encapsulated here, not exposed externally.

    /**
     * Generate a random 6-digit OTP code.
     */
    private static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Hash an OTP code for secure DB storage.
     */
    private static function hashCode(string $code): string
    {
        return password_hash($code, PASSWORD_DEFAULT);
    }

    /**
     * Verify submitted code against stored hash.
     * timing-safe — prevents timing attacks.
     */
    private static function verifyCode(string $submitted, string $hashed): bool
    {
        return password_verify($submitted, $hashed);
    }

    /**
     * Check if OTP has expired.
     */
    private static function isExpired(string $expiresAt): bool
    {
        return strtotime($expiresAt) < time();
    }

    /**
     * Calculate expiry datetime string from now.
     */
    private static function expiresAt(int $seconds): string
    {
        return date('Y-m-d H:i:s', time() + $seconds);
    }
}
