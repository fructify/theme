<?php
////////////////////////////////////////////////////////////////////////////////
//             ___________                     __   __  _____
//             \_   _____/______ __ __   _____/  |_|__|/ ____\__ __
//              |    __) \_  __ \  |  \_/ ___\   __\  \   __<   |  |
//              |     \   |  | \/  |  /\  \___|  | |  ||  |  \___  |
//              \___  /   |__|  |____/  \___  >__| |__||__|  / ____|
//                  \/                      \/               \/
// -----------------------------------------------------------------------------
//                          https://github.com/fructify
//
//          Designed and Developed by Brad Jones <brad @="bjc.id.au" />
// -----------------------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////

use Doctrine\Common\Cache\FilesystemCache;

return
[
    // Hosting Environment
    // -------------------------------------------------------------------------
    // Add the current detected environment into the container.
    // NOTE: ```FRUCTIFY_ENV``` is defined in your ```wp-config.php``` file.
    // -------------------------------------------------------------------------
    'hosting' =>
    [
        'env' => FRUCTIFY_ENV
    ],

    // Session Settings
    // -------------------------------------------------------------------------
    // These defaults should work for 99% of cases.
    // Obviously the child theme can override these settings easily.
    //
    // The session name is used by ```session_name```.
    // http://php.net/manual/en/function.session-name.php
    //
    // It is also used to define the name of Aura.Session segment.
    //
    // Refer to the PHP documenation for the cookie settings.
    // http://php.net/manual/en/function.session-set-cookie-params.php
    // -------------------------------------------------------------------------
    'session' =>
    [
        'name' => 'FRUCTIFY',
        'cookie' =>
        [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => false,
            'httponly' => true
        ]
    ],

    // Database Connection Details
    // -------------------------------------------------------------------------
    // Add the database connection details into the container,
    // just in case we want to connect using something other than wpdb.
    // -------------------------------------------------------------------------
    'db' =>
    [
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASSWORD,
        'host' => DB_HOST,
        'charset' => DB_CHARSET,
        'collate' => DB_COLLATE
    ],

    // Common File Paths
    // -------------------------------------------------------------------------
    // Here we define some commonly used file paths.
    //
    // NOTE: When running this theme directly, without a child theme,
    //       the parent and child file paths will resolve to the same locations.
    //       Many of the services determin if the theme is being run directly or
    //       via a child theme simply by comparing these paths.
    // -------------------------------------------------------------------------
    'paths' =>
    [
        'root' => ABSPATH,
        'uploads' => ABSPATH.'/wp-content/uploads',
        'cache' => ABSPATH.'/wp-content/uploads/cache',
        'theme' =>
        [
            'parent' =>
            [
                'root' => __DIR__,
                'hooks' => __DIR__.'/hooks',
                'views' => __DIR__.'/views',
                'routes' => __DIR__.'/routes',
                'assets' => __DIR__.'/assets/dist',
                'middleware' => __DIR__.'/middleware'
            ],
            'child' =>
            [
                'root' => get_stylesheet_directory(),
                'hooks' => get_stylesheet_directory().'/hooks',
                'views' => get_stylesheet_directory().'/views',
                'routes' => get_stylesheet_directory().'/routes',
                'assets' => get_stylesheet_directory().'/assets/dist',
                'middleware' => get_stylesheet_directory().'/middleware'
            ]
        ]
    ],

    // Common URL Paths
    // -------------------------------------------------------------------------
    // Here we define some commonly used URL paths.
    // -------------------------------------------------------------------------
    'urls' =>
    [
        'root' => get_site_url(),
        'uploads' => wp_upload_dir()['baseurl'],
        'cache' => wp_upload_dir()['baseurl'].'/cache',
        'theme' =>
        [
            'parent' =>
            [
                'root' => get_template_directory_uri(),
                'assets' => get_template_directory_uri().'/assets/dist'
            ],
            'child' =>
            [
                'root' => get_stylesheet_directory_uri(),
                'assets' => get_stylesheet_directory_uri().'/assets/dist'
            ]
        ]
    ],

    // Not Found View
    // -------------------------------------------------------------------------
    // When a ```League\Route\Http\Exception\NotFoundException``` is encounted
    // in the dispatch method of the Router, this is the view that will be
    // rendered instead of letting the Exception go unhandled.
    // -------------------------------------------------------------------------
    'notFound' => 'errors/404',

    // Friendly Error View
    // -------------------------------------------------------------------------
    // While running on a production environment, when any exception or
    // catchable error is encounted after the ErrorHandler middleware has been
    // registered in the stack, this is the view that will be rendered by the
    // ErrorHandler. On staging and development environments filp/whoops will
    // output a detailed exception report.
    // -------------------------------------------------------------------------
    'friendlyError' => 'errors/500',

    // Container Cache Driver
    // -------------------------------------------------------------------------
    // When running in a staging or production environment we will cache the
    // IoC Container. By default we use a file based strategy, a child theme
    // may override this and use a more performant driver.
    //
    // see: http://php-di.org/doc/performances.html
    // -------------------------------------------------------------------------
    'cache' =>
    [
        'container' =>
        [
            'driver' => function($config)
            {
                return new FilesystemCache($config->paths->cache.'/di');
            }
        ]
    ]
];
