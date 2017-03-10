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
 * Allows Pages to have Tags.
 *
 * Wordpress Pages by default do not get tag support,
 * this adds that support back.
 *
 * @credit: https://wordpress.org/plugins/tag-pages/
 */
add_action('init', function()
{
    /**
     * Add the 'post_tag' taxonomy, which is the name of the existing taxonomy
     * used for tags to the Post type page. Normally in WordPress Pages cannot
     * be tagged, but this let's WordPress treat Pages just like Posts
     * and enables the tags metabox so you can add tags to a Page.
     *
     * > NOTE: This uses the register_taxonomy_for_object_type() function
     * > which is only in WordPress 3 and higher!
     */
    add_action('admin_init', function()
    {
        register_taxonomy_for_object_type('post_tag', 'page');
    });

    /**
     * Display all post_types on the tags archive page. This forces WordPress to
     * show tagged Pages together with tagged Posts. Thanks to Page Tagger by
     * Ramesh Nair: http://wordpress.org/extend/plugins/page-tagger/
     */
    add_action('pre_get_posts', function(&$query)
    {
        if ($query->is_archive && $query->is_tag)
        {
            $q = &$query->query_vars;
            $q['post_type'] = 'any';
        }
    });
});
