<?php

namespace Emylie\IO {

	use \Emylie\Traits\Dispatcher;

	class File{

		const EVENT_CHANGE = 'File.Change';

		use Dispatcher;

		private $_path;
		private $_stream;

		static public function open($path){
			return new static($path);
		}

		public function __construct($path){
			$this->_path = $path;
		}

		private function _getStream($mode){
			if(null == $this->_stream){
				$this->_stream = fopen($this->_path, $mode);
			}

			return $this->_stream;
		}

		private function _closeStream(){
			if(null != $this->_stream){
				fclose($this->_stream);
			}

			$this->_stream = null;
		}

		public function read(){
			$result = fread($this->_getStream('r'), filesize($this->_path));
			
			$this->_closeStream();

			return $result;
		}

		public function write($content){
			fwrite($this->_getStream('w'), $content);

			$this->_closeStream();
		}

		public function delete(){
			unlink($this->_path);
		}

		public function exists(){
			return is_file($this->_path);
		}

	}
}