<?php

/**
 * RichTextFormatter
 *
 * Two directions:
 *
 *   htmlToMarker()  — editor HTML -> marker string for storage
 *   markerToHtml()  — marker string -> safe HTML for public display
 *
 * Editor produces (via applySpanClass / applyLinkFormat):
 *   <span class="rt-bold">   ->  **text**
 *   <span class="rt-italic"> ->  *text*
 *   <span class="rt-ul">     ->  - text
 *   <a href="url">text</a>   ->  [text](url)
 *
 * On second edit, the field contains markerToHtml's output tags:
 *   <strong>, <em>, <ul><li>
 * These are also handled in htmlToMarker to keep the round-trip clean.
 */

class RichTextFormatter
{
    public static function htmlToMarker(string $html): string
    {
        if (trim($html) === '') return '';

        // Normalise &nbsp;
        $html = str_replace(['&nbsp;', "\xc2\xa0"], ' ', $html);

        // ── Lists (block-level, before inline passes) ─────────

        // <ul><li> from public display (second edit onwards)
        $html = preg_replace_callback(
            '/<ul[^>]*>(.*?)<\/ul>/si',
            function ($m) {
                preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $m[1], $items);
                return implode("\n", array_map(
                    fn($t) => '- ' . trim(strip_tags($t)),
                    $items[1]
                ));
            },
            $html
        );

        // <span class="rt-ul"> or <span class='rt-ul'> from editor
        $html = preg_replace_callback(
            '/<span[^>]*class=["\']rt-ul["\'][^>]*>(.*?)<\/span>/si',
            fn($m) => '- ' . trim($m[1]),
            $html
        );

        // ── Links ─────────────────────────────────────────────

        $html = preg_replace_callback(
            '/<a\s+href=["\']([^"\']*)["\'][^>]*>(.*?)<\/a>/si',
            fn($m) => '[' . strip_tags($m[2]) . '](' . $m[1] . ')',
            $html
        );

        // ── Inline: editor spans (single OR double quotes) ────

        // rt-bold
        $html = preg_replace_callback(
            '/<span[^>]*class=["\']rt-bold["\'][^>]*>(.*?)<\/span>/si',
            fn($m) => '**' . trim($m[1]) . '**',
            $html
        );

        // rt-italic
        $html = preg_replace_callback(
            '/<span[^>]*class=["\']rt-italic["\'][^>]*>(.*?)<\/span>/si',
            fn($m) => '*' . trim($m[1]) . '*',
            $html
        );

        // ── Inline: public display tags (second edit onwards) ─

        // <strong> from markerToHtml
        $html = preg_replace('/<strong[^>]*>(.*?)<\/strong>/si', '**$1**', $html);

        // <em> from markerToHtml
        $html = preg_replace('/<em[^>]*>(.*?)<\/em>/si', '*$1*', $html);

        // ── Block separators -> newlines ───────────────────────

        $html = preg_replace('/<br\s*\/?>/i',    "\n", $html);
        $html = preg_replace('/<\/(div|p)>/i',    "\n", $html);
        $html = preg_replace('/<(div|p)[^>]*>/i', '',   $html);

        // Strip anything remaining
        $html = strip_tags($html);

        // Clean up whitespace
        $html  = preg_replace('/[ \t]+/', ' ', $html);
        $lines = array_map('trim', explode("\n", $html));
        $out   = [];
        $prev  = false;
        foreach ($lines as $line) {
            $blank = $line === '';
            if ($blank && $prev) continue;
            $out[] = $line;
            $prev  = $blank;
        }

        return trim(implode("\n", $out));
    }

    public static function markerToHtml(string $marker): string
    {
        if (trim($marker) === '') return '';

        $lines   = explode("\n", $marker);
        $out     = [];
        $listBuf = [];

        $flushList = function () use (&$listBuf, &$out) {
            if (empty($listBuf)) return;
            $out[] = '<ul>' . implode('', array_map(
                fn($item) => '<li>' . $item . '</li>',
                $listBuf
            )) . '</ul>';
            $listBuf = [];
        };

        foreach ($lines as $line) {
            if (preg_match('/^- (.*)$/', $line, $m)) {
                $listBuf[] = self::inlineToHtml($m[1]);
                continue;
            }
            $flushList();
            if (trim($line) === '') continue;
            $out[] = self::inlineToHtml($line);
        }
        $flushList();

        return implode("\n", $out);
    }

    private static function inlineToHtml(string $line): string
    {
        $e = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // [text](url) -> <a>
        $e = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)]+)\)/',
            function ($m) {
                $host = parse_url($m[2], PHP_URL_HOST);
                if (!$host) return htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                return '<a href="' . htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8')
                    . '" target="_blank" rel="noopener noreferrer">'
                    . htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8') . '</a>';
            },
            $e
        );

        // **text** -> <strong>  (before * to avoid matching inside **)
        $e = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $e);

        // *text* -> <em>
        $e = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $e);

        return $e;
    }
}
