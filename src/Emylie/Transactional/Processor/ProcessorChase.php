<?php

namespace Emylie\Transactional\Processor{

	use \Emylie\Core\Config;
	use \Emylie\Transactional\Transaction\Purchase;

	class ProcessorChase extends Processor{

		private $_username;
		private $_password;
		private $_gates;
		private $_accountNumber;
		private $_bin;
		private $_terminal;
		private $_currentGate = 0;
		private $_timeout = 0;

		protected function _init(){
			$this->_username = Config::$config['transactional'][$this->_name]['username'];
			$this->_password = Config::$config['transactional'][$this->_name]['password'];
			$this->_gates = Config::$config['transactional'][$this->_name]['gates'];
			$this->_accountNumber = Config::$config['transactional'][$this->_name]['account_number'];
			$this->_bin = Config::$config['transactional'][$this->_name]['bin'];
			$this->_terminal = Config::$config['transactional'][$this->_name]['terminal'];
			$this->_timeout = Config::$config['transactional'][$this->_name]['timeout'];
		}

		public function handleCCPurchase(Purchase $purchase){

			$dom = new \DOMDocument('1.0');
			$domRequest = $dom->createElement('Request');
			$dom->appendChild($domRequest);

			$domNewOrder = $dom->createElement('NewOrder');
			$domRequest->appendChild($domNewOrder);

			//	Merchant Info
			$domNewOrder->appendChild($dom->createElement('OrbitalConnectionUsername', $this->_username));
			$domNewOrder->appendChild($dom->createElement('OrbitalConnectionPassword', $this->_password));
			$domNewOrder->appendChild($dom->createElement('IndustryType', 'EC'));
			$domNewOrder->appendChild($dom->createElement('MessageType', 'AC'));
			$domNewOrder->appendChild($dom->createElement('BIN', $this->_bin));
			$domNewOrder->appendChild($dom->createElement('MerchantID', $this->_accountNumber));
			$domNewOrder->appendChild($dom->createElement('TerminalID', $this->_terminal));

			//	Card Info
			$domNewOrder->appendChild($dom->createElement('AccountNum', $purchase->getSource()->number));
			$domNewOrder->appendChild($dom->createElement('Exp', $purchase->getSource()->expirationMonth.substr($purchase->getSource()->expirationYear, -2)));

			//	Currency Info
			$domNewOrder->appendChild($dom->createElement('CurrencyCode', $this->_getCurrencyCode($purchase->getCurrency())));
			$domNewOrder->appendChild($dom->createElement('CurrencyExponent', $this->_getCurrencyExponent($purchase->getCurrency())));

			//	Security Number info
			if($purchase->getSource()->securityValue != null){
				$domNewOrder->appendChild($dom->createElement('CardSecValInd', '1'));
				$domNewOrder->appendChild($dom->createElement('CardSecVal', $purchase->getSource()->securityValue));
			}

			//	AVS
			if($purchase->getSource()->avs != null){
				if(isset($purchase->getSource()->avs['postal_code'])){
					$domNewOrder->appendChild($dom->createElement('AVSzip', $purchase->getSource()->avs['postal_code']));
				}

				if(isset($purchase->getSource()->avs['holder_name'])){
					$domNewOrder->appendChild($dom->createElement('AVSname', $purchase->getSource()->avs['holder_name']));
				}
			}

			//	Order Info
			$domNewOrder->appendChild($dom->createElement('OrderID', $purchase->getId()));
			$domNewOrder->appendChild($dom->createElement('Amount', ($purchase->getAmount() * 100)));

			//	Issue Request
			$requestResult = $this->_issueRequest($dom);

			//	Check Success
			$requestResult['transaction_result'] = [
				'success' =>
					$requestResult['responseElement'] != null
				 && $requestResult['response']['ProcStatus'] == '0'
			];

			//	Analyze Response Elements if successful
			if($requestResult['transaction_result']['success']){

				//	Check Approval
				$requestResult['transaction_result']['approved'] =
					$requestResult['response']['ApprovalStatus'] == '1'
				 && $requestResult['response']['RespCode'] == '00'
				;

				//	Check Security Components
				$requestResult['transaction_result']['security']['card_verification'] = $requestResult['response']['CVV2RespCode'] == 'M';
				$requestResult['transaction_result']['security']['address_verification'] = in_array(
					$requestResult['response']['AVSRespCode'],
					['H', '1', '4', 'R', '7', '8', '9', 'J', 'JA', 'JB', 'JC', 'JD', 'M1', 'M2', 'M3', 'M4', 'N3', 'N5', 'N6', 'N7', 'N8', 'N9', 'X', '']
				);

				//	Check Processor Reference ID
				$requestResult['transaction_result']['reference'] = $requestResult['response']['TxRefNum'];
			}else{
				$requestResult['transaction_result']['security']['card_verification'] = null;
				$requestResult['transaction_result']['security']['address_verification'] = null;
				$requestResult['transaction_result']['approved'] = null;

				if($requestResult['responseElement'] == null){
					$requestResult['transaction_result']['error'] = Purchase::ERROR_COULD_NOT_REACH;
				}else{
					$requestResult['transaction_result']['error'] =
						isset(static::$_procErrors[$requestResult['response']['ProcStatus']])
						? static::$_procErrors[$requestResult['response']['ProcStatus']]
						: static::$_procErrors[0]

					;
				}
			}

			return $requestResult;
		}

