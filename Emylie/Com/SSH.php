<?php
/**
 * SSH communication abstraction for php_ssh2
 *
 * @copyright  2011 Guillaume Amringer
 * @version    Release: 1.0
 * @author     Guillaume Amringer
 */

namespace Emylie\Com{

	class SSH{

		private static $_instances = array();

		private $_connection;
		private $_fileConnection;
		private $_shell;
		private $_sftp;

		public $is_connected = false;

		public $wait = 120000;

		private $_currentUser;
		private $_currentDir;

		private $_server;
		private $_port;
		private $_username;
		private $_password;

		public static function get($instance = '_default'){
			if (!isset(self::$_instances[$instance])) {
				self::$_instances[$instance] = new self();
			}

			return self::$_instances[$instance];
		}

		public function connect($server, $port, $username = null, $password = null){
			$this->_server = $server;
			$this->_port = $port;

			$this->_connection = ssh2_connect($server, $port);

			if (!is_resource($this->_connection)) {
				trigger_error('Could not Connect to ' . $server . ' on port ' . $port, E_USER_WARNING);
			}else{
				$this->is_connected = true;
			}

			if (null !== $username && null !== $password) {
				$this->login($username, $password);
			}
		}

		public function login($username, $password){
			if (!is_resource($this->_connection)) {
				trigger_error('Connection to server is not established for authentication', E_USER_WARNING);
				return;
			}

			$this->_username = $username;
			$this->_password = $password;

			ssh2_auth_password($this->_connection, $username, $password);
			$this->_shell = ssh2_shell($this->_connection);

			$data = array();
			$endReached = false;
			$i=0;
			while (!$endReached) {
				$line = fgets($this->_shell);
				if($line != false){
					if(preg_match('/^.*(\$|\#) $/', $line)){
						$this->fetchDir($line);
						$endReached = true;
					} elseif ($i++ > 0) {
						$data[] = $line;
					}
				}else{
					usleep($this->wait);
				}
			}

			return $data;
		}

		public function exec($command){
			if (!is_resource($this->_connection)) {
				trigger_error('Connection to server is not established for command', E_USER_WARNING);
				return;
			}

			fwrite($this->_shell, $command . PHP_EOL);

			$data = array();
			$endReached = false;
			$i=0;
			while (!$endReached) {
				$line = fgets($this->_shell);
				if($line != false){
					if(preg_match('/^.*(\$|\#) $/', $line)){
						$this->fetchDir($line);
						$endReached = true;
					} elseif ($i++ > 0) {
						$data[] = $line;
					}
				}else{
					usleep($this->wait);
				}
			}

			return implode('', $data);
		}

		public function getCurrentDir(){
			if (!is_resource($this->_connection)) {
				trigger_error('Connection to server is not established', E_USER_WARNING);
				return;
			}

			return $this->_currentDir;
		}

		private function fetchDir($line){
			preg_match('/^(.*)@(.*):(.*)\$/', $line, $matches);
			if(isset($matches[3])){
				$this->_currentDir = trim($matches[3]);
			}
		}

		public function upload($local, $remote, $mode = 0755){
			if (!is_resource($this->_connection)) {
				trigger_error('Connection to server is not established for upload', E_USER_WARNING);
				return;
			}

			ssh2_scp_send($this->_getFileConnection(), $local, $remote, $mode);
		}

		public function download($remote, $local){
			if (!is_resource($this->_connection)) {
				trigger_error('Connection to server is not established for download', E_USER_WARNING);
				return;
			}

			ssh2_scp_recv($this->_getFileConnection(), $remote, $local);
		}

		private function _getFileConnection(){
			if(null === $this->_fileConnection){
				$this->_fileConnection = ssh2_connect($this->_server, $this->_port);
				ssh2_auth_password($this->_fileConnection, $this->_username, $this->_password);
			}

			return $this->_fileConnection;
		}
	}

}