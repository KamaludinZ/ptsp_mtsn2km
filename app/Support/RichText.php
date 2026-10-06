<?php

namespace App\Support;

use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Rich text written with TinyMCE (services, pengumuman, FAQ): sanitised on
 * save and on display, so scripts, event handlers and unsafe links never
 * reach a page. Older plain-text values ("1. KTP 2. KK", one per line, or a
 * JSON list) still read well: they are shown as lists or paragraphs.
 */
class RichText
{
    /** Safe HTML from editor content (null for empty content). */
    public static function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $clean = self::filterStyles(trim(self::sanitizer()->sanitize($html)));

        return preg_replace('/[\s\x{00A0}]+/u', '', html_entity_decode(strip_tags($clean, '<img>'))) === '' ? null : $clean;
    }

    /** HTML to print on a page: sanitised rich text, or plain text turned into lists/paragraphs. */
    public static function render(?string $value): HtmlString
    {
        return new HtmlString((string) self::sanitize(self::toHtml($value)));
    }

    /** Plain text for previews, search snippets and table cells. */
    public static function plain(?string $value, int $limit = 0): string
    {
        $text = Str::squish(html_entity_decode(strip_tags(str_replace(['</p>', '</li>', '<br>', '<br/>', '<br />'], ' ', (string) self::toHtml($value))), ENT_QUOTES | ENT_HTML5));

        return $limit > 0 ? Str::limit($text, $limit) : $text;
    }

    /** Whether $value already is HTML (as TinyMCE stores it). */
    public static function isHtml(?string $value): bool
    {
        return (bool) preg_match('/<\s*(p|ul|ol|li|br|div|h[1-6]|table|strong|em|a|span|blockquote)\b/i', (string) $value);
    }

    /**
     * Older values as HTML: a JSON list becomes <ul>, "1. a 2. b" or
     * numbered lines become <ol>, other lines become paragraphs.
     */
    public static function toHtml(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || self::isHtml($value)) {
            return $value === '' ? null : $value;
        }

        $list = json_decode($value, true);
        if (is_array($list) && array_is_list($list) && $list !== []) {
            return '<ul>' . collect($list)->map(fn ($item) => '<li>' . e(is_array($item) ? implode(' ', $item) : (string) $item) . '</li>')->implode('') . '</ul>';
        }

        $numbered = preg_split('/(?:^|\s+)\d{1,2}[.)]\s+/u', $value, -1, PREG_SPLIT_NO_EMPTY);
        if (preg_match('/^\s*1[.)]\s+/u', $value) && count($numbered) > 1) {
            return '<ol>' . collect($numbered)->map(fn ($item) => '<li>' . e(trim($item)) . '</li>')->implode('') . '</ol>';
        }

        return collect(preg_split('/\R{2,}/', $value))
            ->map(fn ($paragraph) => '<p>' . preg_replace('/\R/', '', nl2br(e(trim($paragraph)), false)) . '</p>')
            ->implode('');
    }

    /**
     * CSS properties TinyMCE writes for alignment, indentation, colours and
     * image/table sizing, each with the values allowed. Anything else in a
     * style attribute (url(), expression(), positioning…) is dropped.
     */
    private const STYLES = [
        'text-align' => '/^(left|right|center|justify)$/',
        'padding-left' => '/^\d{1,3}(\.\d+)?(px|em|rem)$/',
        'color' => '/^(#[0-9a-f]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\))$/i',
        'background-color' => '/^(#[0-9a-f]{3,8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*(0|1|0?\.\d+)\s*)?\))$/i',
        'text-decoration' => '/^(underline|line-through|none)$/',
        'width' => '/^\d{1,4}(\.\d+)?(px|%)$/',
        'height' => '/^\d{1,4}(\.\d+)?(px|%)$/',
        'float' => '/^(left|right|none)$/',
        'display' => '/^(block|inline-block)$/',
        'margin-left' => '/^(auto|0|\d{1,3}(px|em))$/',
        'margin-right' => '/^(auto|0|\d{1,3}(px|em))$/',
        'border-collapse' => '/^collapse$/',
        'vertical-align' => '/^(top|middle|bottom)$/',
        'list-style-type' => '/^(disc|circle|square|decimal|lower-alpha|upper-alpha|lower-roman|upper-roman)$/',
    ];

    /** Keep only STYLES properties with allowed values in every style attribute. */
    private static function filterStyles(string $html): string
    {
        if ($html === '' || ! str_contains($html, 'style=')) {
            return $html;
        }

        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="rich-text-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        foreach ((new \DOMXPath($dom))->query('//*[@style]') as $element) {
            $kept = collect(explode(';', $element->getAttribute('style')))
                ->map(fn (string $rule) => array_map('trim', explode(':', $rule, 2)))
                ->filter(fn (array $rule) => count($rule) === 2
                    && isset(self::STYLES[strtolower($rule[0])])
                    && preg_match(self::STYLES[strtolower($rule[0])], $rule[1]))
                ->map(fn (array $rule) => strtolower($rule[0]) . ': ' . $rule[1])
                ->implode('; ');

            $kept === '' ? $element->removeAttribute('style') : $element->setAttribute('style', $kept . ';');
        }

        $root = $dom->getElementById('rich-text-root');
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        static $sanitizer;

        return $sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowAttribute('style', ['p', 'span', 'div', 'td', 'th', 'tr', 'table', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'ul', 'ol', 'blockquote'])
                ->allowAttribute('class', '*')
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
                ->allowMediaSchemes(['https', 'http'])
                ->allowRelativeMedias()
                ->withMaxInputLength(200000)
        );
    }
}
