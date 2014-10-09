<?php

include dirname(__FILE__).'/../../SplClassLoader.php';
(new \SplClassLoader('Emylie\Examples', dirname(__FILE__).'/../../examples'))->register();
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../../src'))->register();

/**
 *	Instanciate Request
 */
try{
	$request = \Emylie\Stack\HTTP\Request::fromGlobals();
}
catch(\Exception $e){
	$request = new \Emylie\Stack\HTTP\Request('GET', '/examples/Stack/HTTPResponseApplication.php',
	                                          [], [], []);
}

/**
 *	Define Router
 */
$router = new \Emylie\Routing\Router();

//	Define Strict Route
$router->addRoute(new \Emylie\Routing\Route('foo', '(GET|POST) /foo',
											'\Emylie\Examples\Stack\HTTPResponseApplication\Views\DefaultView',
											['var'=>'123']));
//	Define Catch-All Route
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\Emylie\Examples\Stack\HTTPResponseApplication\Views\DefaultView',
											['var'=>'abc']));

/**
 *	Instanciate Application
 */
$fooApp = new \Emylie\Stack\HTTP\ResponseApplication();
$fooApp->setService(\Emylie\Stack\HTTP\ResponseApplication::SERVICE_ROUTING, $router);

/**
 *	Respond to request
 */
try{
	$fooApp->handle($request);
}
catch(\Exception $e){
	echo 'Could not display response';
}