<?php

namespace Emylie\DI
{
	Trait ServiceLocator
	{

		protected $services = [];

		public function setService($name, Service $service)
		{
			$this->service[$name] = $service;

			return $this;
		}

		public function getService($name)
		{
			if (!isset($this->service[$name])) {
				throw new Exception('Service '.$name.' is not registered');
			}

			return $this->service[$name];
		}
	}
}