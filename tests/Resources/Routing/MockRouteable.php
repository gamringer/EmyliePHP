<?php

namespace Emylie\Test\Resources\Routing {

    use \Emylie\Routing\Routeable;
    use \Emylie\Routing\Ventureable;

    class MockRouteable implements Routeable
    {

        private $target;

        public function __construct($target)
        {
            $this->target = $target;
        }

        public function getTarget()
        {
            return $this->target;
        }

		public function discover(Ventureable $route, callable $scope)
		{
            return $route->match($this->getTarget());
		}
        
    }
}