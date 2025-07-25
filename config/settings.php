<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Local Authentication
    |--------------------------------------------------------------------------
    |
    | This option defines if the local authentication feature is enabled or not.
    |
    */

    'local_auth' => [
        'enabled' => env('SETTINGS_LOCAL_AUTH_ENABLED', true),
    ],

];
