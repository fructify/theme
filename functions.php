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

use DI\ContainerBuilder;
use Stringy\Stringy as s;
use Fructify\Contracts\IKernel;

// Install the import function globally.
// see: https://github.com/brad-jones/import/
Brads\Importer::globalise();

/**
 * The Theme IoC Container.
 *
 * Wrap everything up into a closure because the global scope is already pretty
 * crowded being a wordpress environment and all... :) Additonally it stops
 * anyone from cheating and say requesting the IoC Container by using something
 * like ```$GLOBALS['app']```.
 */
call_user_func(function()
{
    $builder = new ContainerBuilder();

    // We will enable @Inject annotation support. Between autowiring &
    // annotations I am hoping we won't need to have much in the way of
    // custom definitions in the ```container.php``` file.
    // http://php-di.org/doc/annotations.html
    $builder->useAnnotations(true);

    // Add our definitions from ```container.php```.
    $definitions = import(__DIR__.'/container.php');
    $builder->addDefinitions($definitions);

    // Grab the config object so we can use it to build the container.
    $config = $definitions['config']->__invoke();

    // Add definitions from a child theme that might exist.
    $parentThemePath = $config->paths->theme->parent->root;
    $childThemePath = $config->paths->theme->child->root;
    if ($parentThemePath != $childThemePath)
    {
        $childContainer = $childThemePath.'/container.php';

        if (file_exists($childContainer))
        {
            $builder->addDefinitions(import($childContainer));
        }
    }

    // If running on staging or production we will make
    // sure the container is cached for maximum performance.
    if (s::create($config->hosting->env)->containsAny(['staging','production']))
    {
        $builder->setDefinitionCache
        (
            $config->cache->container->driver->__invoke($config)
        );

        // NOTE: This would only be used in the case there are lazy injections.
        // see: http://php-di.org/doc/lazy-injection.html
        $builder->writeProxiesToFile(true, $config->paths->cache.'/proxies');
    }

    // Boot our theme kernel.
    $builder->build()->get(IKernel::class)->boot();
});
