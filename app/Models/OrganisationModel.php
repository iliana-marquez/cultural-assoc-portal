<?php

/**
 * OrganisationModel
 *
 * Manages the single organisation_info row.
 * One deployment = one organisation = one row.
 * No create or delete - only get and update.
 * URL management handled by UrlModel.
 */

class OrganisationModel extends BaseModel
{
    private string $table = 'organisation_info';

    /**
     * Fetch the organisation record.
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
            "SELECT u.url, ut.label, ut.icon
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
     * Called from edit mode - manage updates contact/identity data.
     * 
     * @param array $data Associative array of fields to update
     * @return bool
     */
    public function update(array $data): bool
    {
        return $this->execute(
            "UPDATE {$this->table}
             SET name              = ?,
                 tagline           = ?,
                 description       = ?,
                 organisation_type = ?,
                 logo_url          = ?,
                 street            = ?,
                 postcode          = ?,
                 city              = ?,
                 country           = ?,
                 registration_number = ?,
                 email             = ?,
                 phone             = ?,
                 statutes_url      = ?
             WHERE id = 1",
            [
                $data['name']                ?? null,
                $data['tagline']             ?? null,
                $data['description']         ?? null,
                $data['organisation_type']   ?? null,
                $data['logo_url']            ?? null,
                $data['street']              ?? null,
                $data['postcode']            ?? null,
                $data['city']               ?? null,
                $data['country']             ?? null,
                $data['registration_number'] ?? null,
                $data['email']              ?? null,
                $data['phone']              ?? null,
                $data['statutes_url']        ?? null,
            ]
        );
    }
}
