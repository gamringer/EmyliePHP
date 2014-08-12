<?php

namespace Emylie\Transactional\Account{

	class BankAccount{

		public $number;
		public $transit;
		public $institution;

		public function __construct($number, $transit, $institution){
			$this->number = $number;
			$this->transit = $transit;
			$this->institution = $institution;
		}

		public function purchase($transaction){
			return $transaction->getProcessor()->handleBAPurchase($transaction);
		}

		public function validate(){
			return true;
		}

	}

}