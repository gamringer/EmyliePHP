<?php

namespace Emylie\Core\Data {

	class Collection implements \IteratorAggregate, \Countable{

		public function getIterator(){
			return new \ArrayIterator($this->elements);
		}

		public function count(){
			return count($this->elements);
		}

		public $owner;
		public $type;
		public $elements = [];
		public $added = [];
		public $removed = [];
		public $ids = [];
		public $original_ids = [];

		private $_updated;

		public function __construct($model, $elements = null, $owner = null, $initial = false){

			$this->owner = $owner;
			$this->type = $model;

			$this->setElements($elements);

			$this->_updated = $initial;
			$this->original_ids = $this->ids;
		}

		public function add($element){
			$this->elements[] = $element;
			$this->added[] = $element;

			$this->_updated = true;

			return $this;
		}

		public function remove($element){
			foreach($this->elements as $index => $existing_element){
				if($existing_element->ID == $element->ID){
					array_splice($this->elements, $index, 1);
					break;
				}
			}

			$this->removed[] = $element;

			$this->_updated = true;

			return $this;
		}

		public function setElements($elements){
			$ids = array();
			if($elements != null){
				foreach($elements as $element){
					$ids[] = $element->ID;
				}
			}

			if($this->ids == $ids){
				return $this;
			}

			$this->ids = $ids;
			$this->elements = is_array($elements) ? $elements : array();

			$this->_updated = true;

			return $this;
		}

		public function save(){

			if(!$this->_updated){
				return;
			}

			$owner = $this->owner;
			foreach($this->added as $added){
				$added->{$owner::$id_field} = $owner->ID;
				$added->save();
			}
			$this->added = [];

			$this->_updated = false;
		}

	}

}