<?php

namespace Emylie\Test\Util {

    use \Emylie\Routing\Route;

    class RouteTest extends \PHPUnit_Framework_TestCase {

        public function testConstruct() {
            
            $name = 'fooRoute';
            $pattern = '/.*/';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);

            $this->assertEquals($route->getName(), $name);
            $this->assertEquals($route->getPattern(), $pattern);
            $this->assertEquals($route->getDestination(), $destination);
            $this->assertEquals(serialize($route->getData()), serialize($data));

            $this->assertNotEquals($route->getName(), '');
            $this->assertNotEquals($route->getPattern(), '');
            $this->assertNotEquals($route->getDestination(), '');
            $this->assertNotEquals(serialize($route->getData()), serialize([]));
        }

        public function testMatchesProperly() {
            
            $name = 'fooRoute';
            $pattern = '/foo[a]+';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);

            $this->assertTrue($route->match('/fooa'));
            $this->assertTrue($route->match('/fooaaaaaaaaaa'));
            $this->assertFalse($route->match('/foob'));
            $this->assertFalse($route->match('/foo'));
            $this->assertFalse($route->match('fooaaaaaaaa'));
        }
        
    }
}