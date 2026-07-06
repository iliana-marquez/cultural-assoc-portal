<?php

/**+
 * index.php
 * 
 * Single entry point for all requests.
 * Loads environment, core classes, and resolves the current route.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Core
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../core/RateLimiter.php';
require_once __DIR__ . '/../core/SchemaBuilder.php';
require_once __DIR__ . '/../core/CloudinaryService.php';

// Models
require_once __DIR__ . '/../app/Models/BaseModel.php';
require_once __DIR__ . '/../app/Models/UserModel.php';
require_once __DIR__ . '/../app/Models/EditorModel.php';
require_once __DIR__ . '/../app/Models/OrganisationModel.php';
require_once __DIR__ . '/../app/Models/UrlModel.php';
require_once __DIR__ . '/../app/Models/PagesModel.php';
require_once __DIR__ . '/../app/Models/TeamModel.php';
require_once __DIR__ . '/../app/Models/VenueModel.php';
require_once __DIR__ . '/../app/Models/ParticipantModel.php';
require_once __DIR__ . '/../app/Models/EventModel.php';
require_once __DIR__ . '/../app/Models/MediaModel.php';
require_once __DIR__ . '/../app/Models/MemberModel.php';
require_once __DIR__ . '/../app/Models/ContributorModel.php';

// Controllers
require_once __DIR__ . '/../app/Controllers/BaseController.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';
require_once __DIR__ . '/../app/Controllers/PageController.php';
require_once __DIR__ . '/../app/Controllers/FormController.php';
require_once __DIR__ . '/../app/Controllers/ContactController.php';
require_once __DIR__ . '/../app/Controllers/MembershipRequestController.php';
require_once __DIR__ . '/../app/Controllers/MemberController.php';
require_once __DIR__ . '/../app/Controllers/TeamController.php';
require_once __DIR__ . '/../app/Controllers/EventController.php';
require_once __DIR__ . '/../app/Controllers/ParticipantController.php';
require_once __DIR__ . '/../app/Controllers/MediaController.php';
require_once __DIR__ . '/../app/Controllers/OrganisationController.php';
require_once __DIR__ . '/../app/Controllers/NewsletterController.php';
require_once __DIR__ . '/../app/Controllers/ContributorController.php';

// Environment 
$envPaths = [
    dirname(__DIR__) . '/.env',      // local + public_html level
    dirname(__DIR__, 2) . '/.env',   // one above public_html
];

foreach ($envPaths as $path) {
    if (file_exists($path)) {
        $env = parse_ini_file($path);
        foreach ($env as $key => $value) {
            $_ENV[$key] = $value;
        }
        break;
    }
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
