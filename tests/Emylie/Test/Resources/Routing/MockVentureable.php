<?php

namespace Emylie\Test\Routing {

    use \Emylie\Routing\Ventureable;

    class MockVentureable implements Ventureable {

		public function getName()
		{
			return 'foo_venture';
		}

		public function getPattern()
		{
			return '.*';
		}

		public function getDestination()
		{
			return 'home';
		}

		public function getData()
		{
			return [];
		}

		public function match($request, &$extract = null)
		{
			return true;
		}
        
    }
}