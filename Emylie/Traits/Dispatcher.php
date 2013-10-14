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

		public function listen($name, Callable $callback, $context = null){

			if($context = null){
				$context = $this;
			}

			$callback->bindTo($context);

			$this->_callbacks[$name][] = $callback;
		}
	}
}