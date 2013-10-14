<?php

namespace Emylie\Core {

	class Crypto{

		static public function encrypt($key, $data) {

			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, 'ctr');
    		$iv = mcrypt_create_iv($iv_size, MCRYPT_DEV_URANDOM);

			$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, 'ctr', $iv);

			return base64_encode($iv.$ciphertext);
		}

		static public function decrypt($key, $data) {
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, 'ctr');

			$data = base64_decode($data);
			$iv = substr($data, 0, $iv_size);
			$ciphertext = substr($data, $iv_size);

			if(!isset($iv[$iv_size-1])){
				return null;
			}

			$plaintext = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $ciphertext, 'ctr', $iv);

			return $plaintext;
		}

		static public function encryptArray($key, $data) {
			return static::encrypt($key, json_encode($data));
		}

		static public function decryptArray($key, $data) {
			return json_decode(static::decrypt($key, $data), true);
		}

		static public function cryptPassword($password, $cost = 14){

			if($cost < 4){
				$cost = 4;
			}elseif($cost > 31){
				$cost = 31;
			}

			$salt = '$2y$'.sprintf('%02s', $cost).'$';
			
			$fs = fopen('/dev/urandom', 'r');
			$salt .= bin2hex(fread($fs, 11));
			fclose($fs);

			return crypt($password, $salt);
		}

		static public function verifyPassword($password, $crypt){
			return crypt($password, $crypt) == $crypt;
		}

	}

}