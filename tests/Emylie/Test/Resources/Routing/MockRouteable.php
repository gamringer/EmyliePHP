<?php

namespace Emylie\Test\Resources\Routing {

    use \Emylie\Routing\Routeable;

    class MockRouteable implements Routeable {

        public function getTarget() {
            
            return 'mock target';
        }
        
    }
}