<?php

namespace Emylie {

    class StackTest extends \PHPUnit_Framework_TestCase {

        public function testUnit() {

            $this->assertEquals('1,000 B', Math::unit(1000, 'B', 0));
            $this->assertEquals('1 KiB', Math::unit(1024, 'B', 0));
            $this->assertEquals('1 KB', Math::unit(1000, 'B', 0, false));
            $this->assertEquals('1.23 KB', Math::unit(1230, 'B', 2, false));
            //$this->assertEquals('1000 B', Math::unit(10515618156400, 'B', 2, true, ' ', 9, false));
            // unit($amount, $unit, $decimals = 2, $binary = true, $separator = ' ', $minPower = 0, $zeropad = true){
        }
        
    }
}