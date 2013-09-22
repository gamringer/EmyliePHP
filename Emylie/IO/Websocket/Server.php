<?php

namespace Emylie\IO\Websocket {

	use \Emylie\Exec\Process;

	Class Server {

		use \Emylie\Traits\Dispatcher;

		const EV_PEER_CONNECT = 1;

		const PEER_HANDLER_DEFAULT = 1;
		const PEER_HANDLER_BASIC = 1;
		const PEER_HANDLER_PCNTL = 2;
		const PEER_HANDLER_PTHREAD = 3;

		private $_ip;
		private $_port;
		private $_handler;

		public function __construct($address, $handler = null){
			list($this->_ip, $this->_port) = explode(':', $address);

			if($handler == null){

			}
		}

		public function start(){
			$peers = [];

			// socket creation
			$master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, true);

			if(!is_resource($master)){exit;}
			if(!socket_bind($master, $this->_ip, $this->_port)){exit;}
			if(!socket_listen($master)){exit;}

			$sockets = [$master];

			while(true){
				$read = $sockets;
				$write = [];
				$except = [];

				$evc = socket_select($read, $write, $except, null);

				foreach($read as $socket){
					$this->_startPeer($socket);
				}
			}	
		}

		private function _startPeer($socket){
			$peer = Peer::produce($socket);
			$action = function() use($peer) {
				$this->_dispatch([
					'name' => self::EV_PEER_CONNECT,
					'peer' => $peer
				]);

				$peer->poll();
			};

			$fork = Process::fork();
			$fork->run($action, $this);
		} 
	}
}