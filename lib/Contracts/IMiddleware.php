<?php namespace Fructify\Contracts;
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

interface IMiddleware
{
    /**
     * Runs the underlying middleware dispatcher.
     *
     * This method will dispatch the request to the middleware stack.
     * Once a response has been sent to the browser this method will
     * "exit" the PHP process to ensure no further erroneous content
     * gets sent.
     *
     * __THERE IS NO POINT ADDING CODE AFTER CALLING THIS METHOD!__
     *
     * @return void
     */
    public function dispatch();
}
