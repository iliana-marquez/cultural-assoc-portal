<?php

/**
 * TeamController
 *
 * Handles team listing and detail pages.
 * Free intro/closing sections from pages table via PagesModel.
 * Team data from team table via TeamModel.
 * Member URLs from urls table via UrlModel.
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/TeamModel.php';
require_once __DIR__ . '/../Models/PagesModel.php';
require_once __DIR__ . '/../Models/UrlModel.php';

class TeamController extends BaseController
{
    private TeamModel  $teamModel;
    private PagesModel $pagesModel;
    private UrlModel   $urlModel;

    public function __construct()
    {
        parent::__construct();
        $this->teamModel  = new TeamModel();
        $this->pagesModel = new PagesModel();
        $this->urlModel   = new UrlModel();
    }

    /**
     * GET /team
     * Team listing page.
     * Free intro sections + team member cards.
     */
    public function index(array $params = []): void
    {
        $sections = $this->pagesModel->getForPage('team');
        $members  = $this->teamModel->getAll();

        // Add slug to each member for card links
        foreach ($members as $member) {
            $member->slug = TeamModel::generateSlug(
                $member->first_name,
                $member->last_name
            );
        }

        $seo = $this->buildSeo(
            $this->org,
            $this->org->name . ' | Team',
            'Das Team von ' . $this->org->name
        );

        $this->render('pages/team', [
            'sections' => $sections,
            'members'  => $members,
            'seo'      => $seo,
            'pageKey'  => 'team',
        ]);
    }

    /**
     * GET /team/{slug}
     * Team member detail page.
     */
    public function show(array $params = []): void
    {
        $slug   = $params['slug'] ?? '';
        $member = $this->teamModel->getBySlug($slug);

        if (!$member) {
            $this->renderNotFound();
            return;
        }

        $member->slug = $slug;
        $urls = $this->urlModel->getForEntity('team', $member->id);

        $seo = $this->buildSeo(
            $this->org,
            TeamModel::displayName($member) . ' | ' . $this->org->name,
            $member->biography
                ? substr(strip_tags($member->biography), 0, 160)
                : ($member->motto ?? ''),
            $member->image ?? $this->org->logo_url ?? '',
            'website',
            SchemaBuilder::build('person', $member)
        );

        $this->render('pages/team-detail', [
            'member'  => $member,
            'urls'    => $urls,
            'seo'     => $seo,
        ]);
    }

    // Add to TeamController:

    /**
     * POST /team/add
     * Add new team member.
     */
    public function add(array $params = []): void
    {
        $this->requireLogin();
        // validate + insert via TeamModel
    }

    /**
     * POST /team/{id}/save
     * Update team member.
     */
    public function save(array $params = []): void
    {
        $this->requireLogin();
        // validate + update via TeamModel
    }

    /**
     * POST /team/{id}/delete
     * Delete team member.
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();
        // hard delete — team member is an entity, consider soft delete
    }
}
