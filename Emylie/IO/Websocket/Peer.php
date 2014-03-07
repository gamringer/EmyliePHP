<?php

namespace Emylie\IO\Websocket {
		
	use \Emylie\Exec\Process;
	
	class Peer{

		use \Emylie\Traits\Dispatcher;

		const EV_DISCONNECT = 1;
		const EV_MESSAGE = 2;

		public $socket;

		private $_connected = true;
		private $_server;
		private $_evBase;
		private $_event;
		
		static protected $_instances = [];

		public function __construct($server, $socket){
			$this->_server = $server;
			$this->socket = $socket;
		}

		static public function get($socket){
			
			$s = intval($socket);
			
			return isset(static::$_instances[$s]) ? static::$_instances[$s] : null;
		}

		static public function produce($server){

			$socket = stream_socket_accept($server->getSocket());
			$s = intval($socket);
			
			static::$_instances[$s] = new static($server, $socket);

			return static::$_instances[$s];
		}

		public function recv($header){
			$b2 = ord(fread($this->socket, 1));

			$masked = ($b2 & 0b10000000) == 0b10000000;
			$length = $b2 & 0b01111111;

			if($length == 126) {
				$length = fread($this->socket, 2);
				$r = unpack('n', $length);
				$length = $r[1];

			}elseif($length == 127) {
				$length = fread($this->socket, 8);
				$v = unpack('N*', $length);
				$length = $v[1]<<32 | $v[2];
			}
			
			if($masked){
				$mask = fread($this->socket, 4);
			}
			$text = '';
			if($length > 0){
				$data = fread($this->socket, $length);

				if($masked){
					for ($i = 0; $i < strlen($data); ++$i) {
						$text .= $data[$i] ^ $mask[$i%4];
					}
				}
			}
			
			return $text;
		}

		public function send($data){
			fwrite($this->socket, $this->_encode($data));
		}

		private function _encode($text, $last = true, $first = true){
			$length = strlen($text);

			$content = '';

			// Byte 1 (Text Node + FIN)
			$b1 = 0;
			if($first){
				$b1 = 0x1;
			}
			if($last){
				$b1 = $b1 | 0x80;
			}

			$content .=	 pack('C', $b1);

			//	Byte 2
			$b2 = 0x0;	//	Bit 1: No Mask
			if($length > 65535){
				$b2 |= 127;
				$content .=	 pack('C', $b2);
				$content .=	 pack('N*', $length>>32, $length);

			}elseif($length > 125){
				$b2 |= 126;
				$content .=	 pack('C', $b2);
				$content .=	 pack('n', $length);

			}else{
				$b2 |= $length;
				$content .=	 pack('C', $b2);
			}

			$content .= $text;

			return $content;
		}

		protected function _handshake(){

			$header = [];

			$i = 0;
			do {
				$line = stream_get_line($this->socket, 1024, "\r\n");

				if(preg_match('/GET (.*) HTTP\/([\d\.]+)/', $line, $match)){
					$root = $match[1];
					$httpversion = $match[2];
				
				}elseif(preg_match("/([\w\-]+): (.*)/i", $line, $match)){
					$header[$match[1]] = $match[2];
				}

			} while($line != '');
			
			if(
				!isset($header['Sec-WebSocket-Version'])
			 || !isset($header['Sec-WebSocket-Key'])
			 || $header['Sec-WebSocket-Version'] != 13
			) {
				return false;
			}
			
			$acceptKeyBase = $header['Sec-WebSocket-Key'].'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
			$acceptKey = base64_encode(sha1($acceptKeyBase, true));

			$upgrade = "HTTP/1.1 101 Switching Protocols\r\n".
					   "Upgrade: websocket\r\n".
					   "Connection: Upgrade\r\n".
					   "Sec-WebSocket-Accept: ".$acceptKey."\r\n".
					   "\r\n";
			
			fwrite($this->socket, $upgrade);

			return true;
		}

		private function _handleMessage(){
			$f = function($socket){

				do{
					$headerByte = ord(fread($socket, 1));

					if(feof($socket)){
						return $this->_close();
					}

					$opcodeBits = $headerByte & 0b00001111;
					$header = [
						'fin' => ($headerByte & 0b10000000) == 0b10000000,
						'rsv1' => ($headerByte & 0b01000000) == 0b01000000,
						'rsv2' => ($headerByte & 0b00100000) == 0b00100000,
						'rsv3' => ($headerByte & 0b00010000) == 0b00010000,
						'opcode' => $opcodeBits,

						'continuation' => $opcodeBits == 0x0,
						'text' => $opcodeBits == 0x1,
						'binary' => $opcodeBits == 0x2,
						'close' => $opcodeBits == 0x8,
						'ping' => $opcodeBits == 0x9,
						'pong' => $opcodeBits == 0xa,
					];

					$content = $this->recv($header);

					if($header['close']){
						return $this->_close();
					}
					
					$this->_dispatch([
						'name' => self::EV_MESSAGE,
						'message' => $content
					]);
				}while(stream_get_meta_data($socket)['unread_bytes'] > 0);
				
			};

			return $f->bindTo($this);
		}

		private function _close(){

			$this->_dispatch([
				'name' => self::EV_DISCONNECT
			]);

			$this->_connected = false;

			$this->_event->free();
			$this->_server->getEvBase()->stop();

			stream_socket_shutdown($this->socket, STREAM_SHUT_RDWR);
		}

		public function enableCrypto($mode){
			stream_set_blocking($this->socket, true);
			stream_socket_enable_crypto($this->socket, true, $mode, $this->socket);
		}

		public function start(){

			$this->_evBase = $this->_server->getEvBase();

			$this->_handshake();

			$this->_event = new \Event($this->_evBase, $this->socket, \Event::READ | \Event::PERSIST, $this->_handleMessage(), $this->socket);
			$this->_event->add();
		}

	}
}