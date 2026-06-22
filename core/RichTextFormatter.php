<?php

/**
 * RichTextFormatter
 *
 * Converts between a small, safe marker syntax (a deliberate subset
 * of markdown) and real HTML, for every rich-text field across the
 * app — free section text, organisation description, event
 * description, team bio, etc.
 *
 * Why markers, not raw contenteditable HTML: contenteditable's own
 * output can't be trusted directly — different browsers produce
 * slightly different (but visually equivalent) tags, and sanitizing
 * arbitrary HTML safely is a genuinely hard problem (strip_tags()
 * alone doesn't validate attributes within allowed tags, so
 * something like <strong onclick="..."> could theoretically
 * survive). A tiny, explicit converter recognizing ONLY our exact,
 * limited vocabulary sidesteps that risk entirely — anything that
 * isn't one of our five known patterns is escaped as plain text,
 * never trusted as markup.
 *
 * The editor never sees or types these markers — the toolbar
 * applies live visual formatting directly in the contenteditable
 * field; markerToHtml()/htmlToMarker() only run at the boundary
 * (loading content into the editor, saving it back out).
 *
 * Supported syntax (a deliberate markdown SUBSET, not a full
 * parser — chosen for developer-side clarity when reading stored
 * content directly, even though end users never see it):
 *   **bold text**              → <strong>bold text</strong>
 *   *italic text*               → <em>italic text</em>
 *   [link text](https://...)    → <a href="...">link text</a>
 *   - item (one per line)       → <ul><li>item</li></ul>
 *   1. item (one per line)      → <ol><li>item</li></ol>
 */

class RichTextFormatter
{
    /**
     * Convert stored marker syntax into real, safe HTML for display.
     * Plain text (anything not matching a known marker) is always
     * escaped first — markers are recognized and upgraded AFTER
     * escaping, so there's no way for arbitrary HTML to survive
     * even if it was somehow present in stored content.
     */
    public static function markerToHtml(string $marker): string
    {
        $lines = explode("\n", $marker);
        $htmlLines = [];
        $listBuffer = [];
        $listType = null; // 'ul' or 'ol'

        $flushList = function () use (&$listBuffer, &$listType, &$htmlLines) {
            if (empty($listBuffer)) return;
            $tag = $listType === 'ol' ? 'ol' : 'ul';
            $items = array_map(fn($item) => '<li>' . $item . '</li>', $listBuffer);
            $htmlLines[] = "<{$tag}>" . implode('', $items) . "</{$tag}>";
            $listBuffer = [];
            $listType = null;
        };

        foreach ($lines as $line) {
            $bulletMatch = preg_match('/^- (.*)$/', $line, $bm);
            $numberedMatch = preg_match('/^\d+\. (.*)$/', $line, $nm);

            if ($bulletMatch) {
                if ($listType !== 'ul') $flushList();
                $listType = 'ul';
                $listBuffer[] = self::inlineMarkersToHtml($bm[1]);
                continue;
            }

            if ($numberedMatch) {
                if ($listType !== 'ol') $flushList();
                $listType = 'ol';
                $listBuffer[] = self::inlineMarkersToHtml($nm[1]);
                continue;
            }

            // Not a list line — flush any pending list first.
            $flushList();

            if (trim($line) === '') {
                $htmlLines[] = '';
            } else {
                $htmlLines[] = self::inlineMarkersToHtml($line);
            }
        }
        $flushList();

        return implode("\n", $htmlLines);
    }

    /**
     * Apply inline markers (bold, italic, link) to a single line.
     * The line is escaped FIRST via htmlspecialchars, then markers
     * are recognized and upgraded — so raw HTML in the source can
     * never survive, only our own, explicitly-recognized patterns.
     */
    private static function inlineMarkersToHtml(string $line): string
    {
        $escaped = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');

        // Links: [text](url) — the url is validated as a real
        // hostname shape before being trusted, same discipline as
        // every other url accepted elsewhere in this system.
        $escaped = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            function ($m) {
                $text = $m[1];
                $url  = $m[2];
                $host = parse_url($url, PHP_URL_HOST);
                if (!$host || !preg_match('/^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)*$/i', $host)) {
                    // Not a recognizable url shape — render as
                    // plain text rather than a broken/unsafe link.
                    return $text;
                }
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' . $text . '</a>';
            },
            $escaped
        );

        // Bold: **text** — must come before italic, since *text*
        // would otherwise also match inside **text**.
        $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped);

        // Italic: *text*
        $escaped = preg_replace('/\*(.+?)\*/', '<em>$1</em>', $escaped);

        return $escaped;
    }

    /**
     * Convert real HTML (from the editor's own contenteditable DOM,
     * sent up at save time) back into marker syntax for storage.
     * This is the inverse of markerToHtml() — deliberately narrow,
     * recognizing only the exact tags our own toolbar ever produces
     * (<strong>, <em>, <a>, <ul>/<ol>/<li>), never trusting or
     * preserving anything else that might be present.
     */
    public static function htmlToMarker(string $html): string
    {
        // Lists first, since they're block-level and span multiple
        // lines — handled before inline replacements run on the
        // remaining text.
        $html = preg_replace_callback(
            '/<ul>(.*?)<\/ul>/s',
            function ($m) {
                preg_match_all('/<li>(.*?)<\/li>/s', $m[1], $items);
                $lines = array_map(fn($item) => '- ' . trim($item), $items[1]);
                return implode("\n", $lines);
            },
            $html
        );
        $html = preg_replace_callback(
            '/<ol>(.*?)<\/ol>/s',
            function ($m) {
                preg_match_all('/<li>(.*?)<\/li>/s', $m[1], $items);
                $lines = [];
                foreach ($items[1] as $i => $item) {
                    $lines[] = ($i + 1) . '. ' . trim($item);
                }
                return implode("\n", $lines);
            },
            $html
        );

        // Links: <a href="...">text</a> → [text](url)
        $html = preg_replace_callback(
            '/<a\s+href="([^"]*)"[^>]*>(.*?)<\/a>/s',
            function ($m) {
                return '[' . $m[2] . '](' . $m[1] . ')';
            },
            $html
        );

        // Bold / italic
        $html = preg_replace('/<strong>(.*?)<\/strong>/s', '**$1**', $html);
        $html = preg_replace('/<em>(.*?)<\/em>/s', '*$1*', $html);

        // Strip any remaining tags entirely — anything not produced
        // by our own toolbar (e.g. a stray <br>, <div>, <span> a
        // browser's contenteditable implementation might insert)
        // is never preserved as markup, only its text content.
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html);
        $html = strip_tags($html);

        return trim($html);
    }
}
