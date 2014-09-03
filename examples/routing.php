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
											'\examples\Resources\Views\DefaultView->dynamicMethod',
											['var'=>'123']));
//	Define Catch-All Route
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\examples\Resources\Views\DefaultView::staticMethod',
											['var'=>'abc']));

//	Return Dispatcheable Package
$package = $router->route($request);
