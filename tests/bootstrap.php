<?php

include dirname(__FILE__).'/../SplClassLoader.php';

(new \SplClassLoader('Emylie\Test', dirname(__FILE__).'/../tests'))->register();
(new \SplClassLoader('Emylie', dirname(__FILE__).'/../src'))->register();

if (!function_exists('getallheaders')) {
	function getallheaders(){return [];}
}