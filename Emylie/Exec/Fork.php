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

		public function __construct(){}

		public function run(Callable $command, $context = null){
			if(!$this->_running){
				$this->_running = true;
			}
			
			if($context == null){
				$context = $this;
			}
			$command = $command->bindTo($context);

			$ppid = getmypid();
			$pid = pcntl_fork();
			if($pid == 0){

				$this->_pid = getmypid();
				$this->_ppid = $ppid;

				$result = $command();

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
		}

		public function getPID(){
			return $this->_pid;
		}

		public function getPPID(){
			return $this->_ppid;
		}

		public function isRunning(){
			return $this->_running;
		}
	}
}