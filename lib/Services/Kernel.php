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

use Fructify\Contracts\IKernel;
use Fructify\Contracts\IRouter;
use Symfony\Component\Finder\Finder;
use Interop\Container\ContainerInterface as IContainer;

class Kernel implements IKernel
{
    /**
     * @Inject
     * @var IContainer
     */
    private $container;

    /**
     * @Inject
     * @var Finder
     */
    private $finder;

    /**
     * @Inject("config")
     * @var StdClass
     */
    private $config;

    /** @inheritdoc */
    public function boot()
    {
        // Where are our hooks located?
        $parentHooks = $this->config->paths->theme->parent->hooks;
        $childHooks = $this->config->paths->theme->child->hooks;
        $files = $this->finder->files()->name('*.php')->in($parentHooks);
        if ($parentHooks != $childHooks && is_dir($childHooks))
        {
            $files = $files->in($childHooks);
        }

        // Loop through the hook files
        foreach ($files as $file)
        {
            // Create a closure that will include the hook file.
            $closure = function() use ($file) { return include($file); };

            // Unbind the closure from this class.
            // ie: Make it so in the included file ```$this``` is undefined.
            $unBoundClosure = $closure->bindTo(null);

            // Call the closure, it can return the actual hook closure.
            // This is so hooks may have dependecies injected into them.
            // Sometimes though, hooks are super simple and don't require
            // any dependecies in which case they do not need to return a
            // closure and can add their hooks directly.
            $hookClosure = call_user_func($unBoundClosure);

            // Now use the container to call the hook closure.
            // php-di will inject dependecies as needed.
            if ($hookClosure instanceof \Closure)
            {
                $this->container->call($hookClosure,
                [
                    'config' => $this->config
                ]);
            }
        }
    }
}
