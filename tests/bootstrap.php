<?php

include dirname(__FILE__).'/../vendor/autoload.php';

if (!function_exists('getallheaders')) {
	function getallheaders(){return [];}
}