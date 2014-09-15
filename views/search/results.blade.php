@extends('layouts.master')

@section('main')

	<div class="page-title">
		<h1>Search Results for: <span class="search-query">{{{ $query }}}</span></h1>
		<h6>Found {{ $num_found }} results in {{ $time_taken }}.</h6>
	</div>

	<div class="page-content">
		<article>
			@foreach ($results as $result)
				<section>
					<h3>{{ $result->post_title }}</h3>
					<p>{{ $result->post_excerpt }}</p>
				</section>
				<hr>
			@endforeach
		</article>
	</div>

@stop