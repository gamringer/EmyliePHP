<?php

namespace Emylie\Core {

	class AssetsManager{

		private static $css = array('_default'=>array());
		private static $js = array('_default'=>array());

		public static function addCSS($path, $group = '_default'){
			if(!in_array($path, self::$css[$group])){
				self::$css[$group][] = $path;
			}
		}

		public static function getCSS($pGroups = null){

			$result = array();

			if(is_null($pGroups)){
				$groups = array_keys(self::$css);
			}elseif(is_array($pGroups)){
				$groups = $pGroups;
			}else{
				$groups = array($pGroups);
			}

			foreach($groups as $group){
				foreach(self::$css[$group] as $path){
					$result[] = '<link rel="stylesheet" type="text/css" href="'.$path.'" />';
				}
			}

			return implode(chr(13), $result);
		}

		public static function addJS($path, $group = '_default'){
			if(!in_array($path, self::$js[$group])){
				self::$js[$group][] = $path;
			}
		}

		public static function getJS($pGroups = null){
			$result = array();

			if(is_null($pGroups)){
				$groups = array_keys(self::$js);
			}elseif(is_array($pGroups)){
				$groups = $pGroups;
			}else{
				$groups = array($pGroups);
			}

			foreach($groups as $group){
				foreach(self::$js[$group] as $path){
					$result[] = '<script type="text/javascript" src="'.$path.'"></script>';
				}
			}

			return implode(chr(13), $result);
		}

	}
}
