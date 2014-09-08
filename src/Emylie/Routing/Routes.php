<?php

namespace Emylie\Routing
{
	interface Routes
	{
		public function getRoutes();
		
		public function clearRoutes();

		public function addRoute(Ventureable $route);

		public function route(Routeable $request);
	}
}