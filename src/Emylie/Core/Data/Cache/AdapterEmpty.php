<?php

namespace Emylie\Core\Data\Cache {

	use Emylie\Core\Config;
	use Emylie\Core\Data\Cache;

    class AdapterEmpty extends Cache {

		public function get($key){
			return self::NOT_CACHED;
		}

		public function getMulti($keys){
			$result = array();

			foreach($keys as $key){
				$result[$key] = Cache::NOT_CACHED;
			}

			return $result;
		}

		public function set($key, $value){
			return true;
		}

		public function delete($key){
			return true;
		}

		public function setMulti($items){
			return true;
		}
	}

}