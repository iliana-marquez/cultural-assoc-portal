<?php

/**
 * UrlModel
 *
 * Manages external URLs via pivot architecture.
 * URLs stored once in urls table — linked to any entity via entity_urls pivot.
 * One URL can be linked to multiple entities (team + participant, events sharing festival URL).
 * Fix URL once → reflects everywhere it's linked.
 */

class UrlModel extends BaseModel
{
    private string $table       = 'urls';
    private string $pivotTable  = 'entity_urls';

    /**
     * Whether the given url_type_id corresponds to the "Email" type.
     * Used internally to decide whether normalize() should prepend
     * mailto: automatically.
     *
     * @param int $urlTypeId
     * @return bool
     */
    private function isEmailType(int $urlTypeId): bool
    {
        $type = $this->fetchOne(
            "SELECT label FROM url_types WHERE id = ?",
            [$urlTypeId]
        );
        return $type && strcasecmp($type->label, 'Email') === 0;
    }

    /**
     * Required domain (suffix-matched, not substring-matched) for each
     * type that has a known canonical platform domain. A Bandcamp link
     * like "https://myband.bandcamp.com" correctly matches "bandcamp.com"
     * as a SUFFIX of the host — "bandcamp.com.evil-site.net" does not,
     * since that's a substring match but not a suffix match.
     *
     * Types not listed here (Website, Press, Radio, TV, Maps, Other)
     * have no specific domain requirement — any syntactically valid
     * URL is accepted.
     *
     * @return array<string, string[]>
     */
    private static function platformDomains(): array
    {
        return [
            'facebook'   => ['facebook.com'],
            'instagram'  => ['instagram.com'],
            'linkedin'   => ['linkedin.com'],
            'youtube'    => ['youtube.com', 'youtu.be'],
            'spotify'    => ['spotify.com'],
            'soundcloud' => ['soundcloud.com'],
            'vimeo'      => ['vimeo.com'],
            'bandcamp'   => ['bandcamp.com'],
        ];
    }

    /**
     * Whether a host (+ path, for the ambiguous google.com case)
     * looks like a real maps link. Mirrors the JS validation exactly.
     *
     * @param string $host  already lowercased
     * @param string $path
     * @return bool
     */
    private static function isValidMapsHost(string $host, string $path): bool
    {
        $suffixMatch = function (string $domain) use ($host) {
            return $host === $domain || str_ends_with($host, '.' . $domain);
        };

        if ($suffixMatch('maps.google.com')) return true;
        if ($suffixMatch('maps.apple.com')) return true;
        if ($suffixMatch('openstreetmap.org')) return true;
        if ($suffixMatch('goo.gl')) return true;
        // Bare google.com is too broad to accept on domain alone
        // (it's also search, gmail, etc.) — require /maps in the path.
        if ($suffixMatch('google.com') && str_starts_with($path, '/maps')) return true;

        return false;
    }

    /**
     * Validate a URL against the rules for its selected type.
     * Returns null when valid, or a human-readable German error
     * message when invalid — server-side authority, called from
     * UrlController before any save, regardless of what client-side
     * validation already ran (which can be bypassed).
     *
     * @param string $url       the RAW, not-yet-normalized url as typed
     * @param int    $urlTypeId
     * @return string|null
     */
    public function validateForType(string $url, int $urlTypeId): ?string
    {
        $type = $this->fetchOne(
            "SELECT label FROM url_types WHERE id = ?",
            [$urlTypeId]
        );

        if (!$type) {
            return 'Unbekannter Link-Typ.';
        }

        $label = strtolower($type->label);
        $url   = trim($url);

        if ($label === 'email') {
            // Strip an already-typed mailto: before checking the shape,
            // since the editor may or may not have typed it themselves.
            $candidate = preg_replace('/^mailto:/i', '', $url);
            if (!filter_var($candidate, FILTER_VALIDATE_EMAIL)) {
                return 'Bitte eine gültige E-Mail-Adresse eingeben.';
            }
            return null;
        }

        // Everything else must at least be a syntactically valid URL
        // once normalized the same way normalize() would.
        $candidate = preg_replace('/^http:\/\//i', 'https://', $url);
        if (!preg_match('#^https?://#i', $candidate)) {
            $candidate = 'https://' . $candidate;
        }

        $host = parse_url($candidate, PHP_URL_HOST);
        $path = parse_url($candidate, PHP_URL_PATH) ?: '';
        if (!$host) {
            return 'Bitte eine gültige URL eingeben.';
        }
        $host = strtolower($host);

        if ($label === 'maps') {
            if (!self::isValidMapsHost($host, $path)) {
                return 'Diese URL scheint kein Karten-Link zu sein (z. B. Google Maps, Apple Maps, OpenStreetMap).';
            }
            return null;
        }

        $domains = self::platformDomains();
        if (isset($domains[$label])) {
            $matches = false;
            foreach ($domains[$label] as $requiredDomain) {
                // Suffix match: host is the domain itself, or ends with
                // ".{domain}" (covers subdomains like myband.bandcamp.com).
                if ($host === $requiredDomain || str_ends_with($host, '.' . $requiredDomain)) {
                    $matches = true;
                    break;
                }
            }
            if (!$matches) {
                $expected = implode(' oder ', $domains[$label]);
                return 'Diese URL scheint nicht zu ' . $type->label . ' zu gehören (erwartet: ' . $expected . ').';
            }
        }

        return null;
    }

