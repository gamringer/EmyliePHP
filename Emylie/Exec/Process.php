<?php

namespace Emylie\Exec{
	Class Process{
		
		public static $forked = false;

		private static $_runningChildren = [];
		private static $_path = [];

		public static function fork(){

			//	If hasn't yet forked, set signal listener
			if(!self::$forked){
				//self::_prepareFirstFork();
			}

			//	Set Variables for new process
			$fork = new Fork();
			$fork->listen('start', function($e) {
				self::$_runningChildren[$e['target']->getPID()] = $e['target'];
			});
			$fork->listen('complete', function($e) {
				unset(self::$_runningChildren[$e['target']->getPID()]);
			});

			return $fork;
		}

		private static function _prepareFirstFork(){
			pcntl_signal(SIGCHLD, function($signal){
				while($childpid = pcntl_waitpid(0, $status, WNOHANG)){
					if($childpid == -1){
						return;
					}

					self::$_runningChildren[$childpid]->handleSignalStatus(pcntl_wexitstatus($status));
				}
			});
		}

		public static function awaitRelease(){
			while(!empty(self::$_runningChildren)){
				pcntl_signal_dispatch();
			}
		}

		public static function checkRelease(){
			if(!empty(self::$_runningChildren)){
				pcntl_signal_dispatch();
			}
		}

		public static function getPID(){
			return getmypid();
		}

	}
}	