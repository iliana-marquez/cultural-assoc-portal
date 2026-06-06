<?php

/**+
 * index.php
 * 
 * Single entry point for all requests.
 * Loads environment, core classes, and resolves the current route.
 */

// Environment 
$env = parse_ini_file(__DIR__ . '/../.env');
foreach ($env as $key => $value) {
    $_ENV[$key] = $value;
}

// Config
$config = require __DIR__ . '/../config/app.php';

// Debug mode
if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Core
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';

// Routes
$router = new Router();
require_once __DIR__ . '/../config/routes.php';

// Resolve 
$router->resolve();
