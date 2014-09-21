<?php

namespace Emylie\Routing
{
	interface Routeable
	{
		public function getTarget();
		
		public function discover(Ventureable $route);
	}
}