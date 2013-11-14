<?php

namespace Emylie\Core\Data {

	use \Emylie\Core\Config;

	abstract class Model{

		protected static $_instances = array();

		public static $i_name = 'default';
		public static $table_name;
		public static $id_field;
		public static $fields;

		public static $cacheable = false;

		public static $has_many = [];
		public static $registreable_extensions = [];
		public static $lockedFields = [];

		public $info;
		public $collections = [];
		public $extensions = [];
		public $ID;

		protected $_validation_errors = [];

		public function __construct($info = null){
			if(is_array($info)){
				$this->info = $info;
			}

			if(isset($this->info[static::$id_field])){
				$this->ID = $this->info[static::$id_field];
			}

			$this->_init();
		}

		public static function getDB(){
			return SQL::db(static::$i_name);
		}

		protected function _init(){}

		public function __get($var_name){

			if(isset($this->info[$var_name])){
				return $this->info[$var_name];
			}

			return null;
		}

		public function __set($var_name, $value){
			if(in_array($var_name, static::$fields)){
				$this->info[$var_name] = $value;
			}else{
				$this->$var_name = $value;
			}
		}

		public function create($returnExisting = false){

			if(in_array('date_added', static::$fields) && !isset($this->info['date_added'])){
				$this->info['date_added'] = time();
			}

			$data = [];
			foreach($this->info as $index => $value){
				if(!in_array($index, static::$lockedFields)){
					$data[$index] = SQL::db(static::$i_name)->escape($value);
				}
			}

			$itemUpdates = [];
			if($returnExisting){
				$itemUpdates[static::$id_field] = 'LAST_INSERT_ID('.static::$id_field.')';
			}

			$this->ID = SQL::db(static::$i_name)->insert(static::$table_name, $data, empty($itemUpdates), $itemUpdates);

			$this->info[static::$id_field] = $this->ID;

			static::$_instances[$this->ID] = $this;
		}

		public function update($fields = null){

			//	Update DB
			if(in_array('date_updated', static::$fields)){
				$this->info['date_updated'] = time();
			}

			$statements['table_name'] = static::$table_name;
			$statements['where'] = [static::$id_field.' = "'.$this->ID.'"'];
			$statements['set'] = [];

			foreach($this->info as $index => $value){
				if(
					in_array($index, static::$fields)
				 && !in_array($index, static::$lockedFields)
				 && (
				 		$fields == null
				 	 || in_array($index, $fields)
				 	)
				){
					$statements['set'][] = $index.' = '.SQL::db(static::$i_name)->escape($value);
				}
			}

			SQL::db(static::$i_name)->update($statements);

			//	Clear Cache
			$this->removeCache();
		}

		public function save(){
			if(!isset($this->ID)){
				$this->create();
			}else{
				$this->update();
			}

			foreach($this->collections as $collection){
				$collection->save();
			}

			foreach($this->extensions as $extension){
				$extension->save();
			}
		}

		public function delete(){
			if(isset($this->ID)){
				SQL::db(static::$i_name)->delete(array(
					'from' => static::$table_name,
					'where' => array(static::$id_field.' = '.$this->ID)
				));

				$this->removeCache();
			}
		}

		public static function findAll($options){

		    $cache = [
		    	'get' => isset($options['cache']['get']) ? $options['cache']['get'] : static::$cacheable,
		    	'set' => isset($options['cache']['set']) ? $options['cache']['set'] : static::$cacheable
	    	];
			unset($options['cache']);

			$key = Config::$config['cache']['main']['prefix'].'model_query:'.static::$table_name.':'.md5(json_encode($options));

			if($cache['get']){
				$list = Cache::instance('main')->get($key);
			}else{
				$list = Cache::NOT_CACHED;
			}

			if(Cache::NOT_CACHED === $list){
				$options['fields'] = array_merge([static::$table_name.'.'.static::$id_field], isset($options['fields']) ? $options['fields'] : []);
				$options['from'] = [static::$table_name];

				$items = static::getDB()->select($options);
				$list = array();
				foreach($items as $item){
					$list[] = $item[static::$id_field];
				}

				if($cache['set']){
					Cache::instance('main')->set($key, $list);
				}
			}

			$options['cache'] = $cache;

			return static::findMany($list, $options);
		}

