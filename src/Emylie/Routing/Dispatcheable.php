<?php

namespace Emylie\Routing
{
	interface Dispatcheable
	{
		public function getData();

		public function getRoute();

		public function getRequest();
	}
}