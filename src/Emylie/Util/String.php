<?php

namespace Emylie\Util {

	class String {

		public static function toClassName($str){

			$className = '';
			$parts = explode('_', $str);

			foreach($parts as $part){
				$className .= ucfirst($part);
			}

			return $className;
		}

	}

}