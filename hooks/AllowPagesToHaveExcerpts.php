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

/**
 * Allows Pages to have Excerpts.
 *
 * Wordpress Pages by default do not get excerpt support,
 * this adds that support back.
 *
 * @see: http://www.wpbeginner.com/plugins/add-excerpts-to-your-pages-in-wordpress/
 */
add_action('init', function()
{
    add_post_type_support('page', 'excerpt');
});
