<?php

namespace Emylie\Example\Resources\Views
{
	class DefaultView
	{
		public function dynamicMethod(){
			echo 'This is a Dynamic Method' . PHP_EOL;
		}

		public static function staticMethod(){
			echo 'This is a Static Method' . PHP_EOL;
		}
	}
}