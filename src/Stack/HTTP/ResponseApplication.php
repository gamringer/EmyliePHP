<?php

namespace Emylie\Stack\HTTP
{

	use \Emylie\Stack\Application;

	class ResponseApplication extends Application
	{

		//	Routing Services
		const SERVICE_ROUTING = 'routing';
		const SERVICE_DISPATCHING = 'dispatching';

		//	Data Services
		const SERVICE_PERSISTANCE = 'persistance';
		const SERVICE_CACHE = 'cache';

		public function __construct()
		{
			return parent::__construct();
		}
	}
}