<?php

namespace Emylie\Test\Util {

    use \Emylie\Util\Math;

    class MathTest extends \PHPUnit_Framework_TestCase {

        public function testCompound() {
            
            $amount     = 100;  //  100 Dollars
            $interest   = 0.05; //  5% interest per period (ex: year)
            $duration   = 3;    //  Lasts 3 periods (ex: 3 years)
            $rate       = 12;   //  Compounding rounds per period (ex: calculated monthly)
            $this->assertEquals('116.14722', round(Math::compound($amount, $interest, $duration, $rate), 5));

            $rate       = 365;  //  or daily
            $this->assertEquals('116.18223', round(Math::compound($amount, $interest, $duration, $rate), 5));

            $rate       = 0;    //  or continuously
            $this->assertEquals('116.18342', round(Math::compound($amount, $interest, $duration, $rate), 5));

            //  Only 1 interest payment at the end of the period
            $this->assertEquals('105', round(Math::compound(100, 0.05, 1, 1), 5));

            //  Continuous Compounding by setting $rate = 0
            $this->assertEquals('105.12711', round(Math::compound(100, 0.05, 1, 0), 5));

        }
        
    }
}