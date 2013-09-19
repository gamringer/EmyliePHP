<?php

namespace Emylie\Exec{

	use Emylie\Core\Config;
	use Emylie\Traits\Dispatcher;
	use Emylie\IO\File;

	Class Fork {

		use Dispatcher;

		private $_command;
		private $_pid;
		private $_ppid;
		private $_resultFile;
		private $_running = false;

		public function __construct(){
			//$this->_resultFile = File::open($this->_getResultsPath().DIRECTORY_SEPARATOR.$this->_pid.'.erf');
		}

		public function run(Callable $command, $context = null){
			if(!$this->_running){
				$this->_running = true;
			}

			if($context == null){
				$context = $this;
			}
			$this->_command = $command->bindTo($context);
			$this->_ppid = getmypid();
			$pid = pcntl_fork();
			if($pid == 0){


				$this->_pid = getmypid();

				$command = $this->_command;
				$result = $command();
//				$this->_setResult($command());
				exit();
			}
			$this->_pid = $pid;

			$this->_dispatch([
				'name' => 'start'
			]);

			return $this;
		}

		public function handleSignalStatus($tatus){
			$this->_dispatch([
				'name' => 'complete'
			]);
			$this->_resultFile->delete();
		}

		public function getPID(){
			return $this->_pid;
		}

		public function getPPID(){
			return $this->_ppid;
		}

		private function _getResultsPath(){
			$path = '/tmp/emylie';
			if(isset(Config::$config['emylie']['process']['result_dir']) && is_dir(Config::$config['emylie']['process']['result_dir'])){
				$path = Config::$config['emylie']['process']['result_dir'];
			}

			if(!is_dir($path)){
				mkdir($path, 0777, true);
			}

			return $path;
		}

		private function _setResult($result){
			$this->_resultFile->write(json_encode($result));
		}

		public function getResult(){
			if(!$this->_resultFile->exists()){
				return null;
			}

			return json_decode($this->_resultFile->read());
		}

		public function isRunning(){
			return $this->_running;
		}
	}
}