		public static function findMany($ids, $options = array()){

			$options['cache']['get'] = isset($options['cache']['get']) ? $options['cache']['get'] : static::$cacheable;
			$options['cache']['set'] = isset($options['cache']['set']) ? $options['cache']['set'] : static::$cacheable;

			$options['limit'] = sizeof($ids);
			$options['offset'] = 0;

			$result = array();

			//	Look in the registry
			$cache_ids = array();
			foreach($ids as $id){
				if(!isset(static::$_instances[$id])){
					$cache_ids[] = $id;
				}
			}

			//	Look in the Cache
			if($options['cache']['get']){
				$keys = array();
				foreach($cache_ids as $id){
					$keys[] = Config::$config['cache']['main']['prefix'].'model:'.static::$table_name.':'.$id;
				}
				$cache_result = Cache::instance('main')->getMulti($keys);

				//	Save to registry and prep DB Fetch
				$db_ids = array();
				foreach($cache_ids as $id){
					if($cache_result[Config::$config['cache']['main']['prefix'].'model:'.static::$table_name.':'.$id] !== Cache::NOT_CACHED){
						static::$_instances[$id] = new static($cache_result[Config::$config['cache']['main']['prefix'].'model:'.static::$table_name.':'.$id]);
					} else {
						$db_ids[] = $id;
					}
				}
			}else{
				$db_ids = $cache_ids;
			}

			//	Look in DB
			if(isset($db_ids[0])){
				if(!isset($options['fields'])){
					$options['fields'] = [];
				}
				foreach(static::$fields as $field){
					$options['fields'][] = static::$table_name . '.' . $field;
				}
				$options['from'] = [static::$table_name];
				$options['group'] = [static::$id_field];
				$options['where'] = [static::$table_name.'.'.static::$id_field.' IN('.implode(',', $db_ids).')'];
				$db_result = static::getDB()->select($options);

				//	Save to Cache and Registry
				$items = array();
				foreach($db_result as $item){
					$items[Config::$config['cache']['main']['prefix'].'model:'.static::$table_name.':'.$item[static::$id_field]] = $item;
					static::$_instances[$item[static::$id_field]] = new static($item);
				}

				if(!empty($items) && $options['cache']['set']){
					Cache::instance('main')->setMulti($items);
				}
			}

			//	At this point, all existing items are in the registry
			foreach($ids as $id){
				if(isset(static::$_instances[$id])){
					$result[] = static::$_instances[$id];
				}else{
					$result[] = null;
				}
			}

			return $result;
		}

		public static function findOne($options){

			$options['limit'] = 1;

			$result = static::findAll($options);

			if(isset($result[0])){
				return $result[0];
			}

			return null;
		}

		public static function find($id, $options = []){

			//	Cut out obvious null
			if($id == 0){
				return null;
			}

			$options['cache']['get'] = isset($options['cache']['get']) ? $options['cache']['get'] : static::$cacheable;
			$options['cache']['set'] = isset($options['cache']['set']) ? $options['cache']['set'] : static::$cacheable;

			//	Look in Registry
			if(!isset(static::$_instances[$id])){

				//Look in Cache
				if ($options['cache']['get']) {
					$key = Config::$config['cache']['main']['prefix'].'model:'.static::$table_name.':'.$id;
					$cache_result = Cache::instance('main')->get($key);
				} else {
					$cache_result = Cache::NOT_CACHED;
				}


				//	If cached, assign to registry
				if(Cache::NOT_CACHED !== $cache_result){
					static::$_instances[$id] = new static($cache_result);

				//	Otherwise, fetch from DB, assign to registry, then cache
				} else {
					$sql = array(
						'limit' => 1,
						'fields' => static::$fields,
						'from' => array(static::$table_name),
						'where' => array(
							static::$id_field.' = '.$id
						)
					);
					$db_result = static::getDB()->select($sql);

					if (isset($db_result[0])) {
						$item = $db_result[0];

						if ($options['cache']['set']) {
							Cache::instance('main')->set($key, $item);
						}

						static::$_instances[$id] = new static($item);
					}else{
						static::$_instances[$id] = null;
					}
				}
			}

			//	Return from registry
			return static::$_instances[$id];
		}

