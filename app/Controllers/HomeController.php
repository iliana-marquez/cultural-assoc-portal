<?php

/**
 * HomeController
 * 
 * Handles request for the homepage.
 */

require_once __DIR__ . '/BaseController.php';

class HomeController extends BaseController
{
    public function index(array $params = []): void
    {
        $this->render('pages/home');
    }
}
