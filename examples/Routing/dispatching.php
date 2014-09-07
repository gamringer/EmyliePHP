<?php

include dirname(__FILE__).'/../SplClassLoader.php';
(new \SplClassLoader('examples', dirname(__FILE__).'/..'))->register();
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../src'))->register();

$request = new \Emylie\Stack\HTTP\Request('GET', '/foo', [], [], []);
$dispatcher = new \Emylie\Routing\Dispatcher();

/**
 *  Call Closure
 */
$router = new \Emylie\Routing\Router();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											function(){
												echo 'This is a [Closure]' . PHP_EOL;
											},
											['var'=>'abc']));
$dispatcher->dispatch($router->route($request));

/**
 *  Call Static Method in Class
 */
$router = new \Emylie\Routing\Router();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\examples\Resources\Views\DefaultView::staticMethod',
											['var'=>'abc']));
$dispatcher->dispatch($router->route($request));

/**
 *  Call Dynamic Method in Object
 */
$router = new \Emylie\Routing\Router();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\examples\Resources\Views\DefaultView->dynamicMethod',
											['var'=>'abc']));
$dispatcher->dispatch($router->route($request));