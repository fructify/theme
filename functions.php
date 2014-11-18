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

// Make sure we are being called inside the WordPress environment.
if (!defined('ABSPATH')) exit;

/*
 * Some people may use this theme, separate from the main WordPress project.
 * For more info on the WordPress composer project, checkout:
 * 
 *     https://github.com/fructify/wordpress
 * 
 * So it is possible that the composer autoloader has not yet been required.
 * Lets test if we can find our bootloader class. If the class does not exist
 * we need to install the composer autoloader.
 */
if (!class_exists('Fructify\Bootloader'))
{
	// We make the assumption that the vendors dir is at the root
	$autoloader = ABSPATH.'/vendor/autoload.php';

	// Check to see if it's there
	if (file_exists($autoloader))
	{
		require($autoloader);
	}
	else
	{
		// Houston... We Have A Problem!
		throw new RuntimeException
		(
			'The Composer Autoloader Could Not Be Found @ '.$autoloader
		);
	}
}

/*
 * According to the wordpress documenation a child theme's functions.php file
 * will be included before the parent theme. So we need a way to work out
 * if our bootloader has already been run or not.
 * 
 * For more info on child themes see: http://codex.wordpress.org/Child_Themes
 */
if (!Fructify\Bootloader::isBooted())
{
	/*
	 * If there is no child theme or the child theme
	 * has no requirement for a custom boot loader.
	 * We will run the Bootloader now.
	 */
	new Fructify\Bootloader();
}