<?php

/**
 * FormController
 *
 * Abstract base for all public-facing form controllers.
 * Provides the shared security stack — CSRF, rate limiting,
 * input sanitization, email validation, DNS check.
 *
 * Extended by:
 *   ContactController  → /kontakt
 *   MitgliedController → /mitglied-werden
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../../core/Mailer.php';
require_once __DIR__ . '/../../core/RateLimiter.php';

class FormController extends BaseController
{
    /**
     * Validate CSRF token for a given session key.
     */
    protected function validateCsrf(string $sessionKey, string $postKey): bool
    {
        $token = filter_input(INPUT_POST, $postKey, FILTER_SANITIZE_SPECIAL_CHARS);
        return $token && hash_equals($_SESSION[$sessionKey] ?? '', $token);
    }

    /**
     * Check and increment rate limiter.
     */
    protected function checkRateLimit(string $key, int $max = 3, int $window = 600): bool
    {
        return RateLimiter::check($key, $max, $window);
    }

    protected function incrementRateLimit(string $key): void
    {
        RateLimiter::increment($key);
    }

    /**
     * Sanitize a plain text POST field.
     */
    protected function sanitizeField(string $field): string
    {
        return strip_tags(trim(filter_input(INPUT_POST, $field, FILTER_UNSAFE_RAW) ?? ''));
    }

    /**
     * Sanitize an email POST field.
     */
    protected function sanitizeEmail(string $field): string
    {
        return strip_tags(trim(filter_input(INPUT_POST, $field, FILTER_SANITIZE_EMAIL) ?? ''));
    }

    /**
     * Validate email format and DNS.
     * Returns error string or empty string if valid.
     */
    protected function validateEmail(string $email): string
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Bitte eine gültige E-Mail-Adresse eingeben.';
        }

        if (strlen($email) > 200) {
            return 'E-Mail-Adresse zu lang.';
        }

        $domain = substr(strrchr($email, '@'), 1);
        if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
            return 'E-Mail-Domain konnte nicht verifiziert werden.';
        }

        return '';
    }

    /**
     * Regenerate a CSRF token in the session.
     */
    protected function regenerateCsrf(string $sessionKey): void
    {
        $_SESSION[$sessionKey] = bin2hex(random_bytes(32));
    }

    /**
     * Generate a CSRF token if not already set.
     */
    protected function ensureCsrf(string $sessionKey): string
    {
        $this->startSession();
        if (empty($_SESSION[$sessionKey])) {
            $_SESSION[$sessionKey] = bin2hex(random_bytes(32));
        }
        return $_SESSION[$sessionKey];
    }
}
