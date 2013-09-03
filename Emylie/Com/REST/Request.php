<?php

namespace Emylie\Com\REST {

	class Request {

		public $url;
		public $method;
		public $data;

		private $_headers = [];

		public function __construct($method, $url, $data){
			$this->method = $method;
			$this->url = $url;
			$this->data = $data;
		}

		public function setHeader($header, $replace = true){
			if($replace){
				$this->_headers[substr($header, 0, strpos($header, ':'))] = $header;
			}else{
				$this->_headers[] = $header;
			}
		}

		public function issue(){
			$ch = curl_init($this->url);

			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);

			$json = curl_exec($ch);
			$result = json_decode($json, true);
			curl_close($ch);

			return $result;
		}

	}

}