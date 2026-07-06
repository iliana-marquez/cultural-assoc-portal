<?php

/**
 * SpendenController
 *
 * GET /spenden → donation info page
 *   Free sections above (editor fills via CMS)
 *   Hardcoded donation section below — bank details, QR code, donation_note
 *   All org data from $this->org (loaded once in BaseController)
 *
 * No POST yet — placeholder for future: PDF download, donation form, etc.
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/PagesModel.php';

class SpendenController extends BaseController
{
    private PagesModel $pagesModel;

    public function __construct()
    {
        parent::__construct();
        $this->pagesModel = new PagesModel();
    }

    /**
     * GET /spenden
     */
    public function index(array $params = []): void
    {
        $sections = $this->pagesModel->getForPage('spenden');

        $seo = $this->buildSeo(
            $this->org,
            'Spenden | ' . $this->org->name
        );

        $this->render('pages/spenden', [
            'sections' => $sections,
            'pageKey'  => 'spenden',
            'seo'      => $seo,
        ]);
    }
}
