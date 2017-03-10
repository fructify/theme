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
 * Remove Junk from Wordpress Head.
 *
 * Wordpress has this ability to add all this extra bullshit into your
 * ```<head></head>``` that you really just don't care for in most cases.
 * This hook removes it and cleans up our head section.
 */
add_action('init', function()
{
    remove_action('wp_head', 'rsd_link');                                       // remove really simple discovery link
    remove_action('wp_head', 'wp_generator');                                   // remove wordpress version
    remove_action('wp_head', 'feed_links', 2);                                  // remove rss feed links (make sure you add them in yourself if youre using feedblitz or an rss service)
    remove_action('wp_head', 'feed_links_extra', 3);                            // removes all extra rss feed links
    remove_action('wp_head', 'index_rel_link');                                 // remove link to index page
    remove_action('wp_head', 'wlwmanifest_link');                               // remove wlwmanifest.xml (needed to support windows live writer)
    remove_action('wp_head', 'start_post_rel_link', 10, 0);                     // remove random post link
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);                    // remove parent post link
    remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);                 // remove the next and previous post links
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);         // remove the next and previous post links
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);                    // remove shortlink
});
