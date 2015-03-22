<?php

$nav_menu = array('carousel' => array('carousel.php', 'Carousel'), 'contacts' => array('contacts.php', 'Contacts'));

echo '<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>' . $page_title . '</title>

		<link href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">' . PHP_EOL;

if (is_array($css)) {
	foreach ($css as $v) {
		echo '		<link href="' . $v . '" rel="stylesheet">' . PHP_EOL;
	}
}

echo '
		<!--[if lt IE 9]>
		<script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body style="padding-top: 70px;">
		<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="/uvic/" title="University of Victoria 0.1">University of Victoria</a>
			</div>

			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav">' . PHP_EOL;

				foreach ($nav_menu as $k => $v) {
					echo '			<li';
					echo (strpos($_SERVER['PHP_SELF'], $v[0]) !== false) ? ' class="active"' : '';
					echo '><a id="' . $k . '" href="' . $v[0] . '">' . $v[1] . '</a></li>' . PHP_EOL;
				}

				echo '
				</ul>
			</div><!-- /.navbar-collapse -->
		</nav>

		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<h1>' . $page_title . '</h1>';
