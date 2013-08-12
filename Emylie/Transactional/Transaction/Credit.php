<?php

namespace Emylie\Transactional\Transaction{

	class Credit extends Transaction{

		protected $_type = 'credit';
		private $_destination;
		private $_amount;
		private $_currency;

		public function __construct($info){

			parent::__construct($info);

			$this->_destination = $info['destination'];
			$this->_amount = $info['amount'];
			$this->_currency = $info['currency'];
		}

		public function getCurrency(){
			return $this->_currency;
		}

		public function getDestination(){
			return $this->_destination;
		}

		public function getAmount(){
			return $this->_amount;
		}

		public function initiate($options){

			//	Make Sure Processor Was Set
			if(null == $this->_processor){
				return false;
			}

			$result = $this->_destination->acceptCredit($this, $options);

			if($result['transaction_result']['success']){
				$this->_processorReferenceNumber = $result['transaction_result']['reference'];
				$this->_success = $result['transaction_result']['success'];
			}else{
				$this->_error = $result['transaction_result']['error'];
			}

			return $result['transaction_result']['success'];
		}

	}

}