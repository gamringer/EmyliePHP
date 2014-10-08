<?php

namespace Emylie\Com {

	class RestClient {

		private static $_public;
		private static $_private;
		private static $_location;

		public static function registerKeys($public, $private){
			static::$_public = $public;
			static::$_private = $private;
		}

		public static function registerLocation($location){
			static::$_location = $location;
		}

		public static function call($method, $uri, $data = ''){

			$url = static::$_location.$uri;
			$data = json_encode($data);
			$time = time();

			$ctx = hash_init('sha512', HASH_HMAC, static::$_private);
			hash_update($ctx, $uri);
			hash_update($ctx, $data);
			hash_update($ctx, $time);
			$hash = hash_final($ctx);

			$ch = curl_init($url);

			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'X-Public: ' . static::$_public,
				'X-Timestamp: ' . $time,
				'X-Digest: ' . $hash,
				'Content-Type: application/json'
			]);

			$json = curl_exec($ch);
			$result = json_decode($json, true);
			curl_close($ch);

			return $result;
		}

		public static function get($uri, $data = ''){
			return static::call('get', $uri, $data);
		}

		public static function put($uri, $data = ''){
			return static::call('put', $uri, $data);
		}

		public static function post($uri, $data = ''){
			return static::call('post', $uri, $data);
		}

		public static function delete($uri, $data = ''){
			return static::call('delete', $uri, $data);
		}

	}

}