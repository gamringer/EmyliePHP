<?php

include dirname(__FILE__).'/../../SplClassLoader.php';
(new \SplClassLoader('examples', dirname(__FILE__).'/../..'))->register();
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../../src'))->register();

$request = new \Emylie\Stack\HTTP\Request('GET', '/foo', [], [], []);
$router = new \Emylie\Routing\Router();
$dispatcher = new \Emylie\Routing\Dispatcher($router);

/**
 *  Call Closure
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											function(){
												echo 'This is a [Closure]' . PHP_EOL;
											},
											['var'=>'abc']));
$dispatcher->dispatch($request);

/**
 *  Call Static Method in Class
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\examples\Resources\Views\DefaultView::staticMethod',
											['var'=>'abc']));
$dispatcher->dispatch($request);

/**
 *  Call Dynamic Method in Object
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\examples\Resources\Views\DefaultView->dynamicMethod',
											['var'=>'abc']));
$dispatcher->dispatch($request);


/**
 *  Call Unknown path
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'unknown path',
											['var'=>'abc']));
try {
	$dispatcher->dispatch($request);
}

catch(\Emylie\Routing\Exception $e){
	echo $e->getMessage() . PHP_EOL;
}