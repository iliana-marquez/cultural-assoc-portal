
<?php

/**
 * app.php
 *
 * Application settings — non-secret, environment-independent.
 * Set once per deployment
 * Secrets (DB credentials, API keys) live in .env
 * 
 */

return [

    // Database
    'db_charset'  => 'utf8mb4',

    // Application
    'app_name'    => 'Organisation Website System',
    'locale'      => 'de_AT',
    'timezone'    => 'Europe/Vienna',
    'debug'       => ($_ENV['APP_DEBUG'] ?? 'false') === 'true', // sets depending on dev/prod mode

    // Session
    'session_name'     => 'ows_session',
    'session_lifetime' => 3600, // 1 hour in seconds

    // OTP
    'otp_expiry'            => 900,  // 15 minutes in seconds
    'otp_max_attempts'      => 3,    // max requests per hour per email
    'otp_rate_limit_window' => 3600,    // 1 hour in seconds

    // Admin path
    'admin_path' => $_ENV['ADMIN_PATH'] ?? throw new RuntimeException('ADMIN_PATH not set in .env'),


];
