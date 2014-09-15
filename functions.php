<?php
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

// Make sure we are being called inside the WordPress environment.
if (!defined('ABSPATH')) exit;

/*
 * Some people may use this theme, separate from my WordPress project.
 * For more info on the WordPress composer project, checkout:
 * 
 *     https://github.com/brad-jones/wordpress
 * 
 * So it is possible that the composer autoloader has not yet been required.
 * Lets test if we can find our bootloader class. If the class does not exist
 * we need to install the composer autoloader.
 */
if (!class_exists('LaraPress\Bootloader'))
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
		throw new Exception
		(
			'The Composer Autoloader Could Not Be Found @ '.$autoloader
		);
	}
}

// Now lets check to see if a child theme has already booted up LaraPress.
if (!LaraPress\Bootloader::isBooted())
{
	/*
	 * If there is no child theme or the child theme
	 * has no requirement for a custom boot loader.
	 * We will run the Bootloader now.
	 */
	new LaraPress\Bootloader();
}