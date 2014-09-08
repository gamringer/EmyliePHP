<?php

namespace Emylie\Routing
{
	class Dispatcher
	{

		protected $router;

		public function __construct(Routes $router)
		{
			$this->router = $router;
		}

		public function dispatch(Routeable $request)
		{
			$extract = null;
			$route = $this->router->route($request, $extract);
			
			$destination = $route->getDestination();
			$callable = $this->getCallable($destination);

			return $callable($request);
		}

		protected function getCallable($destination)
		{
			if ($destination instanceof \Closure) {
				return $destination;
			}
			
			if (preg_match('/^(?<class>[\w\\\\]+)(?<type>\-\>|::)(?<method>[\w\\\\]+)$/', $destination, $match)) {
				$return = [];
				if ($match['type'] == '->') {
					$return[] = new $match['class']();
				
				}elseif ($match['type'] == '::') {
					$return[] = $match['class'];
				}
				
				$return[] = $match['method'];

				return $return;
			}

			throw new Exception('[Dispatcher] could not dispatch [Dispatcheable] to destination: '.$destination);
		}
	}
}