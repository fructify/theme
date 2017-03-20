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

use League\Route\Route;
use Psr\Http\Message\ServerRequestInterface;
use Fructify\Services\Dispatcher as FructifyDispatcher;
use League\Route\RouteCollection as LeagueRouteCollection;

class RouteCollection extends LeagueRouteCollection
{
    /**
     * @inheritDoc
     *
     * However we allow routes to be overwritten.
     */
    public function map($method, $path, $handler)
    {
        $path = sprintf('/%s', ltrim($path, '/'));

        $route = (new Route)->setMethods((array) $method)
        ->setPath($path)->setCallable($handler);

        $this->routes[$path] = $route;

        return $route;
    }

    /**
     * @inheritDoc
     *
     * However we need to provide our own custom dispatcher.
     */
    public function getDispatcher(ServerRequestInterface $request)
    {
        // NOTE: We don't need to worry about setting the
        // strategy the container does that for us.

        $this->prepRoutes($request);

        return (new FructifyDispatcher($this->getData()))
        ->setStrategy($this->getStrategy());
    }
}
