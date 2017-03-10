<?php
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

// Import concretions
use Foil\Foil;
use Fructify\Services;
use Aura\Session\Session;
use Aura\Session\SessionFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory as R;
use Zend\Diactoros\Response\SapiEmitter;
use League\Route\RouteCollection;
use League\Route\Strategy\ParamStrategy;
use Dflydev\Symfony\FinderFactory\FinderFactory;

// Import interfaces
use Fructify\Contracts;
use Aura\Session\SegmentInterface as ISession;
use Foil\Contracts\EngineInterface as IView;
use Fructify\Contracts\IMiddleware;
use Interop\Container\ContainerInterface as IContainer;
use Zend\Diactoros\Response\EmitterInterface as IEmitter;
use League\Route\Strategy\StrategyInterface as IStrategy;
use Dflydev\Symfony\FinderFactory\FinderFactoryInterface as IFinderFactory;
use League\Route\RouteCollectionInterface as IRouteCollection;
use Psr\Http\Message\ResponseInterface as IResponse;
use Psr\Http\Message\ServerRequestInterface as IServerRequest;

/**
 * IoC Container Definitions.
 *
 * We are using an inversion of control container & dependecy injection.
 *
 * @see http://php-di.org/doc/php-definitions.html
 */
return
[
    // Container Config
    // -------------------------------------------------------------------------
    // The theme has a config file as well. This is for PHP scalar values,
    // anything more should be defined directly in this file.
    //
    // The values of this object we will allow to be "Located".
    //
    // That is to say other services may depend directly on the structure of
    // this config object but the config object it's self should never be
    // "Located", it should always be injected.
    // -------------------------------------------------------------------------
    'config' => function()
    {
        $childConfig = []; $parentConfig = import(__DIR__.'/config.php');

        $parentThemePath = $parentConfig['paths']['theme']['parent']['root'];
        $childThemePath = $parentConfig['paths']['theme']['child']['root'];

        if ($parentThemePath != $childThemePath)
        {
            $childConfigPath = $childThemePath.'/config.php';

            if (file_exists($childConfigPath))
            {
                $childConfig = import($childConfigPath);
            }
        }

        $aToO = function($x) use (&$aToO)
        {
            if (is_array($x)) return (object)array_map($aToO, $x);
            else return $x;
        };

        return $aToO(array_merge_recursive($parentConfig, $childConfig));
    },

    // Bind the container to it's self.
    // -------------------------------------------------------------------------
    // This is so services such as the Kernel & Router may depend on the
    // container it's self. They use the "call" method to invoke hooks/routes.
    // -------------------------------------------------------------------------
    IContainer::class => DI\factory(function(IContainer $c){ return $c; }),

    // Bind Request Super Globals
    // -------------------------------------------------------------------------
    // This collects up all the $_SERVER, $_GET, $_POST, $_FILES & $_COOKIES
    // information into one Object that then provides a fluent & testable API.
    //
    // see: https://github.com/zendframework/zend-diactoros
    // -------------------------------------------------------------------------
    IServerRequest::class => DI\factory(function(){ return R::fromGlobals(); }),

    // Setup Aura Session
    // -------------------------------------------------------------------------
    // We are using the Aura Session package to provide a nice session api,
    // with all the modern things (ie: flash messages) we have come to expect
    // after using frameworks like Laravel.
    //
    // see: https://github.com/auraphp/Aura.Session
    //
    // NOTE: Aura Session does not provide any custom storage backend.
    // It simply wraps around PHP's provided $_SESSION super globals and
    // associated API. So if you want to store sessions in say a mysql database
    // you should setup your own SessionHandler.
    //
    // see: http://php.net/manual/en/class.sessionhandler.php
    // -------------------------------------------------------------------------
    Session::class => DI\factory(function(IContainer $c)
    {
        $session = (new SessionFactory)->newInstance
        (
            $c->get(IServerRequest::class)->getCookieParams()
        );

        $session->setName($c->get('config')->session->name);

        $session->setCookieParams((array)$c->get('config')->session->cookie);

        return $session;
    }),

    // Session Segment
    // -------------------------------------------------------------------------
    // Aura Session works with segments, basically nested arrays within the
    // $_SESSION super global. Aura Session also doesn't provide an interface
    // for the "Session Manager" but it does for a SessionSegment.
    //
    // So I have decided that we will setup a global segment, this will allow
    // hooks, middleware & routes to easily depend on the SegmentInterface.
    // I am yet to run into an issue with Session variable collisions.
    //
    // If you do need to create a new segment, or perform some other task, like
    // regenerating the session id, you can always depend on the concrete
    // Session class.
    // -------------------------------------------------------------------------
    ISession::class => DI\factory(function(IContainer $c)
    {
        return $c->get(Session::class)->getSegment
        (
            $c->get('config')->session->name
        );
    }),

    // Setup the League Router
    // -------------------------------------------------------------------------
    // Our router wraps around the league/route package.
    //
    // Couple of things to note:
    //
    //  - We are using v2 RC1 so the documentation at their site is out of date.
    //
    //  - We have also extended the main RouteCollection class to allow the same
    //    route path to be registered multiple times. Each time over writing the
    //    previous route. This allows us to provide "default" routes.
    //
    // see: http://route.thephpleague.com/
    // -------------------------------------------------------------------------
    IRouteCollection::class => DI\factory(function(IContainer $c)
    {
        return (new Services\RouteCollection($c))->setStrategy
        (
            (new ParamStrategy)->setContainer($c)
        );
    }),

    // Setup the Foil view engine
    // -------------------------------------------------------------------------
    // Foil brings all the flexibility and power of modern template engines to
    // native PHP templates. Write simple, clean and concise templates with
    // nothing more than PHP.
    //
    // see: http://www.foilphp.it/
    // -------------------------------------------------------------------------
    IView::class => DI\factory(function(IContainer $c)
    {
        $childViews = $c->get('config')->paths->theme->child->views;
        $parentViews = $c->get('config')->paths->theme->parent->views;
        if ($childViews == $parentViews)
        {
            $folders = [$parentViews];
        }
        else
        {
            $folders = [$childViews, $parentViews];
        }

        $config =
        [
            'folders' => $folders,
            'autoescape' => false
        ];

        $engine = Foil::boot($config)->engine();

        $engine->useData
        ([
            'config' => $c->get('config'),
            'request' => $c->get(IServerRequest::class)
        ]);

        return $engine;
    }),

    // Map Interfaces to Classes
    // -------------------------------------------------------------------------
    // Here we define some additional interface to class mappings.
    // -------------------------------------------------------------------------
    IResponse::class => DI\object(Response::class),
    IEmitter::class => DI\object(SapiEmitter::class),
    IFinderFactory::class => DI\object(FinderFactory::class),
    Contracts\IMiddleware::class => DI\object(Services\Middleware::class),
    Contracts\IRouter::class => DI\object(Services\Router::class),
    Contracts\IKernel::class => DI\object(Services\Kernel::class)
];
