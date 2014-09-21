<?php


namespace Emylie\Test\Routing {
    
    use \Emylie\Routing\Dispatcher;
    use \Emylie\Routing\Router;
    use \Emylie\Routing\Route;
    use \Emylie\Routing\Routeable;
    use \Emylie\Test\Resources\Routing\MockRouteable;

    class DispatcherTest extends \PHPUnit_Framework_TestCase {

        public function testRules() {
            
            $router = new Router();
            $dispatcher = new Dispatcher($router);

            $this->assertEmpty($dispatcher->getRules());
            $dispatcher->addRule(function($destination){return false;});

            $this->assertNotEmpty($dispatcher->getRules());

            $dispatcher->clearRules();
            $this->assertEmpty($dispatcher->getRules());
        }

        public function testDispatch() {
            
            $request = new MockRouteable('mock route');
            $router = new Router();
            $dispatcher = new Dispatcher($router);

            $dispatcher->clearRules();
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
        	
        	$request = new MockRouteable('mock route');
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