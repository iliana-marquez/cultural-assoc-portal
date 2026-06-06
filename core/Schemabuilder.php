<?php

/**
 * SchemaBuilder
 *
 * Builds JSON-LD structured data strings for search engines and AI crawlers.
 * All values come from the database — no hardcoded content data.
 * Schema.org vocabulary (@context, @type) is fixed as per the standard.
 *
 * Usage:
 *   SchemaBuilder::build('organisation', $org);
 *   SchemaBuilder::build('occurrence', $event);   // events and projects
 *   SchemaBuilder::build('person', $person);       // team and participants
 */

class SchemaBuilder
{
    /**
     * Build a JSON-LD schema string for the given entity type.
     *
     * @param string $type   'organisation' | 'occurrence' | 'person'
     * @param object $data   Entity data object from the database
     * @return string        JSON-LD script tag or empty string if type unknown
     */
    public static function build(string $type, object $data): string
    {
        $schema = match ($type) {
            'organisation' => self::organisation($data),
            'occurrence'   => self::occurrence($data),
            'person'       => self::person($data),
            default        => null,
        };

        if (!$schema) return '';

        return '<script type="application/ld+json">'
            . json_encode(
                $schema,
                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
            )
            . '</script>';
    }

    /**
     * Organisation schema — homepage and about page.
     *
     * @type driven by organisation_info.schema_type (default: 'Organization')
     * Address fields from split columns: street, postcode, city, country
     */
    private static function organisation(object $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => $data->schema_type ?? 'Organization',
            'name'     => $data->name,
            'logo'     => $data->logo_url ?? '',
            'email'    => $data->email ?? '',
            'address'  => [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $data->street ?? '',
                'postalCode'      => $data->postcode ?? '',
                'addressLocality' => $data->city ?? '',
                'addressCountry'  => $data->country ?? '',
            ],
        ];
    }

    /**
     * Occurrence schema — covers both events and projects.
     *
     * Events have: date, time, venue joined as venue_name/venue_street/venue_city/venue_country
     * Projects have: start_date, end_date, venue joined the same way
     * Both handled via ?? fallbacks — one method, two entity types.
     */
    private static function occurrence(object $data): array
    {
        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Event',
            'name'        => $data->title,
            'description' => strip_tags($data->description ?? ''),
            'startDate'   => $data->date ?? $data->start_date ?? '',
            'endDate'     => $data->end_date ?? '',
            'location'    => [
                '@type'           => 'Place',
                'name'            => $data->venue_name ?? '',
                'streetAddress'   => $data->venue_street ?? '',
                'addressLocality' => $data->venue_city ?? '',
                'addressCountry'  => $data->venue_country ?? '',
            ],
        ];
    }

    /**
     * Person schema — team members and participants.
     *
     * team has: role
     * participants have: field
     * Both handled via ?? fallback on jobTitle.
     */
    private static function person(object $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'Person',
            'name'     => $data->first_name . ' ' . $data->last_name,
            'jobTitle' => $data->role ?? $data->field ?? '',
            'image'    => $data->image ?? '',
        ];
    }
}
