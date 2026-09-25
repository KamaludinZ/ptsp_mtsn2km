<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class Faq extends Model
{
    /**
     * FAQ answers are rich text written in the admin panel and shown on the
     * public "Tentang" page; strip scripts, event handlers and unsafe URLs.
     */
    public function safeAnswer(): string
    {
        static $sanitizer;

        $sanitizer ??= new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowSafeElements()
                ->allowLinkSchemes(['https', 'http', 'mailto', 'tel'])
                ->allowRelativeLinks()
        );

        return $sanitizer->sanitize((string) $this->answer);
    }
}
