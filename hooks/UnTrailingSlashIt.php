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
 * Removes trailing slashes.
 *
 * Wordpress always likes to append a trailing slash to everything.
 * Having a trailing slash followed by nothing makes no logical sense.
 * It also causes issues for URL re-writing and other link concatination tasks.
 * Thus here we remove the trailing slashes from all permalinks.
 *
 * > NOTE: In the .htaccess file there are also rules to redirect to
 * > non-trailing slash versions of any urls requested.
 */
$linkFixer = function($link){ return untrailingslashit($link); };
add_filter('page_link', $linkFixer);
add_filter('post_type_link', $linkFixer);
