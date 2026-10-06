<?php

namespace App\Filament\Forms\Components;

use App\Support\RichText;
use Closure;
use Filament\Forms\Components\Field;

/**
 * Rich text field on TinyMCE Cloud, used by every content field of the panel
 * (layanan, pengumuman, FAQ) so they all edit the same way. The HTML is
 * sanitised on save; older plain-text values are opened as lists/paragraphs.
 */
class TinyEditor extends Field
{
    protected string $view = 'filament.forms.components.tiny-editor';

    /** 'full' (headings, colours, tables, images…) or 'simple' (text, lists, links). */
    protected string | Closure $profile = 'full';

    protected int | Closure $height = 360;

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (TinyEditor $component, ?string $state): void {
            $component->state(RichText::toHtml($state));
        });

        $this->dehydrateStateUsing(fn (?string $state) => RichText::sanitize($state));
    }

    public function profile(string | Closure $profile): static
    {
        $this->profile = $profile;

        return $this;
    }

    public function simple(): static
    {
        return $this->profile('simple');
    }

    public function height(int | Closure $height): static
    {
        $this->height = $height;

        return $this;
    }

    public function getProfile(): string
    {
        return $this->evaluate($this->profile);
    }

    /** Script URL of TinyMCE Cloud (null when no API key is configured). */
    public static function scriptUrl(): ?string
    {
        $key = config('tinymce.api_key');

        return filled($key) ? 'https://cdn.tiny.cloud/1/' . rawurlencode($key) . '/tinymce/' . config('tinymce.version', '7') . '/tinymce.min.js' : null;
    }

    /** Options handed to tinymce.init() by resources/js/tiny-editor.js. */
    public function getEditorConfig(): array
    {
        $full = $this->getProfile() === 'full';

        return [
            'script' => self::scriptUrl(),
            'uploadUrl' => $full ? route('editor.upload') : null,
            'disabled' => $this->isDisabled(),
            'options' => array_filter([
                'height' => $this->evaluate($this->height),
                'menubar' => $full ? 'edit view insert format table' : false,
                'plugins' => $full
                    ? 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime table wordcount help'
                    : 'autolink lists link wordcount',
                'toolbar' => $full
                    ? 'undo redo | blocks | bold italic underline strikethrough | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image table | removeformat code fullscreen'
                    : 'undo redo | bold italic underline | bullist numlist | link | removeformat',
                'block_formats' => 'Paragraf=p; Judul 2=h2; Judul 3=h3; Judul 4=h4; Kutipan=blockquote',
                'language' => config('tinymce.language'),
                'branding' => false,
                'promotion' => false,
                'statusbar' => true,
                'resize' => true,
                'link_default_target' => '_blank',
                'link_assume_external_targets' => 'https',
                'table_default_attributes' => ['border' => '1'],
                'paste_data_images' => $full,
                'image_caption' => false,
                'convert_urls' => false,
                'entity_encoding' => 'raw',
            ], fn ($value) => $value !== null),
        ];
    }
}
