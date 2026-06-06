<?php

/**
 * HomeController
 * 
 * Handles request for the homepage.
 */

class HomeController
{
    public function index(array $params = []): void
    {
        echo 'Hello from HomeController ;D';
    }
}
