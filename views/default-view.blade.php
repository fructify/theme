@extends('layouts.master')

@section('main')

	<div class="page-title"><h1><?php the_title(); ?></h1></div>
	<div class="page-content">
		<article>
			<?php the_content(); ?>
		</article>
	</div>

@stop