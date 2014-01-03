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
		private $_certificate;

		public function __construct($address, $certificate = null, $passphrase = null){
			$this->_address = $address;

			$this->_context = stream_context_create();
			if($certificate != null){
				$this->_certificate = $certificate;
				stream_context_set_option($this->_context, 'ssl', 'local_cert', $certificate);
				stream_context_set_option($this->_context, 'ssl', 'verify_peer', false);
				if($passphrase != null){
					stream_context_set_option($this->_context, 'ssl', 'passphrase', $passphrase);
				}
			}
		}

		public function start(\EventBase $base = null, $loop = true){
			
			if($base == null){
				$base = new \EventBase();
			}
			$this->_evBase = $base;

			$this->_master = stream_socket_server($this->_address, $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $this->_context);

			$this->_event = new \Event($this->_evBase, $this->_master, \Event::READ | \Event::PERSIST, $this->_handleConnect(), $this->_master);
			$this->_event->add();

			if($loop){
				$this->_evBase->loop();
			}
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

				pcntl_signal(SIGCHLD, SIG_IGN);
				$pid = pcntl_fork();
				if($pid == 0){
					$this->_handlePeer($peer);
				}

			};

			return $f->bindTo($this);
		}

		private function _handlePeer($peer){

			if(isset($this->_certificate)){
				$peer->enableCrypto(STREAM_CRYPTO_METHOD_TLS_SERVER);
			}

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