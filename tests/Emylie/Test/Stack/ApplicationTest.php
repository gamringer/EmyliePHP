<?php


namespace Emylie\Test\Routing {
    
    use \Emylie\Stack\Application;
    use \Emylie\Routing\Router;

    class ApplicationTest extends \PHPUnit_Framework_TestCase {

        public function testConstructor() {
        	$application = new Application();
        }

        public function testIsServiceLocator() {
            $application = new Application();

            $traits = class_uses($application);
            $this->assertArrayHasKey('Emylie\DI\ServiceLocator', $traits);
        }

        public function testCanHandle() {
            $application = new Application();

            $application->handle(null);
        }

    }
}