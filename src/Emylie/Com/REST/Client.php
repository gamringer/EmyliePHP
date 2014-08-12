<?php

namespace Emylie\Com\REST {

	class Client {

		private $_signator;
		private $_location;
		private $_headers = [];

		public function __construct($signator = null){
			$this->_signator = $signator;
		}

		public function bind($location){
			$this->_location = $location;
		}

		private function _call($method, $uri, $data = ''){

			$url = $this->_location.$uri;

			$request = new Request($method, $url, json_encode($data));
			$request->setHeader('Content-Type: application/json');

			if($this->_signator != null){
				$this->_signator->sign($request);
			}

			return $request->issue();
		}

		public function get($uri, $data = ''){
			return $this->_call('get', $uri, $data);
		}

		public function put($uri, $data = ''){
			return $this->_call('put', $uri, $data);
		}

		public function post($uri, $data = ''){
			return $this->_call('post', $uri, $data);
		}

		public function delete($uri, $data = ''){
			return $this->_call('delete', $uri, $data);
		}
	}

}