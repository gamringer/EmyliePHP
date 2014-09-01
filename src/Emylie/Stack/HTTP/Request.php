<?php

namespace Emylie\Stack\HTTP
{

	use \Emylie\Routing\Routeable;

	class Request implements Routeable
	{

		protected $verb;
		protected $path;
		protected $headers;
		protected $queryData;
		protected $bodyData;

		protected $rawQuery;
		protected $rawBody;

		public function __construct($verb, $path, $headers, Array $queryData, Array $bodyData)
		{
			$this->verb = $verb;
			$this->path = $path;
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
			$this->rawBody = $value;

			return $this;
		}

		public function getRawQuery()
		{
			return $this->rawQuery;
		}

		public function setRawQuery($value)
		{
			$this->rawQuery = $value;

			return $this;
		}

		public static function fromGlobals()
		{
			if(!isset($_SERVER['REQUEST_METHOD']) || !isset($_SERVER['REQUEST_URI'])){
				throw new \Exception('Can not parse globals');
			}
			
			$uriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
			$request = new static($_SERVER['REQUEST_METHOD'], $uriParts[0],
			                      apache_request_headers(), $_GET, $_POST);
			$request->setRawQuery($_SERVER['QUERY_STRING']);
			$request->setRawBody(fopen('php://input', 'r'));

			return $request;
		}
	}
}