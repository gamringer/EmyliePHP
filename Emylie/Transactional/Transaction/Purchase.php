<?php

namespace Emylie\Transactional\Transaction{

	class Purchase extends Transaction{

		protected $_type = 'purchase';
		private $_source;
		private $_amount;
		private $_currency;

		public function __construct($info){

			parent::__construct($info);

			$this->_source = $info['source'];
			$this->_amount = $info['amount'];
			$this->_currency = $info['currency'];
		}

		public function getCurrency(){
			return $this->_currency;
		}

		public function getSource(){
			return $this->_source;
		}

		public function getAmount(){
			return $this->_amount;
		}

		public function initiate($options){

			//	Make Sure Processor Was Set
			if(null == $this->_processor){
				return false;
			}

			$result = $this->_source->purchase($this, $options);

			if($result['transaction_result']['success']){
				$this->_processorReferenceNumber = $result['transaction_result']['reference'];
				$this->_success = $result['transaction_result']['success'];
				$this->_approved = $result['transaction_result']['approved'];
				$this->_securityResult = $this->_source->validateSecurityVerification($result['transaction_result']['security']);
			}else{
				$this->_error = $result['transaction_result']['error'];
			}

			return $result['transaction_result']['success'];
		}

	}

}