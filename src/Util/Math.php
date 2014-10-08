<?php

namespace Emylie\Util {

	class Math {

		public static function compound($amount, $usury, $duration, $rate = 0){
			if($rate == 0){
				return $amount * pow(M_E, $usury * $duration);
			}else{
				return $amount * pow(1 + ($usury / $rate), $duration * $rate);
			}
		}

	}

}