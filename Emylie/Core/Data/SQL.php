<?php

namespace Emylie\Core\Data {

	use \Emylie\Core\Config;

	class SQL{

		private static $_instances = array();

		private $_name;
		private $_info;
		private $_connection_write = null;
		private $_connection_read = null;

		private $_tables = array();

		public function __construct($i_name, $info = null){
			$this->_name = $i_name;

			if($info !== null){
				$this->_info = $info;
			}
		}

		public static function db($i_name = 'default', $info = null){
			if(!isset(self::$_instances[$i_name])){
				self::$_instances[$i_name] = new SQL($i_name, $info);
			}

			return self::$_instances[$i_name];
		}

		public function connect($mode){
			$connection = '_connection_'.$mode;

			if(null === $this->$connection){
				$info = isset($this->_info) ? $this->_info : Config::$config['db']['mysql'][$this->_name];

				$server = $info[$mode];

				if(is_array($server)){
					$server = $server[rand(0, sizeof($server)-1)];
				}
				$server = explode(':', $server);
				$this->$connection = mysqli_init();
				if(!mysqli_real_connect(
					$this->$connection,
					$server[0],
					$info['username'],
					$info['password'],
					$info['db_name'],
					isset($server[1]) ? $server[1] : 3306
				)){
					$this->error($this->$connection);
				}

				mysqli_set_charset($this->$connection, $info['charset']);
			}
			
			return $this;
		}

		public function getConnection($mode){
			$connection = '_connection_'.$mode;

			if($this->$connection == null){
				$this->connect($mode);
			}

			return $this->$connection;
		}

		public function xBegin($mode){
			mysqli_autocommit($this->getConnection($mode), false);
		}

		public function xCommit($mode){
			mysqli_commit($this->getConnection($mode), true);
		}

		public function xCommitFinal($mode){
			mysqli_autocommit($this->getConnection($mode), true);
		}

		public function xRollback($mode){
			mysqli_rollback($this->getConnection($mode));
		}

		public function xRollbackFinal($mode){
			mysqli_rollback($this->getConnection($mode));
			mysqli_autocommit($this->getConnection($mode), true);
		}

		public function escape($value, $quotes = true){
			if($value === null){
				return 'NULL';
			}

			$connection = null;
			if(null !== $this->_connection_write){
				$connection = $this->getConnection('write');

			}elseif(null !== $this->_connection_read){
				$connection = $this->getConnection('read');

			}else{
				$connection = $this->getConnection('read');
			}

			$q = '';
			if($quotes){
				$q = '"';
			}

			return $q.mysqli_real_escape_string($connection, $value).$q;
		}

		public function read($sql){
			if(null === $this->_connection_read){
				$this->connect('read');
			}

			$result = mysqli_query($this->_connection_read, $sql);

			$return = array();

			if($result === false){
				$this->error($this->_connection_read, $sql);
			} else {
				while($row = mysqli_fetch_assoc($result)){
					$return[] = $row;
				}
			}

			return $return;
		}

		public function readRow($sql){
			$result = $this->read($sql);

			return isset($result[0]) ? $result[0] : null;
		}

		public function readValue($sql){
			$result = $this->readRow($sql);

			return ($result !== null) ? array_shift($result) : null;
		}

		public function readColumn($sql){
			$result = $this->read($sql);

			if ($result !== null) {
				$resColumn = array();
				// create column array and return it
				foreach ($result as $r) {
					$resColumn[] = array_shift($r);
				}

				return $resColumn;
			}

			return null;
		}

		public function write($sql){
			if(null === $this->_connection_write){
				$this->connect('write');
			}

			$result = mysqli_multi_query($this->_connection_write, $sql);

			if($result === false){
				$this->error($this->_connection_write, $sql);

				return false;
			}

			$return = null;
			do {
				if($result = mysqli_store_result($this->_connection_write)){
					$return = mysqli_fetch_assoc($result);
					$result->free();
				}
			} while (mysqli_more_results($this->_connection_write) && mysqli_next_result($this->_connection_write));

			return $return;
		}

