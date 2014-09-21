<?php


namespace Emylie\Test\DI {
    
    use \Emylie\Test\Resources\DI\MockServiceLocator;
    use \Emylie\Test\Resources\DI\MockService;

    class ServiceLocatorTest extends \PHPUnit_Framework_TestCase {

        public function testSetService() {
        	$application = new MockServiceLocator();

        	$application->setService('routing', new MockService());
        	$application->getService('routing');
        }

        /**
         * @expectedException \Emylie\DI\Exception
         */
        public function testGetServiceException() {
        	$application = new MockServiceLocator();

        	$application->getService('does_not_exist');
        }
        
    }
}