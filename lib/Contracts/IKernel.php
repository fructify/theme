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

interface IKernel
{
    /**
     * Once the IoC Container has been built, this is the only service that
     * get's manually resolved. This is basically the root of our theme logic.
     *
     * @return void
     */
    public function boot();

    /**
     * If the kernel in running via a child theme then this will return true.
     *
     * @return bool
     */
    public function hasChildTheme(): bool;

    /**
     * If the child theme has a hooks folder this will return true.
     *
     * @return bool
     */
    public function childHasHooks(): bool;

    /**
     * If the child theme has a routes folder this will return true.
     *
     * @return bool
     */
    public function childHasRoutes(): bool;

    /**
     * If the child theme has a views folder this will return true.
     *
     * @return bool
     */
    public function childHasViews(): bool;

    /**
     * If the child theme has a middleware folder this will return true.
     *
     * @return bool
     */
    public function childHasMiddleware(): bool;
}
