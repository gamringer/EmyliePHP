<?php

namespace Emylie\Transactional\Transaction{

	use \Emylie\Transactional\Processor\Processor;

	class Transaction{

		const ERROR_UNKNOWN = 0;
		const ERROR_COULD_NOT_REACH = 1;
		const ERROR_BAD_CARD_INFO = 2;

		protected $_id;
		protected $_error;
		protected $_type;
		protected $_processor;

		protected $_success;
		protected $_approved;
		protected $_securityResult;

		protected $_processorReferenceNumber;

		public function __construct($info){
			$this->_id = isset($info['id']) ? $info['id'] : uniqid();
		}

		public final function getType(){
			return $this->_type;
		}

		public final function getID(){
			return $this->_id;
		}

		public final function getError(){
			return $this->_error;
		}

		public final function setProcessor(Processor $processor){
			$this->_processor = $processor;
		}

		public final function getProcessor(){
			return $this->_processor;
		}

		public final function getSuccess(){
			return $this->_success;
		}

		public final function getApproved(){
			return $this->_approved;
		}

		public final function getSecurityResult(){
			return $this->_securityResult;
		}

		public final function getProcessorReferenceNumber(){
			return $this->_processorReferenceNumber;
		}

		public function initiate($options){}

	}

}