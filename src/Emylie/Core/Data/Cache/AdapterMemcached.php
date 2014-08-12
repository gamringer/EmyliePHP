<?php

namespace Emylie\Core\Data\Cache {

	use Emylie\Core\Config;
	use Emylie\Core\Data\Cache;

    class AdapterMemcached extends Cache {

		private $_cache_life;
		private $_prefix;
		private $_enabled;

		public function __construct($i_name){
			$this->_name = $i_name;

			if(isset(Config::$config['cache'][$i_name])){
				$servers = Config::$config['cache'][$i_name]['servers'];
				$this->_cache_life = Config::$config['cache'][$i_name]['life'];
				$this->_prefix = Config::$config['cache'][$i_name]['prefix'];

				$this->_connection = new \Memcached();
				foreach($servers as $server) {
					list($address, $port) = explode(':', $server);
					$this->_connection->addServer($address, $port);
				}

				$this->_enabled = Config::$config['cache'][$i_name]['enabled'];
			}
		}

		public function get($key){
			$value = false;
			if($this->_enabled){
				$value = $this->_connection->get($this->_prefix.$key);
			}

			if(false === $value){
				return self::NOT_CACHED;
			}elseif(self::CACHE_VALUE_FALSE === $value){
				return false;
			}else{
				return $value;
			}
		}

		public function getMulti($keys){
			$result = array();
			if($this->_enabled){
				$result = $this->_connection->getMulti($keys);
			}

			foreach($keys as $key){
				if(!isset($result[$key])){
					$result[$key] = Cache::NOT_CACHED;
				}
			}

			return $result;
		}

		public function set($key, $value){
			if(!$this->_enabled){
				return false;
			}

			return $this->_connection->set($this->_prefix.$key, $value, $this->_cache_life);
		}

		public function delete($key){
			if(!$this->_enabled){
				return false;
			}

			return $this->_connection->delete($this->_prefix.$key);
		}

		public function setMulti($items){
			if(!$this->_enabled){
				return false;
			}

			$finalItems = array();
			foreach($items as $key => $item){
				$finalItems[$this->_prefix.$key] = $item;
			}

			return $this->_connection->setMulti($finalItems, $this->_cache_life);
		}
	}

}