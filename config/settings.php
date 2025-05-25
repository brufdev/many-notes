<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    |
    | This option defines if the registration feature is enabled or not.
    |
    */

    'registration' => [
        'enabled' => env('SETTINGS_REGISTRATION_ENABLED', true),
    ],

];
