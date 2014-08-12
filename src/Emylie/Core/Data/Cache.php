<?php

namespace Emylie\Core\Data {

	use \Emylie\Core\Config;

    class Cache {

		private static $_instances = array();

		private $_name;
		private $_connection;

		const NOT_CACHED = '---NOT_CACHED---';
		const CACHE_VALUE_FALSE = '---CACHE_VALUE_FALSE---';

        public static function instance($i_name = 'default'){
                if(!isset(self::$_instances[$i_name])){
                	$type = 'Empty';
                	if(isset(Config::$config['cache'][$i_name])){
                		$type = Config::$config['cache'][$i_name]['type'];
                	}
					$adapter_class = '\Emylie\Core\Data\Cache\Adapter'.$type;
					self::$_instances[$i_name] = new $adapter_class($i_name);
                }

                return self::$_instances[$i_name];
        }
    }

}