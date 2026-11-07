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

    /*
    |--------------------------------------------------------------------------
    | Vault Import
    |--------------------------------------------------------------------------
    |
    | This option defines the maximum total size of a vault archive once
    | unzipped, using the same format as php.ini (for example 512M or 2G).
    |
    */

    'import' => [
        'max_unzipped_size' => env('SETTINGS_IMPORT_MAX_UNZIPPED_SIZE', '1G'),
    ],

];
