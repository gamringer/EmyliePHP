<?php

include dirname(__FILE__).'/../../../SplClassLoader.php';
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../../../src'))->register();

$value = PHP_INT_MAX;
echo 'Value to be packed: ' . $value . PHP_EOL;

$packed = \Emylie\Util\Number::packU64($value);

echo 'Packed (hex): ' . bin2hex($packed) . PHP_EOL;

$unpacked = \Emylie\Util\Number::unpackU64($packed);

echo 'Unpacked: ' . $unpacked . PHP_EOL;