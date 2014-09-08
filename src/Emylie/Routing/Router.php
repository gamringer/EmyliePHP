<?php

namespace Emylie\Routing
{
	class Router implements Routes
	{

		protected $routes = [];

		public function __construct()
		{
			
		}

		public function clearRoutes()
		{
			$this->routes = [];
		}

		public function getRoutes()
		{
			return $this->routes;
		}

		public function addRoute(Ventureable $route)
		{
			$this->routes[$route->getName()] = $route;

			return $this;
		}

		public function route(Routeable $request, &$extract = null)
		{
			foreach ($this->routes as $route) {
				if($route->match($request->getTarget(), $extract)){
					return $route;
				}
			}

			throw new Exception('No [Route] found to match the [Routeable]');
		}
	}
}