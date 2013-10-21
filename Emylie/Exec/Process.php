<?php

namespace Emylie\Exec{
	Class Process{
		
		public static $forked = false;

		private static $_runningChildren = [];

		public static function fork(){

			if(!self::$forked){
				self::_prepareFirstFork();
			}

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
			}, true);
		}

		public static function awaitRelease(){
			while(!empty(self::$_runningChildren)){
				pcntl_signal_dispatch();

				usleep(10000);
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