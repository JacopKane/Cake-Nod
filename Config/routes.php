<?php
$routes = Configure::read('Environment.Routes');

Router::prefixes(array(Configure::read('Environment.Names.prefix')));

foreach($routes as $url => $route) {
	$route[0] = empty($route[0]) ? null : $route[0];
	$route[1] = empty($route[1]) ? null : $route[1];

	Router::connect("{$url}", $route[0], $route[1]);
}

Router::parseExtensions('json');