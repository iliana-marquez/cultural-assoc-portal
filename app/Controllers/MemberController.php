<?php

/**
 * MemberController
 *
 * GET  /members          → member admin listing (tabbed: pending/active/expired)
 * POST /members/{id}/activate → activate pending member, send confirmation email
 * POST /members/{id}/renew    → renew active/expired member, send renewal email
 * POST /members/{id}/delete   → soft delete member
 * GET  /members/export        → CSV export of all non-deleted members
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../Models/MemberModel.php';

class MemberController extends BaseController
{
    private MemberModel $memberModel;

    public function __construct()
    {
        parent::__construct();
        $this->memberModel = new MemberModel();
    }

    /**
     * GET /members
     */
    public function index(array $params = []): void
    {
        $this->requireLogin();

        $pending = $this->memberModel->getAll('pending');
        $active  = $this->memberModel->getAll('active');
        $expired = $this->memberModel->getAll('expired');

        $counts = [
            'pending' => count($pending),
            'active'  => count($active),
            'expired' => count($expired),
        ];

        $seo = $this->buildSeo($this->org, 'Mitglieder | ' . $this->org->name);

        $this->render('pages/members', [
            'pending' => $pending,
            'active'  => $active,
            'expired' => $expired,
            'counts'  => $counts,
            'seo'     => $seo,
        ]);
    }

    /**
     * POST /members/{id}/activate
     */
    public function activate(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $member = $this->memberModel->getById($id);
        if (!$member) {
            $this->jsonError('Mitglied nicht gefunden.');
            return;
        }

        $success = $this->memberModel->activate($id);
        if (!$success) {
            $this->jsonError('Aktivierung fehlgeschlagen.');
            return;
        }

        // Reload to get updated expires_at
        $member = $this->memberModel->getById($id);

        // Send activation confirmation email
        $body = Mailer::renderView('emails/member-activated', [
            'org'        => $this->org,
            'orgName'    => $this->org->name,
            'first_name' => $member->first_name,
            'expires_at' => date('d.m.Y', strtotime($member->expires_at)),
        ]);

        Mailer::send(
            to: $member->email,
            subject: 'Ihre Mitgliedschaft bei ' . $this->org->name . ' wurde aktiviert',
            body: $body,
            fromName: $this->org->name
        );

        $this->jsonSuccess(['expires_at' => date('d.m.Y', strtotime($member->expires_at))]);
    }

    /**
     * POST /members/{id}/renew
     */
    public function renew(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $member = $this->memberModel->getById($id);
        if (!$member) {
            $this->jsonError('Mitglied nicht gefunden.');
            return;
        }

        $success = $this->memberModel->renew($id);
        if (!$success) {
            $this->jsonError('Verlängerung fehlgeschlagen.');
            return;
        }

        // Reload to get updated expires_at
        $member = $this->memberModel->getById($id);

        // Send renewal confirmation email
        $body = Mailer::renderView('emails/member-renewed', [
            'org'        => $this->org,
            'orgName'    => $this->org->name,
            'first_name' => $member->first_name,
            'expires_at' => date('d.m.Y', strtotime($member->expires_at)),
        ]);

        Mailer::send(
            to: $member->email,
            subject: 'Ihre Mitgliedschaft bei ' . $this->org->name . ' wurde verlängert',
            body: $body,
            fromName: $this->org->name
        );

        $this->jsonSuccess(['expires_at' => date('d.m.Y', strtotime($member->expires_at))]);
    }

    /**
     * POST /members/{id}/delete
     */
    public function delete(array $params = []): void
    {
        $this->requireLogin();
        $id = (int) ($params['id'] ?? 0);

        $success = $this->memberModel->delete($id);
        $success ? $this->jsonSuccess() : $this->jsonError('Löschen fehlgeschlagen.');
    }

    /**
     * GET /members/export
     */
    public function export(array $params = []): void
    {
        $this->requireLogin();

        $members = $this->memberModel->export();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="mitglieder-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

        fputcsv($out, [
            'Vorname',
            'Nachname',
            'E-Mail',
            'Straße',
            'PLZ',
            'Ort',
            'Telefon',
            'Geburtstag',
            'Newsletter',
            'Verwendungszweck',
            'Status',
            'Läuft ab',
            'Angemeldet'
        ]);

        foreach ($members as $m) {
            fputcsv($out, [
                $m->first_name,
                $m->last_name,
                $m->email,
                $m->street       ?? '',
                $m->plz          ?? '',
                $m->city         ?? '',
                $m->phone        ?? '',
                $m->birth_date   ?? '',
                $m->newsletter ? 'Ja' : 'Nein',
                $m->payment_reference,
                $m->status,
                $m->expires_at   ?? '',
                $m->created_at,
            ]);
        }

        fclose($out);
        exit;
    }
}
