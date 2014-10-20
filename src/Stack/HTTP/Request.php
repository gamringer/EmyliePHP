<?php

namespace Emylie\Stack\HTTP
{

	use \Emylie\Routing\Routeable;
	use \Emylie\Routing\Ventureable;
	use \Psr\Http\Message\StreamableInterface;

	class Request implements Routeable
	{

		protected $method;
		protected $path;
		protected $headers;
		protected $query;
		protected $body;
		protected $attributes = [];
		protected $queryParams = [];

		public function __construct($method, $path, Array $headers = [], Array $queryParams = [])
		{
			$this->method = (string) $method;
			$this->path = (string) $path;
			$this->headers = $headers;
			$this->queryParams = $queryParams;
		}

		public function setMethod($method)
		{
			return $this->method = (string) $method;
		}

		public function getMethod()
		{
			return $this->method;
		}

		public function setPath($path)
		{
			return $this->path = (string) $path;
		}

		public function getPath()
		{
			return $this->path;
		}

		public function getBody()
		{
			return $this->body;
		}

		public function setBody(StreamableInterface $value)
		{
			$this->body = $value;

			return $this;
		}

		public function getQuery()
		{
			if ($this->query == null) {
				$this->query = http_build_query($this->queryParams);
			}

			return $this->query;
		}

		public function setQuery($query)
		{
			$this->queryParams = [];

			return $this->query = (string) $query;
		}

		public function getQueryParams()
		{
			return $this->queryParams;
		}

		public function setQueryParams(Array $params)
		{
			$this->query = null;
			$this->queryParams = $params;

			return $this;
		}

		public function addQueryParams(Array $params)
		{
			$this->queryParams = array_merge($this->queryParams, $params);

			return $this;
		}

		public function getAttributes()
		{
			return $this->attributes;
		}

		public function setAttributes(\ArrayAccess $attributes)
		{
			return $this->attributes = $attributes;
		}

		public function discover(Ventureable $route, callable $scope)
		{
			$extract = null;
			if($route->match($scope($this), $extract)){
				$this->attributes = array_merge($extract, $this->attributes);

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
				$request->setQuery($uriParts[1]);
			}
			$request->setBody(new StreamReader(fopen('php://input', 'r')));

			return $request;
		}

		public static function getGlobalHeaders()
		{
			return getallheaders();
		}
	}
}