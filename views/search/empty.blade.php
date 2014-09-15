@extends('layouts.master')

@section('main')

	<div class="page-content">
		<article>
			<div style="text-align:center;">
				<h4>
					Sorry, but nothing matched your search criteria
					<span class="search-query">
						"{{{ $query }}}"
					</span>.
					<br>
					Please try again with some different keywords.
				</h4>
			</div>
		</article>
	</div>

@stop