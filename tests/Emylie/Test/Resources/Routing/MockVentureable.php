<?php

namespace Emylie\Test\Resources\Routing {

    use \Emylie\Routing\Ventureable;

    class MockVentureable implements Ventureable
    {

        private $address;
        private $data;

        public function __construct($address, $data = null)
        {
            $this->address = $address;
            $this->data = $data;
        }

        public function getName()
        {
            return 'Mock Ventureable';
        }

		public function match($target, &$extract = null)
		{
            if($target == $this->address){
                $extract = $this->data;

                return true;
            }

            return false;
		}
        
    }
}