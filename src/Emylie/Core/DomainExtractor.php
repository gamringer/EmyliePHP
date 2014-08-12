<?php

namespace Emylie\Core {

	class DomainExtractor{

		static $parts = array();

		public function __construct(){

		}

		static function analyze($domain = null, $name = null){
			if(is_null($domain)){
				$domain = $_SERVER['HTTP_HOST'];
			}

			self::$parts = array_reverse(explode('.',$domain));

			if($name != null){
				$parts = [];
				while($el = array_pop(self::$parts)){
					array_unshift($parts, $el);
					if($el == $name){
						array_unshift($parts, implode('.', array_reverse(self::$parts)));
						break;
					}
				}
			}

			self::$parts = $parts;
		}

		static function getDomain() {
			return self::$parts[1].'.'.self::$parts[0];
		}
	}
}