		private function _getCurrencyCode($currencyISO){
			$currencyISO = strtolower($currencyISO);
			$currencies = [
				'cad' => 124,
				'usd' => 840
			];

			return isset($currencies[$currencyISO]) ? $currencies[$currencyISO] : '';
		}

		private function _getCurrencyExponent($currencyISO){
			$currencyISO = strtolower($currencyISO);
			$currencies = [
				'cad' => 2,
				'usd' => 2
			];

			return isset($currencies[$currencyISO]) ? $currencies[$currencyISO] : '';
		}

		private function _issueRequest($xml){

			$postData = $xml->saveXML();

			$tryIndex = 0;
			while($tryIndex < 3){
				$result = $this->_analyzeResponse(
					$this->_tryRequest($postData)
				);

				if($result === null){
					if(!isset($this->_gates[++$this->_currentGate])){
						$result = [
							'success' => false,
							'retry' => false,
							'responseElement' => null
						];
					}else{
						continue;
					}
				}

				if(!$result['retry']){
					break;
				}

				$tryIndex++;
			}

			$response = [];
			if($result['responseElement'] !== null){
				foreach($result['responseElement']->childNodes as $child){
					$response[$child->nodeName] = trim($child->nodeValue);
				}
			}
			$result['response'] = $response;

		    return $result;
		}

		private function _tryRequest($request){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->_gates[$this->_currentGate].'/authorize');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_PORT, 443);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
			curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
			curl_setopt($ch, CURLOPT_HTTPHEADER, [
				'MIME-Version: 1.1 ',
				'Content-Type: application/PTI56',
				'Content-Length: '.strlen($request),
				'Content-Transfer-Encoding: text',
				'Request-Number: 1',
				'Document-Type: Request',
				'Interface-Version: Clicko - PHP '.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION
			]);

			$result = curl_exec($ch);
			curl_close($ch);

			return $result;
		}

		private function _analyzeResponse($result){

			if($result === false){
				return null;
			}

			$response = new \DOMDocument();
		    $response->loadXML($result);

			$el = $response->documentElement->firstChild;
			if($el->tagName == 'QuickResp'){

				return [
					'success' => false,
					'retry' => $this->_shouldRetryRequest($el->getElementsByTagName('ProcStatus')->item(0)->firstChild->wholeText),
					'responseElement' => $el
				];
			}

			return [
				'success' => true,
				'retry' => false,
				'responseElement' => $el
			];
		}

		private function _shouldRetryRequest($procStatus){

			if(isset(static::$_procStatusAction[$procStatus])){
				return true;
			}

			return false;
		}

		private static $_procStatusAction = [
			1 => 'retry',
			2 => 'retry',
			3 => 'retry',
			40 => 'retry',
			205 => 'retry',
			208 => 'retry',
			301 => 'retry',
			303 => 'retry',
			410 => 'retry',
			411 => 'retry',
			9737 => 'retry',
			9738 => 'retry',
			10011 => 'retry',
			9710 => 'retry',
			9711 => 'wait-retry',
			9712 => 'retry',
			1 => 'retry',
			1 => 'retry',
			1 => 'retry',
		];

		private static $_procErrors = [
			0 => Purchase::ERROR_UNKNOWN,
			839 => Purchase::ERROR_BAD_CARD_INFO
		];

	}

}