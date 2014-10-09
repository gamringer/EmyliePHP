<?php


namespace Emylie\Test\Routing {
    
    use \Emylie\Test\Resources\Routing\MockRouteable;
    use \Emylie\Test\Resources\Routing\MockVentureable;
    use \Emylie\Test\Resources\Stack\HTTP\MockRequest;
    use \Emylie\Stack\HTTP\Request;

    class RequesstTest extends \PHPUnit_Framework_TestCase {

        public function testConstructAndAccessors() {
            $request = new Request('get', '/');

            $this->assertEquals('get /', $request->getTarget());
            $this->assertEquals('', $request->getRawBody());
            $this->assertEquals('', $request->getRawQuery());


            $request->setRawBody('foo=allo');
            $request->setRawQuery('bar=lol');

            $this->assertEquals('foo=allo', $request->getRawBody());
            $this->assertEquals('bar=lol', $request->getRawQuery());
        }

        public function testDiscover() {
            $routeable = $request = new Request('get', '/allo');

            $ventureable = new MockVentureable('get /');
            $routes = $routeable->discover($ventureable);
            $this->assertFalse($routes);
            
            $ventureable = new MockVentureable('get /allo', 'Mock Data');
            $routes = $routeable->discover($ventureable);
            $this->assertTrue($routes);
            $this->assertEquals('Mock Data', $routeable->getRouteData());
            $this->assertEquals($ventureable->getName(), $routeable->getRoute()->getName());

        }

        public function testConstructFromGlobals() {

            $_SERVER['REQUEST_METHOD'] = 'get';
            $_SERVER['REQUEST_URI'] = '/';

            $request = Request::fromGlobals();
        }

        /**
         * @expectedException \Emylie\Stack\HTTP\Exception
         */
        public function testConstructFromGlobalsException() {
            $request = Request::fromGlobals();
        }
        
    }
}