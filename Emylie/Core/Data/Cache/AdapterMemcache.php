<?php

namespace Emylie\Core\Data\Cache {

	use Emylie\Core\Config;
	use Emylie\Core\Data\Cache;

    class AdapterMemcached extends Cache {

		public function __construct($i_name){
			$this->_name = $i_name;

			if(isset(Config::$config['cache'][$i_name])){
				$servers = Config::$config['cache'][$i_name]['servers'];


			}
		}

		public function get($key){
			return 'allo';
		}

		public function set($key, $value){
			return true;
		}
	}

}