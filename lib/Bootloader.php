<?php namespace Fructify;
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

use ReflectionClass;
use function DI\get;
use function DI\object;
use DI\ContainerBuilder;
use League\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Request;

class Bootloader
{
	/**
	 * This simply keeps track of if we have been booted or not.
	 * We only want the constructor of this class to run once.
	 *
	 * @var boolean
	 */
	private static $booted = false;

	/**
	 * Used by ```functions.php``` to determin if the
	 * constructor in this class has run or not.
	 *
	 * @return boolean
	 */
	public static function isBooted()
	{
		return self::$booted;
	}

	/**
	 * This is where we store the built IoC Container.
	 *
	 * @see http://php-di.org/
	 *
	 * @var \Interop\Container\ContainerInterface
	 */
	protected $container;

	/**
	 * This is where we configure the IoC Container using php-di's dsl.
	 *
	 * @see http://php-di.org/doc/php-definitions.html
	 *
	 * @return array
	 */
	private function getDiDefinitions()
	{
		return [];
	}

	/**
	 * Booloader Constructor
	 *
	 * This is where we take control of wordpress.
	 * We use some reflection magic to easily define wordpress hooks.
	 */
	public function __construct()
	{
		// We only want this constructor to run once
		if (self::$booted) return;

		// Build our IoC Container.
		// see: http://php-di.org/
		$builder = new ContainerBuilder();

		// Add our definitions
		$builder->addDefinitions($this->getDiDefinitions());

		// Add a child themes definitions if it has any.
		if (method_exists($this, 'di_definitions')
		{
			$builder->addDefinitions(call_user_func([$this, 'di_definitions']));
		}

		// Save the container making it available to all hook methods.
		$this->container = $builder->build();

		// We use some reflection here to hook into wordpress
		foreach ((new ReflectionClass($this))->getMethods() as $method)
		{
			// Explode the method name
			$parts = explode('_', $method->name);

			// Grab the hook type
			$hook_type = $parts[0];

			// Make sure it's a valid hook
			if (in_array($hook_type, ['action', 'filter']))
			{
				// Grab the priority
				if (is_numeric(($last = array_pop($parts))))
				{
					$hook_priority = $last;
				}
				else
				{
					$hook_priority = 10;
				}


				// Grab the hook name
				$hook_name = str_replace
				(
					[$hook_type.'_', '_'.$hook_priority],
					'',
					$method->name
				);

				// Add the hook
				call_user_func
				(
					'add_'.$hook_type,
					$hook_name,
					$method->getClosure($this),
					$hook_priority,
					$method->getNumberOfParameters()
				);
			}

			// Here we add one more hook type of our own
			elseif ($hook_type == 'install')
			{
				$method->invoke($this);
			}
		}

		// We are now booted
		self::$booted = true;
	}

	/**
	 * Method: action_wp_loaded
	 * =========================================================================
	 * At this point we setup the laravel router and completely take over
	 * the frontend routing. I hate all the wordpress template hierarchy
	 * and the wordpress rewrite rules, etc. A simple HTTP router is so
	 * much easier to follow.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function action_wp_loaded()
	{
		// We only want the router to run for requests that get
		// funneled through index.php by the .htaccess rewrite rules.
		// wp-admin, wp-cron, wp-login, xmlrpc, etc should run as expected.
		if ($_SERVER['SCRIPT_NAME'] == '/index.php')
		{
			$request = Request::createFromGlobals();

			echo 'Hi';
		}
	}

	/**
	 * Method: action_init
	 * =========================================================================
	 * This is the main init hook into wordpress.
	 * And is where we do some more bootstraping...
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function action_init()
	{
		// Remove Junk from WP Head
		remove_action('wp_head', 'rsd_link');									// remove really simple discovery link
		remove_action('wp_head', 'wp_generator');								// remove wordpress version
		remove_action('wp_head', 'feed_links', 2);								// remove rss feed links (make sure you add them in yourself if youre using feedblitz or an rss service)
		remove_action('wp_head', 'feed_links_extra', 3);						// removes all extra rss feed links
		remove_action('wp_head', 'index_rel_link');								// remove link to index page
		remove_action('wp_head', 'wlwmanifest_link');							// remove wlwmanifest.xml (needed to support windows live writer)
		remove_action('wp_head', 'start_post_rel_link', 10, 0);					// remove random post link
		remove_action('wp_head', 'parent_post_rel_link', 10, 0);				// remove parent post link
		remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);				// remove the next and previous post links
		remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);		// remove the next and previous post links
		remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);				// remove shortlink
	}

	/**
	 * Method: filter_page_link and filter_post_type_link
	 * =========================================================================
	 * The following 2 filters remove the trailing slash from any permalinks.
	 * We also have a .htaccess rewrite rule that remove any trailing slashes.
	 * Its so that we can be sure of the URL format at all times.
	 *
	 * Eg: $_SERVER['REQUEST_URI'].'/extra/path/sections'
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function filter_page_link($link)
	{
		return untrailingslashit($link);
	}

	public function filter_post_type_link($link)
	{
		return untrailingslashit($link);
	}
}
