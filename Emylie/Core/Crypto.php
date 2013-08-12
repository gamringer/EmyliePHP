<?php

namespace Emylie\Core {

	class Crypto{

		static public function encrypt($data) {
			$key = Config::$config['passphrase'];
			$keymd5 = md5($key);

			$encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $keymd5, $data, MCRYPT_MODE_CBC, md5($keymd5));

			return base64_encode($encrypted);
		}

		static public function decrypt($data) {
			$key = Config::$config['passphrase'];
			$keymd5 = md5($key);

			$encrypted = base64_decode($data);
			$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $keymd5, $encrypted, MCRYPT_MODE_CBC, md5($keymd5));

			return rtrim($decrypted, "\0");
		}

		static public function encryptArray($data) {
			return static::encrypt(json_encode($data));
		}

		static public function decryptArray($data) {
			return json_decode(static::decrypt($data), true);
		}

		static public function cryptPassword($password, $cost = 14){

			if($cost < 4){
				$cost = 4;
			}elseif($cost > 31){
				$cost = 31;
			}

			$chars = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
			$salt = '$2y$'.sprintf('%02s', $cost).'$';
			for($i=0; $i<22; $i++){
				$salt .= $chars[rand(0,63)];
			}

			return crypt($password, $salt);
		}

		static public function verifyPassword($password, $crypt){
			return crypt($password, $crypt) == $crypt;
		}

	}

}