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
 * 
 * IMPORTANT: static routes must always come before dynamic {id}
 * routes for the same path prefix — the router matches top to bottom.
 */

// ── Editor ────────────────────────────────────────────────
$adminPath = $config['admin_path'];
$router->get('/' . $adminPath,             'AuthController', 'showLogin');
$router->post('/' . $adminPath,            'AuthController', 'sendOtp');
$router->get('/' . $adminPath . '/verify', 'AuthController', 'showVerify');
$router->post('/' . $adminPath . '/verify', 'AuthController', 'verifyOtp');

// -- Contact -------------------------------------------------
$router->get('/kontakt',                    'ContactController', 'index');
$router->post('/kontakt',                   'ContactController', 'send');
$router->get('/logout',                    'AuthController', 'logout');

// ── Free Pages (non entity-specific) → PageController ───────
$router->get('/',                    'PageController', 'show');
$router->get('/ueber-uns',           'PageController', 'show');
$router->get('/alsergrund',          'PageController', 'show');
$router->get('/partner',             'PageController', 'show');
$router->get('/sponsoren',           'PageController', 'show');
$router->get('/mitglied-werden',     'PageController', 'show');
$router->get('/datenschutz',         'PageController', 'datenschutz');
$router->get('/impressum',           'PageController', 'impressum');

// ── Section CRUD → PageController ────────────────────────────
$router->post('/page/section/add',              'PageController', 'addSection');
$router->post('/page/section/{id}/save',        'PageController', 'saveSection');
$router->post('/page/section/{id}/delete',      'PageController', 'deleteSection');
$router->post('/page/section/reorder',          'PageController', 'reorderSections');
$router->post('/page/section/{id}/remove-image',   'PageController',  'removeSectionImage');

// ── Tean CRUD → TeamController ───────────────────────────────
$router->get('/team',                       'TeamController', 'index');
$router->get('/team/{id}/profile-fragment',  'TeamController', 'profileFragment');
$router->get('/team/{slug}',                 'TeamController', 'show');
$router->post('/team/add',              'TeamController', 'add');
$router->post('/team/reorder',          'TeamController', 'reorder');
$router->post('/team/{id}/publish',    'TeamController', 'publish');
$router->post('/team/{id}/unpublish',  'TeamController', 'unpublish');
$router->post('/team/{id}/save', 'TeamController', 'save');
$router->post('/team/{id}/delete', 'TeamController', 'delete');


// ── Events CRUD → EventController ──────────────────────────────
$router->get('/veranstaltungen',              'EventController', 'index');
$router->get('/veranstaltungen/{slug}',       'EventController', 'show');
$router->get('/archiv/filter',                'EventController', 'archiveFilter');
$router->get('/archiv',                       'EventController', 'archive');
$router->get('/events/{id}/promo-fragment',   'EventController', 'promoFragment');
$router->post('/events/add',                     'EventController', 'add');
$router->post('/events/{id}/publish',            'EventController', 'publish');
$router->post('/events/{id}/unpublish',          'EventController', 'unpublish');
$router->post('/events/{id}/cancel',             'EventController', 'cancel');
$router->post('/events/{id}/save',               'EventController', 'save');
$router->post('/events/{id}/delete',             'EventController', 'delete');
$router->post('/events/{id}/participant/add',    'EventController', 'addParticipant');
$router->post('/events/{id}/participant/remove', 'EventController', 'removeParticipant');


// ── Participants CRUD → ParticipantController ──────────────────
$router->get('/kuenstlerinnen',                          'ParticipantController', 'index');
$router->get('/participants/{id}/profile-fragment',      'ParticipantController', 'profileFragment');
$router->get('/kuenstlerinnen/{slug}',                   'ParticipantController', 'show');
$router->post('/participants/add',               'ParticipantController', 'add');
$router->post('/participants/{id}/publish',    'ParticipantController', 'publish');
$router->post('/participants/{id}/unpublish',  'ParticipantController', 'unpublish');
$router->post('/participants/{id}/save',      'ParticipantController', 'save');
$router->post('/participants/{id}/delete',    'ParticipantController', 'delete');


// Organisation
$router->get('/' . $_ENV['ADMIN_PATH'] . '/org',        'OrganisationController', 'edit');
$router->post('/' . $_ENV['ADMIN_PATH'] . '/org/save',  'OrganisationController', 'save');
$router->post('/' . $_ENV['ADMIN_PATH'] . '/org/logo/upload', 'OrganisationController', 'uploadLogo');
$router->post('/' . $_ENV['ADMIN_PATH'] . '/org/logo/delete', 'OrganisationController', 'deleteLogo');
$router->post('/' . $adminPath . '/org/logo/delete', 'OrganisationController', 'deleteLogo');
$router->post('/' . $adminPath . '/org/legal-representative', 'OrganisationController', 'setLegalRepresentative');


// ── Cloudinary service ─────────────────────────────────────────
$router->post('/media/upload',        'MediaController', 'upload');
$router->post('/media/{id}/delete',   'MediaController', 'delete');
$router->post('/media/reorder',       'MediaController', 'reorder');
$router->post('/media/upload-section', 'MediaController', 'uploadSection');
$router->post('/media/{id}/meta',     'MediaController', 'updateMeta');
$router->post('/media/batch-meta',    'MediaController', 'batchMeta');


// ── Venues CRUD → VenueController ────────────────────────────
$router->get('/venues/search',        'VenueController', 'search');
$router->post('/venues/add',          'VenueController', 'add');
$router->post('/venues/{id}/save',    'VenueController', 'save');
$router->post('/venues/{id}/delete',  'VenueController', 'delete');
// ── URLs CRUD → UrlController ────────────────────────────────
$router->get('/urls/fragment',        'UrlController', 'fragment');
$router->get('/urls/section-cta-fragment', 'UrlController', 'sectionCtaFragment');
$router->get('/urls/named-pages',     'UrlController', 'namedPages');
$router->get('/urls/types',           'UrlController', 'types');
$router->get('/urls/search',          'UrlController', 'search');
$router->post('/urls/add',            'UrlController', 'add');
$router->post('/urls/add-internal-page', 'UrlController', 'addInternalPage');
$router->post('/urls/{id}/attach',    'UrlController', 'attach');
$router->post('/urls/{id}/unlink',    'UrlController', 'unlink');
$router->post('/urls/{id}/delete',    'UrlController', 'delete');
$router->post('/urls/{id}/save',      'UrlController', 'save');

// ── Newsletter ────────────────────────────────────────────────
$router->post('/newsletter/subscribe',          'NewsletterController', 'subscribe');
$router->get('/newsletter/confirm/{token}',     'NewsletterController', 'confirm');
$router->get('/newsletter/unsubscribe/{token}', 'NewsletterController', 'unsubscribe');
$router->get('/newsletter/subscribers',         'NewsletterController', 'subscribers');
$router->get('/newsletter/export',              'NewsletterController', 'export');