    /**
     * Normalize a URL string into one canonical, storable format,
     * aware of the selected url_type so the editor never has to know
     * about schemes like mailto: themselves — picking "Email" as the
     * type is enough; the app supplies the correct prefix.
     *
     * - Email type — trimmed, lowercased, "mailto:" prepended if not
     *   already present (handles both "name@org.at" and someone
     *   pasting "mailto:name@org.at" directly, without double-prefixing)
     * - everything else — trimmed, http:// upgraded to https://,
     *   trailing slash removed, domain portion lowercased (path/query
     *   left as-is, since paths can be legitimately case-sensitive)
     *
     * @param string $url
     * @param bool   $isEmailType  whether the selected url_type is Email
     * @return string
     */
    public static function normalize(string $url, bool $isEmailType = false): string
    {
        $url = trim($url);

        if ($isEmailType || stripos($url, 'mailto:') === 0) {
            if (stripos($url, 'mailto:') !== 0) {
                $url = 'mailto:' . $url;
            }
            return strtolower($url);
        }

        // Upgrade http:// to https://
        $url = preg_replace('/^http:\/\//i', 'https://', $url);

        // If no protocol at all, assume https://
        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        // Lowercase only the domain portion (scheme + host),
        // leave path/query/fragment untouched.
        $url = preg_replace_callback(
            '#^(https://)([^/]+)#i',
            function ($m) {
                return $m[1] . strtolower($m[2]);
            },
            $url
        );

        // Strip a single trailing slash.
        $url = rtrim($url, '/');

        return $url;
    }

    /**
     * Get all URLs for an entity via pivot.
     *
     * @param string $entityType  'organisation' | 'team' | 'participant' | 'event' | 'venue'
     * @param int    $entityId
     * @return array
     */
    public function getForEntity(string $entityType, int $entityId): array
    {
        return $this->fetchAll(
            "SELECT u.*, ut.label as type_label, ut.icon
             FROM {$this->table} u
             INNER JOIN {$this->pivotTable} eu ON eu.url_id = u.id
             INNER JOIN url_types ut ON ut.id = u.url_type_id
             WHERE eu.entity_type = ? AND eu.entity_id = ?
             ORDER BY ut.label ASC",
            [$entityType, $entityId]
        );
    }

    /**
     * Search URLs by label or url string — for the "pick existing"
     * step of the URL picker modal.
     *
     * @param string $query
     * @param int    $limit
     * @return array
     */
    public function search(string $query, int $limit = 10): array
    {
        $like = '%' . $query . '%';
        return $this->fetchAll(
            "SELECT u.*, ut.label as type_label, ut.icon
             FROM {$this->table} u
             INNER JOIN url_types ut ON ut.id = u.url_type_id
             WHERE u.url LIKE ? OR u.label LIKE ?
             ORDER BY u.label ASC
             LIMIT ?",
            [$like, $like, $limit]
        );
    }

    /**
     * Get all available URL types (Website, Email, Instagram, etc.)
     * Used to populate the type selector in the picker modal — and
     * to avoid anyone needing to guess at url_type_id values.
     *
     * @return array
     */
    public function getTypes(): array
    {
        return $this->fetchAll(
            "SELECT * FROM url_types ORDER BY label ASC"
        );
    }

