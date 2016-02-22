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

use Psr7Middlewares\Middleware\Whoops;
use Psr7Middlewares\Middleware\ErrorHandler;
use Foil\Contracts\EngineInterface as IView;
use League\Route\Http\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;

return function(IServerRequest $request, IResponse $response, callable $next, IView $view, $config)
{
    if ($config->hosting->env == "production")
    {
        $errorHandler = new ErrorHandler
        (
            function(IServerRequest $request, IResponse $response) use ($view, $config)
            {
                $exception = ErrorHandler::getException($request);

                if ($exception instanceof NotFoundException)
                {
                    $response = $response->withStatus(404, "Not Found");
                    $response->getBody()->write($view->render($config->notFound));
                }
                else
                {
                    $response = $response->withStatus(500, "Server Error");
                    $response->getBody()->write($view->render($config->friendlyError));

                    // TODO: The error should be logged, especially if on production.
                }

                return $response;
            }
        );

        $errorHandler->catchExceptions(true);
    }
    else
    {
        $errorHandler = new Whoops();
    }

    return $errorHandler($request, $response, $next);
};
