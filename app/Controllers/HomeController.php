<?php

/**
 * HomeController
 *
 * Renders the homepage.
 * Hero section driven by organisation_info — always first, always fixed.
 * Free sections below hero fetched from pages table via PagesModel.
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PagesModel.php';

class HomeController extends BaseController
{
    public function index(array $params = []): void
    {
        $pagesModel = new PagesModel();
        $sections   = $pagesModel->getForPage('home');

        $this->render('pages/home', [
            'sections' => $sections,
        ]);
    }
}
