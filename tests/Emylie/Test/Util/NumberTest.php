<?php

namespace Emylie\Test\Util {

    use \Emylie\Util\Number;

    class NumberTest extends \PHPUnit_Framework_TestCase {

        public function testUnit() {
            
            $size = 1000000000000; // 1 Terabyte, ~931 GibiByte
            $this->assertEquals('931 GiB', Number::unit($size, 'B', true));
            $this->assertEquals('1 TB', Number::unit($size, 'B', false));

            $length = 299792458; // 1 light-second
            $this->assertEquals('300 Mm', Number::unit($length, 'm', false));
            $this->assertEquals('299.79246Mm', Number::unit($length, 'm', false, [5, '.', ','], ''));

            $mass = 0.000008548489; // Some small number
            $this->assertEquals('9 µg', Number::unit($mass, 'g', false));
            $this->assertEquals('8.54849µg', Number::unit($mass, 'g', false, [5, '.', ','], ''));

            $mass = 1100000; // 1.1 MegaTon
            $this->assertEquals('1,10 MT', Number::unit($mass, 'T', false, [2, ',', ' ']));

        }

        public function testPackU64() {
            
            $number = 23372036854775807;

            $packed = Number::packU64($number);
            $this->assertEquals('005308be6267ffff', bin2hex($packed));

            $unpacked = Number::unpackU64($packed);
            $this->assertEquals($number, $unpacked);

        }
        
    }
}