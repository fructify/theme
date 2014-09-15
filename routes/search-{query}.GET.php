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

Route::get('/search/{query}', function($query)
{
	// Start timer
	$start = microtime(true);

	/*
	 * Because we are using the Search Everything plugin we need to do this.
	 * As it is obviously designed aorund the "GLOBALNESS" of wordpress.
	 * However if we needed to write some custom search code for a specfic
	 * application. Then I would just use a new WP_Query() and keep everything
	 * local to this route.
	 */ 
	query_posts('s='.$query);

	if (have_posts())
	{
		// Return our search results view
		return View::make('search.results')
			->withQuery($query)
			->withNumFound($GLOBALS['wp_query']->found_posts)
			->withTimeTaken(round(microtime(true) - $start, 3))
			->withResults($GLOBALS['wp_query']->posts)
		;
	}
	else
	{
		// Return our search empty view
		return View::make('search.empty')->withQuery($query);
	}
});