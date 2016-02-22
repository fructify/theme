<?php namespace Fructify\Services;
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

use League\Route\Http\Exception\NotFoundException;

use Fructify\Contracts\IRouter;
use Foil\Contracts\EngineInterface as IView;
use Interop\Container\ContainerInterface as IContainer;
use Zend\Diactoros\Response\EmitterInterface as IEmitter;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;
use League\Route\RouteCollectionInterface as IRouteCollection;
use Dflydev\Symfony\FinderFactoryInterface as IFinderFactory;

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
     * @var IEmitter
     */
    private $emitter;

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
     * @Inject
     * @var IServerRequest
     */
    private $request;

    /**
     * @Inject
     * @var IResponse
     */
    private $response;

    /**
     * @Inject("config")
     * @var StdClass
     */
    private $config;

    /** @inheritdoc */
    public function dispatch()
    {
        $this->discoverRoutes();

        try
        {
            $output = $this->routes->dispatch($this->request, $this->response);
        }
        catch (NotFoundException $e)
        {
            $output = $this->buildNotFoundResponse();
        }

        $this->emitter->emit($output);

        exit;
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
        $files = $this->finder->create()->files()->name('*.php')->in($parentRoutes);
        if ($parentRoutes != $childRoutes && is_dir($childRoutes))
        {
            $files = $files->in($childRoutes);
        }

        foreach ($files->sortByName() as $file)
        {
            // Create a closure that will include the route file.
            $closure = function($route) use ($file) { return include($file); };

            // Unbind the closure from this class.
            // ie: Make it so in the included file ```$this``` is undefined.
            $unBoundClosure = $closure->bindTo(null);

            // Call the closure and pass in the RouteCollection as a parameter.
            $routeClosure = call_user_func($unBoundClosure, $this->routes);

            if ($routeClosure instanceof \Closure)
            {
                $this->container->call($routeClosure,
                [
                    'route' => $this->routes,
                    'config' => $this->config
                ]);
            }
        }
    }

    /**
     * Builds a 404 Not Found Response.
     *
     * @return IResponse
     */
    private function buildNotFoundResponse()
    {
        $notFoundResponse = $this->response->withStatus(404, "Not Found");

        $notFoundResponse->getBody()->write
        (
            $this->view->render
            (
                $this->config->notFound
            )
        );

        return $notFoundResponse;
    }
}
