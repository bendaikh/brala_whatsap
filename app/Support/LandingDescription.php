<?php

namespace App\Support;

/**
 * Helpers for laying out the landing-page description around the order form.
 */
class LandingDescription
{
    /**
     * Split a (Quill) description into a short intro paragraph and the rest.
     *
     * The intro is the first top-level text paragraph, but only when the HTML
     * starts with it (ignoring empty paragraphs) — i.e. it comes before any
     * heading, image, video or list. Descriptions that start with a heading
     * (like the AI image-caption layout "<h2>…</h2><p>…</p><p><img></p>")
     * return an empty intro and the full HTML as the rest.
     *
     * @return array{0: string, 1: string} [introHtml, restHtml]
     */
    public static function splitIntro(?string $html): array
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return ['', ''];
        }

        $offset = 0;
        $length = strlen($html);

        while ($offset < $length) {
            // Skip whitespace between top-level blocks
            if (preg_match('/\G\s+/A', $html, $ws, 0, $offset)) {
                $offset += strlen($ws[0]);
                continue;
            }

            // Next top-level block must be a <p> (no nested <p> in Quill output)
            if (!preg_match('#\G<p\b[^>]*>(.*?)</p>#siA', $html, $m, 0, $offset)) {
                break;
            }

            $inner = $m[1];
            $isMedia = preg_match('#<(img|video|iframe)\b#i', $inner) === 1;
            $text = trim(html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            $text = trim($text, " \t\n\r\0\x0B\xC2\xA0");

            if ($isMedia) {
                break;
            }

            if ($text === '') {
                // Empty spacer paragraph (<p><br></p>) — skip it
                $offset += strlen($m[0]);
                continue;
            }

            $intro = $m[0];
            $rest = substr($html, $offset + strlen($m[0]));

            return [$intro, $rest];
        }

        return ['', $html];
    }

    public static function hasContent(?string $html): bool
    {
        if ($html === null || trim($html) === '') {
            return false;
        }

        $text = trim(strip_tags($html));

        return $text !== ''
            || stripos($html, '<img') !== false
            || stripos($html, '<video') !== false
            || stripos($html, '<iframe') !== false;
    }
}
