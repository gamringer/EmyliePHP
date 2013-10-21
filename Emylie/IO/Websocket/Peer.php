<?php

namespace Emylie\IO\Websocket {
		
	class Peer{

		use \Emylie\Traits\Dispatcher;

		const EV_DISCONNECT = 1;
		const EV_MESSAGE = 2;

		public $socket;

		private $_connected = true;
		
		static protected $_instances = [];

		public function __construct($socket){
			$this->socket = $socket;

			$this->_handshake();
		}

		static public function get($socket){
			
			$s = intval($socket);
			
			return isset(static::$_instances[$s]) ? static::$_instances[$s] : null;
		}

		static public function produce($source){

			$socket = stream_socket_accept($source);
			$s = intval($socket);
			
			static::$_instances[$s] = new static($socket);

			return static::$_instances[$s];
		}

		public function recv(){
			$header = fread($this->socket, 1);
			$length = fread($this->socket, 1);

			if((ord($header) & 0b00001111) == 0x8){
				$this->_dispatch([
					'name' => self::EV_DISCONNECT
				]);

				$this->_connected = false;

				return null;
			}
			$length = ord($length) & 127;

			if($length == 126) {
				$length = fread($this->socket, 2);
				$r = unpack('n', $length);
				$length = $r[1];

			}elseif($length == 127) {
				$length = fread($this->socket, 8);
				$v = unpack('N*', $length);
				$length = $v[1]<<32 | $v[2];
			}
			
			if($length == 0){
				return '';
			}

			$mask = fread($this->socket, 4);
			$data = fread($this->socket, $length);

			$text = '';
			for ($i = 0; $i < strlen($data); ++$i) {
				$text .= $data[$i] ^ $mask[$i%4];
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

		public function poll(){
			while($this->_connected){
				$read = [$this->socket];
				$write = [];
				$except = [];

				if(stream_select($read, $write, $except, 1) > 0){
					foreach($read as $socket){
						$content = $this->recv();
							
						if($content == null){
							break;
						}
						$this->_dispatch([
							'name' => self::EV_MESSAGE,
							'message' => $content
						]);
					}
				}

			}
		}

	}
}