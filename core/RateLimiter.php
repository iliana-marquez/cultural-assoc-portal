<?php

/**
 * RateLimiter
 *
 * Session-based rate limiting utility — reusable for any action.
 * No DB needed — session handles attempt tracking.
 *
 * Usage:
 *   if (!RateLimiter::check('login', $maxAttempts, $window)) { ... }
 *   RateLimiter::increment('login');
 *   RateLimiter::reset('login');
 *
 * Session keys are namespaced by action:
 *   rate_limit_login_attempts
 *   rate_limit_login_last
 *   rate_limit_contact_attempts
 *   rate_limit_contact_last
 */


class RateLimiter
{
    /**
     * Session key prefix — namespaces all rate limit keys.
     * Prevents collision with other session variables.
     */
    private static string $sessionPrefix = 'rate_limit';

    /**
     * Check if action is within rate limit.
     * 
     * Reads attempts and last attempt timestamp from session.
     * Resets counter automatically if window has passed.
     * 
     * @param string    $action         Action name e.g. 'login', 'contact'
     * @param int       $maxAttempts    Max. allowed attempts - from config
     * @param int       $window         Time window in seconds - from config
     * @return bool                     True if allowed, false if limit exceeded
     */
    public static function check(string $action, int $maxAttempts, int $window): bool
    {
        // Build session keys for this specific action
        $keyAttempts = self::$sessionPrefix . '_' . $action . '_attempts';
        $keyLast = self::$sessionPrefix . '_' . $action . '_last';

        // Read current values from session
        $attempts = $_SESSION[$keyAttempts] ?? 0;
        $lastAttempt = $_SESSION[$keyLast] ?? 0;

        // Reset counter if window has passed since last attempt
        if (time() - $lastAttempt > $window) {
            $attempts = 0;
            $_SESSION[$keyAttempts] = 0;
        }

        // Return false if limit excedeed
        return $attempts < $maxAttempts;
    }

    /**
     * Record an attempt for an action.
     * Call after check() passes - before processing request
     * 
     * @param string $action Action name e.g. 'login', 'contact'
     */
    public static function increment(string $action): void
    {
        $keyAttempts = self::$sessionPrefix . '_' . $action . '_attempts';
        $keyLast = self::$sessionPrefix . '_' . $action . '_last';

        $_SESSION[$keyAttempts] = ($_SESSION[$keyAttempts] ?? 0) + 1;
        $_SESSION[$keyLast] = time();
    }

    /**
     * Reset attempts for an action.
     * Use ONLY for authentication flows — resets counter on successful login.
     *
     * Do NOT use for public forms (contact, newsletter, signup).
     * Public forms should let the window expire naturally — resetting on
     * success would allow unlimited submissions by re-triggering after each send.
     *
     * @param string $action Action name — authentication only e.g. 'login'
     */
    public static function reset(string $action): void
    {
        $keyAttempts = self::$sessionPrefix . '_' . $action . '_attempts';
        $keyLast     = self::$sessionPrefix . '_' . $action . '_last';

        unset($_SESSION[$keyAttempts]);
        unset($_SESSION[$keyLast]);
    }
}
