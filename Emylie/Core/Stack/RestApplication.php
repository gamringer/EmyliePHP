<?php

namespace Emylie\Core\Stack {

	class RestApplication extends Application{

		public $data;

		public function _init(){
			$input = file_get_contents('php://input');
			$this->data = json_decode($input, true);
		}

		protected function _process($stack){

			$result = $stack->execute()->result;

			return json_encode($result);
		}
	}
}
