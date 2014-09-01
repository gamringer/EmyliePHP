<?php

namespace Emylie\Routing
{
	class Dispatcher
	{
		public function __construct()
		{
			
		}

		public function dispatch(Dispatcheable $package)
		{
			$destination = $package->getRoute()->getDestination();
			$callable = $this->getCallable($destination);

			$callable($package);
			
			return $this;
		}

		protected function getCallable($destination)
		{
			if ($destination instanceof \Closure) {
				return $destination;
			}
			
			if (preg_match('/^(?<class>[\w\\\\]+)(?<type>\-\>|::)(?<method>[\w\\\\]+)$/', $destination, $match)) {
				if ($match['type'] == '->') {
					return [new $match['class'](), $match['method']];
				}

				elseif ($match['type'] == '::') {
					return [$match['class'], $match['method']];
				}
			}

			throw new \Exception('[Dispatcher] could not dispatch [Dispatcheable] to destination: '.$destination);
		}
	}
}