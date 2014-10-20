<?php

namespace Emylie\Routing
{
	interface Routeable
	{
		public function getAttributes();

		public function setAttributes(\ArrayAccess $attributes);

		public function discover(Ventureable $route, callable $scope);
	}
}