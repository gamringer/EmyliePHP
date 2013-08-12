<?php

namespace Emylie\Content {

	class Image {

		public static function resize($pathFrom, $pathTo, $width, $height, $mode = 'fit'){

			if(!is_file($pathFrom) || !is_writable(dirname($pathTo))){
				return false;
			}
			list($ow, $oh) = getimagesize($pathFrom);
			$oratio = $ow / $oh;

			$crop = '';
			$scaleMod = '';
			$nratio = $width / $height;
			if($mode == 'exact'){
				$scaleMod = '!';

			}elseif($mode == 'fit-force'){
				$scaleMod = ' -gravity center -extent '.$width.'x'.$height;

			}elseif($mode == 'fill'){
				$scaleMod = '^ -gravity center -extent '.$width.'x'.$height;
			}

			$animatedGifAddon = '';
			if(substr($pathFrom, -4) == '.gif' && substr($pathTo, -4) != '.gif'){
				$animatedGifAddon = '[0]';
			}elseif(substr($pathFrom, -4) == '.gif' && substr($pathTo, -4) == '.gif'){
				$animatedGifAddon = ' -coalesce';
			}

			$cmd = 'convert '.$pathFrom.$animatedGifAddon.' -scale '.$width.'x'.$height.$scaleMod.' '.$pathTo;
			$result = shell_exec($cmd);

			return true;
		}


	}

}