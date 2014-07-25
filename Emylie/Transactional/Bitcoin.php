<?php

namespace Emylie\Transactional {

	use \Emylie\Core\Config;

	class Bitcoin{

		private static $_instances = array();

		private $_name;
		private $_info;
		private $_connection;

		public function __construct($i_name, $info = null){
			$this->_name = $i_name;

			if($info !== null){
				$this->_info = $info;
			}

			require_once(VENDOR_DIR.'/jsonrpcphp/includes/jsonRPCClient.php');
			$this->_connection = new \jsonRPCClient('http://'.$this->_info['username'].':'.$this->_info['password'].'@'.$this->_info['location'].':'.$this->_info['port'].'/');

			return $this;
		}

		public static function produce($i_name = 'default', $info = null){
			if($info == null){
				$info = Config::$config['bitcoin'][$i_name];
			}
			if(!isset(static::$_instances[$i_name])){
				static::$_instances[$i_name] = new static($i_name, $info);
			}

			return static::$_instances[$i_name];
		}

		public static function rpc($i_name = 'default'){
			return static::produce($i_name)->_connection;
		}

	}

}