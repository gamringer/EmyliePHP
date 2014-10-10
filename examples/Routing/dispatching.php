<?php

include dirname(__FILE__).'/../../vendor/autoload.php';

$request = new \Emylie\Stack\HTTP\Request('GET', '/foo', [], [], []);
$router = new \Emylie\Routing\Router();
$dispatcher = new \Emylie\Routing\Dispatcher($router);

$dispatcher->addRule(function($destination){
	if ($destination instanceof \Closure) {
		return $destination;
	}

	return false;
});
$dispatcher->addRule(function($destination){
	if (preg_match('/^(?<class>[\w\\\\]+)(?<type>\-\>|::)(?<method>[\w\\\\]+)$/', $destination, $match)) {
		$return = [];
		if ($match['type'] == '->') {
			$return[] = new $match['class']();
		
		}elseif ($match['type'] == '::') {
			$return[] = $match['class'];
		}
		
		$return[] = $match['method'];

		return $return;
	}
	
	return false;
});

/**
 *  Call Closure
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											function(\Emylie\Routing\Routeable $request){
												echo 'This is a [Closure], taking: '. print_r($request, true) . PHP_EOL;
											},
											['var'=>'abc']));
$dispatcher->dispatch($request);

/**
 *  Call Static Method in Class
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\Emylie\Example\Resources\Views\DefaultView::staticMethod',
											['var'=>'abc']));
$dispatcher->dispatch($request);

/**
 *  Call Dynamic Method in Object
 */
$router->clearRoutes();
$router->addRoute(new \Emylie\Routing\Route('bar', '.*',
											'\Emylie\Example\Resources\Views\DefaultView->dynamicMethod',
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