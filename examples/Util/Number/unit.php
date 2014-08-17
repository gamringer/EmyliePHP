<?php

include dirname(__FILE__).'/../../SplClassLoader.php';
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../../src'))->register();

$size = 1000000000000; // 1 Terabyte, ~931 GibiByte
echo \Emylie\Util\Number::unit($size, 'B', true) . PHP_EOL;
echo \Emylie\Util\Number::unit($size, 'B', false) . PHP_EOL;

$length = 299792458; // 1 light-second
echo \Emylie\Util\Number::unit($length, 'm', false) . PHP_EOL;
echo \Emylie\Util\Number::unit($length, 'm', false, [5, '.', ','], '') . PHP_EOL;

$mass = 0.000008548489; // Some small number
echo \Emylie\Util\Number::unit($mass, 'g', false) . PHP_EOL;
echo \Emylie\Util\Number::unit($mass, 'g', false, [5, '.', ','], '') . PHP_EOL;

$mass = 1100000; // 1.1 MegaTon
echo \Emylie\Util\Number::unit($mass, 'T', false, [2, ',', ' ']) . PHP_EOL;