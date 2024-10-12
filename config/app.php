<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application, which will be used when the
    | framework needs to place the application's name in a notification or
    | other UI elements where an application name needs to be displayed.
    |
    */

    'name' => 'Warehouse',

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => true,

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | the application so that it's available within Artisan commands.
    |
    */

    'url' => '209.97.129.148:8089',

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. The timezone
    | is set to "UTC" by default as it is suitable for most use cases.
    |
    */

    'timezone' => 'UTC',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by Laravel's translation / localization methods. This option can be
    | set to any locale for which you plan to have translation strings.
    |
    */

    'locale' => 'en',

    'fallback_locale' => 'en',

    'faker_locale' => 'en_US',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is utilized by Laravel's encryption services and should be set
    | to a random, 32 character string to ensure that all encrypted values
    | are secure. You should do this prior to deploying the application.
    |
    */

    'cipher' => 'AES-256-CBC',

    'key' => 'base64:OS9J7O4DX5zMNUhASURJetb239q9dIDIKlmkfWSIB4k=',

    'previous_keys' => [
        ...array_filter(
            explode(',', '')
        ),
    ],

    'widgets' => [
        'default' => [
            'info' => false,
        ]
        ],
    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        'store' => 'database',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    */

    'log_channel' => 'stack',
    'log_stack' => 'single',
    'log_deprecations_channel' => 'null',
    'log_level' => 'debug',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    */

    'db_connection' => 'mysql',
    'db_host' => 'localhost',
    'db_port' => '33060',
    'db_database' => 'mysql',
    'db_username' => 'root',
    'db_password' => 'twalitso101',
    'queue_connection' => 'sync',
    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the session settings for your application. This
    | is used to store user session information, making it possible for a
    | user to remain logged in across multiple requests.
    |
    */

    'session_driver' => 'database',
    'session_lifetime' => 120,
    'session_encrypt' => false,
    'session_path' => '/',
    'session_domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the cache settings for your application.
    |
    */

    'cache_store' => 'database',
    'cache_prefix' => '',

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the Redis settings for your application.
    |
    */

    'redis_client' => 'phpredis',
    'redis_host' => '127.0.0.1',
    'redis_password' => null,
    'redis_port' => 6379,

    /*
    |--------------------------------------------------------------------------
    | Resend API Key
    |--------------------------------------------------------------------------
    |
    | The API key for the Resend mailer.
    |
    */

    'resend_api_key' => 're_73vhATde_EJ2qUzKzd6vV4XoHRL68AMyH',
    'mail_mailer' => 'resend',
    'mail_from_address' => 'onboarding@resend.dev',
    'mail_from_name' => 'Warehouse',

    'vite_app_name' => 'Warehouse',
];

