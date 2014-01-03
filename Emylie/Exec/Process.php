<?php

namespace Emylie\Exec{
	Class Process{
		
		public static $forked = false;
		private static $_eventBase;

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
			pcntl_signal(SIGCHLD, SIG_IGN);
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

		public static function getEventBase(){

			if(!isset(static::$_eventBase)){
				static::$_eventBase = new \EventBase();
			}

			return static::$_eventBase;
		}

	}
}   