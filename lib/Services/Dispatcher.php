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

use League\Route\Dispatcher as LeagueDispatcher;

class Dispatcher extends LeagueDispatcher
{
    public function dispatch($httpMethod, $uri)
    {
        // Match variable routes first, yes maybe this makes the
        // Fast Router no so fast anymore but it makes it so much
        // more extendable.
        $varRouteData = $this->variableRouteData;
        if (isset($varRouteData[$httpMethod]))
        {
            $result = $this->dispatchVariableRoute($varRouteData[$httpMethod], $uri);
            if ($result[0] === self::FOUND) return $result;
        }
        else if ($httpMethod === 'HEAD' && isset($varRouteData['GET']))
        {
            $result = $this->dispatchVariableRoute($varRouteData['GET'], $uri);
            if ($result[0] === self::FOUND) return $result;
        }

        // Now match the static routes. In our particular use case,
        // 99.9% of the time these routes will be default routes added by
        // ```000-default-route.GET.php```
        if (isset($this->staticRouteMap[$httpMethod][$uri]))
        {
            $handler = $this->staticRouteMap[$httpMethod][$uri];
            return [self::FOUND, $handler, []];
        }
        else if ($httpMethod === 'HEAD' && isset($this->staticRouteMap['GET'][$uri]))
        {
            $handler = $this->staticRouteMap['GET'][$uri];
            return [self::FOUND, $handler, []];
        }

        // Find allowed methods for this URI by matching
        // against all other HTTP methods as well.
        $allowedMethods = [];

        foreach ($this->staticRouteMap as $method => $uriMap)
        {
            if ($method !== $httpMethod && isset($uriMap[$uri]))
            {
                $allowedMethods[] = $method;
            }
        }

        foreach ($varRouteData as $method => $routeData)
        {
            if ($method === $httpMethod) continue;
            $result = $this->dispatchVariableRoute($routeData, $uri);
            if ($result[0] === self::FOUND) $allowedMethods[] = $method;
        }

        // If there are no allowed methods the route simply does not exist
        if ($allowedMethods)
        {
            return [self::METHOD_NOT_ALLOWED, $allowedMethods];
        }
        else
        {
            return [self::NOT_FOUND];
        }
    }
}
