<?php

namespace Emylie\Stack\HTTP
{

	use \Emylie\Routing\Routeable;
	use \Emylie\Routing\Ventureable;

	class Request implements Routeable
	{

		protected $verb;
		protected $path;
		protected $headers;
		protected $queryData;
		protected $bodyData;
		protected $routeData;
		protected $route;

		protected $rawQuery = '';
		protected $rawBody = '';

		public function __construct($verb, $path, Array $headers = [],Array $queryData = [], Array $bodyData = [])
		{
			$this->verb = (string) $verb;
			$this->path = (string) $path;
			$this->headers = $headers;
			$this->queryData = $queryData;
			$this->bodyData = $bodyData;
		}

		public function getTarget()
		{
			return $this->verb.' '.$this->path;
		}

		public function getRawBody()
		{
			return $this->rawBody;
		}

		public function setRawBody($value)
		{
			$this->rawBody = (string) $value;

			return $this;
		}

		public function getRawQuery()
		{
			return $this->rawQuery;
		}

		public function setRawQuery($value)
		{
			$this->rawQuery = (string) $value;

			return $this;
		}

		public function getRoute()
		{
			return $this->route;
		}

		public function getRouteData()
		{
			return $this->routeData;
		}

		public function discover(Ventureable $route)
		{
			if($route->match($this->getTarget(), $this->routeData)){
				$this->route = $route;

				return true;
			}

			return false;
		}

		public static function fromGlobals()
		{
			if (
			    !isset($_SERVER['REQUEST_METHOD'])
			 || !isset($_SERVER['REQUEST_URI'])
			){
				throw new Exception('Can not parse globals');
			}

			$uriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
			$request = new static($_SERVER['REQUEST_METHOD'], $uriParts[0],
			                      static::getGlobalHeaders(), $_GET, $_POST);
			if(isset($uriParts[1])){
				$request->setRawQuery($uriParts[1]);
			}
			$request->setRawBody(fopen('php://input', 'r'));

			return $request;
		}

		public static function getGlobalHeaders()
		{
			return getallheaders();
		}
	}
}