<?php

namespace Emylie\Routing
{
	class Router
	{

		protected $routes = [];

		public function __construct()
		{
			
		}

		public function addRoute(Ventureable $route)
		{
			$this->routes[$route->getName()] = $route;

			return $this;
		}

		public function route(Routeable $request)
		{
			$extract = null;
			foreach ($this->routes as $route) {
				if($route->match($request->getTarget(), $extract)){
					return new Package($extract, $route, $request);
				}
			}

			throw new \Exception('No [Route] found to match the [Routeable]');
		}
	}
}