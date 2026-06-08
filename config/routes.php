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

// Public
$router->get('/', 'HomeController', 'index');

// Editor
$adminPath = $config['admin_path'];

$router->get('/' . $adminPath,             'AuthController', 'showLogin');
$router->post('/' . $adminPath,            'AuthController', 'sendOtp');
$router->get('/' . $adminPath . '/verify', 'AuthController', 'showVerify');
$router->post('/' . $adminPath . '/verify', 'AuthController', 'verifyOtp');
$router->get('/logout',                    'AuthController', 'logout');
