<?php


namespace Emylie\Test\Routing {

    use \Emylie\Routing\Route;
    use \Emylie\Routing\Router;
    use \Emylie\Test\Resources\Routing\MockRouteable;

    class RouterTest extends \PHPUnit_Framework_TestCase {

        public function testConstruct() {
            
            $router = new \Emylie\Routing\Router();
            
            $this->assertEmpty($router->getRoutes());
        }

        public function testAddRoute() {
            
            $router = new \Emylie\Routing\Router();

            $name = 'fooRoute';
            $pattern = '.*';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);
            $router->addRoute($route);

            $this->assertNotEmpty($router->getRoutes());
        }

        public function testClearRoutes() {
            
            $router = new \Emylie\Routing\Router();

            $name = 'fooRoute';
            $pattern = '.*';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);
            $router->addRoute($route);

            $this->assertCount(1, $router->getRoutes());

            $router->clearRoutes();
            
            $this->assertEmpty($router->getRoutes());
        }

        public function testRoute() {
            
            $router = new \Emylie\Routing\Router();

            $name = 'fooRoute';
            $pattern = '.*';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);
            $router->addRoute($route);

            $request = new MockRouteable();
            $route = $router->route($request);

            $this->assertInstanceOf('\Emylie\Routing\Route', $route);
        }

        /**
         * @expectedException \Emylie\Routing\Exception
         */
        public function testRouteException() {
            
            $router = new \Emylie\Routing\Router();

            $request = new MockRouteable();
            $route = $router->route($request);
        }
        
    }
}