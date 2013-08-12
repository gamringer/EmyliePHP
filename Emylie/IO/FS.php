<?php

namespace Emylie\Content {

	class File{

		private $_path;

		static public function open($path){
			return new static($path);
		}

		public function __construct($path){
			$this->_path = $path;
		}

		static public function write($path, $content) {
			$dir = dirname($path);
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}

			$fs = fopen($path, 'w');
			fwrite($fs, $content);
			fclose($fs);
		}

		static public function read($path, $size = 0) {
			if(!is_file($path)){
				return null;
			}

			$fs = fopen($path, 'r');
			$c = fread($fs, $size == 0 ? filesize($path) : $size);
			fclose($fs);

			return $c;
		}

		static public function log($path, $content) {
			$dir = dirname($path);
			if(!is_dir($dir)){
				mkdir($dir, 0777, true);
			}

			$fs = fopen($path, 'a');
			fwrite($fs, $content."\n");
			fclose($fs);
		}

	}
}