<?php

namespace Emylie\Core\Data {

	class ModelExtension {

		public $owner;
		public $type;
		public $info;
		public $ID;

		public function __construct($info, $owner, $type){

			$this->owner = $owner;
			$this->info = $info;
			$this->type = $type;

			if(sizeof($this->info) == 1 && isset($this->info[$owner::$id_field])){
				foreach($owner::$registreable_extensions[$type] as $field){
					$info[0][$field] = null;
				}
			}elseif(isset($this->info[$owner::$id_field])){
				$this->ID = $this->info[$owner::$id_field];
			}

		}

		public function save(){
			if(null === $this->ID){
				$this->create();
			}else{
				$this->update();
			}
		}

		public function create(){
			$owner = $this->owner;

			$fields = [];
			foreach($this->info as $field => $value){
				if(null !== $value){
					$fields[$field] = $owner::getDB()->escape($value);
				}
			}

			if(!empty($fields)){
				$this->ID = SQL::db($owner::$i_name)->insert($owner::$table_name.'_'.$this->type, $fields);
			}

			$this->info[$owner::$id_field] = $this->ID;
		}

		public function update(){

			$owner = $this->owner;

			$statements['table_name'] = $owner::$table_name.'_'.$this->type;
			$statements['where'] = array($owner::$id_field.' = "'.$this->ID.'"');
			$statements['set'] = array();

			foreach($this->info as $index => $value){
				if(in_array($index, $owner::$registreable_extensions[$this->type])){
					$statements['set'][] = $index.' = '.$owner::getDB()->escape($value);
				}
			}

			SQL::db($owner::$i_name)->update($statements);

			//	Clear Cache
			Cache::instance('main')->delete('model:'.$owner::$table_name.'_'.$this->type.':'.$this->ID);
		}

		public function __get($var_name){
			if(isset($this->info[$var_name])){
				return $this->info[$var_name];
			}
		}

		public function __set($var_name, $value){
			$this->info[$var_name] = $value;
		}

		public static function findFor($item, $type){
			if(isset($item::$registreable_extensions[$type])){

				if(!isset($itemthis->ID) || (int) $item->ID == 0){
					$info = [];

				}else{
					$info = SQL::db($item::$i_name)->selectOne(array(
						'fields' => $item::$registreable_extensions[$type],
						'from' => array($item::$table_name.'_'.$type),
						'where' => array(
							$item::$id_field.' = '.$item->ID
						),
						'limit' => 1
					));

					if($info == null){
						$info = [];
					}

					$info[$item::$id_field] = $item->ID;
				}

				$result = new static($info, $item, $type);
			} else {
				$result = null;
			}

			return $result;
		}

	}

}