		public static function findByObject($object, $options = []){
			$model_name = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
			$object_model_name = substr(get_class($object), strrpos(get_class($object), '\\') + 1);

			$method = 'getModels'.$model_name;
			if(!isset($object->ID)){
				return [];
			}elseif(method_exists($object, $method)){
				return $object->$method($options);
			}elseif(isset($object::$has_many[$model_name])){

				$options['where'][] = $object::$has_many[$model_name].'.'.$object::$id_field.' = '.$object->ID;

				if($object::$has_many[$model_name] != static::$table_name){
					$options['joins'] = array(
						$object::$has_many[$model_name] => static::$id_field
					);
				}

				return static::findAll($options);
			}elseif(isset(static::$has_many[$object_model_name])){

				$options['where'][] = static::$has_many[$object_model_name].'.'.$object::$id_field.' = '.$object->ID;

				if(static::$has_many[$object_model_name] != static::$table_name){
					$options['joins'] = array(
						static::$has_many[$object_model_name] => static::$id_field
					);
				}

				return static::findAll($options);
			}elseif(in_array($object::$id_field, static::$fields)){
				$options['where'][] = static::$table_name.'.'.$object::$id_field.' = '.$object->ID;

				return static::findAll($options);
			}

			return null;
		}

		public static function findOneByObject($object, $options = []){

			$options['limit'] = 1;

			$result = static::findByObject($object, $options);

			if(isset($result[0])){
				return $result[0];
			}

			return null;
		}

		public static function findFromSet(Array $set, Array $options = []){

			if(empty($set)){
				return [];
			}

			$ids = [];
			if(in_array(static::$id_field, $set[0]::$fields)){
				foreach($set as $item){
					$ids[] = $item->info[static::$id_field];
				}

				return static::findMany($ids, $options);
			}elseif(in_array($set[0]::$id_field, static::$fields)){
				foreach($set as $item){
					$ids[] = $item->ID;
				}

				$options = array_merge([
					'where' => [
						$set[0]::$id_field.' IN ('.implode(',', $ids).')'
					]
				], $options);

				return static::findAll($options);
			}
		}

		public function removeCache(){
			$key = Config::$config['cache']['main']['prefix'].'model:'.static::$table_name.':'.$this->ID;
			Cache::instance('main')->delete($key);

			unset(static::$_instances[$this->ID]);
		}

		public function copy(){
			$object = new static($this->info);

			unset($object->info[static::$id_field]);
			unset($object->info['date_added']);
			$object->ID = null;

			return $object;
		}

		public function validate(){
			return true;
		}

		public function getValidationErrors(){
			return $this->_validation_errors;
		}

		public function getParent($model){
			$static = get_called_class();
			$class = substr($static, 0, strrpos($static, '\\')) . '\\' . $model;

			return $class::find(isset($this->info[$class::$id_field]) ? $this->info[$class::$id_field] : null);
		}

		/*
		 * Collections not complete yet, do not use
		 * */
		public function getCollection($model){

			$static = get_called_class();
			if(!isset($this->collections[$model])){
				if($model[0] == '\\'){
					$model_class = $model;
				}else{
					$model_class = substr($static, 0, strrpos($static, '\\')) . '\\' . $model;
				}
				$this->collections[$model] = new Collection($model, $model_class::findByObject($this), $this);
			}

			return $this->collections[$model];
		}

		public function setCollection($model, $item_set){
			$this->collections[$model] = new Collection($model, $item_set, $this, true);
		}

		public function getExtension($type){
			if(!isset($this->extensions[$type])){
				$this->extensions[$type] = ModelExtension::findFor($this, $type);
			}

			return $this->extensions[$type];
		}

		public static function count($options){

			$options['fields'] = array('COUNT(1) AS `count`');
			$options['from'] = array(static::$table_name);

			$db_result = static::getDB()->select($options);
			if($db_result != null){
				return $db_result[0]['count'];
			}
		}
	}
}