    /**
     * Get a URL by its id.
     *
     * @param int $urlId
     * @return object|null
     */
    public function getById(int $urlId): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE id = ?",
            [$urlId]
        );
    }

    /**
     * Find existing URL by url string (exact match on the
     * already-normalized form).
     * Used to prevent duplicates and enable sharing.
     *
     * @param string $url
     * @return object|null
     */
    public function findByUrl(string $url): ?object
    {
        return $this->fetchOne(
            "SELECT * FROM {$this->table} WHERE url = ?",
            [$url]
        );
    }

    /**
     * Add URL and link to entity.
     * If URL already exists (after normalization) — reuses existing
     * record rather than creating a duplicate.
     * Links URL to entity via pivot.
     *
     * @param string      $entityType
     * @param int         $entityId
     * @param int         $urlTypeId
     * @param string      $url
     * @param string|null $label       optional display label
     * @return int|false  the url row's id (existing or newly created), or false on failure
     */
    public function addForEntity(string $entityType, int $entityId, int $urlTypeId, string $url, ?string $label = null)
    {
        $url = self::normalize($url, $this->isEmailType($urlTypeId));

        // Check if URL already exists
        $existing = $this->findByUrl($url);

        if ($existing) {
            $urlId = $existing->id;
        } else {
            // Insert new URL
            $this->execute(
                "INSERT INTO {$this->table} (url_type_id, url, label) VALUES (?, ?, ?)",
                [$urlTypeId, $url, $label]
            );
            $urlId = $this->lastInsertId();
        }

        // Link to entity via pivot (ignore if already linked)
        $linked = $this->execute(
            "INSERT IGNORE INTO {$this->pivotTable} (url_id, entity_type, entity_id)
             VALUES (?, ?, ?)",
            [$urlId, $entityType, $entityId]
        );

        return $linked ? $urlId : false;
    }

    /**
     * Count how many entities a URL is currently linked to.
     * Used to determine, before unlinking, whether this removal
     * would be the last link (and therefore also delete the
     * underlying record) — so the caller can warn the editor
     * first, rather than deleting silently.
     *
     * @param int $urlId
     * @return int
     */
    public function countLinks(int $urlId): int
    {
        $result = $this->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->pivotTable} WHERE url_id = ?",
            [$urlId]
        );
        return (int) ($result->count ?? 0);
    }

    /**
     * Attach an already-existing URL (by id) to an entity, without
     * creating a new url row. Used by the picker modal's "choose
     * existing" path.
     *
     * @param int    $urlId
     * @param string $entityType
     * @param int    $entityId
     * @return bool
     */
    public function attachToEntity(int $urlId, string $entityType, int $entityId): bool
    {
        return $this->execute(
            "INSERT IGNORE INTO {$this->pivotTable} (url_id, entity_type, entity_id)
             VALUES (?, ?, ?)",
            [$urlId, $entityType, $entityId]
        );
    }

    /**
     * Unlink URL from entity.
     * Removes pivot row only — URL record preserved for other entities.
     * If URL has no more entity links → delete URL record too.
     *
     * @param int    $urlId
     * @param string $entityType
     * @param int    $entityId
     * @return bool
     */
    public function unlinkFromEntity(int $urlId, string $entityType, int $entityId): bool
    {
        // Remove pivot link
        $this->execute(
            "DELETE FROM {$this->pivotTable}
             WHERE url_id = ? AND entity_type = ? AND entity_id = ?",
            [$urlId, $entityType, $entityId]
        );

        // Check if URL still linked to other entities
        $remaining = $this->fetchOne(
            "SELECT COUNT(*) as count FROM {$this->pivotTable} WHERE url_id = ?",
            [$urlId]
        );

        // If no more links → delete URL record
        if (($remaining->count ?? 0) === 0) {
            $this->execute(
                "DELETE FROM {$this->table} WHERE id = ?",
                [$urlId]
            );
        }

        return true;
    }

    /**
     * Force-delete a URL record entirely, regardless of how many
     * entities still reference it — e.g. an editor cleanup tool for
     * a URL that's simply wrong everywhere it appears.
     *
     * entity_urls.url_id has ON DELETE CASCADE at the database level,
     * so all pivot rows referencing this url are removed automatically;
     * no manual pivot cleanup needed here.
     *
     * @param int $urlId
     * @return bool
     */
    public function delete(int $urlId): bool
    {
        return $this->execute(
            "DELETE FROM {$this->table} WHERE id = ?",
            [$urlId]
        );
    }

    /**
     * Update URL string, type, and/or label.
     * Updates once — reflects on all linked entities automatically.
     *
     * @param int         $urlId
     * @param string      $url
     * @param int         $urlTypeId
     * @param string|null $label
     * @return bool
     */
    public function update(int $urlId, string $url, int $urlTypeId, ?string $label = null): bool
    {
        $url = self::normalize($url, $this->isEmailType($urlTypeId));
        return $this->execute(
            "UPDATE {$this->table} SET url = ?, url_type_id = ?, label = ? WHERE id = ?",
            [$url, $urlTypeId, $label, $urlId]
        );
    }
}
