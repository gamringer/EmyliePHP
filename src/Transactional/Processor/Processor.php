<?php

namespace Emylie\Transactional\Processor{

	use \Emylie\Core\Config;
	use \Emylie\Traits\Multiton;
	use \Emylie\Transactional\Transaction\Transaction;

	class Processor{
		use multiton;

		private static final function _factory($name){
			$processorClassName = __CLASS__ . Config::$config['transactional'][$name]['processor'];

			return new $processorClassName($name);
		}

		public final function process(Transaction $transaction, $options = []){
			$transaction->setProcessor($this);

			return $transaction->initiate($options);
		}
	}

}