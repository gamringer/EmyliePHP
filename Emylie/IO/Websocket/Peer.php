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
			
			$socket = socket_accept($source);
			$s = intval($socket);
			
			static::$_instances[$s] = new static($socket);

			return static::$_instances[$s];
		}

		public function recv(){
			socket_recv($this->socket, $header, 1, MSG_WAITALL);
			socket_recv($this->socket, $length, 1, MSG_WAITALL);

			if((ord($header) & 0b00001111) == 0x8){
				$this->_dispatch([
					'name' => self::EV_DISCONNECT
				]);

				$this->_connected = false;

				return null;
			}

			$length = ord($length) & 127;

			if($length == 126) {
				socket_recv($this->socket, $length, 2, MSG_WAITALL);
				$r = unpack('n', $length);
				$length = $r[1];

			}elseif($length == 127) {
				socket_recv($this->socket, $length, 8, MSG_WAITALL);
				$v = unpack('N*', $length);
				$length = $v[1]<<32 | $v[2];
			}

			socket_recv($this->socket, $mask, 4, MSG_WAITALL);
			socket_recv($this->socket, $data, $length, MSG_WAITALL);

			$text = '';
			for ($i = 0; $i < strlen($data); ++$i) {
				$text .= $data[$i] ^ $mask[$i%4];
			}

			return $text;
		}

		public function send($data){

			socket_write($this->socket, $this->_encode($data));
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

		private function _handshake(){
			$bc = socket_recv($this->socket, $data, 2048, MSG_DONTWAIT);
			
			$version = 0;
			if(preg_match("/Sec-WebSocket-Version: (.*)\r\n/", $data, $match)){
				$version = $match[1];
			}
			
			if($version != 13) {
				return false;
			}

			// Extract header variables
			if(preg_match("/GET (.*) HTTP/", $data, $match)){
				$root = $match[1];
			}
			if(preg_match("/Host: (.*)\r\n/", $data, $match)){
				$host = $match[1];
			}
			if(preg_match("/Origin: (.*)\r\n/", $data, $match)){
				$origin = $match[1];
			}
			if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $data, $match)){
				$key = $match[1];
			}
			
			$acceptKeyBase = $key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
			$acceptKey = base64_encode(sha1($acceptKeyBase, true));

			$upgrade = "HTTP/1.1 101 Switching Protocols\r\n".
					   "Upgrade: websocket\r\n".
					   "Connection: Upgrade\r\n".
					   "Sec-WebSocket-Accept: $acceptKey".
					   "\r\n\r\n";
			
			socket_write($this->socket, $upgrade);

			return true;
		}

		public function poll(){
			while($this->_connected){
				$read = [$this->socket];
				$write = [];
				$except = [];

				$evc = socket_select($read, $write, $except, null);

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