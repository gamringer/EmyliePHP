<?php

include dirname(__FILE__).'/../SplClassLoader.php';
(new \SplClassLoader('Emylie\Examples', dirname(__FILE__).'/../examples'))->register();
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../src'))->register();

$request = new \Emylie\Stack\HTTP\Request('GET', '/examples/Stack/HTTPResponseApplication.php',
	                                          [], [], []);
$request = new \Emylie\Stack\HTTP\Request('GET', '/foo',
	                                          [], [], []);

/**
 *	Define Router
 */
$router = new \Emylie\Routing\Router();

//	Define Strict Route
$router->addRoute(new \Emylie\Routing\Route('foo', '(GET|POST) /(?<fap>foo)',
											'\Emylie\Examples\Stack\HTTPResponseApplication\Views\DefaultView',
											['var'=>'123']));
//	Define Catch-All Route
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\Emylie\Examples\Stack\HTTPResponseApplication\Views\DefaultView',
											['var'=>'abc']));

//	Return Dispatcheable Route
$package = $router->route($request);
