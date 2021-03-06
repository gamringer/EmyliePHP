<?php

namespace Emylie\Routing
{
	class Route implements Ventureable
	{

		protected $name;
		protected $pattern;
		protected $destination;
		protected $data;

		public function __construct($name, $pattern, $destination, Array $data = [])
		{
			$this->name = (string) $name;

			$this->pattern = (string) $pattern;

			$this->destination = $destination;
			$this->data = $data;
		}

		public function getName()
		{
			return $this->name;
		}

		public function getPattern()
		{
			return $this->pattern;
		}

		public function getDestination()
		{
			return $this->destination;
		}

		public function getData()
		{
			return $this->data;
		}

		public function match($target, &$extract = null)
		{
			if (preg_match('#^'.$this->pattern.'$#', $target, $extract)) {

				$extract = array_merge($this->data, $extract);
				
				return true;
			}

			return false;
		}
	}
}