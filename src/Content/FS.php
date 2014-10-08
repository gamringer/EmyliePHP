<?php

namespace Emylie\Content {

	class FS{

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

		static public function relativeFromAbsolute($to, $from = null){
			if($from == null){
				$from = getcwd();
			}

			$tparts = explode('/', substr($to, 1));
			$fparts = explode('/', substr($from, 1));

			$separated = false;
			$result = '.';
			while($fpart = array_shift($fparts)){
				$tpart = array_shift($tparts);
				if($separated || $fpart != $tpart){
					$separated = true;
					$result = '../'.$result;
					if($tpart != null){
						$result .= '/'.$tpart;
					}
				}
			}
			if(isset($tparts[0])){
				$result .= '/'.implode('/', $tparts);
			}

			return preg_replace('/(^|\/)\.(\/|$)/', '', $result);
		}

	}
}