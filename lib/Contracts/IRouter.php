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

namespace Fructify\Contracts;

use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;

interface IRouter
{
    /**
     * Runs the underlying router.
     *
     * This method will dispatch the request to a route handler,
     * it will then return a response object.
     *
     * 404 Exceptions will be caught and dealt with appropriately.
     *
     * @param  IServerRequest $request  The incomming HTTP Request.
     *
     * @param  IResponse      $response The HTTP Response instannce.
     *
     * @return IResponse                A new HTTP Response instannce,
     *                                  with content added from router.
     */
    public function dispatch(IServerRequest $request, IResponse $response): IResponse;
}
