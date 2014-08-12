<?php

namespace Emylie\Traits {
	Trait Multiton{
		private static $_instances = array();

		protected $_name;

		public static function get($name = '_default_'){
			if(!isset(static::$_instances[$name])){
				static::$_instances[$name] = static::_factory($name);
			}

			return static::$_instances[$name];
		}

		private static function _factory($name){
			return new static($name);
		}

		protected function __construct($name){
			$this->_name = $name;

			$this->_init();
		}

		public function getName(){
			return $this->_name;
		}

		protected function _init(){}
	}
}