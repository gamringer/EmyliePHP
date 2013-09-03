<?php

namespace Emylie\Com\REST {

	class SignatorHMACSHA1 {

		private $_consumer;
		private $_private;

		public function __construct($public, $private){
			$this->_consumer = new \AndySmith\OAuth\Consumer($public, $private);
		}

		public function sign($request){
			$parts = explode('?', $request->url);
			$params = [];
			if(isset($parts[1])){
				parse_str($parts[1], $params);
			}

			pr($parts);
			pr($params);

			$r = \AndySmith\OAuth\Request::from_consumer_and_token($this->_consumer, null, $request->method, $request->url, null);
			$r->sign_request(new \AndySmith\OAuth\SignatureMethod_HMAC_SHA1(), $this->_consumer, null);
pr($r);
			$request->setHeader($r->to_header());
		}

		private function _sortParams($params){
			ksort($params);
			foreach($params as $key => &$value){
				if(is_array($value)){
					$value = $this->_sortParams($value);
				}
			}
		}

	}

}