<?php

// Loop through all the pages in the db
foreach (get_pages() as $post_object)
{
	// Grab the relative URL
	$uri = parse_url(get_permalink($post_object->ID), PHP_URL_PATH);

	// Remove trailing slash if any
	$uri = rtrim($uri, '/');

	// Add a route for the post
	Route::get($uri, function() use ($post_object)
	{
		// To keep core wordpress functions happy lets do this.
		query_posts('page_id='.$post_object->ID);
		setup_postdata($GLOBALS['post'] =& $post_object);

		// Lets look for a custom view
		$view_name = str_replace('/', '.', substr($uri, 1));
		if (View::exists($view_name))
		{
			return View::make($view_name);
		}
		else
		{
			return View::make('default-view');
		}
	});
}