<?php

namespace Emylie\Com {

	class HTTPRequest{

		private $_address;
		private $_method = 'get';
		public $port = 80;

		private $_requestBody = true;
		private $_requestheader = false;
		public $head = null;
		public $resultCode = null;

		public $get = array();
		public $post = array();

		public function __construct($address, $method = 'get'){
			if (in_array($method, array('get', 'post', 'put', 'delete', 'head', 'connect'))) {
				$this->_method = $method;
			}

			$this->_address = $address;
			if(substr($address, 0, 8) == 'https://'){
				$this->port = 443;
			}
		}

		public function requestAll(){
			$this->_requestBody = true;
			$this->_requestHeader = true;

			$this->_dispatch();

			return $this->head;
		}

		public function requestHeader(){
			if ($this->_method !== 'get') {
				trigger_error('Header cannot be retrieved alone on POST requests', E_USER_ERROR);
			}

			$this->_requestBody = false;
			$this->_requestHeader = true;

			$this->_dispatch();

			return $this->head;
		}

		public function requestResult(){
			$this->_requestBody = true;
			$this->_requestHeader = false;

			return $this->_dispatch();
		}

		public function requestTouch(){
			$this->_requestBody = false;
			$this->_requestHeader = false;

			return $this->_dispatch();
		}

		private function _dispatch(){
			$address = $this->_address;

			//	Add get params to url
			if(!empty($this->get)){
				$hasGet = strpos($address, '?') !== false;
				$address .= $hasGet ? '&' : '?';
				$address .= http_build_query($this->get);
			}

			$ch = curl_init();
	        curl_setopt($ch, CURLOPT_PORT, $this->port);
	        curl_setopt($ch, CURLOPT_HTTPGET, $this->_method === 'get');
	        curl_setopt($ch, CURLOPT_POST, $this->_method === 'post');
	        curl_setopt($ch, CURLOPT_PUT, $this->_method === 'put');
	        curl_setopt($ch, CURLOPT_URL, $address);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	        curl_setopt($ch, CURLOPT_HEADER, true);
	        curl_setopt($ch, CURLOPT_NOBODY, !$this->_requestBody);
	        if($this->_method === 'post' && !empty($this->post)){
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $this->post);
	        }

	        $data = curl_exec($ch);
	        $curl_errno = curl_errno($ch);
	        $curl_error = curl_error($ch);

	        curl_close($ch);

	        $this->head = array();
	        $headStarted = false;

	        while (strpos($data, chr(13)) !== false) {
	        	$pos = strpos($data, chr(13));
	        	$line = substr($data, 0, $pos);
	        	$data = substr($data, $pos + 2);
	        	if ($headStarted && strlen($line) == 0) {
	        		break;
	        	}
	        	$matches = array();
	        	preg_match('/([a-zA-Z\-]+)\: (.*)/', $line, $matches);
	        	if (!empty($matches)) {
	        		if ($matches[1] == 'Date') {
	        			$this->head['time'] = strtotime($matches[2]);
	        		}
	        		$this->head[$matches[1]] = $matches[2];
	        		$headStarted = true;
	        	}else{
	        		preg_match('/^HTTP\/([0-9\.]+) ([0-9]{3}) .*/', $line, $matches);
	        		if (!empty($matches)) {
	        			$this->resultCode = $matches[2];
	        		}
	        	}
	        }
	        if (isset($this->head['Content-Type']) && $this->head['Content-Type'] == 'text/json') {
	        	$data = json_decode($data, true);
	        }


	        return $data;
		}

	}

}