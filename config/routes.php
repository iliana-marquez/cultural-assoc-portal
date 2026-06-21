<?php

/**
 * routes.php
 * 
 * All application routes defined here.
 * $router is available from index.php
 * 
 * Format:
 *  $router->get('/path', 'ControllerClass', 'method');
 *  $router->post('/path', 'ControllerClass', 'method'); 
 */

// ── Editor ────────────────────────────────────────────────
$adminPath = $config['admin_path'];
$router->get('/' . $adminPath,             'AuthController', 'showLogin');
$router->post('/' . $adminPath,            'AuthController', 'sendOtp');
$router->get('/' . $adminPath . '/verify', 'AuthController', 'showVerify');
$router->post('/' . $adminPath . '/verify', 'AuthController', 'verifyOtp');
$router->get('/kontakt',                    'ContactController', 'index');
$router->get('/logout',                    'AuthController', 'logout');

// ── Free Pages (non entity-specific) → PageController ───────
$router->get('/',                    'PageController', 'show');
$router->get('/ueber-uns',           'PageController', 'show');
$router->get('/alsergrund',          'PageController', 'show');
$router->get('/partner',             'PageController', 'show');
$router->get('/sponsoren',           'PageController', 'show');
$router->get('/mitglied-werden',     'PageController', 'show');

// ── Section CRUD → PageController ────────────────────────────
$router->post('/page/section/add',              'PageController', 'addSection');
$router->post('/page/section/{id}/save',        'PageController', 'saveSection');
$router->post('/page/section/{id}/delete',      'PageController', 'deleteSection');
$router->post('/page/section/reorder',          'PageController', 'reorderSections');
$router->post('/page/section/{id}/remove-image',   'PageController',  'removeSectionImage');

// ── Tean CRUD → TeamController ───────────────────────────────
// GET
$router->get('/team',             'TeamController', 'index');
$router->get('/team/{slug}',      'TeamController', 'show');

// POST
$router->post('/team/add',       'TeamController', 'add');
$router->post('/team/{id}/save', 'TeamController', 'save');
$router->post('/team/{id}/delete', 'TeamController', 'delete');


// ── Events CRUD → EventController ──────────────────────────────
$router->get('/veranstaltungen',              'EventController', 'index');
$router->get('/veranstaltungen/{slug}',       'EventController', 'show');
$router->get('/archiv',                       'EventController', 'archive');
$router->get('/events/{id}/promo-fragment',   'EventController', 'promoFragment');
$router->post('/events/add',                  'EventController', 'add');
$router->post('/events/{id}/save',            'EventController', 'save');
$router->post('/events/{id}/delete',          'EventController', 'delete');
$router->post('/events/{id}/participant/add', 'EventController', 'addParticipant');
$router->post('/events/{id}/participant/remove', 'EventController', 'removeParticipant');


// ── Participants CRUD → ParticipantController ──────────────────
$router->get('/kuenstlerinnen',               'ParticipantController', 'index');
$router->get('/kuenstlerinnen/{slug}',        'ParticipantController', 'show');
$router->post('/participants/add',            'ParticipantController', 'add');
$router->post('/participants/{id}/save',      'ParticipantController', 'save');
$router->post('/participants/{id}/delete',    'ParticipantController', 'delete');


// Organisation
$router->get('/' . $_ENV['ADMIN_PATH'] . '/org',        'OrganisationController', 'edit');
$router->post('/' . $_ENV['ADMIN_PATH'] . '/org/save',  'OrganisationController', 'save');


// ── Cloudinary service ─────────────────────────────────────────
$router->post('/media/upload',        'MediaController', 'upload');
$router->post('/media/{id}/delete',   'MediaController', 'delete');
$router->post('/media/reorder',       'MediaController', 'reorder');
$router->post('/media/upload-section', 'MediaController', 'uploadSection');
$router->post('/media/{id}/meta',     'MediaController', 'updateMeta');
$router->post('/media/batch-meta',    'MediaController', 'batchMeta');

// ── URLs CRUD → UrlController ────────────────────────────────
$router->get('/urls/fragment',        'UrlController', 'fragment');
$router->get('/urls/types',           'UrlController', 'types');
$router->get('/urls/search',          'UrlController', 'search');
$router->post('/urls/add',            'UrlController', 'add');
$router->post('/urls/{id}/attach',    'UrlController', 'attach');
$router->post('/urls/{id}/unlink',    'UrlController', 'unlink');
$router->post('/urls/{id}/delete',    'UrlController', 'delete');
$router->post('/urls/{id}/save',      'UrlController', 'save');
