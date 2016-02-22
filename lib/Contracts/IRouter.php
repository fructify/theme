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

interface IRouter
{
    /**
     * Runs the underlying router.
     *
     * This method will dispatch the request to a route handler,
     * it will then send the response to the browser.
     *
     * 404 Exceptions will be caught and dealt with appropriately.
     *
     * Finally once a response has been sent to the browser the router will
     * "exit" the PHP process to ensure no further erroneous content gets added.
     *
     * __THERE IS NO POINT ADDING CODE AFTER CALLING THIS METHOD!__
     *
     * @return void
     */
    public function dispatch();
}
