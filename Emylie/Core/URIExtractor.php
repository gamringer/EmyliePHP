<?php

namespace Emylie\Core {

	class URIExtractor{

		static $parts = [];
		static $vars = [];

		public function __construct(){

		}

		public static function analyze($uri = null){
			self::$parts = [];
			self::$vars = [];
			if(is_null($uri)){
				$uri = $_SERVER['REQUEST_URI'];
			}
			$parts = explode('?', $uri);
			$uri = $parts[0];
			$query = isset($parts[1]) ? $parts[1] : '';

			self::$parts = array();

			$parts = explode('/',$uri);
			foreach($parts as $part){
				if(isset($part[0])){
					$sub_parts = explode(':',$part);
					if(isset($sub_parts[1])){
						self::$vars[$sub_parts[0]] = $sub_parts[1];
					}else{
						self::$parts[] = $part;
					}
				}
			}
		}

		public static function rebuild($new_variables = array(), $remove = array(), $includeQuery = false){

			$vars = self::$vars;
			foreach($new_variables as $new_variable => $value){
				$vars[$new_variable] = $value;
			}
			ksort($vars);

			$url = '/';
			$query = '';
			foreach(self::$parts as $i => $part){
				if(is_int($i)){
					if($part[0] != '?'){
						$url .= $part.'/';
					}else{
						$query = $part;
					}
				}
			}

			foreach($vars as $var => $value){
				if(!in_array($var, $remove) || isset($new_variables[$var])){
					$url .= $var.':'.$value.'/';
				}
			}

			if($includeQuery){
				$url .= $query;
			}

			return $url;
		}

		public static function get($key, $default = null){

			if(isset(static::$vars[$key])){
				return static::$vars[$key];
			}

			if(isset($_GET[$key])){
				return $_GET[$key];
			}

			return $default;
		}

		public static function getPart($index, $default = null){

			if(isset(static::$parts[$index])){
				return static::$parts[$index];
			}

			return $default;
		}

		public static function recode64($data){
			return str_replace(
				['/','=','+'],
				['.','-','_'],
				$data
			);
		}

		public static function predecode64($data){
			return str_replace(
				['.','-','_'],
				['/','=','+'],
				$data
			);
		}
	}
}
