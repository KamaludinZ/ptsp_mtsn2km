<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp API Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for a WhatsApp API Gateway
    | service. You can use any provider you prefer.
    |
    */

    'api_url' => env('WHATSAPP_API_URL'),

    'api_token' => env('WHATSAPP_API_TOKEN'),

    'sender_id' => env('WHATSAPP_SENDER_ID'),
];
