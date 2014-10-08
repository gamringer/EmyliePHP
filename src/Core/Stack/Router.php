<?php

namespace Emylie\Core\Stack {

	class Router{

		private $_routes = [];

		public function register(Route $route){
			$this->_routes[] = $route;
		}

		public function route($string){
			$result = [];

			foreach($this->_routes as $route){
				$result = $route->validate($string);
				if($result !== null){
					return $result;
				}
			}

			return null;
		}

	}
}
