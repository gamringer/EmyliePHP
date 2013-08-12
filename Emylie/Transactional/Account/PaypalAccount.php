<?php

namespace Emylie\Transactional\Account{

	use \Emylie\Traits\Singleton;

	class PaypalAccount{

		use Singleton;

		private $_accountName;

		public function __construct($info = []){
			$this->_accountName = isset($info['account_name']) ? $info['account_name'] : null;
		}

		public function purchase($transaction, $options){
			return $transaction->getProcessor()->handlePaypalBalancePurchase($transaction, $options);
		}

		public function acceptCredit($transaction, $options){
			return $transaction->getProcessor()->handlePaypalBalanceCredit($transaction, $options);
		}

		public function getAccountName(){
			return $this->_accountName;
		}

	}

}