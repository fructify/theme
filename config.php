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
        'cache' => ABSPATH.'/wp-content/uploads/di-cache',
        'theme' =>
        [
            'parent' =>
            [
                'root' => __DIR__,
                'hooks' => __DIR__.'/hooks',
                'views' => __DIR__.'/views',
                'routes' => __DIR__.'/routes'
            ],
            'child' =>
            [
                'root' => get_stylesheet_directory(),
                'hooks' => get_stylesheet_directory().'/hooks',
                'views' => get_stylesheet_directory().'/views',
                'routes' => get_stylesheet_directory().'/routes'
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
                return new FilesystemCache($config->paths->cache);
            }
        ]
    ]
];
