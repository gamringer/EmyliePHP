<?php

include dirname(__FILE__).'/../../SplClassLoader.php';
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../../src'))->register();

$amount		= 100;	//	100 Dollars
$interest	= 0.05;	//	5% interest per period (ex: year)
$duration	= 3;	//	Lasts 3 periods (ex: 3 years)
$rate		= 12;	//	Compounding rounds per period (ex: calculated monthly)
echo \Emylie\Math::compound($amount, $interest, $duration, $rate) . PHP_EOL;

$rate		= 365;	//	or daily
echo \Emylie\Math::compound($amount, $interest, $duration, $rate) . PHP_EOL;

$rate		= 0;	//	or continuously
echo \Emylie\Math::compound($amount, $interest, $duration, $rate) . PHP_EOL;

//	Only 1 interest payment at the end of the period
echo \Emylie\Math::compound(100, 0.05, 1, 1) . PHP_EOL;

//	Continuous Compounding by setting $rate = 0
echo \Emylie\Math::compound(100, 0.05, 1, 0) . PHP_EOL;