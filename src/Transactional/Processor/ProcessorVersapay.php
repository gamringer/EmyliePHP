<?php

namespace Emylie\Transactional\Processor{

	use \Emylie\Core\Config;
	use \Emylie\Transactional\Transaction\Purchase;

	class ProcessorVersapay extends Processor{

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

		public function handleBAPurchase(Purchase $purchase){

			$dom = new \DOMDocument('1.0');

			//	Issue Request
			$requestResult = $this->_issueRequest($dom);

			return $requestResult;
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

	}

}