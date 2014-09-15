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

class Urls
{
	/**
	 * Path: base
	 * =========================================================================
	 * This will return the base path to the wordpress install.
	 * Classic wordpress users will know this as thye constant: ABSPATH
	 */
	private static $base;
	public static function base()
	{
		if (empty(self::$base))
		{
			self::$base = self::normalise(ABSPATH);
		}

		return self::$base;
	}

	/**
	 * Path: currentTheme
	 * =========================================================================
	 * The path to the theme that is currently active.
	 */
	private static $currentTheme;
	public static function currentTheme()
	{
		if (empty(self::$currentTheme))
		{
			self::$currentTheme = self::normalise(get_stylesheet_directory());
		}

		return self::$currentTheme;
	}

	/**
	 * Path: parentTheme
	 * =========================================================================
	 * If the theme that is currently active is the child of a parent theme.
	 * This will return the path to the parent theme folder.
	 * Otherwise we return the current theme.
	 */
	private static $parentTheme;
	public static function parentTheme()
	{
		if (empty(self::$parentTheme))
		{
			if (get_template_directory() != get_stylesheet_directory())
			{
				self::$parentTheme = self::normalise(get_template_directory());
			}
			else
			{
				self::$parentTheme = self::currentTheme();
			}
		}

		return self::$parentTheme;
	}

	/**
	 * Path: uploads
	 * =========================================================================
	 * This will return the path to the uploads dir.
	 * If you pass true it will only return the base dir.
	 * 
	 * For example:
	 * 
	 *     /wp-content/uploads
	 * 
	 * Instead of:
	 * 
	 *    /up-content/uploads/year/month
	 */
	private static $uploads;
	public static function uploads($base = false)
	{
		if (empty(self::$uploads))
		{
			self::$uploads = wp_upload_dir();
		}

		if ($base)
		{
			return self::normalise(self::$uploads['basedir']);
		}
		else
		{
			return self::normalise(self::$uploads['path']);
		}
	}

	/**
	 * Method: normalise
	 * =========================================================================
	 * This method is used internally to make sure
	 * all paths exist and are of the same format.
	 * 
	 * Parameters:
	 * -------------------------------------------------------------------------
	 * $path - The file path to normalise
	 * 
	 * Returns:
	 * -------------------------------------------------------------------------
	 * string
	 */
	private static function normalise($path)
	{
		// I think this is all we need for now
		return realpath($path);
	}
}