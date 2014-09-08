<?php


namespace Emylie\Test\Routing {
    
    use \Emylie\Routing\Dispatcher;
    use \Emylie\Routing\Router;
    use \Emylie\Routing\Route;
    use \Emylie\Routing\Routeable;

    class DispatcherTest extends \PHPUnit_Framework_TestCase {

        public function testDispatch() {
        	
        	$request = new MockRouteable();
        	$router = new Router();
            $dispatcher = new Dispatcher($router);

            $route = new Route('fooRoute', '.*', 
                               function(Routeable $request){return 'Closure';},
                               ['var'=>'abc']);
        	$router->clearRoutes();
            $router->addRoute($route);
        	$result = $dispatcher->dispatch($request);
        	$this->assertEquals($result, 'Closure');

        	$route = new Route('fooRoute', '.*', 
                               'Emylie\Test\Resources\Views\MockView->dynamicMethod',
                               ['var'=>'abc']);
        	$router->clearRoutes();
            $router->addRoute($route);
        	$result = $dispatcher->dispatch($request);
        	$this->assertEquals($result, 'Emylie\Test\Resources\Views\MockView::dynamicMethod');

        	$route = new Route('fooRoute', '.*', 
                               'Emylie\Test\Resources\Views\MockView::staticMethod',
                               ['var'=>'abc']);
        	$router->clearRoutes();
            $router->addRoute($route);
        	$result = $dispatcher->dispatch($request);
        	$this->assertEquals($result, 'Emylie\Test\Resources\Views\MockView::staticMethod');
        }

        /**
         * @expectedException \Emylie\Routing\Exception
         */
        public function testDispatchException() {
        	
        	$request = new MockRouteable();
        	$router = new Router();
            $dispatcher = new Dispatcher($router);

        	$route = new Route('fooRoute', '.*', 
                               null,
                               ['var'=>'abc']);
        	$router->clearRoutes();
            $router->addRoute($route);
        	$dispatcher->dispatch($request);
        }
        
    }
}