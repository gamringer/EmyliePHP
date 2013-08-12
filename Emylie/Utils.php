<?php

namespace Emylie {

	class Utils {

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