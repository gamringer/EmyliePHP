<?php

namespace Emylie\Core\Stack {

	class RestApplication extends Application{

		public $data;

		public function _init(){
			$this->input = file_get_contents('php://input');
			$this->data = json_decode($this->input, true);
		}

		protected function _getStack($controller, $action){

			if(
				$this->_restricted &&
				!in_array($controller.'::'.$action, $this->_public_actions) &&
				!in_array($controller.'::*', $this->_public_actions)
			){
				$headers = getallheaders();

				if(
					!isset($headers['X-Timestamp'])
				 || isset($headers['X-Timestamp'][10])
				 || !isset($headers['X-Public'])
				 || isset($headers['X-Public'][128])
				 || !isset($headers['X-Digest'])
				 || isset($headers['X-Digest'][128])
				) {
					$controller = 'Error';
					$action = '400';
				}elseif($headers['X-Timestamp'] < time() - 10){
					$controller = 'Error';
					$action = '410';
				}else{
					$at = $this->_access_type;
					$kp = $at::findOne([
						'where' => [
							'public = '. $at::getDB()->escape($headers['X-Public'])
						]
					]);
					if($kp === null || !$kp->active){
						$controller = 'Error';
						$action = '403';
					}else{
						$ctx = hash_init('sha512', HASH_HMAC, $kp->private);
						hash_update($ctx, $_SERVER['REQUEST_URI']);
						hash_update($ctx, $this->input);
						$hash = hash_final($ctx);
						if($hash != $headers['X-Digest']){
							$controller = 'Error';
							$action = '400';
						}
					}
				}
			}
			
			$stack = new Stack($controller, $action);

			if($stack->getStatus() != 200){
				if($stack->getStatus() == 404){
					$stack = new Stack('Error', '404');
				}
			}

			return $stack;
		}

		protected function _process($stack){

			$result = $stack->execute()->result;

			return json_encode($result);
		}
	}
}
