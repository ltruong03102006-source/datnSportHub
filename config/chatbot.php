<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SportHub Chatbot
    |--------------------------------------------------------------------------
    |
    | These settings keep the first chatbot version simple and configurable.
    | The initial engine is rule-based; later stages can swap the responder
    | without changing controllers or views.
    |
    */

    'enabled' => env('CHATBOT_ENABLED', true),

    'name' => env('CHATBOT_NAME', 'SportHub Bot'),

    'mode' => env('CHATBOT_MODE', 'rules'),

    'max_user_message_length' => (int) env('CHATBOT_MAX_USER_MESSAGE_LENGTH', 1000),

    'conversation_idle_minutes' => (int) env('CHATBOT_CONVERSATION_IDLE_MINUTES', 120),

    'default_locale' => env('CHATBOT_DEFAULT_LOCALE', 'vi'),
];
