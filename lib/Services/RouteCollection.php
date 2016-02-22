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

use League\Route\Route;
use League\Route\RouteCollection as LeagueRouteCollection;

class RouteCollection extends LeagueRouteCollection
{
    /**
     * {@inheritdoc}, however we allow routes to be overwritten.
     */
    public function map($method, $path, $handler)
    {
        $path = sprintf('/%s', ltrim($path, '/'));

        $route = (new Route)->setMethods((array) $method)
        ->setPath($path)->setCallable($handler);

        $this->routes[$path] = $route;

        return $route;
    }
}