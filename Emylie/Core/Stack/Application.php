<?php

namespace Emylie\Core\Stack {

	use \Emylie\Core\URIExtractor;

	class Application{

		protected $_name;
		protected $_restricted = false;
		protected $_secure = false;
		protected $_unrestricted_actions;
		protected $_access_action;
		protected $_access_type;
		protected $_public_actions = array();
		protected $_default_controller = 'Default';
		protected $_default_controllers = [];

		private static $_session_started = false;

		private $_dir;

		protected $_user;
		protected $_account;
		protected static $_instance;

		public final function __construct(){

			self::$_instance = $this;

			$this->_init();

			$this->dir = APPS_DIR.DIRECTORY_SEPARATOR.$this->_name;
			$this->loadConfiguration();

			define('APP', $this->_name);
			define('APP_DIR', APPS_DIR.'/'.APP.'/');
			define('APP_WEBROOT', APP_DIR.'web/');
			define('APP_CTRL_DIR', APP_DIR.'controllers/');
			define('APP_VIEW_DIR', APP_DIR.'views/');
		}

		public static function startSession(){
			if(!self::$_session_started){
				self::$_session_started = true;

				session_start();
			}
		}

		protected function _init(){}

		public static final function factory($appname){
			$app_classname = '\\Apps\\'.$appname.'\\'.$appname.'App';
			return new $app_classname();
		}

		public static function getProtocol(){
			return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		}

		public final function execute($command){

			if($this->_secure && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')){
				header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				exit;
			}

			list($controller, $action) = $this->_parseCommand($command);

			$stack = $this->_getStack($controller, $action);

			return $this->_process($stack);
		}

		protected function _process($stack){

			$stack->execute();

			return $stack->getDisplayResult();
		}

		protected function _getStack($controller, $action){

			if(
				$this->_restricted &&
				!in_array($controller.'::'.$action, $this->_public_actions) &&
				!in_array($controller.'::*', $this->_public_actions) &&
				$controller.'::'.$action != $this->_access_action
			){
				static::startSession();

				if(isset($_SESSION['access_id'])) {
					$model_class_name = $this->_access_type;
					$this->_user = $model_class_name::find($_SESSION['access_id']);

					$model_class_name = $this->_account_type;
					$this->_account = $model_class_name::find($_SESSION['account_id']);
				}

				if(null === $this->_user){
					list($controller, $action) = explode('::', $this->_access_action);
				}
			}

			$stack = new Stack($controller, $action);

			if($stack->getStatus() != 200){
				if($stack->getStatus() == 404){
					$stack = new Stack('Error', '404');
				}
			}

			return $stack;
		}

		protected function _parseCommand($command){

			$first = URIExtractor::getPart(0, $this->_default_controller);

			if(isset($this->_default_controllers[$first])){
				$controller = $this->_default_controllers[$first];
				$action = $first;
			}elseif(isset(URIExtractor::$parts[1])){
				$controller = $first;
				$action = URIExtractor::$parts[1];
			}else{
				$controller = $first;
				$action = 'default';
			}

			return array($controller, $action);
		}

		public function load($type, $action = 'default'){}

		public function loadConfiguration(){
			if(file_exists(BASE_PATH.'/config.php')){
				include BASE_PATH.'/config.php';
			}

			if(DEV && file_exists(BASE_PATH.'/config_dev.php')){
				include BASE_PATH.'/config_dev.php';
			}

			if(file_exists(BASE_PATH.'/config_local.php')){
				include BASE_PATH.'/config_local.php';
			}

			if(file_exists($this->dir.'/config.php')){
				include $this->dir.'/config.php';
			}

			if(DEV && file_exists($this->dir.'/config_dev.php')){
				include $this->dir.'/config_dev.php';
			}

			if(file_exists($this->dir.'/config_local.php')){
				include $this->dir.'/config_local.php';
			}
		}

		public static function getApp(){
			return self::$_instance;
		}

		public function getName(){
			return $this->_name;
		}

		public function getUser(){
			return $this->_user;
		}

		public function getAccount(){
			return $this->_account;
		}

		public function login($user, $account, $remember = false){

			static::startSession();
			$_SESSION['access_id'] = $user->ID;
			$_SESSION['account_id'] = $account->ID;

			$this->_user = $user;
			$this->_account = $account;

			$user->last_login_time = time();
			$user->save();

			if($remember){
				$memento = $user->createAccessToken(604800);
				setcookie('memento', $memento, time() + 604800, '/', '.'.$this->_name.'.'.DOMAIN);
			}
		}

		public function logout(){
			if(isset($_SESSION['access_id'])){
				unset($_SESSION['access_id']);
				unset($_SESSION['account_id']);
			}
			if(isset($_SESSION['oauth'])){
				unset($_SESSION['oauth']);
			}
			setcookie('memento', null, 0, '/', '.'.$this->_name.'.'.DOMAIN);
		}

		public static function getEndUserIP($trustProxy = true){

			if(DEV){
				return Config::$config['office_ip'];
			}

			$ip = $_SERVER['REMOTE_ADDR'];
			if($trustProxy){
				$forwarded =
					isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :
					(isset($_SERVER['X_FORWARDED_FOR']) ? $_SERVER['X_FORWARDED_FOR'] :
					(isset($_SERVER['X-FORWARDED-FOR']) ? $_SERVER['X-FORWARDED-FOR'] :
					$_SERVER['REMOTE_ADDR']));

				$forwardParts = explode(',', $forwarded);
				foreach($forwardParts as $forwardedIP){
					$forwardedIP = trim($forwardedIP);
					$forwardedIPlong = ip2long($forwardedIP);
					if(
						!($forwardedIPlong >= 167772160 && $forwardedIPlong <= 184549375)
					 && !($forwardedIPlong >= 2886729728 && $forwardedIPlong <= 2887778303)
					 && !($forwardedIPlong >= 3232235520 && $forwardedIPlong <= 3232301055)
					 && !($forwardedIPlong >= 2851995648 && $forwardedIPlong <= 2852061183)
					 && !($forwardedIPlong >= 2130706432 && $forwardedIPlong <= 2147483647)
					){
						return $forwardedIP;
					}
				}
			}

			return $ip;
		}

	}
}