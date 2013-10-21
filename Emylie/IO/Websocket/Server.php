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

		private $_address;
		private $_handler;

		public function __construct($address, $certificate, $handler = null){
			$this->_address = $address;

			$this->_context = stream_context_create();
			if(substr($address, 0, 3) == 'ssl'){
				stream_context_set_option($this->_context, 'ssl', 'local_cert', '/home/wilhelm/clicko.com.pem');
				stream_context_set_option($this->_context, 'ssl', 'verify_peer', false);
			}
		}

		public function start(){
			$peers = [];

			// socket creation
			$master = stream_socket_server($this->_address, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $this->_context);

			$sockets = [$master];
			while(true){
				$read = $sockets;
				$write = [];
				$except = [];

				if(@stream_select($read, $write, $except, 2) > 0){
					foreach($read as $socket){
						$this->_startPeer($socket);
					}
				}

				Process::checkRelease();
			}	
		}

		private function _startPeer($socket){
			$peer = Peer::produce($socket);

			$fork = Process::fork()->run(function() use($peer) {
				$this->_dispatch([
					'name' => self::EV_PEER_CONNECT,
					'peer' => $peer
				]);

				$peer->poll();
			}, $this);
		} 
	}
}