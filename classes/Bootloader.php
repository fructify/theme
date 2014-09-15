<?php namespace LaraPress;
////////////////////////////////////////////////////////////////////////////////
//      ____                       __________                                   
//     |    |   _____ ____________ \______   \_______   ____   ______ ______    
//     |    |   \__  \\_  __ \__  \ |     ___/\_  __ \_/ __ \ /  ___//  ___/
//     |    |___ / __ \|  | \// __ \|    |     |  | \/\  ___/ \___ \ \___ \     
//     |_______ (____  /__|  (____  /____|     |__|    \___  >____  >____  >    
//             \/    \/           \/                       \/     \/     \/     
// -----------------------------------------------------------------------------
//          Designed and Developed by Brad Jones <brad @="bjc.id.au" />         
// -----------------------------------------------------------------------------
////////////////////////////////////////////////////////////////////////////////

class Bootloader
{
	/**
	 * Property: booted
	 * =========================================================================
	 * This simply keeps track of if we have been booted or not.
	 * We only want the constructor of this class to run once.
	 */
	private static $booted = false;

	/**
	 * Method: isBooted
	 * =========================================================================
	 * This tells the caller if we have been booted or not.
	 * 
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 * 
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public static function isBooted()
	{
		return self::$booted;
	}

	/**
	 * Method: __construct
	 * =========================================================================
	 * This is where we take control of wordpress.
	 * We use some reflection to hook into the wordpress work-flows.
	 * 
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function __construct()
	{
		// We only want this constructor to run once
		if (self::$booted) return;

		// We use some reflection here to hook into wordpress
		foreach ((new \ReflectionClass($this))->getMethods() as $method)
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
	 * Method: install_sessions
	 * =========================================================================
	 * Since wordpress does not use sessions at all, lets start them here.
	 * We are using the Laravel Session component, please do not use the
	 * built-in PHP session functions.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function install_sessions()
	{
		// For ages I was trying to work out why I was always getting a second
		// phantom session appearing in the database. Turned out to be wp-cron.
		if (!isset($_GET['doing_wp_cron']))
		{
			/*
			 * Most wordpress setups don't specify a collation
			 * and the laravel db layer needs it to be explicitly defined.
			 * We will set a sensible default here.
			 */
			if (!defined('DB_COLLATE') || empty(DB_COLLATE))
			{
				$collate = 'utf8_unicode_ci';
			}
			else
			{
				$collate = DB_COLLATE;
			}

			\Gears\Session::install
			(
				[
					'driver'    => 'mysql',
					'host'      => DB_HOST,
					'database'  => DB_NAME,
					'username'  => DB_USER,
					'password'  => DB_PASSWORD,
					'charset'   => DB_CHARSET,
					'collation' => $collate,
					'prefix'    => $GLOBALS['wpdb']->prefix,
				],
				'sessions',
				'wordpress-session'
			);
		}
	}

	/**
	 * Method: install_assetmini
	 * =========================================================================
	 * Another one of my projects that I use often is AssetMini.
	 * This installs the AssetMini minfication system if it exists.
	 * For more info checkout:
	 * 
	 *     https://github.com/phpgearbox/assetmini
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function install_assetmini()
	{
		// Check to see if AssetMini exists.
		// I am not expecting everyone to use it.
		if (!class_exists('\Gears\AssetMini')) return;

		/*
		 * The WP_ENV comes from https://github.com/brad-jones/wordpress
		 * Thus we can't assume it exists. But if it does we will use
		 * it to our advantage.
		 */
		if (defined('WP_ENV'))
		{
			if (WP_ENV == 'local')
			{
				\Gears\AssetMini::setDebug(true);
			}
			else
			{
				\Gears\AssetMini::setDebug(false);
			}
		}

		// Install asset mini globally, so we can call Asset::???()
		\Gears\AssetMini::globalise();
	}

	/**
	 * Method: install_blade
	 * =========================================================================
	 * This installs the laravel blade templating engine.
	 *
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * n/a
	 *
	 * Returns:
	 * -------------------------------------------------------------------------
	 * void
	 */
	public function install_blade()
	{
		// Set the cache path
		$cache_path = Paths::parentTheme().'/views/cache';

		// Create our view paths array
		$views_paths = [];

		// Are we being run from a child theme?
		if (Paths::currentTheme() != Paths::parentTheme())
		{
			$views_paths[] = Paths::currentTheme().'/views';
		}

		// Add our own views
		$views_paths[] = Paths::parentTheme().'/views';

		// Install Blade
		\Gears\View::install($views_paths, $cache_path);
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
			// Are we being run from a child theme?
			if (Paths::currentTheme() != Paths::parentTheme())
			{
				try
				{
					\Gears\Router::install(Paths::currentTheme().'/routes', false);
				}
				catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e)
				{
					// do nothing for now
				}
			}

			// Check to see if we have a 404 view
			if (\View::exists('errors.404'))
			{
				$notfound = \View::make('errors.404');
			}
			else
			{
				$notfound = null;
			}

			/*
			 * If the execution gets to here it means either there is no child
			 * theme. Or that the child theme router returned a 404. Either way
			 * we will now run a second router, pointing to our route files.
			 */
			\Gears\Router::install(Paths::parentTheme().'/routes', $notfound);

			// The router by default exits php after it has done it's thing.
			// Statements after here are pointless...
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