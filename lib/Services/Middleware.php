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

use Relay\Runner;
use YaLinqo\Enumerable as Linq;
use Fructify\Contracts\IKernel;
use Fructify\Contracts\IMiddleware;
use Interop\Container\ContainerInterface as IContainer;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;
use Zend\Diactoros\Response\EmitterInterface as IEmitter;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface as IFinderFactory;

/**
 * This class basically wraps around the "relay/relay" php package.
 *
 * @see: http://relayphp.com/
 */
class Middleware implements IMiddleware
{
    /**
     * @Inject
     * @var IContainer
     */
    private $container;

    /**
     * @Inject
     * @var IKernel
     */
    private $kernel;

    /**
     * @Inject
     * @var IServerRequest
     */
    private $request;

    /**
     * @Inject
     * @var IResponse
     */
    private $response;

    /**
     * @Inject
     * @var IEmitter
     */
    private $emitter;

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
    public function dispatch()
    {
        $runner = new Runner($this->buildQueue(), function($file)
        {
            return $this->resolve($file);
        });

        $output = $runner($this->request, $this->response);

        $this->emitter->emit($output);

        exit;
    }

    /**
     * Creates an array of file paths that contain middleware.
     *
     * The middleware files are hierarchical, allowing the child theme to
     * override any middleware that we provide by default. The array is also
     * sorted by filename. So the child theme can "insert" it's middleware
     * in the desired order.
     *
     * @return array
     */
    private function buildQueue()
    {
        $queue = [];

        // Where is our middleware located?
        $parentMiddleware = $this->config->paths->theme->parent->middleware;
        $childMiddleware = $this->config->paths->theme->child->middleware;

        // Loop through our middleware files
        foreach ($this->finder->createFinder()->files()->name('*.php')->in($parentMiddleware) as $file)
        {
            if ($this->kernel->childHasMiddleware())
            {
                // Only register one of our middleware files if the
                // child theme does not have the same middleware file.
                if (!file_exists(str_replace($parentMiddleware, $childMiddleware, $file)))
                {
                    $queue[] = $file;
                }
            }
            else
            {
                $queue[] = $file;
            }
        }

        // Now lets loop through the child theme middleware files.
        if ($this->kernel->childHasMiddleware())
        {
            foreach ($this->finder->createFinder()->files()->name('*.php')->in($childMiddleware) as $file)
            {
                $queue[] = $file;
            }
        }

        // Ensure the middleware queue is sorted.
        return Linq::from($queue)->orderBy('$v')->toArray();
    }

    /**
     * Used by Relay\Runner when it dispatches the middleware stack.
     *
     * @param  string $file
     * @return Closure
     */
    private function resolve($file)
    {
        return function(IServerRequest $request, IResponse $response, callable $next) use ($file)
        {
            return $this->container->call(import($file),
            [
                'request' => $request,
                'response' => $response,
                'next' => $next,
                'config' => $this->config
            ]);
        };
    }
}
