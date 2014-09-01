<?php

namespace Emylie\Routing
{
	class Package implements Dispatcheable
	{

		protected $data;
		protected $route;
		protected $request;

		public function __construct(Array $data, Ventureable $route, Routeable $request)
		{
			$this->data = $data;
			$this->route = $route;
			$this->request = $request;
		}

		public function getData()
		{
			return $this->data;
		}

		public function getRoute()
		{
			return $this->route;
		}

		public function getRequest()
		{
			return $this->request;
		}
	}
}