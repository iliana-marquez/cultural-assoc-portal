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
