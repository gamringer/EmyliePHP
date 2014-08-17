<?php

namespace Emylie {

	class Number {

		public static function unit($amount, $unit, $binary = true, $number_format = [], $separator = ' '){

			if ($amount == 0) {
				array_unshift($number_format, 0);
			
				return call_user_func_array('number_format', $number_format);
			}

			$positive = true;
			if ($amount < 0) {
				$positive = false;
				$amount = abs($amount);
			}

			if ($binary) {
				$prefixIndex = floor((int)(string)log($amount, 2)/10);
				$amount /= pow(2, 10 * $prefixIndex);
				$prefixes = ['','Ki','Mi','Gi','Ti','Pi','Ei','Zi','Yi'];

			} elseif($amount > 1) {
				$prefixIndex = floor((int)(string)log($amount, 10)/3);
				$amount /= pow(10, 3 * $prefixIndex);
				$prefixes = ['','K','M','G','T','P','E','Z','Y'];

			}else{
				$prefixIndex = abs(floor((int)(string)log($amount, 10)/3));
				$amount *= pow(10, 3 * $prefixIndex);
				$prefixes = ['','m','µ','n','p','f','a','z','y'];
			}

			$amount *= $positive ? 1 : -1;

			array_unshift($number_format, $amount);
			$result = call_user_func_array('number_format', $number_format);

			$result .= $separator . $prefixes[$prefixIndex] . $unit;
			
			return $result;
		}

		public static function packU64($value){
			
			$higherV = ($value & 0xffffffff00000000) >> 32;
			$lowerV  = ($value & 0x00000000ffffffff);

			return pack('NN', $higherV, $lowerV);
		}

		public static function unpackU64($packed){
			
			list($higherV, $lowerV) = array_values(unpack('N2', $packed)); 
			
			return $higherV << 32 | $lowerV;
		}

	}

}