<?php

namespace Emylie\Core\Stack {

	use \Emylie\Core\AssetsManager;
	use \Emylie\Core\Config;

	class Stack{

		private $_content;
		private $_controller;
		private $_action;
		private $_controller_name;
		private $_controller_type;
		private $_view;
		private $_display_parent;
		private $_status;
		public $result;
		public $data;

		public $template;

		public function __construct($type, $action = 'default', $data = null){
			$this->_content = '';

			$this->_action = $action;

			$this->_getController(\Emylie\Utils::toClassName($type));

			if(!is_callable($this->_controller_name.'::action_'.($action == 'default' && isset($this->_controller) ? strtolower($this->_controller->default_action) : $action))){
				$this->_status = 404;
			}

			$this->data = $data;
		}

		public function getControllerName(){
			return $this->_controller_type;
		}

		public function getActionName(){
			return $this->_action;
		}

		public function _getController($type){
			$this->_controller_type = $type;
			if(is_file(APP_CTRL_DIR.$this->_controller_type.'Controller.php')){
				$this->_controller_name = 'Apps\\'.APP.'\\controllers\\'.$this->_controller_type.'Controller';
				$this->_controller = new $this->_controller_name();
				$this->_controller->stack = $this;
			}else{
				$this->_status = 404;
			}
		}

		public function execute(){

			if($this->_action == 'default' && !method_exists($this->_controller,'action_default')){
				$this->_action = strtolower($this->_controller->default_action);
			} else {
				$this->_action = strtolower($this->_action);
			}

			$result = null;

			if(method_exists($this->_controller,'action_'.$this->_action)){
				$this->_status = 200;

				$result = $this->_controller->{'action_'.$this->_action}();
			}else{
				$this->_status = 404;
			}

			$this->result = $result;

			return $this;
		}

		public function reroute($controller, $action, $data = array()){

			$this->_action = $action;

			$this->_getController(\Emylie\Utils::toClassName($controller));

			$this->_controller->data = $data;

			$this->execute();

			return $this->result;
		}

		public function getDisplayResult($child_result = null){

			if(!isset($this->_content[0])){
				$this->_content = $this->_getContent($child_result);
			}

			if(is_null($this->_controller->display_result_container)){
				return $this->_content;
			}else{
				return $this->_controller->display_result_container->getDisplayResult($this->_content);
			}
		}

		public function addScripts(){
			$template = isset($this->template) ? $this->template : $this->_action;

			$css_path = 'assets/'.APP.'/css/'.$this->_controller_type.'/'.$template.'.css';
			if(file_exists(CDN_DIR.DIRECTORY_SEPARATOR.$css_path)){
				AssetsManager::addCSS(Config::$config['cdn']['url'].$css_path);
			}

			$js_path = 'assets/'.APP.'/js/'.$this->_controller_type.'/'.$template.'.js';
			if(file_exists(CDN_DIR.DIRECTORY_SEPARATOR.$js_path)){
				AssetsManager::addJS(Config::$config['cdn']['url'].$js_path);
			}
		}

		private function _getContent($child_content){

			$this->addScripts();

			$template = isset($this->template) ? $this->template : $this->_action;

			$this->_view = new View($this->_controller_type);

			$this->_view->data = $this->_controller->data;

			if(!is_null($child_content)){
				$this->_view->child_content = $child_content;
			}

			return $this->_view->getContent($template);
		}

		public function getStatus(){
			return $this->_status;
		}

	}
}
