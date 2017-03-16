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

use Gears\String\Str;
use Zend\Diactoros\Stream;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;

return function(IServerRequest $request, IResponse $response, callable $next, $config)
{
    /** @var IResponse $response */
    $response = $next($request, $response);

    if ($config->hosting->env === "local")
    {
        // Read the response body that has been generated thus far.
        $originalBody = $response->getBody(); $originalBody->rewind();
        $originalBodyContent = $originalBody->getContents();

        // Inject our live reload script into the head of the document.
        $modifiedBodyContent = Str::s($originalBodyContent)->replace
        (
            '<head>',
            '<head><script src="http://localhost:35729/livereload.js"></script>'
        );

        // Write our new response.
        $newBody = new Stream('php://temp', 'wb+');
        $newBody->write((string)$modifiedBodyContent);
        $response = $response->withBody($newBody);
    }

    return $response;
};
