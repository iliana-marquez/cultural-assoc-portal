<?php

/**
 * OrganisationModel
 *
 * Manages the single organisation_info row.
 * One deployment = one organisation = one row.
 * No insert or delete — only get and update.
 * URL management handled by UrlModel.
 */

class OrganisationModel extends BaseModel
{
    private string $table = 'organisation_info';

    /**
     * Fetch the organisation record.
     * Always returns the first (and only) row.
     *
     * @return object|null
     */
    public function get(): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} LIMIT 1"
        );
    }

    /**
     * Fetch organisation with its URLs joined.
     * Used when displaying contact page or footer links.
     *
     * @return object|null
     */
    public function getWithUrls(): ?object
    {
        $org = $this->get();

        if (!$org) return null;

        $org->urls = $this->fetchAll(
            "SELECT u.id, u.url, ut.label, ut.icon
             FROM urls u
             JOIN url_types ut ON u.url_type_id = ut.id
             WHERE u.entity_type = 'organisation'
             AND u.entity_id = ?
             ORDER BY ut.id ASC",
            [$org->id]
        );

        return $org;
    }

    /**
     * Update organisation info.
     * Called from edit mode — manager updates contact/identity data.
     *
     * @param array $data Associative array of fields to update
     * @return bool
     */
    /**
     * Update a single field — used by entity-edit-row AJAX saves.
     */
    public function updateField(string $field, string $value): bool
    {
        $allowed = [
            'name',
            'tagline',
            'description',
            'seo_description',
            'organisation_type',
            'email',
            'phone',
            'street',
            'postcode',
            'city',
            'country',
            'registration_number',
            'statutes_url',
            'schema_type',
            'logo_url',
            'inline_logo_url',
            'account_holder',
            'iban',
            'bic',
            'payment_purpose',
            'donation_purpose',
            'donation_note',
            'membership_fee',
            'membership_note',
        ];

        if (!in_array($field, $allowed)) return false;

        return $this->execute(
            "UPDATE {$this->table} SET {$field} = ? WHERE id = 1",
            [$value]
        );
    }
}
