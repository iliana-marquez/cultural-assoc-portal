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
$router->post('/events/add',                  'EventController', 'add');
$router->post('/events/{id}/save',            'EventController', 'save');
$router->post('/events/{id}/delete',          'EventController', 'delete');
$router->post('/events/{id}/participant/add', 'EventController', 'addParticipant');
$router->post('/events/{id}/participant/remove', 'EventController', 'removeParticipant');


// ── Participants CRUD → ParticipantController ─────────────────────
$router->get('/kuenstlerinnen',               'ParticipantController', 'index');
$router->get('/kuenstlerinnen/{slug}',        'ParticipantController', 'show');
$router->post('/participants/add',            'ParticipantController', 'add');
$router->post('/participants/{id}/save',      'ParticipantController', 'save');
$router->post('/participants/{id}/delete',    'ParticipantController', 'delete');
