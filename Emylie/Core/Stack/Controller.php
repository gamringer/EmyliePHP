<?php

namespace Emylie\Core\Stack {

	class Controller{

		public $default_action = 'default';
		public $data;
		public $stack;

		public $display_result_container;

		public function __construct(){
			$this->data = [];
		}

		public function __call($function_name, $arguments){
			if(substr($function_name, 0, 7) == 'action_'){
				return false;
			}
		}

		public function URLRedirect($url, $parameters = array()){

			if(!empty($parameters)){
				$query = http_build_query($parameters);
				$marker_position = strpos($url, '?');

				if(is_integer($marker_position)){
					if($marker_position + 1 < strlen($url)){
						$url .= '&';
					}
				}else{
					$url .= '?';
				}

				$url .= $query;
			}

			header('Location: '.$url);
			exit();
		}

		public function displayIn($stack){
			$stack->execute();
			$this->display_result_container = $stack;
		}

		public function pushFile($path, $name){
			if (file_exists($path)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.$name.'"');
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($path));
				ob_clean();
				flush();
				readfile($path);
				exit;
			}
		}

	}
}
