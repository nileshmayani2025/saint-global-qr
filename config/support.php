<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Customer support contacts
    |--------------------------------------------------------------------------
    | Rendered as floating call / WhatsApp buttons at the bottom of every page
    | in both the admin panel and the consumer app. Leave a value blank and its
    | button simply is not rendered.
    |
    | Numbers may be written however you like ("+91 98765 43210"); the tel: and
    | wa.me links strip the formatting themselves.
    */

    'helpline' => env('SUPPORT_HELPLINE'),

    'whatsapp' => env('SUPPORT_WHATSAPP'),

    // Pre-filled text for the WhatsApp chat.
    'whatsapp_message' => env('SUPPORT_WHATSAPP_MESSAGE', 'Hello, I need help with Saint Globle products.'),

];
