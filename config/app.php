<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    */

    'name' => env('APP_NAME', 'Warehouse'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    */

    'url' => env('APP_URL', 'http://localhost'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    */

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    */

    'cipher' => 'AES-256-CBC',

    'key' => env('APP_KEY'),

    'previous_keys' => [
        ...array_filter(
            explode(',', env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | NOTE: This is a non-standard key for a config file.
    | It's left here as it was in the original file.
    |--------------------------------------------------------------------------
    */
    'widgets' => [
        'default' => [
            'info' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    */

    'maintenance' => [
        'driver' => env('MAINTENANCE_DRIVER', 'file'),
        // The 'store' option is only used for the 'cache' driver.
        'store' => 'database',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    | NOTE: These keys are not standard for this file, they are usually
    | configured in config/logging.php. They are refactored here as requested.
    |--------------------------------------------------------------------------
    */

    'log_channel' => env('LOG_CHANNEL', 'stack'),
    'log_stack' => env('LOG_STACK', 'single'), // Non-standard key
    'log_deprecations_channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
    'log_level' => env('LOG_LEVEL', 'debug'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    | NOTE: These keys are not standard for this file. They belong in
    | config/database.php. They are refactored here as requested.
    |--------------------------------------------------------------------------
    */

    'db_connection' => env('DB_CONNECTION', 'mysql'),
    'db_host' => env('DB_HOST', '127.0.0.1'),
    'db_port' => env('DB_PORT', '3306'),
    'db_database' => env('DB_DATABASE', 'laravel'),
    'db_username' => env('DB_USERNAME', 'root'),
    'db_password' => env('DB_PASSWORD', ''),
    'queue_connection' => env('QUEUE_CONNECTION', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    | NOTE: These keys are not standard for this file. They belong in
    | config/session.php. They are refactored here as requested.
    |--------------------------------------------------------------------------
    */

    'session_driver' => env('SESSION_DRIVER', 'file'),
    'session_lifetime' => env('SESSION_LIFETIME', 120),
    'session_encrypt' => (bool) env('SESSION_ENCRYPT', false),
    'session_path' => '/',
    'session_domain' => env('SESSION_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    | NOTE: These keys are not standard for this file. They belong in
    | config/cache.php. They are refactored here as requested.
    |--------------------------------------------------------------------------
    */

    'cache_store' => env('CACHE_DRIVER', 'file'),
    'cache_prefix' => env('CACHE_PREFIX', 'laravel_cache'),

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    | NOTE: These keys are not standard for this file. They belong in
    | config/database.php under the 'redis' connection.
    |--------------------------------------------------------------------------
    */

    'redis_client' => env('REDIS_CLIENT', 'phpredis'),
    'redis_host' => env('REDIS_HOST', '127.0.0.1'),
    'redis_password' => env('REDIS_PASSWORD', null),
    'redis_port' => env('REDIS_PORT', 6379),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configuration
    |--------------------------------------------------------------------------
    | NOTE: These keys are not standard for this file. They belong in
    | config/mail.php and config/services.php.
    |--------------------------------------------------------------------------
    */

    'resend_api_key' => env('RESEND_API_KEY'),
    'mail_mailer' => env('MAIL_MAILER', 'smtp'),
    'mail_from_address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
    'mail_from_name' => env('MAIL_FROM_NAME', '${APP_NAME}'),

    /*
    |--------------------------------------------------------------------------
    | Vite Configuration
    |--------------------------------------------------------------------------
    */
    'vite_app_name' => env('VITE_APP_NAME', 'Warehouse'),
];
