<?php declare(strict_types=1);
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

/**
 * Registers Corcel / Laravel Eloquent ORM.
 *
 * Lets face it, the default WordPress data layer sucks, hey just like the
 * rest of WordPress. Instead we will use Corcel which integrates Laravel's,
 * Eloquent ORM with WordPress.
 *
 * @see https://github.com/corcel/corcel
 */
return function($config)
{
    add_action('after_setup_theme', function() use ($config)
    {
        Corcel\Database::connect
        ([
            'database'  => $config->db->name,
            'username'  => $config->db->user,
            'password'  => $config->db->pass,
            'prefix'    => $config->db->prefix,
            'charset'   => $config->db->charset,
            'collation' => $config->db->collate
        ]);
    });
};
