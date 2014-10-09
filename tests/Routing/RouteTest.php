<?php

namespace Emylie\Test\Routing {

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

            $this->assertNotEmpty($route->getName());
            $this->assertNotEmpty($route->getPattern());
            $this->assertNotEmpty($route->getDestination());
            $this->assertNotEmpty($route->getData());
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

        public function testExtractsProperData() {
            
            $name = 'fooRoute';
            $pattern = '/(?<vartest>foo[a]+)/?.*';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);

            $extract = null;
            $matches = $route->match('/fooa/lolalole', $extract);

            $this->assertArrayHasKey('vartest', $extract);
            $this->assertArrayHasKey('bar', $extract);
        }

        public function testExtractsOverridesDefaultData() {
            
            $name = 'fooRoute';
            $pattern = '/(?<bar>foo[a]+)/?.*';
            $destination = '\examples\Resources\Views\DefaultView';
            $data = ['foo' => 'bonjour', 'bar' => 'allo'];
            $route = new Route($name, $pattern, $destination, $data);

            $extract = null;
            $matches = $route->match('/fooa/lolalole', $extract);

            $this->assertArrayHasKey('foo', $extract);
            $this->assertEquals($data['foo'], $extract['foo']);
            $this->assertArrayHasKey('bar', $extract);
            $this->assertEquals('fooa', $extract['bar']);
        }
        
    }
}