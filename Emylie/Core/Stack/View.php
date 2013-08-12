<?php

namespace Emylie\Core\Stack {

	class View{

		private $_type;
		private $_template;
		private $_content;

		public $data;
		public $child_content;

		public function __construct($type){
			$this->_type = $type;
			$this->data = array();
		}

		public function getContent($template){
			$this->_template = $template;

			if(!isset($this->_content[0])){
				$fn = APP_VIEW_DIR.$this->_type.'/'.strtolower($this->_template).'.tpl.php';
				if(is_file($fn)){

					if(null != $this->data){
						extract($this->data);
					}

					ob_start();
					include $fn;
					$this->_content = ob_get_contents();
					ob_end_clean();
				}
			}

			return $this->_content;
		}

		private function _getChildContent(){
			return is_null($this->child_content) ? '' : $this->child_content;
		}
	}
}
