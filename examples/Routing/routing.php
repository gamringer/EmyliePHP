<?php

include dirname(__FILE__).'/../../vendor/autoload.php';

$request = new \Emylie\Stack\HTTP\Request('GET', '/examples/Stack/HTTPResponseApplication.php',
	                                          [], [], []);
$request = new \Emylie\Stack\HTTP\Request('GET', '/foo',
	                                          [], [], []);

/**
 *	Define Router
 */
$router = new \Emylie\Routing\Router();
$router->setScope(function(\Emylie\Stack\HTTP\Request $request){
	return $request->getMethod().' '.$request->getPath();
});

//	Define Strict Route
$router->addRoute(new \Emylie\Routing\Route('foo', '(GET|POST) /(?<fap>foo)',
											'\Emylie\Example\Resources\Views\DefaultView->dynamicMethod',
											['var'=>'123']));
//	Define Catch-All Route
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\Emylie\Example\Resources\Views\DefaultView::staticMethod',
											['var'=>'abc']));

//	Return [Ventureable] Object and populates $extract
$route = $router->route($request);

var_dump($route);
