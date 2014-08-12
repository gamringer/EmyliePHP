<?php

namespace Emylie\Transactional\Account{

	class CreditCard{

		public $number;
		public $expirationMonth;
		public $expirationYear;

		public $securityValue;
		public $avs;

		public function __construct($number, $expirationMonth, $expirationYear, $securityValue = null, $avs = null){
			$this->number = $number;
			$this->expirationMonth = $expirationMonth;
			$this->expirationYear = $expirationYear;
			$this->securityValue = $securityValue;
			$this->avs = is_array($avs) ? $avs : null;
		}

		public function purchase($transaction){
			return $transaction->getProcessor()->handleCCPurchase($transaction);
		}

		public function validate(){
			return true;
		}

		public static function validateSecurityVerification(Array $securityResults){
			return $securityResults['address_verification'] && $securityResults['card_verification'];
		}

	}

}