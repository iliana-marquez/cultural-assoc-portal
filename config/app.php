
<?php

/**
 * app.php
 *
 * Application settings — non-secret, environment-independent.
 * Secrets (DB credentials, API keys) live in .env
 */

return [

    // Database
    'db_charset'  => 'utf8mb4',

    // Application
    'app_name'    => 'Organisation Website System',
    'locale'      => 'de_AT',
    'timezone'    => 'Europe/Vienna',
    'debug'       => true, // set to false on production

    // Session
    'session_name'     => 'ows_session',
    'session_lifetime' => 3600, // 1 hour in seconds

    // OTP
    'otp_expiry'       => 600,  // 10 minutes in seconds
    'otp_max_attempts' => 3,    // max requests per hour per email

    // Pagination
    'per_page'         => 12,

];
