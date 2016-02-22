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
use Interop\Container\ContainerInterface as IContainer;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface as IFinderFactory;

class Kernel implements IKernel
{
    /**
     * @Inject
     * @var IContainer
     */
    private $container;

    /**
     * @Inject
     * @var IFinderFactory
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

        // Loop through our hook files
        foreach ($this->finder->createFinder()->files()->name('*.php')->in($parentHooks) as $file)
        {
            if ($this->childHasHooks())
            {
                // Only register one of our hooks if the child theme
                // does not have the same hook file.
                if (!file_exists(str_replace($parentHooks, $childHooks, $file)))
                {
                    $this->registerHook($file);
                }
            }
            else
            {
                $this->registerHook($file);
            }
        }

        // Now lets loop through the child theme hooks
        if ($this->childHasHooks())
        {
            foreach ($this->finder->createFinder()->files()->name('*.php')->in($childHooks) as $file)
            {
                $this->registerHook($file);
            }
        }
    }

    /** @inheritdoc */
    public function hasChildTheme()
    {
        return
        (
            $this->config->paths->theme->parent->root
            !=
            $this->config->paths->theme->child->root
        );
    }

    /** @inheritdoc */
    public function childHasHooks()
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->hooks);
    }

    /** @inheritdoc */
    public function childHasRoutes()
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->routes);
    }

    /** @inheritdoc */
    public function childHasViews()
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->views);
    }

    /** @inheritdoc */
    public function childHasMiddleware()
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->middleware);
    }

    /**
     * Registers/Runs a Hook File.
     *
     * @param  string $file The filepath to where the hook exists.
     * @return void
     */
    private function registerHook($file)
    {
        // Create a closure that will include the hook file.
        $closure = function() use ($file) { return require($file); };

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
                // Hooks may also depend on the container config
                'config' => $this->config
            ]);
        }
    }
}
