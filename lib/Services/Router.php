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

namespace Fructify\Services;

use stdClass;
use DI\Annotation\Inject;
use YaLinqo\Enumerable as Linq;
use Fructify\Contracts\IRouter;
use Foil\Contracts\EngineInterface as IView;
use Interop\Container\ContainerInterface as IContainer;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;
use League\Route\RouteCollectionInterface as IRouteCollection;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface as IFinderFactory;

class Router implements IRouter
{
    /**
     * @Inject
     * @var IContainer
     */
    private $container;

    /**
     * @Inject
     * @var IRouteCollection
     */
    private $routes;

    /**
     * @Inject
     * @var IView
     */
    private $view;

    /**
     * @Inject
     * @var IFinderFactory
     */
    private $finder;

    /**
     * @Inject("config")
     * @var stdClass
     */
    private $config;

    public function dispatch(IServerRequest $request, IResponse $response): IResponse
    {
        $this->discoverRoutes();

        return $this->routes->dispatch($request, $response);
    }

    /**
     * Finds all theme route files and adds the routes to the RouteCollection.
     *
     * @return void
     */
    private function discoverRoutes()
    {
        // Where are our routes located?
        $parentRoutes = $this->config->paths->theme->parent->routes;
        $childRoutes = $this->config->paths->theme->child->routes;
        $files = $this->finder->createFinder()->files()->name('*.php')->in($parentRoutes);
        if ($parentRoutes != $childRoutes && is_dir($childRoutes))
        {
            $files = $files->in($childRoutes);
        }

        // Collect all the files
        $splFiles = []; foreach ($files as $file) $splFiles[] = $file;

        // Now we need to sort them ourselves because it seems Finder's
        // sortByName(), only sorts per in('dir') and not across the
        // entire list.
        $orderedFiles = Linq::from($splFiles)->orderBy('$v', function($a, $b)
        {
            return strnatcasecmp
            (
                $a->getBasename('.php'),
                $b->getBasename('.php')
            );
        });

        // Finally lets add some routes.
        foreach ($orderedFiles as $file)
        {
            $route = import($file->getRealPath(), ['route' => $this->routes]);

            if (is_callable($route))
            {
                $this->container->call($route, ['config' => $this->config]);
            }
        }
    }
}
