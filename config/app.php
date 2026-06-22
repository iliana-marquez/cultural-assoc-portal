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
    'otp_max_attempts'      => 2,    // max requests per hour per email
    'otp_rate_limit_window' => 60,    // 1 min for testing

    // Admin path
    'admin_path' => $_ENV['ADMIN_PATH'] ?? throw new RuntimeException('ADMIN_PATH not set in .env'),

    // This deployment's real, public domain (no scheme, no
    // trailing slash — e.g. 'example.com'). Used to verify
    // that an internal-page CTA link genuinely points at THIS
    // site, not an arbitrary domain a client request might claim —
    // window.location.origin is browser-reported, client-side
    // data, never something the server can trust on its own.
    'site_domain' => $_ENV['SITE_DOMAIN'] ?? throw new RuntimeException('SITE_DOMAIN not set in .env'),

    // Cloudinary
    'cloudinary_cloud' => $_ENV['CLOUDINARY_CLOUD_NAME']
        ?? throw new RuntimeException('CLOUDINARY_CLOUD_NAME not set'),
    'cloudinary_key'   => $_ENV['CLOUDINARY_API_KEY']
        ?? throw new RuntimeException('CLOUDINARY_API_KEY not set'),
    'cloudinary_secret' => $_ENV['CLOUDINARY_API_SECRET']
        ?? throw new RuntimeException('CLOUDINARY_API_SECRET not set'),
    'cloudinary_folder' => $_ENV['CLOUDINARY_FOLDER'] ?? 'OWS',


];