		public function select($statements){

			if(!isset($statements['offset']) || $statements['offset'] < 0){
				$statements['offset'] = 0;
			}

			$sql = array();
			$sql[] = 'SELECT '.implode(',',$statements['fields']);
			$sql[] = 'FROM '.implode(',',$statements['from']);
			if(isset($statements['joins'])){
				foreach($statements['joins'] as $table => $field){
					$sql[] = 'JOIN '.$table.' '.(substr($field, 0, 3) == 'ON ' ? $field : 'USING('.$field.')');
				}
			}
			if(isset($statements['left-joins'])){
				foreach($statements['left-joins'] as $table => $field){
					$sql[] = 'LEFT JOIN '.$table.' '.(substr($field, 0, 3) == 'ON ' ? $field : 'USING('.$field.')');
				}
			}
			if(isset($statements['where'][0]))$sql[] = 'WHERE '.implode(' AND ',$statements['where']);
			if(isset($statements['group'][0]))$sql[] = 'GROUP BY '.implode(', ',$statements['group']);
			if(isset($statements['having'][0]))$sql[] = 'HAVING '.implode(' AND ',$statements['having']);
			if(isset($statements['order'][0]))$sql[] = 'ORDER BY '.implode(', ',$statements['order']);
			if(isset($statements['limit']))$sql[] = 'LIMIT '.$statements['offset'].','.$statements['limit'];

			return $this->read(implode(' ', $sql));
		}

		public function selectOne($statements){
			$statements['limit'] = 1;

			$result = $this->select($statements);
			if(isset($result[0])){
				return $result[0];
			}

			return null;
		}

		public function selectValue($statements){

			$result = $this->selectOne($statements);
			if($result != null){
				return array_shift($result);
			}

			return null;
		}

		public function insert($table_name, $item, $ignore = true, $updates = []){

			$keys = [];
			$values = [];
			foreach($item as $key => &$value){
				$keys[] = $key;
				$values[] = $value;
			}

			$updateStatements = [];
			if(!$ignore){
				foreach($updates as $field => $value){
					$updateStatements[] = $field.'='.$value;
				}
			}

			$sql = 'INSERT '.($ignore ? 'IGNORE ' : '').'INTO '.$table_name.' (`'.implode('`,`', $keys).'`) VALUES ('.implode(',', $values).')'.(!empty($updateStatements) ? ' ON DUPLICATE KEY UPDATE '.implode(',', $updateStatements) : '').';SELECT LAST_INSERT_ID() AS id;';

			return $this->write($sql)['id'];
		}

		public function insertMany($table_name, $items, $options = array()){

			$values = array();
			foreach($items as $item){
				$values[] = '('.implode(',', $item).')';
			}

			$ignore = isset($options['ignore']) && $options['ignore'] ? ' IGNORE' : '';

			$sql = 'INSERT'.$ignore.' INTO '.$table_name.' (`'.implode('`,`', array_keys($items[0])).'`) VALUES '.implode(',', $values).'';

			$this->write($sql);
		}

		public function update($statements){

			$sql = 'UPDATE '.$statements['table_name'].' SET '.implode(',', $statements['set']).' WHERE '.implode(' AND ', $statements['where']).' LIMIT 1';

			$this->write($sql);
		}

		public function delete($statements){

			if(isset($statements['limit']) && (int) $statements['limit'] < 1){
				unset($statements['limit']);
			}

			$sql = 'DELETE FROM '.$statements['from'].' WHERE '.implode(' AND ',$statements['where']).(isset($statements['limit']) ? ' LIMIT '.((int) $statements['limit']) : '');

			$this->write($sql);
		}

		public function error($connection, $sql = ''){

			if(is_resource($connection)){
				$msg = mysqli_error($connection);
			}else{
				$msg = mysqli_error($connection);
			}

			trigger_error($msg . ' |===| ' . $sql, E_USER_WARNING);
		}

		public function close($mode = null){

			if(($mode == null || $mode == 'read') && $this->_connection_read != null){
				mysqli_close($this->_connection_read);
				$this->_connection_read = null;
			}

			if(($mode == null || $mode == 'write') && $this->_connection_write != null){
				mysqli_close($this->_connection_write);
				$this->_connection_write = null;
			}

		}
	}
}
