<?php

namespace Emylie\Core\OAuth{

	use \Emylie\Core\Config;
	use \Emylie\Core\Crypto;
	use \Emylie\Core\HTTPRequest;
	use \Emylie\Core\Stack\Application;

	class OAuthFacebook{

		protected static $_authorizeUrl = 'https://www.facebook.com/dialog/oauth/';
		protected static $_accessUrl = 'https://graph.facebook.com/oauth/access_token';
		protected static $_apiUrl = 'https://graph.facebook.com/me';

		public static function isAuthenticated(){
			Application::startSession();

			return
				isset($_SESSION['oauth'])
			 && $_SESSION['oauth']['provider'] == 'facebook'
			 && $_SESSION['oauth']['expire'] > time();
		}

		public static function authenticate($options){

			if(
				isset($_GET['code'])
			 && isset($_GET['state'])
			){
				$state = Crypto::decryptArray($_GET['state']);
				if($state['provider'] == 'facebook'){

					$result = [];
					$req = new HTTPRequest(static::$_accessUrl);
					$req->get = [
						'redirect_uri' => $options['redirect_uri'],
						'code' => $_GET['code'],
						'client_id' => Config::$config['oauth']['providers']['facebook']['key'],
						'client_secret' => Config::$config['oauth']['providers']['facebook']['secret']
					];
					parse_str($req->requestResult(), $result);

					if(isset($result['access_token'])){
						$_SESSION['oauth']['provider'] = 'facebook';
						$_SESSION['oauth']['token'] = $result['access_token'];
						$_SESSION['oauth']['expire'] = time() + $result['expires'];

						return true;
					}
				}
			}else{
				header('Location: '.static::getLoginURL($options));
				exit;
			}

			return false;
		}

		public static function getLoginURL($options){

			if(!isset($options['redirect_uri'])){
				trigger_error('Missing parameter: redirect_uri');
			}

			return static::$_authorizeUrl.'?'.http_build_query([
				'redirect_uri' => $options['redirect_uri'],
				'client_id' => Config::$config['oauth']['providers']['facebook']['key'],
				'state' => Crypto::encryptArray([
					'provider' => 'facebook',
					'uid' => uniqid('', true)
				]),
				'scope' => isset($options['scope']) ? $options['scope'] : ''
			]);
		}

		public static function getUserInfo(){
			if(static::isAuthenticated()){
				$result = [];
				$req = new HTTPRequest(static::$_apiUrl);
				$req->get = [
					'access_token' => $_SESSION['oauth']['token']
				];
				return json_decode($req->requestResult(), true);
			}
		}

	}

}