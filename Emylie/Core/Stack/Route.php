<?php

namespace Emylie\Core\Stack {

	class Route {

		public $path;
		public $data;
		public $validation;

		private $_parts;

		public function __construct($path, Array $data = [], Callable $validation = null){
			$this->path = $path;
			$this->data = $data;
			$this->validation = $validation;
			$this->_parts = explode('/', trim($path, '/'));
		}

		public function validate($string){
			$result = $this->data;
			$parts = explode('/', trim($string, '/'));
			if(sizeof($parts) < sizeof($this->_parts) - (end($this->_parts) == '*' ? 1 : 0)){
				return null;
			}
			
			foreach($this->_parts as $i => $part){
				if($part[0] == ':'){
					$result[substr($part, 1)] = $parts[$i];
				}elseif($part == '*'){
					break;
				}elseif($part != $parts[$i]){
					return null;
				}
			}
			
			$callback = $this->validation;
			if($callback == null || $callback($result)){
				return $result;
			}

			return null;
		}
	}
}
