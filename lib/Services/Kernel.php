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

namespace Fructify\Services;

use stdClass;
use DI\Annotation\Inject;
use Fructify\Contracts\IKernel;
use Symfony\Component\Finder\SplFileInfo;
use Interop\Container\ContainerInterface as IContainer;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface as IFinderFactory;

class Kernel implements IKernel
{
    /**
     * @Inject
     * @var IContainer
     */
    protected $container;

    /**
     * @Inject
     * @var IFinderFactory
     */
    protected $finder;

    /**
     * @Inject("config")
     * @var stdClass
     */
    protected $config;

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

    public function hasChildTheme(): bool
    {
        return
        (
            $this->config->paths->theme->parent->root
            !=
            $this->config->paths->theme->child->root
        );
    }

    public function childHasHooks(): bool
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->hooks);
    }

    public function childHasRoutes(): bool
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->routes);
    }

    public function childHasViews(): bool
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->views);
    }

    public function childHasMiddleware(): bool
    {
        if (!$this->hasChildTheme()) return false;

        return is_dir($this->config->paths->theme->child->middleware);
    }

    /**
     * Registers/Runs a Hook File.
     *
     * @param  SplFileInfo $file The filepath to where the hook exists.
     * @return void
     */
    protected function registerHook(SplFileInfo $file)
    {
        $hook = import($file->getRealPath());

        if (is_callable($hook))
        {
            $this->container->call($hook, ['config' => $this->config]);
        }
    }
}
