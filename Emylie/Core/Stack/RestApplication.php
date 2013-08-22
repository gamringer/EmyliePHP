<?php

namespace Emylie\Core\Stack {

	class RestApplication extends Application{

		public $data;
		public $input;

		private $_keyPair = null;
		private $_router;
		private $_route;

		public function _init(){
			$this->input = file_get_contents('php://input');
			$this->data = json_decode($this->input, true);

			$this->_router = new Router();
			$this->_router->register(new Route('/:controller/:id/:extension/*', ['action' => 'default'], function($data){
				if((int)$data['id'] == 0){
					return false;
				}

				return true;
			}));
			$this->_router->register(new Route('/:controller/:id/*', ['action' => 'default'], function($data){
				if((int)$data['id'] == 0){
					return false;
				}

				return true;
			}));
			$this->_router->register(new Route('/:controller/:action/*'));
			$this->_router->register(new Route('/*', ['controller'=>'Error', 'action'=>'404']));
		}

		public final function execute($command){

			if($this->_secure && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != 'on')){
				header('HTTP/1.1 403 Forbidden', 403);
				exit;
			}

			$this->_route = $this->protectRoute($this->_router->route($command));

			$stack = new Stack($this->_route['controller'], strtolower($_SERVER['REQUEST_METHOD']).'_'.$this->_route['action']);
			if($stack->getStatus() != 200){
				if($stack->getStatus() == 404){
					$stack = new Stack('Error', '404');
				}
			}

			return $this->_process($stack);
		}

		public function getRoute(){
			return $this->_route;
		}

		public function getKeyPair(){
			return $this->_keyPair;
		}

		protected function protectRoute($route){

			if(
				$this->_restricted &&
				!in_array($route['controller'].'::'.$route['action'], $this->_public_actions) &&
				!in_array($route['controller'].'::*', $this->_public_actions)
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
					$route = ['controller' => 'Error', 'action' => '400'];
				}elseif($headers['X-Timestamp'] < time() - 10){
					$route = ['controller' => 'Error', 'action' => '410'];
				}else{
					$at = $this->_access_type;
					$kp = $at::findOne([
						'where' => [
							'public = '. $at::getDB()->escape($headers['X-Public'])
						]
					]);
					if($kp === null || !$kp->active){
						$route = ['controller' => 'Error', 'action' => '403'];
					}else{
						$ctx = hash_init('sha512', HASH_HMAC, $kp->private);
						hash_update($ctx, $_SERVER['REQUEST_URI']);
						hash_update($ctx, $this->input);
						hash_update($ctx, $headers['X-Timestamp']);
						$hash = hash_final($ctx);
						if($hash != $headers['X-Digest']){
							$route = ['controller' => 'Error', 'action' => '400'];
						}
						$this->_keyPair = $kp;
					}
				}
			}

			return $route;
		}

		protected function _process($stack){

			$result = $stack->execute()->result;

			return json_encode($result);
		}
	}
}
