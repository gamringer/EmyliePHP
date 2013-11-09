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
		private $_evBase;
		private $_event;
		private $_master;

		public function __construct($address, $certificate = null, $handler = null){
			$this->_address = $address;

			$this->_context = stream_context_create();
			if(substr($address, 0, 3) == 'ssl'){
				stream_context_set_option($this->_context, 'ssl', 'local_cert', $certificate);
				stream_context_set_option($this->_context, 'ssl', 'verify_peer', false);
			}
		}

		public function start(){
			
			$this->_master = stream_socket_server($this->_address, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $this->_context);

			$this->_evBase = new \EventBase();

			$this->_event = new \Event($this->_evBase, $this->_master, \Event::READ | \Event::PERSIST, $this->_handleConnect(), $this->_master);
			$this->_event->add();

			$this->_evBase->loop();
		}

		public function getSocket(){
			return $this->_master;
		}

		public function getEvBase(){
			return $this->_evBase;
		}

		private function _handleConnect(){
			$f = function($master){
				$peer = Peer::produce($this);

				$pid = pcntl_fork();
				if($pid == 0){
					$this->_handlePeer($peer);
				}
			};

			return $f->bindTo($this);
		}

		private function _handlePeer($peer){

			$this->_evBase->reInit();
			$this->_event->free();

			$this->_dispatch([
				'name' => self::EV_PEER_CONNECT,
				'peer' => $peer
			]);

			$peer->start();
		}
	}	
}