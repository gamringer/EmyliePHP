<?php

namespace Emylie\Routing
{
	interface Ventureable
	{
		public function getName();

		public function match($target, &$extract = null);
	}
}