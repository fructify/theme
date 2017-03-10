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

use Foil\Contracts\EngineInterface as IView;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;
use League\Route\RouteCollectionInterface as IRouteCollection;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface as IFinderFactory;

/**
 * Default Route Generator.
 *
 * This will do 2 things:
 *
 * 	- First it loops through all view files in "views/pages".
 * 	  For each of these views it adds a route based on the views filename.
 * 	  This is so that static pages can easily be created without the need to
 * 	  create a wordpress page in wp-admin, nor do you have to create a custom
 * 	  route file that just returns a view.
 *
 * 	- Second it loops through each page in the wordpress database.
 * 	  It will then add a route for that pages's permalink.
 * 	  This theme completely ignores "posts".
 */
return function(IRouteCollection $route, IFinderFactory $finder, $config)
{
    /*
     * Add the default root route.
     * This can obviously be overwritten by a child theme.
     *
     * Or the child theme may just decide to provide their own version of
     * ```views/pages/index.php``` without creating their own index route.
     *
     * Or it could get overwritten below when we loop through the pages from
     * the wordpress database.
     */
    $route->get('/', function(IResponse $response, IView $view)
    {
        $response->getBody()->write($view->render('pages/index'));
        return $response;
    });

    // Get the location to our page views
    $parentPages = $config->paths->theme->parent->views.'/pages';
    $childPages = $config->paths->theme->child->views.'/pages';

    // Find all files in our pages folder.
    $files = $finder->createFinder()->files()->name('*.php')->in($parentPages);

    // If the we have a child theme and it has a pages folder,
    // lets find all files in that folder too.
    if ($parentPages != $childPages && is_dir($childPages))
    {
        $files = $files->in($childPages);
    }

    // Ignore the index view, this has already been added above.
    $files = $files->notName('index.php');

    foreach ($files as $file)
    {
        // Create a URI based on the views file path.
        $uri = '/'.str_replace('.php', '', $file->getRelativePathname());

        // Add a route that simply renders the view and returns the response.
        $route->get($uri, function(IResponse $response, IView $view) use ($uri)
        {
            $response->getBody()->write($view->render('pages'.$uri));
            return $response;
        });
    }

    // Now lets loop through the pages stored in the wordpress database.
    foreach (get_pages() as $post)
    {
        // Grab the URI from the permalink of the post object.
        $uri = rtrim(parse_url(get_permalink($post->ID), PHP_URL_PATH), '/');
        if ($uri == "") $uri = "/";

        $route->get($uri, function(IResponse $response, IView $view) use ($uri, $post)
        {
            // Globalise the wordpress post object.
            // This ensures functions such ```the_content()``` work as expected.
            query_posts('page_id='.$post->ID);
            setup_postdata($GLOBALS['post'] =& $post);

            // Even though we globalise the post object above.
            // We will also inject the post object into the view.
            // So that it might be accessed in a more modern fashion.
            $viewData = ['post' => $post];

            if ($uri == "/")
            {
                // In this case we will render the index view.
                $body = $view->render('pages/index', $viewData);
            }
            elseif (($template = $view->find('pages'.$uri)) !== false)
            {
                // In this case there is a view that matches this post object.
                $body = $view->render($template, $viewData);
            }
            else
            {
                // There is no custom page view for this post
                // object so we will just use the default view.
                $body = $view->render('default', $viewData);
            }

            // Write the view to the response & return
            $response->getBody()->write($body);
            return $response;
        });
    }
};
