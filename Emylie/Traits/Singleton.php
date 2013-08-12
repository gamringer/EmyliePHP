<?php
namespace Emylie\Traits {
	Trait Singleton{
		private static $_instance;

		public static function get(){
			if(!isset(static::$_instance)){
				static::$_instance = new static();
			}

			return static::$_instance;
		}
	}
}