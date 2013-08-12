<?php
namespace Emylie\Traits {
	Trait Dispatcher{
		private $_callbacks = [];

		protected function _dispatch($event){
			if(!isset($this->_callbacks[$event['name']])){
				return;
			}

			$event['target'] = $this;

			foreach($this->_callbacks[$event['name']] as $callback){
				$callback($event);
			}
		}

		public function listen($name, Callable $callback){
			$this->_callbacks[$name][] = $callback;
		}
	}
}