<?php
namespace Emylie\Com {

	class Email{
		public $subject = '';
		public $content = '';
		public $htmlContent = '';

		public $emitter = '';
		public $replyAddress = '';

		private $_recipients = array();
		private $_ccRecipients = array();
		private $_bccRecipients = array();

		private $_attachments = array();
		private $_headers = array();

		public function __construct(){

		}

		public function addRecipient($address){
			$this->_recipients[] = $address;
		}

		public function addCC($address){
			$this->_ccRecipients[] = $address;
		}

		public function addBCC($address){
			$this->_bccRecipients[] = $address;
		}

		public function attachFile($path){
			$this->_attachments[] = $path;
		}

		public function addHeader($header){
			$this->_headers[] = $header;
		}

		public function loadTemplateFile($path, $vars){
			if(is_file($path)){
				ob_start();
				extract($vars);
				include $path;
				$content = ob_get_contents();
				ob_end_clean();
			}

			$this->htmlContent = $content;
		}

		public function dispatch(){
			//	Normalize Content
			$this->content = wordwrap($this->content, 70);
			$this->htmlContent = wordwrap($this->htmlContent, 70);

			//	Set Main Headers
			$this->_headers[] = 'X-Mailer: PHP/' . phpversion();
			$this->_headers[] = 'MIME-Version: 1.0';

			//	Set Emitter
			if (isset($this->emitter[0])) {
				$this->_headers[] = 'From: '.$this->emitter;
			}

			//	Set Reply Address
			if (isset($this->replyAddress[0])) {
				$this->_headers[] = 'Reply-to: '.$this->replyAddress;
			} elseif(isset($this->emitter[0])) {
				$this->_headers[] = 'Reply-to: '.$this->emitter;
			}

			//	Set Boundaries
			$hash = md5(uniqid(time(), true));
			$mxdBoundary = 'MIX'.$hash;
			$altBoundary = 'ALT'.$hash;

			//	Set Content and Type
			$contentTypeHeader = 'Content-type: text/plain; charset=utf-8';
			$body = $this->content;
			if (isset($this->htmlContent[0])) {
				if (isset($this->content[0])) {
					$contentTypeHeader = 'Content-type: multipart/alternative; boundary='.$altBoundary;

					$body =
						"\n" . '--' . $altBoundary . "\n" .
						'Content-type: text/plain; charset=utf-8' . "\r\n" .
						'Content-Transfer-Encoding: 7bit' . "\r\n\r\n" .
						$this->content .

						"\n" . '--' . $altBoundary . "\n" .
						'Content-type: text/html; charset=utf-8' . "\r\n" .
						'Content-Transfer-Encoding: 7bit' . "\r\n\r\n" .
						$this->htmlContent .

						"\n" . '--' . $altBoundary . '--' . "\n"
					;
				} else {
					$contentTypeHeader = 'Content-type: text/html; charset=utf-8';
					$body = $this->htmlContent;
				}
			}

			if (isset($this->_attachments[0])) {

				$body =
					"\n" . '--' . $mxdBoundary . "\n" .
					$contentTypeHeader . "\r\n" .
					$body
				;

				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				foreach ($this->_attachments as $attachment) {
					$filename = basename($attachment);
					$mime = finfo_file($finfo, $attachment);

					$body .=
						"\n" . '--' . $mxdBoundary . "\n" .
						'Content-Type: '.$mime.'; name="'.$filename.'"' . "\r\n" .
						'Content-Transfer-Encoding: base64' . "\r\n" .
						'Content-Disposition: attachment' . "\r\n" .
						"\r\n" .
						chunk_split(base64_encode(file_get_contents($attachment))) . "\r\n"
					;
				}

				$body .= "\n" . '--' . $mxdBoundary . '--' . "\n";

				$contentTypeHeader = 'Content-Type: multipart/mixed; boundary='.$mxdBoundary;
			}

			$this->_headers[] = $contentTypeHeader;

			if(!empty($this->_ccRecipients)){
				$this->_headers[] = 'CC: '.implode(',',$this->_ccRecipients);
			}
			if(!empty($this->_bccRecipients)){
				$this->_headers[] = 'BCC: '.implode(',',$this->_bccRecipients);
			}

			$to = implode(',',$this->_recipients);
			$headers = implode("\r\n", $this->_headers);

			mail($to, $this->subject, $body, $headers);
		}
	}
}