<?php

namespace Emylie\Transactional\Processor{

	use \Emylie\Core\Config;
	use \Emylie\Transactional\Transaction\Purchase;
	use \Emylie\Transactional\Transaction\Credit;

	class ProcessorPaypalAP extends Processor{

		const FEES_SENDER = 'SENDER';
		const FEES_PRIMARY = 'PRIMARYRECEIVER';
		const FEES_EACH = 'EACHRECEIVER';
		const FEES_SECONDARY = 'SECONDARYONLY';

		private $_username;
		private $_password;
		private $_gate;
		private $_signature;
		private $_app_id;
		private $_redirect;
		private $_accountID;
		private $_timeout = 0;

		protected function _init(){
			$this->_username = Config::$config['transactional'][$this->_name]['username'];
			$this->_password = Config::$config['transactional'][$this->_name]['password'];
			$this->_gate = Config::$config['transactional'][$this->_name]['gate'];
			$this->_redirect = Config::$config['transactional'][$this->_name]['redirect'];
			$this->_signature = Config::$config['transactional'][$this->_name]['signature'];
			$this->_app_id = Config::$config['transactional'][$this->_name]['app_id'];
			$this->_accountID = Config::$config['transactional'][$this->_name]['account_id'];
			$this->_timeout = Config::$config['transactional'][$this->_name]['timeout'];
		}

		public function handlePaypalBalancePurchase(Purchase $purchase, $options){

			$parameters = [
				'actionType' => 'Pay',
				'currencyCode' => $purchase->getCurrency(),
				'receiverList' => [
					'receiver' => [
						[
							'amount' => $purchase->getAmount(),
							'email' => $this->_accountID
						]
					]
				],
				'trackingId' => $purchase->getID(),
				'returnUrl' => $options['return_url'],
				'cancelUrl' => $options['cancel_url'],
				'feesPayer' => $options['fees'],
				'requestEnvelope' => [
					'errorLanguage' => 'en_US',
					'detailLevel' => 'ReturnAll'
				]
			];
			if(isset($options['ipn_url'])){
				$parameters['ipnNotificationUrl'] = $options['ipn_url'];
			}

			$requestResult = $this->_issueRequest($parameters);

			if(isset($requestResult['response']['payKey'])){
				header('Location: '.$this->_redirect.'?cmd=_ap-payment&paykey='.$requestResult['response']['payKey']);
				exit;
			}else{
				$requestResult['transaction_result']['success'] = false;
				$requestResult['transaction_result']['error'] = Purchase::ERROR_UNKNOWN;
			}

			return $requestResult;
		}

		public function handlePaypalBalanceCredit(Credit $credit, $options){

			$parameters = [
				'actionType' => 'Pay',
				'currencyCode' => $credit->getCurrency(),
				'senderEmail' => $this->_accountID,
				'receiverList' => [
					'receiver' => [
						[
							'amount' => $credit->getAmount(),
							'email' => $credit->getDestination()->getAccountName()
						]
					]
				],
				'trackingId' => $credit->getID(),
				'returnUrl' => 'http://google.com',
				'cancelUrl' => 'http://google.com',
				'feesPayer' => $options['fees'],
				'requestEnvelope' => [
					'errorLanguage' => 'en_US',
					'detailLevel' => 'ReturnAll'
				]
			];

			$requestResult = $this->_issueRequest($parameters);

			$requestResult['transaction_result'] = [
				'success' =>
					$requestResult['success']
				 && $requestResult['response']['responseEnvelope']['ack'] == 'Success'
				 && $requestResult['response']['paymentExecStatus'] == 'COMPLETED'
			];

			if($requestResult['transaction_result']['success']){
				$txInfo = $this->getTransactionInfoById($credit->getID());
				$requestResult['transaction_result']['reference'] = $txInfo['response']['paymentInfoList']['paymentInfo'][0]['senderTransactionId'];
			}elseif(!$requestResult['success']){
				$requestResult['transaction_result']['error'] = Purchase::ERROR_COULD_NOT_REACH;
			}else{
				$requestResult['transaction_result']['error'] = Purchase::ERROR_UNKNOWN;
			}

			return $requestResult;
		}

		public function getTransactionInfoById($id){

			$result = $this->_issueRequest([
				'actionType' => 'PaymentDetails',
				'trackingId' => $id,
				'requestEnvelope' => [
					'errorLanguage' => 'en_US',
					'detailLevel' => 'ReturnAll'
				]
			]);

			return $result;
		}

		private function _issueRequest($json){

			$action = $json['actionType'];
			$json['actionType'] = strtoupper($json['actionType']);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->_gate.'/'.$action);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_PORT, 443);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'X-PAYPAL-SECURITY-USERID: '.$this->_username,
				'X-PAYPAL-SECURITY-PASSWORD: '.$this->_password,
				'X-PAYPAL-SECURITY-SIGNATURE: '.$this->_signature,
				'X-PAYPAL-REQUEST-DATA-FORMAT: JSON',
				'X-PAYPAL-RESPONSE-DATA-FORMAT: JSON',
				'X-PAYPAL-APPLICATION-ID: '.$this->_app_id
			]);

			$result = curl_exec($ch);
			curl_close($ch);

			return [
				'success' => $result !== false,
				'response' => json_decode($result, true)
			];

			return $result;
		}

	}

}