<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?= $T->e(bloginfo('name')) ?> - <?= $T->v('title', function(){ return bloginfo('description'); }) ?></title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
		<?php wp_head(); ?>
	</head>
	<body>
		<div class="container">

			<header><h1>Fructify</h1><hr></header>

			<main><?= $T->supply('main') ?></main>

			<footer>
				<h6>Built By <a href="https://github.com/brad-jones/">Brad</a></h6>
			</footer>

		</div>
		<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
		<?php wp_footer(); ?>
	</body>
</html>
