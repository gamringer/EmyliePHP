<?php

namespace Emylie\Routing
{
	interface Ventureable
	{
		public function getName();

		public function getPattern();

		public function getDestination();

		public function getData();

		public function match($target, &$extract = null);
	}
}