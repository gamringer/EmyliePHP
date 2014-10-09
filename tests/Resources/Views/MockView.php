<?php

namespace Emylie\Test\Resources\Views
{
	class MockView
	{
		public function dynamicMethod(){
			return __METHOD__;
		}

		public static function staticMethod(){
			return __METHOD__;
		}
	}
}