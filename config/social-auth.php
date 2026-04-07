<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Social Authentication Providers
    |--------------------------------------------------------------------------
    |
    | This file stores provider enable/disable flags and UI metadata for
    | social authentication. Credentials remain in config/services.php.
    |
    */

    'providers' => [
        'google' => [
            'enabled' => env('SOCIAL_GOOGLE_ENABLED', false),
            'name' => 'Google',
            'button_text' => 'Continue with Google',
        ],
    ],

];
