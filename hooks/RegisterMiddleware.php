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

use Fructify\Contracts\IMiddleware;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;

return function(IServerRequest $request, IMiddleware $middleware)
{
    add_action('wp_loaded', function() use ($request, $middleware)
    {
        // We only want to run our middleware stack for requests that get
        // funneled through index.php by the .htaccess rewrite rules.
        // wp-admin, wp-cron, wp-login, xmlrpc, etc should run as expected.
        if ($request->getServerParams()['SCRIPT_NAME'] == '/index.php')
        {
            $middleware->dispatch();
        }
    });
};
