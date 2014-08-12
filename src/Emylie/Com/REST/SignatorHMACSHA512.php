<?php

namespace Emylie\Com\REST {

	class SignatorHMACSHA512 {

		private $_public;
		private $_private;

		public function __construct($public, $private){
			$this->_public = $public;
			$this->_private = $private;
		}

		public function sign($request){
			$time = time();

			$uri = substr($request->url, strpos($request->url, '/', 8));

			$ctx = hash_init('sha512', HASH_HMAC, $this->_private);
			hash_update($ctx, $uri);
			hash_update($ctx, $request->data);
			hash_update($ctx, $time);
			$hash = hash_final($ctx);

			$request->setHeader('X-Timestamp: '.$time);
			$request->setHeader('X-Public: '.$this->_public);
			$request->setHeader('X-Digest: '.$hash);
		}

	}

}