<?php

namespace Emylie\IO {
		
	class EventCollection{

		private $_events;

		public function __construct(){
			$this->_events = [];
		}

		public function add(\Event $e){
			$this->_events[] = $e;
		}

		public function purge(){
			while($e = array_shift($this->_events)){
				$e->free();
			}
		}

	}

}