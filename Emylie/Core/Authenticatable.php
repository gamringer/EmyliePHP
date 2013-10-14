<?php

namespace Emylie\Core {

	Trait Authenticatable{

		public static function fromCredentials($email, $password){

			$options = [
				'where' => [
					'email = '.static::getDB()->escape($email),
					'active = 1'
				]
			];

			$user = static::findOne($options);
			
			if(
				null != $user
			 && Crypto::verifyPassword($password, $user->password)
			){
				return $user;
			}

			return null;
		}

		public function verifyPassword($password){

			return Crypto::verifyPassword($password, $this->password);
		}

		public function updatePassword($password){

			//	Encrypt new password
			$this->password = Crypto::cryptPassword($password);

			//	Clear Cache
			$this->removeCache();

			return $this;
		}

		public function createAccessToken($expire = 10){
			return Crypto::encryptArray(Config::$config['passphrase'], [
				'id' => $this->ID,
				'type' => basename(str_replace('\\', '/', get_class($this))),
				'expire' => time() + $expire
			]);
		}

		public static function fromAccessToken($token){
			$tokenInfo = Crypto::decryptArray(Config::$config['passphrase'], $token);
			if(
				time() < $tokenInfo['expire']
			 && $tokenInfo['type'] == substr(get_called_class(), strrpos(get_called_class(), '\\') + 1)
			){
				return static::find($tokenInfo['id'], ['where' => ['active = 1']]);
			}

			return null;
		}

	}

}