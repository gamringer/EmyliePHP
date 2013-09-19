<?php

namespace Emylie {

	class Math {

		public static function unit($amount, $unit, $decimals = 2, $binary = true, $separator = ' ', $minPower = 0){

			$positive = $amount >= 0;
			if ($amount == 0) {
				return round($amount, $decimals);
			}elseif ($amount < 0) {
				$amount = abs($amount);
			}

			if ($binary) {
				$prefixIndex = $minPower >= floor(log($amount, 2)) ? 0 : floor(log($amount, 2)/10);
				$amount /= pow(2, 10 * $prefixIndex);
				$prefixes = ['','Ki','Mi','Gi','Ti','Pi','Ei','Zi','Yi'];
			} elseif($amount > 1) {
				$prefixIndex = $minPower >= floor(log($amount, 10)) ? 0 : floor(log($amount, 10)/3);
				$amount /= pow(10, 3 * $prefixIndex);
				$prefixes = ['','K','M','G','T','P','E','Z','Y'];
			}else{
				$prefixIndex = $minPower >= abs(floor(log($amount, 10))) ? 0 : abs(floor(log($amount, 10)/3));
				$amount *= pow(10, 3 * $prefixIndex);
				$prefixes = ['','m','µ','n','p','f','a','z','y'];
			}

			$amount *= $positive ? 1 : -1;

			return number_format($amount, $decimals) . $separator . $prefixes[$prefixIndex] . $unit;
		}

		public static function compound($amount, $usury, $duration, $rate = 0){
			if($rate == 0){
				return $amount * pow(M_E, $usury * $duration);
			}else{
				return $amount * pow(1 + ($usury / $rate), $duration * $rate);
			}
		}

		public static function fCurrency($amount, $iso, $locale = null, $showIso = false, $dec = 2){

			$symbol = '';
			if(in_array($iso, ['cad','usd','aud'])){
				$symbol = '$';
			}

			if($locale == null){
				$locale = 'fr_CA';
			}

			if($locale == 'fr_CA'){
				return number_format($amount, $dec, ',', ' ').$symbol.($showIso ? strtoupper($iso) : '');

			}elseif($locale == 'en_US'){
				return ($amount < 0 ? '-' : '').$symbol.number_format(abs($amount), $dec, '.', ',').($showIso ? strtoupper($iso) : '');

			}
		}

		public static function packU64($value){
			
			return pack('N*', $value>>32, $value|(-1>>32));
		}

		public static function unpackU64($value){
			
			$v = unpack('N*', $length);

			return $v[1]<<32 | $v[2];
		}

	}

}