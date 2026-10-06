<?php

/*
 * TinyMCE Cloud, the rich text editor of every content field in the panel
 * (App\Filament\Forms\Components\TinyEditor). The API key belongs in .env;
 * each domain the panel is opened from must be approved in the Tiny account.
 */
return [
    'api_key' => env('TINYMCE_API_KEY'),

    'version' => env('TINYMCE_VERSION', '7'),

    'language' => env('TINYMCE_LANGUAGE', 'id'),

    // Images inserted in the editor: public disk, staff only, max size in KB.
    'uploads' => [
        'disk' => 'public',
        'directory' => 'editor-uploads',
        'max_kb' => 2048,
    ],
];
