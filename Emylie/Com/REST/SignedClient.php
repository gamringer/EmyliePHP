<?php

namespace Emylie\Com\REST {

	class SignedClient {

		private $_public;
		private $_private;
		private $_location;
		private $_headers = [];

		public function __construct($public, $private){
			$this->_public = $public;
			$this->_private = $private;
		}

		public function bind($location){
			$this->_location = $location;
		}

		private function _call($method, $uri, $data = ''){

			$url = $this->_location.$uri;
			$data = json_encode($data);
			$time = time();

			$ctx = hash_init('sha512', HASH_HMAC, $this->_private);
			hash_update($ctx, $uri);
			hash_update($ctx, $data);
			hash_update($ctx, $time);
			$hash = hash_final($ctx);

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'X-Public: ' . $this->_public,
				'X-Timestamp: ' . $time,
				'X-Digest: ' . $hash,
				'Content-Type: application/json'
			]);

			$json = curl_exec($ch);
			$result = json_decode($json, true);
			curl_close($ch);

			return $json;
			return $result;
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

		public function addHeader($uri, $data = ''){
			return $this->_call('delete', $uri, $data);
		}

		public function setHeader($header, $replace = true){
			if($replace){
				$this->_headers[substr($header, 0, strpos($header, ':'))] = $header;
			}else{
				$this->_headers[] = $header;
			}

			pr($this->_headers);
		}

	}

}