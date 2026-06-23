<?php

/**
 * ContactController
 * 
 * Renders the contact page.
 * Free intro section from pages table. 
 * Contact information from organisation_info.
 * Social links from urls table.
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';


class ContactController extends BaseController
{
    public function index(array $params = []): void
    {
        $pagesModel = new PagesModel();
        $urlModel = new UrlModel();

        $sections = $pagesModel->getForPage('kontakt');
        $urls = $urlModel->getForEntity('organisation', 1);

        $this->render('pages/contact', [
            'sections' => $sections,
            'urls' => $urls,
            'pageKey' => 'kontakt',
        ]);
    }
}
